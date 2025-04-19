<?php
/**
 * Security Category
 * 
 * Registers the security category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;



advset_register_feature([
    'id' => 'security.protect_emails',
    'category' => 'security',
    'ui_config' => fn() => [
        'fields' => [
            'enabled' => [
                'type' => 'toggle',
                'label' => __('Protect email addresses from spam bots', 'advanced-settings'),
                'default' => false
            ],
            'method' => [
                'type' => 'radio',
                'label' => __('Protection method', 'advanced-settings'),
                'options' => [
                    'entities' => [
                        'label' => __('HTML entities', 'advanced-settings'),
                        'description' => __('More SEO friendly, but not as protected.', 'advanced-settings'),
                    ],
                    'javascript' => [
                        'label' => __('JavaScript', 'advanced-settings'),
                        'description' => __('Better protection, but slightly less SEO-friendly.', 'advanced-settings'),
                    ],
                ],
                'default' => 'entities',
                'visible' => ['enabled' => true]
            ],
        ]
    ],
    'handler_cleanup' => function($value) {
        return empty($value['enabled']) ? null : $value;
    },
    'execution_handler' => function($settings) {
        // TODO: Implement email protection
    },
    'priority' => 10,
]);
