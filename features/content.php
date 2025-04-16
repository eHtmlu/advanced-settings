<?php
/**
 * Content Category
 * 
 * Registers the content category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Register content category
add_action('advset_register_categories', function() {
    advset_register_category([
        'id' => 'content',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>file-document-outline</title><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z" /></svg>',
        'title' => __('Content', 'advanced-settings'),
        'description' => __('Content settings', 'advanced-settings'),
        'priority' => 20,
    ]);
});

// Register content features
add_action('advset_register_features', function() {
    advset_register_feature([
        'id' => 'content.disable_comments',
        'category' => 'content',
        'ui_config' => [
            'fields' => [
                'enabled' => [
                    'type' => 'toggle',
                    'label' => __('Disable comments', 'advanced-settings'),
                ],
            ]
        ],
        'handler_execute' => function() {
            // TODO: Implement comment disabling
        },
        'priority' => 10,
    ]);
});
