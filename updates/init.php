<?php defined('ABSPATH') or exit;

// Cancel if required variables are not defined
if (!isset($old_version) || !isset($new_version)) {
    return;
}

function advset_updates_execute($previous_installed_version, $current_installed_version) {
    $updates_dir = __DIR__ . '/';
    $files = scandir($updates_dir);
    $update_files = [];
    
    // Collect valid version files
    foreach ($files as $file) {
        if (preg_match('/^(\d+(?:\.\d+)*)\.php$/', $file, $matches)) {
            $update_version = $matches[1];
            $update_files[$update_version] = $file;
        }
    }
    
    // Sort version files by version number
    uksort($update_files, 'version_compare');
    
    // Execute relevant updates
    foreach ($update_files as $update_version => $update_file) {
        if (version_compare($update_version, $previous_installed_version, '>') && version_compare($update_version, $current_installed_version, '<=')) {
            
            (function() use($updates_dir, $update_file, $update_version) {
                require_once $updates_dir . $update_file;
            })();
        }
    }
}

advset_updates_execute($old_version, $new_version);

