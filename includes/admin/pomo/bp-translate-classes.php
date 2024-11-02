<?php

if (!defined('T_ML_COMMENT'))
	define('T_ML_COMMENT', T_COMMENT);
else
	define('T_DOC_COMMENT', T_ML_COMMENT);

class bp_translate_l10n_parser {

	function bp_translate_l10n_parser($basedir, $textdomain, $do_gettext = true, $do_domains=false) {
		$domains = array(
			'load_textdomain',
			'load_theme_textdomain',
			'load_plugin_textdomain'
		);
		$gettext = array(
			'__',
			'_e',
			'_c', //context by |
			'_nc', //context by |
			'__ngettext', '_n',
			'__ngettext_noop', '_n_noop',
			'_x', 		//see "_c" but explicite context
			'_nx', 		//see "_n" but  but additional context,
			'_nx_noop'	//see "_n_noop" but  but additional context,
		);

		$escapements = array(
			'esc_attr__',
			'esc_html__',
			'esc_attr_e',
			'esc_html_e',
			'esc_attr_x'
		); //needed only for checks against developer own functions for gettext like Ozz is using

		$this->textdomain = $textdomain;
		$this->basedir = $basedir;
		$this->filename = '';
		$this->l10n_functions = array();
		$this->buildin_functions = array_merge($gettext, $escapements);

		if ($do_gettext) $this->l10n_functions = array_merge($this->l10n_functions, $gettext);
		if ($do_domains) $this->l10n_functions = array_merge($this->l10n_functions, $domains);

		$this->l10n_regular = '/('.implode('$|', $this->l10n_functions).'$)/';
		$this->l10n_domains = '/('.implode('|',$domains).')/';
	}

	function parseFile($filename) {
		if (file_exists($filename)){
			$this->filename = str_replace($this->basedir, '', $filename);
			$content = file_get_contents($filename);
			return $this->parseString($content);
		}
		return false;
	}

	function parseString($content) {
		$results = array(
			'gettext' 	  => array(),
			'not_gettext' => array()
		);

		$in_func = false;
		$in_domain = false;
		$in_not_gettext = false;
		$args_started = false;
		$parens_balance = 0;

		$tokens = token_get_all($content);

		$cur_not_gettext = false;
		$cur_func = false;
		$cur_full_func = false;
		$cur_translator_hint = false;
		$line_number = 1;
		$cur_match_line = 1;
		$cur_argc = 0;
		$cur_args = array();
		$bad_argc = array();

		foreach($tokens as $token) {
			if (is_array($token)) {
				list($id, $text) = $token;
				if (T_STRING == $id && preg_match($this->l10n_regular, $text, $m)) {
					$in_func = true;
					$in_domain = preg_match($this->l10n_domains, $text);
					$parens_balance = 0;
					$args_started = false;
					$cur_func = $m[1];
					$cur_full_func = $text;
					$token = $text;
				} elseif (T_STRING == $id && $in_func) {
					$bad_argc[] = $cur_argc;  //avoid stacked functions inside parts of required params!
					$token = $text;
				} elseif (T_CONSTANT_ENCAPSED_STRING == $id) {
					if ($in_func && $args_started) {
						if ($text{0} == '"') {
							$text = trim($text, '"');
							$text = str_replace('\"', '"', $text);
							$text = str_replace("\\$", "$", $text);
							$text = str_replace("\r\n", "\n", $text);
							$token = $text;
							$text = str_replace("\\n", "\n", $text);
						}
						else{
							$text = trim($text, "'");
							$text = str_replace("\\'", "'", $text);
							$text = str_replace("\\$", "$", $text);
							$text = str_replace("\r\n", "\n", $text);
							$text = str_replace("\\n", "\n", $text);
							$text = str_replace("\\\\", "\\", $text);
							$token = $text;
						}

						if ( isset( $cur_args[$cur_argc] ) )
							$cur_args[$cur_argc] .= $text;
						else
							$cur_args[$cur_argc] = $text;

						if ($cur_argc == 0) $cur_match_line = $line_number;
					}elseif($in_not_gettext) {
						if ($text{0} == '"') {
							$text = trim($text, '"');
							$text = str_replace('\"', '"', $text);
						}
						else{
							$text = trim($text, "'");
							$text = str_replace("\\'", "'", $text);
						}
						$text = str_replace("\\$", "$", $text);
						$text = str_replace("\r\n", "\n", $text);
						$results['not_gettext'][] = $this->_build_non_gettext($line_number, $cur_not_gettext, $text);
						$cur_not_gettext = false;
						$token = $text;
					}
					else {
						$token = $text;
					}
				} elseif ((T_ML_COMMENT == $id || T_COMMENT == $id) && preg_match('|/\*\s*(/?WP_I18N_[a-z_]+)\s*\*/|i', $text, $matches)) {
					$in_not_gettext = $matches[1]{0} == 'W';
					if ($in_not_gettext) $cur_not_gettext = 'Not gettexted string '.$matches[1];
					$token = $text;
				} elseif ((T_ML_COMMENT == $id || T_COMMENT == $id) && preg_match('/\*\s(translators:.*)\*/i', $text, $matches)) {
					$cur_translator_hint = $matches[1];
					$token = $text;
				} elseif((T_VARIABLE == $id)||(T_OBJECT_OPERATOR == $id)||(T_STRING == $id)) {
					if ($in_func && $in_domain && $args_started) {
						if ( isset( $cur_args[$cur_argc] ) )
							$cur_args[$cur_argc] .= $text;
						else
							$cur_args[$cur_argc] = $text;

					}
					$token = $text;
				}
				else {
					$token = $text;
				}
			} elseif ('(' == $token){
				$args_started = true;
				++$parens_balance;
			} elseif (',' == $token) {
				if ($in_func && $args_started) {
					$cur_argc++;
				}
			} elseif (')' == $token) {
				--$parens_balance;
				if ($in_func && 0 == $parens_balance) {
					if (count($cur_args) && isset($cur_args[0])) {
						//skip those, where all args are variables
						$is_dev_func = !in_array($cur_full_func, $this->buildin_functions);
						$gt = $this->_build_gettext($cur_match_line, $cur_func, $cur_args, $cur_argc, $is_dev_func, $bad_argc);
						if (is_array($gt)) {
							if ($cur_translator_hint !== false) {
								$gt['CC'][] = $cur_translator_hint;
							}
							$results['gettext'][] = $gt;
						}
					}
					$in_func = false;
					$in_domain = false;
					$args_started = false;
					$cur_func = false;
					$cur_full_func = false;
					$cur_translator_hint = false;
					$cur_argc = 0;
					$cur_args = array();
					$bad_argc = array();
				}
			}
			$line_number += substr_count($token, "\n");
		}
		return $results;
	}

	function _build_gettext($line, $func, $args, $argc, $is_dev_func, $bad_argc) {
		$res = array(
			'msgid' => '',
			'R'		=> $this->filename.':'.$line,
			'CC' 	=> array(),
			'LTD'	=> ($is_dev_func ? $this->textdomain : '')
		);
		switch($func) {
			case '__':
				// see also esc_html__
				//see also esc_attr__
				//[0] =>  phrase
				//[1] => textdomain (optional)
				if (in_array(0, $bad_argc)) return null; //error, this can't be a function
				$res['msgid'] = $args[0];
				if (isset($args[1])) $res['LTD'] = trim($args[1]);
				elseif ($argc == 1) $res['LTD'] = $this->textdomain;
			case '_e':
				//see also esc_html_e
				//see also esc_attr_e
				//[0] =>  phrase
				//[1] => textdomain (optional)
				if (in_array(0, $bad_argc)) return null; //error, this can't be a function
				$res['msgid'] = $args[0];
				if (isset($args[1])) $res['LTD'] = trim($args[1]);
				elseif ($argc == 1) $res['LTD'] = $this->textdomain;
			case '_c':
				//[0] =>  phrase
				//[1] => textdomain (optional)
				$res['msgid'] = $args[0];
				if (in_array(0, $bad_argc)) return null; //error, this can't be a function
				if (isset($args[1])) $res['LTD'] = trim($args[1]);
				elseif ($argc == 1) $res['LTD'] = $this->textdomain;
				break;
			case '_x':
				//see "_c" but explicite context
				//se also esc_attr_x
				//[0] =>  phrase
				//[1] =>  context
				//[2] => textdomain (optional)
				if (in_array(0, $bad_argc)) return null; //error, this can't be a function
				if (in_array(1, $bad_argc)) return null; //error, this can't be a function
				$res['msgid'] = $args[1]."\04".$args[0];
				if (isset($args[2])) $res['LTD'] = trim($args[2]);
				elseif ($argc == 2) $res['LTD'] = $this->textdomain;
				break;
			case '__ngettext':
				//[0] =>  phrase singular
				//[1] => phrase plural
				//[2] => number
				//[3] => textdomain (optional)
				if (in_array(0, $bad_argc)) return null; //error, this can't be a function
				if (in_array(1, $bad_argc)) return null; //error, this can't be a function
				$res['msgid'] = $args[0]."\00".$args[1];
				$res['P'] = true;
				if (isset($args[3])) $res['LTD'] = trim($args[3]);
				elseif ($argc == 3) $res['LTD'] = $this->textdomain;
				break;
			case '_n':
				//[0] => phrase singular
				//[1] => phrase plural
				//[2] => number
				//[3] => textdomain (optional)
				if (in_array(0, $bad_argc)) return null; //error, this can't be a function
				if (in_array(1, $bad_argc)) return null; //error, this can't be a function
				$res['msgid'] = $args[0]."\00".$args[1];
				$res['P'] = true;
				if (isset($args[3])) $res['LTD'] = trim($args[3]);
				elseif ($argc == 3) $res['LTD'] = $this->textdomain;
				break;
			case '_nc':
				//[0] => phrase singular
				//[1] => phrase plural
				//[2] => number
				//[3] => textdomain (optional)
				if (in_array(0, $bad_argc)) return null; //error, this can't be a function
				if (in_array(1, $bad_argc)) return null; //error, this can't be a function
				$res['msgid'] = $args[0]."\00".$args[1];
				$res['P'] = true;
				if (isset($args[3])) $res['LTD'] = trim($args[3]);
				elseif ($argc == 3) $res['LTD'] = $this->textdomain;
				break;
			case '_nx':
				//see "_n" but  but additional context,
				//[0] => phrase singular
				//[1] => phrase plural
				//[2] => number
				//[3] => context
				//[4] => textdomain (optional)
				if (in_array(0, $bad_argc)) return null; //error, this can't be a function
				if (in_array(1, $bad_argc)) return null; //error, this can't be a function
				if (in_array(3, $bad_argc)) return null; //error, this can't be a function
				$res['msgid'] = $args[3]."\04".$args[0]."\00".$args[1];
				$res['P'] = true;
				if (isset($args[4])) $res['LTD'] = trim($args[4]);
				elseif ($argc == 4) $res['LTD'] = $this->textdomain;
				break;
			case '__ngettext_noop':
				//[0] =>  phrase singular
				//[1] => phrase plural
				if (in_array(0, $bad_argc)) return null; //error, this can't be a function
				if (in_array(1, $bad_argc)) return null; //error, this can't be a function
				$res['msgid'] = $args[0]."\00".$args[1];
				$res['P'] = true;
				break;
			case '_n_noop':
				//see deprecated __ngettext_noop
				//[0] =>  phrase singular
				//[1] => phrase plural
				if (in_array(0, $bad_argc)) return null; //error, this can't be a function
				if (in_array(1, $bad_argc)) return null; //error, this can't be a function
				$res['msgid'] = $args[0]."\00".$args[1];
				$res['P'] = true;
				break;
			case '_nx_noop':
				//see "_n_noop" but  but additional context,
				//[0] => phrase singular
				//[1] => phrase plural
				//[2] => context
				if (in_array(0, $bad_argc)) return null; //error, this can't be a function
				if (in_array(1, $bad_argc)) return null; //error, this can't be a function
				if (in_array(2, $bad_argc)) return null; //error, this can't be a function
				$res['msgid'] = $args[2]."\04".$args[0]."\00".$args[1];
				$res['P'] = true;
				break;
		}
		return $res;
	}

	function _build_non_gettext($line, $stage, $text) {
		return array(
			'msgid' => $text,
			'R' 	=> $this->filename.':'.$line,
			'CC' 	=> array($stage),
			'LTD'	=> '{php-code}'
		);
	}

}

/*
contribution for performant mo-file reading:    Thomas Urban (www.toxa.de)

fixed arround PHP preg_match bug
references and possible explainations:
	Bug #37793  child pid xxx exit signal Segmentation fault (11) => http://bugs.php.net/bug.php?id=37793
	Bugzilla  Bug 841 => http://bugs.exim.org/show_bug.cgi?id=841
	http://mobile-website.mobi/php-utf8-vs-iso-8859-1-59
 */

class CspStringsAreAscii {
	function _strlen($string) { return strlen($string); }
	function _strpos($haystack, $needle, $offset = null) { return strpos($haystack, $needle, $offset); }
	function _substr($string, $offset, $length = null) { return (is_null($length) ? substr($string, $offset) : substr($string, $offset, $length)); }
	function _str_split($string, $chunkSize) { return str_split($string, $chunkSize); }
}

class CspStringsAreMultibyte {
	function _strlen($string) { return mb_strlen($string, 'ascii'); }
	function _strpos($haystack, $needle, $offset = null) { return mb_strpos($haystack, $needle, $offset, 'ascii'); }
	function _substr($string, $offset, $length = null) { return mb_substr($string, $offset, $length, 'ascii'); }
	function _str_split($string, $chunkSize) {
		//do not! break unicode / uft8 character in the middle of encoding, just at char border
		$length = mb_strlen($string);
		$out = array();
		for ($i=0;$i<$length;$i+=$chunkSize) {
			$out[] = mb_substr($string, $i, $chunkSize);
		}
		return $out;
	}
}

class CspTranslationFile {

	function CspTranslationFile() {
		//now lets check whether overloaded functions been used and provide the correct str_* functions as usual
		if(( ini_get( 'mbstring.func_overload' ) & 2 ) && is_callable( 'mb_substr' )) {
			$this->strings = new CspStringsAreMultibyte();
		}
		else{
			$this->strings = new CspStringsAreAscii();
		}
		$this->header = array(
			'Project-Id-Version' 			=> '',
			'Report-Msgid-Bugs-To' 			=> '',
			'POT-Creation-Date' 			=> '',
			'PO-Revision-Date'				=> '',
			'Last-Translator'				=> '',
			'Language-Team'					=> '',
			'MIME-Version'					=> '1.0',
			'Content-Type'					=> 'text/plain; charset=UTF-8',
			'Content-Transfer-Encoding'		=> '8bit',
			'Plural-Forms'					=> 'nplurals=2; plural=n != 1;',
			'X-Poedit-Language'				=> '',
			'X-Poedit-Country'				=> '',
			'X-Poedit-SourceCharset'		=> 'utf-8',
			'X-Poedit-KeywordsList'			=> '__;_e;__ngettext:1,2;_n:1,2;__ngettext_noop:1,2;_n_noop:1,2;_c,_nc:4c,1,2;_x:1,2c;_nx:4c,1,2;_nx_noop:4c,1,2;',
			'X-Poedit-Basepath'				=> '',
			'X-Poedit-Bookmarks'			=> '',
			'X-Poedit-SearchPath-0'			=> '.',
			'X-Textdomain-Support'			=> 'no'
		);
		$this->header_vars = array_keys($this->header);
		array_splice($this->header_vars, array_search('X-Poedit-KeywordsList', $this->header_vars), 1 );
		$this->plural_definitions				= array(
			'nplurals=1; plural=0;' 																	=> array('hu', 'ja', 'ko', 'tr'),
			'nplurals=2; plural=1;' 																	=> array('zh'),
			'nplurals=2; plural=n>1;' 																	=> array('fr'),
			'nplurals=2; plural=n != 1;'																=> array('af','ar','be','bg','ca','da','de','el','en','es','et','eo','eu','fi','fo','fy','he','id','in','is','it','kk','ky','lb','nk','nb','nl','no','pt','ro','sr','sv','th','tl','vi','xh','zu'),
			'nplurals=3; plural=n==1 ? 0 : n==2 ? 1 : 2;' 												=> array('ga'),
			'nplurals=3; plural=(n==1) ? 1 : (n>=2 && n<=4) ? 2 : 0;' 									=> array('sk'),
			'nplurals=3; plural=n%10==1 && n%100!=11 ? 0 : n != 0 ? 1 : 2;' 							=> array('lv'),
			'nplurals=3; plural=n%100/10==1 ? 2 : n%10==1 ? 0 : (n+9)%10>3 ? 2 : 1;' 					=> array('cs', 'hr', 'ru', 'uk'),
			'nplurals=3; plural=n==1 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2;' 		=> array('pl'),
			'nplurals=3; plural=n%10==1 && n%100!=11 ? 0 : n%10>=2 && (n%100<10 || n%100>=20) ? 1 : 2;'	=> array('lt'),
			'nplurals=4; plural=n%100==1 ? 0 : n%100==2 ? 1 : n%100==3 || n%100==4 ? 2 : 3;' 			=> array('sl')
		);
		$this->map 					= array();
		$this->nplurals 			= 2;
		$this->plural_func 			= 'n != 1;';

		$this->reg_comment			= '/^#\s(.*)/';
		$this->reg_comment_ex 		= '/^#\.\s+(.*)/';
		$this->reg_reference		= '/^#:\s+(.*)/';
		$this->reg_flags			= '/^#,\s+(.*)/';
		$this->reg_textdomain		= '/^#\s*@\s*(.*)/';
		$this->reg_msgctxt			= '/^msgctxt\s+(".*")/';
		$this->reg_msgid			= '/^msgid\s+(".*")/';
		$this->reg_msgstr			= '/^msgstr\s+(".*")/';
		$this->reg_msgid_plural		= '/^msgid_plural\s+(".*")/';
		$this->reg_msgstr_plural	= '/^msgstr\[\d+\]\s+(".*")/';
		$this->reg_multi_line		= '/^(".*")/';
	}

	function _set_header_from_string($head, $lang='') {
		if (!is_string($head)) return;
		$hdr = explode("\n", $head);
		foreach($hdr as $e) {
			if (strpos($e, ':') === false) continue;
			list($key, $val) = explode(':', $e, 2);
			$key = trim($key);$val = str_replace("\\","/", trim($val));
			if (in_array($key, $this->header_vars)) {
				$this->header[$key] = $val;
				//ensure qualified pluralization forms now
				if ($key == 'Plural-Forms') {
					$func = '';
					foreach($this->plural_definitions as $f => $langs) {
						if (in_array($lang, $langs)) $func = $f;
					}
					if (empty($func)) { $func = 'nplurals=2; plural=n != 1;'; }
					$this->header[$key] = $func;
				}
			}
		}
		$msgstr = array();
		foreach($this->header as $key => $value) {
			$msgstr[] = $key.": ".$value;
		}
		$msgstr = implode("\n", $msgstr);
		$this->map[''] = $this->_new_entry('', $msgstr);

		if (preg_match("/nplurals\s*=\s*(\d+)\s*\;\s*plural\s*=\s*([^\n]+)\;/", $this->header['Plural-Forms'], $matches)) {
			$this->nplurals = (int)$matches[1];
			$this->plural_func = $matches[2];
		}
	}


	function _new_entry($org, $trans, $reference=false, $flags=false, $tcomment=false, $ccomment=false, $ltd = '') {
		// T ... translation data, contains embed \00 if plurals
		// X ... true, if org contains \04 context in front of
		// P ... true, if is a pluralization,
		// CT ... remark (comment) translator
		// CC ... remark (code) - hard code translations required
		// F ... flags like 'php-format'
		// R ... reference
		// LTD ... loaded text domain
		return array(
			'T' 	=> $trans,
			'X'		=> ($this->strings->_strpos( $org, "\04" ) !== false),
			'P'		=> ($this->strings->_strpos( $org, "\00" ) !== false),
			'CT' 	=> (is_string($tcomment) ? array($tcomment) : (is_array($tcomment) ? $tcomment : array())),
			'CC' 	=> (is_string($ccomment) ? array($ccomment) : (is_array($ccomment) ? $ccomment : array())),
			'F'		=> (is_string($flags) ? array($flags) : (is_array($flags) ? $flags : array())),
			'R'		=> (is_string($reference) ? array($reference) : (is_array($reference) ? $reference : array())),
			'LTD'	=> (is_string($reference) ? array($ltd) : (is_array($ltd) ? $ltd : array()))
		);
	}

	function trim_quotes($s) {
		if ( substr($s, 0, 1) == '"') $s = substr($s, 1);
		if ( substr($s, -1, 1) == '"') $s = substr($s, 0, -1);
		return $s;
	}

	function _clean_import($string) {
		$escapes = array('t' => "\t", 'n' => "\n", '\\' => '\\');
		$lines = array_map('trim', explode("\n", $string));
		$lines = array_map(array('CspTranslationFile', 'trim_quotes'), $lines);
		$unpoified = '';
		$previous_is_backslash = false;
		foreach($lines as $line) {
			preg_match_all('/./u', $line, $chars);
			$chars = $chars[0];
			foreach($chars as $char) {
				if (!$previous_is_backslash) {
					if ('\\' == $char)
						$previous_is_backslash = true;
					else
						$unpoified .= $char;
				} else {
					$previous_is_backslash = false;
					$unpoified .= isset($escapes[$char])? $escapes[$char] : $char;
				}
			}
		}
		return $unpoified;
	}

	function _clean_export($string) {
		$quote = '"';
		$slash = '\\';
		$newline = "\n";

		$replaces = array(
			"$slash" 	=> "$slash$slash",
			"$quote"	=> "$slash$quote",
			"\t" 		=> '\t',
		);

		$string = str_replace(array_keys($replaces), array_values($replaces), $string);

		$po = $quote.implode("${slash}n$quote$newline$quote", explode($newline, $string)).$quote;
		// add empty string on first line for readbility
		if (false !== strpos($string, $newline) &&
				(substr_count($string, $newline) > 1 || !($newline === substr($string, -strlen($newline))))) {
			$po = "$quote$quote$newline$po";
		}
		// remove empty strings
		$po = str_replace("$newline$quote$quote", '', $po);
		return $po;
	}

	function _build_rel_path($base_file) {
		$a = explode('/', $base_file);
		$rel = '';
		for ($i=0; $i<count($a)-1; $i++) { $rel.="../"; }
		return $rel;
	}

	function supports_textdomain_extension() {
		return ($this->header['X-Textdomain-Support'] == 'yes');
	}

	function create_pofile($pofile, $base_file, $proj_id, $timestamp, $translator, $pluralforms, $language, $country) {
		$rel = $this->_build_rel_path($base_file);
		preg_match("/([a-z][a-z]_[A-Z][A-Z]).(mo|po)$/", $pofile, $hits);
		$po_lang = substr($hits[1],0,2);
		$country = strtoupper($country);
		$this->_set_header_from_string(
			"Project-Id-Version: $proj_id\nPO-Revision-Date: $timestamp\nLast-Translator: $translator\nX-Poedit-Language: $language\nX-Poedit-Country: $country\nX-Poedit-Basepath: $rel\nPlural-Forms: \nX-Textdomain-Support: yes",
			$po_lang
		);
		return $this->write_pofile($pofile);
	}

	function read_pofile($pofile, $check_plurals=false, $base_file=false) {
		if (!empty($pofile) && file_exists($pofile) && is_readable($pofile)) {
			$l = filesize($pofile);
			$handle = fopen($pofile,'r');
			$content = fread($handle, $l);
			fclose($handle);

			if (!seems_utf8($content)) $content = utf8_encode($content);

			$content = preg_split("/\r*\n/", $content);

			$msgid = false;
			$cur_entry = $this->_new_entry('', false); //empty

			foreach($content as $line) {

				if (empty($line)) {

					if ($msgid !== false) {
						if (count($cur_entry['LTD']) == 0) $cur_entry['LTD'][] = '';
						$temp = ($cur_entry['X'] !== false ? $cur_entry['X']."\04".$msgid : $msgid);
						$this->map[$temp] = $this->_new_entry(
							$temp,
							$cur_entry['T'],
							$cur_entry['R'],
							$cur_entry['F'],
							$cur_entry['CT'],
							$cur_entry['CC'],
							$cur_entry['LTD']
						);
					}
					$msgid = false;
					$cur_entry = $this->_new_entry('',false);
					continue;
				}

				if (preg_match($this->reg_multi_line, $line, $hits)) {
					if ($cur_entry['T'] === false) { $msgid .= $this->_clean_import($line); }
					else { $cur_entry['T'] = $cur_entry['T'].$this->_clean_import($line); }
					continue;
				}

				if (preg_match($this->reg_msgctxt, $line, $hits)) { $cur_entry['X'] = $this->_clean_import($hits[1]); };
				if (preg_match($this->reg_textdomain, $line, $hits)) { $cur_entry['LTD'][] = $hits[1]; }
				elseif (preg_match($this->reg_comment, $line, $hits)) { $cur_entry['CT'][] = $this->_clean_import($hits[1]); }
				if (preg_match($this->reg_comment_ex, $line, $hits)) { $cur_entry['CC'][] = $this->_clean_import($hits[1]); }
				if (preg_match($this->reg_reference, $line, $hits)) { $cur_entry['R'][] = $hits[1]; }
				if (preg_match($this->reg_flags, $line, $hits)) { $cur_entry['F'][] = $hits[1]; }
				if (preg_match($this->reg_msgid, $line, $hits)) { $msgid = $this->_clean_import($hits[1]); }
				if (preg_match($this->reg_msgstr, $line, $hits)) { $cur_entry['T'] = $this->_clean_import($hits[1]); }
				if (preg_match($this->reg_msgid_plural, $line, $hits)) { $msgid .= "\0".$this->_clean_import($hits[1]); }
				if (preg_match($this->reg_msgstr_plural, $line, $hits)) {
					if ($cur_entry['T'] === false) $cur_entry['T'] = $this->_clean_import($hits[1]);
					else $cur_entry['T'] = $cur_entry['T'].(preg_match("/[^\\0*]*/", $cur_entry['T']) ? "\0" : '').$this->_clean_import($hits[1]);
				}
			}

			preg_match("/([a-z][a-z]_[A-Z][A-Z]).(mo|po)$/", $pofile, $hits);
			$po_lang = substr($hits[1],0,2);
			$this->_set_header_from_string($this->map['']['T'], $po_lang);
			$this->_set_header_from_string('Plural-Forms: ', $po_lang); //for safetly the plural forms!
			if ($base_file) {
				$rel = $this->_build_rel_path($base_file);
				$this->_set_header_from_string("X-Poedit-Basepath: $rel\nX-Poedit-SearchPath-0: .", $po_lang);
			}
			return true;
		}
		return false;
	}

	function write_pofile($pofile, $last = false) {
		if (file_exists($pofile) && !is_writable($pofile)) return false;
		$handle = @fopen($pofile, "wb");
		if ($handle === false) return false;
		$this->_set_header_from_string("Plural-Forms: \nX-Textdomain-Support: yes");

		//write header if last because it has no code ref anyway
		if ($last === true) {
			fwrite($handle, 'msgid ""'."\n");
			fwrite($handle, 'msgstr '.$this->_clean_export($this->map['']['T'])."\n\n");
		}

		foreach($this->map as $key => $entry) {

			if ((is_array($entry['R']) && (count($entry['R']) > 0)) || ($last === false)) {

				if (is_array($entry['CT'])) {
					foreach($entry['CT'] as $comt) {
						fwrite($handle, '#  '.$comt."\n");
					}
				}
				if (is_array($entry['CC'])) {
					foreach($entry['CC'] as $comc) {
						fwrite($handle, '#. '.$comc."\n");
					}
				}
				if (is_array($entry['R'])) {
					foreach($entry['R'] as $ref) {
						fwrite($handle, '#: '.$ref."\n");
					}
				}
				if (is_array($entry['F']) && count($entry['F'])) {
					fwrite($handle, '#, '.implode(', ', $entry['F'])."\n");
				}
				if (is_array($entry['LTD']) && count($entry['LTD'])) {
					foreach($entry['LTD'] as $domain) {
						if(!empty($domain)) fwrite($handle, '#@ '.$domain."\n");
					}
				}

				if($entry['P'] !== false) {
					list($msgid, $msgid_plural) = explode("\0", $key);
					if ($entry['X'] !== false) {
						list($ctx, $msgid) = explode("\04", $msgid);
						fwrite($handle, 'msgctxt '.$this->_clean_export($ctx)."\n");
					}
					fwrite($handle, 'msgid '.$this->_clean_export($msgid)."\n");
					fwrite($handle, 'msgid_plural '.$this->_clean_export($msgid_plural)."\n");
					$msgstr_arr = explode("\0", $entry['T']);
					for ($i=0; $i<count($msgstr_arr); $i++) {
						fwrite($handle, 'msgstr['.$i.'] '.$this->_clean_export($msgstr_arr[$i])."\n");
					}
				}
				else{
					$msgid = $key;
					if ($entry['X'] !== false) {
						list($ctx, $msgid) = explode("\04", $key);
						fwrite($handle, 'msgctxt '.$this->_clean_export($ctx)."\n");
					}
					fwrite($handle, 'msgid '.$this->_clean_export($msgid)."\n");
					fwrite($handle, 'msgstr '.$this->_clean_export($entry['T'])."\n");
				}
				fwrite($handle, "\n");

			}
		}
		fclose($handle);
		return true;
	}

	function read_mofile($mofile, $check_plurals, $base_file=false) {

		//mo file reading without need of further WP introduced classes !
		if (file_exists($mofile)) {
			if (is_readable($mofile)) {
				$file = fopen( $mofile, 'rb' );
				if ( !$file )
					return false;

				$header = fread( $file, 28 );
				if ( $this->strings->_strlen( $header ) != 28 )
					return false;

				// detect endianess
				$endian = unpack( 'Nendian', $this->strings->_substr( $header, 0, 4 ) );
				if ( $endian['endian'] == intval( hexdec( '950412de' ) ) )
					$endian = 'N';
				else if ( $endian['endian'] == intval( hexdec( 'de120495' ) ) )
					$endian = 'V';
				else
					return false;

				// parse header
				$header = unpack( "{$endian}Hrevision/{$endian}Hcount/{$endian}HposOriginals/{$endian}HposTranslations/{$endian}HsizeHash/{$endian}HposHash", $this->strings->_substr( $header, 4 ) );
				if ( !is_array( $header ) )
					return false;

				extract( $header );

				// support revision 0 of MO format specs, only
				if ( $Hrevision != 0 )
					return false;

				// read originals' index
				fseek( $file, $HposOriginals, SEEK_SET );

				$originals = fread( $file, $Hcount * 8 );
				if ( $this->strings->_strlen( $originals ) != $Hcount * 8 )
					return false;

				// read translations index
				fseek( $file, $HposTranslations, SEEK_SET );

				$translations = fread( $file, $Hcount * 8 );
				if ( $this->strings->_strlen( $translations ) != $Hcount * 8 )
					return false;

				// transform raw data into set of indices
				$originals    = $this->strings->_str_split( $originals, 8 );
				$translations = $this->strings->_str_split( $translations, 8 );

				// find position of first string in file
				$HposStrings = 0x7FFFFFFF;

				for ( $i = 0; $i < $Hcount; $i++ )
				{

					// parse index records on original and related translation
					$o = unpack( "{$endian}length/{$endian}pos", $originals[$i] );
					$t = unpack( "{$endian}length/{$endian}pos", $translations[$i] );

					if ( !$o || !$t )
						return false;

					$originals[$i]    = $o;
					$translations[$i] = $t;

					$HposStrings = min( $HposStrings, $o['pos'], $t['pos'] );

				}

				// read strings expected in rest of file
				fseek( $file, $HposStrings, SEEK_SET );

				$strings = '';
				while ( !feof( $file ) )
					$strings .= fread( $file, 4096 );

				fclose( $file );

				//now reading the contents
				$this->map = array();
				for ( $i = 0; $i < $Hcount; $i++ )
				{
					// adjust offset due to reading strings to separate space before
					$originals[$i]['pos']    -= $HposStrings;
					$translations[$i]['pos'] -= $HposStrings;

					// extract original and translations
					$original    = $this->strings->_substr( $strings, $originals[$i]['pos'], $originals[$i]['length'] );
					$translation = $this->strings->_substr( $strings, $translations[$i]['pos'], $translations[$i]['length'] );

					$this->map[$original] = $this->_new_entry($original, $translation);
				}

				preg_match("/([a-z][a-z]_[A-Z][A-Z]).(mo|po)$/", $mofile, $hits);
				$po_lang = substr($hits[1],0,2);
				$this->_set_header_from_string($this->map['']['T'], $po_lang);
				$this->_set_header_from_string('Plural-Forms: ',$po_lang); //for safetly the plural forms!
				if ($base_file) {
					$rel = $this->_build_rel_path($base_file);
					$this->_set_header_from_string("X-Poedit-Basepath: $rel\nX-Poedit-SearchPath-0: .", $po_lang);
				}
				return true;
			}
		}
		return false;
	}

	function _is_valid_entry(&$entry, &$textdomain) {
		return (
			(strlen(str_replace("\0", "", $entry['T'])) > 0)
			&&
			in_array($textdomain, $entry['LTD'])
		);
	}

	function write_mofile($mofile, $textdomain) {
		$handle = @fopen($mofile, "wb");
		if ($handle === false) return false;
		ksort($this->map, SORT_REGULAR);
		//let's calculate none empty values
		$entries = 0;
		foreach($this->map as $key => $value) {
			if($this->_is_valid_entry($value, $textdomain)) { $entries++; }
		}
		$tab_size = $entries * 8;
		//header: little endian magic|revision|entries|offset originals|offset translations|hashing table size|hashing table ofs
		$header = pack('NVVVVVV@'.(28+$tab_size*2),0xDE120495,0x00000000,$entries,28,28+$tab_size,0x00000000,28+$tab_size*2);
		$org_table = '';
		$trans_table = '';
		fwrite($handle, $header);
		foreach($this->map as $key => $value) {
			if ($this->_is_valid_entry($value, $textdomain)) {
				$l=strlen($key);
				$org_table .= pack('VV', $l, ftell($handle));
				$res = pack('A'.$l.'x',$key);
				fwrite($handle, $res);
			}
		}
		foreach($this->map as $key => $value) {
			if ($this->_is_valid_entry($value, $textdomain)) {
				$l=strlen($value['T']);
				$trans_table .= pack('VV', $l, ftell($handle));
				$res = pack('A'.$l.'x',$value['T']);
				fwrite($handle, $res);
			}
		}
		fseek($handle, 28, SEEK_SET);
		fwrite($handle,$org_table);
		fwrite($handle,$trans_table);
		fclose($handle);
		return true;
	}

	function parsing_init() {
		//reset the references and textdomains
		foreach($this->map as $key => $entry) {
			$this->map[$key]['R'] = array();
			$this->map[$key]['CC'] = array();
			$this->map[$key]['LTD'] = array();
		}

		$this->_set_header_from_string("X-Textdomain-Support: yes");
	}

	function parsing_add_messages($path, $sourcefile, $textdomain='') {
		$parser = new bp_translate_l10n_parser($path, $textdomain);
		$r = $parser->parseFile($sourcefile);
		$gettext = $r['gettext'];
		$not_gettext = $r['not_gettext'];
		if (count($gettext)) {
			foreach($gettext as $match) {
				$entry = null;
				if (isset($this->map[$match['msgid']]))
					$entry = $this->map[$match['msgid']];

				if (!is_array($entry)) {
					$entry = $this->_new_entry(
						$match['msgid'],
						str_pad('', (isset($match['P']) ? $this->nplurals -1 : 0), "\0"),
						$match['R'],
						false, false,false,
						$match['LTD']
					);
				}
				else{
					if (!in_array($match['R'], $entry['R']))
						$entry['R'][] = $match['R'];
				}
				if (!in_array($match['LTD'], $entry['LTD'])) {
					$entry['LTD'][] = $match['LTD'];
				}
				foreach($match['CC'] as $cc) {
					if (!in_array($cc, $entry['CC'])) {
						$entry['CC'][] = $cc;
					}
				}

				if(preg_match("/(%[A-Za-z0-9])/", $match['msgid']) > 0) {
					if (!is_array($entry['F'])||(!in_array('php-format', $entry['F']))) {
						$entry['F'][] = 'php-format';
					}
				}
				$this->map[$match['msgid']] = $entry;
			}
		}
		if (count($not_gettext)) {
			foreach($not_gettext as $match) {
				$entry = null;
				if (isset($this->map[$match['msgid']]))
					$entry = $this->map[$match['msgid']];

				if (!is_array($entry)) {
					$entry = $this->_new_entry(
						$match['msgid'],
						'',
						$match['R'],
						false,
						false,
						$match['CC'],
						$match['LTD']
					);
				}
				else{
					if (!in_array($match['R'], $entry['R'])) {
						$entry['R'][] = $match['R'];
					}
					foreach($match['CC'] as $cc) {
						if (!in_array($cc, $entry['CC'])) {
							$entry['CC'][] = $cc;
						}
					}
				}
				if (!in_array($match['LTD'], $entry['LTD'])) {
					$entry['LTD'][] = $match['LTD'];
				}
				$this->map[$match['msgid']] = $entry;
			}
		}
	}

	function parsing_finalize($textdomain) {
		//if there is only one textdomain included and this is '' (empty string) replace all with the given textdomain
		$ltd = array();
		foreach($this->map as $key => $entry) {
			if (is_array($entry['R']) && (count($entry['R']) > 0)) {
				if (count($entry['LTD']) == 0) {
					$this->map[$key]['LTD'] = array($textdomain);
					$entry['LTD'] = array($textdomain);
				}
				foreach($entry['LTD'] as $domain) {
					if (!in_array($domain, $ltd)) $ltd[] = $domain;
				}
			}
		}
		if ((count($ltd) == 1) && ($ltd[0] == '')) {
			$keys = array_keys($this->map);
			foreach($keys as $key) {
				$this->map[$key]['LTD'] = array($textdomain);
			}
		}
	}

	function _convert_for_js($str) {
		$search = array( '"\"', "\\", "\n", "\r", "\t", "\"");
		$replace = array( '"\\\\"', '\\\\', '\\\\n', '\\\\r', '\\\\t', '\\\\\"');
		$str = str_replace( $search, $replace, $str );
		return $str;
	}

	function _convert_js_input($str) {
		$search = array('\\\\\\\"', '\\\\\"','\\\\n', '\\\\t','\\0', "\\'", '\\\\');
		$replace = array('\"', '"', "\n", "\\t", "\0", "'", "\\");
		$str = str_replace( $search, $replace, $str );
		return $str;
	}

	function echo_as_json($path, $file, $sys_locales) {
		$loc = substr($file,strlen($file)-8,-3);
		header('Content-Type: application/json');
?>
{
	header : "<table id=\"po-hdr\" style=\"display:none;\"><?php
		foreach($this->header as $key => $value) {
			echo "<tr><td class=\\\"po-hdr-key\\\">".$key."</td><td class=\\\"po-hdr-val\\\">".htmlspecialchars($value)."</td></tr>";
		}?>",
	destlang: "<?php echo ($sys_locales[$loc]['google-api'] === true ? substr($loc, 0, 2) : ''); ?>",
	last_saved : "<?php $mo = substr($path.$file,0,-2)."mo"; if (file_exists($mo)) { echo date (__('m/d/Y H:i:s',BP_TRANSLATE_PO_TEXTDOMAIN), filemtime($mo)); } else { _e('unknown',BP_TRANSLATE_PO_TEXTDOMAIN); } ?>",
	plurals_num : <?php echo $this->nplurals; ?>,
	plurals_func : "<?php echo $this->plural_func; ?>",
	path : "<?php echo $path; ?>",
	file : "<?php echo $file; ?>",
	index : {
		'total' : [],
		'plurals' : [],
		'open' : [],
		'rem' : [],
		'code' : [],
		'ctx' : [],
		'cur' : [],
		'ltd' : []
	},
	content : [
<?php
		$num = count($this->map);
		$c = 0;
		$ltd = array();
		foreach($this->map as $key => $entry) {
			$c++;
			if (!strlen($key)) { continue; }

			if (strpos($key, "\04") > 0) {
				list($ctx, $key) = explode("\04", $key);
				echo "{ \"ctx\" : \"".$this->_convert_for_js($ctx)."\",";
			}else {
				echo "{ ";
			}

			if (is_array($entry['LTD']) && count($entry['LTD'])) { echo " \"ltd\" : [\"".implode("\",\"",$entry['LTD'])."\"],"; }
			else { echo " \"ltd\" : [\"\"],"; }

			if ($entry['P'] !== false) {
				$parts = explode("\0", $key);
				for($i=0; $i<count($parts); $i++) {
					$parts[$i] = $this->_convert_for_js($parts[$i]);
				}
				echo " \"key\" : [\"".implode("\",\"",$parts)."\"],";
			} else{ echo " \"key\" : \"".$this->_convert_for_js($key)."\","; }

			if (strpos($entry['T'], "\0") !== false) {
				$parts = explode("\0", $entry['T']);
				for($i=0; $i<count($parts); $i++) {
					$parts[$i] = $this->_convert_for_js($parts[$i]);
				}
				echo " \"val\" : [\"".implode("\",\"",$parts)."\"]";
			} else { echo " \"val\" : \"".$this->_convert_for_js($entry['T'])."\""; }

			if (is_array($entry['CT']) && count($entry['CT'])) { echo ", \"rem\" : \"".implode('\n',$this->_convert_for_js($entry['CT']))."\""; }
			else { echo  ", \"rem\" : \"\""; }

			if (is_array($entry['CC']) && count($entry['CC'])) {
				echo  ", \"code\" : \"".implode('\n',$this->_convert_for_js($entry['CC']))."\"";
			}

			if (is_array($entry['R']) && count($entry['R'])) { echo ", \"ref\" : [\"".implode("\",\"",$entry['R'])."\"]"; }
			else { echo ", \"ref\" : []"; }

			echo "}".($c != $num ? ',' : '')."\n";

			foreach($entry['LTD'] as $d) {
				if (!in_array($d, $ltd)) $ltd[] = $d;
			}
		}
?>
	],
	textdomains : ["<?php sort($ltd); echo implode('","', array_reverse($ltd)); ?>"]
}
<?php
	}

	function update_entry($msgid, $msgstr) {
		$msgid = $this->_convert_js_input($msgid);
		if (array_key_exists($msgid, $this->map)) {
			$this->map[$msgid]['T'] = $this->_convert_js_input($msgstr);
			return true;
		}
		//the \t issue must be handled carefully
		$msgid = str_replace('\\t', "\t", $msgid);
		$msgstr = str_replace('\\t', "\t", $msgstr);
		if (array_key_exists($msgid, $this->map)) {
			$this->map[$msgid]['T'] = $this->_convert_js_input($msgstr);
			return true;
		}
		return false;
	}
}

?>