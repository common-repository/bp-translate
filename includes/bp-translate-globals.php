<?php

/* Define slugs and settings for BuddyPress Translate */
	if ( !defined( 'BP_TRANSLATE_SLUG' ) )
		define( 'BP_TRANSLATE_SLUG',					'language');

	if ( !defined( 'BP_TRANSLATE_CLASS_PREFIX' ) )
		define( 'BP_TRANSLATE_CLASS_PREFIX',			'bp-translate-');

	if ( !defined( 'BP_TRANSLATE_QUERY_ARG' ) )
		define( 'BP_TRANSLATE_QUERY_ARG',				'lang' );

	if ( !defined( 'BP_TRANSLATE_USER_QUERY_ARG' ) )
		define( 'BP_TRANSLATE_USER_QUERY_ARG',			'userlang' );

	if ( !defined( 'BP_TRANSLATE_USER_ADMIN_QUERY_ARG' ) )
		define( 'BP_TRANSLATE_USER_ADMIN_QUERY_ARG',	'userlang_admin' );

/*
	BP Translate will only activate for the version of WordPress below.
	It can be changed to work with other versions but might cause problems.
	Proceed at your own risk!
*/
	define( 'BP_TRANSLATE_SUPPORTED_WP_VERSION',	'3.0.1' );
	define( 'BP_TRANSLATE_DB_VERSION',				'1.0' );

	define( 'BP_TRANSLATE_STRING',				1 );
	define( 'BP_TRANSLATE_BOOLEAN',				2 );
	define( 'BP_TRANSLATE_INTEGER',				3 );
	define( 'BP_TRANSLATE_URL',					4 );
	define( 'BP_TRANSLATE_LANGUAGE',			5 );

	define( 'BP_TRANSLATE_URL_QUERY',			1 );
	define( 'BP_TRANSLATE_URL_PATH',			2 );
	define( 'BP_TRANSLATE_URL_DOMAIN',			3 );

	define( 'BP_TRANSLATE_STRFTIME_OVERRIDE',	1 );
	define( 'BP_TRANSLATE_DATE_OVERRIDE',		2 );
	define( 'BP_TRANSLATE_DATE',				3 );
	define( 'BP_TRANSLATE_STRFTIME',			4 );

/* Enable the use of following languages as default */
	$bp_translate['enabled_languages'] = array(
		'0' => 'en',
		'1' => 'es',
	);

/* Start array for per blog taxonomy translations */
	$bp_translate['term_name'] = array();

/* Sets default language(s) */
// Used site wide as fall back language ( site -> blog )
	$bp_translate['default_language'] = 'en';
// Original default language set by site admin
	$bp_translate['default_site_language'] = 'en';
// Default blog language set at blog creation
	$bp_translate['default_blog_language'] = 'en';
// Default user language if no other language exists
	$bp_translate['default_user_language'] = 'en';

/* Hide pages without content */
// Hides pages and posts if user language version doesn't exist
	$bp_translate['hide_untranslated'] = false;
// Site wide default setting
	$bp_translate['hide_site_untranslated'] = false;
// Allow individual blogs to override
	$bp_translate['hide_blog_untranslated'] = false;
// Does a user really want to see untranslated content?
	$bp_translate['hide_user_untranslated'] = false;

/* Enables browser language detection */
	$bp_translate['detect_browser_language'] = true;

/* Automatically update .mo files */
	$bp_translate['auto_update_mo'] = true;

/*
	Sets default url mode :
		BP_TRANSLATE_URL_QUERY - query (?lang=en)
		BP_TRANSLATE_URL_PATH - pre-path (domain.com/en/members/)
		BP_TRANSLATE_URL_DOMAIN - pre-domain (http://en.domain.com/)
*/
	$bp_translate['url_mode'] = BP_TRANSLATE_URL_PATH;

/* pre-Domain Endings - for future use */
	$bp_translate['pre_domain']['de'] = "de";
	$bp_translate['pre_domain']['en'] = "en";
	$bp_translate['pre_domain']['zh'] = "zh";
	$bp_translate['pre_domain']['fi'] = "fs";
	$bp_translate['pre_domain']['fr'] = "fr";
	$bp_translate['pre_domain']['nl'] = "nl";
	$bp_translate['pre_domain']['se'] = "se";
	$bp_translate['pre_domain']['it'] = "it";
	$bp_translate['pre_domain']['ro'] = "ro";
	$bp_translate['pre_domain']['hu'] = "hu";
	$bp_translate['pre_domain']['ja'] = "ja";
	$bp_translate['pre_domain']['es'] = "es";
	$bp_translate['pre_domain']['vi'] = "vi";

/* Names for languages in the corresponding language, add more if needed */
	$bp_translate['language_name']['de'] = "Deutsch";
	$bp_translate['language_name']['en'] = "English";
	$bp_translate['language_name']['zh'] = "ä¸­æ–‡";
	$bp_translate['language_name']['fi'] = "suomi";
	$bp_translate['language_name']['fr'] = "FranÃ§ais";
	$bp_translate['language_name']['nl'] = "Nederlands";
	$bp_translate['language_name']['se'] = "Svenska";
	$bp_translate['language_name']['it'] = "Italiano";
	$bp_translate['language_name']['ro'] = "RomÃ¢nÄƒ";
	$bp_translate['language_name']['hu'] = "Magyar";
	$bp_translate['language_name']['ja'] = "æ—¥æœ¬èªž";
	$bp_translate['language_name']['es'] = "Español";
	$bp_translate['language_name']['vi'] = "Tiáº¿ng Viá»‡t";

/*
	Locales for languages
	See locale -a for available locales
*/
	$bp_translate['locale']['de'] = "de_DE";
	$bp_translate['locale']['en'] = "en_US";
	$bp_translate['locale']['zh'] = "zh_CN";
	$bp_translate['locale']['fi'] = "fi";
	$bp_translate['locale']['fr'] = "fr_FR";
	$bp_translate['locale']['nl'] = "nl_NL";
	$bp_translate['locale']['se'] = "sv_SE";
	$bp_translate['locale']['it'] = "it_IT";
	$bp_translate['locale']['ro'] = "ro_RO";
	$bp_translate['locale']['hu'] = "hu_HU";
	$bp_translate['locale']['ja'] = "ja";
	$bp_translate['locale']['es'] = "es_ES";
	$bp_translate['locale']['vi'] = "vi";

// Language not available messages
// %LANG:<normal_seperator>:<last_seperator>% generates a list of languages seperated by
// <normal_seperator> except for the last one, where <last_seperator> will be used instead.
	$bp_translate['not_available']['de'] = "Leider ist der Eintrag nur auf %LANG:, : und % verfÃ¼gbar.";
	$bp_translate['not_available']['en'] = "Sorry, this entry is only available in %LANG:, : and %.";
	$bp_translate['not_available']['zh'] = "å¯¹ä¸?èµ·ï¼Œæ­¤å†…å®¹å?ªé€‚ç”¨äºŽ%LANG:ï¼Œ:å’Œ%ã€‚";
	$bp_translate['not_available']['fi'] = "Anteeksi, mutta tÃ¤mÃ¤ kirjoitus on saatavana ainoastaan nÃ¤illÃ¤ kielillÃ¤: %LANG:, : ja %.";
	$bp_translate['not_available']['fr'] = "DÃ©solÃ©, cet article est seulement disponible en %LANG:, : et %.";
	$bp_translate['not_available']['nl'] = "Onze verontschuldigingen, dit bericht is alleen beschikbaar in %LANG:, : en %.";
	$bp_translate['not_available']['se'] = "TyvÃ¤rr Ã¤r denna artikel enbart tillgÃ¤nglig pÃ¥ %LANG:, : och %.";
	$bp_translate['not_available']['it'] = "Ci spiace, ma questo articolo Ã¨ disponibile soltanto in %LANG:, : e %.";
	$bp_translate['not_available']['ro'] = "Din pÄƒcate acest articol este disponibil doar Ã®n %LANG:, : È™i %.";
	$bp_translate['not_available']['hu'] = "Sajnos ennek a bejegyzÃ©snek csak %LANG:, : Ã©s % nyelvÅ± vÃ¡ltozata van.";
	$bp_translate['not_available']['ja'] = "ç”³ã?—è¨³ã?‚ã‚Šã?¾ã?›ã‚“ã€?ã?“ã?®ã‚³ãƒ³ãƒ†ãƒ³ãƒ„ã?¯ã?Ÿã? ä»Šã€€%LANG:ã€? :ã?¨ %ã€€ã?®ã?¿ã?§ã?™ã€‚";
	$bp_translate['not_available']['es'] = "Lo sentimos, pero esta entrada está disponible sólo en %LANG:, : y %.";
	$bp_translate['not_available']['vi'] = "Ráº¥t tiáº¿c, má»¥c nÃ y chá»‰ tá»“n táº¡i á»Ÿ %LANG:, : vÃ  %.";

/* BP Translate Services */
	$bp_translate['bp_translate_services'] = false;

/* strftime usage (backward compability) */
	$bp_translate['use_strftime'] = BP_TRANSLATE_DATE;

/* Date Configuration */
	$bp_translate['date_format']['en'] = '%A %B %e%q, %Y';
	$bp_translate['date_format']['de'] = '%A, der %e. %B %Y';
	$bp_translate['date_format']['zh'] = '%x %A';
	$bp_translate['date_format']['fi'] = '%e.&m.%C';
	$bp_translate['date_format']['fr'] = '%A %e %B %Y';
	$bp_translate['date_format']['nl'] = '%d/%m/%y';
	$bp_translate['date_format']['se'] = '%Y/%m/%d';
	$bp_translate['date_format']['it'] = '%e %B %Y';
	$bp_translate['date_format']['ro'] = '%A, %e %B %Y';
	$bp_translate['date_format']['hu'] = '%Y %B %e, %A';
	$bp_translate['date_format']['ja'] = '%Yå¹´%mæœˆ%dæ—¥';
	$bp_translate['date_format']['es'] = '%d de %B de %Y';
	$bp_translate['date_format']['vi'] = '%d/%m/%Y';
	$bp_translate['time_format']['en'] = '%I:%M %p';
	$bp_translate['time_format']['de'] = '%H:%M';
	$bp_translate['time_format']['zh'] = '%I:%M%p';
	$bp_translate['time_format']['fi'] = '%H:%M';
	$bp_translate['time_format']['fr'] = '%H:%M';
	$bp_translate['time_format']['nl'] = '%H:%M';
	$bp_translate['time_format']['se'] = '%H:%M';
	$bp_translate['time_format']['it'] = '%H:%M';
	$bp_translate['time_format']['ro'] = '%H:%M';
	$bp_translate['time_format']['hu'] = '%H:%M';
	$bp_translate['time_format']['ja'] = '%H:%M';
	$bp_translate['time_format']['es'] = '%H:%M hrs.';
	$bp_translate['time_format']['vi'] = '%H:%M';

/*
	Flag images configuration
	Look in /images/flags/ directory for a huge list of flags for usage
*/
	$bp_translate['flag']['en'] = 'gb.png';
	$bp_translate['flag']['de'] = 'de.png';
	$bp_translate['flag']['zh'] = 'cn.png';
	$bp_translate['flag']['fi'] = 'fi.png';
	$bp_translate['flag']['fr'] = 'fr.png';
	$bp_translate['flag']['nl'] = 'nl.png';
	$bp_translate['flag']['se'] = 'se.png';
	$bp_translate['flag']['it'] = 'it.png';
	$bp_translate['flag']['ro'] = 'ro.png';
	$bp_translate['flag']['hu'] = 'hu.png';
	$bp_translate['flag']['ja'] = 'jp.png';
	$bp_translate['flag']['es'] = 'es.png';
	$bp_translate['flag']['vi'] = 'vn.png';

/* Location of flags (needs trailing slash!) */
	$bp_translate['flag_location'] = WP_PLUGIN_DIR . '/bp-translate/includes/images/flags/';

/* Don't convert URLs to this file types */
	$bp_translate['ignore_file_types'] = 'gif,jpg,jpeg,png,pdf,swf,tif,rar,zip,7z,mpg,divx,mpeg,avi,css,js,ppt,mp3';

/* Full country names as locales for Windows systems */
	$bp_translate['windows_locale']['aa'] = "Afar";
	$bp_translate['windows_locale']['ab'] = "Abkhazian";
	$bp_translate['windows_locale']['ae'] = "Avestan";
	$bp_translate['windows_locale']['af'] = "Afrikaans";
	$bp_translate['windows_locale']['am'] = "Amharic";
	$bp_translate['windows_locale']['ar'] = "Arabic";
	$bp_translate['windows_locale']['as'] = "Assamese";
	$bp_translate['windows_locale']['ay'] = "Aymara";
	$bp_translate['windows_locale']['az'] = "Azerbaijani";
	$bp_translate['windows_locale']['ba'] = "Bashkir";
	$bp_translate['windows_locale']['be'] = "Belarusian";
	$bp_translate['windows_locale']['bg'] = "Bulgarian";
	$bp_translate['windows_locale']['bh'] = "Bihari";
	$bp_translate['windows_locale']['bi'] = "Bislama";
	$bp_translate['windows_locale']['bn'] = "Bengali";
	$bp_translate['windows_locale']['bo'] = "Tibetan";
	$bp_translate['windows_locale']['br'] = "Breton";
	$bp_translate['windows_locale']['bs'] = "Bosnian";
	$bp_translate['windows_locale']['ca'] = "Catalan";
	$bp_translate['windows_locale']['ce'] = "Chechen";
	$bp_translate['windows_locale']['ch'] = "Chamorro";
	$bp_translate['windows_locale']['co'] = "Corsican";
	$bp_translate['windows_locale']['cs'] = "Czech";
	$bp_translate['windows_locale']['cu'] = "Church Slavic";
	$bp_translate['windows_locale']['cv'] = "Chuvash";
	$bp_translate['windows_locale']['cy'] = "Welsh";
	$bp_translate['windows_locale']['da'] = "Danish";
	$bp_translate['windows_locale']['de'] = "German";
	$bp_translate['windows_locale']['dz'] = "Dzongkha";
	$bp_translate['windows_locale']['el'] = "Greek";
	$bp_translate['windows_locale']['en'] = "English";
	$bp_translate['windows_locale']['eo'] = "Esperanto";
	$bp_translate['windows_locale']['es'] = "Spanish";
	$bp_translate['windows_locale']['et'] = "Estonian";
	$bp_translate['windows_locale']['eu'] = "Basque";
	$bp_translate['windows_locale']['fa'] = "Persian";
	$bp_translate['windows_locale']['fi'] = "Finnish";
	$bp_translate['windows_locale']['fj'] = "Fijian";
	$bp_translate['windows_locale']['fo'] = "Faeroese";
	$bp_translate['windows_locale']['fr'] = "French";
	$bp_translate['windows_locale']['fy'] = "Frisian";
	$bp_translate['windows_locale']['ga'] = "Irish";
	$bp_translate['windows_locale']['gd'] = "Gaelic (Scots)";
	$bp_translate['windows_locale']['gl'] = "Gallegan";
	$bp_translate['windows_locale']['gn'] = "Guarani";
	$bp_translate['windows_locale']['gu'] = "Gujarati";
	$bp_translate['windows_locale']['gv'] = "Manx";
	$bp_translate['windows_locale']['ha'] = "Hausa";
	$bp_translate['windows_locale']['he'] = "Hebrew";
	$bp_translate['windows_locale']['hi'] = "Hindi";
	$bp_translate['windows_locale']['ho'] = "Hiri Motu";
	$bp_translate['windows_locale']['hr'] = "Croatian";
	$bp_translate['windows_locale']['hu'] = "Hungarian";
	$bp_translate['windows_locale']['hy'] = "Armenian";
	$bp_translate['windows_locale']['hz'] = "Herero";
	$bp_translate['windows_locale']['ia'] = "Interlingua";
	$bp_translate['windows_locale']['id'] = "Indonesian";
	$bp_translate['windows_locale']['ie'] = "Interlingue";
	$bp_translate['windows_locale']['ik'] = "Inupiaq";
	$bp_translate['windows_locale']['is'] = "Icelandic";
	$bp_translate['windows_locale']['it'] = "Italian";
	$bp_translate['windows_locale']['iu'] = "Inuktitut";
	$bp_translate['windows_locale']['ja'] = "Japanese";
	$bp_translate['windows_locale']['jw'] = "Javanese";
	$bp_translate['windows_locale']['ka'] = "Georgian";
	$bp_translate['windows_locale']['ki'] = "Kikuyu";
	$bp_translate['windows_locale']['kj'] = "Kuanyama";
	$bp_translate['windows_locale']['kk'] = "Kazakh";
	$bp_translate['windows_locale']['kl'] = "Kalaallisut";
	$bp_translate['windows_locale']['km'] = "Khmer";
	$bp_translate['windows_locale']['kn'] = "Kannada";
	$bp_translate['windows_locale']['ko'] = "Korean";
	$bp_translate['windows_locale']['ks'] = "Kashmiri";
	$bp_translate['windows_locale']['ku'] = "Kurdish";
	$bp_translate['windows_locale']['kv'] = "Komi";
	$bp_translate['windows_locale']['kw'] = "Cornish";
	$bp_translate['windows_locale']['ky'] = "Kirghiz";
	$bp_translate['windows_locale']['la'] = "Latin";
	$bp_translate['windows_locale']['lb'] = "Letzeburgesch";
	$bp_translate['windows_locale']['ln'] = "Lingala";
	$bp_translate['windows_locale']['lo'] = "Lao";
	$bp_translate['windows_locale']['lt'] = "Lithuanian";
	$bp_translate['windows_locale']['lv'] = "Latvian";
	$bp_translate['windows_locale']['mg'] = "Malagasy";
	$bp_translate['windows_locale']['mh'] = "Marshall";
	$bp_translate['windows_locale']['mi'] = "Maori";
	$bp_translate['windows_locale']['mk'] = "Macedonian";
	$bp_translate['windows_locale']['ml'] = "Malayalam";
	$bp_translate['windows_locale']['mn'] = "Mongolian";
	$bp_translate['windows_locale']['mo'] = "Moldavian";
	$bp_translate['windows_locale']['mr'] = "Marathi";
	$bp_translate['windows_locale']['ms'] = "Malay";
	$bp_translate['windows_locale']['mt'] = "Maltese";
	$bp_translate['windows_locale']['my'] = "Burmese";
	$bp_translate['windows_locale']['na'] = "Nauru";
	$bp_translate['windows_locale']['nb'] = "Norwegian Bokmal";
	$bp_translate['windows_locale']['nd'] = "Ndebele, North";
	$bp_translate['windows_locale']['ne'] = "Nepali";
	$bp_translate['windows_locale']['ng'] = "Ndonga";
	$bp_translate['windows_locale']['nl'] = "Dutch";
	$bp_translate['windows_locale']['nn'] = "Norwegian Nynorsk";
	$bp_translate['windows_locale']['no'] = "Norwegian";
	$bp_translate['windows_locale']['nr'] = "Ndebele, South";
	$bp_translate['windows_locale']['nv'] = "Navajo";
	$bp_translate['windows_locale']['ny'] = "Chichewa; Nyanja";
	$bp_translate['windows_locale']['oc'] = "Occitan (post 1500)";
	$bp_translate['windows_locale']['om'] = "Oromo";
	$bp_translate['windows_locale']['or'] = "Oriya";
	$bp_translate['windows_locale']['os'] = "Ossetian; Ossetic";
	$bp_translate['windows_locale']['pa'] = "Panjabi";
	$bp_translate['windows_locale']['pi'] = "Pali";
	$bp_translate['windows_locale']['pl'] = "Polish";
	$bp_translate['windows_locale']['ps'] = "Pushto";
	$bp_translate['windows_locale']['pt'] = "Portuguese";
	$bp_translate['windows_locale']['qu'] = "Quechua";
	$bp_translate['windows_locale']['rm'] = "Rhaeto-Romance";
	$bp_translate['windows_locale']['rn'] = "Rundi";
	$bp_translate['windows_locale']['ro'] = "Romanian";
	$bp_translate['windows_locale']['ru'] = "Russian";
	$bp_translate['windows_locale']['rw'] = "Kinyarwanda";
	$bp_translate['windows_locale']['sa'] = "Sanskrit";
	$bp_translate['windows_locale']['sc'] = "Sardinian";
	$bp_translate['windows_locale']['sd'] = "Sindhi";
	$bp_translate['windows_locale']['se'] = "Sami";
	$bp_translate['windows_locale']['sg'] = "Sango";
	$bp_translate['windows_locale']['si'] = "Sinhalese";
	$bp_translate['windows_locale']['sk'] = "Slovak";
	$bp_translate['windows_locale']['sl'] = "Slovenian";
	$bp_translate['windows_locale']['sm'] = "Samoan";
	$bp_translate['windows_locale']['sn'] = "Shona";
	$bp_translate['windows_locale']['so'] = "Somali";
	$bp_translate['windows_locale']['sq'] = "Albanian";
	$bp_translate['windows_locale']['sr'] = "Serbian";
	$bp_translate['windows_locale']['ss'] = "Swati";
	$bp_translate['windows_locale']['st'] = "Sotho";
	$bp_translate['windows_locale']['su'] = "Sundanese";
	$bp_translate['windows_locale']['sv'] = "Swedish";
	$bp_translate['windows_locale']['sw'] = "Swahili";
	$bp_translate['windows_locale']['ta'] = "Tamil";
	$bp_translate['windows_locale']['te'] = "Telugu";
	$bp_translate['windows_locale']['tg'] = "Tajik";
	$bp_translate['windows_locale']['th'] = "Thai";
	$bp_translate['windows_locale']['ti'] = "Tigrinya";
	$bp_translate['windows_locale']['tk'] = "Turkmen";
	$bp_translate['windows_locale']['tl'] = "Tagalog";
	$bp_translate['windows_locale']['tn'] = "Tswana";
	$bp_translate['windows_locale']['to'] = "Tonga";
	$bp_translate['windows_locale']['tr'] = "Turkish";
	$bp_translate['windows_locale']['ts'] = "Tsonga";
	$bp_translate['windows_locale']['tt'] = "Tatar";
	$bp_translate['windows_locale']['tw'] = "Twi";
	$bp_translate['windows_locale']['ug'] = "Uighur";
	$bp_translate['windows_locale']['uk'] = "Ukrainian";
	$bp_translate['windows_locale']['ur'] = "Urdu";
	$bp_translate['windows_locale']['uz'] = "Uzbek";
	$bp_translate['windows_locale']['vi'] = "Vietnamese";
	$bp_translate['windows_locale']['vo'] = "Volapuk";
	$bp_translate['windows_locale']['wo'] = "Wolof";
	$bp_translate['windows_locale']['xh'] = "Xhosa";
	$bp_translate['windows_locale']['yi'] = "Yiddish";
	$bp_translate['windows_locale']['yo'] = "Yoruba";
	$bp_translate['windows_locale']['za'] = "Zhuang";
	$bp_translate['windows_locale']['zh'] = "Chinese";
	$bp_translate['windows_locale']['zu'] = "Zulu";

?>