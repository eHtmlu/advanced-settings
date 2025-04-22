<?php
/**
 * Config Category
 * 
 * Registers the config category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;



advset_register_feature([
    'id' => 'advset.show_deprecated_features',
    'category' => 'advset',
    'ui_config' => fn() => [
        'fields' => [
            'enabled' => [
                'type' => 'toggle',
                'label' => __('Show all deprecated features', 'advanced-settings'),
                'description' => __('By default, only deprecated features that are in use are shown.', 'advanced-settings'),
            ],
        ]
    ],
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'advset.show_experimental_expert_features',
    'category' => 'advset',
    'ui_config' => fn() => [
        'fields' => [
            'enabled' => [
                'type' => 'toggle',
                'label' => __('Show all experimental features', 'advanced-settings'),
                'description' => __('By default, only experimental features that are in use are shown.', 'advanced-settings'),
            ],
        ]
    ],
    'priority' => 10,
]);




advset_register_feature([
    'id' => 'advset.tracksettings_choice',
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
