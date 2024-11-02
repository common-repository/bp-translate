<?php 

/* BP Translate Services */

// generate public key
$bp_translate_services_public_key = '-----BEGIN PUBLIC KEY-----|MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDNccmB4Up9V9+vD5kWWiE6zpRV|m7y1sdFihreycdpmu3aPjKooG5LWUbTTyc993nTxV71SKuuYdkPzu5JxniAsI2N0|7DsySZ/bQ2/BEANNwJD3pmz4NmIHgIeNaUze/tvTZq6m+FTVHSvEqAaXJIsQbO19|HeegbfEpmCj1d/CgOwIDAQAB|-----END PUBLIC KEY-----|';

// check schedule
if (!wp_next_scheduled('bp_translate_services_cron_hook')) {
	wp_schedule_event( time(), 'hourly', 'bp_translate_services_cron_hook' );
}

define('BP_TRANSLATE_SERVICES_FAST_TIMEOUT',						10);
define('BP_TRANSLATE_SERVICES_VERIFY',								'verify');
define('BP_TRANSLATE_SERVICES_GET_SERVICES',						'get_services');
define('BP_TRANSLATE_SERVICES_INIT_TRANSLATION',					'init_translation');
define('BP_TRANSLATE_SERVICES_RETRIEVE_TRANSLATION',				'retrieve_translation');
define('BP_TRANSLATE_SERVICES_STATE_OPEN',							'open');
define('BP_TRANSLATE_SERVICES_STATE_ERROR',							'error');
define('BP_TRANSLATE_SERVICES_STATE_CLOSED',						'closed');
define('BP_TRANSLATE_SERVICES_ERROR_INVALID_LANGUAGE',				'BP_TRANSLATE_SERVICES_ERROR_INVALID_LANGUAGE');
define('BP_TRANSLATE_SERVICES_ERROR_NOT_SUPPORTED_LANGUAGE',		'BP_TRANSLATE_SERVICES_ERROR_NOT_SUPPORTED_LANGUAGE');
define('BP_TRANSLATE_SERVICES_ERROR_INVALID_SERVICE',				'BP_TRANSLATE_SERVICES_ERROR_INVALID_SERVICE');
define('BP_TRANSLATE_SERVICES_ERROR_INVALID_ORDER',					'BP_TRANSLATE_SERVICES_ERROR_INVALID_ORDER');
define('BP_TRANSLATE_SERVICES_ERROR_SERVICE_GENERIC',				'BP_TRANSLATE_SERVICES_ERROR_SERVICE_GENERIC');
define('BP_TRANSLATE_SERVICES_ERROR_SERVICE_UNKNOWN',				'BP_TRANSLATE_SERVICES_ERROR_SERVICE_UNKNOWN');
define('BP_TRANSLATE_SERVICES_DEBUG',								'BP_TRANSLATE_SERVICES_DEBUG');

// error messages
$bp_translate_services_error_messages[BP_TRANSLATE_SERVICES_ERROR_INVALID_LANGUAGE] =			__('The language/s do not have a valid ISO 639-1 representation.','bp-translate');
$bp_translate_services_error_messages[BP_TRANSLATE_SERVICES_ERROR_NOT_SUPPORTED_LANGUAGE] =		__('The language/s you used are not supported by the service.','bp-translate');
$bp_translate_services_error_messages[BP_TRANSLATE_SERVICES_ERROR_INVALID_SERVICE] =			__('There is no such service.','bp-translate');
$bp_translate_services_error_messages[BP_TRANSLATE_SERVICES_ERROR_INVALID_ORDER] =				__('The system could not process your order.','bp-translate');
$bp_translate_services_error_messages[BP_TRANSLATE_SERVICES_ERROR_SERVICE_GENERIC] =			__('There has been an error with the selected service.','bp-translate');
$bp_translate_services_error_messages[BP_TRANSLATE_SERVICES_ERROR_SERVICE_UNKNOWN] =			__('An unknown error occured with the selected service.','bp-translate');
$bp_translate_services_error_messages[BP_TRANSLATE_SERVICES_DEBUG] =							__('The server returned a debugging message.','bp-translate');

// hooks
add_action('bp_translate_services_admin_page',		'bp_translate_services_service');
add_action('bp_translate_css',						'bp_translate_services_css');
add_action('bp_translate_services_cron_hook',		'bp_translate_services_cron');
add_action('bp_translate_configuration',			'bp_translate_services_config_hook');
add_action('bp_translate_load_config',				'bp_translate_services_load');
add_action('bp_translate_save_settings_site',				'bp_translate_services_save');
add_action('bp_translate_clean_uri',				'bp_translate_services_clean_uri');
add_action('admin_menu',							'bp_translate_services_init');

add_filter('manage_order_columns',					'bp_translate_services_order_columns');
add_filter('bp_translate_configuration_pre',		'bp_translate_services_config_pre_hook');

// serializing/deserializing functions
function bp_translate_services_base64_serialize($var) {
	if(is_array($var)) {
		foreach($var as $key => $value) {
			$var[$key] = bp_translate_services_base64_serialize($value);
		}
	}
	$var = serialize($var);
	$var = strtr(base64_encode($var), '-_,', '+/=');
	return $var;
}

function bp_translate_services_base64_unserialize($var) {
	$var = base64_decode(strtr($var, '-_,', '+/='));
	$var = unserialize($var);
	if(is_array($var)) {
		foreach($var as $key => $value) {
			$var[$key] = bp_translate_services_base64_unserialize($value);
		}
	}
	return $var;
}

// sends a encrypted message to BP Translate Services and decrypts the received data
function bp_translate_services_queryQS($action, $data='', $fast = false) {
	global $bp_translate_services_public_key;
	// generate new private key
	$key = openssl_pkey_new();
	openssl_pkey_export($key, $private_key);
	$public_key=openssl_pkey_get_details($key);
	$public_key=$public_key["key"];
	$message = bp_translate_services_base64_serialize(array('key'=>$public_key, 'data'=>$data));
	openssl_seal($message, $message, $server_key, array($bp_translate_services_public_key));
	$message = bp_translate_services_base64_serialize(array('key'=>$server_key[0], 'data'=>$message));
	$data = "message=".$message;
	
	// connect to qts
	if($fast) {
		$fp = fsockopen('www.qianqin.de', 80, $errno, $errstr, BP_TRANSLATE_SERVICES_FAST_TIMEOUT);
		stream_set_timeout($fp, BP_TRANSLATE_SERVICES_FAST_TIMEOUT);
	} else {
		$fp = fsockopen('www.qianqin.de', 80);
	}
	if(!$fp) return false;
	
	fputs($fp, "POST /bp-translate/services/$action HTTP/1.1\r\n");
	fputs($fp, "Host: www.qianqin.de\r\n");
	fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
	fputs($fp, "Content-length: ". strlen($data) ."\r\n");
	fputs($fp, "Connection: close\r\n\r\n");
	fputs($fp, $data);
	$res = '';
	while(!feof($fp)) {
		$res .= fgets($fp, 128);
	}
	// check for timeout
	$info = stream_get_meta_data($fp);
	if($info['timed_out']) return false;
	fclose($fp);
	
	preg_match("#^Content-Length:\s*([0-9]+)\s*$#ism",$res, $match);
	$content_length = $match[1];
	$content = substr($res, -$content_length, $content_length);
	$debug = $content;
	$content = bp_translate_services_base64_unserialize($content);
	openssl_open($content['data'], $content, $content['key'], $private_key);
	if($content===false) {
		echo "<pre>DEBUG:\n";
		echo $debug;
		echo "</pre>";
	}
	openssl_free_key($key);
	return bp_translate_services_cleanup(bp_translate_services_base64_unserialize($content), $action);
}

function bp_translate_services_clean_uri($clean_uri) {
	return preg_replace("/&(bp_translate_services_delete|bp_translate_services_cron)=[^&#]*/i","",$clean_uri);
}

function bp_translate_services_translateButtons($available_languages, $missing_languages) {
	global $bp_translate, $post;
	if(sizeof($missing_languages)==0) return;
	$missing_languages_name = array();
	foreach($missing_languages as $language) {
		$missing_languages_name[] = '<a href="edit.php?page=bp_translate_services&post='.$post->ID.'&target_language='.$language.'">'.$bp_translate['language_name'][$language].'</a>';
	}
	$missing_languages_names = join(', ', $missing_languages_name);
	printf(__('<div>Translate to %s</div>', 'bp-translate') ,$missing_languages_names);
}

function bp_translate_services_css() {
	echo "#bp_translate_services_content_preview { width:100%; height:200px }";
	echo ".service_description { margin-left:20px; margin-top:0 }";
	echo "#bp_translate-services h4 { margin-top:0 }";
	echo "#bp_translate-services h5 { margin-bottom:0 }";
	echo "#bp_translate-services .description { font-size:11px }";
	echo "#bp_translate_select_translate { margin-right:11px }";
	echo ".bp_translate_services_status { border:0 }";
	echo ".bp_translate_services_no-bottom-border { border-bottom:0 !important }";
}

function bp_translate_services_load() {
	global $bp_translate, $bp_translate_services_public_key;
	$bp_translate_services = get_site_option('bp_translate_bp_translate_services');
	$bp_translate_services = bp_translate_validate_bool($bp_translate_services, $bp_translate['bp_translate_services']);
	$bp_translate['bp_translate_services'] = $bp_translate_services && function_exists('openssl_get_publickey');
	if($bp_translate['bp_translate_services'] && is_string($bp_translate_services_public_key)) {
		$bp_translate_services_public_key = openssl_get_publickey(join("\n",explode("|",$bp_translate_services_public_key)));
	}
}

function bp_translate_services_init() {
	global $bp_translate;
	if($bp_translate['bp_translate_services']) {
	/* disabled for meta box
		add_filter('bp_translate_toolbar',			'bp_translate_services_toobar');
		add_filter('bp_translate_modify_editor_js',	'bp_translate_services_editor_js');
	*/
		add_meta_box('translatediv', __('Translate to','bp-translate'), 'bp_translate_services_translate_box', 'post', 'side', 'core');
		add_meta_box('translatediv', __('Translate to','bp-translate'), 'bp_translate_services_translate_box', 'page', 'side', 'core');
		
		add_action('bp_translate_language_column',			'bp_translate_services_translateButtons', 10, 2);
	}
}

function bp_translate_services_save() {
	global $bp_translate;
	if($bp_translate['bp_translate_services'])
		update_site_option('bp_translate_bp_translate_services', '1');
	else
		update_site_option('bp_translate_bp_translate_services', '0');
}

function bp_translate_services_cleanup($var, $action) {
	switch($action) {
		case BP_TRANSLATE_SERVICES_GET_SERVICES:
			foreach($var as $service_id => $service) {
				// make array out ouf serialized field
				$fields = array();
				$required_fields = explode('|',$service['service_required_fields']);
				foreach($required_fields as $required_field) {
					list($fieldname, $title) = explode(' ', $required_field, 2);
					if($fieldname!='') {
						$fields[] = array('name' => $fieldname, 'value' => '', 'title' => $title);
					}
				}
				$var[$service_id]['service_required_fields'] = $fields;
			}
		break;
	}
	if(isset($var['error']) && $var['error'] == BP_TRANSLATE_SERVICES_DEBUG) {
		echo "<pre>Debug message received from Server: \n";
		var_dump($var['message']);
		echo "</pre>";
	}
	return $var;
}

function bp_translate_services_config_pre_hook($message) {
	global $bp_translate;
	if(isset($_POST['default_language'])) {
		bp_translate_check_setting('bp_translate_services', true, BP_TRANSLATE_BOOLEAN);
		bp_translate_services_load();
		if($bp_translate['bp_translate_services']) {
			$services = bp_translate_services_queryQS(BP_TRANSLATE_SERVICES_GET_SERVICES);
			$service_settings = get_site_option('bp_translate_services_service_settings');
			if(!is_array($service_settings)) $service_settings = array();
			
			foreach($services as $service_id => $service) {
				// check if there are already settings for the field
				if(!is_array($service_settings[$service_id])) $service_settings[$service_id] = array();
				
				// update fields
				foreach($service['service_required_fields'] as $field) {
					if(isset($_POST['bp_translate_services_'.$service_id.'_'.$field['name']])) {
						// skip empty passwords to keep the old value
						if($_POST['bp_translate_services_'.$service_id.'_'.$field['name']]=='' && $field['name']=='password') continue;
						$service_settings[$service_id][$field['name']] = $_POST['bp_translate_services_'.$service_id.'_'.$field['name']];
					}
				}
			}
			update_site_option('bp_translate_services_service_settings', $service_settings);
		}
	}
	if(isset($_GET['bp_translate_services_delete'])) {
		$_GET['bp_translate_services_delete'] = intval($_GET['bp_translate_services_delete']);
		$orders = get_site_option('bp_translate_services_orders');
		if(is_array($orders)) {
			foreach($orders as $key => $order) {
				if($orders[$key]['order']['order_id'] == $_GET['bp_translate_services_delete']) {
					unset($orders[$key]);
					update_site_option('bp_translate_services_orders',$orders);
				}
			}
		}
		$message = __('Order deleted.','bp-translate');
	}
	if(isset($_GET['bp_translate_services_cron'])) {
		bp_translate_services_cron();
		$message = __('Status updated for all open orders.','bp-translate');
	}
	return $message;
}

function bp_translate_services_translate_box($post) {
	global $bp_translate;
	$languages = bp_translate_get_sorted_languages();
?>
<p>
	<ul>
<?php
	foreach($languages as $language) {
?>
		<li><img src="<?php echo trailingslashit(WP_CONTENT_URL).$bp_translate['flag_location'].$bp_translate['flag'][$language]; ?>" alt="<?php echo $bp_translate['language_name'][$language]; ?>"> <a href="edit.php?page=bp_translate_services&post=<?php echo intval($_REQUEST['post']); ?>&target_language=<?php echo $language; ?>"><?php echo $bp_translate['language_name'][$language]; ?></li>
<?php
	}
?>
	</ul>
</p>
<?php
}

function bp_translate_services_order_columns($columns) {
	return array(
				'title' => __('Post Title', 'bp-translate'),
				'service' => __('Service', 'bp-translate'),
				'source_language' => __('Source Language', 'bp-translate'),
				'target_language' => __('Target Language', 'bp-translate'),
				'action' => __('Action', 'bp-translate')
				);
}

function bp_translate_services_config_hook($request_uri) {
	global $bp_translate;
	return;
?>
<h3><?php _e('BP Translate Services Settings', 'bp-translate') ?><span id="bp_translate-show-services" style="display:none"> (<a name="bp_translate_service_settings" href="#bp_translate_service_settings" onclick="showServices();"><?php _e('Show', 'bp-translate'); ?></a>)</span></h3>
<table class="form-table" id="bp_translate-services">
	<tr>
		<th scope="row"><?php _e('BP Translate Services', 'bp-translate') ?></th>
		<td>
			<?php if(!function_exists('openssl_get_publickey')) { printf(__('<div id="message" class="error fade"><p>BP Translate Services could not load <a href="%s">OpenSSL</a>!</p></div>'), 'http://www.php.net/manual/book.openssl.php'); } ?>
			<label for="bp_translate_services"><input type="checkbox" name="bp_translate_services" id="bp_translate_services" value="1"<?php echo ($bp_translate['bp_translate_services'])?' checked="checked"':''; ?>/> <?php _e('Enable BP Translate Services', 'bp-translate'); ?></label>
			<br/>
			<?php _e('With BP Translate Services, you will be able to use professional human translation services with a few clicks. (Requires OpenSSL)', 'bp-translate'); ?><br />
			<?php _e('Save after enabling to see more Configuration options.', 'bp-translate'); ?>
		</td>
	</tr>
<?php 
	if($bp_translate['bp_translate_services']) { 
		$service_settings = get_site_option('bp_translate_services_service_settings');
		$services = bp_translate_services_queryQS(BP_TRANSLATE_SERVICES_GET_SERVICES);
		$orders = get_site_option('bp_translate_services_orders');
?>
	<tr valign="top">
		<th scope="row"><h4><?php _e('Open Orders', 'bp-translate'); ?></h4></th>
		<td>
<?php if(is_array($orders) && sizeof($orders)>0) { ?>
			<table class="widefat">
				<thead>
				<tr>
<?php print_column_headers('order'); ?>
				</tr>
				</thead>

				<tfoot>
				<tr>
<?php print_column_headers('order', false); ?>
				</tr>
				</tfoot>
<?php
		foreach($orders as $order) { 
			$post = &get_post($order['post_id']);
			if(!$post) continue;
			$post->post_title = wp_specialchars(bp_translate_use_current_language_if_not_found_use_default_language($post->post_title));
?>
				<tr>
					<td class="bp_translate_services_no-bottom-border"><a href="post.php?action=edit&post=<?php echo $order['post_id']; ?>" title="<?php printf(__('Edit %s', 'bp-translate'),$post->post_title); ?>"><?php echo $post->post_title; ?></a></td>
					<td class="bp_translate_services_no-bottom-border"><a href="<?php echo $services[$order['service_id']]['service_url']; ?>" title="<?php _e('Website', 'bp-translate'); ?>"><?php echo $services[$order['service_id']]['service_name']; ?></a></td>
					<td class="bp_translate_services_no-bottom-border"><?php echo $bp_translate['language_name'][$order['source_language']]; ?></td>
					<td class="bp_translate_services_no-bottom-border"><?php echo $bp_translate['language_name'][$order['target_language']]; ?></td>
					<td class="bp_translate_services_no-bottom-border"><a class="delete" href="<?php echo add_query_arg('bp_translate_services_delete', $order['order']['order_id'], $request_uri); ?>#bp_translate_service_settings">Delete</a></td>
				</tr>
<?php 
			if(isset($order['status'])) {
?>
				<tr class="bp_translate_services_status">
					<td colspan="5">
						<?php printf(__('Current Status: %s','bp-translate'), $order['status']); ?>
					</td>
				</tr>
<?php
			}
		}
?>
			</table>
			<p><?php printf(__('BP Translate Services will automatically check every hour whether the translations are finished and update your posts accordingly. You can always <a href="%s">check manually</a>.','bp-translate'),'options-general.php?page=bp_translate&bp_translate_services_cron=true#bp_translate_service_settings'); ?></p>
			<p><?php _e('Deleting an open order doesn\'t cancel it. You will have to logon to the service homepage and cancel it there.','bp-translate'); ?></p>
<?php } else { ?>
			<p><?php _e('No open orders.','bp-translate'); ?></p>
<?php } ?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" colspan="2">
			<h4><?php _e('Service Configuration', 'bp-translate');?></h4>
			<p class="description"><?php _e('Below, you will find configuration settings for BP Translate Service Providers, which are required for them to operate.', 'bp-translate'); ?></p>
		</th>
	</tr>
<?php
		foreach($services as $service) {
			if(sizeof($service['service_required_fields'])>0) {
?>
	<tr valign="top">
		<th scope="row" colspan="2">
			<h5><?php _e($service['service_name']);?> ( <a name="bp_translate_services_service_<?php echo $service['service_id']; ?>" href="<?php echo $service['service_url']; ?>"><?php _e('Website', 'bp-translate'); ?></a> )</h5>
			<p class="description"><?php _e($service['service_description']); ?></p>
		</th>
	</tr>
<?php
				foreach($service['service_required_fields'] as $field) {
?>
	<tr valign="top">
		<th scope="row"><?php echo $field['title']; ?></th>
		<td>
			<input type="<?php echo ($field['name']=='password')?'password':'text';?>" name="<?php echo 'bp_translate_services_'.$service['service_id']."_".$field['name']; ?>" value="<?php echo (isset($service_settings[$service['service_id']][$field['name']])&&$field['name']!='password')?$service_settings[$service['service_id']][$field['name']]:''; ?>" style="width:100%"/>
		</td>
	</tr>
<?php
				}
			}
		}
	}
?>
</table>
<script type="text/javascript">
// <![CDATA[
	function showServices() {
		document.getElementById('bp_translate-services').style.display='block';
		document.getElementById('bp_translate-show-services').style.display='none';
		return false;
	}
	
	if(location.hash!='#bp_translate_service_settings') {
	document.getElementById('bp_translate-show-services').style.display='inline';
	document.getElementById('bp_translate-services').style.display='none';
	}
// ]]>
</script>
<?php
}

function bp_translate_services_cron() {
	global $wpdb;
	// poll translations
	$orders = get_site_option('bp_translate_services_orders');
	if(!is_array($orders)) return;
	foreach($orders as $key => $order) {
		bp_translate_services_UpdateOrder($order['order']['order_id']);
	}
}

function bp_translate_services_UpdateOrder($order_id) {
	global $wpdb;
	$orders = get_site_option('bp_translate_services_orders');
	if(!is_array($orders)) return false;
	foreach($orders as $key => $order) {
		// search for wanted order
		if($order['order']['order_id']!=$order_id) continue;
		
		// query server for updates
		$order['order']['order_url'] = get_option('home');
		$result = bp_translate_services_queryQS(BP_TRANSLATE_SERVICES_RETRIEVE_TRANSLATION, $order['order']);
		$orders[$key]['status'] = $result['order_comment'];
		// update db if post is updated
		if(isset($result['order_status']) && $result['order_status']==BP_TRANSLATE_SERVICES_STATE_CLOSED) {
			$order['post_id'] = intval($order['post_id']);
			$post = &get_post($order['post_id']);
			$title = bp_translate_split($post->post_title);
			$content = bp_translate_split($post->post_content);
			$title[$order['target_language']] = $result['order_translated_title'];
			$content[$order['target_language']] = $result['order_translated_text'];
			$post->post_title = bp_translate_join($title);
			$post->post_content = bp_translate_join($content);
			$wpdb->show_errors();
			$wpdb->query('UPDATE '.$wpdb->posts.' SET post_title="'.mysql_escape_string($post->post_title).'", post_content = "'.mysql_escape_string($post->post_content).'" WHERE ID = "'.$post->ID.'"');
			wp_cache_add($post->ID, $post, 'posts');
			unset($orders[$key]);
		}
		update_site_option('bp_translate_services_orders',$orders);
		return true;
	}
	return false;
}

function bp_translate_services_service() {
	global $bp_translate, $bp_translate_services_public_key, $bp_translate_services_error_messages;
	$post_id = intval($_REQUEST['post']);
	if(bp_translate_is_enabled($_REQUEST['source_language']))
		$translate_from = $_REQUEST['source_language'];
	if(bp_translate_is_enabled($_REQUEST['target_language']))
		$translate_to = $_REQUEST['target_language'];
	if($translate_to == $translate_from) $translate_to = '';
	$post = &get_post($post_id);
	if(!$post) {
		printf(__('Post with id "%s" not found!','bp-translate'), $post_id);
		return;
	}
	$default_service = intval(get_site_option('bp_translate_services_default_service'));
	$service_settings = get_site_option('bp_translate_services_service_settings');
	// Detect available Languages and possible target languages
	$available_languages = bp_translate_get_available_languages($post->post_content);
	if(sizeof($available_languages)==0) {
		$error = __('The requested Post has no content, no Translation possible.', 'bp-translate');
	}
	
	// try to guess source and target language
	if(!in_array($translate_from, $available_languages)) $translate_from = '';
	$missing_languages = array_diff($bp_translate['enabled_languages'], $available_languages);
	$default_language = bp_translate_get_default_language();
	if(empty($translate_from) && in_array($default_language, $available_languages) && $translate_to!=$default_language) $translate_from = $default_language;
	if(empty($translate_to) && sizeof($missing_languages)==1) $translate_to = $missing_languages[0];
	if(in_array($translate_to, $available_languages)) {
		$message = __('The Post already has content for the selected target language. If a translation request is send, the current text for the target language will be overwritten.','bp-translate');
	}
	if(sizeof($available_languages)==1) {
		if($available_languages[0] == $translate_to) {
			$translate_to = '';
		}
		$translate_from = $available_languages[0];
	} elseif($translate_from == '' && sizeof($available_languages) > 1) {
		$languages = bp_translate_get_sorted_languages();
		foreach($languages as $language) {
			if($language != $translate_to && in_array($language, $available_languages)) {
				$translate_from = $language;
				break;
			}
		}
	}
	
	
	// link to current page with get variables
	$url_link = add_query_arg('post', $post_id);
	if(!empty($translate_to)) $url_link = add_query_arg('target_language', $translate_to, $url_link);
	if(!empty($translate_from)) $url_link = add_query_arg('source_language', $translate_from, $url_link);
	
	// get correct title and content
	$post_title = bp_translate_use($translate_from,$post->post_title);
	$post_content = bp_translate_use($translate_from,$post->post_content);
	if(isset($translate_from) && isset($translate_to)) {
		$title = sprintf('Translate &quot;%1$s&quot; from %2$s to %3$s', htmlspecialchars($post_title), $bp_translate['language_name'][$translate_from], $bp_translate['language_name'][$translate_to]);
	} elseif(isset($translate_from)) {
		$title = sprintf('Translate &quot;%1$s&quot; from %2$s', htmlspecialchars($post_title), $bp_translate['language_name'][$translate_from]);
	} else {
		$title = sprintf('Translate &quot;%1$s&quot;', htmlspecialchars($post_title));
	}
	
	// Check data
	
	if(isset($_POST['service_id'])) {
		$service_id = intval($_POST['service_id']);
		$default_service = $service_id;
		update_site_option('bp_translate_services_default_service', $service_id);
		$order_key = substr(md5(time().AUTH_KEY),0,20);
		$request = array(
				'order_service_id' => $service_id,
				'order_url' => get_option('home'),
				'order_key' => $order_key,
				'order_title' => $post_title,
				'order_text' => $post_content,
				'order_source_language' => $translate_from,
				'order_source_locale' => $bp_translate['locale'][$translate_from],
				'order_target_language' => $translate_to,
				'order_target_locale' => $bp_translate['locale'][$translate_to]
			);
		// check for additional fields
		if(is_array($service_settings[$service_id])) {
			$request['order_required_field'] = array();
			foreach($service_settings[$service_id] as $setting => $value) {
				$request['order_required_field'][$setting] = $value;
			}
		}
		$answer = bp_translate_services_queryQS(BP_TRANSLATE_SERVICES_INIT_TRANSLATION, $request);
		if(isset($answer['error'])) {
			$error = sprintf(__('An error occured: %s', 'bp-translate'), $bp_translate_services_error_messages[$answer['error']]);
			if($answer['message']!='') {
				$error.='<br />'.sprintf(__('Additional information: %s', 'bp-translate'), bp_translate_use_current_language_if_not_found_use_default_language($answer['message']));
			}
		}
		if(isset($answer['order_id'])) {
			$orders = get_site_option('bp_translate_services_orders');
			if(!is_array($orders)) $orders = array();
			$orders[] = array('post_id'=>$post_id, 'service_id' => $service_id, 'source_language'=>$translate_from, 'target_language'=>$translate_to, 'order' => array('order_key' => $order_key, 'order_id' => $answer['order_id']));
			update_site_option('bp_translate_services_orders', $orders);
			if(empty($answer['message'])) {
				$order_completed_message = '';
			} else {
				$order_completed_message = htmlspecialchars($answer['message']);
			}
			bp_translate_services_UpdateOrder($answer['order_id']);
		}
	}
	if(isset($error)) {
?>
<div class="wrap">
<h2><?php _e('BP Translate Services', 'bp-translate'); ?></h2>
<div id="message" class="error fade"><p><?php echo $error; ?></p></div>
<p><?php printf(__('An serious error occured and BP Translate Services cannot proceed. For help, please visit the <a href="%s">Support Forum</a>','bp-translate'), 'http://www.qianqin.de/bp-translate/forum/');?></p>
</div>
<?php
	return;
	}
	if(isset($order_completed_message)) {
?>
<div class="wrap">
<h2><?php _e('BP Translate Services', 'bp-translate'); ?></h2>
<div id="message" class="updated fade"><p><?php _e('Order successfully sent.', 'bp-translate'); ?></p></div>
<p><?php _e('Your translation order has been successfully transfered to the selected service.','bp-translate'); ?></p>
<?php
		if(!empty($order_completed_message)) {
?>
<p><?php printf(__('The service returned this message: %s','bp-translate'), $order_completed_message);?></p>
<?php
		}
?>
<p><?php _e('Feel free to choose an action:', 'bp-translate'); ?></p>
<ul>
	<li><a href="<?php echo add_query_arg('target_language', null, $url_link); ?>"><?php _e('Translate this post to another language.', 'bp-translate'); ?></a></li>
	<li><a href="edit.php"><?php _e('Translate a different post.', 'bp-translate'); ?></a></li>
	<li><a href="options-general.php?page=bp_translate#bp_translate_service_settings"><?php _e('View all open orders.', 'bp-translate'); ?></a></li>
	<li><a href="options-general.php?page=bp_translate&bp_translate_services_cron=true#bp_translate_service_settings"><?php _e('Let BP Translate Services check if any open orders are finished.', 'bp-translate'); ?></a></li>
	<li><a href="<?php echo get_permalink($post_id); ?> "><?php _e('View this post.', 'bp-translate'); ?></a></li>
</ul>
</div>
<?php
		return;
	}
?>
<div class="wrap">
<h2><?php _e('BP Translate Services', 'bp-translate'); ?></h2>
<?php
if(!empty($message)) {
?>
<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php
}
?>
<h3><?php echo $title;?></h3>
<form action="edit.php?page=bp_translate_services" method="post" id="bp_translate-services-translate">
<p><?php
	if(sizeof($available_languages)>1) {
		$available_languages_name = array();
		foreach(array_diff($available_languages,array($translate_from)) as $language) {
			$available_languages_name[] = '<a href="'.add_query_arg('source_language',$language, $url_link).'">'.$bp_translate['language_name'][$language].'</a>';
		}
		$available_languages_names = join(", ", $available_languages_name);
		printf(__('Your article is available in multiple languages. If you do not want to translate from %1$s, you can switch to one of the following languages: %2$s', 'bp-translate'),$bp_translate['language_name'][$translate_from],$available_languages_names);
	}
?></p>
<input type="hidden" name="post" value="<?php echo $post_id; ?>"/>
<input type="hidden" name="source_language" value="<?php echo $translate_from; ?>"/>
<?php
	if(empty($translate_to)) {
?>
<p><?php _e('Please choose the language you want to translate to:', 'bp-translate');?></p>
<ul>
<?php 
		foreach($bp_translate['enabled_languages'] as $language) {
			if($translate_from == $language) continue;
?>
	<li><label><input type="radio" name="target_language" value="<?php echo $language;?>" /> <?php echo $bp_translate['language_name'][$language]; ?></li>
<?php
		}
?>
</ul>
	<p class="submit">
		<input type="submit" name="submit" class="button-primary" value="<?php _e('Continue', 'bp-translate') ?>" />
	</p>
<?php
	} else {
?>
<p><?php printf(__('Please review your article and <a href="%s">edit</a> it if needed.', 'bp-translate'),'post.php?action=edit&post='.$post_id); ?></p>
<textarea name="bp_translate_services_content_preview" id="bp_translate_services_content_preview" readonly="readonly"><?php echo $post_content; ?></textarea>
<?php
		$timestamp = time();
		if($timestamp != bp_translate_services_queryQS(BP_TRANSLATE_SERVICES_VERIFY, $timestamp)) {
?>
<p class="error"><?php _e('ERROR: Could not connect to BP Translate Services. Please try again later.', 'bp-translate');?></p>
<?php
			return;
		}
	
?>
<h4><?php _e('Use the following Translation Service:', 'bp-translate'); ?></h4>
<ul>
<?php
		if($services = bp_translate_services_queryQS(BP_TRANSLATE_SERVICES_GET_SERVICES)) {
			foreach($services as $service_id => $service) {
				// check if we have data for all required fields
				$requirements_matched = true;
				foreach($service['service_required_fields'] as $field) {
					if(!isset($service_settings[$service_id][$field['name']]) || $service_settings[$service_id][$field['name']] == '') $requirements_matched = false;
				}
				if(!$requirements_matched) {
?>
<li>
	<label><input type="radio" name="service_id" disabled="disabled" /> <b><?php echo bp_translate_use_current_language_if_not_found_use_default_language($service['service_name']); ?></b> ( <a href="<?php echo $service['service_url']; ?>" target="_blank"><?php _e('Website', 'bp-translate'); ?></a> )</label>
	<p class="error"><?php printf(__('Cannot use this service, not all <a href="%s">required fields</a> filled in for this service.','bp-translate'), 'options-general.php?page=bp_translate#bp_translate_services_service_'.$service_id); ?></p>
	<p class="service_description"><?php echo bp_translate_use_current_language_if_not_found_use_default_language($service['service_description']); ?></p>
</li>
<?php
				} else {
?>
<li><label><input type="radio" name="service_id" <?php if($default_service==$service['service_id']) echo 'checked="checked"';?> value="<?php echo $service['service_id'];?>" /> <b><?php echo bp_translate_use_current_language_if_not_found_use_default_language($service['service_name']); ?></b> ( <a href="<?php echo $service['service_url']; ?>" target="_blank"><?php _e('Website', 'bp-translate'); ?></a> )</label><p class="service_description"><?php echo bp_translate_use_current_language_if_not_found_use_default_language($service['service_description']); ?></p></li>
<?php
				}
			}
?>
</ul>
<p><?php _e('Your article will be SSL encrypted and securly sent to BP Translate Services, which will forward your text to the chosen Translation Service. Once BP Translate Services receives the translated text, it will automatically appear on your blog.', 'bp-translate'); ?></p>
	<p class="submit">
		<input type="hidden" name="target_language" value="<?php echo $translate_to; ?>"/>
		<input type="submit" name="submit" class="button-primary" value="<?php _e('Request Translation', 'bp-translate') ?>" />
	</p>
<?php
		}
	}
?>
</div>
</form>
<?php
}

function bp_translate_services_toobar($content) {
	// Create Translate Button 
	$content .= bp_translate_create_tinymce_toolbar_button('translate', 'translate', 'init_qs', __('Translate'));
	return $content;
}

function bp_translate_services_editor_js($content) {
	$content .= "
		init_qs = function(action, id) {
			document.location.href = 'edit.php?page=bp_translate_services&post=".intval($_REQUEST['post'])."';
		}
		";
	return $content;
}

?>