=== Simple Announcement Bar ===
Contributors: safatash
Tags: announcement, notification, banner, sticky bar, alert, countdown, scheduling
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 2.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful, lightweight, and highly customizable announcement bar with advanced positioning, scheduling, and targeting.

== Description ==

Simple Announcement Bar is a feature-rich, zero-dependency WordPress plugin that adds a customizable notification banner to your website. Version 2.0 introduces a completely rebuilt tabbed admin interface with a live preview, advanced positioning, scheduling, countdown timers, and scroll-based triggers.

Features include:
* **Live Preview:** See your changes in real-time right in the WordPress admin.
* **Advanced Positioning:** Place the bar at the Top, Bottom, Left Wall, or Right Wall.
* **Visibility & Targeting:** Show on the entire site, homepage only, specific posts/pages, or exclude specific IDs.
* **Device Targeting:** Easily hide the bar on mobile or desktop devices.
* **Scheduling & Countdown:** Set start/end dates and display a live countdown timer.
* **Scroll Triggers:** Show the bar only after scrolling a certain percentage, or hide it when scrolling down.
* **Behavior Controls:** Load minimized, add an open/reopen button, and set notification delays.
* **Full Design Control:** Customize padding, margins, borders, background images, opacity, and all colors.
* **Lightweight:** Built entirely with vanilla JavaScript and CSS. No jQuery or heavy libraries required.

== Installation ==

1. Upload the `simple-announcement-bar` folder to the `/wp-content/plugins/` directory, or install the ZIP file via the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to **Settings > Announcement Bar** to configure your message, colors, and behavior.

== Frequently Asked Questions ==

= Does this plugin slow down my site? =
No. The plugin uses vanilla JavaScript and minimal CSS, ensuring zero impact on your site's performance.

= Can I use HTML in the message? =
Yes, basic HTML tags like `<strong>`, `<em>`, and `<a>` are allowed and safely sanitized.

= Why isn't the bar showing up? =
Make sure you have entered a message in the settings. If the message field is empty, the bar will not render. Also, check your visibility settings (device targeting, specific pages) and ensure you haven't previously closed the bar (which hides it via `localStorage`).

== Changelog ==

= 2.0.0 =
* Major update: Rebuilt admin UI with tabs and live preview.
* Added: Top, Bottom, Left, and Right positioning.
* Added: Scheduling and live countdown timer.
* Added: Scroll-based triggers (show after %, hide on scroll down).
* Added: Device targeting (hide on mobile/desktop).
* Added: Load minimized state and reopen button.
* Added: Advanced design controls (padding, margin, borders, background image, opacity).

= 1.0.0 =
* Initial release.
