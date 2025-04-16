<?php
/**
 * Admin Category
 * 
 * Registers the admin category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Register admin category
add_action('advset_register_categories', function() {
    advset_register_category([
        'id' => 'admin',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>account</title><path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" /></svg>',
        'title' => __('Admin', 'advanced-settings'),
        'description' => __('Admin settings', 'advanced-settings'),
        'priority' => 10,
    ]);
});

// Register admin features
add_action('advset_register_features', function() {
    advset_register_feature([
        'id' => 'admin.updates.notifications.core',
        'category' => 'admin',
        'ui_config' => [
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
        'ui_config' => [
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
        'ui_config' => [
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
});
