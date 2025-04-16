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
 * Validate a field value based on its type
 * 
 * @param string $type The field type
 * @param mixed $value The value to validate
 * @param array $config The field configuration
 * @return bool Whether the value is valid
 */
function advset_validate_field_type($type, $value, $config = []) {
    switch ($type) {
        case 'toggle':
        case 'checkbox':
            return is_bool($value);
            
        case 'select':
        case 'radio':
            if (!is_string($value)) {
                return false;
            }
            // Check if value is in allowed options
            if (isset($config['options']) && is_array($config['options'])) {
                return array_key_exists($value, $config['options']);
            }
            return true;
            
        case 'text':
        case 'password':
            if (!is_string($value)) {
                return false;
            }
            if ($value === '') {
                return true;
            }
            // Check pattern if specified
            if (isset($config['pattern']) && is_string($config['pattern'])) {
                return preg_match('/' . $config['pattern'] . '/', $value) === 1;
            }
            return true;
            
        case 'email':
            if (!is_string($value)) {
                return false;
            }
            if ($value === '') {
                return true;
            }
            // Generic email validation (similar to browser)
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            // Check pattern if specified
            if (isset($config['pattern']) && is_string($config['pattern'])) {
                return preg_match('/' . $config['pattern'] . '/', $value) === 1;
            }
            return true;
            
        case 'url':
            if (!is_string($value)) {
                return false;
            }
            if ($value === '') {
                return true;
            }
            // Generic URL validation (similar to browser)
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                return false;
            }
            // Check pattern if specified
            if (isset($config['pattern']) && is_string($config['pattern'])) {
                return preg_match('/' . $config['pattern'] . '/', $value) === 1;
            }
            return true;
            
        case 'tel':
            if (!is_string($value)) {
                return false;
            }
            if ($value === '') {
                return true;
            }
            // Generic telephone validation (similar to browser)
            // Remove all non-digit characters except +, -, (, ), and space
            $cleaned = preg_replace('/[^\d\+\-\(\)\s]/', '', $value);
            if (empty($cleaned)) {
                return false;
            }
            // Check pattern if specified
            if (isset($config['pattern']) && is_string($config['pattern'])) {
                return preg_match('/' . $config['pattern'] . '/', $value) === 1;
            }
            return true;
            
        case 'number':
        case 'range':
            if (!is_numeric($value)) {
                return false;
            }
            if ($value === '') {
                return true;
            }
            // Check min value
            if (isset($config['min']) && is_numeric($config['min']) && $value < $config['min']) {
                return false;
            }
            // Check max value
            if (isset($config['max']) && is_numeric($config['max']) && $value > $config['max']) {
                return false;
            }
            // Check step value
            if (isset($config['step']) && is_numeric($config['step']) && $config['step'] > 0) {
                // Calculate if value is a multiple of step
                $step = $config['step'];
                $min = isset($config['min']) ? $config['min'] : 0;
                $diff = $value - $min;
                if (abs(round($diff / $step) - ($diff / $step)) > 0.000001) {
                    return false;
                }
            }
            return true;
            
        case 'color':
            return is_string($value) && preg_match('/^#[0-9a-f]{6}$/i', $value);
            
        case 'date':
            if (!is_string($value)) {
                return false;
            }
            if ($value === '') {
                return true;
            }
            // Check date format (YYYY-MM-DD)
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return false;
            }
            // Check if date is valid
            $date = DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                return false;
            }
            // Check min date
            if (isset($config['min']) && is_string($config['min'])) {
                $min = DateTime::createFromFormat('Y-m-d', $config['min']);
                if ($min && $date < $min) {
                    return false;
                }
            }
            // Check max date
            if (isset($config['max']) && is_string($config['max'])) {
                $max = DateTime::createFromFormat('Y-m-d', $config['max']);
                if ($max && $date > $max) {
                    return false;
                }
            }
            // Check step (in days)
            if (isset($config['step']) && is_numeric($config['step']) && $config['step'] > 0) {
                $min = isset($config['min']) ? DateTime::createFromFormat('Y-m-d', $config['min']) : $date;
                if ($min) {
                    $diff = $date->diff($min)->days;
                    if (abs(round($diff / $config['step']) - ($diff / $config['step'])) > 0.000001) {
                        return false;
                    }
                }
            }
            return true;
            
        case 'time':
            if (!is_string($value)) {
                return false;
            }
            if ($value === '') {
                return true;
            }
            // Check time format (HH:MM or HH:MM:SS)
            if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
                return false;
            }
            // Check if time is valid
            $time = DateTime::createFromFormat('H:i:s', $value . ':00');
            if (!$time || $time->format('H:i:s') !== $value . ':00') {
                return false;
            }
            // Check min time
            if (isset($config['min']) && is_string($config['min'])) {
                $min = DateTime::createFromFormat('H:i:s', $config['min'] . ':00');
                if ($min && $time < $min) {
                    return false;
                }
            }
            // Check max time
            if (isset($config['max']) && is_string($config['max'])) {
                $max = DateTime::createFromFormat('H:i:s', $config['max'] . ':00');
                if ($max && $time > $max) {
                    return false;
                }
            }
            // Check step (in seconds)
            if (isset($config['step']) && is_numeric($config['step']) && $config['step'] > 0) {
                $min = isset($config['min']) ? DateTime::createFromFormat('H:i:s', $config['min'] . ':00') : $time;
                if ($min) {
                    $diff = $time->getTimestamp() - $min->getTimestamp();
                    if (abs(round($diff / $config['step']) - ($diff / $config['step'])) > 0.000001) {
                        return false;
                    }
                }
            }
            return true;
            
        case 'datetime-local':
            if (!is_string($value)) {
                return false;
            }
            if ($value === '') {
                return true;
            }
            // Check datetime format (YYYY-MM-DDTHH:MM or YYYY-MM-DDTHH:MM:SS)
            if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/', $value)) {
                return false;
            }
            // Check if datetime is valid
            $datetime = DateTime::createFromFormat('Y-m-d\TH:i:s', $value . ':00');
            if (!$datetime || $datetime->format('Y-m-d\TH:i:s') !== $value . ':00') {
                return false;
            }
            // Check min datetime
            if (isset($config['min']) && is_string($config['min'])) {
                $min = DateTime::createFromFormat('Y-m-d\TH:i:s', $config['min'] . ':00');
                if ($min && $datetime < $min) {
                    return false;
                }
            }
            // Check max datetime
            if (isset($config['max']) && is_string($config['max'])) {
                $max = DateTime::createFromFormat('Y-m-d\TH:i:s', $config['max'] . ':00');
                if ($max && $datetime > $max) {
                    return false;
                }
            }
            // Check step (in seconds)
            if (isset($config['step']) && is_numeric($config['step']) && $config['step'] > 0) {
                $min = isset($config['min']) ? DateTime::createFromFormat('Y-m-d\TH:i:s', $config['min'] . ':00') : $datetime;
                if ($min) {
                    $diff = $datetime->getTimestamp() - $min->getTimestamp();
                    if (abs(round($diff / $config['step']) - ($diff / $config['step'])) > 0.000001) {
                        return false;
                    }
                }
            }
            return true;
            
        case 'month':
            if (!is_string($value)) {
                return false;
            }
            if ($value === '') {
                return true;
            }
            // Check month format (YYYY-MM)
            if (!preg_match('/^\d{4}-\d{2}$/', $value)) {
                return false;
            }
            // Check if month is valid
            $month = DateTime::createFromFormat('Y-m', $value);
            if (!$month || $month->format('Y-m') !== $value) {
                return false;
            }
            // Check min month
            if (isset($config['min']) && is_string($config['min'])) {
                $min = DateTime::createFromFormat('Y-m', $config['min']);
                if ($min && $month < $min) {
                    return false;
                }
            }
            // Check max month
            if (isset($config['max']) && is_string($config['max'])) {
                $max = DateTime::createFromFormat('Y-m', $config['max']);
                if ($max && $month > $max) {
                    return false;
                }
            }
            // Check step (in months)
            if (isset($config['step']) && is_numeric($config['step']) && $config['step'] > 0) {
                $min = isset($config['min']) ? DateTime::createFromFormat('Y-m', $config['min']) : $month;
                if ($min) {
                    $diff = ($month->format('Y') * 12 + $month->format('m')) - ($min->format('Y') * 12 + $min->format('m'));
                    if (abs(round($diff / $config['step']) - ($diff / $config['step'])) > 0.000001) {
                        return false;
                    }
                }
            }
            return true;
            
        case 'week':
            if (!is_string($value)) {
                return false;
            }
            if ($value === '') {
                return true;
            }
            // Check week format (YYYY-Www)
            if (!preg_match('/^\d{4}-W\d{2}$/', $value)) {
                return false;
            }
            // Check if week is valid
            $week = DateTime::createFromFormat('o-\WW', $value);
            if (!$week || $week->format('o-\WW') !== $value) {
                return false;
            }
            // Check min week
            if (isset($config['min']) && is_string($config['min'])) {
                $min = DateTime::createFromFormat('o-\WW', $config['min']);
                if ($min && $week < $min) {
                    return false;
                }
            }
            // Check max week
            if (isset($config['max']) && is_string($config['max'])) {
                $max = DateTime::createFromFormat('o-\WW', $config['max']);
                if ($max && $week > $max) {
                    return false;
                }
            }
            // Check step (in weeks)
            if (isset($config['step']) && is_numeric($config['step']) && $config['step'] > 0) {
                $min = isset($config['min']) ? DateTime::createFromFormat('o-\WW', $config['min']) : $week;
                if ($min) {
                    $diff = floor(($week->getTimestamp() - $min->getTimestamp()) / (7 * 24 * 60 * 60));
                    if (abs(round($diff / $config['step']) - ($diff / $config['step'])) > 0.000001) {
                        return false;
                    }
                }
            }
            return true;
            
        default:
            return true; // Unknown types are considered valid
    }
}

/**
 * Get all feature texts from feature files
 * 
 * @return WP_REST_Response Response with features and categories
 */
function advset_get_features_callback() {
    // Initialize categories and features
    advset_init_categories_and_features();

    // Get features and categories
    $features = advset_get_features();
    $categories = advset_get_categories();

    // Prepare response
    $response = [
        'features' => [],
        'categories' => array_values($categories),
    ];

    // Format features for response
    foreach ($features as $id => $feature) {
        $response['features'][] = [
            'id' => $id,
            'category' => $feature['category'],
            'ui_component' => isset($feature['ui_component']) ? $feature['ui_component'] : '',
            'ui_config' => isset($feature['ui_config']) ? $feature['ui_config'] : (object) [],
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
    // Initialize categories and features
    advset_init_categories_and_features();

    $settings = $request->get_param('settings');
    
    if (!is_array($settings)) {
        return new WP_Error('invalid_settings', 'Settings must be an object', ['status' => 400]);
    }
    
    $updated = false;
    $errors = [];
    
    // Get all registered features
    $features = advset_get_features();
    
    foreach ($features as $feature_id => $feature) {
        if (isset($settings[$feature_id])) {
            $value = $settings[$feature_id];
            
            // First validate the field types if ui_config and fields are present
            if (isset($feature['ui_config']['fields']) && is_array($feature['ui_config']['fields'])) {
                // Check if value contains any fields that are not in ui_config
                $invalid_fields = array_diff_key($value, $feature['ui_config']['fields']);
                if (!empty($invalid_fields)) {
                    $errors[] = sprintf(
                        'Invalid fields in setting "%s": %s. Allowed fields: %s',
                        $feature_id,
                        implode(', ', array_keys($invalid_fields)),
                        implode(', ', array_keys($feature['ui_config']['fields']))
                    );
                    continue;
                }

                foreach ($feature['ui_config']['fields'] as $field_id => $field_config) {
                    if (isset($value[$field_id]) && isset($field_config['type'])) {
                        if (!advset_validate_field_type($field_config['type'], $value[$field_id], $field_config)) {
                            $errors[] = sprintf(
                                'Invalid value for field "%s" in setting "%s". Expected type: %s%s%s',
                                $field_id,
                                $feature_id,
                                $field_config['type'],
                                in_array($field_config['type'], ['select', 'radio']) && isset($field_config['options']) 
                                    ? sprintf(' (allowed values: %s)', implode(', ', array_keys($field_config['options'])))
                                    : '',
                                in_array($field_config['type'], ['text', 'email', 'url', 'tel', 'password']) && isset($field_config['pattern'])
                                    ? sprintf(' (must match pattern: %s)', $field_config['pattern'])
                                    : ''
                            );
                            continue 2; // Skip to next setting
                        }
                    }
                }
            }
            
            // Then validate using the feature's own validator if it exists
            if (isset($feature['handler_validate']) && is_callable($feature['handler_validate'])) {
                $is_valid = call_user_func($feature['handler_validate'], $value);
                if (!$is_valid) {
                    $errors[] = "Invalid value for setting: $feature_id";
                    continue;
                }
            }
            
            // Execute the handler if it exists
            if (isset($feature['handler_execute']) && is_callable($feature['handler_execute'])) {
                try {
                    call_user_func($feature['handler_execute']);
                    $updated = true;
                } catch (Exception $e) {
                    $errors[] = "Error executing handler for setting: $feature_id - " . $e->getMessage();
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