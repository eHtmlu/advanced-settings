<?php

if (!defined('ABSPATH')) exit;


class Advset__Feature__Post_Types {

    private static $instance = null;

    private $flush_rewrite_rules = false;

    private function __construct() {
        add_action('init', [$this, 'handle_init']);
        add_action('rest_api_init', [$this, 'rest_api_init']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', ['Advset_Tristate_Checkbox', 'enqueue_assets']);

        register_activation_hook(ADVSET_FILE, [$this, 'handle_activation']);
        register_deactivation_hook(ADVSET_FILE, [$this, 'handle_deactivation']);
    }

    public function handle_activation() {
        $this->flush_rewrite_rules = true; // set flag to flush rewrite rules on next init
    }

    public function handle_deactivation() {
        flush_rewrite_rules(); // flush immediately
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
                    $this->flush_rewrite_rules = true;
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
                    'comments',
                    'revisions',
                    'trackbacks',
                    'author',
                    'excerpt',
                    'page-attributes',
                    'thumbnail',
                    'custom-fields',
                    'post-formats',
                ];
    
                // These taxonomies are allowed to be added to the post type
                $taxonomies_allowed = [
                    'category',
                    'post_tag',
                ];

                // Get supports that are explicitly set to true
                // @TODO: Add support for FALSE value for $supports (to allow disabling all supports)
                $supports = array_intersect(array_keys(array_filter($_POST['supports'] ?? [], function($v) {
                    return Advset_Tristate_Checkbox::post_to_tristate($v) === true;
                })), $supports_allowed) ?: null;

                // Get taxonomies that are explicitly set to true
                $taxonomies = array_intersect(array_keys(array_filter($_POST['taxonomies'] ?? [], function($v) {
                    return Advset_Tristate_Checkbox::post_to_tristate($v) === true;
                })), $taxonomies_allowed) ?: null;
    
                $post_types[$type] = array_filter([
                    'labels'              => $labels,
                    'description'         => $_POST['description'] ?? null,
                    'public'              => Advset_Tristate_Checkbox::post_to_tristate($_POST['public']),
                    'hierarchical'        => Advset_Tristate_Checkbox::post_to_tristate($_POST['hierarchical']),
                    'exclude_from_search' => Advset_Tristate_Checkbox::post_to_tristate($_POST['exclude_from_search']),
                    'publicly_queryable'  => Advset_Tristate_Checkbox::post_to_tristate($_POST['publicly_queryable']),
                    'show_ui'             => Advset_Tristate_Checkbox::post_to_tristate($_POST['show_ui']),
                    'show_in_menu'        => Advset_Tristate_Checkbox::post_to_tristate($_POST['show_in_menu']),
                    'show_in_nav_menus'   => Advset_Tristate_Checkbox::post_to_tristate($_POST['show_in_nav_menus']),
                    'show_in_admin_bar'   => Advset_Tristate_Checkbox::post_to_tristate($_POST['show_in_admin_bar']),
                    'show_in_rest'        => Advset_Tristate_Checkbox::post_to_tristate($_POST['show_in_rest']),
                    //'rest_base'           => $_POST['rest_base'] ?? null,
                    //'rest_namespace'      => $_POST['rest_namespace'] ?? null,
                    //'rest_controller_class' => $_POST['rest_controller_class'] ?? null,
                    //'autosave_rest_controller_class' => $_POST['autosave_rest_controller_class'] ?? null,
                    //'revisions_rest_controller_class' => $_POST['revisions_rest_controller_class'] ?? null,
                    'late_route_registration' => Advset_Tristate_Checkbox::post_to_tristate($_POST['late_route_registration']),
                    //'menu_position'       => !empty($_POST['menu_position']) ? (int)$_POST['menu_position'] : null,
                    //'menu_icon'           => $_POST['menu_icon'] ?? null,
                    //'capability_type'     => $_POST['capability_type'] ?? null,
                    //'capabilities'        => $_POST['capabilities'] ?? null,
                    'map_meta_cap'        => Advset_Tristate_Checkbox::post_to_tristate($_POST['map_meta_cap']),
                    'supports'            => $supports,
                    //'register_meta_box_cb' => $_POST['register_meta_box_cb'] ?? null,
                    'taxonomies'          => $taxonomies,
                    'has_archive'         => Advset_Tristate_Checkbox::post_to_tristate($_POST['has_archive']),
                    //'rewrite'             => $_POST['rewrite'] ?? [],
                    'query_var'           => Advset_Tristate_Checkbox::post_to_tristate($_POST['query_var']),
                    'can_export'          => Advset_Tristate_Checkbox::post_to_tristate($_POST['can_export']),
                    //'delete_with_user'    => Advset_Tristate_Checkbox::post_to_tristate($_POST['delete_with_user']),
                    //'template'            => $_POST['template'] ?? null,
                    //'template_lock'       => $_POST['template_lock'] ?? null,
                ], function($v) {
                    return $v !== null;
                });

                update_option( 'advset_post_types', $post_types );
                $this->flush_rewrite_rules = true;
            }
        }
    
        if (!empty($post_types)) {
            foreach ($post_types as $post_type => $args) {
                register_post_type($post_type, $args);
                if (in_array('thumbnail', $args['supports'] ?? [])) {
                    add_theme_support('post-thumbnails', [$post_type, 'post']);
                }
            }
        }

        if ($this->flush_rewrite_rules) {
            flush_rewrite_rules();
            $this->flush_rewrite_rules = false;
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






