<?php

return [
    'category' => 'security',
    'title' => __('Security', 'advanced-settings'),
    'description' => __('Security settings', 'advanced-settings'),
    'items' => [
        'security.protect_emails' => [
            'texts' => [
                'title' => __('Protect Emails', 'advanced-settings'),
                'description' => __('Protect email addresses from spam bots.', 'advanced-settings'),
                'label' => __('Protect email addresses from spam bots', 'advanced-settings'),
            ],
            'ui_component' => 'GenericToggle',
            'handler_execute' => function() {
                // TODO: Implement email protection
            },
            'handler_validate' => function($value) {
                return is_bool($value);
            },
        ],
    ],
];
