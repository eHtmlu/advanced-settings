<?php
/**
 * Advanced Settings API
 * 
 * Provides REST API endpoints for the Advanced Settings plugin
 */

// Exit direct requests
if (!defined('ABSPATH')) exit;

// Register endpoint for loading all feature texts
register_rest_route('advanced-settings/v1', '/features', [
    'methods' => 'GET',
    'callback' => 'advset_get_features_callback',
    'permission_callback' => function() {
        return current_user_can('manage_options');
    }
]);

// Register endpoint for saving settings
register_rest_route('advanced-settings/v1', '/settings', [
    'methods' => 'POST',
    'callback' => 'advset_save_settings_callback',
    'permission_callback' => function() {
        return current_user_can('manage_options');
    },
    'args' => [
        'settings' => [
            'required' => true,
            'type' => 'object',
            'description' => 'Settings to save'
        ]
    ]
]);

/**
 * Get all feature texts from feature files
 * 
 * @return WP_REST_Response Response with features and categories
 */
function advset_get_features_callback() {
    $features = [];
    $categories = [];

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
                $features[] = [
                    'id' => $key,
                    'category' => $feature_data['category'],
                    'title' => $feature['texts']['title'],
                    'description' => $feature['texts']['description'],
                    'label' => isset($feature['texts']['label']) ? $feature['texts']['label'] : '',
                    'ui_component' => isset($feature['ui_component']) ? $feature['ui_component'] : ''
                ];
            }
        }
    }

    return new WP_REST_Response([
        'features' => $features,
        'categories' => $categories
    ], 200);
}

/**
 * Save settings
 * 
 * @param WP_REST_Request $request The request object
 * @return WP_REST_Response|WP_Error Response or error
 */
function advset_save_settings_callback($request) {
    $settings = $request->get_param('settings');
    
    if (!is_array($settings)) {
        return new WP_Error('invalid_settings', 'Settings must be an object', ['status' => 400]);
    }
    
    // Get all feature files
    $features_dir = ADVSET_DIR . '/features';
    $updated = false;
    $errors = [];
    
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
                        $errors[] = "Invalid value for setting: $key";
                        continue;
                    }
                }
                
                // Execute the handler if it exists
                if (isset($feature['handler_execute']) && is_callable($feature['handler_execute'])) {
                    try {
                        call_user_func($feature['handler_execute']);
                        $updated = true;
                    } catch (Exception $e) {
                        $errors[] = "Error executing handler for setting: $key - " . $e->getMessage();
                    }
                }
            }
        }
    }
    
    if (!empty($errors)) {
        return new WP_Error('validation_error', 'Some settings could not be updated', [
            'status' => 400,
            'errors' => $errors
        ]);
    }
    
    if (!$updated) {
        return new WP_Error('no_updates', 'No settings were updated', ['status' => 400]);
    }
    
    return new WP_REST_Response([
        'success' => true,
        'message' => 'Settings updated successfully'
    ], 200);
}