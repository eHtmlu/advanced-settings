<?php
/**
 * Editing Category
 * 
 * Registers the editing category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;



advset_register_feature([
    'id' => 'editing.posts.disable_autosave',
    'category' => 'editing',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Disable auto save', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        define('AUTOSAVE_INTERVAL', 60 * 60 * 24 * 365 * 100); // save interval => 100 years
    },
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'editing.posts.limit_revisions',
    'category' => 'editing',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Limit post revisions', 'advanced-settings'),
                'description' => __('Reduce database size by limiting the number of saved revisions', 'advanced-settings'),
            ],
            'limit' => [
                'type' => 'number',
                'label' => __('Revisions to keep', 'advanced-settings'),
                'description' => __('0 means revisions are disabled.', 'advanced-settings'),
                'min' => 0,
                'default' => 5,
                'visible' => ['enable' => true],
            ],
            'type' => [
                'type' => 'radio',
                'label' => __('Post types', 'advanced-settings'),
                'description' => __('Select the post types that should be affected by the revision limit.', 'advanced-settings'),
                'options' => [
                    'post' => ['label' => 'Posts only'],
                    'page' => ['label' => 'Pages only'],
                    'post_and_page' => ['label' => 'Posts and Pages'],
                    'all' => ['label' => 'All post types (includes custom post types)'],
                ],
                'default' => 'post',
                'visible' => ['enable' => true],
            ],
        ]
    ],
    'handler_cleanup' => function($settings) {
        return empty($settings['enable']) ? null : $settings;
    },
    'execution_handler' => function($settings) {
        if ($settings['type'] === 'all') {
            add_filter('wp_revisions_to_keep', function($num) use($settings) {
                return $settings['limit'];
            });
            return;
        }
        foreach (explode('_and_', $settings['type']) as $type) {
            add_filter('wp_' . $type . '_revisions_to_keep', function($num) use($settings) {
                return $settings['limit'];
            });
        }
    },
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'editing.media.enable_svg',
    'category' => 'editing',
    'ui_config' => fn() => [
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Allow SVG upload for admins', 'advanced-settings'),
                'description' => __('To keep the plugin lightweight, the SVG security checks in this feature are very limited. Therefore, this feature is only available to administrators.', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        if (!current_user_can('administrator')) {
            return;
        }
        
        add_filter('upload_mimes', function($mimes) {
            $mimes['svg'] = 'image/svg+xml';
            $mimes['svgz'] = 'image/svg+xml';
            return $mimes;
        });
        
        add_filter('wp_handle_upload_prefilter', function($file) {
            if ($file['type'] !== 'image/svg+xml') {
                return $file;
            }
            
            $file_content = file_get_contents($file['tmp_name']);

            // 1. Check if the file is empty
            if (empty($file_content)) {
                $file['error'] = __('Empty SVG file', 'advanced-settings');
                return $file;
            }

            // 2. Remove XML declaration and Doctype for better compatibility
            $clean_content = preg_replace('/<\?xml\b[^>]*>\s*/i', '', $file_content);
            $clean_content = preg_replace('/<!DOCTYPE[^>[]*(\[[^]]*\])?[^>]*>\s*/is', '', $clean_content);
            
            // 3. XXE-Protection (critical!)
            $entity_loader_state = libxml_disable_entity_loader(true);
            libxml_use_internal_errors(true);
            libxml_clear_errors();
            
            $svg = @simplexml_load_string($clean_content);
            
            if ($svg === false) {
                $errors = libxml_get_errors();
                $first_error = !empty($errors[0]) ? $errors[0]->message : __('Unknown error', 'advanced-settings');
                libxml_clear_errors();
                /* translators: %s is the first error message from libxml */
                $file['error'] = sprintf(__('Invalid SVG: %s', 'advanced-settings'), esc_html($first_error));
                return $file;
            }

            // 4. Simple, but effective security checks
            $unsafe_patterns = [
                '/<script/i', 
                '/\bon\w+\s*=/i', 
                '/javascript:\s*[a-z]+/i',
                '/<!ENTITY/i',
                '/<object/i',
                '/<iframe/i',
                '/<embed/i',
                '/href\s*=\s*["\']\s*javascript:/i',
                '/xlink:href\s*=\s*["\']\s*javascript:/i',
                '/style\s*=\s*["\'][^"]*expression\s*\(/i',
                '/style\s*=\s*["\'][^"]*url\s*\(\s*javascript:/i',
                '/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is',
            ];
            
            foreach ($unsafe_patterns as $pattern) {
                if (preg_match($pattern, $file_content)) {
                    $file['error'] = __('SVG security check failed: Potential dangerous element detected.', 'advanced-settings') . ' ' . $pattern;
                    return $file;
                }
            }
    
            // 5. Additional protection against Base64-Encoded malicious code
            if (preg_match('/base64\s*[,;]/i', $file_content)) {
                $file['error'] = __('SVG security check failed: Base64 encoding not allowed.', 'advanced-settings');
                return $file;
            }

            // Reset libxml state
            libxml_disable_entity_loader($entity_loader_state);
            libxml_clear_errors();

            return $file;
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
    'priority' => 30,
]);



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
    'priority' => 40,
]);


