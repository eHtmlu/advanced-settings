<?php

return [
    'category' => 'content',
    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>file-document-outline</title><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z" /></svg>',
    'title' => __('Content', 'advanced-settings'),
    'description' => __('Content settings', 'advanced-settings'),
    'items' => [
        'content.disable_comments' => [
            'texts' => [
                'title' => __('Disable Comments', 'advanced-settings'),
                'description' => __('Disable comments on the site', 'advanced-settings'),
                'label' => __('Disable comments', 'advanced-settings'),
            ],
            'ui_component' => 'GenericToggle',
            'handler_execute' => function() {
                // TODO: Implement comment disabling
            },
            'handler_validate' => function($value) {
                return is_bool($value);
            },
        ],
    ],
];
