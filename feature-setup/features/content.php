<?php
/**
 * Content Category
 * 
 * Registers the content category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;



advset_register_feature([
    'id' => 'content.disable_comments',
    'category' => 'content',
    'ui_config' => fn() => [
        'fields' => [
            'enabled' => [
                'type' => 'toggle',
                'label' => __('Disable comments', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        // TODO: Implement comment disabling
    },
    'priority' => 10,
]);
