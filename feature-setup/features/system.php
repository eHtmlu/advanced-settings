<?php
/**
 * System Category
 * 
 * Registers the system category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;



advset_register_feature([
    'id' => 'system.favicon.remove_default',
    'category' => 'system',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Remove default WordPress favicon', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_action('init', function() {
            if ($_SERVER['REQUEST_URI'] === '/favicon.ico') {
                header("Content-Type: image/x-icon");
                http_response_code(404);
                exit;
            }
        });
    },
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'system.comments.disable_system',
    'category' => 'system',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Disable comment system', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_filter( 'comments_open', '__return_false' );
        add_filter( 'comments_array', function() { return []; }, 10 );
    
        // Removes from admin menu
        add_action( 'admin_menu', function() { remove_menu_page( 'edit-comments.php' ); } );
        
        // Removes from admin bar
        add_action( 'wp_before_admin_bar_render', function() {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu('comments');
        } );
    },
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'system.posts.disable_autosave',
    'category' => 'system',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Disable auto save', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        define('AUTOSAVE_INTERVAL', 60 * 60 * 24 * 365 * 100); // save interval => 100 years
    },
    'priority' => 20,
]);



advset_register_feature([
    'id' => 'system.updates.skip_bundled_themes',
    'category' => 'system',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Prevent installation of new default WordPress themes during core updates', 'advanced-settings'),
                'descriptionHtml' => sprintf(__('By default, themes like %s are added automatically every year.', 'advanced-settings'), (function() {
                    $digit1 = [
                        '2' => 'Twenty',
                        '3' => 'Thirty',
                        '4' => 'Forty',
                        '5' => 'Fifty',
                        '6' => 'Sixty',
                        '7' => 'Seventy',
                        '8' => 'Eighty',
                        '9' => 'Ninety',
                    ];
                    $digit2 = [
                        '0' => '',
                        '1' => '-One',
                        '2' => '-Two',
                        '3' => '-Three',
                        '4' => '-Four',
                        '5' => '-Five',
                        '6' => '-Six',
                        '7' => '-Seven',
                        '8' => '-Eight',
                        '9' => '-Nine',
                    ];
                    $current_year = date('y');
                    $current_theme_slug = 'twenty' . strtolower($digit1[$current_year[0]] . substr($digit2[$current_year[1]], 1));
                    $current_theme_name = 'Twenty ' . $digit1[$current_year[0]] . $digit2[$current_year[1]];
                    $current_theme_url = 'https://wordpress.org/themes/' . $current_theme_slug . '/';

                    return '<a href="' . $current_theme_url . '" target="_blank">' . $current_theme_name . '</a>';
                })()),
            ],
        ]
    ],
    'execution_handler' => function() {
        if (!defined('CORE_UPGRADE_SKIP_NEW_BUNDLED')) {
            define('CORE_UPGRADE_SKIP_NEW_BUNDLED', true);
        }
    },
    'priority' => 20,
]);



advset_register_feature([
    'id' => 'system.notifications.disable_core_updates',
    'category' => 'system',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Disable core update email notifications', 'advanced-settings'),
                'descriptionHtml' => sprintf(__('You can change the admin email (which is the recipient) in %s.', 'advanced-settings'), '<a href="' . admin_url('options-general.php') . '">' . __('General Settings') . '</a>'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_filter('auto_core_update_send_email', '__return_false');
    },
    'priority' => 20,
]);



advset_register_feature([
    'id' => 'system.notifications.disable_plugin_updates',
    'category' => 'system',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Disable plugin update email notifications', 'advanced-settings'),
                'descriptionHtml' => sprintf(__('You can change the admin email (which is the recipient) in %s.', 'advanced-settings'), '<a href="' . admin_url('options-general.php') . '">' . __('General Settings') . '</a>'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_filter('auto_plugin_update_send_email', '__return_false');
    },
    'priority' => 30,
]);



advset_register_feature([
    'id' => 'system.notifications.disable_theme_updates',
    'category' => 'system',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Disable theme update email notifications', 'advanced-settings'),
                'descriptionHtml' => sprintf(__('You can change the admin email (which is the recipient) in %s.', 'advanced-settings'), '<a href="' . admin_url('options-general.php') . '">' . __('General Settings') . '</a>'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_filter('auto_theme_update_send_email', '__return_false');
    },
    'priority' => 40,
]);


