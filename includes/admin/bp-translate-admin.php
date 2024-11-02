<?php

/**
 * BP_Translate_Admin
 *
 * Admin class for BP Member Map
 */
class BP_Translate_Admin {

	function init() {
		add_action( 'admin_head', array( 'BP_Translate_Admin', 'admin_head' ) );
	}

	function admin_head() {
		global $profileuser;

	}

	function update_profile_language ( $user_id ) {

		if ( !current_user_can( 'edit_user', $user_id ) )
			return false;

		update_user_meta( $user_id, 'WPLANG',		$_POST['WPLANG'] );
		update_user_meta( $user_id, 'WPLANG_ADMIN',	$_POST['WPLANG_ADMIN'] );

	}

	function user_profile_language( $profileuser ) {
		global $bp_translate;

		$lang			= get_user_meta( $profileuser->ID, 'WPLANG', true );
		$admin_lang		= get_user_meta( $profileuser->ID, 'WPLANG_ADMIN', true ); ?>

		<h3><?php _e( 'Language', 'bp-translate' ); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Website Language', 'bp-translate' ); ?></th>
				<td>
					<select name="WPLANG" id="WPLANG">
<?php foreach ( bp_translate_get_sorted_languages() as $language ) { ?>
						<option value="<?php echo $bp_translate['locale'][$language] ?>" <?php selected( $lang, $bp_translate['locale'][$language] ); ?>><?php echo $bp_translate['language_name'][$language] ?></option>
<?php } ?>
					</select>

				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Administration Language', 'bp-translate' ); ?></th>
				<td>
					<select name="WPLANG_ADMIN" id="WPLANG_ADMIN">
<?php foreach ( bp_translate_get_sorted_languages() as $language ) { ?>
						<option value="<?php echo $bp_translate['locale'][$language] ?>" <?php selected( $admin_lang, $bp_translate['locale'][$language] ); ?>><?php echo $bp_translate['language_name'][$language] ?></option>
<?php } ?>
					</select>

				</td>
			</tr>
		</table>
<?php
	}
}
add_action( 'init', array( 'BP_Translate_Admin', 'init' ) );
add_action( 'edit_user_profile',		array( 'BP_Translate_Admin', 'user_profile_language' ) );
add_action( 'show_user_profile',		array( 'BP_Translate_Admin', 'user_profile_language' ) );
add_action( 'personal_options_update',	array( 'BP_Translate_Admin', 'update_profile_language' ) );
add_action( 'edit_user_profile_update',	array( 'BP_Translate_Admin', 'update_profile_language' ) );


/* BP Translate blog language settings */
function bp_translate_admin_menus() {
	global $menu, $submenu, $bp_translate;

	/* Add site level language settings */
	add_options_page( __( 'Language', 'bp-translate' ), __( 'Language', 'bp-translate' ), 'activate_plugins', 'bp-translate-blog', 'bp_translate_settings_blog' );

}

function bp_translate_check_setting_blog( $var, $update = false, $type = BP_TRANSLATE_STRING ) {
	global $bp_translate;

	switch ( $type ) {
		case BP_TRANSLATE_URL:
			$_POST[$var] = trailingslashit( $_POST[$var] );

		case BP_TRANSLATE_LANGUAGE:
		case BP_TRANSLATE_STRING:
			if ( isset( $_POST['submit'] ) && isset( $_POST[$var] ) ) {
				if ( $type != BP_TRANSLATE_LANGUAGE || bp_translate_is_enabled( $_POST[$var] ) )
					$bp_translate[$var] = $_POST[$var];

				if ( $update )
					update_option( 'bp_translate_' . $var, $bp_translate[$var] );

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

				if ( $update ) {
					if ( $bp_translate[$var] )
						update_option( 'bp_translate_' . $var, '1' );
					else
						update_option( 'bp_translate_' . $var, '0' );

				}
				return true;

			} else {
				return false;
			}
			break;

		case BP_TRANSLATE_INTEGER:
			if ( isset( $_POST['submit'] ) && isset( $_POST[$var] ) ) {
				$bp_translate[$var] = intval($_POST[$var]);
				if ( $update )
					update_option( 'bp_translate_' . $var, $bp_translate[$var] );

				return true;

			} else {
				return false;
			}
			break;

	}

	return false;
}

function bp_translate_settings_blog() {
	global $bp_translate, $wpdb;

	/* Init some needed variables */
	$error = '';
	$altered_table = false;

	$message = apply_filters( 'bp_translate_settings_pre', '' );

	/* Check for action */
	if ( isset( $_POST['bp_translate_reset'] ) && isset( $_POST['bp_translate_reset2'] ) ) {
		$message = __( 'BP Translate has been reset.', 'bp-translate' );

	/* Save settings */
	} elseif ( isset( $_POST['default_blog_language'] ) ) {
		bp_translate_check_setting_blog( 'default_blog_language',			true, BP_TRANSLATE_LANGUAGE );
		bp_translate_check_setting_blog( 'hide_blog_untranslated',			true, BP_TRANSLATE_BOOLEAN );
		bp_translate_check_setting_blog( 'use_strftime',					true, BP_TRANSLATE_INTEGER );
	}

	/* Update language tags */
	if ( isset( $_GET['convert'] ) ) {

		$wpdb->show_errors();

		foreach ( $bp_translate['enabled_languages'] as $lang ) {
			$wpdb->query( 'UPDATE ' . $wpdb->posts . ' set post_title = REPLACE(post_title, "[lang_' . $lang . ']","<!--:' . $lang . '-->")' );
			$wpdb->query( 'UPDATE ' . $wpdb->posts . ' set post_title = REPLACE(post_title, "[/lang_' . $lang . ']","<!--:-->")' );
			$wpdb->query( 'UPDATE ' . $wpdb->posts . ' set post_content = REPLACE(post_content, "[lang_' . $lang . ']","<!--:' . $lang . '-->")' );
			$wpdb->query( 'UPDATE ' . $wpdb->posts . ' set post_content = REPLACE(post_content, "[/lang_' . $lang . ']","<!--:-->")' );
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
				if ( $language != $bp_translate['default_blog_language'] ) {
					$content[$language] = "";
					$title[$language] = "";
				}
			}

			$content = bp_translate_join( $content );
			$title = bp_translate_join( $title );

			$wpdb->query( 'UPDATE ' . $wpdb->posts . ' set post_content = "' . mysql_escape_string( $content ) . '", post_title = "' . mysql_escape_string( $title ) . '" WHERE ID=' . $post->ID );
		}

		$message = __( 'All Posts marked as default language!', 'bp-translate' );

	}

	/* Evertyhing fine? */
	$everything_fine = ( (
		isset( $_POST['submit'] ) ) &&
		$error == ''
	);

	/* Settings might have changed, so save */
	if ( $everything_fine ) {
		bp_translate_save_settings_blog();

		if ( empty( $message ) )
			$message = __( 'Options saved.', 'bp-translate' );
	}

	// don't accidently delete/enable/disable twice
	$clean_uri = preg_replace( "/&(markdefault)=[^&#]*/i", "", $_SERVER['REQUEST_URI'] );
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
					<h3><?php _e('General Settings', 'bp-translate') ?></h3>
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e('Default Language', 'bp-translate') ?></th>
							<td>
								<fieldset><legend class="hidden"><?php _e('Default Language', 'bp-translate') ?></legend>
<?php
		foreach ( bp_translate_get_sorted_languages() as $key => $language ) {
			echo "\t<label title='" . $bp_translate['language_name'][$language] . "'><input type='radio' name='default_blog_language' value='" . $language . "'";
			if ( $language == $bp_translate['default_blog_language'] ) {
				echo " checked='checked'";
			}
			echo ' />';
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
								<label for="hide_blog_untranslated"><input type="checkbox" name="hide_blog_untranslated" id="hide_blog_untranslated" value="1"<?php echo ($bp_translate['hide_blog_untranslated'])?' checked="checked"':''; ?>/> <?php _e('Hide Content which is not available for the selected language.', 'bp-translate'); ?></label>
								<br/>
								<span><?php _e( 'If checked, posts will be hidden if the content is not available for the selected language.<br />If unchecked, a message will appear showing all the languages the content is available in.', 'bp-translate' ); ?></span>
								<p class="description"><?php _e( 'This will not work if you installed BP Translate on a blog with existing entries. Please see "Convert Database" under "Maintenance".', 'bp-translate' ); ?></p>
							</td>
						</tr>
					</table>
					<h3><?php _e( 'Maintenance', 'bp-translate') ?></h3>
					<h4><?php _e( 'Use these settings carefully. Do not close your web browser, stop the loading process, or navigate away from this page until these settings have completely saved.', 'bp-translate' ) ?></h4>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Reset BP Translate', 'bp-translate');?></th>
							<td>
								<label for="bp_translate_reset"><input type="checkbox" name="bp_translate_reset" id="bp_translate_reset" value="1"/> <?php _e('Check this box and click Save Changes to reset BP Translate settings for your blog.', 'bp-translate'); ?></label>
								<br/>
								<label for="bp_translate_reset2"><input type="checkbox" name="bp_translate_reset2" id="bp_translate_reset2" value="1"/> <?php _e('Yes, I really want to reset BP Translate.', 'bp-translate'); ?></label>
								<br/>
								<label for="bp_translate_reset3"><input type="checkbox" name="bp_translate_reset3" id="bp_translate_reset3" value="1"/> <?php _e('Also delete Translations for your blogs Taxonomies.', 'bp-translate'); ?></label>
								<br/>
								<?php _e('If something isn\'t working correctly, you can always try to reset all BP Translate settings. A Reset won\'t delete any posts but will remove all settings.', 'bp-translate'); ?>
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
	unset( $bp_translate_copy['term_name'] );
	echo htmlspecialchars( print_r( $bp_translate_copy, true ) );
?>
								</textarea>
							</td>
						</tr>
					</table>
<?php do_action( 'bp_translate_settings_blog', $clean_uri ); ?>
					<p class="submit">
						<input type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes', 'bp-translate') ?>" />
					</p>
				</form>
			</div>
<?php
}

/* Following functions were moved from bp-translate-wphacks */
function bp_translate_force_html_editor_default() {
	return 'html';
}

// modifies term form to support multilingual content
function bp_translate_modify_term_form( $id, $name, $term ) {
	global $bp_translate;

	echo "<script type=\"text/javascript\">\n// <![CDATA[\r\n";

	// ' workaround
	$termname = $term->name;

	// create input fields for each language
	foreach ( $bp_translate['enabled_languages'] as $language )
		if ( $_GET['action'] == 'edit' )
			echo bp_translate_insert_term_input2( $id, $name, $termname, $language );
		else
			echo bp_translate_insert_term_input( $id, $name, $termname, $language );

	// hide real category text
	echo "ins.style.display='none';\n";
	echo "// ]]>\n</script>\n";
}

function bp_translate_modify_category_form($term) {
	return bp_translate_modify_term_form('tag-name', __('Category Name', 'bp-translate'), $term);
}

function bp_translate_modify_tag_form($term) {
	if ( !isset( $_GET['tag_ID'] ) )
		return bp_translate_modify_term_form('tag-name', __('Tag Name', 'bp-translate'), $term);
	else
		return bp_translate_modify_term_form('name', __('Tag Name', 'bp-translate'), $term);
}

function bp_translate_modify_link_category_form($term) {
	return bp_translate_modify_term_form('name', __('Category Name', 'bp-translate'), $term);
}

// modifies TinyMCE to edit multilingual content
function bp_translate_modify_tinymce ( $old_content ) {
	global $bp_translate, $bp_translate_term;

	$init_editor = true;
	if ( $GLOBALS['wp_version'] != BP_TRANSLATE_SUPPORTED_WP_VERSION ) {
		if ( $_REQUEST['bp_translateincompatiblemessage'] != "shown" )
			echo '<p class="updated" id="bp_translate_imsg">' . __( 'Due to an incompatibility, multilanguage posting has been turned off. <a href="javascript:bp_translate_editorInit();" title="Activate BP Translate" id="bp_translate_imsg_link">Turn on one time.</a>', 'bp-translate').'</p>';

		$init_editor = false;
	}

	preg_match( "/<textarea[^>]*id='([^']+)'/", $old_content, $matches );
	$id = $matches[1];

	preg_match( "/cols='([^']+)'/", $old_content, $matches );
	$cols = $matches[1];

	preg_match( "/rows='([^']+)'/", $old_content, $matches );
	$rows = $matches[1];
	
	// don't do anything if not editing the content
	if ( $id != "content" )
		return $old_content;

	// don't do anything to the editor if it's not rich
	if ( !user_can_richedit() )
		return $old_content;

	$content = "";
	$content_append = "";

	// create editing field for selected languages
	$old_content = substr( $old_content, 0, 26 )
		. "<textarea id='bp_translate_textarea_" . $id . "' name='bp_translate_textarea_" . $id . "' tabindex='2' rows='" . $rows . "' cols='" . $cols . "' style='display:none' onblur='bp_translate_save(this.value);'></textarea>"
		. substr( $old_content, 26 );

	// do some crazy js to alter the admin view
	$content .= "<script type=\"text/javascript\">\n// <![CDATA[\n";
	$content .= "function bp_translate_editorInit1() {\n";

	// include needed js functions
	$content .= $bp_translate['js']['bp_translate_is_array'];
	$content .= $bp_translate['js']['bp_translate_xsplit'];
	$content .= $bp_translate['js']['bp_translate_split'];
	$content .= $bp_translate['js']['bp_translate_integrate'];
	$content .= $bp_translate['js']['bp_translate_use'];
	$content .= $bp_translate['js']['bp_translate_switch'];
	$content .= $bp_translate['js']['bp_translate_assign'];
	$content .= $bp_translate['js']['bp_translate_save'];
	$content .= $bp_translate['js']['bp_translate_integrate_title'];
	$content .= $bp_translate['js']['bp_translate_get_active_language'];

	// insert language, visual and html buttons
	$el = bp_translate_get_sorted_languages();

	foreach($el as $language) {
		$content .= bp_translate_insert_title_input($language);
	}

	$el = bp_translate_get_sorted_languages(true);
	foreach($el as $language) {
		$content .= bp_translate_create_tinymce_toolbar_button($language, $id);
	}

	$content = apply_filters( 'bp_translate_toolbar', $content );

	// hijack tinymce control
	$content .= $bp_translate['js']['bp_translate_disable_old_editor'];

	// hide old title bar
	if ( !isset( $bp_translate_term ) )
		$content .= "document.getElementById('titlediv').style.display='none';\n";

	$content .="}\n";

	$content .= "// ]]>\n</script>\n";

	$content_append .= "<script type=\"text/javascript\">\n// <![CDATA[\n";
	$content_append .= "function bp_translate_editorInit2() {\n";

	// show default language tab
	$content_append .= "document.getElementById('content').style.display='none';\n";
	$content_append .= "document.getElementById('bp_translate_select_".$bp_translate['default_blog_language']."').className='edButton active';\n";
	// show default language
	$content_append .= "var ta = document.getElementById('".$id."');\n";
	$content_append .= "bp_translate_assign('bp_translate_textarea_".$id."',bp_translate_use('".$bp_translate['default_blog_language']."',ta.value));\n";

	$content_append .= "}\n";

	$content_append .= "function bp_translate_editorInit3() {\n";
	// make tinyMCE get the correct data
	$content_append .= $bp_translate['js']['bp_translate_tinyMCEOverload'];
	$content_append .= "}\n";
	$content_append .= "function bp_translate_editorInit() {\n";
	$content_append .= "bp_translate_editorInit1();\n";
	$content_append .= "bp_translate_editorInit2();\n";
	$content_append .= "jQuery('#bp_translate_imsg').hide();\n";
	$content_append .= "bp_translate_editorInit3();\n";
	$content_append .= "}\n";

	if ( $init_editor ) {
		$content_append .= $bp_translate['js']['bp_translate_wpOnload'];
	} else {
		$content_append .= "var qtmsg = document.getElementById('bp_translate_imsg');\n";
		$content_append .= "var et = document.getElementById('editor-toolbar');\n";
		$content_append .= "et.parentNode.insertBefore(qtmsg, et);\n";
	}
	$content_append = apply_filters( 'bp_translate_modify_editor_js', $content_append );
	$content_append .= "// ]]>\n</script>\n";

	return $content . $old_content . $content_append;
}

function bp_translate_insert_term_input( $id, $name, $term, $language ) {
	global $bp_translate;

	$html = "
		var il = document.getElementsByTagName('input');
		var d =  document.createElement('div');
		var l = document.createTextNode('" . $name . " (" . $bp_translate['language_name'][$language] . ")');
		var ll = document.createElement('label');
		var i = document.createElement('input');
		var ins = null;
		for(var j = 0; j < il.length; j++) {
			if(il[j].id=='" . $id . "') {
				ins = il[j];
				break;
			}
		}
		i.type = 'text';
		i.id = i.name = ll.htmlFor ='bp_translate_term_" . $language . "';
	";
	if ( isset( $bp_translate['term_name'][$term][$language] ) ) {
		$html .="
			i.value = '" . addslashes( htmlspecialchars_decode( $bp_translate['term_name'][$term][$language], ENT_NOQUOTES ) ) . "';
			";
	} else {
		$html .="
			i.value = ins.value;
			";
	}
	if ( $language == $bp_translate['default_blog_language'] ) {
		$html .="
			i.onchange = function() {
				var il = document.getElementsByTagName('input');
				var ins = null;
				for(var j = 0; j < il.length; j++) {
					if(il[j].id=='" . $id . "') {
						ins = il[j];
						break;
					}
				}
				ins.value = document.getElementById('bp_translate_term_" . $language . "').value;
			};
		";
	}
	$html .="
		ins = ins.parentNode;
		d.className = 'form-field form-required';
		ll.appendChild(l);
		d.appendChild(ll);
		d.appendChild(i);
		ins.parentNode.insertBefore(d,ins);
		";
	return $html;
}

function bp_translate_insert_term_input2( $id, $name, $term, $language ) {
	global $bp_translate;

	$html = "
		var tr = document.createElement('tr');
		var th = document.createElement('th');
		var ll = document.createElement('label');
		var l = document.createTextNode('" . $name . " (" . $bp_translate['language_name'][$language] . ")');
		var td = document.createElement('td');
		var i = document.createElement('input');
		var ins = document.getElementById('" . $id . "');
		i.type = 'text';
		i.id = i.name = ll.htmlFor ='bp_translate_term_" . $language . "';
	";

	if ( isset( $bp_translate['term_name'][$term][$language] ) ) {
		$html .= "
			i.value = '" . addslashes( htmlspecialchars_decode( $bp_translate['term_name'][$term][$language], ENT_QUOTES ) ) . "';
			";
	} else {
		$html .= "
			i.value = ins.value;
			";
	}

	if ( $language == $bp_translate['default_blog_language'] ) {
		$html .= "
			i.onchange = function() {
				var il = document.getElementsByTagName('input');
				var ins = null;
				for(var j = 0; j < il.length; j++) {
					if(il[j].id=='" . $id . "') {
						ins = il[j];
						break;
					}
				}
				ins.value = document.getElementById('bp_translate_term_" . $language . "').value;
			};
			";
	}

	$html .= "
		ins = ins.parentNode.parentNode;
		tr.className = 'form-field form-required';
		th.scope = 'row';
		th.vAlign = 'top';
		ll.appendChild(l);
		th.appendChild(ll);
		tr.appendChild(th);
		td.appendChild(i);
		tr.appendChild(td);
		ins.parentNode.insertBefore(tr,ins);
		";

	return $html;
}

function bp_translate_insert_title_input($language){
	global $bp_translate;
	$html ="
		var td = document.getElementById('titlediv');
		var qtd = document.createElement('div');
		var h = document.createElement('h3');
		var l = document.createTextNode('".__("Title", 'bp-translate')." (".$bp_translate['language_name'][$language].")');
		var tw = document.createElement('div');
		var ti = document.createElement('input');
		var slug = document.getElementById('edit-slug-box');

		ti.type = 'text';
		ti.id = 'bp_translate_title_".$language."';
		ti.tabIndex = '1';
		ti.value = bp_translate_use('".$language."', document.getElementById('title').value);
		ti.onchange = bp_translate_integrate_title;
		ti.className = 'bp_translate_title_input';
		h.className = 'bp_translate_title';
		tw.className = 'bp_translate_title_wrap';

		qtd.className = 'postarea';

		h.appendChild(l);
		tw.appendChild(ti);
		qtd.appendChild(h);
		qtd.appendChild(tw);";
	if($bp_translate['default_blog_language'] == $language)
		$html.="if(slug) qtd.appendChild(slug);";
	$html.="
		td.parentNode.insertBefore(qtd,td);

		";
	return $html;
}

function bp_translate_create_tinymce_toolbar_button($language, $id, $js_function = 'switchEditors.go', $label = ''){
	global $bp_translate;
	$html = "
		var bc = document.getElementById('editor-toolbar');
		var mb = document.getElementById('media-buttons');
		var ls = document.createElement('a');
		var l = document.createTextNode('".(($label==='')?$bp_translate['language_name'][$language]:$label)."');
		ls.id = 'bp_translate_select_".$language."';
		ls.className = 'edButton';
		ls.onclick = function() { ".$js_function."('".$id."','".$language."'); };
		ls.appendChild(l);
		bc.insertBefore(ls,mb);
		";
	return $html;
}

?>