<?php

/**
 * This file is a compatibility layer for the old version of the plugin.
 * It will be removed in the future.
 */

add_action('plugins_loaded', function() {
    activate_plugin(plugin_basename(__DIR__ . '/advanced-settings.php'));
    deactivate_plugins(plugin_basename(__FILE__));
});

require_once __DIR__ . '/advanced-settings.php';

