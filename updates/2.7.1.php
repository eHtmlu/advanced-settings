<?php defined('ABSPATH') or exit;


if (($choice = get_option('advset_tracksettings_choice', false)) !== false) {
    update_option('advset_advset', ['advset_tracksettings_choice' => (int) $choice ? '1' : '0']);
    delete_option('advset_tracksettings_choice');

    unset($GLOBALS['advset_options']);
}
