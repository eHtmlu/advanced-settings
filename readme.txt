=== Advanced Settings ===
Plugin Name: Advanced Settings
Author: eHtmlu
Contributors: eHtmlu, webarthur
Author URI: https://ehtmlu.com/
Plugin URI: https://wordpress.org/plugins/advanced-settings/
Tags: settings, options, performance, speed, admin
Requires at least: 5.0.0
Tested up to: 6.7
Stable tag: 2.7.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Get advanced settings and change all you imagine that are not provided by WordPress.

== Description ==

This plugin offers settings that you might expect to find in the WordPress core.

💚 This plugin is currently being extensively revised, but it remains high performing, lightweight and largely backwards compatible. If you have any questions or wishes, just get in touch, for example by creating a topic on the [plugin support page](https://wordpress.org/support/plugin/advanced-settings/#new-topic-0).

= Post types =

* Manage/create/edit
* Add supports: title, editor, author, thumbnail, excerpt, trackbacks, custom fields, comments, revisions, page attributes, etc.
* Configure: hierarchical, has_archive, query_var, show_in_menu, show_ui, publicly_queryable, public, etc.
* Taxonomies: category, post_tag

= HTML Code =

* Fix incorrect Facebook thumbnails including OG metas
* Hide top admin menu
* Automatically add a FavIcon (whenever there is a favicon.ico, favicon.png or favicon.svg file in the template folder)
* Add a description meta tag using the blog description (SEO)
* Add description and keywords meta tags in each posts (SEO)
* Remove header WordPress generator meta tag
* Remove header WLW Manifest meta tag (Windows Live Writer link)
* Remove header RSD (Weblog Client Link) meta tag
* Remove header shortlink meta tag
* Configure site title to use just the wp_title() function (better for hardcode programming)
*	Limit the excerpt length
* Add a read more link after excerpt
* Remove wptexturize filter
* Remove Trackbacks and Pingbacks from Comment Count
* Insert author bio in each post
* Allow HTML in user profile
* Compress all HTML code
* Remove HTML comments (it's don't remove conditional IE comments like: <!--[if IE]>)
* Add Google Analytics code
* Add FeedBurner code

= System =

* Hide the WordPress update message in the Dashboard
* Add dashboard logo
* Unregister default WordPress widgets
* Disable widget system
* Disable comment system
* Disable Posts Auto Saving
* Disable author pages
* Automatically generate the Post Thumbnail (from the first image in post)
* Set JPEG quality
* Resize image at upload to max size
* Prevent installation of new default WordPress themes during core updates
* Display total number of executed SQL queries and page loading time

= Scripts =

* Remove unnecessary jQuery migrate script (jquery-migrate.min.js)
* Include jQuery Google CDN instead local script (version 1.11.0)
* Remove type="text/javascript" attribute from <script> tag
* Track enqueued scripts
* Merge and include removed scripts
* Load merged removed scripts in footer

= Styles =

* Track enqueued styles
* Merge and include removed styles

= Filters/Hooks =

* Disable wp filters/hooks

Contribute on github: [github.com/eHtmlu/advanced-settings](https://github.com/eHtmlu/advanced-settings)

"Simplicity is the ultimate sophistication" -- Da Vinci


== Installation ==

Upload plugin to your blog, activate it, then click on a setting options in admin menu (system, html code, post types and filters/actions).


== Screenshots ==

1. Menu
2. The admin page
3. The Filters/Actions admin page


== Changelog ==

= 2.7.0 - 2025-03-10 =
* Added support for SVG favicons in "Automatically add a FavIcon" setting
* Fixed a few issues
* Several code optimizations
* Add tracking consent modal and tracking itself

= 2.6.0 - 2025-02-27 =
* Changed navigation from multiple menu items to a single menu item with tab navigation
* Marked a few settings as deprecated
* Marked a few settings as experimental
* Added "get in touch" notice
* Tidied up a bit

= 2.5.0 - 2025-01-13 =
* Add new feature: Disable author pages
* Add new feature: Prevent installation of new default WordPress themes during core updates

= 2.4.0 - 2023-12-26 =
* Updated code for WordPress version 6.4.2

= 2.3.4 - 2020-12-02 =
* Updated code for WordPress version 5.5.3

= 2.3.3 - 2017-03-03 =
* Add styles admin page
* Filters admin page fix
* New description

= 2.3.2 - 2017-03-02 =
* Fixes for script actions & hooks

= 2.3.1 - 2017-02-25 =
* Add scripts admin page

= 2.2.2 - 2016-07-15 =
* Remove Trackbacks and Pingbacks from Comment Count
* Add a Custom Dashboard Logo

= 2.2.1 - 2015-08-31 =
* Fix delete posttype bug
* Update plugin links
* Add Git repository

= 2.2 - 2014-09-09 =
* Fix migrate bug on update

= 2.1 - 2014-08-29 =
* Fix update options bug
* Remove unnecessary jQuery migrate script (jquery-migrate.min.js)
* Include jQuery Google CDN instead local script (version 1.11.0)
* Fix incorrect Facebook thumbnails including OG metas
* Remove header RSD (Weblog Client Link) meta tag
* Remove header shortlink meta tag
* Fix delete link in post types admin page

= 2.0 - 2014-02-17 =
* Organized admin menu creating new menu options

= 1.5.3 =
* Disable The “Please Update Now” Message On WordPress Dashboard
* Unregister default WordPress widgets
* Remove widget system
* The comment filter don't remove conditional IE comments now

= 1.5.1 =
* Actions/Filter admin page

= 1.5 =
* Add auto post thumbnail
* Add resize at upload
* Add allow HTML in user profiles
* Update form submit method (code)
* pt_BR translation

= 1.4.6 =
* Fix the "Remove comments system" bug

= 1.4.5 =
* Increase the size of author thumbnail to 100px

= 1.4.4 =
* Fix the "Insert author bio on each post"

= 1.4.3 =
* Code compactor now skips the &lt;pre> tag
