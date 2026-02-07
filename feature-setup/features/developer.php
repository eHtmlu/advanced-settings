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
    'ui_config' => fn() => [
        'tags' => [
            __('Developer', 'advanced-settings'),
            __('Admin', 'advanced-settings'),
            __('Posts', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Custom post types', 'advanced-settings'),
            ],
            'info' => [
                'type' => 'info',
                'descriptionHtml' => sprintf(__('<strong>Note:</strong> You can find the post types settings page <a href="%s">here</a>.', 'advanced-settings'), admin_url('admin.php?page=advanced-settings-post-types')),
                'visible' => ['enable' => true]
            ],
        ],
    ],
    'execution_handler' => function() {
        require_once ADVSET_DIR.'/admin-ui/classic-ui-elements/tristate-checkbox/tristate-checkbox.php';
        require_once ADVSET_DIR.'/feature-setup/features/includes/developer.settings_pages.php';
        require_once ADVSET_DIR.'/feature-setup/features/includes/developer.settings_pages.post_types--init.php';
        Advset__Feature__Post_Types::init();
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


