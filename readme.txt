=== Plugin Name ===
Contributors: johnjamesjacoby, codestyling, qianqin
Donate link: http://wordpress.org/extend/plugins/bp-translate/
Tags: multilingual, multilanguage, tinymce, buddypress, Polyglot, bilingual, po, mo, po-mo, editor
Requires at least: 2.9.2
Tested up to: 2.9.2
Stable tag: 0.3-beta

Provides multilanguage blogging, localization editing, and user to user translation in one plugin. Requires WordPressMU (with enhancements for BuddyPress)

== Description ==
This plugin is a forked combination of [qTranslate](http://www.qianqin.de/qtranslate/) by Qian Qin and [Codestyling Localization](http://www.code-styling.de/english/development/wordpress-plugin-codestyling-localization-en/) by Heiko Rabe, so major credits goto them for the majority of this code.

BP Translate brings multilanguage ability and text localization within the WordPress administration panel. It relies heavily on code initially created by two very cool plugins, and uses a few Google Translate API's to translate user to user interactions (Private messages, Forum discussions, etc...)

== Installation ==

1. Upload `/bp-translate/` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Take a few minutes to configure the plugin and familiarize yourself with its features.

== Frequently Asked Questions ==

= Who contributed to this project? =
[Qian Qin] (http://www.qianqin.de/qtranslate/)
[Heiko Rabe] (http://www.code-styling.de/english/development/wordpress-plugin-codestyling-localization-en/)
Most of this plugin is their work, remixed for multi-site use.

= Is this plugin stable? =
Mostly...

It hasn't been tested in all environments and situations, so your mileage may vary. I strongly recommending testing BP Translate on a closed environment to ensure it's the right tool for your job.

= Does this plugin modify my existing data? =
No, but it will modify future data. Posts and pages will have code automatically injected into it by this plugin to tell it which languages to display under what circumstances. Use of this plugin basically commits you into use it going forward, as removal of the injected code is quite a manual process.

Also, there is no full uninstall feature to revert your multi-lingual pages and posts back to "normal", so consider yourself warned.

JavaScript must be active in your browser for it to work, and you must be using an up-to-date version of WordPressMU. Changes in JavaScript that comes bundled with WordPress will cause incompatibilities with BP Translate, so be sure to keep an eye on things.

= Does this plugin automatically translate my pages and posts? =
No. While qTranslate did originally offer a feature like this, I opted to remove it as it did not fit the scope of my needs.

(This may come back in a future version.)

== Changelog ==

= 0.3-beta =
* Move a ton of files around and lots of little updates

= 0.2-beta =
* Tweaks

= 0.1-beta =
* Initial upload to WP plugin repository