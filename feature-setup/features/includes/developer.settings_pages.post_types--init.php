<?php

if (!defined('ABSPATH')) exit;


class Advset__Feature__Post_Types {

    private static $instance = null;

    private function __construct() {
        add_action('init', [$this, 'handle_init']);
        add_action('rest_api_init', [$this, 'rest_api_init']);
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function handle_init() {
        $post_types = get_option('advset_post_types', []);

        $nonce = $_POST['_advset_posttype_nonce'] ?? null;
    
        if ($nonce && wp_verify_nonce($nonce, 'advset_posttype_nonce') && is_admin() && current_user_can('manage_options')) {
    
            // Delete post type via POST only
            if (!empty($_POST['_advset_posttype_action_delete'])) {
                $delete_slug = sanitize_key($_POST['_advset_posttype_action_delete'] ?? '');
                if (isset($post_types[$delete_slug])) {
                    unset($post_types[$delete_slug]);
                    update_option('advset_post_types', $post_types);
                }
            }
    
            // Add post type
            if (isset($_POST['_advset_posttype_action_save'])) {
    
                $type = sanitize_key( $_POST['type'] ?? '' );
    
                $type_result = $this->advset_posttypes_check_type($_POST['type'], $_POST['type_stored']);
                $type = $type_result['type_available'];
    
                if (!$type) {
                    add_action('admin_notices', function() use ($type_result) {
                        echo '<div class="notice notice-error"><p>'.__('The post type is not available.', 'advanced-settings').'</p></div>';
                    });
                    return;
                }
    
                // If type has changed, remove the old type as we are going to add the new type
                if (!empty($_POST['type_stored']) && $type !== $_POST['type_stored'] && isset($post_types[$_POST['type_stored']])) {
                    unset($post_types[$_POST['type_stored']]);
                }
    
                $labels = [
                    'name' => $_POST['label'] ?? '',
                    #'singular_name' => @$singular_name,
                    #'add_new' => @$add_new,
                    #'add_new_item' => @$add_new_item,
                    #'edit_item' => @$edit_item,
                    #'new_item' => @$new_item,
                    #'all_items' => @$all_items,
                    #'view_item' => @$view_item,
                    #'search_items' => @$search_items,
                    #'not_found' =>  @$not_found,
                    #'not_found_in_trash' => @$not_found_in_trash,
                    #'parent_item_colon' => @$parent_item_colon,
                    #'menu_name' => @$menu_name
                ];
        
                // These supports are allowed to be added to the post type
                $supports_allowed = [
                    'title',
                    'editor',
                    'author',
                    'thumbnail',
                    'excerpt',
                    'trackbacks',
                    'custom-fields',
                    'comments',
                    'revisions',
                    'page-attributes',
                ];
    
                // These taxonomies are allowed to be added to the post type
                $taxonomies_allowed = [
                    'category',
                    'post_tag',
                ];
    
                $post_types[$type] = [
                    'labels'              => $labels,
                    'description'         => $_POST['description'] ?? '',
                    'public'              => !empty($_POST['public']),
                    'publicly_queryable'  => !empty($_POST['publicly_queryable']),
                    'show_ui'             => !empty($_POST['show_ui']),
                    'show_in_menu'        => !empty($_POST['show_in_menu']),
                    'query_var'           => !empty($_POST['query_var']),
                    #'rewrite'             => array( 'slug' => 'book' ),
                    #'capability_type'     => 'post',
                    'has_archive'         => !empty($_POST['has_archive']),
                    'hierarchical'        => !empty($_POST['hierarchical']),
                    #'menu_position'       => (int)@$menu_position,
                    'supports'            => array_intersect($_POST['supports'] ?? [], $supports_allowed),
                    'taxonomies'          => array_intersect($_POST['taxonomies'] ?? [], $taxonomies_allowed),
                ];
        
                update_option( 'advset_post_types', $post_types );
            }
        }
    
        if (!empty($post_types)) {
            foreach ($post_types as $post_type => $args) {
                register_post_type($post_type, $args);
                if (in_array('thumbnail', $args['supports'])) {
                    add_theme_support('post-thumbnails', [$post_type, 'post']);
                }
            }
        }
    }

    public function rest_api_init() {
        register_rest_route('advset_posttypes/v1', '/check-type', [
            'methods' => 'POST',
            'callback' => function($request) {
                $label_input = $request->get_param('label_input');
                $type_input = $request->get_param('type_input');
                $type_stored = $request->get_param('type_stored');
                $generate_from_label = $request->get_param('generate_from_label');
    
                $type = $generate_from_label && $label_input ? $label_input : $type_input;
    
                return $this->advset_posttypes_check_type($type, $type_stored);
            },
            'args' => [
                'label_input' => [
                    'type' => 'string',
                    'required' => true,
                ],
            ],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            },
        ]);
    }

    public function admin_menu() {
        add_options_page(
            __('Custom Post Types', 'advanced-settings'),
            __('Custom Post Types', 'advanced-settings'),
            'manage_options',
            'advanced-settings-post-types',
            function() {
                include ADVSET_DIR.'/feature-setup/features/includes/developer.settings_pages.post_types--admin-post-types.php';
            }
        );
    }

    private function advset_posttypes_check_type($type_input, $type_stored = null) {
        $type = sanitize_key($type_input);
        //$type_stored = $type_stored ? '' : $type;
        $is_valid = !empty($type);
        $is_taken = $is_valid && post_type_exists($type) && $type !== $type_stored;
    
        $alternate_type = null;
        $alternate_is_taken = $is_taken;
        for ($i = 1; $alternate_is_taken && $i <= 100; $i++) {
            $alternate_type = $type . '-' . $i;
            $alternate_is_taken = post_type_exists($alternate_type) && $alternate_type !== $type_stored;
        }
        $type_available = $alternate_is_taken ? null : ($alternate_type ? $alternate_type : $type);
        return [
            'type' => $type,
            'type_stored' => $type_stored,
            'is_taken' => $is_taken,
            'is_valid' => $is_valid,
            'alternate_type' => $alternate_type,
            'alternate_is_taken' => $alternate_is_taken,
            'type_available' => $type_available,
        ];
    }
}






