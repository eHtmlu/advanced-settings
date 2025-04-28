<?php

// Exit direct requests
if (!defined('ABSPATH')) exit;

// track_enqueued_styles
if( !advset_is_admin_area() ):

    // track
    if (advset_option('track_enqueued_styles')) {
        add_filter( 'print_styles_array', function($styles) {
            $wp_styles = wp_styles();
            $tracked = get_option('advset_tracked_styles', array());
            $queue = empty($wp_styles->to_do) ? array() : $wp_styles->to_do;

            if ($queue) {
                foreach ($queue as $handle) {
                    if ($handle!=='advset-merged-styles' && $wp_styles->registered[$handle]->src) {
                        $tracked[$handle] = $wp_styles->registered[$handle];
                    }
                }
            }
            $tracked = update_option('advset_tracked_styles', $tracked, true);

            return $styles;
        }, 100000);
    }

    // remove styles
    add_filter( 'print_styles_array', function($styles, $arg2=null) {

        $wp_styles = wp_styles();

        // remove styles
        if ($removed_styles = get_option('advset_styles')) {
            foreach ($removed_styles as $key => $handle) {
                if (strpos($key, 'remove_enqueued_style_')===0) {
                    // unset($styles[array_search($item, $styles)]);
                    $wp_styles->registered[$handle]->src = '';
                }
            }
        }

        return $styles;
    });

    // enqueue style
    if( advset_option('track_merge_removed_styles') ) {
        $file = WP_CONTENT_DIR.'/advset-merged-styles.css';
        if (file_exists($file)) {
            add_action('wp_loaded', function() {
                $deps = array();
                $in_footer = false;
                $ver = filemtime(WP_CONTENT_DIR.'/advset-merged-styles.css');
                wp_enqueue_style('advset-merged-styles', WP_CONTENT_URL.'/advset-merged-styles.css?'.$ver, $deps, $ver, $in_footer);
            });
        }
    }

endif;

// styles admin page save filter
function track_merge_removed_styles_filter($opt) {

    // print_r($opt);
    // die;

    if (!empty($opt['track_merge_removed_styles'])) {
        $merge = array();
        $merged_list = '';
        $tracked = get_option('advset_tracked_styles');

        if ($removed_styles = $opt) {
            foreach ($removed_styles as $key => $item) {
                if (strpos($key, 'remove_enqueued_style_')===0) {
                    $merge []= $tracked[$item]->src;
                }
            }

            if ($merge) {

                $file = WP_CONTENT_DIR.'/advset-merged-styles.css';
                $url = WP_CONTENT_URL.'/advset-merged-styles.css';

                file_put_contents($file, '/* Advanced Sttings WP Plugin - Merged styles  */'."\n\n");

                foreach ($merge as $src) {
                    if (strpos($src, '/')===0) {
                        $src = get_site_url().$src;
                    }

                    // replace urls
                    $css = file_get_contents($src);
                    if ($urls = preg_match_all('/url\([^\)]+\)/', $css, $matches)) {
                        foreach ($matches[0] as $match) {
                            if (!preg_match('/url\([^a-z]*(http|data)/i', $match)) {
                                $newUrl = preg_replace("/(url\(['\"]*)/", "$0".dirname($src).'/', $match);
                                $css = str_replace($match, $newUrl, $css);
                            }
                        }
                    }

                    file_put_contents($file, "/* $src */\n\n".$css."\n\n\n", FILE_APPEND);

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
        add_filter( 'pre_update_option_advset_styles', 'track_merge_removed_styles_filter', 10, 2 );
    });
}
