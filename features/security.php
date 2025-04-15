<?php

return [
    'category' => 'security',
    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>shield-check-outline</title><path d="M21,11C21,16.55 17.16,21.74 12,23C6.84,21.74 3,16.55 3,11V5L12,1L21,5V11M12,21C15.75,20 19,15.54 19,11.22V6.3L12,3.18L5,6.3V11.22C5,15.54 8.25,20 12,21M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9" /></svg>',
    'title' => __('Security', 'advanced-settings'),
    'description' => __('Security settings', 'advanced-settings'),
    'items' => [
        'security.protect_emails' => [
            'ui_component' => 'generic',
            'ui_config' => [
                'fields' => [
                    'enabled' => [
                        'type' => 'toggle',
                        'label' => __('Protect email addresses from spam bots', 'advanced-settings'),
                        'default' => false
                    ],
                    'method' => [
                        'type' => 'select',
                        'label' => __('Protection method', 'advanced-settings'),
                        'options' => [
                            'entities' => [
                                'label' => __('HTML entities (SEO friendly)', 'advanced-settings'),
                            ],
                            'javascript' => [
                                'label' => __('JavaScript (Better protection)', 'advanced-settings'),
                            ],
                        ],
                        'default' => 'entities',
                        'visible' => ['enabled' => true]
                    ],
                ]
            ],
            'handler_execute' => function() {
                // TODO: Implement email protection
            },
        ],
    ],
];
