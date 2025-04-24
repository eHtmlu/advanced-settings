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


