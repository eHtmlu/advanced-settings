<?php defined('ABSPATH') or exit;

return [

    '2.1' => function() {
        if ($settings=get_option('powerconfigs')) {
            update_option('advset_code', $settings);
            update_option('advset_system', $settings);
            update_option('advset_remove_filters', $settings['remove_filters']);
            delete_option('powerconfigs');
        }
    },

    '2.7.0' => function() {
        if ($post_types_unsanitized=get_option('adv_post_types')) {

            // Fix key issue (keys where not sanitized)
            $post_types_fixed = [];
            foreach ($post_types_unsanitized as $stored_key => $value) {
                $post_types_fixed[sanitize_key( $stored_key )] = $value;
            }
            
            // Rename option to make it consistent to other plugin options
            update_option('advset_post_types', $post_types_fixed);
            delete_option('adv_post_types');
        }
        
        require_once dirname(__DIR__) . '/class.tracksettings.php';
        Advanced_Settings_Track_Settings::get_instance()->send_if_consent_exists();
    },

    '2.8.0' => function() {
        if (($choice = get_option('advset_tracksettings_choice', false)) !== false) {
            update_option('advset_advset', ['advset_tracksettings_choice' => (int) $choice ? '1' : '0']);
            delete_option('advset_tracksettings_choice');

            unset($GLOBALS['advset_options']);
        }
    },

];
