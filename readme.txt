=== IAR Basic Setup ===
Contributors: iamrootagency
Tags: cleanup, optimization, disable gutenberg, disable comments, security, smtp, maintenance mode
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modular WordPress cleanup and optimization plugin. Enable only the features you need.

== Description ==

IAR Basic Setup is a lightweight, modular plugin that helps you clean up and optimize your WordPress installation. Each feature can be individually enabled or disabled through a simple toggle interface.

**Available Modules:**

* **Disable Gutenberg** - Replaces the block editor with the classic editor
* **Disable Comments** - Completely removes comments functionality site-wide
* **Hide Admin Bar** - Hides the admin bar for non-administrator users
* **Clean Head** - Removes unnecessary meta tags from the document head (generator, RSD, Windows Live Writer, etc.)
* **Disable Emojis** - Removes WordPress emoji scripts and styles for better performance
* **Enable SVG Support** - Allows uploading SVG files to the media library
* **Disable XML-RPC** - Disables XML-RPC for better security
* **Disable Auto Updates** - Disables automatic updates for core, plugins, and themes
* **Enable Debug Mode** - Enables error reporting, display, and logging for troubleshooting
* **Post Cloner** - Adds a Clone action to duplicate posts, pages, and custom post types
* **Custom Login URL** - Replaces /wp-login.php with a custom path to reduce brute-force attacks
* **Custom Login Logo** - Replaces the WordPress logo on the login page with a custom image
* **Disable RSS Feeds** - Disables all RSS and Atom feed endpoints
* **Disable Author Archives** - Prevents user enumeration through author archive pages
* **Disable REST API for Guests** - Restricts REST API access to authenticated users only
* **Limit Login Attempts** - Blocks an IP address after failed login attempts
* **Maintenance Mode** - Displays a maintenance page for non-admin visitors
* **Duplicate Menu** - Adds a Duplicate action to clone nav menus with all items
* **SMTP Mail** - Configures WordPress to send emails through SMTP

**Features:**

* Modular design - enable only what you need
* Clean, modern admin interface
* Lightweight with no bloat
* No external dependencies
* Translation ready

== Installation ==

1. Upload the `iar-basic-setup` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'IAR Basic Setup' in the admin menu
4. Enable the modules you want to use

== Frequently Asked Questions ==

= Will this plugin slow down my site? =

No. The plugin is designed to be lightweight. Only the modules you enable are loaded, and each module contains minimal code.

= Can I use this with the Classic Editor plugin? =

Yes, but if you enable the "Disable Gutenberg" module, you won't need the Classic Editor plugin.

= Is it safe to enable SVG uploads? =

Yes. SVG files are sanitized on upload to block potentially malicious content (scripts, event handlers, etc.).

= What gets cloned with the Post Cloner? =

The Post Cloner duplicates: post title (prefixed with "Copy of"), content, excerpt, all meta data (including ACF fields and featured images), and all taxonomies (categories, tags, custom taxonomies). The cloned post is always created as a draft.

== Screenshots ==

1. Main settings page with module toggles
2. Post Cloner settings page

== Changelog ==

= 1.0.0 =
* Initial release
* Added Disable Gutenberg module
* Added Disable Comments module
* Added Hide Admin Bar module
* Added Clean Head module
* Added Disable Emojis module
* Added SVG Support module
* Added Disable XML-RPC module
* Added Post Cloner module
* Added Disable Auto Updates module
* Added Enable Debug Mode module
* Added Custom Login URL module
* Added Custom Login Logo module
* Added Disable RSS Feeds module
* Added Disable Author Archives module
* Added Disable REST API for Guests module
* Added Limit Login Attempts module
* Added Maintenance Mode module
* Added Duplicate Menu module
* Added SMTP Mail module

== Upgrade Notice ==

= 1.0.0 =
Initial release.
