<?php

return [
    'category' => 'content',
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
