<?php
/**
 * Feature Registration
 * 
 * Handles the registration of features and categories via hooks
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AdvSet_FeatureManager {

    // Global arrays to store features and categories
    private $features = [];
    private $categories = [];

    private function __construct() {
        // Private constructor to prevent instantiation
    }

    public static function getInstance() {
        static $instance = null;
        if ($instance !== null) return $instance;

        $instance = new self();

        // Load categories and features
        require_once ADVSET_DIR . '/feature-setup/categories.php';
        require_once ADVSET_DIR . '/feature-setup/features.php';

        // Register categories in the init hook at the earliest, as translations must not be loaded earlier.
        if (did_action('init')) {
            do_action('advset_register_categories');
        } else {
            add_action('init', function() {
                do_action('advset_register_categories');
            });
        }

        // Register features immediately because we are already in or after the plugins_loaded hook
        do_action('advset_register_features');

        return $instance;
    }

    /**
     * Register a feature category
     * 
     * @param array $category Category data
     * @return void
     */
    public function register_category($category) {
        if (!is_array($category) || !isset($category['id'])) {
            return;
        }

        // Apply filters
        $category = apply_filters('advset_register_category_' . $category['id'], $category);
        $category = apply_filters('advset_register_category', $category);

        // Store category
        $this->categories[$category['id']] = $category;
    }

    /**
     * Register a feature
     * 
     * @param array $feature Feature data
     * @return void
     */
    public function register_feature($feature) {
        if (!is_array($feature) || !isset($feature['id'])) {
            return;
        }

        // Ensure ui_config is a callable
        if (isset($feature['ui_config']) && !is_callable($feature['ui_config'])) {
            trigger_error(sprintf(
                'Feature "%s": ui_config must be a callable function, as it is used as a callback to avoid premature calls to translation functions.',
                $feature['id']
            ), E_USER_WARNING);
            return;
        }

        // Apply filters
        $feature = apply_filters('advset_register_feature_' . $feature['id'], $feature);
        $feature = apply_filters('advset_register_feature', $feature);

        // Store feature
        $this->features[$feature['id']] = $feature;
    }

    /**
     * Get all registered categories
     * 
     * @return array
     */
    public function get_categories() {
        $categories = apply_filters('advset_categories', $this->categories);
        
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
    public function get_features() {
        $features = apply_filters('advset_features', $this->features);
        
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
    public function get_category($id) {
        return isset($this->categories[$id]) ? $this->categories[$id] : null;
    }

    /**
     * Get a specific feature
     * 
     * @param string $id Feature ID
     * @return array|null
     */
    public function get_feature($id) {
        return isset($this->features[$id]) ? $this->features[$id] : null;
    }

    /**
     * Get features by category
     * 
     * @param string $category_id Category ID
     * @return array
     */
    public function get_features_by_category($category_id) {
        $features = $this->get_features();
        $category_features = array_filter($features, function($feature) use ($category_id) {
            return isset($feature['category']) && $feature['category'] === $category_id;
        });
        
        return $category_features;
    }
}
