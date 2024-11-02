<?php

$my_bp_translate_dashboard = new bp_translate_dashboard_langswitcher();

class bp_translate_dashboard_langswitcher {
	function bp_translate_dashboard_langswitcher( ) {
		global $text_direction;

		$plugin_url = WP_PLUGIN_URL . '/' . dirname( plugin_basename( __FILE__ ) );

		wp_enqueue_script( 'jquery' );

		wp_enqueue_style( 'bp-translate-dashboard-ui', $plugin_url . '/css/site-admin-switcher.css' );

		if ( $text_direction == 'rtl' )
			wp_enqueue_style('bp-translate-dashboard-css-rtl', $plugin_url . '/includes/css/rtl.css');

		add_action( 'admin_head', array( &$this, 'on_admin_head' ) );
		add_action( 'wp_ajax_bp_translate_dashboard_change_language', array( &$this, 'on_ajax_bp_translate_dashboard_change_language' ) );
		add_action( 'wp_ajax_bp_translate_dashboard_refresh_switcher', array( &$this, 'on_ajax_bp_translate_dashboard_refresh_switcher' ) );
	}

	function on_print_dashboard_switcher() {
		global $bp_translate;

		$user_language = bp_translate_get_language();
		$user_language = substr( $user_language, 0, 2 );

		echo '<div id="bpt-langswitcher-actions" class="alignright">';
		echo '<div id="bpt-langswitcher-first"><span class="bpt-' . $user_language . '">' . $bp_translate['language_name'][$user_language] . ' (' . $bp_translate['locale'][$user_language] . ')</span></div><div id="bpt-langswitcher-toggle"><br /></div>';
		echo '<div id="bpt-langswitcher-inside">';

		if ( count( $bp_translate['enabled_languages'] ) > 1 ) {
			foreach( $bp_translate['enabled_languages'] as $lang ) {
				if ( $lang != $user_language ) {
					echo '<div id="bpt-langswitcher-action">';
					echo '<a href="javascript:void(0);" hreflang="' . $bp_translate['locale'][$lang] . '">';
					echo $bp_translate['language_name'][$lang] . ' (' . $bp_translate['locale'][$lang] . ')';
					echo '</a></div>';
				}
			}
		}
		echo '</div></div>';
	}

	function on_admin_head() {
?>
		<script  type="text/javascript">
			//<![CDATA[
			function csl_extend_dashboard_header(html) {
				if (html) {
					jQuery("#bpt-langswitcher-actions").remove();
					jQuery("h1:first").before(html);
				} else {
					jQuery("#wphead-info #user_info").before('<?php $this->on_print_dashboard_switcher(); ?>');
				}
				jQuery("#bpt-langswitcher").click(function() {
					jQuery(this).blur();
					jQuery("#bpt-langoptions").toggle();
				});
				jQuery(".bpt-langoption").click(function() {
					jQuery(this).blur();
					jQuery("#bpt-langswitcher-inside").hide();
					jQuery.post("admin-ajax.php", { 
						action: 'bp_translate_dashboard_change_language',
						lang_admin: jQuery(this).attr('hreflang') },
						function(data) {
							window.location.reload();
						}
					)
				});
				jQuery('#bpt-langswitcher-toggle, #bpt-langswitcher-inside').bind( 'mouseenter', function(){jQuery('#bpt-langswitcher-inside').removeClass('slideUp').addClass('slideDown'); setTimeout(function(){if ( jQuery('#bpt-langswitcher-inside').hasClass('slideDown') ) { jQuery('#bpt-langswitcher-inside').slideDown(100); jQuery('#bpt-langswitcher-first').addClass('slide-down'); }}, 200) } );
				jQuery('#bpt-langswitcher-toggle, #bpt-langswitcher-inside').bind( 'mouseleave', function(){jQuery('#bpt-langswitcher-inside').removeClass('slideDown').addClass('slideUp'); setTimeout(function(){if ( jQuery('#bpt-langswitcher-inside').hasClass('slideUp') ) { jQuery('#bpt-langswitcher-inside').slideUp(100, function(){ jQuery('#bpt-langswitcher-first').removeClass('slide-down'); } ); }}, 300) } );
			}
			function csl_refresh_language_switcher() {
				jQuery.post("admin-ajax.php", {
					action: 'bp_translate_dashboard_refresh_switcher' },
					function(data) {
						csl_extend_dashboard_header(data);
					}
				)
			}
			jQuery(document).ready(function() {
				csl_extend_dashboard_header(false);
			});
			//]]>
		</script>
<?php
	}

	function on_ajax_bp_translate_dashboard_change_language() {
		$u = wp_get_current_user();

		if ( !$u->WPLANG_ADMIN )
			exit();

		update_usermeta( $u->ID, 'WPLANG_ADMIN', $_REQUEST['lang_admin'] );
		exit();
	}

	function on_ajax_bp_translate_dashboard_refresh_switcher() {
		$this->on_print_dashboard_switcher();
		exit();
	}
}

?>