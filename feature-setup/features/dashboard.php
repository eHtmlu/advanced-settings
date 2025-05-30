<?php
/**
 * Dashboard Category
 * 
 * Registers the dashboard category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;



advset_register_feature([
    'id' => 'dashboard.adminbar.remove_from_frontend',
    'category' => 'dashboard',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Hide top admin menu in the frontend', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_filter('show_admin_bar', '__return_false');
    },
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'dashboard.notices.remove_update_nag',
    'category' => 'dashboard',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Hide the WordPress update message in the Dashboard', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_action( 'admin_menu', function() {
            remove_action( 'admin_notices', 'update_nag', 3 );
        }, 2 );
    },
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'dashboard.welcome_panel.remove',
    'category' => 'dashboard',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Hide the Welcome Panel', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_action( 'admin_menu', function() {
            remove_action( 'welcome_panel', 'wp_welcome_panel' );
        }, 2 );
    },
    'priority' => 20,
]);



advset_register_feature([
    'id' => 'dashboard.adminbar.custom_logo',
    'category' => 'dashboard',
    'ui_config' => fn() => [
        'fields' => [
            'url' => [
                'type' => 'text',
                'label' => __('Custom logo URL', 'advanced-settings'),
                'placeholder' => 'https://www.example.com/your-custom-logo.png',
                'description' => __('Paste your custom dashboard logo here', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function($settings) {
        add_action('wp_before_admin_bar_render', function() use($settings) {
            if (!is_admin()) return;
            
            echo '
                <style>
                #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
                    background-image: url('.$settings['url'].') !important;
                    background-position: center;
                    background-size: contain;
                    background-repeat: no-repeat;
                    color:rgba(0, 0, 0, 0);
                }
                </style>
            ';
        });
    },
    'priority' => 30,
]);


