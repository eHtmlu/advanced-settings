<?php




if( is_admin() ) {

    // Settings tracking
    //require_once __DIR__ . '/includes/class.tracksettings.php';
    //Advanced_Settings_Track_Settings::get_instance();

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



# ADMIN PAGE TABS
function advset_page_header() {
    ?>
    <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 2rem; align-items: flex-start; ">
        <div style="flex-grow: 1; ">
            <h1><?php _e('Advanced Settings'); echo ' &rsaquo; ' . get_admin_page_title(); ?></h1>
        </div>
    </div>
    <p>
        <?php echo sprintf(__('This settings page is part of the Advanced Settings plugin.<br />This page can be deactivated in the <a%s>general settings</a>.', 'advanced-settings'), ' href="javascript:void(0);" onclick="advset_open_modal(); return false;"'); ?>
    </p>
    <style>

        .expert-setting {
            color: #c60;
        }

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




