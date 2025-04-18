<?php
/**
 * Categories
 * 
 * Registers the categories
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;


add_action('advset_register_categories', function() {

    advset_register_category([
        'id' => 'admin',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>account</title><path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" /></svg>',
        'title' => __('Admin', 'advanced-settings'),
        'description' => __('Admin settings', 'advanced-settings'),
        'priority' => 10,
    ]);

    advset_register_category([
        'id' => 'advset',
        'icon' => file_get_contents(ADVSET_DIR . '/admin-ui/images/admin-bar-icon.svg'),
        'title' => __('Config', 'advanced-settings'),
        'description' => __('Config', 'advanced-settings'),
        'priority' => 40,
    ]);

    advset_register_category([
        'id' => 'content',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>file-document-outline</title><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z" /></svg>',
        'title' => __('Content', 'advanced-settings'),
        'description' => __('Content settings', 'advanced-settings'),
        'priority' => 20,
    ]);

    advset_register_category([
        'id' => 'security',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>shield-check-outline</title><path d="M21,11C21,16.55 17.16,21.74 12,23C6.84,21.74 3,16.55 3,11V5L12,1L21,5V11M12,21C15.75,20 19,15.54 19,11.22V6.3L12,3.18L5,6.3V11.22C5,15.54 8.25,20 12,21M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9" /></svg>',
        'title' => __('Security', 'advanced-settings'),
        'description' => __('Security settings', 'advanced-settings'),
        'priority' => 30,
    ]);

});
