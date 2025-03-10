<?php defined('ABSPATH') or exit;


if( $settings=get_option('powerconfigs') ) {
    update_option('advset_code', $settings);
    update_option('advset_system', $settings);
    update_option('advset_remove_filters', $settings['remove_filters']);
    delete_option('powerconfigs');
}

