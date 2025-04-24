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
    // Get features and categories
    $features = advset_get_features();
    $categories = advset_get_categories();

    // Prepare response
    $response = [
        'settings' => get_option('advanced_settings_settings', (object) []),
        'features' => [],
        'categories' => array_values($categories),
    ];

    // Format features for response
    foreach ($features as $id => $feature) {
        $ui_config = isset($feature['ui_config']) ? $feature['ui_config'] : fn() => (object) [];
        
        // ui_config must be a callable
        if (!is_callable($ui_config)) {
            continue;
        }

        $response['features'][] = [
            'id' => $id,
            'category' => $feature['category'],
            'ui_component' => isset($feature['ui_component']) ? $feature['ui_component'] : '',
            'ui_config' => $ui_config(),
            'deprecated' => isset($feature['deprecated']) ? $feature['deprecated'] : null,
            'experimental' => isset($feature['experimental']) ? $feature['experimental'] : null,
        ];
    }

    return new WP_REST_Response($response, 200);
}

/**
 * Save settings
 * 
 * @param WP_REST_Request $request The request object
 * @return WP_REST_Response|WP_Error Response or error
 */
function advset_save_settings_callback($request) {
    $settings = $request->get_param('settings');
    
    // Use the direct save function
    $result = advset_save_settings($settings, true);
    
    // Convert the result to a REST response
    if (is_wp_error($result)) {
        return new WP_Error(
            $result->get_error_code(),
            $result->get_error_message(),
            array_merge(['status' => 400], $result->get_error_data() ?: [])
        );
    }
    
    return new WP_REST_Response([
        'success' => true,
        'message' => $result['changed'] ? 'Settings updated successfully' : 'Settings are unchanged',
        'settings' => (object) $result['settings'],
    ], 200);
}