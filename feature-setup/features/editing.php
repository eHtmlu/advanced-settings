<?php
/**
 * Editing Category
 * 
 * Registers the editing category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;




advset_register_feature([
    'id' => 'editing.image.jpeg_quality',
    'category' => 'editing',
    'ui_config' => fn() => [
        'fields' => [
            'jpeg_quality' => [
                'type' => 'number',
                'label' => __('JPEG Quality', 'advanced-settings'),
                'descriptionHtml' => __('Defines the quality for JPEG images on a scale of 1 to 100 when they are resized.', 'advanced-settings'),
                'min' => 1,
                'max' => 100,
            ],
        ]
    ],
    'handler_cleanup' => function($settings) {
        return empty($settings['jpeg_quality']) ? null : $settings;
    },
    'execution_handler' => function($settings) {
        add_filter('jpeg_quality', function($quality) use($settings) {
            return (int) $settings['jpeg_quality'];
        });
    },
    'priority' => 20,
]);



advset_register_feature([
    'id' => 'editing.image.downsize_on_upload',
    'category' => 'editing',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Downsize images on upload', 'advanced-settings'),
            ],
            'max_width' => [
                'type' => 'number',
                'label' => __('Max width', 'advanced-settings'),
                'description' => __('Empty or 0 means no limit.', 'advanced-settings'),
                'min' => 0,
                'visible' => ['enable' => true],
            ],
            'max_height' => [
                'type' => 'number',
                'label' => __('Max height', 'advanced-settings'),
                'description' => __('Empty or 0 means no limit.', 'advanced-settings'),
                'min' => 0,
                'visible' => ['enable' => true],
            ],
        ]
    ],
    'handler_cleanup' => function($settings) {
        return empty($settings['enable']) ? null : $settings;
    },
    'execution_handler' => function($settings) {
        add_action('wp_handle_upload', function($upload) use($settings) {
            $file_path = $upload['file'];
            $image_info = getimagesize($file_path);
        
            if (!$image_info) return $upload;
        
            list($width, $height, $type) = $image_info;
        
            $max_width  = ((int) $settings['max_width']) ?? null;
            $max_height = ((int) $settings['max_height']) ?? null;
        
            if (($max_width === null || $width <= $max_width) && ($max_height === null || $height <= $max_height)) {
                return $upload; // nothig to do
            }
        
            $editor = wp_get_image_editor($file_path);
            if (is_wp_error($editor)) return $upload;
        
            $editor->resize($max_width, $max_height, false);
            $editor->save($file_path); // overwrites original

            return $upload;
        });
    },
    'priority' => 20,
]);






/* 
advset_register_feature([
    'id' => 'security.test_fields',
    'category' => 'security',
    'ui_config' => fn() => [
        'fields' => [
            'test_email' => [
                'type' => 'email',
                'label' => __('Test email', 'advanced-settings'),
                'descriptionHtml' => __('Enter an email address to test the protection.', 'advanced-settings'),
                'placeholder' => 'test@example.com',
            ],
            'test2' => [
                'type' => 'number',
                'label' => __('Number', 'advanced-settings'),
                'descriptionHtml' => __('Number description', 'advanced-settings'),
            ],
            'test3' => [
                'type' => 'date',
                'label' => __('Date', 'advanced-settings'),
                'descriptionHtml' => __('Date description', 'advanced-settings'),
            ],
            'test4' => [
                'type' => 'time',
                'label' => __('Time', 'advanced-settings'),
                'descriptionHtml' => __('Time description', 'advanced-settings'),
            ],
            'test5' => [
                'type' => 'datetime-local',
                'label' => __('Date and time', 'advanced-settings'),
                'descriptionHtml' => __('Date and time description', 'advanced-settings'),
            ],
            'test6' => [
                'type' => 'month',
                'label' => __('Month', 'advanced-settings'),
                'descriptionHtml' => __('Month description', 'advanced-settings'),
            ],
            'test7' => [
                'type' => 'week',
                'label' => __('Week', 'advanced-settings'),
                'descriptionHtml' => __('Week description', 'advanced-settings'),
            ],
            'test8' => [
                'type' => 'range',
                'label' => __('Range', 'advanced-settings'),
                'descriptionHtml' => __('Range description', 'advanced-settings'),
            ],
            'test9' => [
                'type' => 'color',
                'label' => __('Color', 'advanced-settings'),
                'descriptionHtml' => __('Color description', 'advanced-settings'),
            ],
            'test10' => [
                'type' => 'url',
                'label' => __('URL', 'advanced-settings'),
                'descriptionHtml' => __('URL description', 'advanced-settings'),
            ],
            'test11' => [
                'type' => 'email',
                'label' => __('Email', 'advanced-settings'),
                'descriptionHtml' => __('Email description', 'advanced-settings'),
            ],
            'test12' => [
                'type' => 'password',
                'label' => __('Password', 'advanced-settings'),
                'descriptionHtml' => __('Password description', 'advanced-settings'),
            ],
            'test13' => [
                'type' => 'tel',
                'label' => __('Tel', 'advanced-settings'),
                'descriptionHtml' => __('Tel description', 'advanced-settings'),
            ],
            'test14' => [
                'type' => 'textarea',
                'label' => __('Textarea', 'advanced-settings'),
                'descriptionHtml' => __('Textarea description', 'advanced-settings'),
            ],
            'test15' => [
                'type' => 'checkbox',
                'label' => __('Checkbox', 'advanced-settings'),
                'descriptionHtml' => __('Checkbox description', 'advanced-settings'),
            ],
            'test16' => [
                'type' => 'radio',
                'label' => __('Radio', 'advanced-settings'),
                'descriptionHtml' => __('Radio description', 'advanced-settings'),
                'options' => [
                    'option1' => [
                        'label' => __('Option 1', 'advanced-settings'),
                        'description' => __('Option 1 description', 'advanced-settings'),
                    ],
                    'option2' => [
                        'label' => __('Option 2', 'advanced-settings'),
                        'description' => __('Option 2 description', 'advanced-settings'),
                    ],
                    'option3' => [
                        'label' => __('Option 3', 'advanced-settings'),
                        'description' => __('Option 3 description', 'advanced-settings'),
                    ],
                ],
            ],
            'test17' => [
                'type' => 'select',
                'label' => __('Select', 'advanced-settings'),
                'descriptionHtml' => __('Select description', 'advanced-settings'),
                'options' => [
                    'option1' => [
                        'label' => __('Option 1', 'advanced-settings'),
                    ],
                    'option2' => [
                        'label' => __('Option 2', 'advanced-settings'),
                    ],
                    'option3' => [
                        'label' => __('Option 3', 'advanced-settings'),
                    ],
                ],
            ],
            'test18' => [
                'type' => 'select',
                'label' => __('Select', 'advanced-settings'),
                'descriptionHtml' => __('Select description', 'advanced-settings'),
                'options' => [
                    'option1' => [
                        'label' => __('Option 1', 'advanced-settings'),
                    ],
                    'option2' => [
                        'label' => __('Option 2', 'advanced-settings'),
                    ],
                    'option3' => [
                        'label' => __('Option 3', 'advanced-settings'),
                    ],
                ],
            ],
            'test19' => [
                'type' => 'radio',
                'label' => __('Radio', 'advanced-settings'),
                'descriptionHtml' => __('Radio description', 'advanced-settings'),
                'options' => [
                    'option1' => [
                        'label' => __('Option 1', 'advanced-settings'),
                    ],
                    'option2' => [
                        'label' => __('Option 2', 'advanced-settings'),
                    ],
                    'option3' => [
                        'label' => __('Option 3', 'advanced-settings'),
                    ],
                ],
            ],
            'test20' => [
                'type' => 'info',
                'label' => __('Info', 'advanced-settings'),
                'descriptionHtml' => __('Info description', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        // TODO: Implement email protection
    },
    'priority' => 20,
]);
 */