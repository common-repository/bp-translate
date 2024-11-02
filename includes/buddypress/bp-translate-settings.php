<?php

function bp_translate_setup_nav() {
	global $bp;

	// Set up settings as a sudo-component for identification and nav selection
	$bp->settings->id = 'settings';
	$bp->settings->slug = BP_SETTINGS_SLUG;

	// Register this in the active components array
	$bp->active_components[$bp->settings->slug] = $bp->settings->id;

	$settings_link = $bp->displayed_user->domain . $bp->settings->slug . '/';

	bp_core_new_subnav_item (
		array(
			'name' => __( 'Language', 'bp-translate' ),
			'slug' => BP_TRANSLATE_SLUG,
			'parent_url' => $settings_link,
			'parent_slug' => $bp->settings->slug,
			'screen_function' => 'bp_translate_user_settings',
			'position' => 30,
			'item_css_id' => 'language',
			'user_has_access' => bp_is_home()
		));
}
add_action( 'bp_setup_nav', 'bp_translate_setup_nav' );

function bp_translate_user_settings() {
	global $bp;

	if ( isset( $_POST['submit'] ) ) {
		check_admin_referer('bp_translate_user_settings');

		$current_user = set_current_user( $bp->displayed_user->id );

		// Form has been submitted and nonce checks out, lets do it.
		update_usermeta( $current_user->ID, 'WPLANG', $_POST['WPLANG'] );
		update_usermeta( $current_user->ID, 'WPLANG_ADMIN', $_POST['WPLANG_ADMIN'] );

		// Trigger this when everything is all good
		$bp_translate['redirect'] = true;
		bp_core_add_message( __( 'Changes Saved.', 'bp-translate' ), 'success');
	}

	add_action( 'bp_template_title', 'bp_translate_user_settings_title' );
	add_action( 'bp_template_content', 'bp_translate_user_settings_content' );

	bp_core_load_template( apply_filters( 'bp_translate_template_settings', 'settings/language' ) );
}

function bp_translate_user_settings_title() {
	_e( 'Language Settings', 'bp-translate' );
}

function bp_translate_user_settings_select( $args = '' ) {
	global $bp, $bp_translate;

	$defaults = array(
		'key' => 'WPLANG',
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$current_user = set_current_user( $bp->displayed_user->id );

	if ( is_dir( ABSPATH . LANGDIR ) && $dh = opendir( ABSPATH . LANGDIR ) )
		while ( ( $lang_file = readdir( $dh ) ) !== false )
			if ( substr( $lang_file, -3 ) == '.mo' )
				$lang_files[] = $lang_file;

			if ( is_array( $lang_files ) && !empty( $lang_files ) ) {
?>
				<select name="<?php echo $key ?>" id="<?php echo $key ?>">
<?php foreach ( bp_translate_get_sorted_languages() as $language ) { ?>
					<option value="<?php echo $bp_translate['locale'][$language]; ?>" <?php if ( $bp_translate['locale'][$language] == $current_user->$key ) echo ' selected="selected"'; ?>><?php echo $bp_translate['language_name'][$language];?></option>
<?php } ?>
				</select>
<?php
			}
}
	function bp_translate_user_settings_select_admin() {
		bp_translate_user_settings_select( array( 'key' => 'WPLANG_ADMIN' ) );
	}

function bp_translate_user_settings_content() {
	global $bp;

	$current_user = set_current_user( $bp->displayed_user->id );
?>
		<form action="<?php echo $bp->displayed_user->domain . 'settings/' . BP_TRANSLATE_SLUG . '/' ?>" method="post" class="standard-form" id="settings-form">
			<table class="notification-settings" id="language-settings">
				<tr>
					<th class="icon"></th>
					<th class="title"><?php _e( 'Language', 'bp-translate' ) ?></th>
					<th class="yes"><?php _e( 'Select', 'bp-translate' ) ?></th>
				</tr>
				<tr>
					<td></td>
					<td><?php _e( 'Your native language', 'bp-translate' ) ?></td>
					<td>
						<?php bp_translate_user_settings_select(); ?>

					</td>
				</tr>
				<tr>
					<td></td>
					<td><?php _e( 'Administration language', 'bp-translate' ) ?></td>
					<td>
						<?php bp_translate_user_settings_select_admin(); ?>

					</td>
				</tr>
			</table>
			<table class="notification-settings" id="translation-settings">
				<tr>
					<th class="icon"></th>
					<th class="title"><?php _e( 'Translations', 'bp-translate' ) ?></th>
					<th class="yes"><?php _e( 'Yes', 'bp-translate' ) ?></th>
					<th class="no"><?php _e( 'No', 'bp-translate' )?></th>
				</tr>
				<tr>
					<td></td>
					<td><?php _e( 'Automatically translate LOGOI website content', 'bp-translate' ) ?></td>
					<td class="yes"><input type="radio" name="bp-translate[auto_translate_site]" value="yes" <?php if ( !get_usermeta( $current_user->id, 'auto_translate_site' ) || 'yes' == get_usermeta( $current_user->id, 'auto_translate_site' ) ) { ?>checked="checked" <?php } ?>/></td>
					<td class="no"><input type="radio" name="bp-translate[auto_translate_site]" value="no" <?php if ( 'no' == get_usermeta( $current_user->id, 'auto_translate_site' ) ) { ?>checked="checked" <?php } ?>/></td>
				</tr>
				<tr>
					<td></td>
					<td><?php _e( 'Automatically translate user content', 'bp-translate' ) ?></td>
					<td class="yes"><input type="radio" name="bp-translate[auto_translate_user]" value="yes" <?php if ( !get_usermeta( $current_user->id, 'auto_translate_user' ) || 'yes' == get_usermeta( $current_user->id, 'auto_translate_user' ) ) { ?>checked="checked" <?php } ?>/></td>
					<td class="no"><input type="radio" name="bp-translate[auto_translate_user]" value="no" <?php if ( 'no' == get_usermeta( $current_user->id, 'auto_translate_user' ) ) { ?>checked="checked" <?php } ?>/></td>
				</tr>
				<tr>
					<td></td>
					<td><?php _e( 'Allow my content to be translated by others', 'bp-translate' ) ?></td>
					<td class="yes"><input type="radio" name="bp-translate[allow_to_be_translated]" value="yes" <?php if ( !get_usermeta( $current_user->id, 'allow_to_be_translated' ) || 'yes' == get_usermeta( $current_user->id, 'allow_to_be_translated' ) ) { ?>checked="checked" <?php } ?>/></td>
					<td class="no"><input type="radio" name="bp-translate[allow_to_be_translated]" value="no" <?php if ( 'no' == get_usermeta( $current_user->id, 'allow_to_be_translated' ) ) { ?>checked="checked" <?php } ?>/></td>
				</tr>
				<?php do_action( 'bp_translate_user_settings' ) ?>
			</table>
			<p class="submit">
				<input type="submit" name="submit" value="<?php _e( 'Save Changes', 'bp-translate' ) ?>" id="submit" class="auto"/>
				<?php wp_nonce_field('bp_translate_user_settings') ?>

			</p>
		</form>
<?php } ?>