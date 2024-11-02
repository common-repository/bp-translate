<?php
/*
Based on CodeStyling Localization Version: 1.93 by Heiko Rabe
Plugin URI: http://www.code-styling.de/english/development/wordpress-plugin-codestyling-localization-en
 */
 
if ( function_exists( 'add_action' ) ) {

	define( "BP_TRANSLATE_PO_PLUGINPATH", "/bp-translate" );

	define( 'BP_TRANSLATE_PO_TEXTDOMAIN', 'codestyling-localization' );
	define( 'BP_TRANSLATE_PO_BASE_URL', WP_PLUGIN_URL . BP_TRANSLATE_PO_PLUGINPATH );

	if ( function_exists("admin_url") )
		define( 'BP_TRANSLATE_PO_ADMIN_URL', rtrim( admin_url(), '/' ) );
	else
		define( 'BP_TRANSLATE_PO_ADMIN_URL', rtrim( get_option('siteurl') . '/wp-admin/', '/' ) );

	define( 'BP_TRANSLATE_PO_BASE_PATH', WP_PLUGIN_DIR . BP_TRANSLATE_PO_PLUGINPATH );
	
	define( 'BP_TRANSLATE_PO_MIN_REQUIRED_WP_VERSION', '2.8.4' );
	define( 'BP_TRANSLATE_PO_MIN_REQUIRED_PHP_VERSION', '4.4.2' );
	
	register_activation_hook( __FILE__, 'bp_translate_po_install_plugin' );
}

if ( function_exists( 'bp_translate_po_install_plugin' ) ) {
	// rewrite and extend the error messages displayed at failed activation fall trough
	// if it's a real code bug forcing the activation error to get the appropriated message instead
	if ( isset( $_GET['action'] ) && isset( $_GET['plugin'] ) && ( $_GET['action'] == 'error_scrape') && ($_GET['plugin'] == plugin_basename(__FILE__) )) {
		if (
			(!version_compare($wp_version, BP_TRANSLATE_PO_MIN_REQUIRED_WP_VERSION, '>=')) 
			|| 
			(!version_compare(phpversion(), BP_TRANSLATE_PO_MIN_REQUIRED_PHP_VERSION, '>='))
			||
			!function_exists('token_get_all')
		) {
			load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
			echo "<table>";
			echo "<tr style=\"font-size: 12px;\"><td><strong style=\"border-bottom: 1px solid #000;\">Codestyling Localization</strong></td><td> | ".__('required', BP_TRANSLATE_PO_TEXTDOMAIN)."</td><td> | ".__('actual', BP_TRANSLATE_PO_TEXTDOMAIN)."</td></tr>";			
			if (!version_compare($wp_version, BP_TRANSLATE_PO_MIN_REQUIRED_WP_VERSION, '>=')) {
				echo "<tr style=\"font-size: 12px;\"><td>WordPress Blog Version:</td><td align=\"center\"> &gt;= <strong>".BP_TRANSLATE_PO_MIN_REQUIRED_WP_VERSION."</strong></td><td align=\"center\"><span style=\"color:#f00;\">".$wp_version."</span></td></tr>";
			}
			if (!version_compare(phpversion(), BP_TRANSLATE_PO_MIN_REQUIRED_PHP_VERSION, '>=')) {
				echo "<tr style=\"font-size: 12px;\"><td>PHP Interpreter Version:</td><td align=\"center\"> &gt;= <strong>".BP_TRANSLATE_PO_MIN_REQUIRED_PHP_VERSION."</strong></td><td align=\"center\"><span style=\"color:#f00;\">".phpversion()."</span></td></tr>";
			}
			if (!function_exists('token_get_all')) {
				echo "<tr style=\"font-size: 12px;\"><td>PHP Tokenizer Module:</td><td align=\"center\"><strong>active</strong></td><td align=\"center\"><span style=\"color:#f00;\">not installed</span></td></tr>";			
			}
			echo "</table>";
		}
	}
}


function bp_translate_po_install_plugin(){
	global $wp_version;
	if (
		(!version_compare($wp_version, BP_TRANSLATE_PO_MIN_REQUIRED_WP_VERSION, '>=')) 
		|| 
		(!version_compare(phpversion(), BP_TRANSLATE_PO_MIN_REQUIRED_PHP_VERSION, '>='))
		|| 
		!function_exists('token_get_all')
	){
		$current = get_option('active_plugins');
		array_splice($current, array_search( plugin_basename(__FILE__), $current), 1 );
		update_option('active_plugins', $current);
		exit;
	}
}


//////////////////////////////////////////////////////////////////////////////////////////
//	general purpose methods
//////////////////////////////////////////////////////////////////////////////////////////

if (!function_exists('_n')) {
	function _n() {
		$args = func_get_args();
		return call_user_func_array('__ngettext', $args);
	}
}

if (!function_exists('_n_noop')) {
	function _n_noop() {
		$args = func_get_args();
		return call_user_func_array('__ngettext_noop', $args);
	}
}

if (!function_exists('_x')) {
	function _x() {
		$args = func_get_args();
		$what = array_shift($args);
		$args[0] = $what.'|'.$args[0];
		return call_user_func_array('_c', $args);
	}
}

if (!function_exists('file_get_contents')) {
	function file_get_contents($filename, $incpath = false, $resource_context = null) {
		if (false === $fh = fopen($filename, 'rb', $incpath)) {
			user_error('file_get_contents() failed to open stream: No such file or directory', E_USER_WARNING);
			return false;
		}
		
		clearstatcache();
		if ($fsize = @filesize($filename)) {
			$data = fread($fh, $fsize);
		} else {
			$data = '';
			while (!feof($fh)) {
				$data .= fread($fh, 8192);
			}
		}
		
		fclose($fh);
		return $data;
	}	
}

if (!function_exists('scandir')) {
	function scandir($dir) {
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
		    $files[] = $filename;
		}
		closedir($dh);
		return $files;
	}
}

function has_subdirs($base='') {
	$array = array_diff(scandir($base), array('.', '..'));
  foreach($array as $value) : 
    if (is_dir($base.$value)) return true; 
  endforeach;
  return false;
}

function lscandir($base='', $reg='', &$data) {
  $array = array_diff(scandir($base), array('.', '..')); 
  foreach($array as $value) : 
		if (is_file($base.$value) && preg_match($reg, $value) ) : 
			$data[] = str_replace("\\","/",$base.$value); 
		endif;
  endforeach;  
  return $data; 
}

function rscandir($base='', $reg='', &$data) {
  $array = array_diff(scandir($base), array('.', '..')); 
  foreach($array as $value) : 
    if (is_dir($base.$value)) : 
      $data = rscandir($base.$value.'/', $reg, $data); 
    elseif (is_file($base.$value) && preg_match($reg, $value) ) : 
      $data[] = str_replace("\\","/",$base.$value); 
    endif;
  endforeach;
  return $data; 
}		

function rscanpath($base='', &$data) {
  $array = array_diff(scandir($base), array('.', '..')); 
  foreach($array as $value) : 
    if (is_dir($base.$value)) : 
	  $data[] = str_replace("\\","/",$base.$value);
      $data = rscanpath($base.$value.'/', $data); 
    endif;
  endforeach;
  return $data; 
}		


function rscandir_php($base='', &$exclude_dirs, &$data) {
  $array = array_diff(scandir($base), array('.', '..')); 
  foreach($array as $value) : 
    if (is_dir($base.$value)) : 
      if (!in_array($base.$value, $exclude_dirs)) : $data = rscandir_php($base.$value.'/', $exclude_dirs, $data); endif; 
    elseif (is_file($base.$value) && preg_match('/\.php$/', $value) ) : 
      $data[] = str_replace("\\","/",$base.$value); 
    endif;
  endforeach;
  return $data; 
}		

function file_permissions($filename) {
	static $R = array("---","--x","-w-","-wx","r--","r-x","rw-","rwx");
	$perm_o	= substr(decoct(fileperms( $filename )),3);
	return "[".$R[(int)$perm_o[0]] . '|' . $R[(int)$perm_o[1]] . '|' . $R[(int)$perm_o[2]]."]";
}

function bp_translate_fetch_remote_content($url) {
	$res = null;
	
	if(file_exists(ABSPATH . 'wp-includes/class-snoopy.php')) {
		require_once( ABSPATH . 'wp-includes/class-snoopy.php');
		$s = new Snoopy();
		$s->fetch($url);	
		if($s->status == 200) {
			$res = $s->results;	
		}
	} else {
		$res = wp_remote_fopen($url);	
	}
	return $res;	
}

function bp_translate_po_check_security() {
	if (!is_user_logged_in() || !current_user_can('edit_private_pages')) {
		wp_die(__('You do not have permission to manage translation files.', BP_TRANSLATE_PO_TEXTDOMAIN));
	}
}

function bp_translate_po_get_wordpress_capabilities() {
	$data = array();
	$data['locale'] = get_locale();
	$data['type'] = 'wordpress';
	$data['img_type'] = 'wordpress';
	if (isset($GLOBALS['wpmu_version'])) $data['img_type'] .= "_mu";
	$data['type-desc'] = __('WordPress',BP_TRANSLATE_PO_TEXTDOMAIN);
	$data['name'] = "WordPress";
	$data['author'] = "<a href=\"http://codex.wordpress.org/WordPress_in_Your_Language\">WordPress.org</a>";
	$data['version'] = $GLOBALS['wp_version'];
	if (isset($GLOBALS['wpmu_version'])) $data['version'] .= " | ".$GLOBALS['wpmu_version'];
	$data['description'] = "WordPress is a state-of-the-art publishing platform with a focus on aesthetics, web standards, and usability. WordPress is both free and priceless at the same time.<br />More simply, WordPress is what you use when you want to work with your blogging software, not fight it.";
	$data['status'] =  __("activated",BP_TRANSLATE_PO_TEXTDOMAIN);
	$data['base_path'] = str_replace("\\","/", ABSPATH);
	$data['special_path'] = '';
	$data['filename'] = str_replace(str_replace("\\","/",ABSPATH), '', str_replace("\\","/",WP_LANG_DIR));
	$data['textdomain'] = array('identifier' => '', 'is_const' => false );
	$data['languages'] = array();
	$data['is-path-unclear'] = false;
	$data['gettext_ready'] = true;
	$tmp = array();
	$data['is_US_Version'] = !is_dir(WP_LANG_DIR);
	if (!$data['is_US_Version']) {
		$files = rscandir(str_replace("\\","/",WP_LANG_DIR).'/', "/(.\mo|\.po)$/", $tmp);
		foreach($files as $filename) {
			$file = str_replace(str_replace("\\","/",WP_LANG_DIR).'/', '', $filename);
			preg_match("/^([a-z][a-z]_[A-Z][A-Z]).(mo|po)$/", $file, $hits);
			if (empty($hits[1]) === false) {
				$data['languages'][$hits[1]][$hits[2]] = array(
					'class' => "-".(is_readable($filename) ? 'r' : '').(is_writable($filename) ? 'w' : ''),
					'stamp' => date(__('m/d/Y H:i:s',BP_TRANSLATE_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename)
				);
				$data['special_path'] = '';
			}
		}

		$data['base_file'] = (empty($data['special_path']) ? '' : $data['special_path'].'/') . $data['filename'].'/';
	}
	return $data;
}

function bp_translate_po_get_plugin_capabilities($plug, $values) {
	$data = array();
	$data['locale'] = get_locale();
	$data['type'] = 'plugins';	
	$data['img_type'] = 'plugins';	
	$data['type-desc'] = __('Plugin',BP_TRANSLATE_PO_TEXTDOMAIN);	
	$data['name'] = $values['Name'];
	$data['author'] = $values['Author'];
	$data['version'] = $values['Version'];
	$data['description'] = $values['Description'];
	$data['status'] = is_plugin_active($plug) ? __("activated",BP_TRANSLATE_PO_TEXTDOMAIN) : __("deactivated",BP_TRANSLATE_PO_TEXTDOMAIN);
	$data['base_path'] = str_replace("\\","/", WP_PLUGIN_DIR.'/'.dirname($plug).'/');
	$data['special_path'] = '';
	$data['filename'] = "";
	$data['is-simple'] = (dirname($plug) == '.');
	$data['is-path-unclear'] = false;
	$data['gettext_ready'] = false;

	if ($data['is-simple']) {
		$files = array(WP_PLUGIN_DIR.'/'.$plug);
	}
	else{
		$tmp = array();
		$files = rscandir(str_replace("\\","/",WP_PLUGIN_DIR).'/'.dirname($plug)."/", "/.php$/", $tmp);
	}
	$const_list = array();
	foreach($files as $file) {	
		$content = file_get_contents($file);
		if (preg_match("/load_(|plugin_)textdomain\s*\(\s*(\'|\"|)([\w\-_]+|[A-Z\-_]+)(\'|\"|)\s*(,|\))\s*([^;]+)\)/", $content, $hits)) {
			$data['textdomain'] = array('identifier' => $hits[3], 'is_const' => empty($hits[2]) );
			$data['gettext_ready'] = true;
			$data['php-path-string'] = $hits[6];
		}
		else if(preg_match("/load_(|plugin_)textdomain\s*\(/", $content, $hits)) {
			//ATTENTION: it is gettext ready but we don't realy know the textdomain name! Assume it's equal to plugin's name.
			//TODO: let's think about it in future to find a better solution.
			$data['textdomain'] = array('identifier' => substr(basename($plug),0,-4), 'is_const' => false );
			$data['gettext_ready'] = true;
			$data['php-path-string'] = '';			
		}
		if($data['gettext_ready'] && !$data['textdomain']['is_const']) break; //make it short :-)
		if (preg_match_all("/define\s*\(([^\)]+)\)/" , $content, $hits)) {
			$const_list = array_merge($const_list, $hits[1]);
		}
	}
	if ($data['gettext_ready']) {
		
		if ($data['textdomain']['is_const']) {
			foreach($const_list as $e) {
				$a = split(',', $e);
				$c = trim($a[0], "\"' \t");
				if ($c == $data['textdomain']['identifier']) {
					$data['textdomain']['is_const'] = $data['textdomain']['identifier'];
					$data['textdomain']['identifier'] = trim($a[1], "\"' \t");
				}
			}
		}
		$data['filename'] = $data['textdomain']['identifier'];
	}		
	
	$data['languages'] = array();
	if($data['gettext_ready']){
		if ($data['is-simple']) { $tmp = array(); $files = lscandir(str_replace("\\","/",dirname(WP_PLUGIN_DIR.'/'.$plug)).'/', "/(.\mo|.po)$/", $tmp); }
		else { 	$tmp = array(); $files = rscandir(str_replace("\\","/",dirname(WP_PLUGIN_DIR.'/'.$plug)).'/', "/(.\mo|.po|.pot)$/", $tmp); }
		foreach($files as $filename) {
			if ($data['is-simple']) {
				$file = str_replace(str_replace("\\","/",WP_PLUGIN_DIR).'/'.dirname($plug), '', $filename);
				preg_match("/".$data['filename']."-([a-z][a-z]_[A-Z][A-Z]).(mo|po)$/", $file, $hits);		
				if (empty($hits[2]) === false) {				
					$data['languages'][$hits[1]][$hits[2]] = array(
						'class' => "-".(is_readable($filename) ? 'r' : '').(is_writable($filename) ? 'w' : ''),
						'stamp' => date(__('m/d/Y H:i:s',BP_TRANSLATE_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename)
					);
					$data['special_path'] = '';
				}
			}else{
				$file = str_replace(str_replace("\\","/",WP_PLUGIN_DIR).'/'.dirname($plug), '', $filename);
				preg_match("/([\/a-z0-9\-_]*)\/".$data['filename']."-([a-z][a-z]_[A-Z][A-Z]).(mo|po)$/", $file, $hits);
				if (empty($hits[2]) === false) {
					$data['languages'][$hits[2]][$hits[3]] = array(
						'class' => "-".(is_readable($filename) ? 'r' : '').(is_writable($filename) ? 'w' : ''),
						'stamp' => date(__('m/d/Y H:i:s',BP_TRANSLATE_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename)
					);
					$data['special_path'] = ltrim($hits[1], "/");
				}
			}
		}
		if (!$data['is-simple'] && ($data['special_path'] == '') && (count($data['languages']) == 0)) {
			$data['is-path-unclear'] = has_subdirs(str_replace("\\","/",dirname(WP_PLUGIN_DIR.'/'.$plug)).'/');
			if ($data['is-path-unclear'] && (count($files) > 0)) {
				$file = str_replace(str_replace("\\","/",WP_PLUGIN_DIR).'/'.dirname($plug), '', $files[0]);
				preg_match("/^\/([\/a-z0-9\-_]*)\//", $file, $hits);
				$data['is-path-unclear'] = false;
				if (empty($hits[1]) === false) { $data['special_path'] = $hits[1]; }
			}
		}
		
		//DEBUG:  $data['php-path-string']  will contain real path part like: "false,'codestyling-localization'" | "'wp-content/plugins/' . NGGFOLDER . '/lang'" | "GENGO_LANGUAGES_DIR" | "$moFile"
		//this may be part of later excessive parsing to find correct lang file path even if no lang files exist as hint or implementation of directory selector, if 0 languages contained
		//if any lang files may be contained the qualified sub path will be extracted out of
		//will be handled in case of  $data['is-path-unclear'] == true by display of treeview at file creation dialog 
		//var_dump($data['php-path-string']);

	}
	$data['base_file'] = (empty($data['special_path']) ? $data['filename'] : $data['special_path']."/".$data['filename']).'-';	
	return $data;
}

function bp_translate_po_get_plugin_mu_capabilities($plug, $values){
	$data = array();
	$data['locale'] = get_locale();
	$data['type'] = 'plugins_mu';	
	$data['img_type'] = 'plugins_mu';	
	$data['type-desc'] = __('μ Plugin',BP_TRANSLATE_PO_TEXTDOMAIN);	
	$data['name'] = $values['Name'];
	$data['author'] = $values['Author'];
	$data['version'] = $values['Version'];
	$data['description'] = $values['Description'];
	$data['status'] = __("activated",BP_TRANSLATE_PO_TEXTDOMAIN);
	$data['base_path'] = str_replace("\\","/", WPMU_PLUGIN_DIR.'/'.dirname($plug).'/');
	$data['special_path'] = '';
	$data['filename'] = "";
	$data['is-simple'] = true;
	$data['is-path-unclear'] = false;
	$data['gettext_ready'] = false;
	$file = WPMU_PLUGIN_DIR.'/'.$plug;

	$const_list = array();
	$content = file_get_contents($file);
	if (preg_match("/load_(|plugin_)textdomain\s*\(\s*(\'|\"|)([\w\-_]+|[A-Z\-_]+)(\'|\"|)\s*(,|\))\s*([^;]+)\)/", $content, $hits)) {
		$data['textdomain'] = array('identifier' => $hits[3], 'is_const' => empty($hits[2]) );
		$data['gettext_ready'] = true;
		$data['php-path-string'] = $hits[6];
	}
	else if(preg_match("/load_(|plugin_)textdomain\s*\(/", $content, $hits)) {
		//ATTENTION: it is gettext ready but we don't realy know the textdomain name! Assume it's equal to plugin's name.
		//TODO: let's think about it in future to find a better solution.
		$data['textdomain'] = array('identifier' => substr(basename($plug),0,-4), 'is_const' => false );
		$data['gettext_ready'] = true;
		$data['php-path-string'] = '';			
	}
	if (!($data['gettext_ready'] && !$data['textdomain']['is_const'])) {
		if (preg_match_all("/define\s*\(([^\)]+)\)/" , $content, $hits)) {
			$const_list = array_merge($const_list, $hits[1]);
		}
	}

	if ($data['gettext_ready']) {
		
		if ($data['textdomain']['is_const']) {
			foreach($const_list as $e) {
				$a = split(',', $e);
				$c = trim($a[0], "\"' \t");
				if ($c == $data['textdomain']['identifier']) {
					$data['textdomain']['is_const'] = $data['textdomain']['identifier'];
					$data['textdomain']['identifier'] = trim($a[1], "\"' \t");
				}
			}
		}
		$data['filename'] = $data['textdomain']['identifier'];
	}		
	
	$data['languages'] = array();
	if($data['gettext_ready']){
		$tmp = array(); $files = lscandir(str_replace("\\","/",dirname(WPMU_PLUGIN_DIR.'/'.$plug)).'/', "/(.\mo|.po)$/", $tmp); 		
		foreach($files as $filename) {
			$file = str_replace(str_replace("\\","/",WPMU_PLUGIN_DIR).'/'.dirname($plug), '', $filename);
			preg_match("/".$data['filename']."-([a-z][a-z]_[A-Z][A-Z]).(mo|po)$/", $file, $hits);		
			if (empty($hits[2]) === false) {				
				$data['languages'][$hits[1]][$hits[2]] = array(
					'class' => "-".(is_readable($filename) ? 'r' : '').(is_writable($filename) ? 'w' : ''),
					'stamp' => date(__('m/d/Y H:i:s',BP_TRANSLATE_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename)
				);
				$data['special_path'] = '';
			}
		}
	}
	$data['base_file'] = (empty($data['special_path']) ? $data['filename'] : $data['special_path']."/".$data['filename']).'-';		
	return $data;
}

function bp_translate_po_get_theme_capabilities( $theme, $values, $active ) {
	$data = array();
	$data['locale'] = get_locale();
	$data['type'] = 'themes';
	$data['img_type'] = 'themes';	
	$data['type-desc'] = __('Theme',BP_TRANSLATE_PO_TEXTDOMAIN);	
	$data['name'] = $values['Name'];
	$data['author'] = $values['Author'];
	$data['version'] = $values['Version'];
	$data['description'] = $values['Description'];
	$data['status'] = $theme == $active->name ? __( "activated", BP_TRANSLATE_PO_TEXTDOMAIN ) : __( "deactivated", BP_TRANSLATE_PO_TEXTDOMAIN );
	$data['base_path'] = str_replace( "\\","/", dirname( $values['Template Files'][0] ) . '/' );

	if ( file_exists( $values['Template Files'][0] ) )
		$data['base_path'] = dirname( str_replace( "\\", "/", $values['Template Files'][0] ) ) . '/';
	
	$data['special-path'] = '';

	foreach( $values['Template Files'] as $themefile ) {

		//$main = str_replace( "\\","/", $themefile );
		//$main = file_get_contents( $main );
		$main = '';
		if (!file_exists($themefile)) {
			$main = file_get_contents(WP_CONTENT_DIR.str_replace('wp-content', '', $themefile));
		} else {
			$main = file_get_contents($themefile);
		}

		if ( preg_match( "/load_theme_textdomain\s*\(\s*(\'|\"|)([\w\-_]+|[A-Z\-_]+)(\'|\"|)\s*(,|\))/", $main, $hits ) )
			break;

		if ( preg_match( "/load_child_theme_textdomain\s*\(\s*(\'|\"|)([\w\-_]+|[A-Z\-_]+)(\'|\"|)\s*(,|\))/", $main, $hits ) )
			break;

	}
	$data['is-path-unclear'] = false;
	$data['gettext_ready'] = empty( $hits ) === false;

	if ( $data['gettext_ready'] ) {

		$data['textdomain'] = array( 'identifier' => $hits[2], 'is_const' => empty( $hits[1] ) );
		$data['languages'] = array();
	
		$const_list = array();
		if ( !( $data['gettext_ready'] && !$data['textdomain']['is_const'] ) ) {
			if ( preg_match_all("/define\s*\(([^\)]+)\)/" , $main, $hits ) )
				$const_list = array_merge($const_list, $hits[1]);

		}
		if ( $data['gettext_ready'] ) {
			if ( $data['textdomain']['is_const'] ) {
				foreach( $const_list as $e ) {
					$a = split(',', $e);
					$c = trim($a[0], "\"' \t");

					if ( $c == $data['textdomain']['identifier'] ) {
						$data['textdomain']['is_const'] = $data['textdomain']['identifier'];
						$data['textdomain']['identifier'] = trim($a[1], "\"' \t");
					}
				}
			}		
		}		
	
		$tmp = array();
		//$dn = str_replace( "\\","/", dirname($values['Template Files'][0]) . '/' );
		$dn = dirname(str_replace("\\","/",WP_CONTENT_DIR).str_replace('wp-content', '', $values['Template Files'][0]));

		if ( file_exists( $values['Template Files'][0] ) )
			$dn = dirname( str_replace( "\\", "/", $values['Template Files'][0] ) );

		$files = rscandir( $dn . '/', "/(.\mo|\.po|\.pot)$/", $tmp );
		$sub_dirs = array();

		foreach( $files as $filename ) {
			preg_match( "/\/([a-z][a-z]_[A-Z][A-Z]).(mo|po)$/", $filename, $hits );
			if ( empty($hits[1]) === false ) {
				$data['languages'][$hits[1]][$hits[2]] = array(
					'class' => "-" . ( is_readable( $filename ) ? 'r' : '') . ( is_writable( $filename ) ? 'w' : '' ),
					'stamp' => date( __( 'm/d/Y H:i:s', BP_TRANSLATE_PO_TEXTDOMAIN ), filemtime( $filename ) ) . " " . file_permissions( $filename )
				);
				$data['filename'] = '';
				$sd = dirname( str_replace( $dn . '/', '', $filename ) );
				
				if ($sd == '.')
					$sd = '';
				
				if ( !in_array( $sd, $sub_dirs ) )
					$sub_dirs[] = $sd;
			}
		}

		//completely other directories can be defined WP if >= 2.7.0
		global $wp_version;
		if ( version_compare($wp_version, '2.7', '>=' ) ) {
			if ( count($data['languages'] ) == 0 ) {
				$data['is-path-unclear'] = has_subdirs( $dn . '/' );
				if ( $data['is-path-unclear'] && ( count( $files ) > 0 ) ) {
					foreach( $files as $file ) {
						$f = str_replace( $dn . '/', '', $file );
						if ( preg_match( "/^([a-z][a-z]_[A-Z][A-Z]).(mo|po|pot)$/", basename( $f ) ) ) {
							$data['special_path'] = ( dirname( $f ) == '.' ? '' : dirname( $f ) );
							$data['is-path-unclear'] = false;
							break;
						}
					}
				}
			}
			else{
				if ( $sub_dirs[0] != '' )
					$data['special_path'] = ltrim( $sub_dirs[0], "/" );

			}
		}
	}
	
	$data['base_file'] = ( empty( $data['special_path'] ) ? '' : $data['special_path'] . "/" );

	return $data;
}

function bp_translate_po_collect_by_type($type){
	$res = array();
	if (empty($type) || ($type == 'wordpress')) {
		$res[] = bp_translate_po_get_wordpress_capabilities();
	}
	if (empty($type) || ($type == 'plugins')) {
		//WARNING: Plugin handling is not well coded by WordPress core
		$err = error_reporting(0);
		$plugs = get_plugins(); 
		error_reporting($err);
		$textdomains = array();
		foreach($plugs as $key => $value) { 
			$data = bp_translate_po_get_plugin_capabilities($key, $value);
			if (!$data['gettext_ready']) continue;
			if (in_array($data['textdomain'], $textdomains)) {
				for ($i=0; $i<count($res); $i++) {
					if ($data['textdomain'] == $res[$i]['textdomain']) {
						$res[$i]['child-plugins'][] = $data;
						break;
					}
				}
			}
			else{
				array_push($textdomains, $data['textdomain']);
				$res[] = $data;
			}
		}
	}
	if (isset($GLOBALS['wpmu_version'])) {
		if (empty($type) || ($type == 'plugins_mu')) {
			$plugs = array();
			$textdomains = array();
			if( is_dir( WPMU_PLUGIN_DIR ) ) {
				if( $dh = opendir( WPMU_PLUGIN_DIR ) ) {
					while( ( $plugin = readdir( $dh ) ) !== false ) {
						if( substr( $plugin, -4 ) == '.php' ) {
							$plugs[$plugin] = get_plugin_data( WPMU_PLUGIN_DIR . '/' . $plugin );
						}
					}
				}
			}		
			foreach($plugs as $key => $value) { 
				$data = bp_translate_po_get_plugin_mu_capabilities($key, $value);
				if (!$data['gettext_ready']) continue;
				if (in_array($data['textdomain'], $textdomains)) {
					for ($i=0; $i<count($res); $i++) {
						if ($data['textdomain'] == $res[$i]['textdomain']) {
							$res[$i]['child-plugins'][] = $data;
							break;
						}
					}
				}
				else{
					array_push($textdomains, $data['textdomain']);
					$res[] = $data;
				}
			}
		}
	}

	if ( empty( $type ) || ( $type == 'themes' ) ) {
		$themes = get_themes();

		//WARNING: Theme handling is not well coded by WordPress core
		$err = error_reporting(0);
		$ct = current_theme_info();
		error_reporting( $err );

		foreach( $themes as $key => $value ) {
			$data = bp_translate_po_get_theme_capabilities( $key, $value, $ct );

			if ( !$data['gettext_ready'] )
				continue;

			$res[] = $data;
		}	
	}
	return $res;
}

//////////////////////////////////////////////////////////////////////////////////////////
//	Admin Ajax Handler
//////////////////////////////////////////////////////////////////////////////////////////

if (function_exists('add_action')) {
	add_action('wp_ajax_bp_translate_po_dlg_new', 'bp_translate_po_ajax_handle_dlg_new');
	add_action('wp_ajax_bp_translate_po_dlg_delete', 'bp_translate_po_ajax_handle_dlg_delete');
	add_action('wp_ajax_bp_translate_po_dlg_rescan', 'bp_translate_po_ajax_handle_dlg_rescan');
	add_action('wp_ajax_bp_translate_po_dlg_show_source', 'bp_translate_po_ajax_handle_dlg_show_source');
	
	add_action('wp_ajax_bp_translate_po_create', 'bp_translate_po_ajax_handle_create');
	add_action('wp_ajax_bp_translate_po_destroy', 'bp_translate_po_ajax_handle_destroy');
	add_action('wp_ajax_bp_translate_po_scan_source_file', 'bp_translate_po_ajax_handle_scan_source_file');	
	add_action('wp_ajax_bp_translate_po_change_permission', 'bp_translate_po_ajax_handle_change_permission');
	add_action('wp_ajax_bp_translate_po_launch_editor', 'bp_translate_po_ajax_handle_launch_editor');
	add_action('wp_ajax_bp_translate_po_translate_by_google', 'bp_translate_po_ajax_handle_translate_by_google');
	add_action('wp_ajax_bp_translate_po_save_catalog_entry', 'bp_translate_po_ajax_handle_save_catalog_entry');
	add_action('wp_ajax_bp_translate_po_generate_mo_file', 'bp_translate_po_ajax_handle_generate_mo_file');
	add_action('wp_ajax_bp_translate_po_create_language_path', 'bp_translate_po_ajax_handle_create_language_path');
	add_action('wp_ajax_bp_translate_po_create_pot_indicator', 'bp_translate_po_ajax_handle_create_pot_indicator');
	//WP 2.7 help extensions
	add_filter('screen_meta_screen', 'bp_translate_po_filter_screen_meta_screen');
	add_filter('contextual_help_list', 'bp_translate_po_filter_help_list_filter');
}

//WP 2.7 help extensions
function bp_translate_po_filter_screen_meta_screen($screen) {
	if (preg_match('/codestyling-localization$/', $screen)) return "codestyling-localization";
	return $screen;
}

//WP 2.7 help extensions
function bp_translate_po_filter_help_list_filter($_wp_contextual_help) {

	require_once(ABSPATH.'/wp-includes/rss.php');
	$rss = fetch_rss('http://www.code-styling.de/online-help/plugins.php?type=config&locale='.get_locale().'&plug=codestyling-localization');
	if ( $rss ) {
		$_wp_contextual_help['codestyling-localization'] = '';
		foreach ($rss->items as $item ) {
			if ($item['category'] == 'thickbox') {
				$_wp_contextual_help['codestyling-localization'] .= '<a href="'. $item['link'] . '&amp;TB_iframe=true" class="thickbox" name="<strong>'. $item['title'] . '</strong>">'. $item['title'] . '</a> | ';
			} else {
				$_wp_contextual_help['codestyling-localization'] .= '<a target="_blank" href="'. $item['link'] . '" >'. $item['title'] . '</a> | ';
			}
		}
	}
	return $_wp_contextual_help;
}

function bp_translate_po_ajax_handle_dlg_new() {
	bp_translate_po_check_security();
	load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
	require_once('bp-translate-l10n.php');
?>
	<table class="widefat" cellspacing="2px">
		<tr>
			<td nowrap="nowrap"><strong><?php _e('Project-Id-Version',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
			<td><?php echo rawurldecode($_POST['name']); ?><input type="hidden" id="bp-translate-dialog-name" value="<?php echo rawurldecode($_POST['name']); ?>" /></td>
		</tr>
		<tr>
			<td><strong><?php _e('Creation-Date',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
			<td><?php echo date("Y-m-d H:iO"); ?><input type="hidden" id="bp-translate-dialog-timestamp" value="<?php echo date("Y-m-d H:iO"); ?>" /></td>
		</tr>
		<tr>
			<td style="vertical-align:middle;"><strong><?php _e('Last-Translator',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
			<td><input style="width:330px;" type="text" id="bp-translate-dialog-translator" value="<?php $myself = wp_get_current_user(); echo "$myself->user_nicename &lt;$myself->user_email&gt;"; ?>" /></td>
		</tr>
		<tr>
			<td valign="top"><strong><?php echo $bp_translate_l10n_login_label[substr(get_locale(),0,2)]?>:</strong></td>
			<td>
				<div style="width:332px;height:300px; overflow:scroll;border:solid 1px #54585B;overflow-x:hidden;">
					<?php $existing = explode('|', ltrim($_POST['existing'],'|')); if(strlen($existing[0]) == 0) $existing=array(); ?>
					<input type="hidden" id="bp-translate-dialog-row" value="<?php echo $_POST['row']; ?>" />
					<input type="hidden" id="bp-translate-dialog-numlangs" value="<?php echo count($existing)+1; ?>" />
					<input type="hidden" id="bp-translate-dialog-language" value="" />
					<input type="hidden" id="bp-translate-dialog-path" value="<?php echo $_POST['path']; ?>" />
					<input type="hidden" id="bp-translate-dialog-subpath" value="<?php echo $_POST['subpath']; ?>" />
					<table style="font-family:monospace;">
					<?php
						$total = array_keys($bp_translate_l10n_sys_locales);
						foreach($total as $key) {
							if (in_array($key, $existing)) continue;
							$values = $bp_translate_l10n_sys_locales[$key];
							if ( get_locale() == $key ) { $selected = '" selected="selected'; } else { $selected=""; };
							?>
							<tr>
								<td><input type="radio" name="mo-locale" value="<?php echo $key; ?><?php echo $selected; ?>" onclick="$('submit_language').enable();$('bp-translate-dialog-language').value = this.value;" /></td>
								<td><img alt="" title="locale: <?php echo $key ?>" src="<?php echo BP_TRANSLATE_PO_BASE_URL."/includes/images/flags/".$bp_translate_l10n_sys_locales[$key]['country-www']."/images\""; ?>" /></td>
								<td><?php echo $key; ?></td>
								<td style="padding-left: 5px;border-left: 1px solid #aaa;"><?php echo $values['lang-native']."<br />"; ?></td>
							</tr>
							<?php
						}
					?>
					</table>
				</div>
			</td>
		</tr>
	</table>
	<div style="text-align:center; padding-top: 10px"><input class="button" id="submit_language" type="submit" disabled="disabled" value="<?php _e('create po-file',BP_TRANSLATE_PO_TEXTDOMAIN); ?>" onclick="return bp_translate_create_new_pofile(this,<?php echo "'".$_POST['type']."'"; ?>);"/></div>
<?php
exit();
}

function bp_translate_po_ajax_handle_dlg_delete() {
	bp_translate_po_check_security();
	load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
	require_once('bp-translate-l10n.php');
	$lang = isset($bp_translate_l10n_sys_locales[$_POST['language']]) ? $bp_translate_l10n_sys_locales[$_POST['language']]['lang-native'] : $_POST['language'];
?>
	<p style="text-align:center;"><?php echo sprintf(__('You are about to delete <strong>%s</strong> from "<strong>%s</strong>" permanently.<br/>Are you sure you wish to delete these files?', BP_TRANSLATE_PO_TEXTDOMAIN), $lang, rawurldecode($_POST['name'])); ?></p>
	<div style="text-align:center; padding-top: 10px"><input class="button" id="submit_language" type="submit" value="<?php _e('delete files',BP_TRANSLATE_PO_TEXTDOMAIN); ?>" onclick="bp_translate_destroy_files(this,'<?php echo str_replace("'", "\\'", rawurldecode($_POST['name']))."','".$_POST['row']."','".$_POST['path']."','".$_POST['subpath']."','".$_POST['language']."','".$_POST['numlangs'];?>');" /></div>
<?php
	exit();
}

function bp_translate_po_ajax_handle_dlg_rescan() {
	bp_translate_po_check_security();
	load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
	require_once('bp-translate-l10n.php');
	if ($_POST['type'] == 'wordpress') {
		$files = array();
		$excludes = array(str_replace("\\","/",WP_CONTENT_DIR));
		rscandir_php($_POST['path'], $excludes, $files);
		$excludes = array();
		rscandir_php(str_replace("\\","/",WP_CONTENT_DIR)."/themes/default/",$excludes, $files);
		rscandir_php(str_replace("\\","/",WP_CONTENT_DIR)."/themes/classic/",$excludes, $files);
		$files[] = str_replace("\\","/",WP_PLUGIN_DIR)."/akismet/akismet.php";
	}
	else{
		$files = array();
		$excludes = array();
		if (strpos($_POST['path'], '/./')) { $files[] = substr($_POST['path'].$_POST['subpath'],0,-1).'.php'; }
		else { rscandir_php($_POST['path'], $excludes, $files); }
	}
	$country_www = isset($bp_translate_l10n_sys_locales[$_POST['language']]) ? $bp_translate_l10n_sys_locales[$_POST['language']]['country-www'] : 'unknown';
	$lang_native = isset($bp_translate_l10n_sys_locales[$_POST['language']]) ? $bp_translate_l10n_sys_locales[$_POST['language']]['lang-native'] : $_POST['language'];
	$filename = $_POST['path'].$_POST['subpath'].$_POST['language'].".po";
?>	
	<input id="bp-translate-dialog-source-file-json" type="hidden" value="{ <?php 
		echo "row: '".$_POST['row']."',";
		echo "language: '".$_POST['language']."',";
		echo "textdomain: '".$_POST['textdomain']."',";
		echo "next : 0,";
		echo "path : '".$_POST['path']."',";
		echo "pofile : '".$_POST['path'].$_POST['subpath'].$_POST['language'].".po',";
		echo "files : ['".implode("','",$files)."']"
	?>}" />
	<table class="widefat" cellspacing="2px">
		<tr>
			<td nowrap="nowrap"><strong><?php _e('Project-Id-Version',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
			<td colspan="2"><?php echo rawurldecode($_POST['name']); ?><input type="hidden" name="name" value="<?php echo rawurldecode($_POST['name']); ?>" /></td>
		</tr>
		<tr>
			<td nowrap="nowrap"><strong><?php _e('Language Target',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
			<td><img alt="" title="locale: <?php echo $_POST['language']; ?>" src="<?php echo BP_TRANSLATE_PO_BASE_URL."/includes/images/flags/".$country_www.".png\""; ?>" /></td>
			<td><?php echo $lang_native; ?></td>
		</tr>	
		<tr>
			<td nowrap="nowrap"><strong><?php _e('Affected Total Files',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
			<td nowrap="nowrap" align="right"><?php echo count($files); ?></td>
			<td><em><?php echo "/".str_replace(str_replace("\\",'/',ABSPATH), '', $_POST['path']); ?></em></td>
		</tr>
		<tr>
			<td nowrap="nowrap" valign="top"><strong><?php _e('Scanning Progress',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
			<td id="bp-translate-dialog-progressvalue" nowrap="nowrap" valign="top" align="right">0</td>
			<td>
				<div style="height:13px;width:290px;border:solid 1px #333;"><div id="bp-translate-dialog-progressbar" style="height: 13px;width:0%; background-color:#0073D9"></div></div>
				<div id="bp-translate-dialog-progressfile" style="width:290px;white-space:nowrap;overflow:hidden;font-size:8px;font-family:monospace;padding-top:3px;">&nbsp;</div>
			</td>
		</tr>
	</table>
	<div style="text-align:center; padding-top: 10px"><input class="button" id="bp-translate-dialog-rescan" type="submit" value="<?php _e('scan now',BP_TRANSLATE_PO_TEXTDOMAIN); ?>" onclick="bp_translate_scan_source_files(this);"/><span id="bp-translate-dialog-scan-info" style="display:none"><?php _e('Please standby, files presently being scanned ...',BP_TRANSLATE_PO_TEXTDOMAIN); ?></span></div>
<?php
	exit();
}

function bp_translate_po_convert_js_input_for_source($str) {
	$search = array('\\\\\"','\\\\n', '\\\\t', '\\\\$','\\0', "\\'", '\\\\');
	$replace = array('"', "\n", "\\t", "\\$", "\0", "'", "\\");
	$str = str_replace( $search, $replace, $str );
	return $str;
}

function bp_translate_po_ajax_handle_dlg_show_source() {
	bp_translate_po_check_security();
	load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
	list($file, $match_line) = explode(':', $_POST['file']);
	$l = filesize($_POST['path'].$file);
	$handle = fopen($_POST['path'].$file,'rb');
	$content = str_replace(array("\r","\\$"),array('','$'), fread($handle, $l));
	fclose($handle);

	$msgid = $_POST['msgid'];
	$msgid = bp_translate_po_convert_js_input_for_source($msgid);	
	if (strlen($msgid) > 0) {
		if (strpos($msgid, "\00") > 0)
			$msgid = explode("\00", $msgid);
		else
			$msgid = explode("\01", $msgid); //opera fix
		foreach($msgid as $item) {	
			if (strpos($content, $item) === false) {
				//difficult to separate between real \n notation and LF brocken strings also \t 
				$test = str_replace("\n", '\n', $item);
				if (strpos($content, $test) === false) {
					$test2 = str_replace('\t', "\t", $item);
					if (strpos($content, $test2) === false) {
						$test2 = str_replace('\t', "\t", $test);
						if (strpos($content, $test2) === true) {
							$item = $test2;
						}
					}else{
						$item = $test2;
					}
				}else {
					$item = $test;
				}
			}
			$content = str_replace($item, "\1".$item."\2", $content);
		}
	}
	$content = htmlentities($content);
	$content = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$content);
	$content = preg_split("/\n/", $content);
	$c=0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head><?php do_action('admin_head'); ?></head>
<body onload="init();" style="margin:0; padding:0;font-family:monospace;font-size:13px;">
	<table id="php_source" cellspacing="0" width="100%" style="padding:0; margin:0;">
<?php	
	$open = 0;
	$closed = 0;
	foreach($content as $line) {
		$c++;
		$style = $c % 2 == 1 ? "#fff" : "#eee";
		$open += substr_count($line,"\1");
		$closed += substr_count($line,"\2");
		$contained = preg_match("/(\1|\2)/", $line) || ($c == $match_line) || ($open != $closed);
		if ($contained) $style="#FFEF3F";
		
		if (!preg_match("/(\01|\02)/", $line) && $contained) $line = "<span style='background-color:#f00; color:#fff;padding:0 3px;'>".$line."</span>";
		if((substr_count($line,"\1") < substr_count($line,"\2")) && ($open == $closed)) $line = "<span style='background-color:#f00; color:#fff;padding:0 3px;'>".$line;
		if(substr_count($line,"\1") > substr_count($line,"\2")) $line .= "</span>";
		$line = str_replace("\1", "<span style='background-color:#f00; color:#fff;padding:0 3px;'>", $line);
		$line = str_replace("\2", "</span>", $line);
		
		echo "<tr id=\"l-$c\" style=\"background-color:$style;\"><td align=\"right\" style=\"background-color:#888;padding-right:5px;\">$c</td><td nowrap=\"nowrap\" style=\"padding-left:5px;\">$line</td></tr>\n";
	}
?>
	</table>
	<script type="text/javascript">
	/* <![CDATA[ */
	function init() {
		try{
			window.scrollTo(0,document.getElementById('l-'+<?php echo max($match_line-15,1); ?>).offsetTop);
		}catch(e) {
			//silently kill errors if *.po files line numbers comes out of an outdated file and exceed the line range
		}
	}
	/* ]]> */
	</script>	
</body>
</html>
<?php
	exit();
}

function bp_translate_po_ajax_handle_create() {
	bp_translate_po_check_security();
	load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
	require_once('bp-translate-l10n.php');
	require_once('bp-translate-classes.php');
	$pofile = new CspTranslationFile();
	$filename = $_POST['path'].$_POST['subpath'].$_POST['language'].'.po';
	if(!$pofile->create_pofile(
		$filename, 
		$_POST['subpath'],
		$_POST['name'], 
		$_POST['timestamp'], 
		$_POST['translator'], 
		$bp_translate_l10n_plurals[substr($_POST['language'],0,2)], 
		$bp_translate_l10n_sys_locales[$_POST['language']]['lang'], 
		$bp_translate_l10n_sys_locales[$_POST['language']]['country'])
	) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo sprintf(__("You do not have the permission to create the file '%s'.", BP_TRANSLATE_PO_TEXTDOMAIN), $filename);
	}
	else{	
		header('Content-Type: application/json');
?>
{
		name: '<?php echo rawurldecode($_POST['name']); ?>',
		row : '<?php echo $_POST['row']; ?>',
		head: '<?php echo sprintf(_n('<strong>%d</strong> Language', '<strong>%d</strong> Languages',$_POST['numlangs'],BP_TRANSLATE_PO_TEXTDOMAIN), $_POST['numlangs']); ?>',
		path: '<?php echo $_POST['path']; ?>',
		subpath: '<?php echo $_POST['subpath']; ?>',
		language: '<?php echo $_POST['language']; ?>',
		native: '<?php echo $bp_translate_l10n_sys_locales[$_POST['language']]['lang-native']; ?>',
		image: '<?php echo BP_TRANSLATE_PO_BASE_URL."/includes/images/flags/".$bp_translate_l10n_sys_locales[$_POST['language']]['country-www'].".png";?>',
		type: '<?php echo $_POST['type']; ?>',
		permissions: '<?php echo date(__('m/d/Y H:i:s',BP_TRANSLATE_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename); ?>'
}
<?php		
	}
	exit();
}

function bp_translate_po_ajax_handle_destroy() {
	bp_translate_po_check_security();
	load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
	$pofile = $_POST['path'].$_POST['subpath'].$_POST['language'].'.po';
	$mofile = $_POST['path'].$_POST['subpath'].$_POST['language'].'.mo';
	$error = false;
	if (file_exists($pofile)) if (!@unlink($pofile)) $error = sprintf(__("You do not have the permission to delete the file '%s'.", BP_TRANSLATE_PO_TEXTDOMAIN), $pofile);
	if (file_exists($mofile)) if (!@unlink($mofile)) $error = sprintf(__("You do not have the permission to delete the file '%s'.", BP_TRANSLATE_PO_TEXTDOMAIN), $mofile);
	if ($error) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo $error;
		exit();
	}
	$num = $_POST['numlangs'] - 1;
	header('Content-Type: application/json');
?>
{
	row : '<?php echo $_POST['row']; ?>',
	head: '<?php echo sprintf(_n('<strong>%d</strong> Language', '<strong>%d</strong> Languages',$num,BP_TRANSLATE_PO_TEXTDOMAIN), $num); ?>',
	language: '<?php echo $_POST['language']; ?>'
}
<?php	
	exit();
}
function bp_translate_po_ajax_handle_scan_source_file() {
	bp_translate_po_check_security();
	require_once('bp-translate-classes.php');
	$textdomain = $_POST['textdomain'];
	//TODO: give the domain into translation file as default domain
	$pofile = new CspTranslationFile();
	//BUGFIX: 1.90 - may be, we have only the mo but no po, so we dump it out as base po file first
	if (!file_exists($_POST['pofile'])) {
		//try implicite convert first and reopen as po second
		if($pofile->read_mofile(substr($_POST['pofile'],0,-2)."mo", $bp_translate_l10n_plurals)) {
			$pofile->write_pofile($_POST['pofile']);
		}
	}		
	if ($pofile->read_pofile($_POST['pofile'])) {
		if ((int)$_POST['num'] == 0) { $pofile->parsing_init(); }
		
		$php_files = explode("|", $_POST['php']);
		$s = (int)$_POST['num'];
		$e = min($s + (int)$_POST['cnt'], count($php_files));
		$last = ($e >= count($php_files));
		for ($i=$s; $i<$e; $i++) {
			$pofile->parsing_add_messages($_POST['path'], $php_files[$i], $textdomain);
		}	
		if ($last) { $pofile->parsing_finalize($textdomain); }
		if ($pofile->write_pofile($_POST['pofile'], $last)) {
			header('Content-Type: application/json');
			echo '{ title: "'.date(__('m/d/Y H:i:s',BP_TRANSLATE_PO_TEXTDOMAIN), filemtime($_POST['pofile']))." ".file_permissions($_POST['pofile']).'" }';
		}
		else{
			header('Status: 404 Not Found');
			header('HTTP/1.1 404 Not Found');
			echo sprintf(__("You do not have the permission to write the file '%s'.", BP_TRANSLATE_PO_TEXTDOMAIN), $_POST['pofile']);
		}
	}
	else{
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo sprintf(__("You do not have the permission to read the file '%s'.", BP_TRANSLATE_PO_TEXTDOMAIN), $_POST['pofile']);
	}
	exit();
}

function bp_translate_po_ajax_handle_change_permission() {
	bp_translate_po_check_security();
	load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
	$filename = $_POST['file'];
	$error = false;
	if (file_exists($filename)) {
		@chmod($filename, 0644);
		if(!is_writable($filename)) {
			@chmod($filename, 0664);
			if (!is_writable($filename)) {
				@chmod($filename, 0666);
			}
			if (!is_writable($filename)) $error = __('Server Restrictions: Changing file rights is not permitted.', BP_TRANSLATE_PO_TEXTDOMAIN);
		}
	}
	else $error = sprintf(__("You do not have the permission to modify the file rights for a not existing file '%s'.", BP_TRANSLATE_PO_TEXTDOMAIN), $filename);
	if ($error) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo $error;			
	}
	else{
		header('Content-Type: application/json');
		echo '{ title: "'.date(__('m/d/Y H:i:s',BP_TRANSLATE_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename).'" }';
	}
	exit();
}

function bp_translate_po_ajax_handle_launch_editor() {
	bp_translate_po_check_security();
	load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
	require_once('bp-translate-l10n.php');
	require_once('bp-translate-classes.php');
	$f = new CspTranslationFile();
	if (!file_exists($_POST['basepath'].$_POST['file'])) {
		//try implicite convert first
		if($f->read_mofile(substr($_POST['basepath'].$_POST['file'],0,-2)."mo", $bp_translate_l10n_plurals, $_POST['file'])) {
			$f->write_pofile($_POST['basepath'].$_POST['file']);
		}
	}
	else{
		$f->read_pofile($_POST['basepath'].$_POST['file'], $bp_translate_l10n_plurals, $_POST['file']);
	}
	if ($f->supports_textdomain_extension()){
		$f->echo_as_json($_POST['basepath'], $_POST['file'], $bp_translate_l10n_sys_locales);
	}else {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		_e("Your translation file doesn't support the required textdomain extension.<br/>Please re-scan the related source files to enable this feature.",BP_TRANSLATE_PO_TEXTDOMAIN);
	}
	exit();
}

function bp_translate_po_ajax_handle_translate_by_google() {
	bp_translate_po_check_security();
	// reference documentation: http://code.google.com/intl/de-DE/apis/ajaxlanguage/documentation/reference.html
	// example 'http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q=hello%20world&langpair=en%7Cit'
	$msgid = $_POST['msgid'];
	$search = array('\\\\\\\"', '\\\\\"','\\\\n', '\\\\r', '\\\\t', '\\\\$','\\0', "\\'", '\\\\');
	$replace = array('\"', '"', "\n", "\r", "\\t", "\\$", "\0", "'", "\\");
	$msgid = str_replace( $search, $replace, $msgid );
	$res = bp_translate_fetch_remote_content("http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&format=html&q=".urlencode($msgid)."&langpair=en%7C".$_POST['destlang']);
	if ($res) {
		header('Content-Type: application/json');
		echo $res;
	}
	else{
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
		_e("Sorry, Google Translation is not available.", BP_TRANSLATE_PO_TEXTDOMAIN);		
	}
	exit();
}

function bp_translate_po_ajax_handle_save_catalog_entry() {
	bp_translate_po_check_security();
	load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
	require_once('bp-translate-classes.php');
	$f = new CspTranslationFile();
	//opera bugfix: replace embedded \1 with \0 because Opera can't send embeded 0
	$_POST['msgid'] = str_replace("\1", "\0", $_POST['msgid']);
	$_POST['msgstr'] = str_replace("\1", "\0", $_POST['msgstr']);
	if ($f->read_pofile($_POST['path'].$_POST['file'])) {
		if (!$f->update_entry($_POST['msgid'], $_POST['msgstr'])) {
			header('Status: 404 Not Found');
			header('HTTP/1.1 404 Not Found');
			echo sprintf(__("You do not have the permission to write the file '%s'.", BP_TRANSLATE_PO_TEXTDOMAIN), $_POST['file']);
		}
		else{
			$f->write_pofile($_POST['path'].$_POST['file']);
			header('Status: 200 Ok');
			header('HTTP/1.1 200 Ok');
			header('Content-Length: 1');	
			echo "0";
		}
	}
	else{
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo sprintf(__("You do not have the permission to read the file '%s'.", BP_TRANSLATE_PO_TEXTDOMAIN), $_POST['file']);
	}
	exit();
}

function bp_translate_po_ajax_handle_generate_mo_file(){
	bp_translate_po_check_security();
	load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
	require_once('bp-translate-classes.php');
	$pofile = (string)$_POST['pofile'];
	$textdomain = (string)$_POST['textdomain'];
	$f = new CspTranslationFile();
	if (!$f->read_pofile($pofile)) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo sprintf(__("You do not have the permission to read the file '%s'.", BP_TRANSLATE_PO_TEXTDOMAIN), $pofile);
		exit();
	}
	//lets detected, what we are about to be writing:
	$mo = substr($pofile,0,-2).'mo';

	$wp_dir = str_replace("\\","/",WP_LANG_DIR);
	$pl_dir = str_replace("\\","/",WP_PLUGIN_DIR);
	$plm_dir = str_replace("\\","/",WPMU_PLUGIN_DIR);
	$parts = pathinfo($mo);
	//dirname|basename|extension
	if (preg_match("|^".$wp_dir."|", $mo)) {
		//we are WordPress itself
		if (!empty($textdomain)) {
			$mo	= $parts['dirname'].'/'.$textdomain.'-'.$parts['basename'];
		}
	}elseif(preg_match("|^".$pl_dir."|", $mo)|| preg_match("|^".$plm_dir."|", $mo)) {
		//we are a normal or wpmu plugin
		if (strpos($parts['basename'], $textdomain) === false) {
			preg_match("/([a-z][a-z]_[A-Z][A-Z]\.mo)$/", $parts['basename'], $h);
			if (!empty($textdomain)) {
				$mo	= $parts['dirname'].'/'.$textdomain.'-'.$h[1];
			}else {
				$mo	= $parts['dirname'].'/'.$h[1];
			}
		}
	}else{
		//we are a theme plugin, could be tested but skipped for now.
	}
	
	if (!$f->write_mofile($mo,$textdomain)) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo sprintf(__("You do not have the permission to write the file '%s'.", BP_TRANSLATE_PO_TEXTDOMAIN), $mo);
		exit();
	}

	header('Content-Type: application/json');
?>
{
	filetime: '<?php echo date (__('m/d/Y H:i:s',BP_TRANSLATE_PO_TEXTDOMAIN), filemtime($mo)); ?>'
}
<?php		
	exit();
}

function bp_translate_po_ajax_handle_create_language_path() {
	bp_translate_po_check_security();
	load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
	
	if (!mkdir(WP_CONTENT_DIR."/languages")) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		_e("You do not have the permission to create the WordPress Language File Path.<br/>Please create the appropriated path using you FTP access.", BP_TRANSLATE_PO_TEXTDOMAIN);
	}
	else{
			header('Status: 200 ok');
			header('HTTP/1.1 200 ok');
			header('Content-Length: 1');	
			print 0;
	}
	exit();
}

function bp_translate_po_ajax_handle_create_pot_indicator() {
	bp_translate_po_check_security();
	load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
	
	$handle = @fopen($_POST['potfile'], "w");
	
	if ($handle === false) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		_e("You do not have the permission to choose the translation file directory<br/>Please upload at least one language file (*.mo|*.po) or an empty template file (*.pot) at the appropriated folder using FTP.", BP_TRANSLATE_PO_TEXTDOMAIN);
	}
	else{
		@fwrite($handle, 
			'MIME-Version: 1.0\n'.
			'Content-Type: text/plain; charset=UTF-8\n'.
			'Content-Transfer-Encoding: 8bit'
		);
		@fclose($handle);
		header('Status: 200 ok');
		header('HTTP/1.1 200 ok');
		header('Content-Length: 1');	
		print 0;
	}
	exit();
}

//////////////////////////////////////////////////////////////////////////////////////////
//	Admin Initialization ad Page Handler
//////////////////////////////////////////////////////////////////////////////////////////
if (function_exists('add_action')) {
	if (is_admin() && !defined('DOING_AJAX')) {
		add_action('admin_init', 'bp_translate_po_init');
		add_action('admin_head', 'bp_translate_po_admin_head');
		add_action('admin_menu', 'bp_translate_po_admin_menu');
		require_once('bp-translate-l10n.php');
	}
}

function bp_translate_po_init() {
	//currently not used, subject of later extension
}


function bp_translate_load_po_edit_admin_page(){
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_script('prototype');
	wp_enqueue_script('scriptaculous-effects');

	if (function_exists('wp_enqueue_style')) {
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style('codestyling-localization', BP_TRANSLATE_PO_BASE_URL.'/includes/admin/pomo/bp-translate-pomo.php?css=default');
	}
}

function bp_translate_po_admin_head() {
	if (!function_exists('wp_enqueue_style') 
		&& 
		preg_match("/^codestyling\-localization\/codestyling\-localization\.php/", $_GET['page'])
	) {
		print '<link rel="stylesheet" href="'.get_option('siteurl')."/wp-includes/js/thickbox/thickbox.css".'" type="text/css" media="screen"/>';
		print '<link rel="stylesheet" href="'.BP_TRANSLATE_PO_BASE_URL.'/includes/admin/pomo/bp-translate-pomo.php?css=default'.'" type="text/css" media="screen"/>';
	}
}

function bp_translate_po_admin_menu() {
	load_plugin_textdomain(BP_TRANSLATE_PO_TEXTDOMAIN, PLUGINDIR . '/bp-translate/languages/l10n',BP_TRANSLATE_PO_TEXTDOMAIN );
	//$hook = add_submenu_page( 'bp-translate', __('Localization Management', BP_TRANSLATE_PO_TEXTDOMAIN), __('Translations', BP_TRANSLATE_PO_TEXTDOMAIN), 2, __FILE__, 'bp_translate_po_main_page' );
	add_action('load-'.$hook, 'bp_translate_load_po_edit_admin_page' ); //only load the scripts and stylesheets by hook, if this admin page will be shown
}

function bp_translate_po_main_page() {
	bp_translate_po_check_security();
	$mo_list_counter = 0;
	global $bp_translate_l10n_sys_locales, $wp_version;
	$bp_translate_wp_main_page = ( version_compare($wp_version, '2.7 ', '>=') ? "tools" : "edit");
?>
	<div id="bp-translate-wrap-main" class="wrap">
		<div class="icon32" id="icon-tools"><br/></div>
		<h2><?php _e('Manage Language Files', BP_TRANSLATE_PO_TEXTDOMAIN); ?></h2>
		<ul class="subsubsub">
			<li>
				<a<?php if(!isset($_GET['type'])) echo " class=\"current\""; ?> href="<?php echo $bp_translate_wp_main_page ?>.php?page=bp-translate/includes/admin/network/bp-translate-site-admin.php"><?php  _e('All Translations', BP_TRANSLATE_PO_TEXTDOMAIN); ?>
				</a> |
			</li>
			<li>
				<a<?php if(isset($_GET['type']) && $_GET['type'] == 'wordpress') echo " class=\"current\""; ?> href="<?php echo $bp_translate_wp_main_page ?>.php?page=bp-translate/includes/admin/network/bp-translate-site-admin.php&amp;type=wordpress"><?php _e('WordPress', BP_TRANSLATE_PO_TEXTDOMAIN); ?>
				</a> |
			</li>
<?php if ( isset( $GLOBALS['wpmu_version'] ) ) { ?>
			<li>
				<a<?php if(isset($_GET['type']) && $_GET['type'] == 'plugins_mu') echo " class=\"current\""; ?> href="<?php echo $bp_translate_wp_main_page ?>.php?page=bp-translate/includes/admin/network/bp-translate-site-admin.php&amp;type=plugins_mu"><?php _e('μ Plugins', BP_TRANSLATE_PO_TEXTDOMAIN); ?>
				</a> |
			</li>
<?php } ?>
			<li>
				<a<?php if(isset($_GET['type']) && $_GET['type'] == 'plugins') echo " class=\"current\""; ?> href="<?php echo $bp_translate_wp_main_page ?>.php?page=bp-translate/includes/admin/network/bp-translate-site-admin.php&amp;type=plugins"><?php _e('Plugins', BP_TRANSLATE_PO_TEXTDOMAIN); ?>
				</a> |
			</li>
			<li>
				<a<?php if(isset($_GET['type']) && $_GET['type'] == 'themes') echo " class=\"current\""; ?> href="<?php echo $bp_translate_wp_main_page ?>.php?page=bp-translate/includes/admin/network/bp-translate-site-admin.php&amp;type=themes"><?php _e('Themes', BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>
			</li>
		</ul>
		<table class="widefat clear" style="cursor:default;" cellspacing="0">
			<thead>
				<tr>
					<th scope="col"><?php _e('Type',BP_TRANSLATE_PO_TEXTDOMAIN); ?></th>
					<th scope="col"><?php _e('Description',BP_TRANSLATE_PO_TEXTDOMAIN); ?></th>
					<th scope="col"><?php _e('Languages',BP_TRANSLATE_PO_TEXTDOMAIN); ?></th>
				</tr>
			</thead>
			<tbody class="list" id="the-gettext-list">
<?php $rows = bp_translate_po_collect_by_type(isset($_GET['type']) ? $_GET['type'] : ''); foreach($rows as $data) : ?>
<?php if ( !$data['is-path-unclear']) : ?>
				<tr<?php if ($data['status'] == __("activated",BP_TRANSLATE_PO_TEXTDOMAIN)) : echo " class=\"bp-translate-active\""; else : echo " class=\"bp-translate-inactive\""; endif; ?>>
					<td align="center"><img alt="" src="<?php echo BP_TRANSLATE_PO_BASE_URL . "/includes/images/l10n/" . $data['img_type'] . ".gif"; ?>" /><div><strong><?php echo $data['type-desc']; ?></strong></div></td>
					<td>
						<h3 class="bp-translate-type-name"><?php echo $data['name']; ?><span style="font-weight:normal;">&nbsp;&nbsp;&copy;&nbsp;</span><?php echo $data['author']; ?></h3>
						<table class="bp-translate-type-info" border="0" width="100%">
							<tr>
								<td width="140px"><strong><?php _e('Textdomain',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
								<td class="bp-translate-info-value"><?php echo $data['textdomain']['identifier']; ?><?php if ($data['textdomain']['is_const']) echo " (".__('defined by constant',BP_TRANSLATE_PO_TEXTDOMAIN).")"; ?></td>
							</tr>
							<tr>
								<td><strong><?php _e('Version',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
								<td class="bp-translate-info-value"><?php echo $data['version']; ?></td>
							</tr>
							<tr>
								<td><strong><?php _e('State',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
								<td class="bp-translate-info-value"><?php echo $data['status']; ?></td>
							</tr>
							<tr>
								<td><strong><?php _e('Description',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
								<td class="bp-translate-info-value"><?php echo $data['description'];?></td>
							</tr>
							<!--
							<tr>
								<td colspan="2" align="center" style="padding-top: 10px;color: #f00;"><?php _e('<strong>ATTENTION</strong>: The path of translation files is ambiguous, please select the language file folder!',BP_TRANSLATE_PO_TEXTDOMAIN) ?></td>
							</tr>
							-->
						</table>
<?php	if ( isset( $data['child-plugins'] ) ) {
			foreach( $data['child-plugins'] as $child ) { ?>
						<div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ccc;">
							<h3 class="bp-translate-type-name"><?php echo $child['name']; ?> <small><em><?php _e('by',BP_TRANSLATE_PO_TEXTDOMAIN); ?> <?php echo $child['author']; ?></em></small></h3>
							<table class="bp-translate-type-info" border="0">
								<tr>
									<td><strong><?php _e('Version',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
									<td width="100%" class="bp-translate-info-value"><?php echo $child['version']; ?></td>
								</tr>
								<tr>
									<td><strong><?php _e('State',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
									<td class="bp-translate-info-value"><?php echo $child['status']; ?></td>
								</tr>
								<tr>
									<td><strong><?php _e('Description',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</strong></td>
									<td class="bp-translate-info-value"><?php echo $child['description'];?></td>
								</tr>
							</table>
						</div>
<?php		}
		}
?>
					</td>
					<td>
<?php  if ( $data['type'] == 'wordpress' && $data['is_US_Version'] ) { ?>
							<div style="color:#f00;"><?php _e("The original US version doesn't contain the language directory.",BP_TRANSLATE_PO_TEXTDOMAIN); ?></div>
							<br/>
							<div><a class="clickable button" onclick="bp_translate_create_languange_path(this);"><?php _e('try to create the WordPress language directory',BP_TRANSLATE_PO_TEXTDOMAIN); ?></a></div>
							<br/>
							<div>
								<?php _e('or create the missing directory using FTP Access as:',BP_TRANSLATE_PO_TEXTDOMAIN); ?>
								<br/><br/>
								<?php echo WP_CONTENT_DIR."/"; ?><strong style="color:#f00;">languages</strong>
							</div>
<?php } elseif( $data['is-path-unclear'] ) { ?>
							<strong style="border-bottom: 1px solid #ccc;"><?php _e('Available Directories:',BP_TRANSLATE_PO_TEXTDOMAIN) ?></strong><br/><br/>
							<?php
								$tmp = array();
								$dirs = rscanpath($data['base_path'], $tmp);
								$dir = $data['base_path'];
								echo '<a class="clickable pot-folder" onclick="bp_translate_create_pot_indicator(this,\''.$dir.$data['base_file'].'xx_XX.pot\');">'. str_replace(str_replace("\\","/",WP_PLUGIN_DIR), '', $dir)."</a><br/>";
								foreach($dirs as $dir) {
									echo '<a class="clickable pot-folder" onclick="bp_translate_create_pot_indicator(this,\''.$dir.'/'.$data['base_file'].'xx_XX.pot\');">'. str_replace(str_replace("\\","/",WP_PLUGIN_DIR), '', $dir)."</a><br/>";
								}
							?>
<?php } else { ?>
						<table width="100%" cellspacing="0" class="widefat mo-list" id="mo-list-<?php echo ++$mo_list_counter; ?>" summary="<?php echo $data['textdomain']['identifier'].'|'.$data['type']; ?>">
							<thead>
								<tr class="mo-list-desc">
									<th nowrap="nowrap" align="center"><?php _e( 'Language', BP_TRANSLATE_PO_TEXTDOMAIN ); ?></th>
									<th nowrap="nowrap" align="center"><?php _e( 'Permissions', BP_TRANSLATE_PO_TEXTDOMAIN ); ?></th>
									<th nowrap="nowrap" align="center"><?php _e( 'Actions', BP_TRANSLATE_PO_TEXTDOMAIN ); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr class="mo-list-head">
									<td colspan="2" nowrap="nowrap">
										<a rel="<?php echo implode( '|', array_keys( $data['languages'] ) ) ;?>" class="button clickable mofile" onclick="bp_translate_add_language(this,'<?php echo $data['type']; ?>', '<?php echo rawurlencode( $data['name'] ) ?> v <?php echo $data['version'] ?>', 'mo-list-<?php echo $mo_list_counter ?>', '<?php echo $data['base_path'] ?>', '<?php echo $data['base_file'] ?>', this.rel, '<?php echo $data['type'] ?>');"><?php _e( "Add New Language", BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>
									</td>
									<td nowrap="nowrap" class="bp-translate-ta-right"><?php echo sprintf(_n('<strong>%d</strong> Language', '<strong>%d</strong> Languages',count($data['languages']),BP_TRANSLATE_PO_TEXTDOMAIN), count($data['languages'])); ?></td>
								</tr>
							<?php
								foreach($data['languages'] as $lang => $gtf) :
									$country_www = isset($bp_translate_l10n_sys_locales[$lang]) ? $bp_translate_l10n_sys_locales[$lang]['country-www'] : 'unknown';
									$lang_native = isset($bp_translate_l10n_sys_locales[$lang]) ? $bp_translate_l10n_sys_locales[$lang]['lang-native'] : '<em>locale: </em>'.$lang;
							?>
								<tr class="mo-file" lang="<?php echo $lang; ?>">
									<td nowrap="nowrap" width="100%"><img title="<?php _e('Locale',BP_TRANSLATE_PO_TEXTDOMAIN); ?>: <?php echo $lang ?>" alt="(locale: <?php echo $lang; ?>)" src="<?php echo BP_TRANSLATE_PO_BASE_URL."/includes/images/flags/".$country_www.".png"; ?>" /><?php if (get_locale() == $lang) echo "<strong>"; ?>&nbsp;<?php echo $lang_native; ?><?php if (get_locale() == $lang) echo "</strong>"; ?></td>
									<td nowrap="nowrap" align="center">
										<div style="width:44px">
											<?php if (array_key_exists('po', $gtf)) {
												echo "<a class=\"bp-translate-filetype-po".$gtf['po']['class']."\" title=\"".$gtf['po']['stamp'].($gtf['po']['class'] == '-r' ? '" onclick="bp_translate_make_writable(this,\''.$data['base_path'].$data['base_file'].$lang.".po".'\',\'bp-translate-filetype-po-rw\');' : '')."\">&nbsp;</a>";
											} else { ?>
											<a class="bp-translate-filetype-po" title="<?php _e('-n.a.-',BP_TRANSLATE_PO_TEXTDOMAIN); ?> [---|---|---]">&nbsp;</a>
											<?php } ?>
											<?php if (array_key_exists('mo', $gtf)) {
												echo "<a class=\"bp-translate-filetype-mo".$gtf['mo']['class']."\" title=\"".$gtf['mo']['stamp'].($gtf['mo']['class'] == '-r' ? '" onclick="bp_translate_make_writable(this,\''.$data['base_path'].$data['base_file'].$lang.".mo".'\',\'bp-translate-filetype-mo-rw\');' : '')."\">&nbsp;</a>";
											} else { ?>
											<a class="bp-translate-filetype-mo" title="<?php _e('-n.a.-',BP_TRANSLATE_PO_TEXTDOMAIN); ?> [---|---|---]">&nbsp;</a>
											<?php } ?>
										</div>
									</td>
									<td nowrap="nowrap" style="padding-right: 5px;">
										<a class="clickable" onclick="bp_translate_launch_editor(this, '<?php echo $data['base_file'].$lang.".po" ;?>', '<?php echo $data['base_path']; ?>');"><?php _e('Edit',BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>
										<span> | </span>
										<a class="clickable" onclick="bp_translate_rescan_language(this, '<?php echo rawurlencode( $data['name'] ) . " v" . $data['version']; ?>', '<?php echo "mo-list-" . $mo_list_counter; ?>', '<?php echo $data['base_path']; ?>', '<?php echo $data['base_file']; ?>', '<?php echo $lang; ?>', '<?php echo $data['type']; ?>');"><?php _e('Rescan',BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>
										<span> | </span>
										<a class="clickable" onclick="bp_translate_remove_language(this,'<?php echo rawurlencode($data['name']) . " v" . $data['version']; ?>', '<?php echo 'mo-list-' . $mo_list_counter; ?>', '<?php echo $data['base_path']; ?>', '<?php echo $data['base_file']; ?>', '<?php echo $lang; ?>');"><?php _e('Delete',BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
<?php } ?>
					</td>
				</tr>
<?php endif; ?>
<?php endforeach; ?>
			</tbody>
		</table>
	</div><!-- bp-translate-wrap-main closed -->
<div id="bp-translate-wrap-editor" class="wrap" style="display:none">
	<div class="icon32" id="icon-tools"><br/></div>
	<h2><?php _e('Translate Language File', BP_TRANSLATE_PO_TEXTDOMAIN); ?>&nbsp;&nbsp;&nbsp;<a class="clickable button" onclick="window.location.reload()"><?php _e('&larr; back to main page', BP_TRANSLATE_PO_TEXTDOMAIN) ?></a></h2>
	<div id="bp-translate-json-header">
		<div class="po-header-toggle"><strong><?php _e('File:', BP_TRANSLATE_PO_TEXTDOMAIN); ?></strong> <a onclick="bp_translate_toggle_header(this,'po-hdr');"><?php _e('unknown', BP_TRANSLATE_PO_TEXTDOMAIN); ?></a></div>
	</div>
	<div class="action-bar">
		<p>
			<small>
			<?php _e('<b>Hint:</b> The extended feature for textdomain separation shows at dropdown box <i>Textdomain</i> the pre-selected primary textdomain.',BP_TRANSLATE_PO_TEXTDOMAIN); ?><br/>
			<?php _e('All other additional contained textdomains occur at the source but will not be used, if not explicitely supported by this component!',BP_TRANSLATE_PO_TEXTDOMAIN); ?><br/>
			<?php _e('Please contact the author, if some of the non primary textdomain based phrases will not show up translated at the required position!',BP_TRANSLATE_PO_TEXTDOMAIN); ?><br/>
			<?php _e('The Textdomain <i><b>default</b></i> always stands for the WordPress main language file, this could be either intentionally or accidentally!',BP_TRANSLATE_PO_TEXTDOMAIN); ?><br/>
			</small>
		</p>
		<div class="alignleft"id="bp-translate-mo-textdomain"><span><b><?php _e('Textdomain:',BP_TRANSLATE_PO_TEXTDOMAIN); ?></b></span>&nbsp;&nbsp;<select id="bp-translate-mo-textdomain-val" onchange="bp_translate_change_textdomain_view(this.value);"></select></div>
		<div class="alignleft">&nbsp;&nbsp;<input id="bp-translate-write-mo-file" class="button button-secondary" type="submit" value="<?php _e('generate mo-file', BP_TRANSLATE_PO_TEXTDOMAIN); ?>" onclick="bp_translate_generate_mofile(this);" /></div>
		<div class="alignleft" style="margin-left:10px;font-size:11px;padding-top:3px;"><?php _e('last written:',BP_TRANSLATE_PO_TEXTDOMAIN);?>&nbsp;&nbsp;<span id="catalog-last-saved" ><?php _e('unknown',BP_TRANSLATE_PO_TEXTDOMAIN); ?></span></div>
		<br class="clear" />
	</div>
	<ul class="subsubsub">
		<li><a id="bp-translate-filter-all" class="bp-translate-filter current" onclick="bp_translate_filter_result(this, bp_translate_idx.total)"><?php _e('Total', BP_TRANSLATE_PO_TEXTDOMAIN); ?> ( <span class="bp-translate-flt-cnt">0</span> )</a> | </li>
		<li><a id="bp-translate-filter-plurals" class="bp-translate-filter" onclick="bp_translate_filter_result(this, bp_translate_idx.plurals)"><?php _e('Plural', BP_TRANSLATE_PO_TEXTDOMAIN); ?> ( <span class="bp-translate-flt-cnt">0</span> )</a> | </li>
		<li><a id="bp-translate-filter-ctx" class="bp-translate-filter" onclick="bp_translate_filter_result(this, bp_translate_idx.ctx)"><?php _e('Context', BP_TRANSLATE_PO_TEXTDOMAIN); ?> ( <span class="bp-translate-flt-cnt">0</span> )</a> | </li>
		<li><a id="bp-translate-filter-open" class="bp-translate-filter" onclick="bp_translate_filter_result(this, bp_translate_idx.open)"><?php _e('Not translated', BP_TRANSLATE_PO_TEXTDOMAIN); ?> ( <span class="bp-translate-flt-cnt">0</span> )</a> | </li>
		<li><a id="bp-translate-filter-rem" class="bp-translate-filter" onclick="bp_translate_filter_result(this, bp_translate_idx.rem)"><?php _e('Comments', BP_TRANSLATE_PO_TEXTDOMAIN); ?> ( <span class="bp-translate-flt-cnt">0</span> )</a> | </li>
		<li><a id="bp-translate-filter-code" class="bp-translate-filter" onclick="bp_translate_filter_result(this, bp_translate_idx.code)"><?php _e('Code Hint', BP_TRANSLATE_PO_TEXTDOMAIN); ?> ( <span class="bp-translate-flt-cnt">0</span> )</a></li>
		<li style="display:none;"> | <span id="bp-translate-filter-search" class="current"><?php _e('Search Result', BP_TRANSLATE_PO_TEXTDOMAIN); ?>  ( <span class="bp-translate-flt-cnt">0</span> )</span></li>
		<li style="display:none;"> | <span id="bp-translate-filter-regexp" class="current"><?php _e('Expression Result', BP_TRANSLATE_PO_TEXTDOMAIN); ?>  ( <span class="bp-translate-flt-cnt">0</span> )</span></li>
	</ul>
	<div class="tablenav">
		<div class="alignleft">
			<div class="alignleft" style="padding-top: 5px;font-size:11px;"><strong><?php _e('Page Size', BP_TRANSLATE_PO_TEXTDOMAIN); ?>:&nbsp;</strong></div>
			<select id="catalog-pagesize" name="catalog-pagesize" onchange="bp_translate_change_pagesize(this.value);" class="alignleft" style="font-size:11px;" autocomplete="off">
				<option value="10">10</option>
				<option value="25">25</option>
				<option value="50">50</option>
				<option value="75">75</option>
				<option value="100" selected="selected">100</option>
				<option value="150">150</option>
				<option value="200">200</option>
			</select>
		</div>
		<div id="catalog-pages-top" class="tablenav-pages alignright">
			<a href="#" class="prev page-numbers"><?php _e('&laquo; Previous', BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>
			<a href="#" class="page-numbers">1</a>
			<a href="#" class="page-numbers">2</a>
			<a href="#" class="page-numbers">3</a>
			<span class="page-numbers current">4</span>
			<a href="#" class="next page-numbers"><?php _e('Next &raquo;', BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>
		</div>
		<br class="clear" />
	</div>
	<br class="clear" />
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th nowrap="nowrap"><span><?php _e('Infos',BP_TRANSLATE_PO_TEXTDOMAIN); ?></span></th>
				<th width="50%">
					<table>
						<tr>
							<th style="background:transparent;border-bottom:0px;padding:0px;"><?php _e('Original:',BP_TRANSLATE_PO_TEXTDOMAIN); ?></th>
							<th style="background:transparent;border-bottom:0px;padding:0px;vertical-align:top;">
								<input id="s_original" name="s_original" type="text" size="16" value="" onkeyup="bp_translate_search_result(this)" style="margin-bottom:3px;" autocomplete="off" />
								<br/>
								<input id="ignorecase_key" name="ignorecase_key" type="checkbox" value="checked" onclick="bp_translate_search_key('s_original')" /><label for="ignorecase_key" style="font-weight:normal;margin-top:-2px;"> <?php _e('non case-sensitive', BP_TRANSLATE_PO_TEXTDOMAIN) ?></label>
							</th>
							<th style="background:transparent;border-bottom:0px;padding:0px;vertical-align:top;">
								<a class="clickable regexp" onclick="bp_translate_search_regexp('s_original')"></a>
							</th>
						</tr>
					</table>
				</th>
				<th width="50%">
					<table>
						<tr>
							<th style="background:transparent;border-bottom:0px;padding:0px;"><?php _e('Translation:',BP_TRANSLATE_PO_TEXTDOMAIN); ?></th>
							<th style="background:transparent;border-bottom:0px;padding:0px;vertical-align:top;">
								<input id="s_translation" name="s_translation" type="text" size="16" value="" onkeyup="bp_translate_search_result(this)" style="margin-bottom:3px;" autocomplete="off" />
								<br/>
								<input id="ignorecase_val" name="ignorecase_val" type="checkbox" value="checked" onclick="bp_translate_search_val('s_translation')" /><label for="ignorecase_val" style="font-weight:normal;margin-top:-2px;"> <?php _e('non case-sensitive', BP_TRANSLATE_PO_TEXTDOMAIN) ?></label>
							</th>
							<th style="background:transparent;border-bottom:0px;padding:0px;vertical-align:top;">
								<a class="clickable regexp" onclick="bp_translate_search_regexp('s_translation')"></a>
							</th>
						</tr>
					</table>
				</th>
				<th nowrap="nowrap"><span><?php _e('Actions',BP_TRANSLATE_PO_TEXTDOMAIN); ?></span></th>
			</tr>
		</thead>
		<tbody id="catalog-body">
			<tr><td colspan="4" align="center"><img alt="" src="<?php echo BP_TRANSLATE_PO_BASE_URL."/includes/images/l10n/loading.gif"?>" /><br /><span style="color:#328AB2;"><?php _e('Please wait, file content presently being loaded ...',BP_TRANSLATE_PO_TEXTDOMAIN); ?></span></td></tr>
		</tbody>
	</table>	
	<div class="tablenav">
		<a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);" style="margin:3px 0 0 30px;"><?php _e('scroll to top', BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>
		<div id="catalog-pages-bottom" class="tablenav-pages">
			<a href="#" class="prev page-numbers"><?php _e('&laquo; Previous', BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>
			<a href="#" class="page-numbers">1</a>
			<a href="#" class="page-numbers">2</a>
			<a href="#" class="page-numbers">3</a>
			<span class="page-numbers current">4</span>
			<a href="#" class="next page-numbers"><?php _e('Next &raquo;', BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>
		</div>
		<br class="clear" />
	</div>
	<br class="clear" />
</div><!-- bp-translate-wrap-editor closed -->
<div id="bp-translate-dialog-container" style="display:none;">
	<div>
		<h3 id="bp-translate-dialog-header">
			<img alt="" id="bp-translate-dialog-icon" class="alignleft" src="<?php echo BP_TRANSLATE_PO_BASE_URL; ?>/includes/images/l10n/gettext.gif" />
			<span id="bp-translate-dialog-caption" class="alignleft"><?php _e('Edit Catalog Entry',BP_TRANSLATE_PO_TEXTDOMAIN); ?></span>
			<img alt="" id="bp-translate-dialog-cancel" class="alignright clickable" title="<?php _e('close', BP_TRANSLATE_PO_TEXTDOMAIN); ?>" src="<?php echo BP_TRANSLATE_PO_BASE_URL."/includes/images/l10n/close.gif"; ?>" onclick="bp_translate_cancel_dialog();" />
			<br class="clear" />
		</h3>	
		<div id="bp-translate-dialog-body"></div>
		<div style="text-align:center;"><img id="bp-translate-dialog-saving" src="<?php echo BP_TRANSLATE_PO_BASE_URL; ?>/includes/images/l10n/saving.gif" style="margin-top:20%;display:none;" /></div>
	</div>
</div><!-- bp-translate-dialog-container closed -->
<br />
<script type="text/javascript">
/* <![CDATA[ */
Object.extend(Array.prototype, {
  intersect: function(array){
    return this.findAll( function(token){ return array.include(token) } );
  }
});

//--- management based functions ---
function bp_translate_make_writable(elem, file, success_class) {
	elem = $(elem);
	elem.blur();
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_change_permission',
				file: file
			},
			onSuccess: function(transport) {		
				elem.className=success_class;
				elem.title=transport.responseJSON.title;
				elem.onclick = null;
			},
			onFailure: function(transport) {
				bp_translate_show_error(transport.responseText);
			}
		}
	);
	return false;	
}

function bp_translate_add_language(elem, type, name, row, path, subpath, existing, type) {
	elem = $(elem);
	elem.blur();
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_dlg_new',
				type: type,
				name: name,
				row: row,
				path: path,
				subpath: subpath,
				existing: existing,
				type: type
			},
			onSuccess: function(transport) {
				$('bp-translate-dialog-caption').update("<?php _e('Add New Language',BP_TRANSLATE_PO_TEXTDOMAIN); ?>");
				$("bp-translate-dialog-body").update(transport.responseText).setStyle({'padding' : '10px'});
				tb_show.defer(null,"#TB_inline?height=530&width=500&inlineId=bp-translate-dialog-container&modal=true",false);
			}
		}
	); 	
	return false;
}

function bp_translate_create_new_pofile(elem, type){
	elem = $(elem);
	elem.blur();
	
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_create',
				name: $('bp-translate-dialog-name').value,
				timestamp: $('bp-translate-dialog-timestamp').value,
				translator: $('bp-translate-dialog-translator').value,
				path: $('bp-translate-dialog-path').value,
				subpath: $('bp-translate-dialog-subpath').value,
				language: $('bp-translate-dialog-language').value,
				row : $('bp-translate-dialog-row').value,
				numlangs: $('bp-translate-dialog-numlangs').value,
				type: type
			},
			onSuccess: function(transport) {	
				$$('#'+transport.responseJSON.row+' .mo-list-head').first().down(3).update(transport.responseJSON.head);
				rel = $$('#'+transport.responseJSON.row+' .mo-list-head').first().down(2).rel;
				$$('#'+transport.responseJSON.row+' .mo-list-head').first().down(2).rel += ((rel.empty() ? '' : "|" ) + transport.responseJSON.language);
				elem_after = null;
								
				content = "<tr class=\"mo-file\" lang=\""+transport.responseJSON.language+"\">"+
					"<td nowrap=\"nowrap\" width=\"100%\">"+
						"<img title=\"<?php _e('Locale',BP_TRANSLATE_PO_TEXTDOMAIN); ?>: "+transport.responseJSON.language+"\" alt=\"(locale: "+transport.responseJSON.language+")\" src=\""+transport.responseJSON.image+"\" />" +
						("<?php echo get_locale(); ?>" == transport.responseJSON.language ? "<strong>" : "") + 
						"&nbsp;" + transport.responseJSON.native +
						("<?php echo get_locale(); ?>" == transport.responseJSON.language ? "</strong>" : "") + 
					"</td>"+
					"<td align=\"center\">"+
						"<div style=\"width:44px\">"+
						"<a class=\"bp-translate-filetype-po-rw\" title=\""+transport.responseJSON.permissions+"\">&nbsp;</a>"+
						"<a class=\"bp-translate-filetype-mo\" title=\"<?php _e('-n.a.-',BP_TRANSLATE_PO_TEXTDOMAIN); ?> [---|---|---]\">&nbsp;</a>"+
						"</div>"+
					"</td>"+
					"<td nowrap=\"nowrap\">"+
						"<a class=\"clickable\" onclick=\"bp_translate_launch_editor(this, '"+transport.responseJSON.subpath+transport.responseJSON.language+".po"+"', '"+transport.responseJSON.path+"');\"><?php _e('Edit',BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>"+
						"<span> | </span>"+
						"<a class=\"clickable\" onclick=\"bp_translate_rescan_language(this,'"+escape(transport.responseJSON.name)+"','"+transport.responseJSON.row+"','"+transport.responseJSON.path+"','"+transport.responseJSON.subpath+"','"+transport.responseJSON.language+"','"+transport.responseJSON.type+"')\"><?php _e('Rescan',BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>"+
						"<span> | </span>" +
						"<a class=\"clickable\" onclick=\"bp_translate_remove_language(this,'"+escape(transport.responseJSON.name)+"','"+transport.responseJSON.row+"','"+transport.responseJSON.path+"','"+transport.responseJSON.subpath+"','"+transport.responseJSON.language+"');\"><?php _e('Delete',BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>"+
					"</td>"+
					"</tr>";			
				$$('#'+transport.responseJSON.row+' .mo-file').each(function(tr) {
					if ((tr.lang > transport.responseJSON.language) && !Object.isElement(elem_after)) {	elem_after = tr; }
				});
				ne = null;
				if (Object.isElement(elem_after)) { ne = elem_after.insert({ 'before' : content }).previous(); }
				else { ne = $$('#'+transport.responseJSON.row+' tbody').first().insert(content).childElements().last(); }
				new Effect.Highlight(ne, { startcolor: '#25FF00', endcolor: '#FFFFCF' });
			},
			onFailure: function(transport) {
				bp_translate_show_error(transport.responseText);
			}
		}
	); 	
	bp_translate_cancel_dialog();
	return false;
}

function bp_translate_remove_language(elem, name, row, path, subpath, language) {
	elem = $(elem);
	elem.blur();
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_dlg_delete',
				name: name,
				row: row,
				path: path,
				subpath: subpath,
				language: language,
				numlangs: $$('#'+row+' .mo-list-head').first().down(2).rel.split('|').size()
			},
			onSuccess: function(transport) {
				$('bp-translate-dialog-caption').update("<?php _e('Confirm Delete Language',BP_TRANSLATE_PO_TEXTDOMAIN); ?>");
				$("bp-translate-dialog-body").update(transport.responseText).setStyle({'padding' : '10px'});
				tb_show.defer(null,"#TB_inline?height=180&width=300&inlineId=bp-translate-dialog-container&modal=true",false);
			}
		}
	); 	
	return false;
}

function bp_translate_destroy_files(elem, name, row, path, subpath, language, numlangs){
	elem = $(elem);
	elem.blur();
	bp_translate_cancel_dialog();
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_destroy',
				name: name,
				row: row,
				path: path,
				subpath: subpath,
				language: language,
				numlangs: numlangs
			},
			onSuccess: function(transport) {
				$$('#'+transport.responseJSON.row+' .mo-file').each(function(tr) {
					if (tr.lang == transport.responseJSON.language) { 
						new Effect.Highlight(tr, { 
							startcolor: '#FF7A0F', 
							endcolor: '#FFFFCF', 
							duration: 1,
							afterFinish: function(obj) { 
								$$('#'+transport.responseJSON.row+' .mo-list-head').first().down(3).update(transport.responseJSON.head);
								a = $$('#'+transport.responseJSON.row+' .mo-list-head').first().down(2).rel.split('|').without(transport.responseJSON.language);
								$$('#'+transport.responseJSON.row+' .mo-list-head').first().down(2).rel = a.join('|');
								obj.element.remove(); 
							}
						});
					}
				});
			},
			onFailure: function(transport) {
				bp_translate_show_error(transport.responseText);
			}
		}
	); 	
	return false;	
}

function bp_translate_rescan_language(elem, name, row, path, subpath, language, type, textdomain) {
	elem = $(elem);
	elem.blur();
	var a = elem.up('table').summary.split('|');
	actual_domain = a[0];
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_dlg_rescan',
				name: name,
				row: row,
				path: path,
				subpath: subpath,
				language: language,
				numlangs: $$('#'+row+' .mo-list-head').first().down(1).rel.split('|').size(),
				type: type,
				textdomain: actual_domain
			},
			onSuccess: function(transport) {
				$('bp-translate-dialog-caption').update("<?php _e('Rescanning PHP Source Files',BP_TRANSLATE_PO_TEXTDOMAIN); ?>");
				$("bp-translate-dialog-body").update(transport.responseText).setStyle({'padding' : '10px'});
				tb_show.defer(null,"#TB_inline?height=230&width=510&inlineId=bp-translate-dialog-container&modal=true",false);
			}
		}
	); 		
	return false;
}

var bp_translate_php_source_json = 0;
var bp_translate_chuck_size = 20;

function bp_translate_scan_source_files() {
	if (bp_translate_php_source_json == 0) {
		$('bp-translate-dialog-rescan').hide();
		$('bp-translate-dialog-cancel').hide();
		$('bp-translate-dialog-scan-info').show();
		bp_translate_php_source_json = $('bp-translate-dialog-source-file-json').value.evalJSON();
	}
	if (bp_translate_php_source_json.next >= bp_translate_php_source_json.files.size()) {
		if ($('bp-translate-dialog-cancel').visible()) {
			bp_translate_cancel_dialog();
			bp_translate_php_source_json = 0;
			return false;
		}
		$('bp-translate-dialog-scan-info').hide();
		$('bp-translate-dialog-rescan').show().writeAttribute({'value' : '<?php _e('finished', BP_TRANSLATE_PO_TEXTDOMAIN); ?>' });
		$('bp-translate-dialog-cancel').show();
		$('bp-translate-dialog-progressfile').update('&nbsp;');
		elem = $$("#"+bp_translate_php_source_json.row+" .mo-file[lang=\""+bp_translate_php_source_json.language+"\"] div a").first();
		elem.className = "bp-translate-filetype-po-rw";
		elem.title = bp_translate_php_source_json.title;
		return false;
	}
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_scan_source_file',
				pofile: bp_translate_php_source_json.pofile,
				textdomain: bp_translate_php_source_json.textdomain,
				num: bp_translate_php_source_json.next,
				cnt: bp_translate_chuck_size,
				path: bp_translate_php_source_json.path,
				php: bp_translate_php_source_json.files.join("|")
			},
			onSuccess: function(transport) {
				bp_translate_php_source_json.title = transport.responseJSON.title;
				bp_translate_php_source_json.next += bp_translate_chuck_size;
				var perc = Math.min(Math.round(bp_translate_php_source_json.next*1000.0/bp_translate_php_source_json.files.size())/10.0, 100.00);
				$('bp-translate-dialog-progressvalue').update(Math.min(bp_translate_php_source_json.next, bp_translate_php_source_json.files.size()));
				$('bp-translate-dialog-progressbar').setStyle({'width' : ''+perc+'%'});
				if (bp_translate_php_source_json.files[bp_translate_php_source_json.next-bp_translate_chuck_size]) $('bp-translate-dialog-progressfile').update("<?php _e('File:', BP_TRANSLATE_PO_TEXTDOMAIN); ?>&nbsp;"+bp_translate_php_source_json.files[bp_translate_php_source_json.next-bp_translate_chuck_size].replace(bp_translate_php_source_json.path,""));
				bp_translate_scan_source_files().delay(0.1);
			},
			onFailure: function(transport) {
				$('bp-translate-dialog-scan-info').hide();
				$('bp-translate-dialog-rescan').show().writeAttribute({'value' : '<?php _e('finished', BP_TRANSLATE_PO_TEXTDOMAIN); ?>' });
				$('bp-translate-dialog-cancel').show();
				bp_translate_php_source_json = 0;
				bp_translate_show_error(transport.responseText);
			}
		}
	); 	
	return false;
}

//--- editor based functions ---
var bp_translate_pagesize = 100;
var bp_translate_pagenum = 1;
var bp_translate_search_timer = null;
var bp_translate_search_interval = Prototype.Browser.IE ? 0.3 : 0.1;

var bp_translate_destlang = 'de';
var bp_translate_path = '';
var bp_translate_file = '';
var bp_translate_num_plurals = 2;
var bp_translate_func_plurals = '';
var bp_translate_idx = {	'total' : [], 'plurals' : [], 'open' : [], 'rem' : [], 'code' : [], 'ctx' : [], 'cur' : [] , 'ltd' : [] }
var bp_translate_searchbase = [];
var bp_translate_pofile = [];
var bp_translate_textdomains = [];
var bp_translate_actual_type = '';

function bp_translate_init_editor(actual_domain, actual_type) {
	//list all contained text domains
	opt_list = '';
	bp_translate_actual_type = actual_type;
	for (i=0; i<bp_translate_textdomains.size(); i++) {
		opt_list += '<option value="'+bp_translate_textdomains[i]+'"'+(bp_translate_textdomains[i] == actual_domain ? ' selected="selected"' : '')+'>'+(bp_translate_textdomains[i].empty() ? 'default' : bp_translate_textdomains[i])+'</option>';
	}
	initial_domain = $('bp-translate-mo-textdomain-val').update(opt_list).value;
	
	//setup all indizee register
	for (i=0; i<bp_translate_pofile.size(); i++) {
		bp_translate_idx.total.push(i);
		if (Object.isArray(bp_translate_pofile[i].key)) {
			if (!Object.isArray(bp_translate_pofile[i].val)) {
				if(bp_translate_pofile[i].val.blank()) bp_translate_idx.open.push(i);
			}
			else{
				if(bp_translate_pofile[i].val.join('').blank()) bp_translate_idx.open.push(i);
			}
			bp_translate_idx.plurals.push(i);
		}else if(bp_translate_pofile[i].val.empty()) {
			bp_translate_idx.open.push(i);
		}
		if(!bp_translate_pofile[i].rem.empty()) bp_translate_idx.rem.push(i);
		if(bp_translate_pofile[i].ctx) bp_translate_idx.ctx.push(i);
		if(bp_translate_pofile[i].code) bp_translate_idx.code.push(i);
		if(bp_translate_pofile[i].ltd.indexOf(initial_domain) != -1) bp_translate_idx.ltd.push(i);
	}
//$	bp_translate_idx.cur = bp_translate_idx.total;
	bp_translate_idx.cur = bp_translate_idx.ltd.intersect(bp_translate_idx.total);
	bp_translate_searchbase = bp_translate_idx.cur;
	if(bp_translate_textdomains[0] != '{php-code}'){
		$('bp-translate-write-mo-file').show();
	}else{
		$('bp-translate-write-mo-file').hide();
	}
	bp_translate_change_pagesize(100);
	window.scrollTo(0,0);
	$('s_original').value="";
	$('s_original').autoComplete="off";
	$('s_translation').value="";
	$('s_translation').autoComplete="off";	
}

function bp_translate_change_textdomain_view(textdomain) {
	bp_translate_idx.ltd = [];
	for (i=0; i<bp_translate_pofile.size(); i++) {
		if (bp_translate_pofile[i].ltd.indexOf(textdomain) != -1) bp_translate_idx.ltd.push(i);
	}
	bp_translate_idx.cur = bp_translate_idx.ltd.intersect(bp_translate_idx.total);
	bp_translate_searchbase = bp_translate_idx.cur;
	$$("a.bp-translate-filter").each(function(e) { e.removeClassName('current')});
	$('bp-translate-filter-all').addClassName('current');
	var hide = ((bp_translate_actual_type != 'wordpress' && textdomain.empty()) || (textdomain == '{php-code}'));
	if (hide) {
		$('bp-translate-write-mo-file').hide();
	}
	else {
		$('bp-translate-write-mo-file').show();
	}
	bp_translate_filter_result('bp-translate-filter-all', bp_translate_idx.total);
}

function bp_translate_show_error(message) {
	error = "<div style=\"text-align:center\"><img src=\"<?php echo BP_TRANSLATE_PO_BASE_URL."/includes/images/l10n/error.gif"; ?>\" align=\"left\" />"+message+
			"<p style=\"margin:15px 0 0 0;text-align:center; padding-top: 5px;border-top: solid 1px #aaa;\">"+
			"<input class=\"button\" type=\"submit\" onclick=\"return bp_translate_cancel_dialog();\" value=\"  Ok  \"/>"+
			"</p>"+
			"</div>";
	$('bp-translate-dialog-caption').update("Localization - <?php _e('Access Error',BP_TRANSLATE_PO_TEXTDOMAIN); ?>");
	$("bp-translate-dialog-body").update(error).setStyle({'padding' : '10px'});
	if ($('bp-translate-dialog-saving')) $('bp-translate-dialog-saving').hide();
	tb_show.defer(null,"#TB_inline?height=140&width=510&inlineId=bp-translate-dialog-container&modal=true",false);
}

function bp_translate_cancel_dialog(){
	tb_remove();
	$('bp-translate-dialog-body').update("");
	$$('.highlight-editing').each(function(e) {
		e.removeClassName('highlight-editing');
	});
}

function bp_translate_launch_editor(elem, file, path) {
	var a = $(elem).up('table').summary.split('|');
	$('bp-translate-wrap-main').hide();
	$('bp-translate-wrap-editor').show();
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_launch_editor',
				basepath: path,
				file: file
			},
			onSuccess: function(transport) {
				//switch to editor now
				$('bp-translate-json-header').insert(transport.responseJSON.header);
				$('catalog-last-saved').update(transport.responseJSON.last_saved);
				$$('#bp-translate-json-header a')[0].update(transport.responseJSON.file);
				bp_translate_destlang = transport.responseJSON.destlang;
				bp_translate_path = transport.responseJSON.path;
				bp_translate_file = transport.responseJSON.file;
				bp_translate_num_plurals = transport.responseJSON.plurals_num;
				bp_translate_func_plurals = transport.responseJSON.plurals_func;
				bp_translate_idx = transport.responseJSON.index;
				bp_translate_pofile = transport.responseJSON.content;
				bp_translate_textdomains = transport.responseJSON.textdomains;
				bp_translate_init_editor(a[0], a[1]);
			},
			onFailure: function(transport) {
				$('catalog-body').update('<tr><td colspan="4" align="center" style="color:#f00;">'+transport.responseText+'</td></tr>');
			}
		}
	); 
	return false;	
}

function bp_translate_toggle_header(host, elem) {
	$(host).up().toggleClassName('po-header-collapse');
	$(elem).toggle();
}

function bp_translate_change_pagesize(newsize) {
	bp_translate_pagesize = parseInt(newsize);
	bp_translate_change_pagenum(1);
}

function bp_translate_change_pagenum(newpage) {
	bp_translate_pagenum = newpage;
	var cp = $('catalog-pages-top');
	var cb = $('catalog-body')
	
	var inner = '';
	
	var cnt = Math.round(bp_translate_idx.cur.size() * 1.0 / bp_translate_pagesize + 0.499);
	if (cnt > 1) {
		
		if (bp_translate_pagenum > 1) { inner += "<a class=\"next page-numbers\" onclick=\"bp_translate_change_pagenum("+(bp_translate_pagenum-1)+")\"><?php _e('&laquo; Previous', BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>"; }
		var low = Math.max(bp_translate_pagenum - 5,1);
		if (low > 1) inner += "<span>&nbsp;...&nbsp;</span>"; 
		for (i=low; i<=Math.min(low+10,cnt); i++) {
			inner += "<a class=\"page-numbers"+(i==bp_translate_pagenum ? ' current' : '')+"\" onclick=\"bp_translate_change_pagenum("+i+")\">"+i+"</a>";
		}
		if (Math.min(low+10,cnt) < cnt) inner += "<span>&nbsp;...&nbsp;</span>"; 
		if (bp_translate_pagenum < cnt) { inner += "<a class=\"next page-numbers\" onclick=\"bp_translate_change_pagenum("+(bp_translate_pagenum+1)+")\"><?php _e('Next &raquo;', BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>"; }
	}
	cp.update(inner);
	$('catalog-pages-bottom').update(inner);
	
	inner = '';

	for (var i=(bp_translate_pagenum-1)*bp_translate_pagesize; i<Math.min(bp_translate_pagenum * bp_translate_pagesize, bp_translate_idx.cur.size());i++) {
		inner += "<tr"+(i % 2 == 0 ? '' : ' class="odd"')+" id=\"msg-row-"+bp_translate_idx.cur[i]+"\">";
		var tooltip = [];
		if (!bp_translate_pofile[bp_translate_idx.cur[i]].rem.empty()) tooltip.push(String.fromCharCode(3)+"<?php _e('Comment',BP_TRANSLATE_PO_TEXTDOMAIN); ?>"+String.fromCharCode(4)+bp_translate_pofile[bp_translate_idx.cur[i]].rem);
		if (bp_translate_pofile[bp_translate_idx.cur[i]].code) tooltip.push(String.fromCharCode(3)+"<?php _e('Code Hint',BP_TRANSLATE_PO_TEXTDOMAIN); ?>"+String.fromCharCode(4)+bp_translate_pofile[bp_translate_idx.cur[i]].code);
		if (tooltip.size() > 0) {
			tooltip = tooltip.join(String.fromCharCode(1)).replace("\n", String.fromCharCode(1)).escapeHTML();
			tooltip = tooltip.replace(/\1/g, '<br/>').replace(/\3/g, '<strong>').replace(/\4/g, '</strong>');
		}
		else { tooltip = '' };
		inner += "<td nowrap=\"nowrap\">";
		if(bp_translate_pofile[bp_translate_idx.cur[i]].ref.size() > 0) {
			inner += "<a class=\"bp-translate-msg-tip\"><img alt=\"\" src=\"<?php echo BP_TRANSLATE_PO_BASE_URL;?>/includes/images/l10n/php.gif\" /><span><strong><?php _e('Files:',BP_TRANSLATE_PO_TEXTDOMAIN); ?></strong>";
			bp_translate_pofile[bp_translate_idx.cur[i]].ref.each(function(r) {
				inner += "<em onclick=\"bp_translate_view_phpfile(this, '"+r+"', "+bp_translate_idx.cur[i]+")\">"+r+"</em><br />";
			});
			inner += "</span></a>";
		}		
		inner += (tooltip.empty() ? '' : "<a class=\"bp-translate-msg-tip\"><img alt=\"\" src=\"<?php echo BP_TRANSLATE_PO_BASE_URL;?>/includes/images/l10n/comment.gif\" /><span>"+tooltip+"</span></a>");
		inner += "</td>";
		ctx_str = '';
		if (bp_translate_pofile[bp_translate_idx.cur[i]].ctx) {
			ctx_str = "<div><b style=\"border-bottom: 1px dotted #000;\"><?php _e('Context',BP_TRANSLATE_PO_TEXTDOMAIN); ?>:</b>&nbsp;<span style=\"color:#f00;\">"+bp_translate_pofile[bp_translate_idx.cur[i]].ctx+"</span></div>";
		}
		if (Object.isArray(bp_translate_pofile[bp_translate_idx.cur[i]].key)) {
			inner += 
				"<td>"+ctx_str+"<div><span class=\"bp-translate-pl-form\"><?php _e('Singular:',BP_TRANSLATE_PO_TEXTDOMAIN); ?> </span>"+bp_translate_pofile[bp_translate_idx.cur[i]].key[0].escapeHTML()+"</div><div><span class=\"bp-translate-pl-form\"><?php _e('Plural:',BP_TRANSLATE_PO_TEXTDOMAIN); ?> </span>"+bp_translate_pofile[bp_translate_idx.cur[i]].key[1].escapeHTML()+"</div></td>"+
				"<td>"+ctx_str;
			for (pl=0;pl<bp_translate_num_plurals; pl++) {
				if (bp_translate_num_plurals == 1) {
					inner += "<div><span class=\"bp-translate-pl-form\"><?php _e('Plural Index Result =',BP_TRANSLATE_PO_TEXTDOMAIN); ?> "+pl+" </span>"+(!bp_translate_pofile[bp_translate_idx.cur[i]].val.empty() ? bp_translate_pofile[bp_translate_idx.cur[i]].val.escapeHTML() : '&nbsp;')+"</div>"
				}
				else{
					inner += "<div><span class=\"bp-translate-pl-form\"><?php _e('Plural Index Result =',BP_TRANSLATE_PO_TEXTDOMAIN); ?> "+pl+" </span>"+(!bp_translate_pofile[bp_translate_idx.cur[i]].val[pl].empty() ? bp_translate_pofile[bp_translate_idx.cur[i]].val[pl].escapeHTML() : '&nbsp;')+"</div>"
				}
			}
			inner += "</td>";
		}
		else{
			inner += 
				"<td>"+ctx_str+bp_translate_pofile[bp_translate_idx.cur[i]].key.escapeHTML()+"</td>"+
				"<td>"+ctx_str+(bp_translate_pofile[bp_translate_idx.cur[i]].val.empty() ? '&nbsp;' : bp_translate_pofile[bp_translate_idx.cur[i]].val.escapeHTML())+"</td>";
		}
		inner += 
			"<td nowrap=\"nowrap\">"+
			  "<a class=\"tr-edit-link\" onclick=\"return bp_translate_edit_catalog(this);\"><?php _e('Edit',BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>&nbsp;|&nbsp;"+  
			  "<a onclick=\"return bp_translate_copy_catalog(this);\"><?php _e('Copy',BP_TRANSLATE_PO_TEXTDOMAIN); ?></a>"; // TODO: add here comment editing link
		inner += "</td></tr>";
	}	
	cb.update(inner);
	
	$$("#bp-translate-filter-all span").first().update(bp_translate_idx.total.size());
	$$("#bp-translate-filter-plurals span").first().update(bp_translate_idx.plurals.size());
	$$("#bp-translate-filter-open span").first().update(bp_translate_idx.open.size());
	$$("#bp-translate-filter-rem span").first().update(bp_translate_idx.rem.size());
	$$("#bp-translate-filter-code span").first().update(bp_translate_idx.code.size());
	$$("#bp-translate-filter-ctx span").first().update(bp_translate_idx.ctx.size());
	$$("#bp-translate-filter-search span").first().update(bp_translate_idx.cur.size());
	$$("#bp-translate-filter-regexp span").first().update(bp_translate_idx.cur.size());
}

function bp_translate_filter_result(elem, set) {
	$$("a.bp-translate-filter").each(function(e) { e.removeClassName('current')});
	$(elem).addClassName('current');
	$('s_original').clear();
	$('s_translation').clear();
	$('bp-translate-filter-search').up().hide();
	$('bp-translate-filter-regexp').up().hide();
//$	bp_translate_idx.cur = set;
	bp_translate_idx.cur = bp_translate_idx.ltd.intersect(set);
	bp_translate_searchbase = bp_translate_idx.cur;
	bp_translate_change_pagenum(1);
}

function bp_translate_search_key(elem, expr) {
	var term = $(elem).value;
	var ignore_case = $('ignorecase_key').checked;
	var is_expr = (typeof(expr) == "object");
	if (is_expr) { 
		term = expr; ignore_case = false; 
		$('s_original').clear();
	}
	else { 
		if (ignore_case) term = term.toLowerCase(); 
	}
	$('s_translation').clear();
	$$("a.bp-translate-filter").each(function(e) { e.removeClassName('current')});
	bp_translate_idx.cur = [];
	try{
		for (i=0; i<bp_translate_searchbase.size(); i++) {
			if (Object.isArray(bp_translate_pofile[bp_translate_searchbase[i]].key)) {
				if (bp_translate_pofile[bp_translate_searchbase[i]].key.find(function(s){ return (ignore_case ? s.toLowerCase().include(term) : s.match(term)); })) bp_translate_idx.cur.push(bp_translate_searchbase[i]);			
			}
			else{
				if ( (ignore_case ? bp_translate_pofile[bp_translate_searchbase[i]].key.toLowerCase().include(term) : bp_translate_pofile[bp_translate_searchbase[i]].key.match(term) ) ) bp_translate_idx.cur.push(bp_translate_searchbase[i]);
			}
		}
	}catch(e) {
		//in case of half ready typed regexp catch it silently
		bp_translate_idx.cur = bp_translate_idx.total;
	}
	$('bp-translate-filter-search').up().hide();
	$('bp-translate-filter-regexp').up().hide();
	if (term) {
		if (is_expr) $('bp-translate-filter-regexp').up().show();
		else $('bp-translate-filter-search').up().show();
		bp_translate_change_pagenum(1);
	}
	else {
		bp_translate_filter_result('bp-translate-filter-all', bp_translate_idx.total);
	}
}

function bp_translate_search_val(elem, expr) {
	var term = $(elem).value;
	var ignore_case = $('ignorecase_val').checked;
	var is_expr = (typeof(expr) == "object");
	if (is_expr) { 
		term = expr; ignore_case = false; 
		$('s_translation').clear();
	}
	else { 
		if (ignore_case) term = term.toLowerCase(); 
	}
	$('s_original').clear();
	$$("a.bp-translate-filter").each(function(e) { e.removeClassName('current')});
	bp_translate_idx.cur = [];
	try{
		for (i=0; i<bp_translate_searchbase.size(); i++) {
			if (Object.isArray(bp_translate_pofile[bp_translate_searchbase[i]].val)) {
				if (bp_translate_pofile[bp_translate_searchbase[i]].val.find(function(s){ return (ignore_case ? s.toLowerCase().include(term) : s.match(term)); })) bp_translate_idx.cur.push(bp_translate_searchbase[i]);
			}
			else{
				if ( (ignore_case ? bp_translate_pofile[bp_translate_searchbase[i]].val.toLowerCase().include(term) : bp_translate_pofile[bp_translate_searchbase[i]].val.match(term) ) ) bp_translate_idx.cur.push(bp_translate_searchbase[i]);
			}
		}
	}catch(e) {
		//in case of half ready typed regexp catch it silently
		bp_translate_idx.cur = bp_translate_idx.total;
	}
	$('bp-translate-filter-search').up().hide();
	$('bp-translate-filter-regexp').up().hide();
	if (term) {
		if (is_expr) $('bp-translate-filter-regexp').up().show();
		else $('bp-translate-filter-search').up().show();
		bp_translate_change_pagenum(1);
	}
	else {
		bp_translate_filter_result('bp-translate-filter-all', bp_translate_idx.total);
	}
}

function bp_translate_search_result(elem) {
	window.clearTimeout(bp_translate_search_timer);
	if ($(elem).id == "s_original") {
		bp_translate_search_timer = this.bp_translate_search_key.delay(bp_translate_search_interval, elem);
	}else{
		bp_translate_search_timer = this.bp_translate_search_val.delay(bp_translate_search_interval, elem);
	}
}

function bp_translate_exec_expression(elem) {
	var s = $("bp-translate-dialog-expression").value;
	var t = /^\/(.*)\/([gi]*)/;
	var a = t.exec(s);
	var r = (a != null ? RegExp(a[1], a[2]) : RegExp(s, ''));
	if (elem == "s_original") {
		bp_translate_search_key(elem, r);
	}else{
		bp_translate_search_val(elem, r);
	}
	bp_translate_cancel_dialog();
}

function bp_translate_search_regexp(elem) {
	$(elem).blur();
	$('bp-translate-dialog-caption').update("<?php _e('Extended Expression Search',BP_TRANSLATE_PO_TEXTDOMAIN); ?>");
	$("bp-translate-dialog-body").update(
		"<div><strong><?php _e('Expression:',BP_TRANSLATE_PO_TEXTDOMAIN); ?></strong></div>"+
		"<input type=\"text\" id=\"bp-translate-dialog-expression\" style=\"width:98%;font-size:11px;line-height:normal;\" value=\"\"\>"+		
		"<div style=\"margin-top:10px; color:#888;\"><strong><?php _e('Examples: <small>Please refer to official Perl regular expression descriptions</small>',BP_TRANSLATE_PO_TEXTDOMAIN); ?></strong></div>"+
		'<div style="height: 215px; overflow:scroll;">'+
		<?php require('bp-translate-perlreg.php'); ?>
		'</div>'+
		"<p style=\"margin:5px 0 0 0;text-align:center; padding-top: 5px;border-top: solid 1px #aaa;\">"+
		"<input class=\"button\" type=\"submit\" onclick=\"return bp_translate_exec_expression('"+elem+"');\" value=\"  <?php echo _e('Search', BP_TRANSLATE_PO_TEXTDOMAIN); ?>  \"/>"+
		"</p>"
	).setStyle({'padding' : '10px'});		
	tb_show(null,"#TB_inline?height=385&width=600&inlineId=bp-translate-dialog-container&modal=true",false);	
	$("bp-translate-dialog-expression").focus();
}

function bp_translate_translate_google(elem, source, dest) {
	$(elem).blur();
	$(elem).down().show();
	//resulting {"responseData": {"translatedText":"Kann nicht öffnen zu schreiben!"}, "responseDetails": null, "responseStatus": 200}
	//TODO: can't handle google errors by own error dialog, because Thickbox is not multi instance ready (modal over modal) !!!
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{
			parameters: {
				action: 'bp_translate_po_translate_by_google',
				msgid: $(source).value,
				destlang: bp_translate_destlang
			},
			onSuccess: function(transport) {
				if (transport.responseJSON.responseStatus == 200 && !transport.responseJSON.responseData.translatedText.empty()) {
					$(dest).value = transport.responseJSON.responseData.translatedText;
				}else{
					alert(transport.responseJSON.responseDetails);
				}
				$(elem).down().hide();
			},
			onFailure: function(transport) {
				$(elem).down().hide();
				alert(transport.responseText); 
			}
		}
	);
}

function bp_translate_save_translation(elem, isplural, additional_action){
	$(elem).blur();
	
	msgid = $('bp-translate-dialog-msgid').value;
	msgstr = '';
	
	glue = (Prototype.Browser.Opera ? '\1' : '\0'); //opera bug: can't send embedded 0 in strings!
	
	if (isplural) {
		msgid = [$('bp-translate-dialog-msgid').value, $('bp-translate-dialog-msgid-plural').value].join(glue);
		msgstr = [];
		if (bp_translate_num_plurals == 1){
			msgstr = $('bp-translate-dialog-msgstr-0').value;
		}
		else {
			for (pl=0;pl<bp_translate_num_plurals; pl++) {
				msgstr.push($('bp-translate-dialog-msgstr-'+pl).value);
			}
			msgstr = msgstr.join(glue);
		}
	}
	else{
		msgstr = $('bp-translate-dialog-msgstr').value;
	}
	idx = parseInt($('bp-translate-dialog-msg-idx').value);
	if (additional_action != 'close') {
		$('bp-translate-dialog-body').hide();
		$('bp-translate-dialog-saving').show();
	}
	//add the context in front of again
	if (bp_translate_pofile[idx].ctx) msgid = bp_translate_pofile[idx].ctx+ String.fromCharCode(4) + msgid;
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_save_catalog_entry',
				path: bp_translate_path,
				file: bp_translate_file,
				isplural: isplural,
				msgid: msgid,
				msgstr: msgstr,
				msgidx: idx
			},
			onSuccess: function(transport) {
				if (isplural && (bp_translate_num_plurals != 1)) {
					bp_translate_pofile[idx].val = msgstr.split(glue);
				}
				else{
					bp_translate_pofile[idx].val = msgstr;
				}
				//TODO: check also erasing fields !!!!
				if (!msgstr.empty() && (bp_translate_idx.open.indexOf(idx) != -1)) { 
					bp_translate_idx.open = bp_translate_idx.open.without(idx); 
//					bp_translate_idx.cur = bp_translate_idx.cur.without(idx); //TODO: only allowed if this is not total !!!
				}
				bp_translate_change_pagenum(bp_translate_pagenum);
				if (additional_action != 'close') {
					var lin_idx = bp_translate_idx.cur.indexOf(idx);
					if (additional_action == 'prev') {
						lin_idx--; 
					}
					if (additional_action == 'next') {
						lin_idx++; 
					}					
					if (Math.floor(lin_idx / bp_translate_pagesize) != bp_translate_pagenum -1) {
						bp_translate_change_pagenum(Math.floor(lin_idx / bp_translate_pagesize) + 1);
					}
					$('bp-translate-dialog-saving').hide();
					$('bp-translate-dialog-body').show();
					bp_translate_edit_catalog($$("#msg-row-"+bp_translate_idx.cur[lin_idx]+" a.tr-edit-link")[0]);
				}
				else {
					bp_translate_cancel_dialog();
				}
			},
			onFailure: function(transport) {
				$('bp-translate-dialog-saving').hide();
				$('bp-translate-dialog-body').show();
				//opera bug: Opera has in case of error no valid responseText (always empty), even if server sends it! Ensure status text instead (dirty fallback)
				bp_translate_show_error( (Prototype.Browser.Opera ? transport.statusText : transport.responseText));
			}
		}
	); 	
	return false;
}

function bp_translate_suppress_enter(event) {
	if(event.keyCode == Event.KEY_RETURN) Event.stop(event);
}

function bp_translate_copy_catalog(elem) {
	elem = $(elem);
	elem.blur();
	var msg_idx = parseInt(elem.up().up().id.replace('msg-row-',''));
	msgid = bp_translate_pofile[msg_idx].key;
	msgstr = bp_translate_pofile[msg_idx].key;
	if(Object.isArray(bp_translate_pofile[msg_idx].key)) {
		msgid = bp_translate_pofile[msg_idx].key.join("\0");
		if (bp_translate_num_plurals == 1) {
			msgstr = bp_translate_pofile[msg_idx].key[0];
		}
		else{
			msgstr = msgid;
		}
	}
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_save_catalog_entry',
				path: bp_translate_path,
				file: bp_translate_file,
				isplural: Object.isArray(bp_translate_pofile[msg_idx].key),
				msgid: msgid,
				msgstr: msgstr,
				msgidx: msg_idx
			},
			onSuccess: function(transport) {
				idx = msg_idx;
				if (Object.isArray(bp_translate_pofile[msg_idx].key) && (bp_translate_num_plurals != 1)) {
					bp_translate_pofile[idx].val = msgstr.split("\0");
				}
				else{
					bp_translate_pofile[idx].val = msgstr;
				}
				//TODO: check also erasing fields !!!!
				if (!msgstr.empty() && (bp_translate_idx.open.indexOf(idx) != -1)) { 
					bp_translate_idx.open = bp_translate_idx.open.without(idx); 
				}
				bp_translate_change_pagenum(bp_translate_pagenum);
			},
			onFailure: function(transport) {
				bp_translate_show_error(transport.responseText);
			}
		}
	); 	
	return false;	
}

function bp_translate_edit_catalog(elem) {
	elem = $(elem);
	elem.blur();
	elem.up().up().addClassName('highlight-editing');
	var msg_idx = parseInt(elem.up().up().id.replace('msg-row-',''));
	$('bp-translate-dialog-caption').update("<?php _e('Edit Catalog Entry',BP_TRANSLATE_PO_TEXTDOMAIN); ?>");
	if (Object.isArray(bp_translate_pofile[msg_idx].key)) {
		trans = '';
		for (pl=0;pl<bp_translate_num_plurals; pl++) {
			if (!bp_translate_destlang.empty()) {
				switch(pl){
					case 0:
						trans += "<div style=\"margin-top:10px;height:20px;\"><strong class=\"alignleft\"><?php _e('Plural Index Result =',BP_TRANSLATE_PO_TEXTDOMAIN); ?> "+pl+"</strong><a class=\"alignright clickable google\" onclick=\"bp_translate_translate_google(this, 'bp-translate-dialog-msgid', 'bp-translate-dialog-msgstr-0');\"><img style=\"display:none;\" src=\"<?php echo BP_TRANSLATE_PO_BASE_URL; ?>/includes/images/l10n/loading-small.gif\" />&nbsp;<?php _e('translate with Google API',BP_TRANSLATE_PO_TEXTDOMAIN); ?></a><br class=\"clear\" /></div>";
					break;
					case 1:
						trans += "<div style=\"margin-top:10px;height:20px;\"><strong class=\"alignleft\"><?php _e('Plural Index Result =',BP_TRANSLATE_PO_TEXTDOMAIN); ?> "+pl+"</strong><a class=\"alignright clickable google\" onclick=\"bp_translate_translate_google(this, 'bp-translate-dialog-msgid-plural', 'bp-translate-dialog-msgstr-1');\"><img style=\"display:none;\" src=\"<?php echo BP_TRANSLATE_PO_BASE_URL; ?>/includes/images/l10n/loading-small.gif\" />&nbsp;<?php _e('translate with Google API',BP_TRANSLATE_PO_TEXTDOMAIN); ?></a><br class=\"clear\" /></div>";
					break;
					default:
						trans += "<div style=\"margin-top:10px;height:20px;\"><strong><?php _e('Plural Index Result =',BP_TRANSLATE_PO_TEXTDOMAIN); ?> "+pl+"</strong></div>";
					break;
				}
			}
			else{
				trans += "<div style=\"margin-top:10px;\"><strong><?php _e('Plural Index Result =',BP_TRANSLATE_PO_TEXTDOMAIN); ?> "+pl+"</strong></div>";
			}
			if (bp_translate_num_plurals == 1) {
				trans += "<textarea id=\"bp-translate-dialog-msgstr-"+pl+"\" cols=\"50\" rows=\"1\" style=\"width:98%;font-size:11px;line-height:normal;\">"+bp_translate_pofile[msg_idx].val.escapeHTML()+"</textarea>";
			}
			else{
				trans += "<textarea id=\"bp-translate-dialog-msgstr-"+pl+"\" cols=\"50\" rows=\"1\" style=\"width:98%;font-size:11px;line-height:normal;\">"+bp_translate_pofile[msg_idx].val[pl].escapeHTML()+"</textarea>";
			}
		}
	
		$("bp-translate-dialog-body").update(	
			"<div><strong><?php _e('Singular:',BP_TRANSLATE_PO_TEXTDOMAIN); ?></strong></div>"+
			"<textarea id=\"bp-translate-dialog-msgid\" cols=\"50\" rows=\"1\" style=\"width:98%;font-size:11px;line-height:normal;\" readonly=\"readonly\">"+bp_translate_pofile[msg_idx].key[0].escapeHTML()+"</textarea>"+
			"<div style=\"margin-top:10px;\"><strong><?php _e('Plural:',BP_TRANSLATE_PO_TEXTDOMAIN); ?></strong></div>"+
			"<textarea id=\"bp-translate-dialog-msgid-plural\" cols=\"50\" rows=\"1\" style=\"width:98%;font-size:11px;line-height:normal;\" readonly=\"readonly\">"+bp_translate_pofile[msg_idx].key[1].escapeHTML()+"</textarea>"+
			"<div style=\"font-weight:bold;padding-top: 5px;border-bottom: dotted 1px #aaa;\"><?php _e("Plural Index Calculation:",BP_TRANSLATE_PO_TEXTDOMAIN);?>&nbsp;&nbsp;&nbsp;<span style=\"color:#D54E21;\">"+bp_translate_func_plurals+"</span></div>"+
			trans+
			"<p style=\"margin:5px 0 0 0;text-align:center; padding-top: 5px;border-top: solid 1px #aaa;\">"+
			"<input class=\"button\""+(bp_translate_idx.cur.indexOf(msg_idx) > 0 ? "" : " disabled=\"disabled\"")+" type=\"submit\" onclick=\"return bp_translate_save_translation(this, true, 'prev');\" value=\"  <?php echo _e('« Save & Previous',BP_TRANSLATE_PO_TEXTDOMAIN); ?>  \"/>&nbsp;&nbsp;&nbsp;&nbsp;"+
			"<input class=\"button\" type=\"submit\" onclick=\"return bp_translate_save_translation(this, true, 'close');\" value=\"  <?php echo _e('Save',BP_TRANSLATE_PO_TEXTDOMAIN); ?>  \"/>"+
			"&nbsp;&nbsp;&nbsp;&nbsp;<input class=\"button\""+(bp_translate_idx.cur.indexOf(msg_idx)+1 < bp_translate_idx.cur.size() ? "" : " disabled=\"disabled\"")+" type=\"submit\" onclick=\"return bp_translate_save_translation(this, true, 'next');\" value=\"  <?php echo _e('Save & Next »',BP_TRANSLATE_PO_TEXTDOMAIN); ?>  \"/>"+
			"</p><input id=\"bp-translate-dialog-msg-idx\" type=\"hidden\" value=\""+msg_idx+"\" />"
		).setStyle({'padding' : '10px'});		
	}else{
		$("bp-translate-dialog-body").update(	
			"<div><strong><?php _e('Original:',BP_TRANSLATE_PO_TEXTDOMAIN); ?></strong></div>"+
			"<textarea id=\"bp-translate-dialog-msgid\" cols=\"50\" rows=\"7\" style=\"width:98%;font-size:11px;line-height:normal;\" readonly=\"readonly\">"+bp_translate_pofile[msg_idx].key.escapeHTML()+"</textarea>"
			+ (bp_translate_destlang.empty() ? 
			"<div style=\"margin-top:10px;\"><strong><?php _e('Translation:',BP_TRANSLATE_PO_TEXTDOMAIN); ?></strong></div>"
			:
			 "<div style=\"margin-top:10px;height:20px;\"><strong class=\"alignleft\"><?php _e('Translation:',BP_TRANSLATE_PO_TEXTDOMAIN); ?></strong><a class=\"alignright clickable google\" onclick=\"bp_translate_translate_google(this, 'bp-translate-dialog-msgid', 'bp-translate-dialog-msgstr');\"><img style=\"display:none;\" align=\"left\" src=\"<?php echo BP_TRANSLATE_PO_BASE_URL; ?>/includes/images/l10n/loading-small.gif\" />&nbsp;<?php _e('translate with Google API',BP_TRANSLATE_PO_TEXTDOMAIN); ?></a><br class=\"clear\" /></div>"
			 ) +
			"<textarea id=\"bp-translate-dialog-msgstr\" cols=\"50\" rows=\"7\" style=\"width:98%;font-size:11px;line-height:normal;\">"+bp_translate_pofile[msg_idx].val.escapeHTML()+"</textarea>"+
			"<p style=\"margin:5px 0 0 0;text-align:center; padding-top: 5px;border-top: solid 1px #aaa;\">"+
			"<input class=\"button\""+(bp_translate_idx.cur.indexOf(msg_idx) > 0 ? "" : " disabled=\"disabled\"")+" type=\"submit\" onclick=\"return bp_translate_save_translation(this, false, 'prev');\" value=\"  <?php echo _e('« Save & Previous',BP_TRANSLATE_PO_TEXTDOMAIN); ?>  \"/>&nbsp;&nbsp;&nbsp;&nbsp;"+
			"<input class=\"button\" type=\"submit\" onclick=\"return bp_translate_save_translation(this, false, 'close');\" value=\"  <?php echo _e('Save',BP_TRANSLATE_PO_TEXTDOMAIN); ?>  \"/>"+
			"&nbsp;&nbsp;&nbsp;&nbsp;<input class=\"button\""+(bp_translate_idx.cur.indexOf(msg_idx)+1 < bp_translate_idx.cur.size() ? "" : " disabled=\"disabled\"")+" type=\"submit\" onclick=\"return bp_translate_save_translation(this, false, 'next');\" value=\"  <?php echo _e('Save & Next »',BP_TRANSLATE_PO_TEXTDOMAIN); ?>  \"/>"+
			"</p><input id=\"bp-translate-dialog-msg-idx\" type=\"hidden\" value=\""+msg_idx+"\" />"
		).setStyle({'padding' : '10px'});
	}
	tb_show(null,"#TB_inline?height="+(bp_translate_num_plurals > 2 && Object.isArray(bp_translate_pofile[msg_idx].key) ? '520' : '385')+"&width=600&inlineId=bp-translate-dialog-container&modal=true",false);
	$$('#bp-translate-dialog-body textarea').each(function(e) {
		e.observe('keydown', bp_translate_suppress_enter);
		e.observe('keypress', bp_translate_suppress_enter);
		e.observe('keyup', bp_translate_suppress_enter);
	});
	return false;
}

function bp_translate_view_phpfile(elem, phpfile, idx) {
	elem.blur();	
	glue = (Prototype.Browser.Opera ? '\1' : '\0'); //opera bug: can't send embedded 0 in strings!
	msgid = bp_translate_pofile[idx].key;
	if (Object.isArray(msgid)) {
		msgid = msgid.join(glue);
	}
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_dlg_show_source',
				path: bp_translate_path,
				file: phpfile,
				msgid: msgid
			},
			onSuccess: function(transport) {
				//own <iframe> creation, because of POST content filling into inline thickbox
				var iframe = null;
				$('bp-translate-dialog-caption').update("<?php _e('File:', BP_TRANSLATE_PO_TEXTDOMAIN); ?> "+phpfile.split(':')[0]);
				$('bp-translate-dialog-body').insert(iframe = new Element('iframe', {'class' : 'bp-translate-dialog-iframe', 'frameBorder' : '0'}).writeAttribute({'width' : '100%', 'height' : '570px', 'margin': '0'})).setStyle({'padding' : '0px'});
				tb_show(null,"#TB_inline?height=600&width=600&inlineId=bp-translate-dialog-container&modal=true",false);
				iframe.contentWindow.document.open();
				iframe.contentWindow.document.write(transport.responseText);
				iframe.contentWindow.document.close();
			}
		}
	); 
	return false;	
}

function bp_translate_generate_mofile(elem) {
	elem.blur();
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_generate_mo_file',
				pofile: bp_translate_path + bp_translate_file,
				textdomain: $('bp-translate-mo-textdomain-val').value
			},
			onSuccess: function(transport) {
				new Effect.Highlight($('catalog-last-saved').update(transport.responseJSON.filetime), { startcolor: '#25FF00', endcolor: '#FFFFCF' });
			},
			onFailure: function(transport) {
				bp_translate_show_error(transport.responseText);
			}
		}
	); 
	return false;
}

function bp_translate_create_languange_path(elem) {
	elem.blur();
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_create_language_path'
			},
			onSuccess: function(transport) {
				window.location.reload();
			},
			onFailure: function(transport) {
				bp_translate_show_error(transport.responseText);
			}
		}
	); 
	return false;	
}

function bp_translate_create_pot_indicator(elem, potfile) {
	elem.blur();
	new Ajax.Request('<?php echo BP_TRANSLATE_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'bp_translate_po_create_pot_indicator',
				potfile: potfile
			},
			onSuccess: function(transport) {
				window.location.reload();
			},
			onFailure: function(transport) {
				bp_translate_show_error(transport.responseText);
			}
		}
	); 
	return false;	
}

/* TODO: implement context sensitive help 
function bp_translate_process_online_help(event) {
	if (event) {
		if (event.keyCode == 112) {
			Event.stop(event);
			//TODO: launch appropriated help ajax here for none IE
			return false;
		}
	}else{
		//TODO: launch appropriated help ajax here for IE
		return false;
	}
	return true;
}

function bp_translate_term_help_key(event) {
	if(event.keyCode == 112) {
		Event.stop(event);
		return false;
	}
	return true;
}

if (Prototype.Browser.IE) {
	document.onhelp = bp_translate_process_online_help;
}else{
	document.observe("keydown", bp_translate_process_online_help);
}
document.observe("keyup", bp_translate_term_help_key);
document.observe("keypress", bp_translate_term_help_key);
*/

/* ]]> */
</script>
<?php	
}

//////////////////////////////////////////////////////////////////////////////////////////
//	stylesheet handling during direct plugin file call
//////////////////////////////////////////////////////////////////////////////////////////
if (isset($_GET['css']) && $_GET['css'] == 'default') {
	header("Content-Type: text/css");
?>
/* general usage */
.clickable { cursor: pointer; }
.regexp { padding:0;display:block;width:16px;height:16px;background-image:url(../../images/l10n/regexp.gif);background-repeat: none; background-position: left top; }
.regexp:hover { background-image:url(../../images/l10n/regexp-hover.gif); }
.pot-folder { padding-left: 20px; background: url(../../images/l10n/folder.gif) no-repeat 0 2px; }
.bp-translate-filetype-po, .bp-translate-filetype-po-r, .bp-translate-filetype-po-rw, 
.bp-translate-filetype-mo, .bp-translate-filetype-mo-r, .bp-translate-filetype-mo-rw { cursor: default; display:block; float: left; margin-top: 2px; height: 12px; width: 18px;}
.bp-translate-filetype-po { background: url(../../images/l10n/po.gif) no-repeat 0 0; }
.bp-translate-filetype-po-r { cursor: pointer !important; background: url(../../images/l10n/po.gif) no-repeat -18px 0; }
.bp-translate-filetype-po-rw { background: url(../../images/l10n/po.gif) no-repeat -36px 0; }
.bp-translate-filetype-mo { margin-left: 5px; background: url(../../images/l10n/mo.gif) no-repeat 0 0; }
.bp-translate-filetype-mo-r { cursor: pointer !important; margin-left: 5px; background: url(../../images/l10n/mo.gif) no-repeat -18px 0; }
.bp-translate-filetype-mo-rw { margin-left: 5px; background: url(../../images/l10n/mo.gif) no-repeat -36px 0; }

/* overview page styles */
#the-gettext-list table.widefat { border-color: #aaa; }
tr.bp-translate-inactive { background-color: #eee; }
tr.bp-translate-inactive table.widefat tr { background-color: #fafafa; }
tr.bp-translate-active { background-color: #fff; }
*:first-child + html tr.bp-translate-active td { background-color: #fff; }
.bp-translate-type-name { margin: 0pt 10px 1em 0pt; }
.bp-translate-type-info {}
table.bp-translate-type-info td { padding:0; border-bottom: 0px; }
table.bp-translate-type-info td.bp-translate-info-value { padding:0 5px; }
table.mo-list td { padding: 5px; }
table.mo-list tr.mo-list-head td, table.mo-list tr.mo-list-desc td { border-bottom: 1px solid #ccc !important; }
.bp-translate-ta-right { text-align: right; }
tr.mo-file td { border-bottom: 1px solid #ddd !important; }
tr.mo-file:hover td { background-color: #ffc !important; }

/* new ajax dialogs */
#TB_ajaxContent { background-color: #EAF3FA !important; width: auto !important; overflow: hidden !important; }
#TB_ajaxContent.TB_modal { padding: 0px; }
#bp-translate-dialog-header { background-color:#222 !important; margin:0; padding:0px 2px; color:#D7D7D7; height:20px; font-size:13px; }
#bp-translate-dialog-header img { width: 16px; height:16px; padding-top: 2px;}
#bp-translate-dialog-caption { padding: 1px 0 0 5px; }
#TB_window a.google:hover { color: #D54E21 !important; }

/* catalog editor styles */
#catalog-body a { cursor: pointer; }
#catalog-body td { overflow: hidden; }
#catalog-body tr.odd { background-color: #eee; }
*:first-child + html #catalog-body tr.odd td { background-color: #eee; }
#catalog-body tr.highlight-editing { background-color: #FFF36F !important; }
*:first-child + html #catalog-body tr.highlight-editing td { background-color: #FFF36F !important; }
#catalog-body .bp-translate-pl-form { padding-top: 5px; font-weight: bold; color:#aaa; display:block; border-bottom: 1px dotted #ccc; }
#bp-translate-filter-search, #bp-translate-filter-regexp { font-weight: bold; color: #FF0000; }
.page-numbers { cursor: pointer; }
#php-files a, .subsubsub a.bp-translate-filter { cursor: pointer; }
#php-files { padding: 3px; border: 1px solid #ccc; overflow:auto; height: 100px;}

/* file and comment tooltip */
.bp-translate-msg-tip span { display: none; }
.bp-translate-msg-tip:hover span { display:block; position: absolute; z-index:50; margin-top: -5px; padding: 3px; background-color:#FFF79F; border: solid 1px #333; color:black; }
*:first-child + html .bp-translate-msg-tip span { margin: 10px 0 0 -26px !important; }
.bp-translate-msg-tip:hover span strong { margin-bottom: 3px; border-bottom: dotted 1px #333; display:block; cursor: default; }
.bp-translate-msg-tip:hover span em { font-style: normal; color: #328AB2; }
.bp-translate-msg-tip:hover span em:hover { color: #D54E21; }

#po-hdr { border-top: 1px dotted #ccc;}
.po-header-toggle { margin: 10px 0 0 0; padding-left:20px; cursor: pointer; background: transparent url('../../images/l10n/expand.gif') 0 3px no-repeat; }
.po-header-collapse { background: transparent url('../../images/l10n/collapse.gif') 0 3px no-repeat; margin-bottom: 3px;}
.po-hdr-key { font-family: monospace; font-size: 11px; font-weight:bold; }
.po-hdr-val { font-family: monospace; font-size: 11px; padding-left: 10px; }

<?php
}

?>