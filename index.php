<?php
/*
Plugin Name: Advanced Settings
Plugin URI: https://wordpress.org/plugins/advanced-settings/
Description: Advanced settings for WordPress.
Author: Helmut Wandl
Author URI: https://ehtmlu.com/
Version: 2.9.0
Requires at least: 5.0.0
Requires PHP: 5.3
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
	$plugin_data = $plugin_data_loaded ? $plugin_data : get_plugin_data(__FILE__);
    define('ADVSET_VERSION', $plugin_data['Version']);
}

// Define cache file path
define('ADVSET_CACHE_FILE', WP_CONTENT_DIR . '/cache/advanced-settings/active-features.php');







// from https://stevegrunwell.com/blog/quick-tip-is_login_page-function-for-wordpress/
if ( ! function_exists( 'is_admin_area' ) ) {
  function is_admin_area() {
    return is_admin() || in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) );
  }
}




/**
 * Updates verison information in database and checks if a version migration is required
 * 
 * (optimized for high performance)
 */
function advset_check_for_version_migrations() {
    $current_filemtime = filemtime(__FILE__);
    $cache = get_option('advset_version_cache', []);

    if ( isset($cache['version'], $cache['filemtime']) && $cache['filemtime'] === $current_filemtime ) {
        $new_version = $cache['version'];
    } else {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_data = get_plugin_data(__FILE__);
        $new_version = $plugin_data['Version'];

        update_option('advset_version_cache', ['version' => $new_version, 'filemtime' => $current_filemtime], true);

		if (get_option('advset_version__first_install', false) === false) {
			update_option('advset_version__first_install', $new_version);
		}
    }

    $old_version = get_option('advset_version', '1.0.0');

    if (version_compare($old_version, $new_version, '<')) {
		require_once __DIR__ . '/updates/init.php';
        update_option('advset_version', $new_version, true);
    }
}
add_action('init', 'advset_check_for_version_migrations', 1);









/**
 * Register deactivation hook for cleanup
 */
register_deactivation_hook(__FILE__, function() {
    require_once ADVSET_DIR . '/includes/cache-manager.php';
    AdvSet_CacheManager::cleanup_cache();
});







/**
 * Admin UI
 */

// Include admin UI for administrators
function advset_load_admin_ui() {
    if (is_user_logged_in() && current_user_can('manage_options')) {
        require_once ADVSET_DIR . '/admin-ui/admin-ui.php';
    }
}
add_action('init', 'advset_load_admin_ui');






/**
 * Categories and features
 */

// Function to initialize management of categories and features (only when needed)
function advset_init_categories_and_features() {
	require_once ADVSET_DIR . '/includes/feature-manager.php';

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
}






/**
 * API
 */

// Load API endpoints
function advset_load_api_endpoints() {

	// Initialize categories and features
	advset_init_categories_and_features();

	// Regenerate cache file when settings are saved
	add_action('advset_after_save_settings', function() {
		require_once ADVSET_DIR . '/includes/cache-manager.php';
		AdvSet_CacheManager::generate_cache_file();
	});

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
			
			// Extract hash from header
			if (preg_match('/Settings Hash: +([a-f0-9]{32})/i', $header, $matches)) {
				$file_hash = $matches[1];
				
				// Get current settings hash
				$settings = get_option('advanced_settings_settings', []);
				$current_hash = md5(serialize($settings));
				
				// If hash matches, include cache file
				if ($current_hash === $file_hash) {
					if ((@include_once ADVSET_CACHE_FILE) === true) {
						return;
					}
				}
			}
		}
	}

	// If we get here, cache is invalid or missing
	// Load cache manager and try to regenerate/execute
	require_once ADVSET_DIR . '/includes/cache-manager.php';

	// Try to generate cache file
	if (!AdvSet_CacheManager::generate_cache_file() || !(@include_once ADVSET_CACHE_FILE)) {
		// Fallback: Execute features directly
		AdvSet_CacheManager::execute_active_features_fallback();
	}
});






