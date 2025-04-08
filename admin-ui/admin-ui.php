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
    $wp_admin_bar->add_menu(array(
        'id'    => 'advset-admin-icon',
        'title' => '<span class="ab-icon">' . $icon_svg . '</span><span class="ab-label">Advanced Settings</span>',
        'href'  => 'javascript:void(0);',
        'meta'  => array(
            'class' => 'advset-admin-icon',
            'onclick' => 'advset_open_modal(); return false;'
        )
    ));
}
add_action('admin_bar_menu', 'advset_admin_bar_icon', 100);




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
    wp_enqueue_script('advset-admin-ui');
    
    // Register React app assets (but don't enqueue them yet)
    wp_register_style(
        'advset-react-app',
        plugins_url('react/css/app.css', __FILE__),
        [],
        filemtime(ADVSET_DIR . '/admin-ui/react/css/app.css')
    );
    
    wp_register_script(
        'advset-react-app',
        plugins_url('react/js/app.js', __FILE__),
        [],
        filemtime(ADVSET_DIR . '/admin-ui/react/js/app.js'),
        true
    );
    
    wp_register_script(
        'advset-react-components',
        plugins_url('react/js/components/index.js', __FILE__),
        ['advset-react-app'],
        filemtime(ADVSET_DIR . '/admin-ui/react/js/components/index.js'),
        true
    );
    
    // Localize script with data
    wp_localize_script('advset-admin-ui', 'advsetAdminUI', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('advset-admin-ui-nonce'),
        'reactAppUrl' => plugins_url('react/js/app.js', __FILE__),
        'reactComponentsUrl' => plugins_url('react/js/components/index.js', __FILE__),
        'reactAppCssUrl' => plugins_url('react/css/app.css', __FILE__),
        'componentRegistryUrl' => plugins_url('react/js/components/ComponentRegistry.js', __FILE__),
        'genericToggleUrl' => plugins_url('react/js/components/GenericToggle.js', __FILE__)
    ));
}
add_action('admin_enqueue_scripts', 'advset_admin_ui_scripts');
add_action('wp_enqueue_scripts', 'advset_admin_ui_scripts');




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
    <dialog id="advset-admin-modal" class="advset-modal" aria-labelledby="advset-modal-title">
        <div class="advset-modal-content">
            <header class="advset-modal-header">
                <div class="advset-modal-header-top">
                    <h2 class="advset-modal-title"><?php _e('Advanced Settings', 'advanced-settings'); ?></h2>
                    <button class="advset-modal-close" aria-label="<?php _e('Close', 'advanced-settings'); ?>"><?php echo $close_svg; ?></button>
                </div>
                <div class="advset-modal-search">
                    <input type="search" id="advset-modal-search" autofocus placeholder="<?php _e('Search …', 'advanced-settings'); ?>">
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
 * AJAX handler for modal content
 */
function advset_get_modal_content() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'advset-admin-ui-nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    // Get modal content
    $content = '';
    
    wp_send_json_success(array('content' => $content));
}
add_action('wp_ajax_advset_get_modal_content', 'advset_get_modal_content');


