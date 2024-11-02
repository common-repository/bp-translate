<?php

function bp_translate_better_language_attributes( $doctype = 'html' ) {

	$attributes = array();
	$output = '';

	if ( $dir = get_bloginfo( 'text_direction' ) )
		$attributes[] = "dir=\"$dir\"";

	if ( $lang = get_bloginfo('language') ) {
		if ( get_option('html_type') == 'text/html' || $doctype == 'html' )
			$attributes[] = "lang=\"" . substr( $lang, 0, 2 ) . "\"";

		if ( get_option('html_type') != 'text/html' || $doctype == 'xhtml' )
			$attributes[] = "xml:lang=\"" . substr( $lang, 0, 2 ) . "\"";
	}

	$output = implode( ' ', $attributes );
	$output = apply_filters( 'language_attributes', $output );

	echo $output;
}

function bp_translate_strftime( $format, $date, $default = '', $before = '', $after = '' ) {

	// don't do anything if format is not given
	if( $format == '' )
		return $default;

	// add date suffix ability (%q) to strftime
	$day = intval( ltrim( strftime( "%d", $date ), '0' ) );
	$search = array();
	$replace = array();

	// date S
	$search[] = '/(([^%])%q|^%q)/';
	if ( $day == 1 || $day == 21 || $day == 31 )
		$replace[] = '$2st';
	elseif ( $day == 2 || $day == 22 )
		$replace[] = '$2nd';
	elseif ( $day == 3 || $day == 23 )
		$replace[] = '$2rd';
	else
		$replace[] = '$2th';

	$search[] = '/(([^%])%E|^%E)/'; $replace[] = '${2}' . $day; // date j
	$search[] = '/(([^%])%f|^%f)/'; $replace[] = '${2}' . date( 'w', $date ); // date w
	$search[] = '/(([^%])%F|^%F)/'; $replace[] = '${2}' . date( 'z', $date ); // date z
	$search[] = '/(([^%])%i|^%i)/'; $replace[] = '${2}' . date( 'i', $date ); // date i
	$search[] = '/(([^%])%J|^%J)/'; $replace[] = '${2}' . date( 't', $date ); // date t
	$search[] = '/(([^%])%k|^%k)/'; $replace[] = '${2}' . date( 'L', $date ); // date L
	$search[] = '/(([^%])%K|^%K)/'; $replace[] = '${2}' . date( 'B', $date ); // date B
	$search[] = '/(([^%])%l|^%l)/'; $replace[] = '${2}' . date( 'g', $date ); // date g
	$search[] = '/(([^%])%L|^%L)/'; $replace[] = '${2}' . date( 'G', $date ); // date G
	$search[] = '/(([^%])%N|^%N)/'; $replace[] = '${2}' . date( 'u', $date ); // date u
	$search[] = '/(([^%])%Q|^%Q)/'; $replace[] = '${2}' . date( 'e', $date ); // date e
	$search[] = '/(([^%])%o|^%o)/'; $replace[] = '${2}' . date( 'I', $date ); // date I
	$search[] = '/(([^%])%O|^%O)/'; $replace[] = '${2}' . date( 'O', $date ); // date O
	$search[] = '/(([^%])%s|^%s)/'; $replace[] = '${2}' . date( 'P', $date ); // date P
	$search[] = '/(([^%])%v|^%v)/'; $replace[] = '${2}' . date( 'T', $date ); // date T
	$search[] = '/(([^%])%1|^%1)/'; $replace[] = '${2}' . date( 'Z', $date ); // date Z
	$search[] = '/(([^%])%2|^%2)/'; $replace[] = '${2}' . date( 'c', $date ); // date c
	$search[] = '/(([^%])%3|^%3)/'; $replace[] = '${2}' . date( 'r', $date ); // date r
	$search[] = '/(([^%])%4|^%4)/'; $replace[] = '${2}' . $date; // date U

	$format = preg_replace( $search, $replace, $format );

	return $before . strftime( $format, $date) . $after;
}

/* Language select code for non-widget users */
function bp_translate_generate_language_select_code( $style = '', $id = 'bp_translate_language_chooser' ) {
	global $bp_translate;

	if ( is_bool( $style ) && $style )
		$style = 'image';

	switch ( $style ) {
		case 'image':
		case 'text':
		case 'dropdown':
			echo '<ul class="bp_translate_language_chooser" id="' . $id . '">';
			echo '<li class="active"><a href="' . bp_translate_convert_url( '', $bp_translate['language'] ) . '"';
			echo ' hreflang="' . $bp_translate['language'] . '"';
			if ( $style == 'image' )
				echo ' class="bp_translate_flag bp_translate_flag_' . $bp_translate['language'] . '"><span';

			if ( $style == 'image' )
				echo ' style="display:none"';

			echo '>' . $bp_translate['language_name'][$bp_translate['language']] . '</span></a></li>';

			foreach ( bp_translate_get_sorted_languages() as $language ) {
				if ( $language != bp_translate_get_language() ) {
					echo '<li><a href="' . bp_translate_convert_url( '', $language ) . '"';
					echo ' hreflang="' . $language . '"';

					if ( $style == 'image' )
						echo ' class="bp_translate_flag bp_translate_flag_' . $language . '"><span';

					if ( $style == 'image' )
						echo ' style="display:none"';

					echo '>' . $bp_translate['language_name'][$language] . '</span></a></li>';
				}
			}
			echo "</ul><div class=\"bp_translate_widget_end\"></div>";
			if ( $style == 'dropdown' ) {
				echo "<script type=\"text/javascript\">\n// <![CDATA[\r\n";
				echo "var lc = document.getElementById('" . $id . "');\n";
				echo "var s = document.createElement('select');\n";
				echo "s.id = 'bp_translate_select_" . $id . "';\n";
				echo "lc.parentNode.insertBefore(s,lc);";

				// create dropdown fields for each language
				foreach ( bp_translate_get_sorted_languages() as $language ) {
					$link = add_query_arg( BP_TRANSLATE_USER_QUERY_ARG, $bp_translate['locale'][$language], $cur_url );
					echo bp_translate_insert_drop_down_element( $language, $link, $id );
				}

				// hide html language chooser text
				echo "s.onchange = function() { document.location.href = this.value;}\n";
				echo "lc.style.display='none';\n";
				echo "// ]]>\n</script>\n";
			}
			break;
		case 'both':
			echo '<ul class="bp_translate_language_chooser" id="' . $id . '">';
			foreach ( bp_translate_get_sorted_languages() as $language ) {
				echo '<li';

				if ( $language == bp_translate_get_language() )
					echo ' class="active"';

				echo '><a href="' . bp_translate_convert_url( '', $language ) . '"';
				echo ' class="bp_translate_flag_' . $language . ' bp_translate_flag_and_text"';
				echo '><span>' . $bp_translate['language_name'][$language] . '</span></a></li>';
			}
			echo "</ul><div class=\"bp_translate_widget_end\"></div>";
			break;
		case 'links' :
			foreach ( bp_translate_get_sorted_languages() as $language ) {
?>
<?php
					$link = add_query_arg( BP_TRANSLATE_USER_QUERY_ARG, $bp_translate['locale'][$language], $cur_url );
?>
			<a href="<?php echo $link; ?>" class="">
				<span><?php echo $bp_translate['language_name'][$language]; ?></span>
			</a>
<?php
			}
			break;

		default:
?>
			<a class="button orange" href="<?php echo bp_core_get_root_domain(); ?>"><?php echo $bp_translate['language_name'][bp_translate_get_language()] ?> &darr;</a>
			<div class="sub">
				<ul class="bp_translate_language_chooser" id="<?php echo $id; ?>">
<?php
			$cur_url = bp_translate_convert_url( '', $language );
			foreach ( bp_translate_get_sorted_languages() as $language ) {
				if ( $language != bp_translate_get_language() ) {
?>
					<li>
<?php
					$link = add_query_arg( BP_TRANSLATE_USER_QUERY_ARG, $bp_translate['locale'][$language], $cur_url );
?>
						<a href="<?php echo $link ?>" class="bp_translate_flag_<?php echo $bp_translate['locale'][$language] ?> bp_translate_flag_and_text">
							<span><?php echo $bp_translate['language_name'][$language] ?></span>
						</a>
					</li>
<?php
				}
			}
?>
				</ul>
			</div>
<?php
			break;
	}
}

/* Returns two character default display language - 'en'. */
function bp_translate_get_default_language() {
	global $bp_translate;

	return $bp_translate['default_language'];
}

/* Returns two character user display language - 'en'. */
function bp_translate_get_language() {
	global $bp_translate;

	return substr( isset( $bp_translate['user_language'] ) ? $bp_translate['user_language'] : $bp_translate['default_user_language'], 0, 2 );
}

/*
 * Returns currently selected language - 'en_US'
 * NOTE: This should always be equal to the $locale WordPress global
 */
function bp_translate_get_long_language() {
	global $bp_translate;

	return $bp_translate['locale'][bp_translate_get_language()];
}

/* Echoes name of currently selected language. */
function bp_translate_language_name( $lang = '' ) {
	echo bp_translate_get_language_name( $lang );
}
	/* Returns name of currently selected language. */
	function bp_translate_get_language_name( $lang = '' ) {
		global $bp_translate;

		if ( $lang == '' || !bp_translate_is_enabled( $lang ) )
			$lang = bp_translate_get_language();

		return $bp_translate['language_name'][$lang];
	}

/* Get user language from private message */
function bp_the_thread_message_sender_language() {
	echo bp_get_the_thread_message_sender_language();
}
	function bp_get_the_thread_message_sender_language() {
		global $thread_template, $bp;

		if ( $thread_template->message->sender_id == $bp->loggedin_user->id )
			return false;

		return apply_filters( 'bp_get_the_thread_message_sender_language', BP_TRANSLATE_CLASS_PREFIX . get_usermeta( $thread_template->message->sender_id, 'WPLANG' ) );
	}

/* Get user language for inbox from sender */
function bp_the_inbox_message_sender_language() {
	echo bp_get_the_inbox_message_sender_language();
}
	function bp_get_the_inbox_message_sender_language() {
		global $messages_template, $bp;

		if ( $messages_template->thread->last_sender_id == $bp->loggedin_user->id )
			return false;

		return apply_filters( 'bp_get_the_inbox_message_sender_language', BP_TRANSLATE_CLASS_PREFIX . get_usermeta( $messages_template->thread->last_sender_id, 'WPLANG' ) );
	}

function bp_translate_member_language( $user_id = '' ) {
	echo bp_translate_get_member_language( $user_id );
}
	function bp_translate_get_member_language( $user_id = '' ) {
		global $bp, $bp_translate, $members_template;

		if ( !$user_id && $members_template->current_member != -1 )
			$user_id = $members_template->member->id;

		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		if ( !$user_id )
			$user_id = $bp->loggedin_user->id;

		$user_lang = substr( get_usermeta( $user_id, 'WPLANG' ), 0, 2 );

		return apply_filters( 'bp_translate_get_member_language', $bp_translate['language_name'][$user_lang] ? $bp_translate['language_name'][$user_lang] : $bp_translate['language_name'][$bp_translate['default_language']] );
	}

/* Get user language by id */
function bp_translate_user_language( $user_id = '' ) {
	echo bp_translate_get_user_language( $user_id );
}
	function bp_translate_get_user_language( $user_id = '' ) {
		global $bp, $bp_translate, $members_template;

		if ( !$user_id && $members_template->current_member != -1 )
			$user_id = $members_template->member->id;

		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		if ( !$user_id )
			$user_id = $bp->loggedin_user->id;

		$user_lang = substr( get_usermeta( $user_id, 'WPLANG' ), 0, 2 );

		return apply_filters( 'bp_translate_get_user_language', $bp_translate['language_name'][$user_lang] ? $bp_translate['language_name'][$user_lang] : $bp_translate['language_name'][$bp_translate['default_language']] );
	}

/* Get user language by id */
function bp_translate_css_language( $user_id = '' ) {
	echo bp_translate_get_css_language( $user_id );
}
	function bp_translate_get_css_language( $user_id = '' ) {
		global $bp, $bp_translate, $members_template;

		if ( !$user_id && $members_template->current_member != -1 )
			$user_id = $members_template->member->id;

		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		if ( !$user_id )
			$user_id = $bp->loggedin_user->id;

		$user_lang = get_usermeta( $user_id, 'WPLANG' );

		return apply_filters( 'bp_translate_get_css_language', BP_TRANSLATE_CLASS_PREFIX . $user_lang );
	}

/* Get user language from forum post */
function bp_the_forum_topic_post_user_language() {
	echo bp_get_the_forum_topic_post_user_language();
}
	function bp_get_the_forum_topic_post_user_language() {
		global $topic_template, $bp;

		if ($topic_template->post->poster_id == $bp->loggedin_user->id)
			return false;

		return apply_filters( 'bp_get_the_forum_topic_post_user_language', BP_TRANSLATE_CLASS_PREFIX . get_usermeta( $topic_template->post->poster_id, 'WPLANG' ) );
	}

/* BuddyPress specific activity filter */
function bp_translate_activity ( $activity ) {
	$activity->component_name = bp_translate_use_current_language_if_not_found_use_default_language( $activity->component_name );
	$activity->component_action = bp_translate_use_current_language_if_not_found_use_default_language( $activity->component_action );
	$activity->primary_link = bp_translate_convert_url($activity->primary_link);

	return $activity;
}

?>