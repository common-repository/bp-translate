<?php

/* BP Translate blog language settings */
function bp_translate_site_admin_menus() {
	global $menu, $submenu, $bp_translate;

	/* Add site level language settings */
	if ( function_exists( 'add_submenu_page' ) && current_user_can( 'install_plugins' ) ) {
		add_submenu_page( 'wpmu-admin.php', __( 'Languages', 'bp-translate' ), __( 'Languages', 'bp-translate' ), 'activate_plugins', 'bp-translate-languages', 'bp_translate_languages' );
		add_submenu_page( 'wpmu-admin.php', __( 'Language Settings', 'bp-translate' ), __( 'Language Settings', 'bp-translate' ), 'activate_plugins', 'bp-translate-site-settings', 'bp_translate_settings_site' );
	}
	
	add_management_page(
		__( 'Localization Management', BP_TRANSLATE_PO_TEXTDOMAIN ),
		__( 'Translations', BP_TRANSLATE_PO_TEXTDOMAIN ),
		'edit_private_pages',
		__FILE__,
		'bp_translate_po_main_page'
	);

	bp_translate_load_po_edit_admin_page();
}

function bp_translate_language_form_site( $lang = '', $language_code = '', $language_name = '', $language_locale = '', $language_date_format = '', $language_time_format = '', $language_flag ='', $language_na_message = '', $language_default = '', $original_lang='' ) {
	global $bp_translate;
?>
		<input type="hidden" name="original_lang" value="<?php echo $original_lang; ?>" />

		<div class="form-field">
			<label for="language_code"><?php _e('Language Code', 'bp-translate') ?></label>
			<input name="language_code" id="language_code" type="text" value="<?php echo $language_code; ?>" size="2" maxlength="2"/>
			<p><?php _e('2-Letter <a href="http://www.w3.org/WAI/ER/IG/ert/iso639.htm#2letter">ISO Language Code</a> for the Language you want to insert. (Example: en)', 'bp-translate'); ?></p>
		</div>
		<div class="form-field">
			<label for="language_flag"><?php _e('Flag', 'bp-translate') ?></label>
<?php
	$files = array();

	if( $dir_handle = @opendir( trailingslashit( WP_CONTENT_DIR ) . $bp_translate['flag_location'] ) ) {
		while ( false !== ( $file = readdir( $dir_handle ) ) ) {
			if ( preg_match( "/\.(jpeg|jpg|gif|png)$/i", $file ) ) {
				$files[] = $file;
			}
		}
		sort( $files );
	}
	if ( sizeof( $files ) > 0 ){
?>
			<select name="language_flag" id="language_flag" onchange="switch_flag(this.value);"  onclick="switch_flag(this.value);" onkeypress="switch_flag(this.value);">
<?php
		foreach ($files as $file) {
?>
				<option value="<?php echo $file; ?>" <?php echo ($language_flag==$file)?'selected="selected"':''?>><?php echo $file; ?></option>
<?php
		}
?>
			</select>
			<img src="" alt="Flag" id="preview_flag" style="vertical-align:middle; display:none"/>
<?php
	} else {
		_e('Incorrect Flag Image Path! Please correct it!', 'bp-translate');
	}
?>
			<p><?php _e('Choose the corresponding country flag for language. (Example: gb.png)', 'bp-translate'); ?></p>
		</div>
		<script type="text/javascript">
			//<![CDATA[
				function switch_flag(url) {
					document.getElementById('preview_flag').style.display = "inline";
					document.getElementById('preview_flag').src = "<?php echo trailingslashit(WP_CONTENT_URL).$bp_translate['flag_location'];?>" + url;
				}

				switch_flag(document.getElementById('language_flag').value);
			//]]>
		</script>
		<div class="form-field">
			<label for="language_name"><?php _e('Name', 'bp-translate') ?></label>
			<input name="language_name" id="language_name" type="text" value="<?php echo $language_name; ?>"/>
			<p><?php _e('The Name of the language, which will be displayed on the site. (Example: English)', 'bp-translate'); ?></p>
		</div>
		<div class="form-field">
			<label for="language_locale"><?php _e('Locale', 'bp-translate') ?></label>
			<input name="language_locale" id="language_locale" type="text" value="<?php echo $language_locale; ?>"  size="5" maxlength="5"/>
			<p>
				<?php _e('PHP and WordPress Locale for the language. (Example: en_US)', 'bp-translate'); ?><br />
				<?php _e('You will need to install the .mo file for this language.', 'bp-translate'); ?>
			</p>
		</div>
		<div class="form-field">
			<label for="language_date_format"><?php _e('Date Format', 'bp-translate') ?></label>
			<input name="language_date_format" id="language_date_format" type="text" value="<?php echo $language_date_format; ?>"/>
			<p><?php _e('Depending on your Date / Time Conversion Mode, you can either enter a <a href="http://www.php.net/manual/function.strftime.php">strftime</a> (use %q for day suffix (st,nd,rd,th)) or <a href="http://www.php.net/manual/function.date.php">date</a> format. This field is optional. (Example: %A %B %e%q, %Y)', 'bp-translate'); ?></p>
		</div>
		<div class="form-field">
			<label for="language_time_format"><?php _e('Time Format', 'bp-translate') ?></label>
			<input name="language_time_format" id="language_time_format" type="text" value="<?php echo $language_time_format; ?>"/>
			<p><?php _e('Depending on your Date / Time Conversion Mode, you can either enter a <a href="http://www.php.net/manual/function.strftime.php">strftime</a> or <a href="http://www.php.net/manual/function.date.php">date</a> format. This field is optional. (Example: %I:%M %p)', 'bp-translate'); ?></p>
		</div>
		<div class="form-field">
			<label for="language_na_message"><?php _e('Not Available Message', 'bp-translate') ?></label>
			<input name="language_na_message" id="language_na_message" type="text" value="<?php echo $language_na_message; ?>"/>
			<p>
				<?php _e('Message to display if post is not available in the requested language. (Example: Sorry, this entry is only available in %LANG:, : and %.)', 'bp-translate'); ?><br />
				<?php _e('%LANG:&lt;normal_seperator&gt;:&lt;last_seperator&gt;% generates a list of languages seperated by &lt;normal_seperator&gt; except for the last one, where &lt;last_seperator&gt; will be used instead.', 'bp-translate'); ?><br />
			</p>
		</div>
<?php
}

function bp_translate_check_setting_site($var, $updateOption = false, $type = BP_TRANSLATE_STRING) {
	global $bp_translate;

	switch( $type ) {
		case BP_TRANSLATE_URL:
			$_POST[$var] = trailingslashit($_POST[$var]);

		case BP_TRANSLATE_LANGUAGE:
		case BP_TRANSLATE_STRING:
			if ( isset( $_POST['submit'] ) && isset( $_POST[$var] ) ) {
				if ( $type != BP_TRANSLATE_LANGUAGE || bp_translate_is_enabled( $_POST[$var] ) )
					$bp_translate[$var] = $_POST[$var];

				if ( $updateOption )
					update_site_option( 'bp_translate_' . $var, $bp_translate[$var] );

				return true;
			} else {
				return false;
			}
			break;

		case BP_TRANSLATE_BOOLEAN:
			if ( isset( $_POST['submit'] ) ) {
				if ( isset( $_POST[$var]) && $_POST[$var] == 1 )
					$bp_translate[$var] = true;
				else
					$bp_translate[$var] = false;

				if ( $updateOption ) {
					if ( $bp_translate[$var] )
						update_site_option( 'bp_translate_' . $var, '1' );
					else
						update_site_option( 'bp_translate_' . $var, '0' );
				}
				return true;

			} else {
				return false;
			}
			break;
		case BP_TRANSLATE_INTEGER:
			if ( isset( $_POST['submit'] ) && isset( $_POST[$var] ) ) {
				$bp_translate[$var] = intval( $_POST[$var] );

				if ( $updateOption )
					update_site_option( 'bp_translate_' . $var, $bp_translate[$var] );

				return true;
			} else {
				return false;
			}
			break;
	}
	return false;
}

function bp_translate_language_columns_site( $columns ) {
	return array(
		'flag' => 'Flag',
		'name' => __( 'Name', 'bp-translate' ),
		'status' => __( 'Action', 'bp-translate' ),
		'status2' => '',
		'status3' => ''
	);
}

function bp_translate_settings_site() {
	global $bp_translate, $wpdb;

	/* Init some needed variables */
	$error = '';
	$altered_table = false;

	$message = apply_filters( 'bp_translate_settings_pre', '' );

	/* Check for action */
	if ( isset( $_POST['bp_translate_reset'] ) && isset( $_POST['bp_translate_reset2'] ) ) {
		$message = __( 'BP Translate has been reset.', 'bp-translate' );

	/* Save settings */
	} elseif ( isset( $_POST['default_site_language'] ) ) {
		bp_translate_check_setting_site( 'default_site_language',		true, BP_TRANSLATE_LANGUAGE );
		bp_translate_check_setting_site( 'flag_location',				true, BP_TRANSLATE_URL );
		bp_translate_check_setting_site( 'ignore_file_types',			true, BP_TRANSLATE_STRING );
		bp_translate_check_setting_site( 'detect_browser_language',		true, BP_TRANSLATE_BOOLEAN );
		bp_translate_check_setting_site( 'hide_site_untranslated',		true, BP_TRANSLATE_BOOLEAN );
		bp_translate_check_setting_site( 'use_strftime',				true, BP_TRANSLATE_INTEGER );
		bp_translate_check_setting_site( 'url_mode',					true, BP_TRANSLATE_INTEGER );
		bp_translate_check_setting_site( 'auto_update_mo',				true, BP_TRANSLATE_BOOLEAN );

		if ( $_POST['update_mo_now'] == '1' && bp_translate_update_gettext_databases( true ) )
			$message = __( 'Gettext databases updated.', 'bp-translate' );

	}

	/* Update language tags */
	if ( isset( $_GET['convert'] ) ) {

		$wpdb->show_errors();

		foreach ( $bp_translate['enabled_languages'] as $lang ) {
			$wpdb->query('UPDATE ' . $wpdb->posts . ' set post_title = REPLACE(post_title, "[lang_' . $lang . ']","<!--:' . $lang . '-->")' );
			$wpdb->query('UPDATE ' . $wpdb->posts . ' set post_title = REPLACE(post_title, "[/lang_' . $lang . ']","<!--:-->")' );
			$wpdb->query('UPDATE ' . $wpdb->posts . ' set post_content = REPLACE(post_content, "[lang_' . $lang . ']","<!--:' . $lang . '-->")' );
			$wpdb->query('UPDATE ' . $wpdb->posts . ' set post_content = REPLACE(post_content, "[/lang_' . $lang . ']","<!--:-->")' );
		}

		$message = __( 'Database Update successful!', 'bp-translate' );

	/* Update language tags */
	} elseif ( isset( $_GET['markdefault'] ) ) {

		$wpdb->show_errors();

		$result = $wpdb->get_results( 'SELECT ID, post_title, post_content FROM ' . $wpdb->posts . ' WHERE NOT (post_content LIKE "%<!--:-->%" OR post_title LIKE "%<!--:-->%")' );

		foreach( $result as $post ) {

			$content = bp_translate_split( $post->post_content );
			$title = bp_translate_split( $post->post_title );

			foreach ( $bp_translate['enabled_languages'] as $language ) {
				if ( $language != $bp_translate['default_site_language'] ) {
					$content[$language] = "";
					$title[$language] = "";
				}
			}

			$content = bp_translate_join( $content );
			$title = bp_translate_join( $title );

			$wpdb->query( 'UPDATE ' . $wpdb->posts . ' set post_content = "' . mysql_escape_string( $content ) . '", post_title = "' . mysql_escape_string( $title ) . '" WHERE ID=' . $post->ID );
		}

		$message = __( 'All Posts marked as default language!', 'bp-translate' );

	} elseif ( isset( $_GET['moveup'] ) ) {
		$languages = bp_translate_get_sorted_languages();
		$message = __( 'No such language!', 'bp-translate' );

		foreach ( $languages as $key => $language ) {
			if ( $language == $_GET['moveup'] ) {
				if ( $key == 0 ) {
					$message = __('Language is already first!', 'bp-translate');
					break;
				}
				$languages[$key] = $languages[$key-1];
				$languages[$key - 1] = $language;
				$bp_translate['enabled_languages'] = $languages;
				$message = __('New order saved.', 'bp-translate');
				break;
			}
		}
	} elseif ( isset ( $_GET['movedown'] ) ) {
		$languages = bp_translate_get_sorted_languages();
		$message = __( 'No such language!', 'bp-translate' );

		foreach ( $languages as $key => $language ) {

			if ( $language == $_GET['movedown'] ) {
				if ( $key == sizeof( $languages )-1 ) {
					$message = __( 'Language is already last!', 'bp-translate' );
					break;
				}

				$languages[$key] = $languages[$key + 1];
				$languages[$key + 1] = $language;
				$bp_translate['enabled_languages'] = $languages;
				$message = __( 'New order saved.', 'bp-translate' );
				break;
			}
		}
	}

	/* Evertyhing fine? */
	$everything_fine = ( (
		isset( $_POST['submit'] ) ||
		isset( $_GET['moveup'] ) ||
		isset( $_GET['movedown'] ) ) &&
		$error == ''
	);

	/* Settings might have changed, so save */
	if ( $everything_fine ) {
		bp_translate_save_settings_site();

		if ( empty( $message ) )
			$message = __( 'Options saved.', 'bp-translate' );
	}

	if ( $bp_translate['auto_update_mo'] ) {

		if ( !is_dir( WP_LANG_DIR ) || !$ll = @fopen( trailingslashit( WP_LANG_DIR ) . 'bp_translate.test', 'a' ) ) {
			$error = sprintf( __('Could not write to "%s", Gettext Databases could not be downloaded!', 'bp-translate' ), WP_LANG_DIR );
		} else {
			@fclose( $ll );
			@unlink( trailingslashit( WP_LANG_DIR ) . 'bp_translate.test' );
		}
	}

	// don't accidently delete/enable/disable twice
	$clean_uri = preg_replace( "/&(disable|convert|markdefault|moveup|movedown)=[^&#]*/i", "", $_SERVER['REQUEST_URI'] );
	$clean_uri = apply_filters( 'bp_translate_clean_uri', $clean_uri );

	// Generate HTML
?>
<?php if ( $message ) : ?>
		<div id="message" class="updated fade"><p><strong><?php echo $message; ?></strong></p></div>
<?php
	endif;
	if ( $error != '' ) : ?>
		<div id="message" class="error fade"><p><strong><?php echo $error; ?></strong></p></div>
<?php endif;?>
		<div class="wrap">
			<h2><?php _e('Language Management and Configuration', 'bp-translate'); ?></h2>
				<form action="<?php echo $clean_uri;?>" method="post">
					<h3><?php _e( 'General Settings', 'bp-translate' ) ?></h3>
					<h4><?php _e( 'These settings can be changed without any risk of data loss.', 'bp-translate' ) ?></h4>
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e('Default Language / Order', 'bp-translate') ?></th>
							<td>
								<fieldset><legend class="hidden"><?php _e('Default Language', 'bp-translate') ?></legend>
<?php
		foreach ( bp_translate_get_sorted_languages() as $key => $language ) {
			echo "\t<label title='" . $bp_translate['language_name'][$language] . "'><input type='radio' name='default_site_language' value='" . $language . "'";
			if ( $language == $bp_translate['default_site_language'] ) {
				echo " checked='checked'";
			}
			echo ' />';
			echo ' <a href="'.add_query_arg('moveup', $language, $clean_uri).'"><img src="'.WP_PLUGIN_URL.'/bp-translate/includes/images/arrowup.png" alt="up" /></a>';
			echo ' <a href="'.add_query_arg('movedown', $language, $clean_uri).'"><img src="'.WP_PLUGIN_URL.'/bp-translate/includes/images/arrowdown.png" alt="down" /></a>';
			echo ' <img src="' . trailingslashit(WP_CONTENT_URL) .$bp_translate['flag_location'].$bp_translate['flag'][$language] . '" alt="' . $bp_translate['language_name'][$language] . '" /> ';
			echo ' '.$bp_translate['language_name'][$language] . "</label><br />\n";
		}
?>
								</fieldset>
								<p class="description"><?php _e( 'Choose the default language of your site.', 'bp-translate' ) ?></p>
								<span><?php printf( __( 'This is the language which will be shown on %s. You can also change the order the languages by clicking on the arrows above.', 'bp-translate' ), get_bloginfo( 'url' ) ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Hide Untranslated Content', 'bp-translate');?></th>
							<td>
								<label for="hide_site_untranslated"><input type="checkbox" name="hide_site_untranslated" id="hide_site_untranslated" value="1"<?php echo ($bp_translate['hide_site_untranslated'])?' checked="checked"':''; ?>/> <?php _e('Hide Content which is not available for the selected language.', 'bp-translate'); ?></label>
								<br/>
								<span><?php _e( 'If checked, posts will be hidden if the content is not available for the selected language.<br />If unchecked, a message will appear showing all the languages the content is available in.', 'bp-translate' ); ?></span>
								<p class="description"><?php _e( 'This will not work if you installed BP Translate on a blog with existing entries. Please see "Convert Database" under "Reset, Repair, and Debug".', 'bp-translate' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Detect Browser Language', 'bp-translate');?></th>
							<td>
								<label for="detect_browser_language"><input type="checkbox" name="detect_browser_language" id="detect_browser_language" value="1"<?php echo ($bp_translate['detect_browser_language'])?' checked="checked"':''; ?>/> <?php _e('Detect the language of the browser and redirect accordingly.', 'bp-translate'); ?></label>
								<p class="description"><?php _e( 'When the frontpage is visited via bookmark/external link/type-in, the visitor will be forwarded to the correct URL for the language specified by his browser.', 'bp-translate' ); ?></p>
							</td>
						</tr>
					</table>
					<h3><?php _e('Advanced Settings', 'bp-translate') ?></h3>
					<h4><?php _e( 'Note! It is possible to lock yourself out of your website by changing these settings. Proceed with caution.', 'bp-translate' ) ?></h4>
					<table class="form-table" id="bp_translate-advanced">
						<tr>
							<th scope="row"><?php _e('URL Modification Mode', 'bp-translate') ?></th>
							<td>
								<fieldset><legend class="hidden"><?php _e('URL Modification Mode', 'bp-translate') ?></legend>
									<label title="Query Mode"><input type="radio" name="url_mode" value="<?php echo BP_TRANSLATE_URL_QUERY; ?>" <?php echo ($bp_translate['url_mode']==BP_TRANSLATE_URL_QUERY)?"checked=\"checked\"":""; ?> /> <?php _e('Use Query Mode (?lang=en)', 'bp-translate'); ?></label><br />
									<label title="Pre-Path Mode"><input type="radio" name="url_mode" value="<?php echo BP_TRANSLATE_URL_PATH; ?>" <?php echo ($bp_translate['url_mode']==BP_TRANSLATE_URL_PATH)?"checked=\"checked\"":""; ?> /> <?php _e('Use Pre-Path Mode (Default, puts /en/ in front of URL)', 'bp-translate'); ?></label><br />
									<label title="Pre-Domain Mode"><input type="radio" name="url_mode" value="<?php echo BP_TRANSLATE_URL_DOMAIN; ?>" <?php echo ($bp_translate['url_mode']==BP_TRANSLATE_URL_DOMAIN)?"checked=\"checked\"":""; ?> /> <?php _e('Use Pre-Domain Mode (uses http://en.yoursite.com)', 'bp-translate'); ?></label><br />
								</fieldset>
								<p class="description"><?php _e('Pre-Path and Pre-Domain mode will only work with mod_rewrite/pretty permalinks. Additional Configuration is needed for Pre-Domain mode!', 'bp-translate'); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Flag Image Path', 'bp-translate');?></th>
							<td>
								<?php echo trailingslashit(WP_CONTENT_URL); ?><input type="text" name="flag_location" id="flag_location" value="<?php echo $bp_translate['flag_location']; ?>" style="width:50%"/>
								<span><?php _e('Path to the flag images under wp-content, with trailing slash. (Default: plugins/bp-translate/includes/images/flags/)', 'bp-translate'); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Ignore Links', 'bp-translate');?></th>
							<td>
								<input type="text" name="ignore_file_types" id="ignore_file_types" value="<?php echo $bp_translate['ignore_file_types']; ?>" style="width:100%"/>
								<span><?php _e('Don\'t convert Links to files of the given file types. (Default: gif,jpg,jpeg,png,pdf,swf,tif,rar,zip,7z,mpg,divx,mpeg,avi,css,js)', 'bp-translate'); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Update Gettext Databases', 'bp-translate');?></th>
							<td>
								<label for="auto_update_mo"><input type="checkbox" name="auto_update_mo" id="auto_update_mo" value="1"<?php echo ($bp_translate['auto_update_mo'])?' checked="checked"':''; ?>/> <?php _e('Automatically check for .mo-Database Updates of installed languages.', 'bp-translate'); ?></label>
								<br/>
								<label for="update_mo_now"><input type="checkbox" name="update_mo_now" id="update_mo_now" value="1" /> <?php _e('Update Gettext databases now.', 'bp-translate'); ?></label>
								<br/>
								<span><?php _e('BP Translate will automatically download the latest translations (.mo files) from the WordPress Localization Repository.', 'bp-translate'); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Date / Time Conversion', 'bp-translate');?></th>
							<td>
								<label><input type="radio" name="use_strftime" value="<?php echo BP_TRANSLATE_DATE; ?>" <?php echo ($bp_translate['use_strftime']==BP_TRANSLATE_DATE)?' checked="checked"':''; ?>/> <?php _e('Use emulated date function.', 'bp-translate'); ?></label><br />
								<label><input type="radio" name="use_strftime" value="<?php echo BP_TRANSLATE_DATE_OVERRIDE; ?>" <?php echo ($bp_translate['use_strftime']==BP_TRANSLATE_DATE_OVERRIDE)?' checked="checked"':''; ?>/> <?php _e('Use emulated date function and replace formats with the predefined formats for each language.', 'bp-translate'); ?></label><br />
								<label><input type="radio" name="use_strftime" value="<?php echo BP_TRANSLATE_STRFTIME; ?>" <?php echo ($bp_translate['use_strftime']==BP_TRANSLATE_STRFTIME)?' checked="checked"':''; ?>/> <?php _e('Use strftime instead of date.', 'bp-translate'); ?></label><br />
								<label><input type="radio" name="use_strftime" value="<?php echo BP_TRANSLATE_STRFTIME_OVERRIDE; ?>" <?php echo ($bp_translate['use_strftime']==BP_TRANSLATE_STRFTIME_OVERRIDE)?' checked="checked"':''; ?>/> <?php _e('Use strftime instead of date and replace formats with the predefined formats for each language.', 'bp-translate'); ?></label><br />
								<?php _e('Depending on the mode selected, additional customizations of the theme may be needed.', 'bp-translate'); ?>
							</td>
						</tr>
					</table>
					<h3><?php _e('Reset, Repair, and Debug', 'bp-translate') ?></h3>
					<h4><?php _e( 'Warning! These settings are only to be used when adding or completely removing BP Translate. If you use them, do not under any circumstances close your web browser, stop the loading process, or navigate away from this page until their processes complete.', 'bp-translate' ) ?></h4>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Reset BP Translate', 'bp-translate');?></th>
							<td>
								<label for="bp_translate_reset"><input type="checkbox" name="bp_translate_reset" id="bp_translate_reset" value="1"/> <?php _e('Check this box and click Save Changes to reset all BP Translate settings.', 'bp-translate'); ?></label>
								<br/>
								<label for="bp_translate_reset2"><input type="checkbox" name="bp_translate_reset2" id="bp_translate_reset2" value="1"/> <?php _e('Yes, I really want to reset BP Translate.', 'bp-translate'); ?></label>
								<br/>
								<label for="bp_translate_reset3"><input type="checkbox" name="bp_translate_reset3" id="bp_translate_reset3" value="1"/> <?php _e('Also delete Translations for all Taxonomies.', 'bp-translate'); ?></label>
								<br/>
								<?php _e('If something isn\'t working correctly, you can always try to reset all BP Translate settings. A Reset won\'t delete any posts but will remove all settings (including all languages added).', 'bp-translate'); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e('Convert Database', 'bp-translate');?></th>
							<td>
								<?php printf(__('If you have installed BP Translate for the first time on an installation with existing posts, you can either go through all your posts manually and save them in the correct language or <a href="%s">click here</a> to mark all existing posts as written in the default language.', 'bp-translate'), $clean_uri.'&markdefault=true'); ?>
								<?php _e('Be sure to make a full database backup before attempting database conversion.', 'bp-translate'); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e('Debugging Information', 'bp-translate');?></th>
							<td>
								<textarea readonly="readonly" id="bp_translate_debug">
<?php
	$bp_translate_copy = $bp_translate;
	/* Remove information to keep data anonymous and other not needed things */
	unset( $bp_translate_copy['url_info'] );
	unset( $bp_translate_copy['js'] );
	unset( $bp_translate_copy['windows_locale'] );
	unset( $bp_translate_copy['pre_domain'] );
	echo htmlspecialchars( print_r( $bp_translate_copy, true ) );
?>
								</textarea>
							</td>
						</tr>
					</table>
<?php do_action('bp_translate_conf_siteiguration', $clean_uri); ?>
					<p class="submit">
						<input type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes', 'bp-translate') ?>" />
					</p>
				</form>
			</div>
<?php
}

/* Handles the adding/removing/editing of site languages */
function bp_translate_languages() {
	global $bp_translate, $wpdb;

	// init some needed variables
	$error = '';
	$original_lang = '';
	$language_code = '';
	$language_name = '';
	$language_locale = '';
	$language_date_format = '';
	$language_time_format = '';
	$language_na_message = '';
	$language_flag = '';
	$language_default = '';
	$altered_table = false;

	$message = apply_filters( 'bp_translate_settings_pre','' );

	if ( isset( $_POST['original_lang'] ) ) {

		// validate form input
		if ( $_POST['language_na_message'] == '' )
			$error = __( 'The Language must have a Not-Available Message!', 'bp-translate' );

		if ( strlen( $_POST['language_locale'] ) < 2 )
			$error = __( 'The Language must have a Locale!', 'bp-translate' );

		if ( $_POST['language_name'] == '' )
			$error = __( 'The Language must have a name!', 'bp-translate' );

		if ( strlen( $_POST['language_code'] ) != 2 )
			$error = __( 'Language Code has to be 2 characters long!', 'bp-translate' );

		if ( $_POST['original_lang'] == '' && $error == '' ) {
			// new language
			if ( isset( $bp_translate['language_name'][$_POST['language_code']] ) )
				$error = __( 'There is already a language with the same Language Code!', 'bp-translate' );

		}

		if ( $_POST['original_lang'] != '' && $error == '' ) {
			// language update
			if ( $_POST['language_code'] != $_POST['original_lang'] && isset( $bp_translate['language_name'][$_POST['language_code']] ) ) {
				$error = __( 'There is already a language with the same Language Code!', 'bp-translate' );
			} else {
			// remove old language
				unset( $bp_translate['language_name'][$_POST['original_lang']] );
				unset( $bp_translate['flag'][$_POST['original_lang']] );
				unset( $bp_translate['locale'][$_POST['original_lang']] );
				unset( $bp_translate['date_format'][$_POST['original_lang']] );
				unset( $bp_translate['time_format'][$_POST['original_lang']] );
				unset( $bp_translate['not_available'][$_POST['original_lang']] );

				// was enabled, so set modified one to enabled too
				if ( in_array( $_POST['original_lang'], $bp_translate['enabled_languages'] ) ) {
					for ( $i = 0; $i < sizeof( $bp_translate['enabled_languages'] ); $i++ ) {
						if ( $bp_translate['enabled_languages'][$i] == $_POST['original_lang'] ) {
							$bp_translate['enabled_languages'][$i] = $_POST['language_code'];
						}
					}
				}

				// was default, so set modified the default
				if ( $_POST['original_lang'] == $bp_translate['default_site_language'] )
					$bp_translate['default_site_language'] = $_POST['language_code'];

			}
		}

		// everything is fine, insert language
		if( $error == '' ) {
			$bp_translate['language_name'][$_POST['language_code']] = $_POST['language_name'];
			$bp_translate['flag'][$_POST['language_code']] = $_POST['language_flag'];
			$bp_translate['locale'][$_POST['language_code']] = $_POST['language_locale'];
			$bp_translate['date_format'][$_POST['language_code']] = $_POST['language_date_format'];
			$bp_translate['time_format'][$_POST['language_code']] = $_POST['language_time_format'];
			$bp_translate['not_available'][$_POST['language_code']] = $_POST['language_na_message'];
		}

		// get old values in the form
		if ( $error != '' || isset( $_GET['edit'] ) ) {
			$original_lang = $_POST['original_lang'];
			$language_code = $_POST['language_code'];
			$language_name = $_POST['language_name'];
			$language_locale = $_POST['language_locale'];
			$language_date_format = $_POST['language_date_format'];
			$language_time_format = $_POST['language_time_format'];
			$language_na_message = $_POST['language_na_message'];
			$language_flag = $_POST['language_flag'];
			$language_default = $_POST['language_default'];
		}

	} elseif ( isset( $_GET['convert'] ) ) {
		// update language tags

		$wpdb->show_errors();

		foreach ( $bp_translate['enabled_languages'] as $lang ) {
			$wpdb->query('UPDATE ' . $wpdb->posts . ' set post_title = REPLACE(post_title, "[lang_' . $lang . ']","<!--:' . $lang . '-->")' );
			$wpdb->query('UPDATE ' . $wpdb->posts . ' set post_title = REPLACE(post_title, "[/lang_' . $lang . ']","<!--:-->")' );
			$wpdb->query('UPDATE ' . $wpdb->posts . ' set post_content = REPLACE(post_content, "[lang_' . $lang . ']","<!--:' . $lang . '-->")' );
			$wpdb->query('UPDATE ' . $wpdb->posts . ' set post_content = REPLACE(post_content, "[/lang_' . $lang . ']","<!--:-->")' );
		}

		$message = "Database Update successful!";

	} elseif ( isset( $_GET['markdefault'] ) ) {
		// update language tags
		
		$wpdb->show_errors();

		$result = $wpdb->get_results( 'SELECT ID, post_title, post_content FROM ' . $wpdb->posts . ' WHERE NOT (post_content LIKE "%<!--:-->%" OR post_title LIKE "%<!--:-->%")' );

		foreach( $result as $post ) {
			$content = bp_translate_split( $post->post_content );
			$title = bp_translate_split( $post->post_title );

			foreach ( $bp_translate['enabled_languages'] as $language ) {
				if ( $language != $bp_translate['default_site_language'] ) {
					$content[$language] = "";
					$title[$language] = "";
				}
			}
			$content = bp_translate_join( $content );
			$title = bp_translate_join( $title );
			$wpdb->query( 'UPDATE ' . $wpdb->posts . ' set post_content = "' . mysql_escape_string( $content ) . '", post_title = "' . mysql_escape_string( $title ) . '" WHERE ID=' . $post->ID );
		}

		$message = "All Posts marked as default language!";

	} elseif ( isset( $_GET['edit'] ) ) {
		$original_lang = $_GET['edit'];
		$language_code = $_GET['edit'];
		$language_name = $bp_translate['language_name'][$_GET['edit']];
		$language_locale = $bp_translate['locale'][$_GET['edit']];
		$language_date_format = $bp_translate['date_format'][$_GET['edit']];
		$language_time_format = $bp_translate['time_format'][$_GET['edit']];
		$language_na_message = $bp_translate['not_available'][$_GET['edit']];
		$language_flag = $bp_translate['flag'][$_GET['edit']];

	} elseif ( isset( $_GET['delete'] ) ) {

		// validate delete (protect code)
		if ( $bp_translate['default_site_language'] == $_GET['delete'] )
			$error = 'Cannot delete Default Language!';

		if ( !isset( $bp_translate['language_name'][$_GET['delete']] ) || strtolower( $_GET['delete'] ) == 'code' )
			$error = 'No such language!';

		// everything seems fine, delete language
		if ( $error == '' ) {
			bp_translate_disable_language( $_GET['delete'] );
			unset( $bp_translate['language_name'][$_GET['delete']] );
			unset( $bp_translate['flag'][$_GET['delete']] );
			unset( $bp_translate['locale'][$_GET['delete']] );
			unset( $bp_translate['date_format'][$_GET['delete']] );
			unset( $bp_translate['time_format'][$_GET['delete']] );
			unset( $bp_translate['not_available'][$_GET['delete']] );
		}

	} elseif ( isset( $_GET['enable'] ) ) {
		// enable validate
		if ( !bp_translate_enable_language( $_GET['enable'] ) )
			$error = __( 'Language is already enabled or invalid!', 'bp-translate' );

	} elseif ( isset( $_GET['disable'] ) ) {
		// enable validate
		if ( $_GET['disable'] == $bp_translate['default_site_language'] )
			$error = __( 'Cannot disable Default Language!', 'bp-translate' );

		if ( !bp_translate_is_enabled( $_GET['disable'] ) )
			if ( !isset( $bp_translate['language_name'][$_GET['disable']] ) )
				$error = __( 'No such language!', 'bp-translate' );

		// everything seems fine, disable language
		if ( $error == '' && !bp_translate_disable_language( $_GET['disable'] ) )
			$error = __( 'Language is already disabled!', 'bp-translate' );

	}

	/* Evertyhing fine? */
	$everything_fine = ( (
		isset( $_POST['submit'] ) ||
		isset( $_GET['delete'] ) ||
		isset( $_GET['enable'] ) ||
		isset( $_GET['disable'] ) ||
		isset( $_GET['moveup'] ) ||
		isset( $_GET['movedown'] ) ) &&
		$error == ''
	);

	// settings might have changed, so save
	if ( $everything_fine ) {
		bp_translate_save_settings_site();

		if ( empty( $message ) )
			$message = __( 'Options saved.', 'bp-translate' );
	}

	// don't accidently delete/enable/disable twice
	$clean_uri = preg_replace( "/&(delete|enable|disable|convert|markdefault|moveup|movedown)=[^&#]*/i", "", $_SERVER['REQUEST_URI'] );
	$clean_uri = apply_filters( 'bp_translate_clean_uri', $clean_uri );

// Generate HTML

?>
<?php
	if ( $message ) :
?>
		<div id="message" class="updated fade"><p><strong><?php echo $message; ?></strong></p></div>
<?php
	endif;
?>
<?php
	if ( $error != '' ) :
?>
		<div id="message" class="error fade"><p><strong><?php echo $error; ?></strong></p></div>
<?php
	endif;
?>
<?php
	if ( isset( $_GET['edit'] ) ) {
?>
		<div class="wrap">
			<h2><?php _e('Edit Language', 'bp-translate'); ?></h2>
			<form action="" method="post" id="bp_translate-edit-language">
<?php
	bp_translate_language_form_site( $language_code, $language_code, $language_name, $language_locale, $language_date_format, $language_time_format, $language_flag, $language_na_message, $language_default, $original_lang );
?>
				<p class="submit"><input type="submit" name="submit" value="<?php _e('Save Changes &raquo;', 'bp-translate'); ?>" /></p>
			</form>
		</div>
<?php
	} else {
?>
		<div class="wrap">
			<h2><?php _e('Languages', 'bp-translate') ?></h2>
			<div id="col-container">
				<div id="col-right">
					<div class="col-wrap">
						<table class="widefat">
							<thead>
							<tr>
<?php print_column_headers('language'); ?>
							</tr>
							</thead>

							<tfoot>
							<tr>
<?php print_column_headers('language', false); ?>
							</tr>
							</tfoot>

							<tbody id="the-list" class="list:cat">
<?php
	foreach( $bp_translate['language_name'] as $lang => $language ) {
		if($lang!='code') {
?>
								<tr>
									<td><img src="<?php echo trailingslashit(WP_CONTENT_URL).$bp_translate['flag_location'].$bp_translate['flag'][$lang]; ?>" alt="<?php echo $language; ?> Flag"></td>
									<td><?php echo $language; ?></td>
									<td><?php if ( in_array( $lang, $bp_translate['enabled_languages'] ) ) { ?><a class="edit" href="<?php echo $clean_uri; ?>&disable=<?php echo $lang; ?>"><?php _e('Disable', 'bp-translate'); ?></a><?php  } else { ?><a class="edit" href="<?php echo $clean_uri; ?>&enable=<?php echo $lang; ?>"><?php _e('Enable', 'bp-translate'); ?></a><?php } ?></td>
									<td><a class="edit" href="<?php echo $clean_uri; ?>&edit=<?php echo $lang; ?>"><?php _e('Edit', 'bp-translate'); ?></a></td>
									<td><?php if ( $bp_translate['default_site_language'] == $lang ) { ?><?php _e('Default', 'bp-translate'); ?><?php  } else { ?><a class="delete" href="<?php echo $clean_uri; ?>&delete=<?php echo $lang; ?>"><?php _e('Delete', 'bp-translate'); ?></a><?php } ?></td>
								</tr>
<?php }} ?>
							</tbody>
					</table>
					<p><?php _e('Enabling a language will cause BP Translate to update the Gettext-Database for the language, which can take a while depending on your server\'s connection speed.','bp-translate');?></p>
				</div>
			</div><!-- /col-right -->
			<div id="col-left">
				<div class="col-wrap">
					<div class="form-wrap">
						<h3><?php _e('Add Language', 'bp-translate'); ?></h3>
						<form name="addcat" id="addcat" method="post" class="add:the-list: validate">
<?php bp_translate_language_form_site( $language_code, $language_code, $language_name, $language_locale, $language_date_format, $language_time_format, $language_flag, $language_default, $language_na_message ); ?>
							<p class="submit">
								<input type="submit" name="submit" value="<?php _e('Add Language', 'bp-translate'); ?>" />
							</p>
						</form>
					</div>
				</div>
			</div><!-- /col-left -->
		</div><!-- /col-container -->
<?php
	}
}

?>