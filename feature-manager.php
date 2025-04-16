<?php
/**
 * Feature Registration
 * 
 * Handles the registration of features and categories via hooks
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Global arrays to store features and categories
global $advset_features, $advset_categories;
$advset_features = [];
$advset_categories = [];

/**
 * Register a feature category
 * 
 * @param array $category Category data
 * @return void
 */
function advset_register_category($category) {
    if (!is_array($category) || !isset($category['id'])) {
        return;
    }

    global $advset_categories;

    // Apply filters
    $category = apply_filters('advset_register_category_' . $category['id'], $category);
    $category = apply_filters('advset_register_category', $category);

    // Store category
    $advset_categories[$category['id']] = $category;
}

/**
 * Register a feature
 * 
 * @param array $feature Feature data
 * @return void
 */
function advset_register_feature($feature) {
    if (!is_array($feature) || !isset($feature['id'])) {
        return;
    }

    global $advset_features;

    // Apply filters
    $feature = apply_filters('advset_register_feature_' . $feature['id'], $feature);
    $feature = apply_filters('advset_register_feature', $feature);

    // Store feature
    $advset_features[$feature['id']] = $feature;
}

/**
 * Get all registered categories
 * 
 * @return array
 */
function advset_get_categories() {
    global $advset_categories;
    $categories = apply_filters('advset_categories', $advset_categories);
    
    // Sort categories by priority
    uasort($categories, function($a, $b) {
        $priority_a = isset($a['priority']) ? $a['priority'] : 999;
        $priority_b = isset($b['priority']) ? $b['priority'] : 999;
        return $priority_a - $priority_b;
    });
    
    return $categories;
}

/**
 * Get all registered features
 * 
 * @return array
 */
function advset_get_features() {
    global $advset_features;
    $features = apply_filters('advset_features', $advset_features);
    
    // Sort features by priority
    uasort($features, function($a, $b) {
        $priority_a = isset($a['priority']) ? $a['priority'] : 999;
        $priority_b = isset($b['priority']) ? $b['priority'] : 999;
        return $priority_a - $priority_b;
    });
    
    return $features;
}

/**
 * Get a specific category
 * 
 * @param string $id Category ID
 * @return array|null
 */
function advset_get_category($id) {
    global $advset_categories;
    return isset($advset_categories[$id]) ? $advset_categories[$id] : null;
}

/**
 * Get a specific feature
 * 
 * @param string $id Feature ID
 * @return array|null
 */
function advset_get_feature($id) {
    global $advset_features;
    return isset($advset_features[$id]) ? $advset_features[$id] : null;
}

/**
 * Get features by category
 * 
 * @param string $category_id Category ID
 * @return array
 */
function advset_get_features_by_category($category_id) {
    $features = advset_get_features();
    $category_features = array_filter($features, function($feature) use ($category_id) {
        return isset($feature['category']) && $feature['category'] === $category_id;
    });
    
    return $category_features;
}

/**
 * Load and register default categories and features
 */
function advset_load_features() {
    $features_dir = ADVSET_DIR . '/features';
    foreach (glob($features_dir . '/*.php') as $feature_file) {
        require_once $feature_file;
    }
}

	
// Load features
advset_load_features();

// Register all categories
do_action('advset_register_categories');

// Register all features
do_action('advset_register_features');
