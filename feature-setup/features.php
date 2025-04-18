<?php
/**
 * Features
 * 
 * Registers the features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;


add_action('advset_register_features', function() {

    $features_dir = ADVSET_DIR . '/feature-setup/features';
    foreach (glob($features_dir . '/*.php') as $feature_file) {
        require_once $feature_file;
    }

});
