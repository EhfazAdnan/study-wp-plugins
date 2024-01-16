<?php

/**
 * The file that defines the cutom fucntions of the plugin
 *
 *
 *
 * @link       codeboxr.com
 * @since      1.4.6
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 */

if ( ! function_exists( 'cbxwpbookmark_object_types' ) ) {

	/**
	 * Return post types list, if plain is true then send as plain array , else array as post type groups
	 *
	 * @param  bool|false  $plain
	 *
	 * @return array
	 */
	function cbxwpbookmark_object_types( $plain = false ) {
		return CBXWPBookmarkHelper::post_types( $plain );
	}//end function cbxwpbookmark_object_types
}


if ( ! function_exists( 'show_cbxbookmark_btn' ) ):

	/**
	 * Returns bookmark button html markup
	 *
	 * @param  int  $object_id  post id
	 * @param  null  $object_type  post type
	 * @param  int  $show_count  if show bookmark counts
	 * @param  string  $extra_wrap_class  style css class
	 * @param  string  $skip_ids  post ids to skip
	 * @param  string  $skip_roles  user roles
	 *
	 * @return string
	 */
	function show_cbxbookmark_btn( $object_id = 0, $object_type = null, $show_count = 1, $extra_wrap_class = '', $skip_ids = '', $skip_roles = '' ) {
		return CBXWPBookmarkHelper::show_cbxbookmark_btn( $object_id, $object_type, $show_count, $extra_wrap_class, $skip_ids, $skip_roles );
	}//end function show_cbxbookmark_btn
endif;


if ( ! function_exists( 'cbxbookmark_post_html' ) ) {
	/**
	 * Returns bookmarks as per $instance attribues
	 *
	 * @param  array  $instance
	 * @param  bool  $echo
	 *
	 * @return void|string
	 */
	function cbxbookmark_post_html( $instance, $echo = false ) {
		$output = CBXWPBookmarkHelper::cbxbookmark_post_html( $instance );

		if ( $echo ) {
			echo '<ul class="cbxwpbookmark-list-generic cbxwpbookmark-mylist">' . $output . '</ul>';
		} else {
			return $output;
		}
	}//end function cbxbookmark_post_html
}


if ( ! function_exists( 'cbxbookmark_mycat_html' ) ) {

	/**
	 * Return users bookmark categories
	 *
	 * @param  array  $instance
	 * @param  bool  $echo
	 *
	 * @return void|string
	 */
	function cbxbookmark_mycat_html( $instance, $echo = false ) {

		$settings_api  = new CBXWPBookmark_Settings_API();
		$bookmark_mode = $settings_api->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );

		if ( $bookmark_mode == 'user_cat' || $bookmark_mode == 'global_cat' ) {
			$output = CBXWPBookmarkHelper::cbxbookmark_mycat_html( $instance );
		} else {
			$output = '<li>' . __( '<strong>Sorry, User categories or global categories can not be shown if bookmark mode is not "No Category"', 'cbxwpbookmark' ) . '</strong></li>';
		}

		$create_category_html = CBXWPBookmarkHelper::create_category_html( $instance );

		if ( $echo ) {
			echo $create_category_html . '<ul class="cbxwpbookmark-list-generic cbxbookmark-category-list cbxbookmark-category-list-' . $bookmark_mode . '">' . $create_category_html . $output . '</ul>';
		} else {
			return $output;
		}
	}//end function cbxbookmark_mycat_html
}

if ( ! function_exists( 'cbxbookmark_most_html' ) ) {
	/**
	 * Returns most bookmarked posts
	 *
	 * @param  array  $instance
	 * @param  array  $attr
	 * @param  bool  $echo
	 *
	 * @return void|string
	 */
	function cbxbookmark_most_html( $instance, $attr = [], $echo = false ) {
		$output = CBXWPBookmarkHelper::cbxbookmark_most_html( $instance, $attr );

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}//end cbxbookmark_most_html
}//end exists cbxbookmark_most_html


if ( ! function_exists( 'get_author_cbxwpbookmarks_url' ) ) {
	function get_author_cbxwpbookmarks_url( $author_id = 0 ) {
		return CBXWPBookmarkHelper::get_author_cbxwpbookmarks_url( $author_id );
	}//end function get_author_cbxwpbookmarks_url

}//end exists get_author_cbxwpbookmarks_url

if ( ! function_exists( 'cbxwpbookmarks_mybookmark_page_url' ) ) {
	/**
	 * Get mybookmark page url
	 *
	 * @return false|string
	 */
	function cbxwpbookmarks_mybookmark_page_url() {
		return CBXWPBookmarkHelper::cbxwpbookmarks_mybookmark_page_url();
	}//end function cbxwpbookmarks_mybookmark_page_url
}//end exists cbxwpbookmarks_mybookmark_page_url

if(!function_exists('cbxwpbookmarks_getTotalBookmark')){
	/**
	 * Get total bookmark for any post
	 *
	 * @param $object_id
	 *
	 * @return int
	 */
	function  cbxwpbookmarks_getTotalBookmark($object_id  = 0){
		return CBXWPBookmarkHelper::getTotalBookmark($object_id);
	}//end function cbxwpbookmarks_getTotalBookmark
}

if(!function_exists('cbxwpbookmarks_getTotalBookmarkByUser')){
	/**
	 * Get total bookmark for any post
	 *
	 * @param $user_id
	 *
	 * @return int
	 */
	function  cbxwpbookmarks_getTotalBookmarkByUser($user_id  = 0){
		return CBXWPBookmarkHelper::getTotalBookmarkByUser($user_id);
	}//end function cbxwpbookmarks_getTotalBookmark
}

if(!function_exists('cbxwpbookmarks_getTotalBookmarkByUserByPostype')){
	/**
	 * Get total bookmark by user_id by post type
	 *
	 * @param  int  $user_id
	 * @param  string  $post_type
	 *
	 * @return int
	 */
	function cbxwpbookmarks_getTotalBookmarkByUserByPostype($user_id = 0, $post_type = ''){
		return CBXWPBookmarkHelper::getTotalBookmarkByUserByPostype($user_id, $post_type);
	}//end function cbxwpbookmarks_getTotalBookmarkByUserByPostype
}


if(!function_exists('cbxwpbookmarks_getTotalBookmarkByCategory')){
	/**
	 * Get total bookmark count for any category id
	 *
	 * @param  int  $cat_id
	 *
	 * @return int
	 */
	function cbxwpbookmarks_getTotalBookmarkByCategory($cat_id = 0){
		return CBXWPBookmarkHelper::getTotalBookmarkByCategory($cat_id);
	}//end function cbxwpbookmarks_getTotalBookmarkByCategory
}

if(!function_exists('cbxwpbookmarks_getTotalBookmarkByCategoryByUser')){
	/**
	 * Get total bookmark count for any category id of any user
	 *
	 * @param  int  $cat_id
	 * @param  int  $user_id
	 *
	 * @return int
	 */
	function cbxwpbookmarks_getTotalBookmarkByCategoryByUser($cat_id = 0, $user_id = 0){
		return CBXWPBookmarkHelper::getTotalBookmarkByCategoryByUser($cat_id, $user_id);
	}//end function cbxwpbookmarks_getTotalBookmarkByCategoryByUser
}

if(!function_exists('cbxwpbookmarks_isBookmarked')){
	/**
	 * Is a post bookmarked at least once
	 *
	 * @param  int  $object_id
	 *
	 * @return bool
	 */
	function cbxwpbookmarks_isBookmarked($object_id = 0){
		return CBXWPBookmarkHelper::isBookmarked($object_id);
	}//end function cbxwpbookmarks_isBookmarked
}

if(!function_exists('cbxwpbookmarks_isBookmarkedByUser')){
	/**
	 * Is post bookmarked by user
	 *
	 * @param  int  $object_id
	 * @param  string  $user_id
	 *
	 * @return mixed
	 */
	function cbxwpbookmarks_isBookmarkedByUser($object_id = 0, $user_id = ''){
		return CBXWPBookmarkHelper::isBookmarkedByUser($object_id, $user_id);
	}//end function cbxwpbookmarks_isBookmarkedByUser
}

if(!function_exists('cbxwpbookmarks_getBookmarkCategoryById')){
	/**
	 * Get bookmark category information by id
	 *
	 * @param $catid
	 *
	 * @return array|null|object|void
	 */
	function cbxwpbookmarks_getBookmarkCategoryById($catid = 0){
		return CBXWPBookmarkHelper::getBookmarkCategoryById($catid);
	}//end function cbxwpbookmarks_getBookmarkCategoryById
}