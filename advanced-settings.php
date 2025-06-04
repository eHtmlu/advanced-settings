<?php
/*
Plugin Name: Advanced Settings
Plugin URI: https://wordpress.org/plugins/advanced-settings/
Description: Offers settings that you might expect to find in the WordPress core.
Author: Helmut Wandl
Author URI: https://ehtmlu.com/
Version: 3.0.2
Requires at least: 5.0.0
Requires PHP: 7.4
Text Domain: advanced-settings
Domain Path: /languages
*/

// Exit direct requests
if (!defined('ABSPATH')) exit;





/**
 * Define constants
 */

define('ADVSET_DIR', dirname(__FILE__));
define('ADVSET_FILE', __FILE__);

// Define plugin version if not already defined
if (!defined('ADVSET_VERSION')) {
    $plugin_data_loaded = isset($plugin_data['Version'], $plugin_data['TextDomain']) && $plugin_data['TextDomain'] === 'advanced-settings';
	
    // The get_plugin_data() function is only automatically available here from WordPress version 6.8, so we have to load it manually for older versions.
    if (!function_exists('get_plugin_data')) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

    $plugin_data = $plugin_data_loaded ? $plugin_data : get_plugin_data(__FILE__);
    define('ADVSET_VERSION', $plugin_data['Version']);
}

// Define cache file path
define('ADVSET_CACHE_FILE', WP_CONTENT_DIR . '/cache/advanced-settings/active-features.php');





/**
 * Updates version information in database and checks if a version migration is required
 */
function advset_check_for_version_migrations() {
    $old_version = get_option('advset_version', '1.0.0');

    if (version_compare($old_version, ADVSET_VERSION, '<')) {
        require_once __DIR__ . '/migrations/init.php';
        update_option('advset_version', ADVSET_VERSION, true);

        if (get_option('advset_version__first_install', false) === false) {
            update_option('advset_version__first_install', ADVSET_VERSION);
        }
    }
}
add_action('plugins_loaded', 'advset_check_for_version_migrations', 10);





/**
 * Register deactivation hook for cleanup
 */
register_deactivation_hook(__FILE__, function() {
    delete_option('advset_guide_shown');
    advset_cleanup_cache();
});





/**
 * Admin UI
 */

// Include admin UI for administrators
function advset_load_admin_ui() {
    if (current_user_can('manage_options')) {
        require_once ADVSET_DIR . '/admin-ui/admin-ui.php';
    }
}
add_action('init', 'advset_load_admin_ui');





/**
 * API
 */

// Load API endpoints
function advset_load_api_endpoints() {
    require_once ADVSET_DIR . '/includes/api-endpoints.php';
}
add_action('rest_api_init', 'advset_load_api_endpoints');





/**
 * Execute active features
 */

add_action('plugins_loaded', function() {
    // Try to load and execute cached features directly
    if (file_exists(ADVSET_CACHE_FILE)) {
        // Read first few lines to check validity
        $handle = @fopen(ADVSET_CACHE_FILE, 'r');
        if ($handle) {
            $header = '';
            for ($i = 0; $i < 20; $i++) {
                $line = fgets($handle);
                if ($line === false) break;
                $header .= $line;
            }
            fclose($handle);
            
            // Extract hash and version from header
            $is_valid = true;
            
            // Check settings hash
            if (preg_match('/Settings Hash: +([a-f0-9]{32})/i', $header, $matches)) {
                $file_hash = $matches[1];
                $settings = get_option('advanced_settings_settings', []);
                $current_hash = md5(serialize($settings));
                if ($current_hash !== $file_hash) {
                    $is_valid = false;
                }
            } else {
                $is_valid = false;
            }
            
            // Check plugin version
            if (preg_match('/Version: +([0-9.]+)/i', $header, $matches)) {
                $file_version = $matches[1];
                if (version_compare($file_version, ADVSET_VERSION, '!=')) {
                    $is_valid = false;
                }
            } else {
                $is_valid = false;
            }
            
            // If everything is valid, include cache file
            if ($is_valid && (@include_once ADVSET_CACHE_FILE) === true) {
                return;
            }
        }
    }

    // Try to generate cache file
    if (!advset_cache_manager()->generate_cache_file() || !(@include_once ADVSET_CACHE_FILE)) {
        // Fallback: Execute features directly
        advset_cache_manager()->execute_active_features_fallback();
    }
});





/**
 * Helper functions
 */

// Check if we are in the admin area or on the login page
function advset_is_admin_area() {
    return is_admin() || in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) );
}

// Get settings
function advset_settings($id = null, $default = null) {
    $settings = get_option('advanced_settings_settings', []);
    return $id ? (isset($settings[$id]) ? $settings[$id] : $default) : $settings;
}

// Check if deprecated features are enabled
function advset_show_deprecated_features() {
    $show_deprecated = advset_settings('advset.features.show_deprecated', false);
    return $show_deprecated && !empty($show_deprecated['enable']);
}

function advset_cache_manager() {
    require_once ADVSET_DIR . '/includes/class.cache-manager.php';
    return AdvSet_CacheManager::getInstance();
}

function advset_cleanup_cache() {
    return advset_cache_manager()->cleanup_cache();
}

function advset_settings_manager() {
    require_once ADVSET_DIR . '/includes/class.settings-manager.php';
    return AdvSet_SettingsManager::getInstance();
}

function advset_save_settings($settings, $return_change_status = false) {
    return advset_settings_manager()->save_settings($settings, $return_change_status);
}

function advset_feature_manager() {
    require_once ADVSET_DIR . '/includes/class.feature-manager.php';
    return AdvSet_FeatureManager::getInstance();
}

function advset_register_category($category) {
    return advset_feature_manager()->register_category($category);
}

function advset_register_feature($feature) {
    return advset_feature_manager()->register_feature($feature);
}

function advset_get_categories() {
    return advset_feature_manager()->get_categories();
}

function advset_get_features() {
    return advset_feature_manager()->get_features();
}

function advset_get_category($id) {
    return advset_feature_manager()->get_category($id);
}

function advset_get_feature($id) {
    return advset_feature_manager()->get_feature($id);
}

function advset_get_features_by_category($category_id) {
    return advset_feature_manager()->get_features_by_category($category_id);
}




