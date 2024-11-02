<?php
/* File for helper functions to perform administrative tasks */

/*
 * Script to run through and set all user langs to default
 * if they do not have a meta setting of their own
 * This is resource heavy so remmeber to comment out action
 */
function bp_translate_update_all_users ( $lang = '' ) {
	global $wpdb;

	$query = "SELECT * FROM {$wpdb->users}";

	$user_list = $wpdb->get_results( $query, ARRAY_A );

	if ( !$lang )
		$lang = bp_translate_get_language();

	foreach ( (array) $user_list as $user) {
		$user_lang =		get_usermeta( $user->ID, 'WPLANG' );
		$user_admin_lang =	get_usermeta( $user->ID, 'WPLANG' );

		if ( !$user_lang )
			update_usermeta( $user->ID, 'WPLANG', $lang );

		if ( !$user_admin_lang )
			update_usermeta( $user->ID, 'WP_ADMINLANG', $lang );
	}
}
//add_action ( 'init', 'bp_translate_update_all_users' );

?>
