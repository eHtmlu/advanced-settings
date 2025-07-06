<?php
/**
 * Frontend Category
 * 
 * Registers the frontend category and its features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;



advset_register_feature([
    'id' => 'frontend.http_headers.remove_php_version',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('HTTP', 'advanced-settings'),
            __('Security', 'advanced-settings'),
            __('Cleanup', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Remove PHP version from HTTP headers', 'advanced-settings'),
                'description' => __('Removes the PHP version from the HTTP headers of the website', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        header_remove('X-Powered-By');
    },
    'priority' => 10,
]);



advset_register_feature([
    'id' => 'frontend.http_headers.add_security_headers',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('HTTP', 'advanced-settings'),
            __('Security', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Add security headers', 'advanced-settings'),
                /* translators: %s is a link to securityheaders.com */
                'descriptionHtml' => sprintf(__('Adds various security headers to HTTP responses. You can check your website\'s security headers with %s.', 'advanced-settings'), '<a href="https://securityheaders.com/?q=' . rawurlencode(get_home_url()) . '&hide=on&followRedirects=on" target="_blank">securityheaders.com</a>'),
            ],
            'info' => [
                'type' => 'info',
                'label' => __('Caution:', 'advanced-settings'),
                'description' => __('If you integrate external services into your website or your website requires access to browser features like camera, microphone, geolocation, or other device sensors, the security headers may be set too strictly. In this case, simply disable this feature. You can implement customized headers via code if necessary.', 'advanced-settings'),
                'visible' => ['enable' => true]
            ],
        ]
    ],
    'execution_handler' => function() {
        if ( advset_is_admin_area() ) return;
        add_action('send_headers', function() {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Content-Security-Policy: object-src \'none\';');
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
            header('Permissions-Policy: accelerometer=(),camera=(),geolocation=(),gyroscope=(),magnetometer=(),microphone=(),payment=(),usb=(),interest-cohort=()');
        });
    },
    'priority' => 20,
]);



advset_register_feature([
    'id' => 'frontend.favicon.auto_from_theme',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Meta', 'advanced-settings'),
            __('Favicon', 'advanced-settings'),
            __('SEO', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Automatically add a favicon from theme directory', 'advanced-settings'),
                'description' => __('Whenever there is a favicon.ico, favicon.png or favicon.svg file in the theme directory', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_action( 'wp_head', function() {
            foreach ([
                'ico' => '',
                'png' => 'image/png',
                'svg' => 'image/svg+xml',
            ] as $suffix => $mime) {
                if ( file_exists(TEMPLATEPATH.'/favicon.' . $suffix) ) {
                    echo '<link rel="shortcut icon"' . ($mime ? ' type="'.$mime.'"' : '') . ' href="'.get_bloginfo('template_url').'/favicon.'.$suffix.'">'."\r\n";
                    break;
                }
            }
        });
    },
    'priority' => 30,
]);



advset_register_feature([
    'id' => 'frontend.meta.facebook_og_metas',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Meta', 'advanced-settings'),
            __('SEO', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Enable Facebook Open Graph Meta Tags', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        if ( advset_is_admin_area() ) return;

        add_action('wp_head', function() {
            global $post;
            if (is_single() || is_page()) { ?>
                <meta property="og:title" content="<?php single_post_title(''); ?>" />
                <meta property="og:description" content="<?php echo strip_tags(get_the_excerpt($post->ID)); ?>" />
                <meta property="og:type" content="article" />
                <meta property="og:image" content="<?php if (function_exists('wp_get_attachment_thumb_url')) {echo wp_get_attachment_url(get_post_thumbnail_id($post->ID)); }?>" />
            <?php }
        });
    },
    'priority' => 40,
]);



advset_register_feature([
    'id' => 'frontend.meta.remove_shortlink',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Meta', 'advanced-settings'),
            __('Cleanup', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Remove shortlink meta tag', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        if ( advset_is_admin_area() ) return;
        remove_action( 'wp_head', 'wp_shortlink_wp_head');
    },
    'priority' => 50,
]);



advset_register_feature([
    'id' => 'frontend.meta.remove_rsd',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Meta', 'advanced-settings'),
            __('Cleanup', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Remove RSD meta tag', 'advanced-settings'),
                'description' => __('This meta tag is used by Windows Live Writer to edit posts. Use it only if you are using Windows Live Writer.', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        if ( advset_is_admin_area() ) return;
        remove_action( 'wp_head', 'rsd_link');
    },
    'priority' => 60,
]);



advset_register_feature([
    'id' => 'frontend.meta.remove_generator',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Meta', 'advanced-settings'),
            __('Security', 'advanced-settings'),
            __('Cleanup', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Remove generator meta tag', 'advanced-settings'),
                'description' => __('Removes the WordPress version from the head section of the website', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        if ( advset_is_admin_area() ) return;
        remove_action( 'wp_head', 'wp_generator');
    },
    'priority' => 70,
]);



advset_register_feature([
    'id' => 'frontend.meta.auto_description',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Meta', 'advanced-settings'),
            __('SEO', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Automatically add a description meta tag', 'advanced-settings'),
            ],
            'add_from' => [
                'type' => 'radio',
                'label' => __('Add description from', 'advanced-settings'),
                'options' => [
                    'both' => [
                        'label' => __('Excerpt and blog description', 'advanced-settings'),
                        'description' => __('Use the excerpt where available, otherwise use the blog description', 'advanced-settings'),
                    ],
                    'excerpt' => [
                        'label' => __('Excerpt', 'advanced-settings'),
                        'description' => __('Use the excerpt where the excerpt is available', 'advanced-settings'),
                    ],
                    'blog_description' => [
                        'label' => __('Blog description', 'advanced-settings'),
                        'description' => __('Use the blog description always as the description meta tag', 'advanced-settings'),
                    ],
                ],
                'default' => 'both',
                'visible' => ['enable' => true]
            ],
        ]
    ],
    'handler_cleanup' => function($settings) {
        return empty($settings['enable']) ? null : $settings;
    },
    'execution_handler' => function($settings) {
        if ( advset_is_admin_area() ) return;
        add_action('wp_head', function() use($settings) {
            $from = $settings['add_from'] ?? 'both';
            if ((is_single() || is_page()) && ($from === 'excerpt' || $from === 'both')) {
                echo '<meta name="description" content="' . strip_tags(get_the_excerpt()) . '" />';
            }
            elseif ($from === 'blog_description' || $from === 'both') {
                echo '<meta name="description" content="' . get_bloginfo('description') . '" />';
            }
        });
    },
    'priority' => 80,
]);



advset_register_feature([
    'id' => 'frontend.author.disable_pages',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Content', 'advanced-settings'),
            __('Security', 'advanced-settings'),
            __('Cleanup', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Disable author pages', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_action( 'template_redirect', function () {
            global $wp_query;
            if ( is_author() ) {
                $wp_query->set_404();
                status_header(404);
            }
        } );
        
        add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
            if ( 'users' === $name ) {
                return false;
            }
            return $provider;
        }, 10, 2 );
    },
    'priority' => 90,
]);



advset_register_feature([
    'id' => 'frontend.title.improve_format',
    'category' => 'frontend',
    'deprecated' => true,
    'ui_config' => fn() => [
        'tags' => [
            __('Developer', 'advanced-settings'),
            __('Frontend', 'advanced-settings'),
            __('Content', 'advanced-settings'),
            __('Automations', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Adjust the wp_title function', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_filter('wp_title', function($title, $sep) {
            global $paged, $page;
    
            if ( is_feed() )
                return $title;
    
            // Add the site name.
            $title .= get_bloginfo( 'name' );
    
            // Add the site description for the home/front page.
            $site_description = get_bloginfo( 'description', 'display' );
            if ( $site_description && ( is_home() || is_front_page() ) )
                $title = "$title $sep $site_description";
    
            // Add a page number if necessary.
            if ( $paged >= 2 || $page >= 2 )
                $title = "$title $sep " . sprintf( __( 'Page %s', 'responsive' ), max( $paged, $page ) );
    
            return $title;
        }, 10, 2);
    },
    'priority' => 100,
]);



advset_register_feature([
    'id' => 'frontend.content.disable_wptexturize',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Editing', 'advanced-settings'),
            __('Frontend', 'advanced-settings'),
            __('Content', 'advanced-settings'),
            __('Cleanup', 'advanced-settings'),
            __('Automations', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Disable wptexturize filter', 'advanced-settings'),
                'description' => __('transformations of quotes to smart quotes, apostrophes, dashes, ellipses, the trademark symbol, and the multiplication symbol', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_filter( 'run_wptexturize', '__return_false' );
    },
    'priority' => 110,
]);



advset_register_feature([
    'id' => 'frontend.oembed.disable',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Editing', 'advanced-settings'),
            __('Frontend', 'advanced-settings'),
            __('Content', 'advanced-settings'),
            __('Security', 'advanced-settings'),
            __('Cleanup', 'advanced-settings'),
            __('GDPR', 'advanced-settings'),
            __('Performance', 'advanced-settings'),
            __('Automations', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Disable auto-embed of external content', 'advanced-settings'),
                'description' => __('Disables WordPress oEmbed, which automatically converts URLs into embedded content.', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('rest_api_init', 'wp_oembed_register_route');
        remove_filter('the_content', [$GLOBALS['wp_embed'], 'autoembed'], 8);
        remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
        add_filter('embed_oembed_discover', '__return_false');
    },
    'priority' => 120,
]);



advset_register_feature([
    'id' => 'frontend.thumbnails.enable_support',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Editing', 'advanced-settings'),
            __('Frontend', 'advanced-settings'),
            __('Content', 'advanced-settings'),
            __('Images', 'advanced-settings'),
            __('Media', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Add thumbnail support', 'advanced-settings'),
                'disabled' => !ADVSET_THUMBS,
                'description' => ADVSET_THUMBS
                    ? ''
                    : __('Already supported by current theme', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_action('after_setup_theme', function (){
            add_theme_support( 'post-thumbnails' );
        });
    },
    'priority' => 130,
]);
define( 'ADVSET_THUMBS', !current_theme_supports('post-thumbnails') );



advset_register_feature([
    'id' => 'frontend.thumbnails.auto_from_first_image',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Editing', 'advanced-settings'),
            __('Frontend', 'advanced-settings'),
            __('Content', 'advanced-settings'),
            __('Images', 'advanced-settings'),
            __('Media', 'advanced-settings'),
            __('Automations', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Automatically generate the Post Thumbnail', 'advanced-settings'),
                'description' => __('from the first image in post', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        require_once ADVSET_DIR . '/feature-setup/features/includes/frontend.auto_thumbs.php';
        add_action('transition_post_status', 'advset__feature__auto_thumbs', 10, 3);
    },
    'priority' => 140,
]);



advset_register_feature([
    'id' => 'frontend.excerpt.word_limit',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Editing', 'advanced-settings'),
            __('Frontend', 'advanced-settings'),
            __('Content', 'advanced-settings'),
            __('Cleanup', 'advanced-settings'),
            __('Automations', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Limit the excerpt length by number of words', 'advanced-settings'),
            ],
            'limit' => [
                'type' => 'number',
                'label' => __('Number of words', 'advanced-settings'),
                'visible' => ['enable' => true]
            ],
        ]
    ],
    'handler_cleanup' => function($value) {
        return empty($value['enable']) ? null : $value;
    },
    'execution_condition' => function($settings) {
        return empty($settings['enable']) || empty($settings['limit']) ? false : true;
    },
    'execution_handler' => function($settings) {
        add_filter('excerpt_length', function($length) use($settings) {
            return $settings['limit'];
        });
    },
    'priority' => 150,
]);



advset_register_feature([
    'id' => 'frontend.excerpt.read_more',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Content', 'advanced-settings'),
            __('SEO', 'advanced-settings'),
            __('Cleanup', 'advanced-settings'),
            __('Automations', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Add a "Read More" link to the excerpt', 'advanced-settings'),
            ],
            'text' => [
                'type' => 'text',
                'label' => __('Text', 'advanced-settings'),
                'visible' => ['enable' => true]
            ],
        ]
    ],
    'handler_cleanup' => function($value) {
        return empty($value['enable']) ? null : $value;
    },
    'execution_condition' => function($settings) {
        return empty($settings['enable']) || empty($settings['text']) ? false : true;
    },
    'execution_handler' => function($settings) {
        add_filter('excerpt_more', function($more) use($settings) {
            return '<a class="excerpt-read-more" href="' . esc_url( get_permalink() ) . '">'.esc_html($settings['text']).'</a>';
        });
    },
    'priority' => 160,
]);



advset_register_feature([
    'id' => 'frontend.comments.exclude_pingbacks_from_count',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Content', 'advanced-settings'),
            __('Comments', 'advanced-settings'),
            __('Automations', 'advanced-settings'),
            __('Cleanup', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Remove pingbacks and trackbacks from comment count', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_filter('get_comments_number', function($count) {
            if ( ! advset_is_admin_area() ) {
                global $id;
                $comments = get_comments('status=approve&post_id=' . $id);
                $comments_by_type = separate_comments($comments);
                return count($comments_by_type['comment']);
            }
            else {
                return $count;
            }
        }, 10);
    },
    'priority' => 170,
]);



advset_register_feature([
    'id' => 'frontend.post.show_author_bio',
    'category' => 'frontend',
    'deprecated' => true,
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Content', 'advanced-settings'),
            __('Automations', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Insert author bio in each post', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        add_filter('the_content', function($content='') {
            return $content.' <div id="entry-author-info">
                <div id="author-avatar">
                    '. get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'author_bio_avatar_size', 100 ) ) .'
                </div>
                <div id="author-description">
                    <h2>'. sprintf( __( 'About %s' ), get_the_author() ) .'</h2>
                    '. get_the_author_meta( 'description' ) .'
                    <div id="author-link">
                        <a href="'. get_author_posts_url( get_the_author_meta( 'ID' ) ) .'">
                            '. sprintf( __( 'View all posts by %s <span class="meta-nav">&rarr;</span>' ), get_the_author() ) .'
                        </a>
                    </div>
                </div>
            </div>';
        });
    },
    'priority' => 180,
]);



advset_register_feature([
    'id' => 'frontend.user.allow_html_bio',
    'category' => 'frontend',
    'deprecated' => true,
    'ui_config' => fn() => [
        'tags' => [
            __('Admin', 'advanced-settings'),
            __('Frontend', 'advanced-settings'),
            __('Content', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Allow complex HTML in user profile description', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        remove_filter('pre_user_description', 'wp_filter_kses');
    },
    'priority' => 190,
]);



advset_register_feature([
    'id' => 'frontend.email.protect',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Content', 'advanced-settings'),
            __('Security', 'advanced-settings'),
            __('Automations', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Protect email addresses from spam bots', 'advanced-settings'),
                'description' => __('Converts email addresses to encoded versions to prevent spam harvesting.', 'advanced-settings'),
                'default' => false
            ],
            'method' => [
                'type' => 'radio',
                'label' => __('Protection method', 'advanced-settings'),
                'options' => [
                    'entities' => [
                        'label' => __('HTML entities', 'advanced-settings'),
                        'description' => __('More SEO friendly, but not as protected.', 'advanced-settings'),
                    ],
                    'javascript' => [
                        'label' => __('JavaScript', 'advanced-settings'),
                        'description' => __('Better protection, but slightly less SEO-friendly.', 'advanced-settings'),
                    ],
                ],
                'default' => 'entities',
                'visible' => ['enable' => true]
            ],
        ]
    ],
    'handler_cleanup' => function($value) {
        return empty($value['enable']) ? null : $value;
    },
    'execution_handler' => function($settings) {
        if ( is_admin() || wp_doing_ajax() ) return;

        add_action('parse_request', function() use($settings) {
            if (defined('REST_REQUEST')) return;

            ob_start(function($content) use($settings) {

                if (!empty($settings['method']) && $settings['method'] === 'javascript') {
                    $content = preg_split('/(\<[^\>]+\>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
            
                    for ($a = 0; $a < count($content); $a++)
                    {
                        if ($a % 2)
                        {
                            if (substr($content[$a], 0, 2) === '</')
                            {
                                continue;
                            }
            
                            $line = preg_split('/((([a-z0-9\_\-]+)\s*\=\s*\")([^\"]*(?:@|\%40|\&\#64\;|\&\#x40\;)[^\"]*)(\"))/', $content[$a], -1, PREG_SPLIT_DELIM_CAPTURE);
                            $b64arr = array();
            
                            if (count($line) > 1)
                            {
                                for ($b = 3; $b < count($line); $b+=6)
                                {
                                    $b64arr[$line[$b]] = $line[$b + 1];
                                    $line[$b + 1] = $line[$b] === 'href' ? 'javascript:;' : '';
                                    $line[$b - 2] = '';
                                    $line[$b] = '';
                                }
            
                                $content[$a] = implode('', $line) . '<script>(function(){var s=document.getElementsByTagName(\'script\'),e=s[s.length-1].parentNode,d=JSON.parse(atob(\'' . base64_encode(json_encode($b64arr)) . '\')),l;for(l in d){e[l]=d[l];}})();</script>';
                            }
                        }
                        else
                        {
                            $line = preg_split('/(?<=^|[^a-z0-9\.+&\_-])([a-z0-9\.+&\_-]+(?:@|\%40|\&\#64\;|\&\#x40\;)[a-z0-9\.\_-]{2,}\.[a-z0-9]+)(?=[^a-z0-9]|$)/i', $content[$a], -1, PREG_SPLIT_DELIM_CAPTURE);
            
                            if (count($line) > 1)
                            {
                                for ($b = 1; $b < count($line); $b+=2)
                                {
                                    $line[$b] = '<script>document.write(atob(\'' . base64_encode($line[$b]) . '\'));</script>';
                                }
            
                                $content[$a] = implode('', $line);
                            }
                        }
                    }
            
                    return implode('', $content);
                }
                else {
                    return preg_replace_callback('/((?:mailto\:)?[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', function($matches) {
                        static $cache = [];
                        $email = $matches[1];
                        
                        // Use cached version if available
                        if (isset($cache[$email])) {
                            return $cache[$email];
                        }
                        
                        // Convert to entities and cache
                        $output = implode('', array_map(function($char) {
                            return '&#' . ord($char) . ';';
                        }, str_split($email)));
                        
                        $cache[$email] = $output;
                        return $output;
                    }, $content);
                }
            });
        });
    },
    'priority' => 200,
]);



advset_register_feature([
    'id' => 'frontend.analytics.google',
    'category' => 'frontend',
    'deprecated' => true,
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Meta', 'advanced-settings'),
            __('GDPR', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Google Analytics', 'advanced-settings'),
            ],
            'ga_code' => [
                'type' => 'text',
                'label' => __('Google Analytics Code', 'advanced-settings'),
                'description' => __('Enter your Google Analytics code here.', 'advanced-settings'),
                'visible' => ['enable' => true]
            ],
        ]
    ],
    'handler_cleanup' => function($settings) {
        return empty($settings['enable']) ? null : $settings;
    },
    'execution_condition' => function($settings) {
        return empty($settings['enable']) || empty($settings['ga_code']) ? false : true;
    },
    'execution_handler' => function($settings) {
        if ( advset_is_admin_area() ) return;
        add_action('wp_footer', function() use($settings) {
            $ga_code = $settings['ga_code'];
            echo "<script async src=\"https://www.googletagmanager.com/gtag/js?id=$ga_code\"></script>
    <script>window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '$ga_code');</script>";
        });
    },
    'priority' => 210,
]);



advset_register_feature([
    'id' => 'frontend.feed.feedburner',
    'category' => 'frontend',
    'deprecated' => true,
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Meta', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('FeedBurner', 'advanced-settings'),
            ],
            'feedburner' => [
                'type' => 'text',
                'label' => __('FeedBurner URL', 'advanced-settings'),
                'description' => __('Enter your FeedBurner URL here.', 'advanced-settings'),
                'visible' => ['enable' => true]
            ],
        ]
    ],
    'handler_cleanup' => function($settings) {
        return empty($settings['enable']) ? null : $settings;
    },
    'execution_condition' => function($settings) {
        return empty($settings['enable']) || empty($settings['feedburner']) ? false : true;
    },
    'execution_handler' => function($settings) {
        add_action( 'feed_link', function( $output, $feed ) use($settings) {

            if ( strpos( $output, 'comments' ) )
                return $output;
        
            if( strpos($settings['feedburner'], '/')===FALSE )
                return esc_url( 'https://feeds.feedburner.com/'.$settings['feedburner'] );
            else
                return esc_url( $settings['feedburner'] );
        }, 10, 2 );
    },
    'priority' => 220,
]);



advset_register_feature([
    'id' => 'frontend.code.minify_html',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('HTML', 'advanced-settings'),
            __('Performance', 'advanced-settings'),
            __('Automations', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Compress HTML output', 'advanced-settings'),
                'description' => __('Removes all whitespace between HTML tags', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        if ( advset_is_admin_area() ) return;
        ob_start(function($content) {
            return trim( preg_replace( '/\s+(?![^<>]*<\/pre>)/', ' ', $content ) );
        });
    },
    'priority' => 230,
]);



advset_register_feature([
    'id' => 'frontend.code.remove_comments',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('HTML', 'advanced-settings'),
            __('Performance', 'advanced-settings'),
            __('Cleanup', 'advanced-settings'),
            __('Automations', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Remove HTML comments', 'advanced-settings'),
                'description' => __('Removes all HTML comments except conditional IE comments "<!--[if IE]> ... <![endif]-->"', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        if ( advset_is_admin_area() ) return;
        ob_start(function($content) {
            return trim( preg_replace( '/<!--[^\[\>\<](.|\s)*?-->/', '', $content ) );
        });
    },
    'priority' => 240,
]);



advset_register_feature([
    'id' => 'frontend.emoji.remove_image_replacement',
    'category' => 'frontend',
    'ui_config' => fn() => [
        'tags' => [
            __('Frontend', 'advanced-settings'),
            __('Content', 'advanced-settings'),
            __('Emojis', 'advanced-settings'),
            __('Automations', 'advanced-settings'),
            __('Cleanup', 'advanced-settings'),
            __('GDPR', 'advanced-settings'),
        ],
        'fields' => [
            'enable' => [
                'type' => 'toggle',
                'label' => __('Remove image replacement for emojis', 'advanced-settings'),
                'description' => __('Missing emojis are no longer loaded as images from s.w.org. This makes your site faster and GDPR-compliant.', 'advanced-settings'),
            ],
        ]
    ],
    'execution_handler' => function() {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        add_filter('emoji_svg_url', '__return_false');
    },
    'priority' => 250,
]);


