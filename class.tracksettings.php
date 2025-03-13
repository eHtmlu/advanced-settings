<?php defined('ABSPATH') or exit;


class Advanced_Settings_Track_Settings {

    private static $instance = null;

    private function __clone() {}

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('wp_ajax_advset_track_choice', [$this, 'handle_tracking_choice']);

        add_action('admin_enqueue_scripts', [$this, 'init_modal']);

        foreach ([
            'advset_scripts',
            'advset_tracked_scripts',
            'advset_tracked_styles',
            'advset_styles',
            'advset_remove_filters',
            'advset_post_types',
            'advset_advset',
            'advset_code',
            'advset_system',
            'advset_tracksettings_asklater',
        ] as $option) {
            add_action('update_option_' . $option, [$this, 'send_if_consent_exists']);
        }
    }

    /**
     * Gets consent value
     */
    public function consent() {
        return advset_option('advset_tracksettings_choice', '0');
    }

    public function init_modal() {
        if ($this->should_show_modal()) {
            $this->enqueue_assets();
            add_action('admin_footer', [$this, 'render_modal_template']);
        }
    }

    /**
     * Checks if the modal should be displayed
     */
    private function should_show_modal() {
        if (!current_user_can('install_plugins')) return false;
        if (false !== advset_option('advset_tracksettings_choice', false)) return false;
        $t = get_option('advset_tracksettings_asklater', 0);
        if (!($t < (time() - (60 * 60)))) return false;
        if ($t > 0) delete_option('advset_tracksettings_asklater');
        return true;
    }

    /**
     * Enqueues required scripts and styles
     */
    public function enqueue_assets() {
        wp_enqueue_script(
            'advset-tracking-modal',
            plugins_url('assets/tracksettings-optin-modal.js', __FILE__),
            ['jquery', 'wp-a11y'],
            filemtime(plugin_dir_path(__FILE__) . 'assets/tracksettings-optin-modal.js'),
            true
        );

        wp_localize_script('advset-tracking-modal', 'advsetTracking', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('tracking_choice_nonce')
        ]);
    }

    /**
     * Renders the modal dialog template
     */
    public function render_modal_template() {
        $title = __('Help to improve Advanced Settings', 'advset-tracking');
        $text = sprintf(__("The Advanced Settings plugin is currently being fundamentally revised. If you let us know which features you use, we can take this into account. We only collect yes/no information about what features you use, nothing else.\n\nOtherwise, we won't know what you're using and we might remove a feature you actually need. You can change this setting later at any time under <a href=\"%s\">%s &gt; %s</a>.\n\nDo you agree to let us know which features you use?", 'advset-tracking'), 'options-general.php?page=advanced-settings&tab=admin-advset', __('Settings'), __('Advanced'));
        $lang = substr(get_user_locale(), 0, 2);
        ?>
        <dialog id="advset-tracking-dialog" 
                role="dialog"
                aria-labelledby="advset-tracking-title"
                aria-modal="true"
                class="wp-dialog">
                
            <div class="wp-dialog-content">
                <header class="">
                    <img src="<?php echo plugins_url('assets/icon-256x256.svg', __FILE__) ?>" />
                    <h2 id="advset-tracking-title" class="wp-dialog-title">
                        <?php echo $title ?>
                    </h2>
                    <div class="advset-tracking-dialog-lang">
                        <?php if ($lang !== 'en') { ?>
                        <span>EN</span>
                        <a 
                            target="_blank"
                            href="https://translate.google.com/?sl=en&tl=<?php echo rawurlencode($lang); ?>&text=<?php echo rawurlencode(html_entity_decode(strip_tags($title . "\n\n" . $text))) ?>&op=translate"
                            >
                            <?php echo strtoupper($lang) ?>
                        </a>
                        <?php } ?>
                    </div>
                    <button type="button" 
                            class="close"
                            title="Close for now"
                            onclick="this.closest('dialog').close()">
                        <?php esc_html_e('Close', 'advset-tracking') ?>
                    </button>
                </header>
                <br />
                <hr />
                <p><?php echo str_replace("\n", '<br />', $text); ?></p>
                
                <div class="wp-dialog-buttons">
                    <div>
                        <button type="button" 
                                class="button"
                                data-choice="later">
                            <?php esc_html_e('ask me later', 'advset-tracking') ?>
                        </button>
                    </div>
                    <br />
                    <div>
                        <button type="button"
                                class="button button-secondary button-hero"
                                data-choice="disagree">
                            <?php esc_html_e('Disagree and take the risk', 'advset-tracking') ?>
                        </button>
                        &nbsp;
                        <button type="button" 
                                class="button button-primary button-hero"
                                data-choice="agree"
                                autofocus>
                            <?php esc_html_e('Agree', 'advset-tracking') ?>
                        </button>
                    </div>
                </div>
            </div>
        </dialog>
        <style>
            
            /* Dialogs */

            #advset-tracking-dialog {
                border: 0;
                border-radius: 1rem;
                padding: 2rem;
                max-width: min(32rem, (100% - 20px));
                box-sizing: border-box;
                display: none;
                opacity: 0;
                transition-property: opacity display overlay transform;
                transition-duration: .3s;
                transition-behavior: allow-discrete;
                transform: scale(.95);
                position: relative;
            }

            #advset-tracking-dialog header {
                display: flex;
                justify-content: start;
                gap: 1rem;
                align-items: center;
            }

            #advset-tracking-dialog header h2 {
                margin: 0;
                line-height: 1.3;
                font-size: 1.5rem;
            }

            #advset-tracking-dialog header img {
                width: 5rem;
                height: 5rem;
                vertical-align: middle;
            }

            .advset-tracking-dialog-lang {
                display: flex;
                width: 4rem;
                align-self: end;
                flex-grow: 0;
                flex-shrink: 0;
            }

            .advset-tracking-dialog-lang > a,
            .advset-tracking-dialog-lang > span {
                width: 50%;
                text-align: center;
                line-height: 1.5;
                border: #ccc solid 1px;
                text-decoration: none;
                color: #000;
            }

            .advset-tracking-dialog-lang > span {
                border-radius: .25rem 0 0 .25rem;
                border-right: 0;
                font-weight: bold;
                cursor: default;
            }

            .advset-tracking-dialog-lang > a {
                border-radius: 0 .25rem .25rem 0;
                color: #666;
                transition: all .3s;
            }

            .advset-tracking-dialog-lang > a:hover,
            .advset-tracking-dialog-lang > a:focus-visible {
                background-color: #ccc;
                color: #000;
            }

            #advset-tracking-dialog .close {
                position: absolute;
                top: .5rem;
                right: .5rem;
                appearance: none;
                border: 0;
                background: none;
                text-indent: -200vw;
                overflow: hidden;
                width: 1rem;
                height: 1rem;
                padding: 0;
                margin: 0;
                color: #ccc;
                cursor: pointer;
                transition: all .3s;
            }

            #advset-tracking-dialog .close:hover,
            #advset-tracking-dialog .close:focus-visible {
                color: #000;
            }

            #advset-tracking-dialog .close::before,
            #advset-tracking-dialog .close::after {
                content: " ";
                border-top: solid 2px currentColor;
                position: absolute;
                display: block;
                top: calc(50% - 1px);
                width: 100%;
            }

            #advset-tracking-dialog .close::before {
                rotate: 45deg;
            }

            #advset-tracking-dialog .close::after {
                rotate: -45deg;
            }

            #advset-tracking-dialog p {
                font-size: 1rem;
            }

            #advset-tracking-dialog .wp-dialog-content > :first-child {
                margin-top: 0;
            }

            /* #advset-tracking-dialog .wp-dialog-buttons {
                text-align: right;
            } */

            /* #advset-tracking-dialog .wp-dialog-buttons .button-small {
                float: left;
            } */

            #advset-tracking-dialog .wp-dialog-buttons > div {
                display: flex;
                justify-content: space-between;
                flex-wrap: wrap;
            }

            #advset-tracking-dialog[open] {
                display: block;
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            #advset-tracking-dialog::backdrop {
                transition-property: opacity display overlay;
                transition-duration: .3s;
                transition-behavior: allow-discrete;
                opacity: 0;
            }

            #advset-tracking-dialog[open]::backdrop {
                opacity: 1;
                background: rgba(0, 0, 0, .7);
            }

            @starting-style {
                #advset-tracking-dialog[open] {
                    opacity: 0;
                    transform: scale(.95);
                }
                #advset-tracking-dialog[open]::backdrop {
                    opacity: 0;
                }
            }
        </style>
        <?php
    }

    /**
     * Handles AJAX tracking choice submissions
     */
    public function handle_tracking_choice() {
        check_ajax_referer('tracking_choice_nonce', 'nonce');

        if (!isset($_POST['choice'])) {
            wp_die();
        }

        if (in_array($_POST['choice'], ['agree', 'disagree'])) {
            $value = ('agree' === $_POST['choice']) ? '1' : '0';
            $option = get_option('advset_advset', []);
            $option['advset_tracksettings_choice'] = $value;
            update_option('advset_advset', $option);
            wp_die();
        }

        if ($_POST['choice'] === 'later') {
            update_option('advset_tracksettings_asklater', time());
        }

        wp_die();
    }

    /**
     * Sends data if user consented
     */
    public function send_if_consent_exists() {

        
        // Cancel if no consent exists
        if ($this->consent() === false) {
            return false;
        }

        // Collect stored data
        $data_stored = [];
        foreach ([
            'advset_scripts',
            'advset_tracked_scripts',
            'advset_tracked_styles',
            'advset_styles',
            'advset_remove_filters',
            'advset_post_types',
            'advset_advset',
            'advset_code',
            'advset_system',
            'advset_tracksettings_asklater',
        ] as $option) {
            $data_stored[$option] = get_option($option, null);
        }


        // Anonymize data (tranform data to yes/no informations)
        $handler_boolean = function($value) { return (string) $value === '1'; };
        $handler_numeric = function($value) { return $value > 0; };
        $handler_string = function($value) { return $value !== ''; };
        $handler_array = function($value) { return is_array($value) && !empty($value); };
        $handlers = [
            //'advset_tracked_scripts' => [],
            'advset_scripts' => [
                'jquery_remove_migrate' => $handler_boolean,
                'jquery_cnd' => $handler_boolean,
                'remove_script_type' => $handler_boolean,
                'track_enqueued_scripts' => $handler_boolean,
                'track_merge_removed_scripts' => $handler_boolean,
                'track_merged_scripts_footer' => $handler_boolean,
                //'remove_enqueued_script_hoverintent-js',
                //'remove_enqueued_script_admin-bar',
            ],
            //'advset_tracked_styles' => [],
            'advset_styles' => [
                'track_enqueued_styles' => $handler_boolean,
                'track_merge_removed_styles' => $handler_boolean,
                //'remove_enqueued_style_dashicons',
                //'remove_enqueued_style_admin-bar',
                //'remove_enqueued_style_wp-block-navigation',
                //'remove_enqueued_style_twentytwentyfive-style',
            ],
            'advset_remove_filters' => function($value) {
                $used = [];
                foreach ($value as $cat => $hooks) {
                    if (!empty($hooks)) {
                        $used[$cat] = $hooks;
                    }
                }
                return empty($used) ? false : $used;
            },
            'advset_post_types' => $handler_array,
            'advset_advset' => [
                'show_deprecated_features' => $handler_boolean,
                'advset_tracksettings_choice' => $handler_boolean,
            ],
            'advset_code' => [
                'facebook_og_metas' => $handler_boolean,
                'remove_menu' => $handler_boolean,
                'remove_default_wp_favicon' => $handler_boolean,
                'favicon' => $handler_boolean,
                'description' => $handler_boolean,
                'single_metas' => $handler_boolean,
                'remove_generator' => $handler_boolean,
                'remove_wlw' => $handler_boolean,
                'remove_rsd' => $handler_boolean,
                'remove_shortlink' => $handler_boolean,
                'config_wp_title' => $handler_boolean,
                'excerpt_limit' => $handler_numeric,
                'excerpt_more_text' => $handler_string,
                'remove_wptexturize' => $handler_boolean,
                'remove_pingbacks_trackbacks_count' => $handler_boolean,
                'author_bio' => $handler_boolean,
                'author_bio_html' => $handler_boolean,
                'compress' => $handler_boolean,
                'remove_comments' => $handler_boolean,
                'analytics' => $handler_string,
                'feedburner' => $handler_string,
            ],
            'advset_system' => [
                'hide_update_message' => $handler_boolean,
                'dashboard_logo' => $handler_string,
                'remove_default_wp_widgets' => $handler_boolean,
                'remove_widget_system' => $handler_boolean,
                'remove_comments_system' => $handler_boolean,
                'disable_auto_save' => $handler_boolean,
                'disable_author_pages' => $handler_boolean,
                'auto_thumbs' => $handler_boolean,
                'jpeg_quality' => $handler_numeric,
                'max_image_size_w' => $handler_numeric,
                'max_image_size_h' => $handler_numeric,
                'core_upgrade_skip_new_bundled' => $handler_boolean,
                'show_query_num' => $handler_boolean,
                'add_thumbs' => $handler_boolean,
            ],
            'advset_tracksettings_asklater' => $handler_boolean,
        ];
        $data_anonymized = [];
        $data = ['url' => home_url(), 'stored' => &$data_stored, 'anonymized' => &$data_anonymized];
        foreach ($handlers as $option_name => $option_handler) {
            $value = $data_stored[$option_name];
            if ($value === null) {
                continue;
            }
            if ($option_handler instanceof Closure) {
                $r = $option_handler($value);
                if ($r === true) {
                    $data_anonymized[$option_name] = 1;
                }
                elseif (!is_bool($r)) {
                    $data_anonymized[$option_name] = $r;
                }
            }
            if (is_array($option_handler)) {
                $result_option = [];
                foreach ($option_handler as $field_name => $field_handler) {
                    if (isset($value[$field_name]) && $field_handler($value[$field_name])) {
                        $result_option[$field_name] = 1;
                    }
                }
                if (!empty($result_option)) {
                    $data_anonymized[$option_name] = $result_option;
                }
            }
        }

        // Send data
        try {
            @file_get_contents('https://advsettracking.ehtmlu.com/', false, stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => http_build_query([
                        'data' => json_encode($data),
                    ])
                ]
            ]));
        } catch(Exception $e) {}

        return true;
    }
}
