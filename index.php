<?php
/*
Plugin Name: Advanced Settings
Plugin URI: https://wordpress.org/plugins/advanced-settings/
Description: Advanced settings for WordPress.
Author: Helmut Wandl
Author URI: https://ehtmlu.com/
Version: 2.5.0
Requires at least: 5.0.0
Requires PHP: 5.3
*/

// Exit direct requests
if (!defined('ABSPATH')) exit;

define('ADVSET_DIR', dirname(__FILE__));

# THE ADMIN PAGE
function advset_page() {
	switch ($_GET['tab'] ?? null) {
		case 'admin-code': include ADVSET_DIR.'/admin-code.php'; break;
		case 'admin-post-types': include ADVSET_DIR.'/admin-post-types.php'; break;
		case 'admin-scripts': include ADVSET_DIR.'/admin-scripts.php'; break;
		case 'admin-styles': include ADVSET_DIR.'/admin-styles.php'; break;
		case 'admin-filters': include ADVSET_DIR.'/admin-filters.php'; break;
		case 'admin-get-in-touch': include ADVSET_DIR.'/admin-get-in-touch.php'; break;
		case 'admin-system':
		default: include ADVSET_DIR.'/admin-system.php'; break;
	}
}

// from https://stevegrunwell.com/blog/quick-tip-is_login_page-function-for-wordpress/
if ( ! function_exists( 'is_admin_area' ) ) {
  function is_admin_area() {
    return is_admin() || in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) );
  }
}

if( is_admin() ) {

	define('ADVSET_URL', 'https://wordpress.org/plugins/advanced-settings/');

	# Admin menu
	add_action('admin_menu', 'advset_menu');

	# Add plugin option in Plugins page
	add_filter( 'plugin_action_links', 'advset_plugin_action_links', 10, 2 );

	// Update the database for plugin versions greater than 2.0 (the change occurred in version 2.1)
	if( $settings=get_option('powerconfigs') ) {
		update_option('advset_code', $settings);
		update_option('advset_system', $settings);
		update_option('advset_remove_filters', $settings['remove_filters']);
		delete_option('powerconfigs');
	}

	// update settings
	if( isset($_POST['option_page']) && $_POST['option_page']=='advanced-settings' ) {

		function advset_update() {

			// security
			if( !current_user_can('manage_options') )
				return;

			// define option name
			$setup_name = 'advset_'.$_POST['advset_group'];

			// prepare option group
			$_POST[$setup_name] = $_POST;

			unset(
				$_POST[$setup_name]['option_page'],
				$_POST[$setup_name]['action'],
				$_POST[$setup_name]['_wpnonce'],
				$_POST[$setup_name]['_wp_http_referer'],
				$_POST[$setup_name]['submit']
			);

			if( !empty($_POST[$setup_name]['auto_thumbs']) )
				$_POST[$setup_name]['add_thumbs'] = '1';

			if( !empty($_POST[$setup_name]['remove_widget_system']) )
				$_POST[$setup_name]['remove_default_wp_widgets'] = '1';

			// save settings
			register_setting( 'advanced-settings', $setup_name );

		}
		add_action( 'admin_init', 'advset_update' );
	}

}

// get a advanced-settings option
function advset_option( $option_name, $default='' ) {
	global $advset_options;

	if( !isset($advset_options) )
		$advset_options = get_option('advset_code', array()) + get_option('advset_system', array()) + get_option('advset_scripts', array()) + get_option('advset_styles', array());

	if( isset($advset_options[$option_name]) )
		return $advset_options[$option_name];
	else
		return $default;
}

function advset_check_if( $option_name, $echo=true ) {
	if ( advset_option( $option_name, 0 ) ) {
		if ($echo) {
			echo ' checked="checked"';
		}
		else {
			return ' checked="checked"';
		}
	}
}

# ADMIN MENU
function advset_menu() {
	$title = '💚 ' . __('Advanced') . ' …';
	add_options_page($title, $title, 'manage_options', 'advanced-settings', 'advset_page');
}

# ADMIN PAGE TABS
function advset_page_header() {
	$active_tab = $_GET['tab'] ?? '';
	?>
	<style>
		.nav-tab-wrapper.show-expert-settings .toggle-expert-settings-show,
		.nav-tab-wrapper:not(.show-expert-settings) .toggle-expert-settings-hide,
		.nav-tab-wrapper:not(.show-expert-settings) .expert-setting {
			display: none;
		}
		.expert-setting,
		.toggle-expert-settings a {
			color: #c60;
			transition: color .3s;
		}
		.nav-tab-wrapper:not(.show-expert-settings) .toggle-expert-settings a {
			color: #999;
		}
		.toggle-expert-settings a:hover,
		.toggle-expert-settings a:focus-visible {
			color: currentColor !important;
		}
		.toggle-expert-settings {
			float: left;
			padding: 5px 10px;
			border-top: 1px solid transparent;
			line-height: 1.71428571;
			font-size: 14px;
			margin-left: .5em;
		}
		.toggle-expert-settings a {
			outline: none;
			box-shadow: none;
			text-decoration: none;
		}

		.heart {
			font-size: 2rem;
			display: inline-block;
			animation: heartbeat 1.5s ease-in-out infinite;
		}
		@keyframes heartbeat {
			0% {
				transform: scale(1);
			}
			15% {
				transform: scale(1.15);
			}
			30% {
				transform: scale(1);
			}
			45% {
				transform: scale(1.1);
			}
			60% {
				transform: scale(1);
			}
			100% {
				transform: scale(1);
			}
		}
	</style>
	<div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 2rem; align-items: flex-start; ">
		<div style="flex-grow: 1; ">
			<h1><?php _e('Settings'); echo ' &rsaquo; '; _e('Advanced'); ?></h1>
		</div>
		<div style="border: #3c3 solid 2px; background: #fff; padding: 1rem; border-radius: .5rem; display: flex; gap: 1rem; font-size: 1rem; line-height: 1.4; "><span class="heart">💚</span><span>This plugin is currently being extensively revised.<br />If you have any questions or wishes, just <a href="?page=advanced-settings&tab=admin-get-in-touch">get in touch</a>.</span></div>
	</div>
	<nav class="nav-tab-wrapper<?php echo empty($_COOKIE['advset_show_expert_settings']) ? '' : ' show-expert-settings' ?>">
		<a href="?page=advanced-settings" class="nav-tab <?php echo $active_tab === '' ? 'nav-tab-active' : ''; ?>"><?php echo __('System') ?></a>
		<a href="?page=advanced-settings&tab=admin-code" class="nav-tab <?php echo $active_tab === 'admin-code' ? 'nav-tab-active' : ''; ?>"><?php echo __('HTML Code') ?></a>
		<a href="?page=advanced-settings&tab=admin-scripts" class="expert-setting nav-tab <?php echo $active_tab === 'admin-scripts' ? 'nav-tab-active' : ''; ?>"><?php echo __('Scripts') ?></a>
		<a href="?page=advanced-settings&tab=admin-styles" class="expert-setting nav-tab <?php echo $active_tab === 'admin-styles' ? 'nav-tab-active' : ''; ?>"><?php echo __('Styles') ?></a>
		<a href="?page=advanced-settings&tab=admin-post-types" class="expert-setting nav-tab <?php echo $active_tab === 'admin-post-types' ? 'nav-tab-active' : ''; ?>"><?php echo __('Post Types') ?></a>
		<a href="?page=advanced-settings&tab=admin-filters" class="expert-setting nav-tab <?php echo $active_tab === 'admin-filters' ? 'nav-tab-active' : ''; ?>"><?php echo __('Filters/Actions') ?></a>
		<div class="toggle-expert-settings"><a href="javascript:;" onclick="const className = 'show-expert-settings', classList = this.closest('nav').classList, setTo = !classList.contains(className); classList.toggle(className, setTo); document.cookie = 'advset_show_expert_settings=' + (setTo ? '1' : '0') + '; path=<?php echo htmlspecialchars(parse_url(admin_url(), PHP_URL_PATH)); ?>; max-age=' + (setTo ? '31536000' : '0'); return false; "><span class="toggle-expert-settings-show">→ show expert settings</span><span class="toggle-expert-settings-hide">← hide expert settings</span></a></div>
	</nav>
	<style>

		.deprecated {
			background: #900;
			color: #fff;
			padding: 0 .5rem;
			display: inline-block;
			border-radius: 3px;
		}

		.experimental {
			background: #39f;
			color: #fff;
			padding: 0 .5rem;
			display: inline-block;
			border-radius: 3px;
			font-size: 14px;
			line-height: 1.4;
		}

	</style>
	<?php
}

function advset_page_deprecated() {
	return '<br />&nbsp; &nbsp; &nbsp; <strong class="deprecated">' . __('DEPRECATED') . '</strong> <span style="color: #900; ">' . __('This option will be removed in an upcoming version.') . '</span>';
}

function advset_page_experimental() {
	return ' <strong class="experimental">' . __('EXPERIMENTAL') . '</strong>';
}

# Add plugin option in Plugins page
function advset_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( basename(dirname(__FILE__)).'/index.php' ) ) {
		$links[] = '<a href="options-general.php?page=advanced-settings">'.__('Settings').'</a>';
	}

	return $links;
}

# Disable The “Please Update Now” Message On WordPress Dashboard
if ( advset_option('hide_update_message') ) {
	add_action( 'admin_menu', '__advsettings_hide_update_message', 2 );
	function __advsettings_hide_update_message() {
		remove_action( 'admin_notices', 'update_nag', 3 );
	}
}

# Add a Custom Dashboard Logo
# from https://www.codementor.io/wordpress/tutorial/wordpress-functions-php-cheatsheet
if ( advset_option('dashboard_logo') ) {
	add_action('wp_before_admin_bar_render', '__advsettings_dashboard_logo');
	function __advsettings_dashboard_logo() {
		if(is_admin())
			echo '
<style>
#wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
	background-image: url('.advset_option('dashboard_logo').') !important;
	background-position: 0 0;
	color:rgba(0, 0, 0, 0);
}
#wpadminbar #wp-admin-bar-wp-logo.hover > .ab-item .ab-icon {
	background-position: 0 0;
}
</style>
';
	}
}

# Remove Trackbacks and Pingbacks from Comment Count
# from https://www.codementor.io/wordpress/tutorial/wordpress-functions-php-cheatsheet
if ( advset_option('remove_pingbacks_trackbacks_count') ) {
	add_filter('get_comments_number', '__advsettings_comment_count', 0);
	function __advsettings_comment_count( $count ) {
		if ( ! is_admin_area() ) {
			global $id;
			$comments = get_comments('status=approve&post_id=' . $id);
			$comments_by_type = separate_comments($comments);
			return count($comments_by_type['comment']);
		}
		else {
			return $count;
		}
	}
}

# Remove admin menu
if( advset_option('remove_menu') )
	add_filter('show_admin_bar' , '__return_false'); // Remove admin menu

# Configure FeedBurner
if( advset_option('feedburner') ) {
	function appthemes_custom_rss_feed( $output, $feed ) {

		if ( strpos( $output, 'comments' ) )
			return $output;

		if( strpos(advset_option('feedburner'), '/')===FALSE )
			return esc_url( 'https://feeds.feedburner.com/'.advset_option('feedburner') );
		else
			return esc_url( advset_option('feedburner') );
	}
	add_action( 'feed_link', 'appthemes_custom_rss_feed', 10, 2 );
}

# Favicon
if( advset_option('favicon') ) {

	function __advsettings_favicon() {
		if( file_exists(TEMPLATEPATH.'/favicon.ico') )
			echo '<link rel="shortcut icon" href="'.get_bloginfo('template_url').'/favicon.ico'.'">'."\r\n";
		elseif( file_exists(TEMPLATEPATH.'/favicon.png') )
			echo '<link rel="shortcut icon" type="image/png" href="'.get_bloginfo('template_url').'/favicon.png'.'">'."\r\n";
	}
	add_action( 'wp_head', '__advsettings_favicon' );
}

# Add blog description meta tag
if( advset_option('description') ) {
	function __advsettings_blog_description() {
		if(is_home() || !advset_option('single_metas'))
			echo '<meta name="description" content="'.get_bloginfo('description').'" />'."\r\n";
	}
	add_action( 'wp_head', '__advsettings_blog_description' );
}

# Add description and keyword meta tag in posts
if( advset_option('single_metas') ) {
	function __advsettings_single_metas() {
		global $post;
		if( is_single() || is_page() ) {

			$tag_list = get_the_terms( $post->ID, 'post_tag' );

			if( $tag_list ) {
				foreach( $tag_list as $tag )
					$tag_array[] = $tag->name;
				echo '<meta name="keywords" content="'.implode(', ', $tag_array).'" />'."\r\n";
			}

			$excerpt = strip_tags($post->post_content);
			$excerpt = strip_shortcodes($excerpt);
			$excerpt = str_replace(array('\n', '\r', '\t'), ' ', $excerpt);
			$excerpt = substr($excerpt, 0, 125);
			if( !empty($excerpt) )
				echo '<meta name="description" content="'.$excerpt.'" />'."\r\n";
		}
	}
	add_action( 'wp_head', '__advsettings_single_metas' );
}

# Remove header generator
if( advset_option('remove_generator') )
	remove_action('wp_head', 'wp_generator');

# Remove WLW
if( advset_option('remove_wlw') )
	remove_action('wp_head', 'wlwmanifest_link');

# Thumbnails support
if( advset_option('add_thumbs') ) {
	function __advsettings_add_thumbs(){
		add_theme_support( 'post-thumbnails' );
	}
	add_action('after_setup_theme', '__advsettings_add_thumbs');
	if( !current_theme_supports('post-thumbnails') )
		define( 'ADVSET_THUMBS', '1' );
}

# JPEG Quality
if( advset_option('jpeg_quality', 0)>0 && $_SERVER['HTTP_HOST']!='localhost' ) {
	add_filter('jpeg_quality', '__advsettings_jpeg_quality');
	function __advsettings_jpeg_quality(){
		return (int) advset_option('jpeg_quality');
	}
}

# REL External
if( advset_option('rel_external') ) {
	function ____replace_targets( $content ) {
		$content = str_replace('target="_self"', '', $content);
		return str_replace('target="_blank"', 'rel="external"', $content);
	}
	add_filter( 'the_content', '____replace_targets' );
}

# Fix post type pagination
if( advset_option('post_type_pag') ) {
	# following are code adapted from Custom Post Type Category Pagination Fix by jdantzer
	function fix_category_pagination($qs){
		if(isset($qs['category_name']) && isset($qs['paged'])){
			$qs['post_type'] = get_post_types($args = array(
				'public'   => true,
				'_builtin' => false
			));
			array_push($qs['post_type'],'post');
		}
		return $qs;
	}
	add_filter('request', 'fix_category_pagination');
}

# Disable auto save
if( advset_option('disable_auto_save') ) {
	define('AUTOSAVE_INTERVAL', 60 * 60 * 24 * 365 * 100); // save interval => 100 years
}

# Remove wptexturize
if( advset_option('remove_wptexturize') ) {
	remove_filter('the_content', 'wptexturize');
	remove_filter('comment_text', 'wptexturize');
	remove_filter('the_excerpt', 'wptexturize');
}

# Filtering the code
if( advset_option('compress') || advset_option('remove_comments') ) {
	add_action('template_redirect','____template');
	function ____template() { ob_start('____template2'); }
	function ____template2($code) {

		# dont remove conditional IE comments "<!--[if IE]>"
		if( advset_option('remove_comments') )
			$code = preg_replace('/<!--[^\[\>\<](.|\s)*?-->/', '', $code);
			/* exemples:
			 * <!--[if IE]>
			 * <!--<![endif]-->
			 * <!--[if gt IE 9]><!--> [html code] ...
			 * old code replaced: $code = preg_replace('/<!--(.|\s)*?-->/', '', $code);
			 * */

		if( advset_option('compress') )
			$code = trim( preg_replace( '/\s+(?![^<>]*<\/pre>)/', ' ', $code ) );

		return $code;
	}
}

# Remove comments system
if( advset_option('remove_comments_system') ) {
	function __av_comments_close( $open, $post_id ) {

		#$post = get_post( $post_id );
		#if ( 'page' == $post->post_type )
			#$open = false;

		return false;
	}
	add_filter( 'comments_open', '__av_comments_close', 10, 2 );

	function __av_empty_comments_array( $open, $post_id ) {
		return array();
	}
	add_filter( 'comments_array', '__av_empty_comments_array', 10, 2 );

	// Removes from admin menu
	function __av_remove_admin_menus() {
		remove_menu_page( 'edit-comments.php' );
	}
	add_action( 'admin_menu', '__av_remove_admin_menus' );

	// Removes from admin bar
	function __av_admin_bar_render() {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('comments');
	}
	add_action( 'wp_before_admin_bar_render', '__av_admin_bar_render' );
}

# Disable author pages
if ( advset_option('disable_author_pages') ) {
    function advset_disable_author_pages__template_redirect() {
        global $wp_query;
        if ( is_author() ) {
            $wp_query->set_404();
            status_header(404);
        }
    }
    add_action( 'template_redirect', 'advset_disable_author_pages__template_redirect' );
    function advset_disable_author_pages__wp_sitemaps_add_provider( $provider, $name ) {
        if ( 'users' === $name ) {
            return false;
        }
        return $provider;
    }
    add_filter( 'wp_sitemaps_add_provider', 'advset_disable_author_pages__wp_sitemaps_add_provider', 10, 2 );
}

# Google Analytics
if( advset_option('analytics') ) {
	add_action('wp_footer', '____analytics'); // Load custom styles
	function ____analytics(){
		$ga_code = advset_option('analytics');
		echo "<script async src=\"https://www.googletagmanager.com/gtag/js?id=$ga_code\"></script>
<script>window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '$ga_code')";
	}
}

# core upgrade skip new bundled
if ( advset_option('core_upgrade_skip_new_bundled') ) {
    if (!defined('CORE_UPGRADE_SKIP_NEW_BUNDLED')) {
        define('CORE_UPGRADE_SKIP_NEW_BUNDLED', true);
    }
}

# Remove admin menu
if( advset_option('show_query_num') ) {
	function __show_sql_query_num(){

		if( !current_user_can('manage_options') )
			return;

		global $wpdb;

		echo '<div style="font-size:10px;text-align:center">'.
				$wpdb->num_queries.' '.__('SQL queries have been executed to show this page in ').
				timer_stop().__('seconds').
			'</div>';
	}
	add_action('wp_footer', '__show_sql_query_num');
}

# Remove [...] from the excerpt
/*if( $configs['remove_etc'] ) {
	function __trim_excerpt( $text ) {
		return rtrim( $text, '[...]' );
	}
	add_filter('get_the_excerpt', '__trim_excerpt');
}*/

# author_bio
if( advset_option('author_bio') ) {
	function advset_author_bio ($content=''){
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
	}
	add_filter('the_content', 'advset_author_bio');
}

# author_bio_html
if( advset_option('author_bio_html') )
	remove_filter('pre_user_description', 'wp_filter_kses');

# remove_widget_system
if( advset_option('remove_default_wp_widgets') || advset_option('remove_widget_system') ) {

	function advset_unregister_default_wp_widgets() {
		unregister_widget('WP_Widget_Pages');
		unregister_widget('WP_Widget_Calendar');
		unregister_widget('WP_Widget_Archives');
		unregister_widget('WP_Widget_Links');
		unregister_widget('WP_Widget_Meta');
		unregister_widget('WP_Widget_Search');
		unregister_widget('WP_Widget_Text');
		unregister_widget('WP_Widget_Categories');
		unregister_widget('WP_Widget_Recent_Posts');
		unregister_widget('WP_Widget_Recent_Comments');
		unregister_widget('WP_Widget_RSS');
		unregister_widget('WP_Widget_Tag_Cloud');
	}
	add_action('widgets_init', 'advset_unregister_default_wp_widgets', 1);
}

# remove_widget_system
if( advset_option('remove_widget_system') ) {

	# this maybe dont work properly
	function advset_remove_widget_support() {
		remove_theme_support( 'widgets' );
	}
	add_action( 'after_setup_theme', 'advset_remove_widget_support', 11 );

	# it works fine
	function advset_remove_widget_system() {
		global $wp_widget_factory;
		$wp_widget_factory->widgets = array();

	}
	add_action('widgets_init', 'advset_remove_widget_system', 1);

	# this maybe dont work properly
	function disable_all_widgets( $sidebars_widgets ) {
		$sidebars_widgets = array( false );
		return $sidebars_widgets;
	}
	add_filter( 'sidebars_widgets', 'disable_all_widgets' );

	# remove widgets from menu
	function advset_remove_widgets_from_menu() {
	  $page = remove_submenu_page( 'themes.php', 'widgets.php' );
	}
	add_action( 'admin_menu', 'advset_remove_widgets_from_menu', 999 );
}

# auto post thumbnails
if( advset_option('auto_thumbs') ) {

	// based on "auto posts plugin" 3.3.2

	// check post status
	function advset_check_post_status( $new_status, $old_status, $post ) {
		if ('publish' == $new_status) {
			advset_publish_post($post);
		}
	}

	//
	function advset_publish_post( $post ) {
		global $wpdb;

		// First check whether Post Thumbnail is already set for this post.
		if (get_post_meta($post->ID, '_thumbnail_id', true) || get_post_meta($post->ID, 'skip_post_thumb', true))
			return;

		// Initialize variable used to store list of matched images as per provided regular expression
		$matches = array();

		// Get all images from post's body
		preg_match_all('/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'>]*)[^\>]*/i', empty($post->post_content) ? '' : $post->post_content, $matches);

		if (count($matches)) {
			foreach ($matches[0] as $key => $image) {
				/**
				 * If the image is from wordpress's own media gallery, then it appends the thumbmail id to a css class.
				 * Look for this id in the IMG tag.
				 */
				preg_match('/wp-image-([\d]*)/i', $image, $thumb_id);
				$thumb_id = empty($thumb_id[1]) ? null : $thumb_id[1];

				// If thumb id is not found, try to look for the image in DB. Thanks to "Erwin Vrolijk" for providing this code.
				if (!$thumb_id) {
					$image = $matches[1][$key];
					$result = $wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid = %s", $image));
					$thumb_id = empty($result[0]->ID) ? null : $result[0]->ID;
				}

				// Ok. Still no id found. Some other way used to insert the image in post. Now we must fetch the image from URL and do the needful.
				if (!$thumb_id) {
					$thumb_id = advset_generate_post_thumbnail($matches, $key, $post);
				}

				// If we succeed in generating thumg, let's update post meta
				if ($thumb_id) {
					update_post_meta( $post->ID, '_thumbnail_id', $thumb_id );
					break;
				}
			}
		}
	}


	function advset_generate_post_thumbnail( $matches, $key, $post ) {
		// Make sure to assign correct title to the image. Extract it from img tag
		$imageTitle = '';
		preg_match_all('/<\s*img [^\>]*title\s*=\s*[\""\']?([^\""\'>]*)/i', empty($post->post_content) ? '' : $post->post_content, $matchesTitle);

		if (count($matchesTitle) && isset($matchesTitle[1])) {
			$imageTitle = empty($matchesTitle[1][$key]) ? '' : $matchesTitle[1][$key];
		}

		// Get the URL now for further processing
		$imageUrl = $matches[1][$key];

		// Get the file name
		$filename = substr($imageUrl, (strrpos($imageUrl, '/'))+1);

		if ( !(($uploads = wp_upload_dir(current_time('mysql')) ) && false === $uploads['error']) )
			return null;

		// Generate unique file name
		$filename = wp_unique_filename( $uploads['path'], $filename );

		// Move the file to the uploads dir
		$new_file = $uploads['path'] . "/$filename";

		if (!ini_get('allow_url_fopen'))
			$file_data = curl_get_file_contents($imageUrl);
		else
			$file_data = @file_get_contents($imageUrl);

		if (!$file_data) {
			return null;
		}

		file_put_contents($new_file, $file_data);

		// Set correct file permissions
		$stat = stat( dirname( $new_file ));
		$perms = $stat['mode'] & 0000666;
		@ chmod( $new_file, $perms );

		// Get the file type. Must to use it as a post thumbnail.
		$wp_filetype = wp_check_filetype( $filename );

		extract( $wp_filetype );

		// No file type! No point to proceed further
		if ( ( !$type || !$ext ) && !current_user_can( 'unfiltered_upload' ) ) {
			return null;
		}

		// Compute the URL
		$url = $uploads['url'] . "/$filename";

		// Construct the attachment array
		$attachment = array(
			'post_mime_type' => $type,
			'guid' => $url,
			'post_parent' => null,
			'post_title' => $imageTitle,
			'post_content' => '',
		);

		$thumb_id = wp_insert_attachment($attachment, false, $post->ID);
		if ( !is_wp_error($thumb_id) ) {
			require_once(ABSPATH . '/wp-admin/includes/image.php');

			// Added fix by misthero as suggested
			wp_update_attachment_metadata( $thumb_id, wp_generate_attachment_metadata( $thumb_id, $new_file ) );
			update_attached_file( $thumb_id, $new_file );

			return $thumb_id;
		}

		return null;
   	}

	add_action('transition_post_status', 'advset_check_post_status', 10, 3);

	if( !function_exists('curl_get_file_contents') ) {

		function curl_get_file_contents($URL) {
			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_URL, $URL);
			$contents = curl_exec($c);
			curl_close($c);

			if ($contents) {
				return $contents;
			}

			return FALSE;
		}

	}

}

# excerpt length
if( advset_option('excerpt_limit') ) {
	function advset_excerpt_length_limit($length) {
		return advset_option('excerpt_limit');
	}
	add_filter( 'excerpt_length', 'advset_excerpt_length_limit', 5 );
}

# excerpt read more link
if( advset_option('excerpt_more_text') ) {
	function excerpt_read_more_link() {
		return '... <a class="excerpt-read-more" href="' . get_permalink() . '">&nbsp;'.advset_option('excerpt_more_text').' +&nbsp;</a>';
	}
	add_filter('excerpt_more', 'excerpt_read_more_link');
}

# remove jquery migrate script
if( !is_admin_area() && advset_option('jquery_remove_migrate') ) {
	function advset_remove_jquery_migrate(&$scripts) {
		$scripts->remove( 'jquery');
		$scripts->add( 'jquery', false, array( 'jquery-core' ), '1.10.2' );
	}
	add_action('wp_default_scripts', 'advset_remove_jquery_migrate');
}

# include jquery google cdn instead local script
if( advset_option('jquery_cnd') ) {
	function advset_jquery_cnd() {
		wp_deregister_script('jquery');
		wp_register_script('jquery', ("https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"), false);
		wp_enqueue_script('jquery');
	}
	add_action('wp_enqueue_scripts', 'advset_jquery_cnd');
}

# facebook og metas
if( !is_admin_area() && advset_option('facebook_og_metas') ) {
	function advset_facebook_og_metas() {
		global $post;
		if (is_single() || is_page()) { ?>
			<meta property="og:title" content="<?php single_post_title(''); ?>" />
			<meta property="og:description" content="<?php echo strip_tags(get_the_excerpt($post->ID)); ?>" />
			<meta property="og:type" content="article" />
			<meta property="og:image" content="<?php if (function_exists('wp_get_attachment_thumb_url')) {echo wp_get_attachment_url(get_post_thumbnail_id($post->ID)); }?>" />
		<?php }
	}
	add_action('wp_head', 'advset_facebook_og_metas');
}

# remove shortlink metatag
if( !is_admin_area() && advset_option('remove_shortlink') ) {
	remove_action( 'wp_head', 'wp_shortlink_wp_head');
}

# remove rsd metatag
if( !is_admin_area() && advset_option('remove_rsd') ) {
	remove_action ('wp_head', 'rsd_link');
}

# configure wp_title
if( advset_option('config_wp_title') ) {
	function advset_wp_title( $title, $sep ) {
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
	}
	add_filter( 'wp_title', 'advset_wp_title', 10, 2 );
}

// Scripts settings
require __DIR__.'/actions-scripts.php';
require __DIR__.'/actions-styles.php';



# image sizes
if( $_POST && (advset_option('max_image_size_w')>0 || advset_option('max_image_size_h')>0) ) {

	// From "Resize at Upload Plus" 1.3

	/* This function will apply changes to the uploaded file */
	function advset_resize_image( $array ) {
	  // $array contains file, url, type
	  if ($array['type'] == 'image/jpeg' OR $array['type'] == 'image/gif' OR $array['type'] == 'image/png') {
		// there is a file to handle, so include the class and get the variables
		require_once( dirname(__FILE__).'/class.resize.php' );
		$maxwidth = advset_option('max_image_size_w');
		$maxheight = advset_option('max_image_size_h');
		$imagesize = getimagesize($array['file']); // $imagesize[0] = width, $imagesize[1] = height

		if ( $maxwidth == 0 OR $maxheight == 0) {
			if ($maxwidth==0) {
				$objResize = new RVJ_ImageResize($array['file'], $array['file'], 'H', $maxheight);
			}
			if ($maxheight==0) {
				$objResize = new RVJ_ImageResize($array['file'], $array['file'], 'W', $maxwidth);
			}
		} else {
			if ( ($imagesize[0] >= $imagesize[1]) AND ($maxwidth * $imagesize[1] / $imagesize[0] <= $maxheight) )  {
				$objResize = new RVJ_ImageResize($array['file'], $array['file'], 'W', $maxwidth);
			} else {
				$objResize = new RVJ_ImageResize($array['file'], $array['file'], 'H', $maxheight);
			}
		}
	  } // if
	  return $array;
	} // function
	add_action('wp_handle_upload', 'advset_resize_image');

}

# remove filters if not in filters admin page
$remove_filters = get_option( 'advset_remove_filters' );
if( !isset($_GET['page'])
	|| $_GET['page']!='advanced-settings-filters' && is_array($remove_filters) ) {

	if( isset($remove_filters) && is_array($remove_filters) )
		foreach( $remove_filters as $tag=>$array )
			if( is_array($array) )
				foreach( $array as $function=>$_ )
					//echo "$tag=>".$function.'<br />';
					remove_filter( $tag, $function );
}

// translate to pt_BR
if( is_admin() && get_locale()==='pt_BR' ) {
	add_filter( 'gettext', 'advset_translate', 10, 3 );
	global $advset_ptbr;

	$advset_ptbr = array(
		'Tracked Scripts <br /> <i style="color:#999">Check to remove scripts</i>' => 'Scripts rastreados <br /> <i style="color:#999">Marque para remover scripts</i>',
		'Options' => 'Opções',
		'Track' => 'Rastrear',
		'Track enqueued scripts' => 'Rastrear scripts incluídos no site',
		'Load merged removed scripts in footer' => 'Carregar scripts removidos que foram juntados no rodapé',
		'Merge and include removed scripts' => 'Juntar and incluir scripts removidos num único arquivo',
		'Remove <i>type="text/javascript"</i> attribute from &lt;script&gt; tag' => 'Remove <i>type="text/javascript"</i> da tag de &lt;script&gt;',
		'Remove Trackbacks and Pingbacks from Comment Count' => 'Remove trackbacks e pingbacks dos comentários',
		'Limit the excerpt length to' => 'Limitar o tamanho da descrição em',
		'words' => 'palavras',
		'Add a read more link after excerpt with the text: ' => 'Adicionar "leia mais" depois da descrição com o texto: ',
		'Hide the WordPress update message in the Dashboard' => 'Ocultar mensagem de atualização do WordPress',
		'Configure site title to use just the wp_title() function (better for hardcode programming)' => 'Configurar título para usar a função wp_title() (para programadores)',
		'Be careful, removing a filter can destabilize your system. For security reasons, no filter removal has efects over this page.' => 'Cuidado! Remover um filtro pode desestabilizar seu sistema. Por segurança, nenhum filtro removido terá efeito nesta página.',
		'it\'s don\'t remove conditional IE comments like' => 'não remove os comentários condicionais do IE, exemplo:',
		'Filters/Actions' => 'Filtros/Ações',
		'Save changes' => 'Salvar alterações',
		'width' => 'largura',
		'height' => 'altura',
		'Contents' => 'Conteúdo',
		'System' => 'Sistema',
		'HTML Code output' => 'Saída do código HTML',
		'Hide top admin menu' => 'Esconde menu de administrador do topo',
		'Automatically add a FavIcon' => 'Adicionar um FavIcon automático para a página',
		'whenever there is a favicon.ico or favicon.png file in the template folder' => 'sempre que houver um arquivo favicon.ico ou favicon.png na pasta do modelo',
		'Add a description meta tag using the blog description' => 'Adicionar uma meta tag de descrição usando a descrição do blog',
		'Add description and keywords meta tags in each posts' => 'Adicionar uma meta tags de descrição e palavras-chave em cada post',
		'Remove header WordPress generator meta tag' => 'Remover meta tag de "gerado pelo WordPress"',
		'Remove header WLW Manifest meta tag' => 'Remover meta tag WLW Manifest',
		'Current theme already has post thumbnail support' => 'Tema atual já tem suporte a imagem destacada (thumbnails)',
		'Automatically generate the Post Thumbnail' => 'Gerar imagem destacada automaticamente',
		'from the first image in post' => 'gera a partir da primeira imagem encontrada no post',
		'Set JPEG quality to' => 'Alterar qualidade do JPEG para',
		'when send and resize images' => 'no momento em que envia ou redimensiona imagens',
		'Resize image at upload to max size' => 'Redimensionar a imagem no upload no tamanho máximo',
		'if zero resize to max height or dont resize if both is zero' => 'Se zero, redimenciona para largura máxima ou nada faz se os dois valores forem zero',
		'if zero resize to max width or dont resize if both is zero' => 'Se zero, redimenciona para altura máxima ou nada faz se os dois valores forem zero',
		'Insert author bio in each post' => 'Adicionar descrição do autor em cada post',
		'Unregister default WordPress widgets' => 'Remover widgets padrões do WordPress',
		'removing some SQL queries can do the database work faster' => 'remove algumas consultas ao banco de dados, isto pode fazer o sistema rodar um pouco mais rápido',
		'Disable widget system' => 'Remover sistema de widgets',
		'Disable comment system' => 'Remover sistema de comentários',
		'Fix post type pagination' => 'Corrige paginação de "post types"',
		'Disable Posts Auto Saving' => 'Desabilita função de auto-salvar',
		'Compress all code' => 'Comprime todo o código',
		'transformations of quotes to smart quotes, apostrophes, dashes, ellipses, the trademark symbol, and the multiplication symbol' => 'estilização de áspas, apóstrofos, elípses, traços, e multiplicação dos símbolos',
		'Remove HTML comments' => 'Remover todos os comentários em HTML',
		'Display total number of executed SQL queries and page loading time <i style="color:#999">(only admin users can see this)' => 'Mostrar o total de SQLs executadas e o tempo de carregamento da página <i style="color:#999">(apenas administradores podem ver)',
		'only admin users can see this' => 'apenas administradores poderão ver',
		'inserts a javascript code in the footer' => 'adicionar um código em javascript no final do código HTML',
		'Allow HTML in user profile' => 'Permitir códigos HTML na descrição de perfil do autor',
		'Remove wptexturize filter' => 'Remove filtro de texturização',
		'Remove unnecessary jQuery migrate script (jquery-migrate.min.js)' => 'Remove script em desuso de migração de versão do jQuery (jquery-migrate.min.js)',
		'Include jQuery Google CDN instead local script (version 1.11.0)' => 'Inclui script jQuery do CDN do Google ao invés de usar arquivo local (versão 1.11.0)',
		'Fix incorrect Facebook thumbnails including OG metas' => 'Corrigir miniaturas do Facebook incluindo metas OG',
		'Remove header RSD (Weblog Client Link) meta tag' => 'Remover meta tag de RSD (Weblog Client Link)',
		'Remove header shortlink meta tag' => 'Remover meta tag de shortlink',
		'Remove header WLW Manifest meta tag (Windows Live Writer link)' => 'Remover meta tag de WLW Manifest (Windows Live Writer link)',
		//'' => '',
	);
}

function advset_translate( $text ) {

	global $advset_ptbr;

	$array = $advset_ptbr;

    if( isset($array[$text]) )
		return $array[$text];
	else
		return $text;
}


// -----------------------------------------------------------------------

add_action('wp_ajax_advset_filters', 'prefix_ajax_advset_filters');
function prefix_ajax_advset_filters() {
    //echo $_POST['tag'].' - '.$_POST['function'];

    // security
    if( !current_user_can('manage_options') )
		return false;

    $remove_filters = (array) get_option( 'advset_remove_filters' );
    $tag = (string)$_POST['tag'];
    $function = (string)$_POST['function'];

    if( $_POST['enable']=='true' )
		unset($remove_filters[$tag][$function]);
    else if ( $_POST['enable']=='false' )
		$remove_filters[$tag][$function] = 1;

    update_option( 'advset_remove_filters', $remove_filters );

    //echo $_POST['enable'];

    return true;
}

# Post Types
add_action( 'init', 'advset_register_post_types' );
function advset_register_post_types() {

	$post_types = (array) get_option( 'adv_post_types', array() );

	if( is_admin() && current_user_can('manage_options') && isset($_GET['delete_posttype']) ) {
		unset($post_types[$_GET['delete_posttype']]);
		update_option( 'adv_post_types', $post_types );
	}

	if( is_admin() && current_user_can('manage_options') && isset($_POST['advset_action_posttype']) ) {

		extract($_POST);

		$labels = array(
			'name' => $label,
			#'singular_name' => @$singular_name,
			#'add_new' => @$add_new,
			#'add_new_item' => @$add_new_item,
			#'edit_item' => @$edit_item,
			#'new_item' => @$new_item,
			#'all_items' => @$all_items,
			#'view_item' => @$view_item,
			#'search_items' => @$search_items,
			#'not_found' =>  @$not_found,
			#'not_found_in_trash' => @$not_found_in_trash,
			#'parent_item_colon' => @$parent_item_colon,
			#'menu_name' => @$menu_name
		);

		$post_types[$type] = array(
			'labels'              => $labels,
			'public'              => (bool) (isset($public) ? $public : false),
			'publicly_queryable'  => (bool) (isset($publicly_queryable) ? $publicly_queryable : false),
			'show_ui'             => (bool) (isset($show_ui) ? $show_ui : false),
			'show_in_menu'        => (bool) (isset($show_in_menu) ? $show_in_menu : false),
			'query_var'           => (bool) (isset($query_var) ? $query_var : false),
			#'rewrite'             => array( 'slug' => 'book' ),
			#'capability_type'     => 'post',
			'has_archive'         => (bool) (isset($has_archive) ? $has_archive : false),
			'hierarchical'        => (bool) (isset($hierarchical) ? $hierarchical : false),
			#'menu_position'       => (int)@$menu_position,
			'supports'            => (array) (empty($supports) ? [] : $supports),
			'taxonomies'          => (array) (empty($taxonomies) ? [] : $taxonomies),
		);

		update_option( 'adv_post_types', $post_types );

	}
	#print_r($post_types);
	if( sizeof($post_types)>0 )
		foreach( $post_types as $post_type=>$args ) {
			register_post_type( $post_type, $args );
			if( in_array( 'thumbnail', $args['supports'] ) ) {
				add_theme_support( 'post-thumbnails', array( $post_type, 'post' ) );
				/*global $_wp_theme_features;

				if( !is_array($_wp_theme_features[ 'post-thumbnails' ]) )
					$_wp_theme_features[ 'post-thumbnails' ] = array();

				$_wp_theme_features[ 'post-thumbnails' ][0][]= $post_type;*/

				#print_r($_wp_theme_features[ 'post-thumbnails' ]);
			}
		}

}

