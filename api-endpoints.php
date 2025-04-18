<?php
/**
 * Advanced Settings API
 * 
 * Provides REST API endpoints for the Advanced Settings plugin
 */

// Exit direct requests
if (!defined('ABSPATH')) exit;


// Initialize categories and features
advset_init_categories_and_features();

// Regenerate cache file when settings are saved
add_action('advset_after_save_settings', function() {
    require_once ADVSET_DIR . '/cache-manager.php';
    AdvSet_CacheManager::generate_cache_file();
});



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
            if ($value === '') {
                return true;
            }
            if (!is_numeric($value)) {
                return false;
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
            if (!preg_match('/^(\d{4})-W(\d{2})$/', $value, $matches)) {
                return false;
            }
            
            $year = intval($matches[1]);
            $week = intval($matches[2]);
            
            // Check if week number is valid (1-53)
            if ($week < 1 || $week > 53) {
                return false;
            }
            
            // Calculate first day of the year
            $firstDayOfYear = new DateTime($year . '-01-01');
            $firstWeekday = intval($firstDayOfYear->format('N'));
            
            // Calculate first week of the year based on ISO-8601
            // If Jan 1 is Fri, Sat or Sun, first week starts next Monday
            $daysToAdd = (11 - $firstWeekday) % 7;
            $firstWeek = clone $firstDayOfYear;
            $firstWeek->modify("+$daysToAdd days");
            
            // Calculate max weeks in this year
            $maxWeeks = ($firstWeekday == 4 || ($firstWeekday == 3 && date('L', strtotime("$year-01-01")) == 1)) ? 53 : 52;
            if ($week > $maxWeeks) {
                return false;
            }
            
            // Calculate the date of the requested week
            $requestedDate = clone $firstWeek;
            $requestedDate->modify("+" . ($week - 1) * 7 . " days");
            
            // Check min week
            if (isset($config['min']) && is_string($config['min'])) {
                if (preg_match('/^(\d{4})-W(\d{2})$/', $config['min'], $minMatches)) {
                    $minYear = intval($minMatches[1]);
                    $minWeek = intval($minMatches[2]);
                    
                    if ($year < $minYear || ($year === $minYear && $week < $minWeek)) {
                        return false;
                    }
                }
            }
            
            // Check max week
            if (isset($config['max']) && is_string($config['max'])) {
                if (preg_match('/^(\d{4})-W(\d{2})$/', $config['max'], $maxMatches)) {
                    $maxYear = intval($maxMatches[1]);
                    $maxWeek = intval($maxMatches[2]);
                    
                    if ($year > $maxYear || ($year === $maxYear && $week > $maxWeek)) {
                        return false;
                    }
                }
            }
            
            // Check step (in weeks)
            if (isset($config['step']) && is_numeric($config['step']) && $config['step'] > 0) {
                if (isset($config['min']) && preg_match('/^(\d{4})-W(\d{2})$/', $config['min'], $minMatches)) {
                    $minYear = intval($minMatches[1]);
                    $minWeek = intval($minMatches[2]);
                    
                    // Calculate total weeks difference
                    $weeksDiff = ($year - $minYear) * 52 + ($week - $minWeek);
                    
                    if (abs(round($weeksDiff / $config['step']) - ($weeksDiff / $config['step'])) > 0.000001) {
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
        ];
    }

    return new WP_REST_Response($response, 200);
}

/**
 * Check if a value should be considered empty/default
 * 
 * @param mixed $value The value to check
 * @param array $field_config The field configuration
 * @return bool Whether the value should be considered empty/default
 */
function advset_is_empty_or_default_value($value, $field_config) {
    // If no field config is provided, only check for null/empty string
    if (!is_array($field_config)) {
        return $value === null || $value === '';
    }
    
    // If a default is set, we only check if the value equals the default
    if (isset($field_config['default'])) {
        return $value === $field_config['default'];
    }
    
    // If no default is set, we check for empty values based on type
    switch ($field_config['type']) {
        case 'toggle':
        case 'checkbox':
            return $value === false;
            
        case 'text':
        case 'email':
        case 'url':
        case 'tel':
        case 'password':
        case 'color':
        case 'date':
        case 'time':
        case 'datetime-local':
        case 'month':
        case 'week':
            return $value === '';
            
        case 'number':
        case 'range':
            return $value === '' || $value === null || $value === 0;
            
        case 'select':
        case 'radio':
            return $value === '' || $value === null;
            
        default:
            // For unknown types, only check for null/empty string
            return $value === null || $value === '';
    }
}

/**
 * Clean up a feature value
 * 
 * @param mixed $value The value to clean up
 * @param array $feature The feature configuration
 * @return mixed|null The cleaned value or null if it should be removed
 */
function advset_cleanup_feature_value($value, $feature) {
    // First perform automatic cleanup
    $cleaned_value = $value;
    
    // Get ui_config
    $ui_config = isset($feature['ui_config']) ? $feature['ui_config'] : fn() => [];
    
    // ui_config must be a callable
    if (!is_callable($ui_config)) {
        return $value;
    }
    
    $ui_config = $ui_config();
    
    // If value is an array/object, check each field
    if (is_array($value) && isset($ui_config['fields'])) {
        $cleaned_value = [];
        
        foreach ($value as $field_id => $field_value) {
            // Skip if field doesn't exist in config
            if (!isset($ui_config['fields'][$field_id])) {
                continue;
            }
            
            // Only keep non-empty and non-default values
            if (!advset_is_empty_or_default_value($field_value, $ui_config['fields'][$field_id])) {
                $cleaned_value[$field_id] = $field_value;
            }
        }
        
        // Set to null if all fields were empty/default
        $cleaned_value = !empty($cleaned_value) ? $cleaned_value : null;
    } else {
        // For simple values, check if empty/default
        $cleaned_value = advset_is_empty_or_default_value($value, $ui_config) ? null : $value;
    }
    
    // Then apply custom cleanup handler if present
    if (isset($feature['handler_cleanup']) && is_callable($feature['handler_cleanup'])) {
        $cleaned_value = call_user_func($feature['handler_cleanup'], $cleaned_value);
    }
    
    return $cleaned_value;
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
    
    $errors = [];

    // Get all registered features
    $features = advset_get_features();
    
    // Get current settings
    $current_settings = get_option('advanced_settings_settings', []);
    
    // Create new settings array
    $new_settings = $current_settings;
    
    // Allow plugins to modify settings before save
    $settings = apply_filters('advset_before_save_settings', $settings, $current_settings);
    
    foreach ($features as $feature_id => $feature) {
        if (isset($settings[$feature_id])) {
            $value = $settings[$feature_id];
            
            // Get ui_config
            $ui_config = isset($feature['ui_config']) ? $feature['ui_config'] : fn() => [];
            
            // ui_config must be a callable
            if (!is_callable($ui_config)) {
                continue;
            }
            
            $ui_config = $ui_config();
            
            // First validate the field types if ui_config and fields are present
            if (isset($ui_config['fields']) && is_array($ui_config['fields'])) {
                // Check if value contains any fields that are not in ui_config
                $invalid_fields = array_diff_key($value, $ui_config['fields']);
                if (!empty($invalid_fields)) {
                    $errors[] = sprintf(
                        'Invalid fields in setting "%s": %s. Allowed fields: %s',
                        $feature_id,
                        implode(', ', array_keys($invalid_fields)),
                        implode(', ', array_keys($ui_config['fields']))
                    );
                    continue;
                }

                foreach ($ui_config['fields'] as $field_id => $field_config) {
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
            
            // Clean up the value
            $cleaned_value = advset_cleanup_feature_value($value, $feature);
            
            // If value is null after cleanup, remove it from settings
            if ($cleaned_value === null) {
                unset($new_settings[$feature_id]);
            } else {
                $new_settings[$feature_id] = $cleaned_value;
            }
        }
    }
    
    if (!empty($errors)) {
        return new WP_Error('validation_error', 'Some settings could not be updated', [
            'status' => 400,
            'errors' => $errors
        ]);
    }
    
    // Check if settings have actually changed
    if (serialize($new_settings) === serialize($current_settings)) {
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Settings are unchanged',
            'settings' => (object) $new_settings
        ], 200);
    }
    
    // Try to save the settings
    $updated = update_option('advanced_settings_settings', $new_settings);
    
    if (!$updated) {
        return new WP_Error('save_failed', 'Failed to save settings', ['status' => 500]);
    }
    
    // Allow plugins to react to saved settings
    do_action('advset_after_save_settings', $new_settings, $current_settings);
    
    return new WP_REST_Response([
        'success' => true,
        'message' => 'Settings updated successfully',
        'settings' => (object) $new_settings
    ], 200);
}