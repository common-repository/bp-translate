<?php

/* BP Translate Widget */
function bp_translate_widget_init() {
	// Check to see required Widget API functions are defined...
	if ( !function_exists( 'register_sidebar_widget' ) || !function_exists( 'register_widget_control' ) )
		return; // ...and if not, exit gracefully from the script.
	
	function bp_translate_widget_switch($args) {
		global $bp_translate;
		extract($args);
		
		// Collect our widget's options, or define their defaults.
		$options = get_site_option('bp_translate_switch');
		$title = empty($options['bp-translate-switch-title']) ? __('Language', 'bp-translate') : $options['bp-translate-switch-title'];

		 // It's important to use the $before_widget, $before_title,
		 // $after_title and $after_widget variables in your output.
		echo $before_widget;

		if ( $options['bp-translate-switch-hide-title'] != 'on' )
			echo $before_title . bp_translate_use_current_language_if_not_found_use_default_language( $title ) . $after_title;

		bp_translate_generate_language_select_code( $options['bp-translate-switch-type'] );

		echo $after_widget;	 
	}
	
	function bp_translate_widget_switch_control() {

		// Collect our widget's options.
		$options = get_site_option('bp_translate_switch');

		// This is for handing the control form submission.
		if ( $_POST['bp-translate-switch-submit'] ) {
			// Clean up control form submission options
			$options['bp-translate-switch-title'] = strip_tags(stripslashes( $_POST['bp-translate-switch-title'] ) );
			$options['bp-translate-switch-hide-title'] = strip_tags(stripslashes( $_POST['bp-translate-switch-hide-title'] ) );
			$options['bp-translate-switch-type'] = strip_tags(stripslashes( $_POST['bp-translate-switch-type'] ) );
			update_site_option( 'bp_translate_switch', $options );
		}

		// Format options as valid HTML. Hey, why not.
		$title = htmlspecialchars( $options['bp-translate-switch-title'], ENT_QUOTES );
		$hide_title = htmlspecialchars( $options['bp-translate-switch-hide-title'], ENT_QUOTES );
		$type = $options['bp-translate-switch-type'];
		if ( $type != 'text' && $type != 'image' && $type != 'both' && $type != 'dropdown')
			$type ='text';

		// The HTML below is the control form for editing options.
?>
		<div>
			<label for="bp-translate-switch-title" style="line-height:35px;display:block;"><?php _e('Title:', 'bp-translate'); ?> <input type="text" id="bp-translate-switch-title" name="bp-translate-switch-title" value="<?php echo $title; ?>" /></label>
			<label for="bp-translate-switch-hide-title" style="line-height:35px;display:block;"><?php _e('Hide Title:', 'bp-translate'); ?> <input type="checkbox" id="bp-translate-switch-hide-title" name="bp-translate-switch-hide-title" <?php echo ($hide_title=='on')?'checked="checked"':''; ?>/></label>
			<?php _e('Display:', 'bp-translate'); ?> <br />
				<label for="bp-translate-switch-type1"><input type="radio" name="bp-translate-switch-type" id="bp-translate-switch-type1" value="text"<?php echo ($type=='text')?' checked="checked"':'' ?>/><?php _e('Text only', 'bp-translate'); ?></label><br />
				<label for="bp-translate-switch-type2"><input type="radio" name="bp-translate-switch-type" id="bp-translate-switch-type2" value="image"<?php echo ($type=='image')?' checked="checked"':'' ?>/><?php _e('Image only', 'bp-translate'); ?></label><br />
				<label for="bp-translate-switch-type3"><input type="radio" name="bp-translate-switch-type" id="bp-translate-switch-type3" value="both"<?php echo ($type=='both')?' checked="checked"':'' ?>/><?php _e('Text and Image', 'bp-translate'); ?></label><br />
				<label for="bp-translate-switch-type4"><input type="radio" name="bp-translate-switch-type" id="bp-translate-switch-type4" value="dropdown"<?php echo ($type=='dropdown')?' checked="checked"':'' ?>/><?php _e('Dropdown Box', 'bp-translate'); ?></label><br />
			<input type="hidden" name="bp-translate-switch-submit" id="bp-translate-switch-submit" value="1" />
		</div>
		<?php
	}
	
	register_sidebar_widget( 'BP Translate Language Chooser', 'bp_translate_widget_switch' );
	register_widget_control( 'BP Translate Language Chooser', 'bp_translate_widget_switch_control' );
}

?>