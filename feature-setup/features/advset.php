<?php
/**
 * Config Category
 * 
 * Registers the config category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;



advset_register_feature([
    'id' => 'advset.features.show_deprecated',
    'category' => 'advset',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Show all deprecated features', 'advanced-settings'),
                'description' => __('By default, only deprecated features that are in use are shown.', 'advanced-settings'),
            ],
        ]
    ],
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'advset.features.show_experimental',
    'category' => 'advset',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Show all experimental features', 'advanced-settings'),
                'description' => __('By default, only experimental features that are in use are shown.', 'advanced-settings'),
            ],
        ]
    ],
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'advset.user_tracking.feature_usage',
    'category' => 'advset',
    'ui_config' => fn() => [
        'fields' => [
            'status' => [
                'type' => 'select',
                'label' => __('Share plugin usage', 'advanced-settings'),
                'options' => [
                    '' => [
                        'label' => __('Not decided yet', 'advanced-settings'),
                    ],
                    'yes' => [
                        'label' => __('Yes, I agree to share my plugin usage with the developers.', 'advanced-settings'),
                    ],
                    'no' => [
                        'label' => __('No, I do not agree to share my plugin usage with the developers.', 'advanced-settings'),
                    ],
                ],
                'default' => '',
            ],
        ]
    ],
    'handler_cleanup' => function($settings) {
        return $settings['status'] === '' ? null : $settings;
    },
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'advset.features.user_guide',
    'category' => 'advset',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Show user guide', 'advanced-settings'),
                'description' => __('Enable to show the user guide again on next page load.', 'advanced-settings'),
            ],
        ]
    ],
    'handler_cleanup' => function($settings) {
        return empty($settings['enable']) ? null : $settings;
    },
    'execution_handler' => function($settings) {
        if (!empty($settings['enable'])) {
            delete_option('advset_guide_shown');
        }
    },
    'priority' => 10,
]);


