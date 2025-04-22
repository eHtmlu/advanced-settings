<?php
/**
 * Dashboard Category
 * 
 * Registers the dashboard category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;



advset_register_feature([
    'id' => 'dashboard.hide.fontend_admin_bar',
    'category' => 'dashboard',
    'ui_config' => fn() => [
        'fields' => [
            'disable' => [
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
    'id' => 'dashboard.hide.update-message',
    'category' => 'dashboard',
    'ui_config' => fn() => [
        'fields' => [
            'disable' => [
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
    'id' => 'dashboard.hide.welcome-panel',
    'category' => 'dashboard',
    'ui_config' => fn() => [
        'fields' => [
            'disable' => [
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
    'id' => 'dashboard.custom-logo',
    'category' => 'dashboard',
    'ui_config' => fn() => [
        'fields' => [
            'url' => [
                'type' => 'text',
                'label' => __('Custom logo URL', 'advanced-settings'),
                'placeholder' => 'https://www.example.com/your-custom-logo.png',
                'description' => __('paste your custom dashboard logo here', 'advanced-settings'),
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



