<?php
/**
 * Admin Category
 * 
 * Registers the admin category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;



advset_register_feature([
    'id' => 'admin.updates.notifications.core',
    'category' => 'admin',
    'ui_config' => fn() => [
        'fields' => [
            'enabled' => [
                'type' => 'toggle',
                'label' => __('Disable core update email notifications', 'advanced-settings'),
                'descriptionHtml' => sprintf(__('You can change the admin email (which is the recipient) in %s.', 'advanced-settings'), '<a href="' . admin_url('options-general.php') . '">' . __('General Settings') . '</a>'),
            ],
        ]
    ],
    'handler_execute' => function() {
        add_filter('auto_core_update_send_email', '__return_false');
    },
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'admin.updates.notifications.plugins',
    'category' => 'admin',
    'ui_config' => fn() => [
        'fields' => [
            'enabled' => [
                'type' => 'toggle',
                'label' => __('Disable plugin update email notifications', 'advanced-settings'),
                'descriptionHtml' => sprintf(__('You can change the admin email (which is the recipient) in %s.', 'advanced-settings'), '<a href="' . admin_url('options-general.php') . '">' . __('General Settings') . '</a>'),
            ],
        ]
    ],
    'handler_execute' => function() {
        add_filter('auto_plugin_update_send_email', '__return_false');
    },
    'priority' => 20,
]);



advset_register_feature([
    'id' => 'admin.updates.notifications.themes.disable',
    'category' => 'admin',
    'ui_config' => fn() => [
        'fields' => [
            'enabled' => [
                'type' => 'toggle',
                'label' => __('Disable theme update email notifications', 'advanced-settings'),
                'descriptionHtml' => sprintf(__('You can change the admin email (which is the recipient) in %s.', 'advanced-settings'), '<a href="' . admin_url('options-general.php') . '">' . __('General Settings') . '</a>'),
            ],
        ]
    ],
    'handler_execute' => function() {
        add_filter('auto_theme_update_send_email', '__return_false');
    },
    'priority' => 30,
]);
