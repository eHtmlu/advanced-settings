<?php defined('ABSPATH') or exit;

return [

    '3.0.0' => function() {

        $advset_options = 
            get_option('advset_advset', []) +
            get_option('advset_code', []) +
            get_option('advset_system', [])
        ;

        $old_settings = $advset_options;
        $new_settings = [];

        $feature_transformations = [
            // system
            'hide_update_message' => ['dashboard.notices.remove_update_nag'],
            'dashboard_logo' => ['dashboard.adminbar.custom_logo', function($value) {
                return array_filter(['url' => empty($value) ? null : (string) $value]);
            }],
            'remove_comments_system' => ['system.comments.disable_system'],
            'disable_auto_save' => ['system.posts.disable_autosave'],
            'disable_author_pages' => ['frontend.author.disable_pages'],
            'auto_thumbs' => ['frontend.thumbnails.auto_from_first_image'],
            'jpeg_quality' => ['editing.image.jpeg_quality', function($value) {
                return array_filter(['jpeg_quality' => empty($value) ? null : (string) $value]);
            }],
            'max_image_size_w' => ['editing.image.downsize_on_upload', function($value) use($advset_options) {
                $w = $value;
                $h = $advset_options['max_image_size_h'];
                return array_filter(['enable' => true, 'max_width' => empty($w) ? null : (string) $w, 'max_height' => empty($h) ? null : (string) $h]);
            }],
            'max_image_size_h' => ['editing.image.downsize_on_upload', function($value) use($advset_options) {
                $w = $advset_options['max_image_size_w'];
                $h = $value;
                return array_filter(['enable' => true, 'max_width' => empty($w) ? null : (string) $w, 'max_height' => empty($h) ? null : (string) $h]);
            }],
            'show_query_num' => ['developer.debug.show_queries'],
            'core_upgrade_skip_new_bundled' => ['system.updates.skip_bundled_themes'],
            'prevent_auto_core_update_send_email' => ['system.notifications.disable_core_updates'],
            'prevent_auto_plugin_update_send_email' => ['system.notifications.disable_plugin_updates'],
            'prevent_auto_theme_update_send_email' => ['system.notifications.disable_theme_updates'],
            'protect_emails' => ['frontend.email.protect', function($value) use($advset_options) {
                return array_filter(['enable' => true, 'method' => empty($advset_options['protect_emails_method']) || $advset_options['protect_emails_method'] !== 'javascript' ? null : 'javascript']);
            }],
            'add_thumbs' => ['frontend.thumbnails.enable_support'],
            // code
            'facebook_og_metas' => ['frontend.meta.facebook_og_metas'],
            'remove_menu' => ['dashboard.adminbar.remove_from_frontend'],
            'remove_default_wp_favicon' => ['system.favicon.remove_default'],
            'favicon' => ['frontend.favicon.auto_from_theme'],
            'description' => ['frontend.meta.auto_description', function($value) use($advset_options) {
                return array_filter(['enable' => true, 'add_from' => empty($advset_options['single_metas']) ? 'blog_description' : null]);
            }],
            'single_metas' => ['frontend.meta.auto_description', function($value) use($advset_options) {
                return array_filter(['enable' => true, 'add_from' => empty($advset_options['description']) ? 'excerpt' : null]);
            }],
            'remove_generator' => ['frontend.meta.remove_generator'],
            'remove_rsd' => ['frontend.meta.remove_rsd'],
            'remove_shortlink' => ['frontend.meta.remove_shortlink'],
            'config_wp_title' => ['frontend.title.improve_format'],
            'excerpt_limit' => ['frontend.excerpt.word_limit', function($value) {
                return array_filter(['enable' => true, 'limit' => empty($value) ? null : (string) $value]);
            }],
            'excerpt_more_text' => ['frontend.excerpt.read_more', function($value) {
                return array_filter(['enable' => true, 'text' => empty($value) ? null : (string) $value]);
            }],
            'remove_wptexturize' => ['frontend.content.disable_wptexturize'],
            'remove_pingbacks_trackbacks_count' => ['frontend.comments.exclude_pingbacks_from_count'],
            'author_bio' => ['frontend.post.show_author_bio'],
            'author_bio_html' => ['frontend.user.allow_html_bio'],
            'compress' => ['frontend.code.minify_html'],
            'remove_comments' => ['frontend.code.remove_comments'],
            'analytics' => ['frontend.analytics.google', function($value) {
                return array_filter(['enable' => true, 'ga_code' => empty($value) ? null : (string) $value]);
            }],
            'feedburner' => ['frontend.feed.feedburner', function($value) {
                return array_filter(['enable' => true, 'feedburner' => empty($value) ? null : (string) $value]);
            }],

            'show_deprecated_features' => ['advset.features.show_deprecated'],
            'show_experimental_expert_features' => ['advset.features.show_experimental'],
            'advset_tracksettings_choice' => ['advset.user_tracking.feature_usage', function($value) {
                return array_filter(['status' => $value === '0' ? 'no' : ($value === '1' ? 'yes' : null)]);
            }],
        ];

        foreach ($feature_transformations as $old_name => $info) {
            $new_id = $info[0];
            $transformation = $info[1] ?? null;
            if (isset($advset_options[$old_name])) {
                if (empty($transformation)) {
                    if (!empty($advset_options[$old_name])) {
                        $new_settings[$new_id] = ['enable' => true];
                    }
                } elseif (($new_value = $transformation($advset_options[$old_name])) && !empty($new_value)) {
                    $new_settings[$new_id] = $new_value;
                }
                unset($old_settings[$old_name]);
            }
        }

        foreach ([
            'scripts',
            'styles',
            'post_types',
            'hooks',
        ] as $feature) {
            switch ($feature) {
                case 'scripts':
                    $scripts_options = get_option('advset_scripts', []);
                    if (!empty($scripts_options) && !(count($scripts_options) === 1 && isset($scripts_options['advset_group']))) {
                        $new_settings['developer.settings_pages.scripts'] = ['enable' => true];
                    }
                    break;
                case 'styles':
                    $styles_options = get_option('advset_styles', []);
                    if (!empty($styles_options) && !(count($styles_options) === 1 && isset($styles_options['advset_group']))) {
                        $new_settings['developer.settings_pages.styles'] = ['enable' => true];
                    }
                    break;
                case 'post_types':
                    $post_types_options = get_option('advset_post_types', []);
                    if (!empty($post_types_options) && !(count($post_types_options) === 1 && isset($post_types_options['advset_group']))) {
                        $new_settings['developer.settings_pages.post_types'] = ['enable' => true];
                    }
                    break;
                case 'hooks':
                    // Check if filters/actions are configured
                    $remove_filters = get_option('advset_remove_filters', []);
                    // Check for active filters (without advset_group)
                    if (empty($remove_filters)) break;
                    if (count($remove_filters) === 1 && isset($remove_filters['advset_group'])) break;
                    
                    // Check if there are active filters in the subarrays
                    foreach ($remove_filters as $tag => $functions) {
                        if ($tag !== 'advset_group' && !empty($functions)) {
                            $new_settings['developer.settings_pages.hooks'] = ['enable' => true];
                            break;
                        }
                    }
                    break;
            }
        }

        update_option('advanced_settings_settings', $new_settings);
        update_option('advset_old_settings', $old_settings);
    },

];
