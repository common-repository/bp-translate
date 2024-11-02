<?php

/**
 * Add sign-up language to BuddyPress sign-up array
 *
 * @param array $usermeta
 * @return array
 */
function bp_translate_user_signup( $usermeta ) {
	$usermeta[BP_TRANSLATE_USER_QUERY_ARG] = $_POST[BP_TRANSLATE_USER_QUERY_ARG];

	return $usermeta;
}
add_filter( 'bp_signup_usermeta', 'bp_translate_user_signup' );

/**
 * Add language from sign-up to usermeta on activation
 *
 * @param array $signup
 * @return array
 */
function bp_translate_user_activate( $signup ) {

	if ( is_array( $signup ) )
		update_usermeta( $signup['user_id'], 'WPLANG', $signup['meta'][BP_TRANSLATE_USER_QUERY_ARG] );

	return $signup;
}
add_filter( 'bp_core_activate_account', 'bp_translate_user_activate' );

/**
 * Main function responsible for saving the user language
 *
 * @global object $wpdb
 * @global object $bp
 * @global array $bp_translate
 * @global object $current_user
 * @param array $args
 */
function bp_translate_set_user_locale( $args = '' ) {
	global $wpdb, $bp, $bp_translate, $current_user;

	$defaults = array(
		'key' => 'WPLANG',
		'arg' => BP_TRANSLATE_USER_QUERY_ARG
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	// Prepare value
	$value = $wpdb->prepare( $_POST[ $key ] );

	if ( !$value )
		$value = $wpdb->prepare( $_GET[ $arg ] );

	// Who are we changing? BuddyPress safe.
	if ( isset( $_GET['user_id'] ) && !empty( $_GET['user_id'] ) ) {
		$user_id = $_GET['user_id'];

	} else {
		if ( $bp->displayed_user->id != $bp->loggedin_user->id ) {
			$user_id = $bp->displayed_user->id;
		} else {
			get_currentuserinfo();
			$user_id = $current_user->ID;
			$current_user->$key = $value;
		}
	}

	// If no user_id then there's nothing to change
	if ( empty( $user_id ) )
		return false;

	// Update the user meta
	update_user_meta( $user_id, $key, $value );

	// Change global
	$bp_translate['user_language'] = $value;
}
	/**
	 * Set the admin area language
	 */
	function bp_translate_set_user_locale_admin () {
		bp_translate_set_user_locale( array( 'key' => 'WPLANG_ADMIN', 'arg' => 'lang_admin' ) );
	}

/**
 * Filters get_locale() and returns user based value
 *
 * @global string $locale
 * @global object $current_user
 * @global object $bp_translate
 * @return string
 */
function bp_translate_user_locale() {
	global $locale, $current_user, $bp_translate;

	// Skip this if we've done this already
	if ( isset( $bp_translate['user_language'] ) )
		return $bp_translate['user_language'];

	if ( !defined( 'WP_INSTALLING' ) && is_user_logged_in() ) {

		get_currentuserinfo();

		if ( is_admin() )
			$user_language = $current_user->WPLANG_ADMIN;
		else
			$user_language = $current_user->WPLANG;

		if ( $user_language )
			$bp_translate['user_language'] = $user_language;
		else
			$bp_translate['user_language'] = bp_translate_get_default_language();

	} else {
		$bp_translate['user_language'] = $bp_translate['locale'][substr( $bp_translate['url_info']['language'], 0, 2 )];
	}

	$locale = $bp_translate['user_language'];

	define( 'WPLANG', $bp_translate['user_language'] );

	return $bp_translate['user_language'];
}

?>