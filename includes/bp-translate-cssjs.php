<?php 

function bp_translate_init_js() {
	global $bp_translate;
	$bp_translate['js']['bp_translate_xsplit'] = "
		String.prototype.xsplit = function(_regEx){
			// Most browsers can do this properly, so let them — they'll do it faster
			if ('a~b'.split(/(~)/).length === 3) { return this.split(_regEx); }

			if (!_regEx.global)
			{ _regEx = new RegExp(_regEx.source, 'g' + (_regEx.ignoreCase ? 'i' : '')); }

			// IE (and any other browser that can't capture the delimiter)
			// will, unfortunately, have to be slowed down
			var start = 0, arr=[];
			var result;
			while((result = _regEx.exec(this)) != null){
				arr.push(this.slice(start, result.index));
				if(result.length > 1) arr.push(result[1]);
				start = _regEx.lastIndex;
			}
			if(start < this.length) arr.push(this.slice(start));
			if(start == this.length) arr.push(''); //delim at the end
			return arr;
		};
		";

	$bp_translate['js']['bp_translate_is_array'] = "
		bp_translate_isArray = function(obj) {
		   if (obj.constructor.toString().indexOf('Array') == -1)
			  return false;
		   else
			  return true;
		}
		";

	$bp_translate['js']['bp_translate_split'] = "
		bp_translate_split = function(text) {
			var split_regex = /(<!--.*?-->)/gi;
			var lang_begin_regex = /<!--:([a-z]{2})-->/gi;
			var lang_end_regex = /<!--:-->/gi;
			var morenextpage_regex = /(<!--more-->|<!--nextpage-->)+$/gi;
			var matches = null;
			var result = new Object;
			var matched = false;
		";
	foreach($bp_translate['enabled_languages'] as $language)
		$bp_translate['js']['bp_translate_split'].= "
			result['".$language."'] = '';
			";
	$bp_translate['js']['bp_translate_split'].= "

			var blocks = text.xsplit(split_regex);
			if(bp_translate_isArray(blocks)) {
				for (var i = 0;i<blocks.length;i++) {
					if((matches = lang_begin_regex.exec(blocks[i])) != null) {
						matched = matches[1];
					} else if(lang_end_regex.test(blocks[i])) {
						matched = false;
					} else {
						if(matched) {
							result[matched] += blocks[i];
						} else {
		";
	foreach($bp_translate['enabled_languages'] as $language)
		$bp_translate['js']['bp_translate_split'].= "
							result['".$language."'] += blocks[i];
			";
	$bp_translate['js']['bp_translate_split'].= "
						}
					}
				}
			}
			for (var i = 0;i<result.length;i++) {
				result[i] = result[i].replace(morenextpage_regex,'');
			}
			return result;
		}
		";

	$bp_translate['js']['bp_translate_use'] = "
		bp_translate_use = function(lang, text) {
			var result = bp_translate_split(text);
			return result[lang];
		}
		";

	$bp_translate['js']['bp_translate_integrate'] = "
		bp_translate_integrate = function(lang, lang_text, text) {
			var texts = bp_translate_split(text);
			var moreregex = /<!--more-->/i;
			var text = '';
			var max = 0;
			var morenextpage_regex = /(<!--more-->|<!--nextpage-->)+$/gi;

			texts[lang] = lang_text;
		";
	foreach($bp_translate['enabled_languages'] as $language)
		$bp_translate['js']['bp_translate_integrate'].= "
			texts['".$language."'] = texts['".$language."'].split(moreregex);
			if(!bp_translate_isArray(texts['".$language."'])) {
				texts['".$language."'] = [texts['".$language."']];
			}
			if(max < texts['".$language."'].length) max = texts['".$language."'].length;
			";
	$bp_translate['js']['bp_translate_integrate'].= "
			for(var i=0; i<max; i++) {
				if(i >= 1) {
					text += '<!--more-->';
				}
		";
	foreach($bp_translate['enabled_languages'] as $language)
		$bp_translate['js']['bp_translate_integrate'].= "
				if(texts['".$language."'][i] && texts['".$language."'][i]!=''){
					text += '<!--:".$language."-->';
					text += texts['".$language."'][i];
					text += '<!--:-->';
				}
			";
	$bp_translate['js']['bp_translate_integrate'].= "
			}
			text = text.replace(morenextpage_regex,'');
			return text;
		}
		";

	$bp_translate['js']['bp_translate_save'] = "
		bp_translate_save = function(text) {
			var ta = document.getElementById('content');
		";
	foreach($bp_translate['enabled_languages'] as $language)
		$bp_translate['js']['bp_translate_save'].= "
			ta.value = bp_translate_integrate(bp_translate_get_active_language(),text,ta.value);
			";
	$bp_translate['js']['bp_translate_save'].= "
			return ta.value;
		}
		";

	$bp_translate['js']['bp_translate_integrate_category'] = "
		bp_translate_integrate_category = function() {
			var t = document.getElementById('cat_name');
		";
	foreach($bp_translate['enabled_languages'] as $language)
		$bp_translate['js']['bp_translate_integrate_category'].= "
			if(document.getElementById('bp_translate_category_".$language."').value!='')
				t.value = bp_translate_integrate('".$language."',document.getElementById('bp_translate_category_".$language."').value,t.value);
			";
	$bp_translate['js']['bp_translate_integrate_category'].= "
		}
		";

	$bp_translate['js']['bp_translate_integrate_tag'] = "
		bp_translate_integrate_tag = function() {
			var t = document.getElementById('name');
		";
	foreach($bp_translate['enabled_languages'] as $language) {
		$bp_translate['js']['bp_translate_integrate_tag'].= "
			if(document.getElementById('bp_translate_tag_".$language."').value!='')
				t.value = bp_translate_integrate('".$language."',document.getElementById('bp_translate_tag_".$language."').value,t.value);
			";
	}
	$bp_translate['js']['bp_translate_integrate_tag'].= "
		}
		";

	$bp_translate['js']['bp_translate_integrate_link_category'] = "
		bp_translate_integrate_link_category = function() {
			var t = document.getElementById('name');
		";
	foreach($bp_translate['enabled_languages'] as $language)
		$bp_translate['js']['bp_translate_integrate_link_category'].= "
			if(document.getElementById('bp_translate_link_category_".$language."').value!='')
				t.value = bp_translate_integrate('".$language."',document.getElementById('bp_translate_link_category_".$language."').value,t.value);
			";
	$bp_translate['js']['bp_translate_integrate_link_category'].= "
		}
		";

	$bp_translate['js']['bp_translate_integrate_title'] = "
		bp_translate_integrate_title = function() {
			var t = document.getElementById('title');
		";
	foreach($bp_translate['enabled_languages'] as $language)
		$bp_translate['js']['bp_translate_integrate_title'].= "
			t.value = bp_translate_integrate('".$language."',document.getElementById('bp_translate_title_".$language."').value,t.value);
			";
	$bp_translate['js']['bp_translate_integrate_title'].= "
		}
		";

	$bp_translate['js']['bp_translate_assign'] = "
		bp_translate_assign = function(id, text) {
			var inst = tinyMCE.get(id);
			var ta = document.getElementById(id);
			if(inst && ! inst.isHidden()) {
				htm = switchEditors.wpautop(text);
				inst.execCommand('mceSetContent', null, htm);
			} else {
				ta.value = text;
			}
		}
		";

	$bp_translate['js']['bp_translate_disable_old_editor'] = "
		jQuery('#content').removeClass('theEditor');
		";

	$bp_translate['js']['bp_translate_tinyMCEOverload'] = "
		tinyMCE.get2 = tinyMCE.get;
		tinyMCE.get = function(id) {
			if(id=='content'&&this.get2('bp_translate_textarea_'+id)!=undefined)
				return this.get2('bp_translate_textarea_'+id);
			return this.get2(id);
		}
		";

	$bp_translate['js']['bp_translate_wpOnload'] = "
		jQuery(document).ready(function($){
			bp_translate_editorInit();
			
			var h = wpCookies.getHash('TinyMCE_content_size');
			var ta = document.getElementById('content');
			edCanvas = document.getElementById('bp_translate_textarea_content');
			ta.value = switchEditors.pre_wpautop(ta.value);

			if ( getUserSetting( 'editor' ) == 'html' ) {
				if ( h )
					$('#bp_translate_textarea_content').css('height', h.ch - 15 + 'px');
				$('#bp_translate_textarea_content').show();
			} else {
				$('#bp_translate_textarea_content').css('color', 'white');
				$('#quicktags').hide();
				// Activate TinyMCE if it's the user's default editor
				$('#content').hide();
				$('#bp_translate_textarea_content').show().addClass('theEditor');
				var waitForTinyMCE = window.setInterval(function() {
					if(tinyMCE.get('bp_translate_textarea_content')!=undefined) {
						tinyMCE.get('bp_translate_textarea_content').onSaveContent.add(function(ed, o) {
							bp_translate_save(o.content);
						});
						window.clearInterval(waitForTinyMCE);
					}
				}, 250);
			}
		});
		";

	$bp_translate['js']['bp_translate_get_active_language'] = "

		bp_translate_get_active_language = function() {
	";
	foreach( $bp_translate['enabled_languages'] as $language )
		$bp_translate['js']['bp_translate_get_active_language'] .= "
				if(document.getElementById('bp_translate_select_" . $language . "').className=='edButton active')
					return '" . $language . "';
			";
	$bp_translate['js']['bp_translate_get_active_language'] .= "
		}
		";

	$bp_translate['js']['bp_translate_switch'] = "
		switchEditors.go = function(id, lang) {
			var inst = tinyMCE.get('bp_translate_textarea_' + id);
			var qt = document.getElementById('quicktags');
			var vta = document.getElementById('bp_translate_textarea_' + id);
			var ta = document.getElementById(id);
			var pdr = document.getElementById('editorcontainer');

			// update merged content
			if(inst && ! inst.isHidden()) {
				tinyMCE.triggerSave();
				//temporal = tinyMCE.get('bp_translate_textarea_'+id).getContent();
				//bp_translate_save(temporal);
			} else {
				bp_translate_save(vta.value);
			}

			// check if language is already active
			if(lang!='tinymce' && lang!='html' && document.getElementById('bp_translate_select_'+lang).className=='edButton active') {
				return;
			}

			if(lang!='tinymce' && lang!='html') {
				document.getElementById('bp_translate_select_'+bp_translate_get_active_language()).className='edButton';
				document.getElementById('bp_translate_select_'+lang).className='edButton active';
			}

			if(lang=='html') {
				if ( ! inst || inst.isHidden() )
					return false;
				vta.style.height = inst.getContentAreaContainer().offsetHeight + 6 + 'px';
				inst.hide();
				qt.style.display = 'block';
				vta.style.color = '';
				document.getElementById('edButtonHTML').className = 'active';
				document.getElementById('edButtonPreview').className = '';
				setUserSetting( 'editor', 'html' );
			} else if(lang=='tinymce') {
				if(inst && ! inst.isHidden())
					return false;
				vta.style.color = '#fff';
				edCloseAllTags(); // :-(
				qt.style.display = 'none';
				vta.value = this.wpautop(bp_translate_use(bp_translate_get_active_language(),ta.value));
				if (inst) {
					inst.show();
				} else {
					tinyMCE.execCommand('mceAddControl', false, 'bp_translate_textarea_'+id);
				}
				document.getElementById('edButtonHTML').className = '';
				document.getElementById('edButtonPreview').className = 'active';
				setUserSetting( 'editor', 'tinymce' );
			} else {
				// switch content
				bp_translate_assign('bp_translate_textarea_'+id,bp_translate_use(lang,ta.value));
			}
		}
		";
}

function bp_translate_header_alt_lang() {
	global $bp_translate;

	// Set links to translations of current page
	$cur_lang = bp_translate_get_language();

	foreach ( $bp_translate['enabled_languages'] as $language ) {
		if ( $language != $cur_lang )
			echo '<link hreflang="' . $language . '" href="' . bp_translate_convert_url( '', $language ) . '" rel="alternate" rev="alternate" />' . "\n";
	}
	echo '<link hreflang="' . $language . '" href="' . bp_translate_convert_url( '', $language ) . '" rel="alternate" rev="alternate" />' . "\n";
}

function bp_translate_header_google_translate() {
	global $bp_translate;

	$user_lang = substr( bp_translate_user_locale(), 0, 2 );
?>
			<script type="text/javascript" src="<?php echo WP_PLUGIN_URL ?>/bp-translate/includes/js/jquery.translate.js"></script>
			<script type="text/javascript">
				jQuery(document).ready( function() {
<?php
	foreach($bp_translate['enabled_languages'] as $language) {
		$language = substr($bp_translate['locale'][$language], 0, 2);

		if ($user_lang != $language) : ?>
					jQuery('.<?php echo BP_TRANSLATE_CLASS_PREFIX . $bp_translate['locale'][$language] ?>').addClass('bp-translated').translate('<?php echo $language ?>', '<?php echo $user_lang ?>');
<?php
		endif;
	}
?>
				});
			</script>
<?php
}

function bp_translate_admin_header() {
	echo "<style type=\"text/css\" media=\"screen\">\n";
	echo ".edButton { cursor:pointer; display:block; float:right; height:18px; margin:5px 5px 0px 0px; padding:4px 5px 2px; border-width:1px; border-style:solid;";
	echo "-moz-border-radius: 3px 3px 0 0; -webkit-border-top-right-radius: 3px; -webkit-border-top-left-radius: 3px; -khtml-border-top-right-radius: 3px;";
	echo "-khtml-border-top-left-radius: 3px; border-top-right-radius: 3px; border-top-left-radius: 3px; background-color:#F1F1F1; border-color:#DFDFDF; color:#999999; }\n";
	echo ".bp_translate_title_wrap { -moz-border-radius: 4px; -webkit-border-radius: 4px; border-color:#ccc; border-style:solid; border-width:1px; padding:2px 3px; background-color: #fff; }\n";
	echo ".bp_translate_title_wrap input { border:0pt none; font-size:1.7em; outline-color:invert; outline-style:none; outline-width:medium; padding:0pt; width:100%; }\n";
	echo "#bp_translate_textarea_content { padding:6px; border:0 none; line-height:150%; outline: none; margin:0pt; width:100%; -moz-box-sizing: border-box; color: black;";
	echo "-webkit-box-sizing: border-box; -khtml-box-sizing: border-box; box-sizing: border-box; }\n";
	echo ".bp_translate_title { -moz-border-radius: 6px 6px 0 0;";
	echo "-webkit-border-top-right-radius: 6px; -webkit-border-top-left-radius: 6px; -khtml-border-top-right-radius: 6px; -khtml-border-top-left-radius: 6px;";
	echo "border-top-right-radius: 6px; border-top-left-radius: 6px; }\n";
	echo "#edButtonPreview { margin-left:6px !important;}";
	echo "#bp_translate_debug { width:100%; height:200px }";

	do_action('bp_translate_css');

	echo "</style>\n";

	return bp_translate_option_filter('disable');
}


?>