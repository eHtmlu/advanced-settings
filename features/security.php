<?php

return [
    'category' => 'security',
    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
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
