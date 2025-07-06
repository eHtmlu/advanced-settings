<?php
/**
 * Developer Category
 * 
 * Registers the developer category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;



advset_register_feature([
    'id' => 'developer.debug.show_queries',
    'category' => 'developer',
    'ui_config' => fn() => [
        'tags' => [
            __('Developer', 'advanced-settings'),
            __('Debug', 'advanced-settings'),
            __('Performance', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Display total number of executed SQL queries and page loading time', 'advanced-settings'),
                'descriptionHtml' => __('Only admin users can see this', 'advanced-settings'),
            ],
        ],
    ],
    'execution_handler' => function() {
        add_action('wp_footer', function() {
            global $wpdb;
            
            if (!current_user_can('manage_options')) return;

            echo '<div style="font-size:10px;text-align:center">'.
                sprintf(__('%s SQL queries have been executed to show this page in %s seconds.', 'advanced-settings'), $wpdb->num_queries, timer_stop()).
            '</div>';
        });
    },
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'developer.settings_pages.scripts',
    'category' => 'developer',
    'experimental' => true,
    'ui_config' => fn() => [
        'tags' => [
            __('Developer', 'advanced-settings'),
            __('Frontend', 'advanced-settings'),
            __('Performance', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Script settings', 'advanced-settings'),
                'description' => __('These script settings are currently under review and may be changed or removed in the future.', 'advanced-settings'),
            ],
            'info' => [
                'type' => 'info',
                'descriptionHtml' => sprintf(__('<strong>Note:</strong> You can find the script settings page <a href="%s">here</a>.', 'advanced-settings'), admin_url('admin.php?page=advanced-settings-scripts')),
                'visible' => ['enable' => true]
            ],
        ]
    ],
    'execution_handler' => function() {
        require_once ADVSET_DIR.'/feature-setup/features/includes/developer.settings_pages.php';
        require_once ADVSET_DIR.'/feature-setup/features/includes/developer.settings_pages.scripts--actions-scripts.php';

        add_action('admin_menu', function() {
            add_options_page(
                __('Scripts', 'advanced-settings'),
                __('Scripts', 'advanced-settings'),
                'manage_options',
                'advanced-settings-scripts',
                function() {
                    include ADVSET_DIR.'/feature-setup/features/includes/developer.settings_pages.scripts--admin-scripts.php';
                }
            );
        });
    },
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'developer.settings_pages.styles',
    'category' => 'developer',
    'experimental' => true,
    'ui_config' => fn() => [
        'tags' => [
            __('Developer', 'advanced-settings'),
            __('Frontend', 'advanced-settings'),
            __('Performance', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Styles settings', 'advanced-settings'),
                'description' => __('These styles settings are currently under review and may be changed or removed in the future.', 'advanced-settings'),
            ],
            'info' => [
                'type' => 'info',
                'descriptionHtml' => sprintf(__('<strong>Note:</strong> You can find the styles settings page <a href="%s">here</a>.', 'advanced-settings'), admin_url('admin.php?page=advanced-settings-styles')),
                'visible' => ['enable' => true]
            ],
        ]
    ],
    'execution_handler' => function() {
        require_once ADVSET_DIR.'/feature-setup/features/includes/developer.settings_pages.php';
        require_once ADVSET_DIR.'/feature-setup/features/includes/developer.settings_pages.styles--actions-styles.php';

        add_action('admin_menu', function() {
            add_options_page(
                __('Styles', 'advanced-settings'),
                __('Styles', 'advanced-settings'),
                'manage_options',
                'advanced-settings-styles',
                function() {
                    include ADVSET_DIR.'/feature-setup/features/includes/developer.settings_pages.styles--admin-styles.php';
                }
            );
        });
    },
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'developer.settings_pages.post_types',
    'category' => 'developer',
    'experimental' => true,
    'ui_config' => fn() => [
        'tags' => [
            __('Developer', 'advanced-settings'),
            __('Admin', 'advanced-settings'),
            __('Posts', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Post types settings', 'advanced-settings'),
                'description' => __('These post types settings are currently under review and may be changed or removed in the future.', 'advanced-settings'),
            ],
            'info' => [
                'type' => 'info',
                'descriptionHtml' => sprintf(__('<strong>Note:</strong> You can find the post types settings page <a href="%s">here</a>.', 'advanced-settings'), admin_url('admin.php?page=advanced-settings-post-types')),
                'visible' => ['enable' => true]
            ],
        ],
    ],
    'execution_handler' => function() {
        require_once ADVSET_DIR.'/feature-setup/features/includes/developer.settings_pages.php';

        add_action('init', function() {

            $post_types = (array) get_option( 'advset_post_types', array() );
        
            if( is_admin() && current_user_can('manage_options') && isset($_GET['delete_posttype']) ) {
                unset($post_types[$_GET['delete_posttype']]);
                update_option( 'advset_post_types', $post_types );
            }
        
            if( is_admin() && current_user_can('manage_options') && isset($_POST['advset_action_posttype']) ) {
        
                extract($_POST);
        
                $labels = array(
                    'name' => $label,
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
                );
        
                $typename = sanitize_key( $type );
        
                $post_types[$type] = array(
                    'labels'              => $labels,
                    'public'              => (bool) (isset($public) ? $public : false),
                    'publicly_queryable'  => (bool) (isset($publicly_queryable) ? $publicly_queryable : false),
                    'show_ui'             => (bool) (isset($show_ui) ? $show_ui : false),
                    'show_in_menu'        => (bool) (isset($show_in_menu) ? $show_in_menu : false),
                    'query_var'           => (bool) (isset($query_var) ? $query_var : false),
                    #'rewrite'             => array( 'slug' => 'book' ),
                    #'capability_type'     => 'post',
                    'has_archive'         => (bool) (isset($has_archive) ? $has_archive : false),
                    'hierarchical'        => (bool) (isset($hierarchical) ? $hierarchical : false),
                    #'menu_position'       => (int)@$menu_position,
                    'supports'            => (array) (empty($supports) ? [] : $supports),
                    'taxonomies'          => (array) (empty($taxonomies) ? [] : $taxonomies),
                );
        
                update_option( 'advset_post_types', $post_types );
        
            }
            #print_r($post_types);
            if( sizeof($post_types)>0 )
                foreach( $post_types as $post_type=>$args ) {
                    register_post_type( $post_type, $args );
                    if( in_array( 'thumbnail', $args['supports'] ) ) {
                        add_theme_support( 'post-thumbnails', array( $post_type, 'post' ) );
                    }
                }
        
        });

        add_action('admin_menu', function() {
            add_options_page(
                __('Post Types', 'advanced-settings'),
                __('Post Types', 'advanced-settings'),
                'manage_options',
                'advanced-settings-post-types',
                function() {
                    include ADVSET_DIR.'/feature-setup/features/includes/developer.settings_pages.post_types--admin-post-types.php';
                }
            );
        });
    },
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'developer.settings_pages.hooks',
    'category' => 'developer',
    'experimental' => true,
    'ui_config' => fn() => [
        'tags' => [
            __('Developer', 'advanced-settings'),
            __('Admin', 'advanced-settings'),
            __('Frontend', 'advanced-settings'),
            __('Performance', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Filters and actions settings', 'advanced-settings'),
                'description' => __('These filters and actions settings are currently under review and may be changed or removed in the future.', 'advanced-settings'),
            ],
            'info' => [
                'type' => 'info',
                'descriptionHtml' => sprintf(__('<strong>Note:</strong> You can find the filters and actions settings page <a href="%s">here</a>.', 'advanced-settings'), admin_url('admin.php?page=advanced-settings-filters')),
                'visible' => ['enable' => true]
            ],
        ],
    ],
    'execution_handler' => function() {
        require_once ADVSET_DIR.'/feature-setup/features/includes/developer.settings_pages.php';

        $remove_filters = get_option( 'advset_remove_filters' );
        $is_advset_filter_page = isset($_GET['page']) && $_GET['page'] === 'advanced-settings-filters';
        if($is_advset_filter_page === false && is_array($remove_filters) ) {
            if( isset($remove_filters) && is_array($remove_filters) )
                foreach( $remove_filters as $tag=>$array )
                    if( is_array($array) )
                        foreach( $array as $function=>$_ )
                            remove_filter( $tag, $function );
        }

        add_action('admin_menu', function() {
            add_options_page(
                __('Filters/Actions', 'advanced-settings'),
                __('Filters/Actions', 'advanced-settings'),
                'manage_options',
                'advanced-settings-filters',
                function() {
                    include ADVSET_DIR.'/feature-setup/features/includes/developer.settings_pages.hooks--admin-filters.php';
                }
            );
        });

        add_action('wp_ajax_advset_filters', function() {
            // security
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Unauthorized');
                return;
            }

            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'advset_filters_nonce')) {
                wp_send_json_error('Invalid nonce');
                return;
            }

            $remove_filters = (array) get_option('advset_remove_filters');
            $tag = (string)$_POST['tag'];
            $function = (string)$_POST['function'];

            if ($_POST['enable'] == 'true') {
                unset($remove_filters[$tag][$function]);
            } else if ($_POST['enable'] == 'false') {
                $remove_filters[$tag][$function] = 1;
            }

            update_option('advset_remove_filters', $remove_filters);

            wp_send_json_success();
        });
    },
    'priority' => 10,
]);


