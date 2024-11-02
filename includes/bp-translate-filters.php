<?php 

// BuddyPress core links that are automatically converted
add_filter( 'bp_get_signup_page',						'bp_translate_convert_url' );
add_filter( 'bp_search_form_action',					'bp_translate_convert_url' );
add_filter( 'bp_core_get_userurl',						'bp_translate_convert_url' );
add_filter( 'bp_core_get_user_domain',					'bp_translate_convert_url' );
add_filter( 'bp_core_get_root_domain',					'bp_translate_convert_url' );
add_filter( 'bp_get_activity_filter_link_href',			'bp_translate_convert_url' );
add_filter( 'bp_get_group_permalink',					'bp_translate_convert_url' );
add_filter( 'bp_get_blog_permalink',					'bp_translate_convert_url' );
add_filter( 'bp_custom_get_group_forum_permalink',		'bp_translate_convert_url' );

// Translate contents
add_filter( 'bp_get_post_title',						'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'bp_activity_add',							'bp_translate_use_current_language_if_not_found_use_default_language', 0 );

// Don't allow WPMU to override the user language
remove_filter( 'locale',						'mu_locale' );
add_filter( 'locale',							'bp_translate_user_locale', 1 );

// Plugins Loaded
add_action( 'plugins_loaded',					'bp_translate_widget_init', 2 );
add_action( 'plugins_loaded',					'bp_translate_init', 2 );
add_action( 'plugins_loaded',					'bp_translate_load_textdomain', 9 );
add_action( 'plugins_loaded',					'bp_translate_catchuri', 2 );
add_filter( 'plugins_loaded',					'bp_translate_user_locale', 11 );

// BP Translate Hooks
add_filter( 'pre_post_name',					'bp_translate_slug_filter' );
add_filter( 'pre_option_rss_language',			'bp_translate_get_language', 0 );

// SuperCache specific filter
add_filter( 'supercache_dir',					'bp_translate_supercache_dir', 0 );

// Hooks (Actions)
add_action( 'wp_head',							'bp_translate_header_alt_lang' );
add_action( 'wp_head',							'bp_translate_header_google_translate' );

// Form alterations
add_action( 'edit_category_form',				'bp_translate_modify_category_form' );
add_action( 'add_tag_form',						'bp_translate_modify_tag_form' );
add_action( 'edit_tag_form',					'bp_translate_modify_tag_form' );
add_action( 'edit_link_category_form',			'bp_translate_modify_link_category_form' );

// Hooks (execution time critical filters) 
add_filter( 'the_content',						'bp_translate_use_current_language_if_not_found_show_available', 0 );
add_filter( 'the_excerpt',						'bp_translate_use_current_language_if_not_found_show_available', 0 );
add_filter( 'the_excerpt_rss',					'bp_translate_use_current_language_if_not_found_show_available', 0 );
add_filter( 'the_title',						'bp_translate_html_decode_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'esc_heml',							'bp_translate_use_default_language', 0 );
add_filter( 'comment_moderation_subject',		'bp_translate_use_default_language', 0 );
add_filter( 'comment_moderation_text',			'bp_translate_use_default_language', 0 );
add_filter( 'sanitize_title',					'bp_translate_use_raw_title', 0, 2 );
add_filter( 'get_comment_date',					'bp_translate_date_from_comment_for_current_language', 0, 2 );
add_filter( 'get_comment_time',					'bp_translate_time_from_comment_for_current_language', 0, 3 );
add_filter( 'get_the_modified_date',			'bp_translate_date_modified_from_post_for_current_language', 0, 2 );
add_filter( 'get_the_modified_time',			'bp_translate_time_modified_from_post_for_current_language', 0, 3 );

/* Remove filters on date and time while I investigate issues */
//add_filter( 'get_the_time',						'bp_translate_time_from_post_for_current_language', 0, 3 );
//add_filter( 'the_time',							'bp_translate_time_from_post_for_current_language', 0, 2 );
//add_filter( 'the_date',							'bp_translate_date_from_post_for_current_language', 0, 4 );

//add_filter( 'get_category',						'bp_translate_use_term_lib', 0 );
//add_filter( 'get_the_tags',						'bp_translate_use_term_lib', 0 );
//add_filter( 'get_tags',							'bp_translate_use_term_lib', 0 );

//add_filter( 'get_term',							'bp_translate_use_term_lib', 0 );
//add_filter( 'get_terms',						'bp_translate_use_term_lib', 0 );

// Taxonomies
add_filter( 'term_name',						'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'tag_rows',							'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'list_cats',						'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'list_pages',						'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'wp_list_categories',				'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'wp_dropdown_cats',					'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'wp_dropdown_pages',				'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'wp_title',							'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'single_post_title',				'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'bloginfo',							'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'get_others_drafts',				'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'get_bloginfo_rss',					'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'get_wp_title_rss',					'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'wp_title_rss',						'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'the_title_rss',					'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'the_content_rss',					'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'gettext',							'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'get_pages',						'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'category_description',				'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'bloginfo_rss',						'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'the_category_rss',					'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'term_links-post_tag',				'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'wp_generate_tag_cloud',			'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'link_name',						'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'link_description',					'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
add_filter( 'the_author',						'bp_translate_use_current_language_if_not_found_use_default_language', 0 );

// Links that need converting automatically
add_filter( 'author_feed_link',					'bp_translate_convert_url' );
add_filter( 'author_link',						'bp_translate_convert_url' );
add_filter( 'author_feed_link',					'bp_translate_convert_url' );
add_filter( 'day_link',							'bp_translate_convert_url' );
add_filter( 'get_comment_author_url_link',		'bp_translate_convert_url' );
add_filter( 'month_link',						'bp_translate_convert_url' );
add_filter( 'page_link',						'bp_translate_convert_url' );
add_filter( 'post_link',						'bp_translate_convert_url' );
add_filter( 'year_link',						'bp_translate_convert_url' );
add_filter( 'category_feed_link',				'bp_translate_convert_url' );
add_filter( 'category_link',					'bp_translate_convert_url' );
add_filter( 'tag_link',							'bp_translate_convert_url' );
add_filter( 'the_permalink',					'bp_translate_convert_url' );
add_filter( 'feed_link',						'bp_translate_convert_url' );
add_filter( 'post_comments_feed_link',			'bp_translate_convert_url' );
add_filter( 'tag_feed_link',					'bp_translate_convert_url' );
add_filter( 'get_pagenum_link',					'bp_translate_convert_url' );

// Add column to posts/pages
/*
add_filter( 'manage_posts_columns',				'bp_translate_language_column_header' );
add_filter( 'manage_posts_custom_column',		'bp_translate_language_column' );
add_filter( 'manage_pages_columns',				'bp_translate_language_column_header' );
add_filter( 'manage_pages_custom_column',		'bp_translate_language_column' );
*/
add_filter( 'wp_list_pages_excludes',			'bp_translate_exclude_pages' );

add_filter( 'the_editor',						'bp_translate_modify_tinymce' );
add_filter( 'bloginfo_url',						'bp_translate_convert_blog_info_url', 10, 2 );
add_filter( 'plugin_action_links',				'bp_translate_links', 10, 2 );
add_filter( 'manage_language_columns',			'bp_translate_language_columns_site' );
add_filter( 'core_version_check_locale',		'bp_translate_version_locale' );

// skip this filters if on backend
if ( !is_admin() ) {

	add_filter( 'the_category',						'bp_translate_use_term_lib', 0 );
	add_filter( 'get_terms',						'bp_translate_use_term_lib', 0 );
	add_filter( 'get_category',						'bp_translate_use_term_lib', 0 );
	add_filter( 'cat_row',							'bp_translate_use_term_lib', 0 );
	add_filter( 'cat_rows',							'bp_translate_use_term_lib', 0 );
	add_filter( 'single_tag_title',					'bp_translate_use_term_lib', 0 );
	add_filter( 'wp_get_object_terms',				'bp_translate_use_term_lib', 0 );
	add_filter( 'single_cat_title',					'bp_translate_use_term_lib', 0 );

	add_filter( 'the_posts',						'bp_translate_posts_filter' );

	// Compability with Default Widgets
	bp_translate_option_filter();

	add_filter( 'widget_title',				'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
	add_filter( 'widget_text',				'bp_translate_use_current_language_if_not_found_use_default_language', 0 );
	
	// don't filter untranslated posts in admin
	add_filter( 'posts_where_request',		'bp_translate_exclude_untranslated_posts' );
} else {
	add_action( 'admin_head',						'bp_translate_admin_header' );
	add_action( 'admin_menu',						'bp_translate_site_admin_menus', 10 );
	add_action( 'admin_menu',						'bp_translate_admin_menus', 10 );
}

?>