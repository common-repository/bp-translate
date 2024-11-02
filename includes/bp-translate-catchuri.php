<?php

function bp_translate_catchuri() {
	global $bp_translate, $bp_settings_updated;

	// Don't do this if in the dashboard
	if ( defined( 'WP_ADMIN' ) )
		return false;

	// Extract url information
	$bp_translate['url_info'] = bp_translate_extract_url( $_SERVER['REQUEST_URI'], $_SERVER["HTTP_HOST"], isset( $_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '' );

	// Set the locale language to check URL against
	bp_translate_user_locale();

	// Forward to new url if needed (usually on POST submission)
	if ( $bp_translate['url_info']['redirect'] ) {
		$target = bp_translate_convert_url( $bp_translate['url_info']['url'], bp_translate_get_language() );
		bp_core_redirect( $target );
		exit;
	}

	// TODO: look at how browser detection would work best
	// Disabled for now
	/*
	if ( $bp_translate['detect_browser_language'] && $bp_translate['url_info']['redirect'] ) {
		$prefered_languages = array();
		if ( preg_match_all( "#([^;,]+)(;[^,0-9]*([0-9\.]+)[^,]*)?#i", $_SERVER["HTTP_ACCEPT_LANGUAGE"], $matches, PREG_SET_ORDER) ) {
			var_dump($matches);die;
			foreach( $matches as $match ) {
				$prefered_languages[$match[1]] = floatval( $match[3] );
				if ( $match[3] == NULL )
					$prefered_languages[$match[1]] = 1.0;
			}
			arsort( $prefered_languages, SORT_NUMERIC );
			foreach ( $prefered_languages as $language => $priority ) {
				if ( bp_translate_is_enabled( $language ) ) {
					if ( $language == bp_translate_get_language() ) break;
					$target = bp_translate_convert_url( $bp->root_domain, $language );
					wp_redirect( $target );
					exit;
				}
			}
		}
	}
	*/

	// Filter all options for language tags
	if ( !is_admin() ) {
		$alloptions = wp_load_alloptions();
		foreach( $alloptions as $option => $value ) {
			add_filter( 'option_' . $option, 'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
		}
	}

	// remove traces of language changer
	unset( $_GET[ BP_TRANSLATE_USER_QUERY_ARG ] );
	
	$_SERVER['REQUEST_URI'] = $bp_translate['url_info']['url'];
	$_SERVER['HTTP_HOST'] = $bp_translate['url_info']['host'];

	// fix url to prevent xss
	$bp_translate['url_info']['url'] = bp_translate_convert_url( add_query_arg( BP_TRANSLATE_USER_QUERY_ARG, bp_translate_get_default_language(), $bp_translate['url_info']['url'] ) );

}

function bp_translate_load_textdomain() {
	// load plugin translations
	load_plugin_textdomain( 'bp-translate', PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)) . '/languages' );
}

/* Returns original url without language adjustment */
function bp_translate_real_url( $url = '' ) {
	global $bp_translate;

	return $bp_translate['url_info']['original_url'];
}

/* Returns cleaned string and language information */
function bp_translate_extract_url( $url, $host = '', $referer = '' ) {
	global $bp_translate, $bp;

	// Get blog root url info
	$home = bp_translate_parse_url( get_option( 'home' ) );
	$home['path'] = trailingslashit( $home['path'] );

	// Get $referer url info
	$referer = bp_translate_parse_url( $referer );

	// Set result defaults
	$result = array();
	$result['language'] = bp_translate_get_default_language();
	$result['url'] = $url;
	$result['original_url'] = $url;
	$result['host'] = $host;
	$result['redirect'] = false;
	$result['internal_referer'] = false;
	$result['home'] = $home['path'];

	if ( !defined( 'DOING_AJAX' ) ) {
		switch ( $bp_translate['url_mode'] ) {
			case BP_TRANSLATE_URL_PATH:
				// pre url
				if ( $new_url = substr( $result['original_url'], strlen( $home['path'] ) ) ) {
					// might have language information
					if ( preg_match( "#^([a-z]{2})/#i", $new_url, $match ) ) {
						if ( bp_translate_is_enabled( $match[1] ) ) {
							// found language information
							$result['language'] = $match[1];
							$result['url'] = $home['path'] . substr( $new_url, 3 );
						}
					}
				}
				break;
			case BP_TRANSLATE_URL_DOMAIN:
				// pre domain
				if ( $host ) {
					if ( preg_match( "#^([a-z]{2}).#i", $host, $match ) ) {
						if ( bp_translate_is_enabled( $match[1] ) ) {
							// found language information
							$result['language'] = $match[1];
							$result['host'] = substr( $host, 3 );
						}
					}
				}
				break;
		}
	} else {
		$result['language'] = $bp_translate['locale'][substr( $referer['path'], 1, 2 )];
	}

	// Check if referer is internal
	if ( $referer['host'] == $result['host'] && bp_translate_starts_with( $referer['path'], $home['path'] ) )
		$result['internal_referer'] = true;

	/* if admin language change request exists, change it */
	if ( isset( $_GET['lang_admin'] ) && bp_translate_is_enabled( substr( $_GET['lang_admin'], 0, 2 ) ) ) {
		/* Changed via URL */
		$result['language'] = $_GET['lang_admin'];

		if ( is_admin() )
			$result['redirect'] = true;

		$result['url'] = preg_replace( "#(&|\?)lang_admin=" . $_GET['lang_admin'] . "&?#i", "$1", $result['url'] );
		$result['url'] = preg_replace( "#[\?\&]+$#i", "", $result['url'] );
		bp_translate_set_user_locale( array( 'key' => 'WPLANG_ADMIN', 'arg' => 'lang_admin' ));
	} elseif ( isset( $_POST['WPLANG_ADMIN'] ) && bp_translate_is_enabled( substr( $_POST['WPLANG_ADMIN'], 0, 2 ) ) ) {
		$result['language'] = $_POST['WPLANG_ADMIN'];

		if ( is_admin() )
			$result['redirect'] = true;

		bp_translate_set_user_locale( array( 'key' => 'WPLANG_ADMIN' ) );
	}

	/* if language change request exists, change it */
	if ( isset( $_GET[ BP_TRANSLATE_USER_QUERY_ARG ] ) && bp_translate_is_enabled( substr( $_GET[ BP_TRANSLATE_USER_QUERY_ARG ], 0, 2 ) ) ) {
		/* Changed via URL */
		$result['language'] = $_GET[ BP_TRANSLATE_USER_QUERY_ARG ];
		$result['redirect'] = true;
		$result['url'] = preg_replace( "#(&|\?)" . BP_TRANSLATE_USER_QUERY_ARG . "=" . $_GET[ BP_TRANSLATE_USER_QUERY_ARG ] . "&?#i", "$1", $result['url'] );
		$result['url'] = preg_replace( "#[\?\&]+$#i", "", $result['url'] );
		bp_translate_set_user_locale();
	} elseif ( isset( $_POST['WPLANG'] ) && bp_translate_is_enabled( substr( $_POST['WPLANG'], 0, 2 ) ) ) {
		/* Changed via post form */
		$result['language'] = $_POST['WPLANG'];
		$result['redirect'] = true;
		bp_translate_set_user_locale();
	} elseif ( $home['host'] == $result['host'] && $home['path'] == $result['url'] ) {
		if ( empty( $referer['host'] ) ) {
			//$result['redirect'] = true;
		} else {
			// check if activating language detection is possible
			if ( preg_match( "#^([a-z]{2}).#i", $referer['host'], $match ) ) {
				if ( bp_translate_is_enabled( $match[1] ) ) {
					// found language information
					$referer['host'] = substr($referer['host'], 3);
				}
			}
			if( !$result['internal_referer'] ) {
				// user coming from external link
				//$result['redirect'] = true;
			}
		}
	}

	return $result;
}

function bp_translate_parse_url( $url ) {
	$r  = "(?:([a-z0-9+-._]+)://)?";
	$r .= "(?:";
	$r .=   "(?:((?:[a-z0-9-._~!$&'()*+,;=:]|%[0-9a-f]{2})*)@)?";
	$r .=   "(?:\[((?:[a-z0-9:])*)\])?";
	$r .=   "((?:[a-z0-9-._~!$&'()*+,;=]|%[0-9a-f]{2})*)";
	$r .=   "(?::(\d*))?";
	$r .=   "(/(?:[a-z0-9-._~!$&'()*+,;=:@/]|%[0-9a-f]{2})*)?";
	$r .=   "|";
	$r .=   "(/?";
	$r .=     "(?:[a-z0-9-._~!$&'()*+,;=:@]|%[0-9a-f]{2})+";
	$r .=     "(?:[a-z0-9-._~!$&'()*+,;=:@\/]|%[0-9a-f]{2})*";
	$r .=    ")?";
	$r .= ")";
	$r .= "(?:\?((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9a-f]{2})*))?";
	$r .= "(?:#((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9a-f]{2})*))?";

	preg_match( "`$r`i", $url, $match );

	$parts = array(
		"scheme"=>'',
		"userinfo"=>'',
		"authority"=>'',
		"host"=> '',
		"port"=>'',
		"path"=>'',
		"query"=>'',
		"fragment"=>''
	);

	switch ( count( (array)$match ) ) {
		case 10: $parts['fragment'] = $match[9];
		case 9: $parts['query'] = $match[8];
		case 8: $parts['path'] =  $match[7];
		case 7: $parts['path'] =  $match[6] . $parts['path'];
		case 6: $parts['port'] =  $match[5];
		case 5: $parts['host'] =  $match[3]?"[".$match[3]."]":$match[4];
		case 4: $parts['userinfo'] =  $match[2];
		case 3: $parts['scheme'] =  $match[1];
	}
	$parts['authority'] = ( $parts['userinfo']?$parts['userinfo']."@":"").
							$parts['host'].
							( $parts['port']?":".$parts['port']:"" );
	return $parts;
}

function bp_translate_fix_slashes( $to_strip ) {
	return rtrim( $to_strip, "/" );
}

function bp_translate_strip_slashes_if_necessary( $str ) {

	if ( 1 == get_magic_quotes_gpc() )
		$str = stripslashes( $str );

	return $str;
}

/* Main function responsible for converting URLs */
function bp_translate_convert_url( $url = '', $lang = '', $forceadmin = false ) {
	global $bp_translate, $bp;

	if ( $url == '' )
		$url = clean_url( $bp_translate['url_info']['url'] );

	// Check for invalid language
	if ( $lang == '' )
		$lang = bp_translate_get_language();

	$default_language = bp_translate_get_default_language();

	if ( defined( 'WP_ADMIN' ) && !$forceadmin )
		return $url;

	if ( !bp_translate_is_enabled( $lang ) )
		return false;

	// Ampersand workaround
	$url = str_replace( '&amp;', '&', $url );
	$url = str_replace( '&#038;', '&', $url );

	// Parse URL and try to convert it if it matches an internal link
	$urlinfo = bp_translate_parse_url( $url );
	$urlinfo['path'] = str_replace( '//', '/', $urlinfo['path'] );
	$urlinfo['path'] = ltrim( $urlinfo['path'], '/' );
	
	$cur_url = $urlinfo['scheme'] . "://" . $urlinfo['host'];

	if ( $cur_url != "://" ) {
		foreach ( $bp_translate['mu_blog_list'] as $blog ) {
			$cur_blog = get_blogaddress_by_domain( $blog['domain'], $blog['path'] );
			if ( rtrim( $cur_blog, "/" ) == rtrim( $cur_url, "/" ) ) {
				$user_blog = $blog['blog_id'];
				break;
			}
		}
	}

	if ( isset( $user_blog ) && !empty( $user_blog ) )
		$home = rtrim( get_blog_option( $user_blog, 'home' ), "/" );
	else
		$home = rtrim( get_option( 'home' ), "/" );

	if ( $urlinfo['host'] != '' ) {
		// check for already existing pre-domain language information
		if ( $bp_translate['url_mode'] == BP_TRANSLATE_URL_DOMAIN && preg_match( "#^([a-z]{2}).#i", $urlinfo['host'], $match ) ) {
			if ( bp_translate_is_enabled( $match[1] ) ) {
				// found language information, remove it
				$url = preg_replace( "/" . $match[1] . "\./i", "", $url, 1);
				// reparse url
				$urlinfo = bp_translate_parse_url( $url );
			}
		}

		if ( substr( $url, 0, strlen( $home ) ) != $home ) {
			return $url;
		}
		// strip home path
		$url = substr( $url, strlen( $home ) );
	} else {
		// relative url, strip home path
		$homeinfo = bp_translate_parse_url( $home );
		if ( $homeinfo['path'] == substr( $url, 0, strlen( $homeinfo['path'] ) ) ) {
			$url = substr( $url, strlen( $homeinfo['path'] ) );
		}
	}

	// check for query language information and remove if found
	if ( preg_match( "#(&|\?)" . BP_TRANSLATE_USER_QUERY_ARG . "=([^&\#]+)#i", $url, $match ) && bp_translate_is_enabled( $match[2] ) ) {
		$url = preg_replace( "#(&|\?)" . BP_TRANSLATE_USER_QUERY_ARG . "=" . $match[2] . "&?#i", "$1", $url );
	}

	// remove any slashes out front
	$url = ltrim( $url, "/" );
	
	// remove any double slashes
	$url = str_replace( '//', '/', $url );

	// remove any useless trailing characters
	$url = rtrim( $url, "?&" );

	// reparse url without home path
	$urlinfo = bp_translate_parse_url( $url );
	
	// check if its a link to an ignored file type
	$ignore_file_types = preg_split( '/\s*,\s*/', strtolower( $bp_translate['ignore_file_types'] ) );
	$urlinfo['path'] = str_replace( '//', '/', $urlinfo['path'] );
	$urlinfo['path'] = ltrim( $urlinfo['path'], '/' );

	if ( isset( $pathinfo['extension'] ) && in_array( strtolower( $pathinfo['extension'] ), $ignore_file_types ) ) {
		return $home . "/" . $url;
	}

	switch( $bp_translate['url_mode'] ) {
		/* TODO: pre url */
		case BP_TRANSLATE_URL_PATH:
			/* might already have language information */
			if ( preg_match( "#^([a-z]{2})/#i", $url, $match ) ) {
				if ( bp_translate_is_enabled( $match[1] ) ) {
					/* found language information, remove it */
					$url = substr( $url, 3 );
				}
			}

			if ( $lang != $default_language )
				$url = $lang . "/" . $url;

			break;

		/* pre domain */
		case BP_TRANSLATE_URL_DOMAIN:
			if ( $lang != $default_language )
				$home = preg_replace( "#//#", "//" . $lang . ".", $home, 1 );

			break;

		/* query */
		default:
			if ( $lang != $default_language ) {
				if ( strpos( $url, '?' ) === false )
					$url .= '?';
				else
					$url .= '&';

				$url .= BP_TRANSLATE_QUERY_ARG . "=" . $lang;
			}
	}

	if ( !$bp_translate['url_info']['internal_referer'] && $urlinfo['path'] == '' && $lang == $default_language && bp_translate_get_language() != $default_language ) {
		/* Unpretty URLs */
		$url = preg_replace( "#(&|\?)" . BP_TRANSLATE_QUERY_ARG . "=" . $match[2] . "&?#i", "$1", $url );
		if ( strpos( $url, '?' ) === false ) {
			$url .= '?';
		} else {
			$url .= '&';
		}
		$url .= BP_TRANSLATE_QUERY_ARG . "=" . $lang;
	}

	// &amp; workaround
	$complete = str_replace( '&', '&amp;', $home . "/" . $url );

	return $complete;
}

?>