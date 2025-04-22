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

define('ADVSET_DIR', dirname(__FILE__));

# THE ADMIN PAGE
function advset_page() {
	switch (isset($_GET['tab']) ? $_GET['tab'] : null) {
		case 'admin-advset': include ADVSET_DIR.'/admin-advset.php'; break;
		case 'admin-code': include ADVSET_DIR.'/admin-code.php'; break;
		case 'admin-get-in-touch': include ADVSET_DIR.'/admin-get-in-touch.php'; break;
		case 'admin-system':
		default: include ADVSET_DIR.'/admin-system.php'; break;
	}
}



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




if( is_admin() ) {

	define('ADVSET_URL', 'https://wordpress.org/plugins/advanced-settings/');

	# Admin menu
	add_action('admin_menu', 'advset_menu');

	# Add plugin option in Plugins page
	add_filter( 'plugin_action_links', 'advset_plugin_action_links', 10, 2 );

	// Settings tracking
	require_once __DIR__ . '/class.tracksettings.php';
	Advanced_Settings_Track_Settings::get_instance();

	// update settings
	if( isset($_POST['option_page']) && $_POST['option_page']=='advanced-settings' ) {

		function advset_update() {

			// security
			if( !current_user_can('manage_options') )
				return;

			// define option name
			$setup_name = 'advset_'.$_POST['advset_group'];

			// prepare option group
			$_POST[$setup_name] = $_POST;

			unset(
				$_POST[$setup_name]['option_page'],
				$_POST[$setup_name]['action'],
				$_POST[$setup_name]['_wpnonce'],
				$_POST[$setup_name]['_wp_http_referer'],
				$_POST[$setup_name]['submit']
			);

			if( !empty($_POST[$setup_name]['auto_thumbs']) )
				$_POST[$setup_name]['add_thumbs'] = '1';

			if( !empty($_POST[$setup_name]['remove_widget_system']) )
				$_POST[$setup_name]['remove_default_wp_widgets'] = '1';

			if( isset($_POST[$setup_name]['advset_tracksettings_choice']) && $_POST[$setup_name]['advset_tracksettings_choice'] === '' )
				unset($_POST[$setup_name]['advset_tracksettings_choice']);

			// save settings
			register_setting( 'advanced-settings', $setup_name );

		}
		add_action( 'admin_init', 'advset_update' );
	}

}

// get a advanced-settings option
function advset_option( $option_name, $default='' ) {
	global $advset_options;

	if( !isset($advset_options) )
		$advset_options = get_option('advset_advset', array()) + get_option('advset_code', array()) + get_option('advset_system', array()) + get_option('advset_scripts', array()) + get_option('advset_styles', array());

	if( isset($advset_options[$option_name]) )
		return $advset_options[$option_name];
	else
		return $default;
}

function advset_check_if( $option_name, $echo=true ) {
	if ( advset_option( $option_name, 0 ) ) {
		if ($echo) {
			echo ' checked="checked"';
		}
		else {
			return ' checked="checked"';
		}
	}
}

# ADMIN MENU
function advset_menu() {
	$title = __('Advanced') . ' …';
	add_options_page($title, $title, 'manage_options', 'advanced-settings', 'advset_page');
}

# ADMIN PAGE TABS
function advset_page_header() {
	$active_tab = isset($_GET['tab']) ? $_GET['tab'] : '';
	?>
	<style>
		.expert-setting {
			color: #c60;
		}
		.heart {
			font-size: 2rem;
			display: inline-block;
			animation: heartbeat 1.5s ease-in-out infinite;
		}
		@keyframes heartbeat {
			0% {
				transform: scale(1);
			}
			15% {
				transform: scale(1.15);
			}
			30% {
				transform: scale(1);
			}
			45% {
				transform: scale(1.1);
			}
			60% {
				transform: scale(1);
			}
			100% {
				transform: scale(1);
			}
		}
	</style>
	<div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 2rem; align-items: flex-start; ">
		<div style="flex-grow: 1; ">
			<h1><?php _e('Settings'); echo ' &rsaquo; '; _e('Advanced'); ?></h1>
		</div>
		<div style="border: #3c3 solid 2px; background: #fff; padding: 1rem; border-radius: .5rem; display: flex; gap: 1rem; font-size: 1rem; line-height: 1.4; "><span class="heart">💚</span><span>This plugin is currently being extensively revised.<br />If you have any questions or wishes, just <a href="?page=advanced-settings&tab=admin-get-in-touch">get in touch</a>.</span></div>
	</div>
	<nav class="nav-tab-wrapper">
		<a href="?page=advanced-settings" class="nav-tab <?php echo $active_tab === '' ? 'nav-tab-active' : ''; ?>"><?php echo __('System') ?></a>
		<a href="?page=advanced-settings&tab=admin-code" class="nav-tab <?php echo $active_tab === 'admin-code' ? 'nav-tab-active' : ''; ?>"><?php echo __('HTML Code') ?></a>
		<a style="float: right; " href="?page=advanced-settings&tab=admin-advset" class="nav-tab <?php echo $active_tab === 'admin-advset' ? 'nav-tab-active' : ''; ?>"><?php echo __('Config') ?></a>
	</nav>
	<style>

		.deprecated {
			background: #900;
			color: #fff;
			padding: 0 .5rem;
			display: inline-block;
			border-radius: 3px;
		}

		.experimental {
			background: #39f;
			color: #fff;
			padding: 0 .5rem;
			display: inline-block;
			border-radius: 3px;
			font-size: 14px;
			line-height: 1.4;
		}

	</style>
	<?php
}

function advset_page_deprecated() {
	return '<br />&nbsp; &nbsp; &nbsp; <strong class="deprecated">' . __('DEPRECATED') . '</strong> <span style="color: #900; ">' . __('This option will be removed in an upcoming version.') . '</span>';
}

function advset_page_experimental() {
	return ' <strong class="experimental">' . __('EXPERIMENTAL') . '</strong>';
}

# Add plugin option in Plugins page
function advset_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( basename(dirname(__FILE__)).'/index.php' ) ) {
		$links[] = '<a href="options-general.php?page=advanced-settings">'.__('Settings').'</a>';
	}

	return $links;
}


















/**
 * Define constants
 */

// Define plugin version if not already defined
if (!defined('ADVSET_VERSION')) {
	$plugin_data_loaded = isset($plugin_data['Version'], $plugin_data['TextDomain']) && $plugin_data['TextDomain'] === 'advanced-settings';
	$plugin_data = $plugin_data_loaded ? $plugin_data : get_plugin_data(__FILE__);
    define('ADVSET_VERSION', $plugin_data['Version']);
}

// Define cache file path
define('ADVSET_CACHE_FILE', WP_CONTENT_DIR . '/cache/advanced-settings/active-features.php');




/**
 * Register deactivation hook for cleanup
 */
register_deactivation_hook(__FILE__, function() {
    require_once ADVSET_DIR . '/cache-manager.php';
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
	require_once ADVSET_DIR . '/feature-manager.php';

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
		require_once ADVSET_DIR . '/cache-manager.php';
		AdvSet_CacheManager::generate_cache_file();
	});

	require_once ADVSET_DIR . '/api-endpoints.php';
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
	require_once ADVSET_DIR . '/cache-manager.php';

	// Try to generate cache file
	if (!AdvSet_CacheManager::generate_cache_file() || !(@include_once ADVSET_CACHE_FILE)) {
		// Fallback: Execute features directly
		AdvSet_CacheManager::execute_active_features_fallback();
	}
});






