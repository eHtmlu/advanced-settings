<?php

// Exit direct requests
if (!defined('ABSPATH')) exit;

// error_reporting(E_ALL); ini_set('display_errors', 1);

// $tracked = update_option('advset_tracked_scripts', [], true);
global $advset_removed_scripts, $advset_extras;
$advset_removed_scripts = [];
$advset_extras = '';

// track_enqueued_scripts
if( !advset_is_admin_area() ) :

    // url script filter -> add extra data script plugin
    add_filter( 'script_loader_src', function($src) {
        global $advset_removed_scripts;
        $mainsrc = explode('?', $src)[0];
        if (isset($advset_removed_scripts[$mainsrc])) {
            $wp_scripts = wp_scripts();
            $src = '';
            $handle = $advset_removed_scripts[$mainsrc];
            // if (isset($wp_scripts->registered[$handle]->extra) && ($extra = $wp_scripts->registered[$handle]->extra) && isset($extra['data'])) {
            //     echo "\n<script>\n" . $extra['data'] . "\n</script>\n";
            // }
        }
        return $src;
    });

    // track scripts
    if (advset_option('track_enqueued_scripts')) {
        add_filter( 'print_scripts_array', function($scripts) {
            global $advset_removed_scripts;
            $wp_scripts = wp_scripts();
            $tracked = get_option('advset_tracked_scripts', array());
            $queue = empty($wp_scripts->to_do) ? array() : $wp_scripts->to_do;

            // track scripts
            if ($queue) {
                foreach ($queue as $handle) {
                    $src =  $wp_scripts->registered[$handle]->src;
                    if ($handle!=='advset-merged-scripts' && $src && !isset($advset_removed_scripts[$src])) {
                        $tracked[$handle] = $wp_scripts->registered[$handle];
                    }
                }
            }
            update_option('advset_tracked_scripts', $tracked, true);

            return $scripts;
        }, 100000);
    }

    // remove scripts
    add_filter( 'print_scripts_array', function($scripts) {
        global $advset_removed_scripts;
        // global $advset_removed_scripts, $advset_extras;
        $wp_scripts = wp_scripts();
        if ($removed_scripts = get_option('advset_scripts')) {
            foreach ($removed_scripts as $key => $handle) {
                if (strpos($key, 'remove_enqueued_script_')===0) {
                    $src = $wp_scripts->registered[$handle]->src;
                    if (strpos($src, '/')===0) {
                        $src = get_site_url().$src;
                    }
                    $advset_removed_scripts[$src] = 'removed';
                    // if (isset($wp_scripts->registered[$handle]->extra) && isset($wp_scripts->registered[$handle]->extra['data'])) {
                    //     $advset_extras .= $wp_scripts->registered[$handle]->extra['data'];
                    // }
                }
            }
        }
        return $scripts;
    });

    // remove type="text/javascript"
    if( advset_option('remove_script_type') ) {
        add_filter( 'script_loader_tag', function ( $tag, $handle ) {
            return str_replace("<script type='text/javascript' ", '<script ', $tag);
        }, 10, 2 );
    }

    // enqueue merged removed scripts file
    if( advset_option('track_merge_removed_scripts') ) {
        add_action('wp_enqueue_scripts', function() {
            $file = WP_CONTENT_DIR.'/advset-merged-scripts.js';
            if (file_exists($file)) {
                $ver = filemtime($file);
                $deps = array();
                $in_footer = (bool) advset_option('track_merged_scripts_footer');
                wp_enqueue_script('advset-merged-scripts', WP_CONTENT_URL.'/advset-merged-scripts.js?'.$ver, $deps, $ver, $in_footer);
            }
        });
    }

endif;


// scripts admin page save filter
function track_merge_removed_scripts_filter($opt) {

    if (!empty($opt['track_merge_removed_scripts'])) {
        $merge = array();
        $merged_list = '';
        $tracked = get_option('advset_tracked_scripts');

        if ($removed_scripts = $opt) {
            foreach ($removed_scripts as $key => $item) {
                if (strpos($key, 'remove_enqueued_script_')===0) {
                    $merge []= $tracked[$item]->src;
                }
            }

            if ($merge) {

                $file = WP_CONTENT_DIR.'/advset-merged-scripts.js';
                $url = WP_CONTENT_URL.'/advset-merged-scripts.js';

                file_put_contents($file, '/* Advanced Sttings WP Plugin - Merged scripts  */'."\n\n");

                foreach ($merge as $src) {
                    if (strpos($src, '/')===0) {
                        $src = get_site_url().$src;
                    }

                    file_put_contents($file, "/* $src */;\n\n".file_get_contents($src)."\n\n\n", FILE_APPEND);

                    $merged_list .= "<br /> &bull; $src";
                }

                if (!file_exists($file)) {
                    update_option('advset_notice', array(
                        'text' => 'Merge fail! Check your wp-content directory permissions.',
                        'class'=> 'error'
                    ));
                }
                else {
                    $ver = filemtime($file);
                    update_option('advset_notice', array(
                        'text' => "files merged: ",
                        'files'=> "<a href='$url?$ver' target='_blank'>$url</a> $merged_list",
                        'size'=> sizeof($merge),
                        'class'=> 'success'
                    ));
                }
            }
        }
    }

    return $opt;
}
if (is_admin()) {
    add_action( 'init', function () {
        add_filter( 'pre_update_option_advset_scripts', 'track_merge_removed_scripts_filter', 10, 2 );
    });
}
