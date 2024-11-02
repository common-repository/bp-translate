<?php

/* BP Translate Core Functions */

function bp_translate_init() {
	global $bp_translate, $bp;

	/* Check if BP Translate is already initialized */
	if ( defined( 'BPTRANS_INIT' ) )
		return;

	define( 'BPTRANS_INIT', true );

	/* Reseted configuration is site admin requests it */
	if ( isset( $_POST['bp_translate_reset'] ) && isset( $_POST['bp_translate_reset2'] ) && defined( 'WP_ADMIN' ) && current_user_can( 'manage_options' ) ) {
		delete_site_option( 'bp_translate_language_names' );
		delete_site_option( 'bp_translate_enabled_languages' );
		delete_site_option( 'bp_translate_flag_location' );
		delete_site_option( 'bp_translate_flags' );
		delete_site_option( 'bp_translate_locales' );
		delete_site_option( 'bp_translate_na_messages' );
		delete_site_option( 'bp_translate_date_formats' );
		delete_site_option( 'bp_translate_time_formats' );
		delete_site_option( 'bp_translate_use_strftime' );
		delete_site_option( 'bp_translate_ignore_file_types' );
		delete_site_option( 'bp_translate_url_mode' );
		delete_site_option( 'bp_translate_detect_browser_language' );
		delete_site_option( 'bp_translate_default_site_language' );
		delete_site_option( 'bp_translate_hide_site_untranslated' );
		delete_site_option( 'bp_translate_auto_update_mo' );
		delete_site_option( 'bp_translate_next_update_mo' );

		if ( isset( $_POST['bp_translate_reset3'] ) )
			delete_blog_option('bp_translate_term_name');

	}

	/* Load up configuration */
	bp_translate_load_config();

	/* Init Javascript functions */
	bp_translate_init_js();

	/* Update Gettext Databases if on Backend */
	if ( defined( 'WP_ADMIN' ) && $bp_translate['auto_update_mo'] )
		bp_translate_update_gettext_databases();

	/* Update definitions if neccesary */
	if ( defined( 'WP_ADMIN' ) && current_user_can( 'manage_categories' ) )
		bp_translate_update_term_library();

}

function bp_translate_validate_bool( $var, $default ) {
	if ( $var === '0' )
		return false;
	elseif ( $var === '1' )
		return true;
	else
		return $default;
}

/* Loads config and defaults to values set on top */
function bp_translate_load_config() {
	global $bp_translate;

	/* Load site options */
	$language_names =			get_site_option( 'bp_translate_language_names' );
	$enabled_languages =		get_site_option( 'bp_translate_enabled_languages' );
	$default_site_language =	get_site_option( 'bp_translate_default_site_language' );
	$flag_location =			get_site_option( 'bp_translate_flag_location' );
	$flags =					get_site_option( 'bp_translate_flags' );
	$locales =					get_site_option( 'bp_translate_locales' );
	$na_messages =				get_site_option( 'bp_translate_na_messages' );
	$date_formats =				get_site_option( 'bp_translate_date_formats' );
	$time_formats =				get_site_option( 'bp_translate_time_formats' );
	$use_strftime =				get_site_option( 'bp_translate_use_strftime' );
	$ignore_file_types =		get_site_option( 'bp_translate_ignore_file_types' );
	$detect_browser_language =	get_site_option( 'bp_translate_detect_browser_language' );
	$hide_site_untranslated =	get_site_option( 'bp_translate_hide_site_untranslated' );
	$auto_update_mo =			get_site_option( 'bp_translate_auto_update_mo' );
	$url_mode =					get_site_option( 'bp_translate_url_mode' );
	$mu_blog_list =				get_site_option( 'bp_translate_blog_list' );

	/* Load blog options */
	$default_blog_language =	get_option( 'bp_translate_default_blog_language' );
	$hide_blog_untranslated =	get_option( 'bp_translate_hide_blog_untranslated' );
	$term_name =				get_option( 'bp_translate_term_name' );

	/* Load user options */
	if ( is_user_logged_in() ) :
		$default_user_language =	get_user_option( 'WPLANG' );
		$hide_user_untranslated =	get_user_option( 'bp_translate_hide_user_untranslated' );
	endif;

	/* Default if not set */
	if ( !is_array( $mu_blog_list ) )
		$mu_blog_list = bp_translate_get_all_blogs();

	if ( !is_array( $term_name ) )
		$term_name = $bp_translate['term_name'];

	if ( !is_array( $ignore_file_types ) )
		$ignore_file_types = $bp_translate['ignore_file_types'];

	if ( !is_array( $date_formats ) )
		$date_formats = $bp_translate['date_format'];

	if ( !is_array( $time_formats ) )
		$time_formats = $bp_translate['time_format'];

	if ( !is_array( $na_messages ) )
		$na_messages = $bp_translate['not_available'];

	if ( !is_array( $locales ) )
		$locales = $bp_translate['locale'];

	if ( !is_array( $flags ) )
		$flags = $bp_translate['flag'];

	if ( !is_array( $language_names ) )
		$language_names = $bp_translate['language_name'];

	if ( !is_array( $enabled_languages ) )
		$enabled_languages = $bp_translate['enabled_languages'];

	if ( !is_string( $flag_location ) || $flag_location === '' )
		$flag_location = $bp_translate['flag_location'];

	if ( empty( $use_strftime ) )
		$use_strftime = $bp_translate['use_strftime'];

	if ( empty( $url_mode ) )
		$url_mode = $bp_translate['url_mode'];

	if ( empty( $default_site_language ) )
		$default_site_language = $bp_translate['default_site_language'];

	if ( empty( $default_blog_language ) )
		$default_blog_language = $bp_translate['default_blog_language'];

	if ( empty( $default_user_language ) )
		$default_user_language = $bp_translate['default_user_language'];

	$hide_blog_untranslated =	bp_translate_validate_bool( $hide_blog_untranslated, $bp_translate['hide_blog_untranslated'] );
	$hide_site_untranslated =	bp_translate_validate_bool( $hide_site_untranslated, $bp_translate['hide_site_untranslated'] );
	$hide_user_untranslated =	bp_translate_validate_bool( $hide_user_untranslated, $bp_translate['hide_user_untranslated'] );

	$detect_browser_language =	bp_translate_validate_bool( $detect_browser_language, $bp_translate['detect_browser_language'] );
	$auto_update_mo =			bp_translate_validate_bool( $auto_update_mo, $bp_translate['auto_update_mo'] );
	$flag_location =			trailingslashit( preg_replace( '#^wp-content/#', '', $flag_location ) );

	/* Check for invalid permalink/url mode combinations */
	$permalink_structure =		get_option( 'permalink_structure' );
	if ( $permalink_structure === "" || strpos( $permalink_structure, '?' ) !== false || strpos( $permalink_structure, 'index.php' ) !== false )
		$url_mode = BP_TRANSLATE_URL_PATH;

	/* Overwrite default site values with loaded values */
	$bp_translate['date_format'] =				$date_formats;
	$bp_translate['time_format'] =				$time_formats;
	$bp_translate['not_available'] =			$na_messages;
	$bp_translate['locale'] =					$locales;
	$bp_translate['flag'] =						$flags;
	$bp_translate['language_name'] =			$language_names;
	$bp_translate['enabled_languages'] =		$enabled_languages;
	$bp_translate['flag_location'] =			$flag_location;
	$bp_translate['use_strftime'] =				$use_strftime;
	$bp_translate['ignore_file_types'] =		$ignore_file_types;
	$bp_translate['url_mode'] =					$url_mode;
	$bp_translate['detect_browser_language'] =	$detect_browser_language;
	$bp_translate['auto_update_mo'] =			$auto_update_mo;
	$bp_translate['mu_blog_list'] =				$mu_blog_list;
	$bp_translate['default_site_language'] =	$default_site_language;
	$bp_translate['hide_site_untranslated'] =	$hide_site_untranslated;

	/* Overwrite default blog values with loaded values */
	$bp_translate['term_name'] =				$term_name;
	$bp_translate['default_blog_language'] =	$default_blog_language;
	$bp_translate['hide_blog_untranslated'] =	$hide_blog_untranslated;

	/* Overwrite default user values with loaded values */
	$bp_translate['default_user_language'] =	$default_user_language;
	$bp_translate['hide_user_untranslated'] =	$hide_user_untranslated;

	/**
	 * Global override...
	 * Start with site...
	 */
	$bp_translate['default_language'] =			$bp_translate['default_site_language'];
	$bp_translate['hide_untranslated'] =		$bp_translate['hide_site_untranslated'];

	/* ... and let blogs override */
	if ( $default_blog_language )
		$bp_translate['default_language'] =		$bp_translate['default_blog_language'];

	if ( $hide_blog_untranslated )
		$bp_translate['hide_untranslated'] =	$bp_translate['hide_blog_untranslated'];

	/* ... and let users override
			if ( $default_user_language )
				$bp_translate['default_language'] =		$bp_translate['default_user_language'];

			if ( $hide_user_untranslated )
				$bp_translate['hide_untranslated'] =	$bp_translate['hide_user_untranslated'];
	/**/

	do_action( 'bp_translate_load_config' );
}

/* Saves site configuration */
function bp_translate_save_settings_site() {
	global $bp_translate;

	/* Save everything */
	update_site_option( 'bp_translate_language_names',			$bp_translate['language_name'] );
	update_site_option( 'bp_translate_enabled_languages',		$bp_translate['enabled_languages'] );
	update_site_option( 'bp_translate_default_site_language',	$bp_translate['default_site_language'] );
	update_site_option( 'bp_translate_flag_location',			$bp_translate['flag_location'] );
	update_site_option( 'bp_translate_flags',					$bp_translate['flag'] );
	update_site_option( 'bp_translate_locales',					$bp_translate['locale'] );
	update_site_option( 'bp_translate_na_messages',				$bp_translate['not_available'] );
	update_site_option( 'bp_translate_date_formats',			$bp_translate['date_format'] );
	update_site_option( 'bp_translate_time_formats',			$bp_translate['time_format'] );
	update_site_option( 'bp_translate_ignore_file_types',		$bp_translate['ignore_file_types'] );
	update_site_option( 'bp_translate_url_mode',				$bp_translate['url_mode'] );
	update_site_option( 'bp_translate_use_strftime',			$bp_translate['use_strftime'] );

	if ( $bp_translate['detect_browser_language'] )
		update_site_option( 'bp_translate_detect_browser_language', '1' );
	else
		update_site_option( 'bp_translate_detect_browser_language', '0' );

	if ( $bp_translate['hide_site_untranslated'] )
		update_site_option( 'bp_translate_hide_site_untranslated', '1' );
	else
		update_site_option( 'bp_translate_hide_site_untranslated', '0' );

	if ( $bp_translate['auto_update_mo'] )
		update_site_option( 'bp_translate_auto_update_mo', '1' );
	else
		update_site_option( 'bp_translate_auto_update_mo', '0' );

	do_action( 'bp_translate_save_settings_site' );
}

/* Saves site configuration */
function bp_translate_save_settings_blog() {
	global $bp_translate;

	/* Save everything */
	update_option( 'bp_translate_default_blog_language',		$bp_translate['default_blog_language'] );
	update_option( 'bp_translate_term_name',					$bp_translate['term_name'] );

	if ( $bp_translate['hide_blog_untranslated'] )
		update_option( 'bp_translate_hide_blog_untranslated', '1' );
	else
		update_option( 'bp_translate_hide_blog_untranslated', '0' );

	do_action( 'bp_translate_save_settings_blog' );
}

function bp_translate_update_gettext_databases( $force = false, $only_for_language = '' ) {
	global $bp_translate;

	if ( !is_dir( WP_LANG_DIR ) ) {
		if ( !@mkdir( WP_LANG_DIR ) )
			return false;
	}

	$next_update = get_site_option( 'bp_translate_next_update_mo' );

	if ( time() < $next_update && !$force )
		return true;

	update_site_option( 'bp_translate_next_update_mo', time() + 7 * 24 * 60 * 60 );

	foreach ( $bp_translate['locale'] as $lang => $locale ) {
		if ( bp_translate_is_enabled( $only_for_language ) && $lang != $only_for_language ) continue;
		if ( !bp_translate_is_enabled( $lang ) ) continue;
		if ( $ll = @fopen( trailingslashit( WP_LANG_DIR ) . $locale . '.mo.filepart', 'a' ) ) {
		// can access .mo file
			fclose($ll);
			// try to find a .mo file
			if( !$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/' . $locale . '/tags/' . $GLOBALS['wp_version'] . '/messages/' . $locale . '.mo', 'r' ) )
				if( !$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/' . substr( $locale, 0, 2 ) . '/tags/' . $GLOBALS['wp_version'] . '/messages/' . $locale . '.mo', 'r' ) )
					if( !$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/' . $locale . '/branches/' . $GLOBALS['wp_version'] . '/messages/' . $locale . '.mo', 'r' ) )
						if( !$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/' . substr( $locale, 0, 2 ) . '/branches/' . $GLOBALS['wp_version'] . '/messages/' . $locale . '.mo', 'r' ) )
							if( !$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/' . $locale . '/branches/' . $GLOBALS['wp_version'] . '/' . $locale . '.mo', 'r' ) )
								if( !$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/' . substr( $locale, 0, 2 ) . '/branches/' . $GLOBALS['wp_version'] . '/' . $locale . '.mo', 'r' ) )
									if( !$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/' . $locale . '/trunk/messages/' . $locale . '.mo', 'r' ) )
										if( !$lcr = @fopen('http://svn.automattic.com/wordpress-i18n/' . substr( $locale, 0, 2 ) . '/trunk/messages/' . $locale . '.mo', 'r' ) ) {
										// couldn't find a .mo file
											if ( filesize ( trailingslashit( WP_LANG_DIR ) . $locale . '.mo.filepart' ) == 0 )
												unlink( trailingslashit( WP_LANG_DIR ) . $locale . '.mo.filepart' );
											continue;
										}
			// found a .mo file, update local .mo
			$ll = fopen( trailingslashit( WP_LANG_DIR ) . $locale . '.mo.filepart', 'w' );
			while( !feof( $lcr ) ) {
			// try to get some more time
				set_time_limit( 30 );
				$lc = fread( $lcr, 8192 );
				fwrite( $ll, $lc );
			}
			fclose( $lcr );
			fclose( $ll );
			// only use completely download .mo files
			rename( trailingslashit( WP_LANG_DIR ) . $locale . '.mo.filepart', trailingslashit( WP_LANG_DIR ) . $locale . '.mo' );
		}
	}
	return true;
}

function bp_translate_update_term_library() {
	global $bp_translate;

	/* No post action? Stop */
	if ( !isset( $_POST['action'] ) )
		return;

	/* We've got action, proceed */
	switch( $_POST['action'] ) {
		case 'editedtag':
		case 'addtag':
		case 'editedcat':
		case 'addcat':
		case 'add-cat':
		case 'add-tag':
		case 'add-link-cat':
			/* Make sure we're posting a term */
			if ( $_POST['bp_translate_term_' . bp_translate_get_default_language()] != '' ) {

				/* Safety dance */
				$default = htmlspecialchars( bp_translate_strip_slashes_if_necessary( $_POST['bp_translate_term_' . bp_translate_get_default_language()] ), ENT_NOQUOTES );

				/* If not array, make it one */
				if ( !is_array( $bp_translate['term_name'][$default] ) )
					$bp_translate['term_name'][$default] = array();

				/* Loop through array and do more dancing */
				foreach ( $bp_translate['enabled_languages'] as $lang ) {
					$_POST['bp_translate_term_' . $lang] = bp_translate_strip_slashes_if_necessary( $_POST['bp_translate_term_' . $lang] );

					if ( $_POST['bp_translate_term_' . $lang] != '')
						$bp_translate['term_name'][$default][$lang] = htmlspecialchars( $_POST['bp_translate_term_' . $lang], ENT_NOQUOTES );
					else
						$bp_translate['term_name'][$default][$lang] = $default;

				}

				/* All's good. Save the option */
				update_option( 'bp_translate_term_name', $bp_translate['term_name'] );

			}

			/* I'm about to */
			break;
	}
}

function bp_translate_date_from_post_for_current_language( $old_date, $format ='', $before = '', $after = '' ) {
	global $post;

	return bp_translate_strftime( bp_translate_convert_date_format( $format ), mysql2date( 'U', $post->post_date ), $old_date, $before, $after );
}

function bp_translate_date_modified_from_post_for_current_language( $old_date, $format ='' ) {
	global $post;

	return bp_translate_strftime( bp_translate_convert_date_format( $format ), mysql2date( 'U', $post->post_modified ), $old_date, $before, $after );
}

function bp_translate_time_from_post_for_current_language( $old_date, $format = '', $gmt = false ) {
	global $post;

	$post_date = $gmt? $post->post_date_gmt : $post->post_date;

	return bp_translate_strftime( bp_translate_convert_time_format( $format ), mysql2date( 'U', $post_date ), $old_date );
}

function bp_translate_time_modified_from_post_for_current_language( $old_date, $format = '', $gmt = false ) {
	global $post;

	$post_date = $gmt? $post->post_modified_gmt : $post->post_modified;

	return bp_translate_strftime( bp_translate_convert_time_format( $format ), mysql2date( 'U', $post_date ), $old_date );
}

function bp_translate_date_from_comment_for_current_language( $old_date, $format ='' ) {
	global $comment;

	return bp_translate_strftime( bp_translate_convert_date_format( $format ), mysql2date( 'U', $comment->comment_date ), $old_date, $before, $after );
}

function bp_translate_time_from_comment_for_current_language( $old_date, $format = '', $gmt = false ) {
	global $comment;

	$comment_date = $gmt? $comment->comment_date_gmt : $comment->comment_date;

	return bp_translate_strftime(bp_translate_convert_time_format( $format ), mysql2date( 'U', $comment_date ), $old_date);
}

/* END DATE TIME FUNCTIONS */
function bp_translate_use_term_lib( $obj ) {
	global $bp_translate;

	if ( is_array( $obj ) ) {
	// handle arrays recursively
		foreach( $obj as $key => $t ) {
			$obj[$key] = bp_translate_use_term_lib( $obj[$key] );
		}
		return $obj;
	}
	if( is_object( $obj ) ) {
	// object conversion
		if ( isset( $bp_translate['term_name'][$obj->name][bp_translate_get_language()] ) ) {
			$obj->name = $bp_translate['term_name'][$obj->name][bp_translate_get_language()];
		}
//	} else {
		// string conversion - unpretty workaround for missing filter :(
//		preg_match_all( "#<a [^>]+>([^<]+)</a>#i", $obj, $matches );
//		if ( is_array( $matches ) && sizeof( $matches[0] ) > 0 ) {
//			$search = array();
//			$replace = array();
//			foreach( $matches[1] as $match ) {
//				if ( isset( $bp_translate['term_name'][$match][bp_translate_get_language()] ) ) {
//					$search[] = '>' . $match . '<';
//					$replace[] = '>' . $bp_translate['term_name'][bp_translate_get_language()] . '<';
//				}
//			}
//			$obj = str_replace( $search, $replace, $obj );
//		} elseif ( isset( $bp_translate['term_name'][$obj][bp_translate_get_language()] ) ) {
//			$obj = $bp_translate['term_name'][$obj][bp_translate_get_language()];
//		}
	} elseif ( isset( $bp_translate['term_name'][$obj][bp_translate_get_language()] ) ) {
		$obj = $bp_translate['term_name'][$obj][bp_translate_get_language()];
	}
	return $obj;
}

function bp_translate_convert_blog_info_url( $url, $what ) {
	if ( $what == 'stylesheet_url' ) return $url;
	if ( $what == 'template_url' ) return $url;
	if ( $what == 'template_directory' ) return $url;
	if ( $what == 'stylesheet_directory' ) return $url;
	return bp_translate_convert_url( $url );
}

// splits text with language tags into array
function bp_translate_split( $text, $quicktags = true ) {
	global $bp_translate;

	//init vars
	$split_regex = "#(<!--[^-]*-->|\[:[a-z]{2}\])#ism";
	$current_language = "";
	$result = array();

	foreach( $bp_translate['enabled_languages'] as $language ) {
		$result[$language] = "";
	}

	// split text at all xml comments
	$blocks = preg_split( $split_regex, $text, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE );
	foreach ( $blocks as $block ) {
	# detect language tags
		if ( preg_match( "#^<!--:([a-z]{2})-->$#ism", $block, $matches ) ) {
			if ( bp_translate_is_enabled( $matches[1] ) ) {
				$current_language = $matches[1];
			} else {
				$current_language = "invalid";
			}
			continue;
		// detect quicktags
		} elseif ( $quicktags && preg_match( "#^\[:([a-z]{2})\]$#ism", $block, $matches ) ) {
			if ( bp_translate_is_enabled( $matches[1] ) ) {
				$current_language = $matches[1];
			} else {
				$current_language = "invalid";
			}
			continue;
		// detect ending tags
		} elseif ( preg_match( "#^<!--:-->$#ism", $block, $matches ) ) {
			$current_language = "";
			continue;
		// detect defective more tag
		} elseif ( preg_match( "#^<!--more-->$#ism", $block, $matches ) ) {
			foreach ( $bp_translate['enabled_languages'] as $language ) {
				$result[$language] .= $block;
			}
			continue;
		}
		// correctly categorize text block
		if ( $current_language == "" ) {
		// general block, add to all languages
			foreach ( $bp_translate['enabled_languages'] as $language ) {
				$result[$language] .= $block;
			}
		} elseif ( $current_language != "invalid" ) {
		// specific block, only add to active language
			$result[$current_language] .= $block;
		}
	}
	foreach ( $result as $lang => $lang_content ) {
		$result[$lang] = preg_replace( "#(<!--more-->|<!--nextpage-->)+$#ism", "", $lang_content );
	}
	return $result;
}

function bp_translate_join( $texts ) {
	global $bp_translate;

	if( !is_array( $texts ) )
		$texts = bp_translate_split( $texts, false );

	$split_regex = "#<!--more-->#ism";
	$max = 0;
	$text = "";

	foreach ( $bp_translate['enabled_languages'] as $language ) {
		$texts[$language] = preg_split( $split_regex, $texts[$language] );
		if ( sizeof( $texts[$language] ) > $max )
			$max = sizeof( $texts[$language] );
	}
	for( $i = 0; $i < $max; $i++ ) {
		if ( $i >= 1 ) {
			$text .= '<!--more-->';
		}
		foreach( $bp_translate['enabled_languages'] as $language ) {
			if ( isset( $texts[$language][$i]) && $texts[$language][$i] !== '' ) {
				$text .= '<!--:' . $language . '-->' . $texts[$language][$i] . '<!--:-->';
			}
		}
	}
	return $text;
}

function bp_translate_disable_language( $lang ) {
	global $bp_translate;

	if ( bp_translate_is_enabled( $lang ) ) {
		$new_enabled = array();
		for ( $i = 0; $i < sizeof( $bp_translate['enabled_languages'] ); $i++ ) {
			if ( $bp_translate['enabled_languages'][$i] != $lang ) {
				$new_enabled[] = $bp_translate['enabled_languages'][$i];
			}
		}
		$bp_translate['enabled_languages'] = $new_enabled;
		return true;
	}
	return false;
}

function bp_translate_enable_language( $lang ) {
	global $bp_translate;

	if ( bp_translate_is_enabled( $lang ) || !isset( $bp_translate['language_name'][$lang] ) ) {
		return false;
	}

	$bp_translate['enabled_languages'][] = $lang;

	// force update of .mo files
	if ( $bp_translate['auto_update_mo'] )
		bp_translate_update_gettext_databases( true, $lang );

	return true;
}

function bp_translate_use( $lang, $text, $show_available = false ) {
	global $bp_translate;

	// return full string if language is not enabled
	if ( !bp_translate_is_enabled( $lang ) )
		return $text;

	if ( is_array( $text ) ) {
	// handle arrays recursively
		foreach( $text as $key => $t ) {
			$text[$key] = bp_translate_use( $lang, $text[$key], $show_available );
		}
		return $text;
	}

	if ( is_object( $text ) ) {
		if ( get_class( $text ) == '__PHP_Incomplete_Class' ) {
			foreach( get_object_vars( $text ) as $key => $t ) {
				$text->$key = bp_translate_use( $lang, $text->$key, $show_available );
			}
			return $text;
		}
	}

	// prevent filtering weird data types and save some resources
	if ( !is_string( $text ) || $text == '' ) {
		return $text;
	}

	// get content
	$content = bp_translate_split( $text );

	// find available languages
	$available_languages = array();

	foreach ( $content as $language => $lang_text ) {
		$lang_text = trim( $lang_text );

		if ( !empty( $lang_text ) )
			$available_languages[] = $language;
	}

	// if no languages available show full text
	if ( sizeof( $available_languages ) == 0 )
		return $text;

	// if content is available show the content in the requested language
	if ( !empty( $content[$lang] ) )
		return $content[$lang];

	// content not available in requested language. what now?
	if ( !$show_available ) {
	// check if content is available in default language, if not return first language found. (prevent empty result)
		$default_language = bp_translate_get_default_language();

		if ( $lang != $default_language )
			return "(" . $bp_translate['language_name'][$default_language] . ") " . bp_translate_use( $default_language, $text, $show_available );

		foreach ( $content as $language => $lang_text ) {
			$lang_text = trim( $lang_text );
			if ( !empty($lang_text) ) {
				return "(" . $bp_translate['language_name'][$language] . ") " . $lang_text;
			}
		}
	}

	// display selection for available languages
	$available_languages = array_unique( $available_languages );

	$language_list = "";

	if ( preg_match( '/%LANG:([^:]*):([^%]*)%/', $bp_translate['not_available'][$lang], $match ) ) {

		$normal_seperator = $match[1];
		$end_seperator = $match[2];

		// build available languages string backward
		$i = 0;
		foreach ( $available_languages as $language ) {
			if ( $i == 1 )
				$language_list = $end_seperator . $language_list;
			if ( $i > 1 )
				$language_list = $normal_seperator . $language_list;

			//$language_list = "<a href=\"" . bp_translate_convert_url( '', $language ) . "\">" . $bp_translate['language_name'][$language] . "</a>" . $language_list;
			$language_list = $bp_translate['language_name'][$language] . $language_list;
			$i++;
		}
	}

	return "<div id='body-message'><p>" . preg_replace( '/%LANG:([^:]*):([^%]*)%/', $language_list, $bp_translate['not_available'][$lang]) . "</p></div>";
}

/* Grab all blogs once an hour and update the list if need be */
/* TODO: Better way to do this */
function bp_translate_get_all_blogs() {
	global $wpdb;

	$blogs = $wpdb->get_results( $wpdb->prepare("SELECT blog_id, domain, path FROM $wpdb->blogs WHERE site_id = %d AND spam = '0' AND deleted = '0' ORDER BY registered DESC", $wpdb->siteid), ARRAY_A );

	foreach ( (array) $blogs as $details ) {
		$blog_list[ $details['blog_id'] ] = $details;
		$blog_list[ $details['blog_id'] ]['postcount'] = $wpdb->get_var( "SELECT COUNT(ID) FROM " . $wpdb->base_prefix . $details['blog_id'] . "_posts WHERE post_status='publish' AND post_type='post'" );
	}
	unset( $blogs );

	$blogs = $blog_list;
	update_site_option( 'bp_translate_blog_list', $blogs );

	if ( false == is_array( $blogs ) )
		return array();

	return array_slice( $blogs, 0, count( $blogs ) );
}
add_action( 'wpmu_new_blog', 'bp_translate_get_all_blogs' );
add_action( 'delete_blog', 'bp_translate_get_all_blogs' );

function bp_translate_insert_drop_down_element( $language, $url, $id ) {
	global $bp_translate;

	$html ="
        var sb = document.getElementById('bp_translate_select_".$id."');
        var o = document.createElement('option');
        var l = document.createTextNode('".$bp_translate['language_name'][$language]."');
        ";

	if( bp_translate_get_language() == $language )
		$html .= "o.selected = 'selected';";

	$html .= "
        o.value = '" . addslashes( htmlspecialchars_decode( $url, ENT_NOQUOTES ) ) . "';
        o.appendChild(l);
        sb.appendChild(o);
        ";

	return $html;
}

function bp_translate_is_enabled($lang) {
	global $bp_translate;

	return in_array( $lang, $bp_translate['enabled_languages'] );
}

function bp_translate_starts_with( $s, $n ) {
	if ( strlen( $n ) > strlen( $s ) )
		return false;

	if ( $n == substr( $s, 0, strlen( $n ) ) )
		return true;

	return false;
}

function bp_translate_get_available_languages( $text ) {
	global $bp_translate;

	$result = array();
	$content = bp_translate_split( $text );

	foreach ( $content as $language => $lang_text ) {
		$lang_text = trim( $lang_text );

		if ( !empty( $lang_text ) )
			$result[] = $language;
	}

	// add default language to keep default URL
	if ( sizeof( $result ) == 0 )
		$result[] = bp_translate_get_language();

	return $result;
}

function bp_translate_convert_date_format_to_strftime_format( $format ) {
	$mappings = array(
		'd' => '%d',
		'D' => '%a',
		'j' => '%E',
		'l' => '%A',
		'N' => '%u',
		'S' => '%q',
		'w' => '%f',
		'z' => '%F',
		'W' => '%V',
		'F' => '%B',
		'm' => '%m',
		'M' => '%b',
		'n' => '%i',
		't' => '%J',
		'L' => '%k',
		'o' => '%G',
		'Y' => '%Y',
		'y' => '%y',
		'a' => '%P',
		'A' => '%p',
		'B' => '%K',
		'g' => '%l',
		'G' => '%L',
		'h' => '%I',
		'H' => '%H',
		'i' => '%M',
		's' => '%S',
		'u' => '%N',
		'e' => '%Q',
		'I' => '%o',
		'O' => '%O',
		'P' => '%s',
		'T' => '%v',
		'Z' => '%1',
		'c' => '%2',
		'r' => '%3',
		'U' => '%4'
	);

	$date_parameters = array();
	$strftime_parameters = array();
	$date_parameters[] = '#%#';
	$strftime_parameters[] = '%%';

	foreach( $mappings as $df => $sf ) {
		$date_parameters[] = '#(([^%\\\\])' . $df . '|^' . $df . ')#';
		$strftime_parameters[] = '${2}' . $sf;
	}

	// convert everything
	$format = preg_replace( $date_parameters, $strftime_parameters, $format );

	// remove single backslashes from dates
	$format = preg_replace( '#\\\\([^\\\\]{1})#', '${1}', $format );

	// remove double backslashes from dates
	$format = preg_replace( '#\\\\\\\\#', '\\\\', $format );

	return $format;
}

function bp_translate_convert_format( $format, $default_format ) {
	global $bp_translate;

	// check for multilang formats
	$format = bp_translate_use_current_language_if_not_found_use_default_language( $format );
	$default_format = bp_translate_use_current_language_if_not_found_use_default_language( $default_format );

	switch( $bp_translate['use_strftime'] ) {
		case BP_TRANSLATE_DATE:
			if( $format=='' )
				$format = $default_format;

			return bp_translate_convert_date_format_to_strftime_format( $format );
		case BP_TRANSLATE_DATE_OVERRIDE:
			return bp_translate_convert_date_format_to_strftime_format( $default_format );

		case BP_TRANSLATE_STRFTIME:
			return $format;

		case BP_TRANSLATE_STRFTIME_OVERRIDE:
			return $default_format;
	}
}

function bp_translate_convert_date_format( $format ) {
	global $bp_translate;

	$user_language = bp_translate_get_language();

	if ( isset( $bp_translate['date_format'][$user_language] ) )
		$default_format = $bp_translate['date_format'][$user_language];
	elseif ( isset( $bp_translate['date_format'][$user_language] ) )
		$default_format = $bp_translate['date_format'][$user_language];
	else
		$default_format = '';

	return bp_translate_convert_format( $format, $default_format );
}

function bp_translate_convert_time_format( $format ) {
	global $bp_translate;

	$user_language = bp_translate_get_language();
	if ( isset( $bp_translate['time_format'][$user_language] ) )
		$default_format = $bp_translate['time_format'][$user_language];
	elseif ( isset( $bp_translate['time_format'][$user_language] ) )
		$default_format = $bp_translate['time_format'][$user_language];
	else
		$default_format = '';

	return bp_translate_convert_format( $format, $default_format );
}

function bp_translate_format_comment_date_time( $format ) {
	global $comment;

	return bp_translate_strftime( bp_translate_convert_format( $format, $format ), mysql2date( 'U', $comment->comment_date ), '', $before, $after );
}

function bp_translate_format_post_date_time( $format ) {
	global $post;

	return bp_translate_strftime( bp_translate_convert_format( $format, $format ), mysql2date( 'U', $post->post_date ), '', $before, $after );
}

function bp_translate_format_post_modified_date_time($format) {
	global $post;

	return bp_translate_strftime( bp_translate_convert_format( $format, $format ), mysql2date( 'U', $post->post_modified ), '', $before, $after );
}

function bp_translate_get_sorted_languages( $reverse = false ) {
	global $bp_translate;

	$languages = $bp_translate['enabled_languages'];
	ksort( $languages );

	// fix broken order
	$clean_languages = array();
	foreach( $languages as $lang ) {
		$clean_languages[] = $lang;
	}
	if ( $reverse ) krsort( $clean_languages );

	return $clean_languages;
}

function bp_translate_slug_filter( $post_name ) {
	if ( empty( $post_name ) || ( $post_name == '' ) ) {
		if ( isset( $_POST['post_title'] ) ) {
			if ( function_exists( 'bp_translate_use_current_language_if_not_found_use_default_language' ) )
				$post_name = bp_translate_use_current_language_if_not_found_use_default_language( $_POST['post_title'] );
			else
				$post_name = $_POST['post_title'];
		} else {
			$post_name = '';
		}
	}
	return $post_name;
}

function bp_translate_locale_for_current_language($locale) {
	global $bp_translate;

	// try to figure out the correct locale
	$user_language = bp_translate_get_language();
	$bp_translate_locale = array();
	$bp_translate_locale[] = $bp_translate['locale'][$user_language].".utf8";
	$bp_translate_locale[] = $bp_translate['locale'][$user_language]."@euro";
	$bp_translate_locale[] = $bp_translate['locale'][$user_language];
	$bp_translate_locale[] = $bp_translate['windows_locale'][$user_language];
	$bp_translate_locale[] = $user_language;

	// return the correct locale and most importantly set it
	// only set LC_TIME as everyhing else doesn't seem to work with windows
	// setlocale(LC_TIME, $locale);

	return $bp_translate['locale'][$user_language];
}

function bp_translate_option_filter($do='enable') {
	$options = array(
		'option_widget_pages',
		'option_widget_archives',
		'option_widget_meta',
		'option_widget_calendar',
		'option_widget_text',
		'option_widget_categories',
		'option_widget_recent_entries',
		'option_widget_recent_comments',
		'option_widget_rss',
		'option_widget_tag_cloud'
	);

	foreach( $options as $option ) {
		if ( $do != 'disable' )
			add_filter( $option, 'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
		else
			remove_filter( $option, 'bp_translate_use_current_language_if_not_found_use_default_language' );

	}
}

function bp_translate_use_current_language_if_not_found_show_available( $content ) {
	return bp_translate_use( bp_translate_get_language(), $content, true );
}

function bp_translate_use_current_language_if_not_found_use_default_language( $content ) {
	return bp_translate_use( bp_translate_get_language(), $content, false );
}

function bp_translate_use_default_language( $content ) {
	return bp_translate_use( bp_translate_get_default_language(), $content, false );
}

function bp_translate_exclude_untranslated_posts( $where ) {
	global $bp_translate, $wpdb;

	if ( $bp_translate['hide_untranslated'] && !is_singular() )
		$where .= " AND $wpdb->posts.post_content LIKE '%<!--:" . bp_translate_get_language() . "-->%'";

	return $where;
}

function bp_translate_exclude_pages( $pages ) {
	global $wpdb, $bp_translate;
	static $exclude = 0;

	if ( !$bp_translate['hide_untranslated'] )
		return $pages;

	if ( is_array( $exclude ) )
		return array_merge( $exclude, $pages );

	$query = "SELECT id FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish' AND NOT ($wpdb->posts.post_content LIKE '%<!--:" . bp_translate_get_language() . "-->%')" ;
	$hide_pages = $wpdb->get_results( $query );
	$exclude = array();

	foreach ( $hide_pages as $page ) {
		$exclude[] = $page->id;
	}

	return array_merge( $exclude, $pages );
}

function bp_translate_posts_filter( $posts ) {
	if ( is_array( $posts ) ) {
		foreach ( $posts as $post ) {
			$post->post_content = bp_translate_use_current_language_if_not_found_show_available( $post->post_content );
			$post = bp_translate_use_current_language_if_not_found_use_default_language( $post );
		}
	}
	return $posts;
}

function bp_translate_links( $links, $file ) {
//Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;

	if ( !$this_plugin )
		$this_plugin = plugin_basename(dirname(__FILE__) . '/bp_translate.php');

	if ( $file == $this_plugin ) {
		$settings_link = '<a href="options-general.php?page=bp_translate">' . __('Settings', 'bp-translate') . '</a>';
		array_unshift( $links, $settings_link ); // before other links
	}
	return $links;
}

function bp_translate_language_column_header( $columns ) {
	$columns['language'] = __( 'Languages', 'bp-translate' );

	return $columns;
}

function bp_translate_language_column( $column ) {
	global $bp_translate, $post;

	if ( 'languages' != $column )
		return false;
	
	$available_languages		= bp_translate_get_available_languages( $post->post_content );
	$missing_languages			= array_diff( $bp_translate['enabled_languages'], $available_languages );
	$available_languages_name	= array();
	$missing_languages_name		= array();

	foreach ( $available_languages as $language )
		$available_languages_name[] = $bp_translate['language_name'][$language];

	$available_languages_names = join( ', ', $available_languages_name );

	echo apply_filters( 'bp_translate_available_languages_names', $available_languages_names );

	do_action( 'bp_translate_language_column', $available_languages, $missing_languages );
}

function bp_translate_html_decode_use_current_language_if_not_found_use_default_language( $content ) {
// workaround for page listing on admin
	if ( !is_string( $content ) )
		return $content;

	if ( defined( 'WP_ADMIN' ) && preg_match( '#edit\.php(\?.*)?$#', $_SERVER['REQUEST_URI'] ) ) {
		return htmlspecialchars( bp_translate_use_current_language_if_not_found_use_default_language( htmlspecialchars_decode( $content ) ) );
	} else {
		return bp_translate_use_current_language_if_not_found_use_default_language( $content );
	}
}

function bp_translate_version_locale() {
	return 'en_US';
}

function bp_translate_use_raw_title( $title, $raw_title = '' ) {
	if ( $raw_title == '' )
		$raw_title = $title;

	$raw_title = bp_translate_use_default_language( $raw_title );
	$title = strip_tags( $raw_title );

	return $title;
}

function bp_translate_supercache_dir( $uri ) {
	global $bp_translate;

	if ( isset( $bp_translate['url_info']['original_url'] ) ) {
		$uri = $bp_translate['url_info']['original_url'];
	} else {
		$uri = $_SERVER['REQUEST_URI'];
	}

	$uri = preg_replace( '/[ <>\'\"\r\n\t\(\)]/', '', str_replace( '/index.php', '/', str_replace( '..', '', preg_replace("/(\?.*)?$/", '', $uri ) ) ) );
	$uri = str_replace( '\\', '', $uri );
	$uri = strtolower( preg_replace( '/:.*$/', '', $_SERVER["HTTP_HOST"] ) ) . $uri; // To avoid XSS attacs

	return $uri;
}

function bp_translate_format_code_lang( $code = '' ) {
	$code = strtolower( substr( $code, 0, 2 ) );

	$lang_codes = array(
		'aa' => 'Afar',
		'ab' => 'Abkhazian',
		'af' => 'Afrikaans',
		'ak' => 'Akan',
		'sq' => 'Albanian',
		'am' => 'Amharic',
		'ar' => 'Arabic',
		'an' => 'Aragonese',
		'hy' => 'Armenian',
		'as' => 'Assamese',
		'av' => 'Avaric',
		'ae' => 'Avestan',
		'ay' => 'Aymara',
		'az' => 'Azerbaijani',
		'ba' => 'Bashkir',
		'bm' => 'Bambara',
		'eu' => 'Basque',
		'be' => 'Belarusian',
		'bn' => 'Bengali',
		'bh' => 'Bihari',
		'bi' => 'Bislama',
		'bs' => 'Bosnian',
		'br' => 'Breton',
		'bg' => 'Bulgarian',
		'my' => 'Burmese',
		'ca' => 'Catalan; Valencian',
		'ch' => 'Chamorro',
		'ce' => 'Chechen',
		'zh' => 'Chinese',
		'cu' => 'Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic',
		'cv' => 'Chuvash',
		'kw' => 'Cornish',
		'co' => 'Corsican',
		'cr' => 'Cree',
		'cs' => 'Czech',
		'da' => 'Danish',
		'dv' => 'Divehi; Dhivehi; Maldivian',
		'nl' => 'Dutch; Flemish',
		'dz' => 'Dzongkha',
		'en' => 'English',
		'eo' => 'Esperanto',
		'et' => 'Estonian',
		'ee' => 'Ewe',
		'fo' => 'Faroese',
		'fj' => 'Fijian',
		'fi' => 'Finnish',
		'fr' => 'French',
		'fy' => 'Western Frisian',
		'ff' => 'Fulah',
		'ka' => 'Georgian',
		'de' => 'German',
		'gd' => 'Gaelic; Scottish Gaelic',
		'ga' => 'Irish',
		'gl' => 'Galician',
		'gv' => 'Manx',
		'el' => 'Greek, Modern',
		'gn' => 'Guarani',
		'gu' => 'Gujarati',
		'ht' => 'Haitian; Haitian Creole',
		'ha' => 'Hausa',
		'he' => 'Hebrew',
		'hz' => 'Herero',
		'hi' => 'Hindi',
		'ho' => 'Hiri Motu',
		'hu' => 'Hungarian',
		'ig' => 'Igbo',
		'is' => 'Icelandic',
		'io' => 'Ido',
		'ii' => 'Sichuan Yi',
		'iu' => 'Inuktitut',
		'ie' => 'Interlingue',
		'ia' => 'Interlingua (International Auxiliary Language Association)',
		'id' => 'Indonesian',
		'ik' => 'Inupiaq',
		'it' => 'Italian',
		'jv' => 'Javanese',
		'ja' => 'Japanese',
		'kl' => 'Kalaallisut; Greenlandic',
		'kn' => 'Kannada',
		'ks' => 'Kashmiri',
		'kr' => 'Kanuri',
		'kk' => 'Kazakh',
		'km' => 'Central Khmer',
		'ki' => 'Kikuyu; Gikuyu',
		'rw' => 'Kinyarwanda',
		'ky' => 'Kirghiz; Kyrgyz',
		'kv' => 'Komi',
		'kg' => 'Kongo',
		'ko' => 'Korean',
		'kj' => 'Kuanyama; Kwanyama',
		'ku' => 'Kurdish',
		'lo' => 'Lao',
		'la' => 'Latin',
		'lv' => 'Latvian',
		'li' => 'Limburgan; Limburger; Limburgish',
		'ln' => 'Lingala',
		'lt' => 'Lithuanian',
		'lb' => 'Luxembourgish; Letzeburgesch',
		'lu' => 'Luba-Katanga',
		'lg' => 'Ganda',
		'mk' => 'Macedonian',
		'mh' => 'Marshallese',
		'ml' => 'Malayalam',
		'mi' => 'Maori',
		'mr' => 'Marathi',
		'ms' => 'Malay',
		'mg' => 'Malagasy',
		'mt' => 'Maltese',
		'mo' => 'Moldavian',
		'mn' => 'Mongolian',
		'na' => 'Nauru',
		'nv' => 'Navajo; Navaho',
		'nr' => 'Ndebele, South; South Ndebele',
		'nd' => 'Ndebele, North; North Ndebele',
		'ng' => 'Ndonga',
		'ne' => 'Nepali',
		'nn' => 'Norwegian Nynorsk; Nynorsk, Norwegian',
		'nb' => 'Bokmål, Norwegian, Norwegian Bokmål',
		'no' => 'Norwegian',
		'ny' => 'Chichewa; Chewa; Nyanja',
		'oc' => 'Occitan, Provençal',
		'oj' => 'Ojibwa',
		'or' => 'Oriya',
		'om' => 'Oromo',
		'os' => 'Ossetian; Ossetic',
		'pa' => 'Panjabi; Punjabi',
		'fa' => 'Persian',
		'pi' => 'Pali',
		'pl' => 'Polish',
		'pt' => 'Portuguese',
		'ps' => 'Pushto',
		'qu' => 'Quechua',
		'rm' => 'Romansh',
		'ro' => 'Romanian',
		'rn' => 'Rundi',
		'ru' => 'Russian',
		'sg' => 'Sango',
		'sa' => 'Sanskrit',
		'sr' => 'Serbian',
		'hr' => 'Croatian',
		'si' => 'Sinhala; Sinhalese',
		'sk' => 'Slovak',
		'sl' => 'Slovenian',
		'se' => 'Northern Sami',
		'sm' => 'Samoan',
		'sn' => 'Shona',
		'sd' => 'Sindhi',
		'so' => 'Somali',
		'st' => 'Sotho, Southern',
		'es' => 'Spanish; Castilian',
		'sc' => 'Sardinian',
		'ss' => 'Swati',
		'su' => 'Sundanese',
		'sw' => 'Swahili',
		'sv' => 'Swedish',
		'ty' => 'Tahitian',
		'ta' => 'Tamil',
		'tt' => 'Tatar',
		'te' => 'Telugu',
		'tg' => 'Tajik',
		'tl' => 'Tagalog',
		'th' => 'Thai',
		'bo' => 'Tibetan',
		'ti' => 'Tigrinya',
		'to' => 'Tonga (Tonga Islands)',
		'tn' => 'Tswana',
		'ts' => 'Tsonga',
		'tk' => 'Turkmen',
		'tr' => 'Turkish',
		'tw' => 'Twi',
		'ug' => 'Uighur; Uyghur',
		'uk' => 'Ukrainian',
		'ur' => 'Urdu',
		'uz' => 'Uzbek',
		've' => 'Venda',
		'vi' => 'Vietnamese',
		'vo' => 'Volapük',
		'cy' => 'Welsh',
		'wa' => 'Walloon',
		'wo' => 'Wolof',
		'xh' => 'Xhosa',
		'yi' => 'Yiddish',
		'yo' => 'Yoruba',
		'za' => 'Zhuang; Chuang',
		'zu' => 'Zulu'
	);

	$lang_codes = apply_filters( 'lang_codes', $lang_codes, $code );
	return strtr( $code, $lang_codes );
}

function bp_translate_select_dropdown_languages( $lang_files = array(), $current = '' ) {
	$flag = false;
	$output = array();

	foreach ( (array) $lang_files as $val ) {
		$code_lang = basename( $val, '.mo' );

		if ( $code_lang == 'en_US' ) { // American English
			$flag = true;
			$ae = __('American English', 'bp-translate');
			$output[$ae] = '<option value="' . $code_lang . '"' . (( $current == $code_lang ) ? ' selected="selected"' : '') . '> ' . $ae . '</option>';
		} elseif ( $code_lang == 'en_GB' ) { // British English
			$flag = true;
			$be = __('British English', 'bp-translate');
			$output[$be] = '<option value="' . $code_lang . '"' . (( $current == $code_lang ) ? ' selected="selected"' : '') . '> ' . $be . '</option>';
		} else {
			$translated = bp_translate_format_code_lang( $code_lang );
			$output[$translated] =  '<option value="' . $code_lang . '"' . (( $current == $code_lang ) ? ' selected="selected"' : '') . '> ' . $translated . '</option>';
		}
	}

	// WordPress english
	if ( $flag === false )
		$output[] = '<option value=""' . (( empty($current )) ? ' selected="selected"' : '') . '>' . __('English', 'bp-translate') . "</option>";

	// Order by name
	uksort( $output, 'strnatcasecmp' );

	$output = apply_filters( 'bp_translate_select_dropdown_languages', $output, $lang_files, $current );

	echo implode( "\n\t", $output );
}

function bp_translate_get_google_translation( $sString, $bEscapeParams = true ) {
// "escape" sprintf paramerters
	if ( $bEscapeParams ) :
		$sPattern = '/(?:%%|%(?:[0-9]+\$)?[+-]?(?:[ 0]|\'.)?-?[0-9]*(?:\.[0-9]+)?[bcdeufFosxX])/';
		$sEscapeString = '<span class="notranslate">$0</span>';
		$sString = preg_replace( $sPattern, $sEscapeString, $sString );
	endif;

	// Compose data array (English to Dutch)
	$aData = array(
		'v'				=> '1.0',
		'q'				=> $sString,
		'langpair'		=> 'en|nl',
	);

	// Initialize connection
	$rService = curl_init();

	// Connection settings
	curl_setopt( $rService, CURLOPT_URL, 'http://ajax.googleapis.com/ajax/services/language/translate' );
	curl_setopt( $rService, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $rService, CURLOPT_POSTFIELDS, $aData );

	// Execute request
	$sResponse = curl_exec( $rService );

	// Close connection
	curl_close( $rService );

	// Extract text from JSON response
	$oResponse = json_decode( $sResponse );
	if (isset( $oResponse->responseData->translatedText ) )
		$sTranslation = $oResponse->responseData->translatedText;
	else
	// If some error occured, use the original string
		$sTranslation = $sString;

	// Replace "notranslate" tags
	if ($bEscapeParams) :
		$sEscapePatern = '/<span class="notranslate">([^<]*)<\/span>/';
		$sTranslation = preg_replace($sEscapePatern, '$1', $sTranslation);
	endif;

	// Return result
	return $sTranslation;
}

function bp_translate_esc_html( $text ) {
	return bp_translate_use_default_language( $text );
}

?>