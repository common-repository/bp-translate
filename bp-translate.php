<?php
/*
Plugin Name: BP Translate
Plugin URI: http://wordpress.org/extend/plugins/bp-translate/
Description: Adds multilingual support into WordPress and BuddyPress.
Version: 0.2b
Author: John James Jacoby
Author URI: http://johnjamesjacoby.com
Tags: multilingual, multilanguage, tinymce, buddypress, Polyglot, bilingual, widget, switcher
Site Wide Only: true
*/

// Load BP Translate globals
require_once( WP_PLUGIN_DIR . '/bp-translate/includes/bp-translate-globals.php' );

// Load includes
require_once( WP_PLUGIN_DIR . '/bp-translate/includes/bp-translate-functions.php' );
require_once( WP_PLUGIN_DIR . '/bp-translate/includes/bp-translate-catchuri.php' );
require_once( WP_PLUGIN_DIR . '/bp-translate/includes/bp-translate-templatetags.php' );
require_once( WP_PLUGIN_DIR . '/bp-translate/includes/bp-translate-user.php' );
require_once( WP_PLUGIN_DIR . '/bp-translate/includes/bp-translate-cssjs.php' );
require_once( WP_PLUGIN_DIR . '/bp-translate/includes/bp-translate-widgets.php' );
require_once( WP_PLUGIN_DIR . '/bp-translate/includes/bp-translate-filters.php' );

// Only run if inside the admin area
if ( defined( 'WP_ADMIN' ) ) {
	require_once( WP_PLUGIN_DIR . '/bp-translate/includes/admin/network/bp-translate-site-admin.php' );
	require_once( WP_PLUGIN_DIR . '/bp-translate/includes/admin/network/bp-translate-site-admin-switcher.php' );
	require_once( WP_PLUGIN_DIR . '/bp-translate/includes/admin/bp-translate-admin.php' );
	require_once( WP_PLUGIN_DIR . '/bp-translate/includes/admin/pomo/bp-translate-pomo.php' );
}

// Load BuddyPress specific code gracefully
function bp_translate_buddypress() {
	require_once( WP_PLUGIN_DIR . '/bp-translate/includes/buddypress/bp-translate-settings.php' );
}

if ( defined( 'BP_VERSION' ) || did_action( 'bp_include' ) )
	bp_translate_buddypress();
else
	add_action( 'bp_include', 'bp_translate_buddypress' );

?>