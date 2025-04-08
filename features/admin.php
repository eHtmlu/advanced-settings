<?php

return [
    'category' => 'admin',
    'title' => __('Admin', 'advanced-settings'),
    'description' => __('Admin settings', 'advanced-settings'),
    'items' => [
        'admin.updates.notifications.core' => [
            'texts' => [
                'title' => __('Core Updates', 'advanced-settings'),
                'description' => __('Receive notifications when core updates are available.', 'advanced-settings'),
                'label' => __('Disable core update email notifications', 'advanced-settings'),
            ],
            'ui_component' => 'generic-toggle',
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
            'ui_component' => 'generic-toggle',
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
            'ui_component' => 'generic-toggle',
            'handler_execute' => function() {
                add_filter('auto_theme_update_send_email', '__return_false');
            },
            'handler_validate' => function($value) {
                return is_bool($value);
            },
        ],
    ],
];
