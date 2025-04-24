<?php
/**
 * Admin UI functionality for Advanced Settings
 * 
 * This file handles the admin bar icon and modal dialog for administrators
 */

// Exit direct requests
if (!defined('ABSPATH')) exit;





/**
 * Add admin bar icon for administrators
 */
function advset_admin_bar_icon() {
    // Only show for administrators
    if (!current_user_can('manage_options')) {
        return;
    }
    
    global $wp_admin_bar;
    
    // Get the icon SVG content
    $icon_path = ADVSET_DIR . '/admin-ui/images/admin-bar-icon.svg';
    $icon_svg = '';
    if (file_exists($icon_path)) {
        $icon_svg = file_get_contents($icon_path);
    }
    
    // Add the main menu item with inline SVG
    $wp_admin_bar->add_menu([
        'id'    => 'advset-admin-icon',
        'title' => '<span class="ab-icon">' . $icon_svg . '</span><span class="screen-reader-text">Advanced Settings</span>',
        'href'  => 'javascript:void(0);',
        'meta'  => [
            'class' => 'advset-admin-icon',
            'onclick' => 'advset_open_modal(); return false;'
        ]
    ]);
}
function advset_admin_bar_icon_register() {
    // Ensure to position the icon right after the wp menu
    add_action('admin_bar_menu', 'advset_admin_bar_icon', 10);
}
// Ensure to be the first after core menu registrations
add_action('add_admin_bar_menus', 'advset_admin_bar_icon_register', -PHP_INT_MAX);







/**
 * Add plugin action links
 */
if (is_admin()) {
	add_filter( 'plugin_action_links', function($links, $file) {
		if ( $file === plugin_basename( ADVSET_FILE ) ) {
			$links[] = '<a href="javascript:void(0);" onclick="advset_open_modal(); return false;">' . __('Settings') . '</a>';
		}
		return $links;
	}, 10, 2 );
}







/**
 * Enqueue admin UI scripts and styles
 */
function advset_admin_ui_scripts() {
    // Only load for administrators
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Register and enqueue CSS
    wp_register_style(
        'advset-admin-ui',
        plugins_url('admin-ui.css', __FILE__),
        [],
        filemtime(ADVSET_DIR . '/admin-ui/admin-ui.css')
    );
    wp_enqueue_style('advset-admin-ui');
    
    // Register and enqueue JavaScript
    wp_register_script(
        'advset-admin-ui',
        plugins_url('admin-ui.js', __FILE__),
        [],
        filemtime(ADVSET_DIR . '/admin-ui/admin-ui.js'),
        true
    );

    // Prepare data for JavaScript
    $js_data = [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('advset-admin-ui-nonce'),
        'reactAppUrl' => plugins_url('react/app.js', __FILE__),
        'reactAppCssUrl' => plugins_url('react/app.css', __FILE__),
        'wpReactUrl' => includes_url('js/dist/vendor/react.min.js'),
        'wpReactDomUrl' => includes_url('js/dist/vendor/react-dom.min.js'),
        'showUserGuide' => get_option('advset_guide_shown') === false,
    ];

    wp_localize_script('advset-admin-ui', 'advsetAdminUI', $js_data);
    wp_enqueue_script('advset-admin-ui');

    // Load user guide assets if needed
    if ($js_data['showUserGuide']) {
        wp_register_style(
            'advset-user-guide',
            plugins_url('user-guide.css', __FILE__),
            ['advset-admin-ui'],
            filemtime(ADVSET_DIR . '/admin-ui/user-guide.css')
        );
        wp_enqueue_style('advset-user-guide');

        wp_register_script(
            'advset-user-guide',
            plugins_url('user-guide.js', __FILE__),
            ['advset-admin-ui', 'wp-i18n'],
            filemtime(ADVSET_DIR . '/admin-ui/user-guide.js'),
            true
        );
        wp_enqueue_script('advset-user-guide');
    }
}
add_action('admin_enqueue_scripts', 'advset_admin_ui_scripts');
add_action('wp_enqueue_scripts', 'advset_admin_ui_scripts');







/**
 * Ensure wp-api is available for administrators
 */
function advset_api_settings() {
    // Only load for administrators
    if (!current_user_can('manage_options')) {
        return;
    }

    wp_enqueue_script('wp-api');
}
add_action('wp_enqueue_scripts', 'advset_api_settings', 1000);
add_action('admin_enqueue_scripts', 'advset_api_settings', 1000);







/**
 * Add modal HTML to admin footer
 */
function advset_admin_modal_html() {
    // Only show for administrators
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Get the loading SVG content
    $loading_path = ADVSET_DIR . '/admin-ui/images/loading.svg';
    $loading_svg = '';
    if (file_exists($loading_path)) {
        $loading_svg = file_get_contents($loading_path);
    }

    // Get the close SVG content
    $close_path = ADVSET_DIR . '/admin-ui/images/close.svg';
    $close_svg = '';
    if (file_exists($close_path)) {
        $close_svg = file_get_contents($close_path);
    }
    
    ?>
    <dialog id="advset-admin-modal" class="advset-setup advset-modal" aria-labelledby="advset-modal-title">
        <div class="advset-modal-content">
            <header class="advset-modal-header">
                <div class="advset-modal-header-top">
                    <h2 class="advset-modal-title"><?php _e('Advanced Settings', 'advanced-settings'); ?></h2>
                    <div class="advset-modal-search">
                        <input type="search" id="advset-modal-search" autofocus placeholder="<?php _e('Search …', 'advanced-settings'); ?>">
                    </div>
                    <div class="advset-modal-header-right">
                        <button class="advset-modal-close" aria-label="<?php _e('Close', 'advanced-settings'); ?>"><?php echo $close_svg; ?></button>
                    </div>
                </div>
            </header>
            <div class="advset-modal-body">
                <div class="advset-modal-body-content"></div>
                <div class="advset-modal-body-loading" aria-label="<?php _e('loading settings …', 'advanced-settings'); ?>">
                    <?php echo $loading_svg; ?>
                </div>
                <div class="advset-no-results" style="display: none;">
                    <p><?php _e('No results found', 'advanced-settings'); ?></p>
                </div>
            </div>
        </div>
    </dialog>
    <?php
}
add_action('admin_footer', 'advset_admin_modal_html');
add_action('wp_footer', 'advset_admin_modal_html');







/**
 * Handle AJAX request to mark guide as shown
 */
function advset_mark_guide_shown() {
    // Verify nonce and capabilities
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'advset-admin-ui-nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    update_option('advset_guide_shown', true);
    wp_send_json_success();
}
add_action('wp_ajax_advset_mark_guide_shown', 'advset_mark_guide_shown');






