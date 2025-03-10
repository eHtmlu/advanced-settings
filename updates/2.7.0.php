<?php defined('ABSPATH') or exit;


if( $post_types_unsanitized=get_option('adv_post_types') ) {

    // Fix key issue (keys where not sanitized)
    $post_types_fixed = [];
    foreach ($post_types_unsanitized as $stored_key => $value) {
        $post_types_fixed[sanitize_key( $stored_key )] = $value;
    }
    
    // Rename option to make it consistent to other plugin options
    update_option('advset_post_types', $post_types_fixed);
    delete_option('adv_post_types');
}

