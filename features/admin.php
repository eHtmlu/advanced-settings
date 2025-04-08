<?php

return [
    'category' => 'admin',
    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
    'title' => __('Admin', 'advanced-settings'),
    'description' => __('Admin settings', 'advanced-settings'),
    'items' => [
        'admin.updates.notifications.core' => [
            'texts' => [
                'title' => __('Core Updates', 'advanced-settings'),
                'description' => __('Receive notifications when core updates are available.', 'advanced-settings'),
                'label' => __('Disable core update email notifications', 'advanced-settings'),
            ],
            'ui_component' => 'GenericToggle',
            'handler_execute' => function() {
                add_filter('auto_core_update_send_email', '__return_false');
            },
            'handler_validate' => function($value) {
                return is_bool($value);
            },
        ],

        'admin.updates.notifications.plugins' => [
            'texts' => [
                'title' => __('Plugin Updates', 'advanced-settings'),
                'description' => __('Receive notifications when plugin updates are available.', 'advanced-settings'),
                'label' => __('Disable plugin update email notifications', 'advanced-settings'),
            ],
            'ui_component' => 'GenericToggle',
            'handler_execute' => function() {
                add_filter('auto_plugin_update_send_email', '__return_false');
            },
            'handler_validate' => function($value) {
                return is_bool($value);
            },
        ],

        'admin.updates.notifications.themes.disable' => [
            'texts' => [
                'title' => __('Theme Updates', 'advanced-settings'),
                'description' => __('Receive notifications when theme updates are available.', 'advanced-settings'),
                'label' => __('Disable theme update email notifications', 'advanced-settings'),
            ],
            'ui_component' => 'GenericToggle',
            'handler_execute' => function() {
                add_filter('auto_theme_update_send_email', '__return_false');
            },
            'handler_validate' => function($value) {
                return is_bool($value);
            },
        ],
    ],
];
