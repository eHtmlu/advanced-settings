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
    'id' => 'dashboard.widgets.remove_default',
    'category' => 'dashboard',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Remove default dashboard widgets', 'advanced-settings'),
                'description' => __('Hide Quick Draft, Activity, and other default dashboard widgets', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_action('wp_dashboard_setup', function() {
            remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
            remove_meta_box('dashboard_primary', 'dashboard', 'side');
            remove_meta_box('dashboard_activity', 'dashboard', 'normal');
            remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
            remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
        });
    },
    'priority' => 40,
]);



advset_register_feature([
    'id' => 'dashboard.branding.customize',
    'category' => 'dashboard',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Custom dashboard branding', 'advanced-settings'),
            ],
            'login_logo' => [
                'type' => 'text',
                'label' => __('Login logo', 'advanced-settings'),
                'placeholder' => 'https://www.example.com/your-custom-logo.png',
                'description' => __('Paste the URL of your custom logo here.', 'advanced-settings'),
                'visible' => ['enable' => true],
            ],
            'login_logo_max_height' => [
                'type' => 'number',
                'label' => __('Login logo max height', 'advanced-settings'),
                'placeholder' => '84',
                'min' => 0,
                'max' => 1000,
                'step' => 1,
                'description' => __('The maximum height of the login logo in pixels.', 'advanced-settings'),
                'visible' => ['enable' => true],
            ],
            'login_headertext' => [
                'type' => 'text',
                'label' => __('Login logo alt text', 'advanced-settings'),
                'placeholder' => __( 'Powered by WordPress' ),
                'description' => __('The alt text for the login logo.', 'advanced-settings'),
                'visible' => ['enable' => true],
            ],
            'login_headerurl' => [
                'type' => 'text',
                'label' => __('Login logo link to', 'advanced-settings'),
                'placeholder' => __( 'https://wordpress.org/' ),
                'description' => __('The URL to which the login logo should link to.', 'advanced-settings'),
                'visible' => ['enable' => true],
            ],
            'admin_bar_logo' => [
                'type' => 'text',
                'label' => __('Admin bar logo', 'advanced-settings'),
                'placeholder' => 'https://www.example.com/your-custom-logo.png',
                'description' => __('Leave empty to use the login logo for the admin bar as well.', 'advanced-settings'),
                'visible' => ['enable' => true],
            ],
            'footer_text' => [
                'type' => 'text',
                'label' => __('Footer text', 'advanced-settings'),
                'placeholder' => 'Powered by Your Company',
                'visible' => ['enable' => true],
            ],
        ]
    ],
    'execution_handler' => function($settings) {
        if ( empty($settings['enable']) ) {
            return;
        }
        if ( !empty($settings['footer_text']) ) {
            add_filter('admin_footer_text', function() use($settings) {
                return $settings['footer_text'];
            });
        }
        if ( !empty($settings['login_logo']) ) {
            add_action('login_head', function() use($settings) {
                echo '<style>
                    #login h1 a {
                        background-image: url("' . esc_url($settings['login_logo']) . '");
                        background-position: center;
                        background-size: contain;
                        background-repeat: no-repeat;
                        width: 100%;
                        ' . (empty($settings['login_logo_max_height']) ? '' : 'height: ' . esc_attr($settings['login_logo_max_height']) . 'px;') . '
                    }
                </style>';
            });
        }
        if ( !empty($settings['login_headertext']) ) {
            add_filter('login_headertext', function() use($settings) {
                return $settings['login_headertext'];
            });
        }
        $admin_bar_logo_url = $settings['admin_bar_logo'] ?? ($settings['login_logo'] ?? null);
        if ( !empty($admin_bar_logo_url) ) {
            add_action('wp_before_admin_bar_render', function() use($admin_bar_logo_url) {
                echo '<style>
                    #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
                        background-image: url("' . esc_url($admin_bar_logo_url) . '") !important;
                        background-position: center;
                        background-size: contain;
                        background-repeat: no-repeat;
                        color:rgba(0, 0, 0, 0);
                    }
                </style>';
            });
        }
        if ( !empty($settings['login_headerurl']) ) {
            add_filter('login_headerurl', function() use($settings) {
                return esc_url($settings['login_headerurl']);
            });
        }
    },
    'priority' => 50,
]);


