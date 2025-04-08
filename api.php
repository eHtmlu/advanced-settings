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