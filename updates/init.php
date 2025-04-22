<?php defined('ABSPATH') or exit;

// Cancel if required variables are not defined
if (!isset($old_version) || !defined('ADVSET_VERSION')) {
    return;
}

function advset_updates_execute($previous_installed_version, $current_installed_version) {
    $updates_dir = __DIR__ . '/';
    $files = scandir($updates_dir);
    $update_files = [];

    // Collect valid version files
    foreach ($files as $file) {
        if (preg_match('/^(\d+)\.x\.x\.php$/', $file, $matches)) {
            $major_version = $matches[1];
            $update_files[$major_version] = $file;
        }
    }
    
    // Sort major version files by version number
    ksort($update_files, SORT_NUMERIC);

    // Execute relevant updates
    foreach ($update_files as $file) {
        $updates = require_once $updates_dir . $file;
        if (!is_array($updates)) {
            continue;
        }

        uksort($updates, 'version_compare');

        foreach ($updates as $update_version => $update_function) {
            if (
                version_compare($update_version, $previous_installed_version, '>') &&
                version_compare($update_version, $current_installed_version, '<=')
            ) {
                try {
                    $update_function();
                } catch (Exception $e) {
                    $error_message = "Update failed for version $update_version: " . $e->getMessage();
                    
                    // Log error to PHP log
                    error_log($error_message);
                    
                    // If WP_DEBUG_LOG is enabled, log to WordPress debug log as well
                    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                        error_log($error_message, 3, WP_CONTENT_DIR . '/debug.log');
                    }
                    
                    // If in admin area, show a detailed error message
                    if (is_admin()) {
                        wp_die("Plugin update error: <pre>{$error_message}</pre>");
                    }
                    
                    // Do not expose details on frontend
                    return;
                }
            }
        }
    }
}

advset_updates_execute($old_version, ADVSET_VERSION);
