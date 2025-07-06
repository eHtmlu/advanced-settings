=== Advanced Settings 3 ===
Plugin Name: Advanced Settings
Author: eHtmlu
Contributors: eHtmlu, webarthur
Author URI: https://ehtmlu.com/
Plugin URI: https://wordpress.org/plugins/advanced-settings/
Tags: settings, admin, dashboard, frontend, editing
Requires at least: 5.0.0
Tested up to: 6.8
Stable tag: 3.1.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Adds settings that you might expect to find in the WordPress core.

== Description ==

Advanced Settings is a powerful WordPress plugin that provides settings you would expect to find in the WordPress core. It is lightweight, performant and offers a modern, fast and user-friendly interface.

**🚀 PERFORMANCE**
Advanced Settings 3 is optimized for extreme performance. It even loads only necessary PHP.
[→ details in FAQ](https://wordpress.org/plugins/advanced-settings#will%20the%20plugin%20become%20slower%20with%20more%20features%3F)

**🔒 SECURITY**
Advanced Settings 3 has been independently reviewed for security vulnerabilities via Patchstack.
[→ details in FAQ](https://wordpress.org/plugins/advanced-settings#how%20is%20the%20security%20of%20the%20plugin%20ensured%3F)

**✳️ INFO ABOUT BAD REVIEWS**
2 bad reviews occurred in 2017 because outdated PHP versions were used, but can't happen again.
[→ details in FAQ](https://wordpress.org/plugins/advanced-settings#what%20caused%20the%20two%20bad%20ratings%3F)

--

== FEATURES ==

**🩷 FEATURE REQUESTS ARE WELCOME**
Advanced Settings 3 was developed to help as many users as possible. If you'd like to see a feature added to this plugin, please let us know. Don't worry, we'll keep the plugin fast and lean; this is a high priority for us. We'll only implement features that don't conflict with this.

= Admin Area =

* Hide the top admin bar for all users in the frontend
* Hide WordPress update message in dashboard
* Hide the welcome panel in the dashboard
* Hide the default widgets in the dashboard 💥 new
* Customize the admin area branding 💥 new

= Frontend =

* Remove PHP version from HTTP headers 💥 new
* Add security HTTP headers 💥 new
* Automatically add FavIcon (when favicon.ico, favicon.png or favicon.svg exists in template folder)
* Add Facebook Open Graph meta tags
* Remove shortlink meta tag
* Remove RSD (Weblog Client Link) meta tag
* Remove WordPress generator meta tag
* Automatically add description meta tag using blog description and post excerpt (SEO)
* Disable author pages
* Remove wptexturize filter
* Disable auto embed of external content 💥 new
* Limit excerpt length
* Add "Read more" link after excerpt
* Remove trackbacks and pingbacks from comment count
* Protect email addresses from spam bots
* Compress HTML code
* Remove HTML comments (except conditional IE comments)
* Disable emoji image replacement

= Editing =

* Disable posts auto saving
* Limit post revisions 💥 new
* Allow SVG uploads for admins 💥 new
* Downsize images on upload to max size
* Set JPEG quality
* Add thumbnail support
* Automatically generate post thumbnail (from first image in post)

= System =

* Hide default WordPress favicon
* Disable comment system
* Disable XML-RPC 💥 new
* Disable public REST API 💥 new
* Prevent installation of new default WordPress themes during core updates
* Disable email notifications for core updates
* Disable email notifications for plugin updates
* Disable email notifications for theme updates

= Developer =

* Display SQL queries and page load time

= Configuration =

* Show/hide deprecated features
* Show/hide experimental features
* Configure tracking consent for feature usage statistics
* Configure visibility of user guide

Contribute on github: [github.com/eHtmlu/advanced-settings](https://github.com/eHtmlu/advanced-settings)

== Installation ==

1. Upload the plugin to your WordPress plugins directory
2. Activate the plugin through the WordPress plugin menu
3. Go to Settings > Advanced in the WordPress admin menu
4. Use the tab navigation to access different settings sections
5. Configure your desired settings in each section

== Frequently Asked Questions ==

= Will the plugin become slower with more features? =

**Short answer:**
No, due to the way the plugin is designed, it would still be just as fast even with thousands of features. Don't worry, we don't add thousands of features.

**Detailed answer:**
Advanced Settings 3 works as follows: Only when you open the plugin's settings window does the plugin recognise which settings are actually available. When saving, only the activated settings are written to a separate cache file. Only this cache file is then taken into account during operation. This means that slowdowns can only occur due to **active** features. However, as some features deactivate WordPress functions, the plugin can actually make WordPress even faster than in the standard configuration.

= How is the security of the plugin ensured? =

**Short answer:**
The experienced plugin developer is additionally supported by the [Patchstack](https://patchstack.com/) community to ensure the security of the plugin.

**Detailed answer:**
The plugin is currently maintained by senior web developer Helmut Wandl, who has over 25 years of experience in web development. He is therefore familiar with various common security concepts for many years.

In addition, the plugin is monitored by the [Patchstack](https://patchstack.com/) community, and if security vulnerabilities are discovered, they are reported and fixed by the plugin developer immediately or as soon as possible, depending on the urgency.

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team helps validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/94cb997e-8daa-4964-8a6a-bdb0ff9bf461)

= What caused the two bad ratings? =

**Short answer:**
The bad ratings arose because a plugin update at the time no longer supported a very outdated PHP version, which unfortunately a few users were still using. However, due to a new WordPress feature, this cannot happen again.

**Detailed answer:**
The plugin developer at the time had decided in 2017 to use PHP features that were introduced with PHP 5.4. Support for the previous version, PHP 5.3, had already been officially discontinued by The PHP Group 3 years earlier. It was therefore natural to assume that all websites actually in use had already been converted to PHP 5.4 or newer. Unfortunately, this was not the case for a few of them.

Nowadays, you can specify in the plugin metadata which PHP version is required as a minimum for the plugin, so that users can only update the plugin if they are using the correct PHP version. While this WordPress feature came too late to prevent the problems and the negative reviews, this problem can fortunately be prevented in the future.

It would be great if you could contribute with your own review to ensure that the ratings reflect the current state of the plugin.

== Screenshots ==

1. Click on the icon in the admin bar to open the advanced settings
2. Search for the features you are looking for
3. Send a feature request if you are missing a feature

== Changelog ==

= 3.1.0 - 2025-07-06 =
* Added 9 new features (see feature list)
* Added tags and tag navigation

= 3.0.2 - 2025-06-04 =
* Fixed order of features
* Fixed a minor security issue in old code (rated as low priority by [Patchstack](https://patchstack.com/))

= 3.0.1 - 2025-05-03 =
* Fixed a critical bug that occurred with WordPress versions prior to 8.6
* Fixed a settings loading error that occurred on WordPress installations that are not in the document root
* Fixed an issue that disabled automatic updates of the plugin if it was active.
* Code optimizations

= 3.0.0 - 2025-04-25 =
* Complete redesign of admin interface with modern design
* New React-based user interface
* Improved performance through caching system
* New modular organization of features and categories
* Improved user guidance with interactive manual
* Numerous code optimizations and bugfixes

= 2.10.0 - 2025-04-10 =
* New feature to protect email addresses from spam bots (HTML entities & JavaScript)

= 2.9.0 - 2025-03-13 =
* New feature to remove default WordPress favicon
* New feature to prevent auto core update emails
* New feature to prevent auto plugin update emails
* New feature to prevent auto theme update emails
* New config feature to show deprecated features (hidden by default)
* New config feature to show experimental expert features (hidden by default)
* Code optimizations

= 2.8.0 - 2025-03-10 =
* Added "Config" tab with settings for the plugin itself
* Performance optimizations

= 2.7.0 - 2025-03-10 =
* Added support for SVG favicons in "Automatically add a FavIcon" setting
* Fixed a few issues
* Several code optimizations
* Added tracking consent modal and tracking itself

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
* Disable The "Please Update Now" Message On WordPress Dashboard
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
