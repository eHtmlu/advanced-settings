<?php

/**
 * This file is a compatibility layer for the old version of the plugin.
 * It will be removed in the future.
 */

add_action('plugins_loaded', function() {
    activate_plugin(plugin_basename(__DIR__ . '/advanced-settings.php'));
    deactivate_plugins(plugin_basename(__FILE__));

	$auto_update_plugins = get_site_option('auto_update_plugins', []);
	if ( in_array('advanced-settings/index.php', $auto_update_plugins) ) {
		$auto_update_plugins[] = 'advanced-settings/advanced-settings.php';
		update_site_option('auto_update_plugins', $auto_update_plugins);
	}
});

require_once __DIR__ . '/advanced-settings.php';

