<?php

// Exit direct requests
if (!defined('ABSPATH')) exit;

// Register new endpoint for loading all feature texts
register_rest_route('advanced-settings/v1', '/features', array(
    'methods' => 'GET',
    'callback' => 'advset_get_features_callback',
    'permission_callback' => function() {
        return current_user_can('manage_options');
    }
));

// Register endpoint for saving settings
register_rest_route('advanced-settings/v1', '/settings', array(
    'methods' => 'POST',
    'callback' => 'advset_save_settings_callback',
    'permission_callback' => function() {
        return current_user_can('manage_options');
    },
    'args' => array(
        'settings' => array(
            'required' => true,
            'type' => 'object',
            'description' => 'Settings to save'
        )
    )
));

/**
 * Get all feature texts from feature files
 */
function advset_get_features_callback() {
    $features = array();
    $categories = array();

    $features_dir = ADVSET_DIR . '/features';

    foreach (glob($features_dir . '/*.php') as $feature_file) {
        $feature_data = include $feature_file;

        if (!is_array($feature_data) || !isset($feature_data['category']) || !isset($feature_data['items'])) {
            continue;
        }

        $categories[$feature_data['category']] = [
            'id' => $feature_data['category'],
            'title' => $feature_data['title'],
            'description' => $feature_data['description'],
        ];

        foreach ($feature_data['items'] as $key => $feature) {
            if (isset($feature['texts'])) {
                $features[] = array(
                    'id' => $key,
                    'category' => $feature_data['category'],
                    'title' => $feature['texts']['title'],
                    'description' => $feature['texts']['description'],
                    'label' => isset($feature['texts']['label']) ? $feature['texts']['label'] : '',
                    'ui_component' => isset($feature['ui_component']) ? $feature['ui_component'] : ''
                );
            }
        }
    }

    return new WP_REST_Response(array(
        'features' => $features,
        'categories' => $categories
    ), 200);
}

/**
 * Save settings
 */
function advset_save_settings_callback($request) {
    $settings = $request->get_param('settings');
    
    if (!is_array($settings)) {
        return new WP_Error('invalid_settings', 'Settings must be an object', array('status' => 400));
    }
    
    // Get all feature files
    $features_dir = ADVSET_DIR . '/features';
    $updated = false;
    
    foreach (glob($features_dir . '/*.php') as $feature_file) {
        $feature_data = include $feature_file;
        
        if (!is_array($feature_data) || !isset($feature_data['items'])) {
            continue;
        }
        
        foreach ($feature_data['items'] as $key => $feature) {
            if (isset($settings[$key])) {
                $value = $settings[$key];
                
                // Validate the value if a validator exists
                if (isset($feature['handler_validate']) && is_callable($feature['handler_validate'])) {
                    $is_valid = call_user_func($feature['handler_validate'], $value);
                    if (!$is_valid) {
                        return new WP_Error('invalid_value', "Invalid value for setting: $key", array('status' => 400));
                    }
                }
                
                // Execute the handler if it exists
                if (isset($feature['handler_execute']) && is_callable($feature['handler_execute'])) {
                    call_user_func($feature['handler_execute']);
                    $updated = true;
                }
            }
        }
    }
    
    if (!$updated) {
        return new WP_Error('no_updates', 'No settings were updated', array('status' => 400));
    }
    
    return new WP_REST_Response(array(
        'success' => true,
        'message' => 'Settings updated successfully'
    ), 200);
}