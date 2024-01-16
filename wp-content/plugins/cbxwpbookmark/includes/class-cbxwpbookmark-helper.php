<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       codeboxr.com
 * @since      1.0.0
 *
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 */

/**
 * The core plugin helper class.
 *
 * This is used to define static methods
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Cbxwpbookmark
 * @subpackage Cbxwpbookmark/includes
 * @author     CBX Team  <info@codeboxr.com>
 */
class CBXWPBookmarkHelper {

	/**
	 * Create necessary tables for this plugin
	 */
	public static function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		// charset_collate Defination

		$bookmark = $wpdb->prefix . 'cbxwpbookmark';
		$cattable = $wpdb->prefix . 'cbxwpbookmarkcat';


		//  cbx_bookmark Table Created

		$sql = "CREATE TABLE $bookmark (
          `id` bigint(20) NOT NULL AUTO_INCREMENT,
          `object_id` bigint(20) NOT NULL DEFAULT 0,
          `object_type` varchar(60) NOT NULL DEFAULT 'post',
          `cat_id` int(11) NOT NULL DEFAULT 0,
          `user_id` bigint(20) NOT NULL DEFAULT 0,
          `created_date`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `modyfied_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`id`)) $charset_collate;";


		//  category Table Created

		$sql .= "CREATE TABLE $cattable (
          `id` mediumint(9) NOT NULL AUTO_INCREMENT,
           `cat_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
           `user_id` bigint(20) NOT NULL DEFAULT 0,
           `privacy` tinyint(2) NOT NULL DEFAULT '1',
           `locked` tinyint(2) NOT NULL DEFAULT '0',
           `created_date`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
           `modyfied_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
           PRIMARY KEY (`id`))  $charset_collate;";


		require_once( ABSPATH . "wp-admin/includes/upgrade.php" );
		dbDelta( $sql );
	}//end create_tables

	/**
	 *  Customizer default values
	 *
	 * @return array
	 */
	public static function customizer_default_values() {
		$customizer_default = [
			'shortcodes'          => 'cbxwpbookmark-mycat,cbxwpbookmark',
			'cbxwpbookmark-mycat' => [
				'title'          => esc_html__( 'Bookmark Categories', 'cbxwpbookmark' ),//if empty title will not be shown
				'order'          => "ASC",                                               //DESC, ASC
				'orderby'        => "cat_name",                                          //other possible values  id, cat_name, privacy
				'privacy'        => 2,                                                   //1 = public 0 = private  2= ignore
				'display'        => 0,                                                   //0 = list  1= dropdown,
				'show_count'     => 0,
				'show_bookmarks' => 0,  //show bookmark as sublist on click on category
				'allowedit'      => 0,  // 0 = don't  1 = yes,  allow edit and delete
				'honorauthor'    => 0,
				'base_url'       => cbxwpbookmarks_mybookmark_page_url()
			],
			'cbxwpbookmark'       => [
				'title'          => esc_html__( 'All Bookmarks', 'cbxwpbookmark' ), //if empty title will not be shown
				'order'          => 'DESC',
				'orderby'        => 'id', //id, object_id, object_type
				'limit'          => 10,
				'type'           => '', //post or object type, multiple post type in comma
				'catid'          => '', //category id
				'loadmore'       => 1,  //this is shortcode only params
				'cattitle'       => 1,  //show category title,
				'catcount'       => 1,  //show item count per category
				'allowdelete'    => 0,
				'allowdeleteall' => 0,
				'showshareurl'   => 1,
				'base_url'       => cbxwpbookmarks_mybookmark_page_url(),
			],
		];

		return apply_filters( 'cbxwpbookmark_customizer_default_values', $customizer_default );
	}//end customizer_default_values

	/**
	 * Adjust customizer default values
	 *
	 * @param  bool  $update
	 * @param  bool  $return
	 *
	 * @return array|nothing
	 */
	public static function customizer_default_adjust( $update = false, $return = false ) {
		$default_values = CBXWPBookmarkHelper::customizer_default_values();

		$store_values = get_option( 'cbxwpbookmark_customizer', [] );


		$adjusted_values = array_replace_recursive( $default_values, $store_values );

		if ( $update ) {
			update_option( 'cbxwpbookmark_customizer', $adjusted_values );
		}


		if ( $return ) {
			return $adjusted_values;
		}
	}//end customizer_default_adjust


	/**
	 * Returns post types as array
	 *
	 * @return array
	 */
	public static function post_types() {
		$post_type_args = [
			'builtin' => [
				'options' => [
					'public'   => true,
					'_builtin' => true,
					'show_ui'  => true,
				],
				'label'   => esc_html__( 'Built in post types', 'cbxwpbookmark' ),
			]

		];

		$post_type_args = apply_filters( 'cbxwpbookmark_post_types', $post_type_args );

		$output    = 'objects'; // names or objects, note names is the default
		$operator  = 'and';     // 'and' or 'or'
		$postTypes = [];

		foreach ( $post_type_args as $postArgType => $postArgTypeArr ) {
			$types = get_post_types( $postArgTypeArr['options'], $output, $operator );

			if ( ! empty( $types ) ) {
				foreach ( $types as $type ) {
					$postTypes[ $postArgType ]['label']                = $postArgTypeArr['label'];
					$postTypes[ $postArgType ]['types'][ $type->name ] = $type->labels->name;
				}
			}
		}

		return $postTypes;
	}//end post_types

	/**
	 * Return the key value pair of post types
	 *
	 * @param $all_post_types
	 *
	 * @return array
	 */
	public static function post_types_multiselect( $all_post_types ) {

		$posts_definition = [];

		foreach ( $all_post_types as $key => $post_type_defination ) {
			foreach ( $post_type_defination as $post_type_type => $data ) {
				if ( $post_type_type == 'label' ) {
					$opt_grouplabel = $data;
				}

				if ( $post_type_type == 'types' ) {
					foreach ( $data as $opt_key => $opt_val ) {
						$posts_definition[ $opt_grouplabel ][ $opt_key ] = $opt_val;
					}
				}
			}
		}

		return $posts_definition;
	}//end post_types_multiselect

	/**
	 * Plain post types list
	 *
	 * @return array
	 */
	public static function post_types_plain() {
		$post_types = self::post_types();
		$post_arr   = [];

		foreach ( $post_types as $optgroup => $types ) {
			foreach ( $types['types'] as $type_slug => $type_name ) {
				$post_arr[ esc_attr( $type_slug ) ] = wp_unslash( $type_name );
			}
		}

		return $post_arr;
	}//end post_types_plain

	/**
	 * Plain post types list in reverse
	 *
	 * @return array
	 */
	public static function post_types_plain_r() {
		$post_types = self::post_types_plain();

		$post_arr = [];

		foreach ( $post_types as $key => $value ) {
			$post_arr[ esc_attr( wp_unslash( $value ) ) ] = esc_attr( $key );
		}

		return $post_arr;
	}//end post_types_plain_r

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
	public static function show_cbxbookmark_btn( $object_id = 0, $object_type = null, $show_count = 1, $extra_wrap_class = '', $skip_ids = '', $skip_roles = '' ) {

		$settings_api = new CBXWPBookmark_Settings_API();

        $hide_for_guest   = absint( $settings_api->get_option( 'hide_for_guest', 'cbxwpbookmark_basics', 0 ) );
        if($hide_for_guest && !is_user_logged_in()) return '';


        $bookmark_mode = $settings_api->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );
        $pop_z_index   = intval( $settings_api->get_option( 'pop_z_index', 'cbxwpbookmark_basics', 1 ) );
		if ( $pop_z_index <= 0 ) {
			$pop_z_index = 1;
		}


		//format the post skip ids
		if ( $skip_ids == '' ) {
			$skip_ids = [];
		} else {
			$skip_ids = explode( ',', $skip_ids );
		}

		//format user roles
		if ( $skip_roles == '' ) {
			$skip_roles = [];
		} else {
			$skip_roles = explode( ',', $skip_roles );
		}

		$current_user   = wp_get_current_user();
		$user_id        = $current_user->ID;
		$loggedin       = ( intval( $user_id ) > 0 ) ? 1 : 0;
		$loggedin_class = ( $loggedin ) ? 'cbxwpbkmarkwrap_loggedin' : 'cbxwpbkmarkwrap_guest';

		if ( $object_id == 0 || $object_type === null ) {
			return '';
		}

		//check if there is skip post id option
		if ( sizeof( $skip_ids ) > 0 ) {
			if ( in_array( $object_id, $skip_ids ) ) {
				return '';
			}
		}

		//check if there is skip role option
		if ( sizeof( $skip_roles ) > 0 ) {
			//if(in_array($object_id, $skip_ids)) return '';
			$current_user_roles = is_user_logged_in() ? $current_user->roles : [ 'guest' ];
			if ( sizeof( array_intersect( $skip_roles, $current_user_roles ) ) > 0 ) {
				return '';
			}

		}


		do_action( 'show_cbxbookmark_btn' );

		$bookmark_class = '';
		$bookmark_total = intval( CBXWPBookmarkHelper::getTotalBookmark( $object_id ) );

		$bookmarked_by_user = CBXWPBookmarkHelper::isBookmarkedByUser( $object_id, $user_id );

		$display_label    = intval( $settings_api->get_option( 'display_label', 'cbxwpbookmark_basics', 1 ) );
		$bookmark_label   = $settings_api->get_option( 'bookmark_label', 'cbxwpbookmark_basics', '' );
		$bookmarked_label = $settings_api->get_option( 'bookmarked_label', 'cbxwpbookmark_basics', '' );
		$bookmark_label   = ( $bookmark_label == '' ) ? esc_html__( 'Bookmark', 'cbxwpbookmark' ) : $bookmark_label;
		$bookmarked_label = ( $bookmarked_label == '' ) ? esc_html__( 'Bookmarked', 'cbxwpbookmark' ) : $bookmarked_label;

		$bookmark_text = $bookmark_label;

		if ( $bookmarked_by_user ) {
			$bookmark_class = 'cbxwpbkmarktrig-marked';
			$bookmark_text  = $bookmarked_label;
		}

		$bookmark_text = apply_filters( 'cbxwpbookmark_bookmark_label_text', $bookmark_text, $bookmarked_by_user, $object_id, $object_type );

		$show_count_html = '';
		if ( $show_count ) {
			$show_count_html = ' (<i class="cbxwpbkmarktrig-count">' . $bookmark_total . '</i>)';
		}

		$nocat_loggedin_html = '';
		if ( $bookmark_mode == 'no_cat' && $loggedin ) {
			$nocat_loggedin_html = ' data-busy="0" ';
		}

		$display_label_style = '';
		if ( $display_label == 0 ) {
			$display_label_style = ' style="display:none;" ';
		}

		$login_url          = wp_login_url();
		$redirect_url       = '';
		$redirect_data_attr = '';

		if ( $user_id == 0 ):
			if ( is_singular() ) {
				$login_url    = wp_login_url( get_permalink() );
				$redirect_url = get_permalink();
			} else {
				global $wp;
				//$login_url =  wp_login_url( home_url( $wp->request ) );
				$login_url    = wp_login_url( home_url( add_query_arg( [], $wp->request ) ) );
				$redirect_url = home_url( add_query_arg( [], $wp->request ) );
			}

			$redirect_data_attr = ' data-redirect-url="' . $redirect_url . '" ';
		endif;


		$cbxwpbkmark = '<a ' . $redirect_data_attr . ' data-display-label="' . intval( $display_label ) . '" data-show-count="' . intval( $show_count ) . '" data-bookmark-label="' . esc_attr( $bookmark_label ) . '"  data-bookmarked-label="' . esc_attr( $bookmarked_label ) . '" ' . $nocat_loggedin_html . ' data-loggedin="' . intval( $loggedin ) . '" data-type="' . $object_type . '" data-object_id="' . $object_id . '" class="cbxwpbkmarktrig ' . $bookmark_class . ' cbxwpbkmarktrig-button-addto" title="' . esc_html__( 'Bookmark This',
				'cbxwpbookmark' ) . '" href="#"><span class="cbxwpbkmarktrig-label" ' . $display_label_style . '>' . esc_attr( $bookmark_text ) . $show_count_html . '</span></a>';

		if ( $user_id == 0 ):

			$cbxwpbkmark .= ' <div  data-type="' . $object_type . '" data-object_id="' . $object_id . '" class="cbxwpbkmarkguestwrap" id="cbxwpbkmarkguestwrap-' . $object_id . '">';

			//$login_url = wp_login_url();
			//$redirect_url = '';

			if ( is_singular() ) {
				$login_url    = wp_login_url( get_permalink() );
				$redirect_url = get_permalink();
			} else {
				global $wp;
				//$login_url =  wp_login_url( home_url( $wp->request ) );
				$login_url    = wp_login_url( home_url( add_query_arg( [], $wp->request ) ) );
				$redirect_url = home_url( add_query_arg( [], $wp->request ) );
			}


			$cbxwpbkmark .= '<div class="cbxwpbkmarkguest-message">';
			$cbxwpbkmark .= '<a href="#" class="cbxwpbkmarkguesttrig_close">'.esc_html__('Close', 'cbxwpbookmark').'</a>';


			/*$cbxwpbkmark_login_html  = '<a ' . apply_filters( 'cbxwpbookmark_login_link_attr', '' ) . ' class="' . apply_filters( 'cbxwpbookmark_login_link_class', 'cbxwpbkmarkguest-text' ) . '" href="' . apply_filters( 'cbxwpbookmark_login_link', $login_url ) . '">' . esc_html__( 'Please login to bookmark', 'cbxwpbookmark' ) . '</a>';*/

			$cbxwpbkmark_login_html = '<h3 class="cbxwpbookmark-title cbxwpbookmark-title-login">' . esc_html__( 'Please login to bookmark', 'cbxwpbookmark' ) . '</h3>';

			$guest_login_form = esc_attr( $settings_api->get_option( 'guest_login_form', 'cbxwpbookmark_basics', 'wordpress' ) );
			if($guest_login_form !== 'none'){
				$cbxwpbkmark_login_html .= '<a href="'.esc_url($login_url).'">'.esc_html__('Please login', 'cbxwpbookmark').'</a>';
            }
			else{
				$cbxwpbkmark_login_html .= wp_login_form( [
					'redirect' => $redirect_url,
					'echo'     => false
				] );
            }




			$cbxwpbkmark .= apply_filters( 'cbxwpbookmark_login_html', $cbxwpbkmark_login_html, $login_url, $redirect_url );

			$guest_register_html = '';
			$guest_show_register = intval( $settings_api->get_option( 'guest_show_register', 'cbxwpbookmark_basics', 1 ) );
			if ( $guest_show_register ) {
				if ( get_option( 'users_can_register' ) ) {
					$register_url        = add_query_arg( 'redirect_to', urlencode( $redirect_url ), wp_registration_url() );
					$guest_register_html .= '<p class="cbxwpbookmark-guest-register">' . sprintf( __( 'No account yet? <a href="%s">Register</a>', 'cbxwpbookmark' ), $register_url ) . '</p>';
				}

				$cbxwpbkmark .= apply_filters( 'cbxwpbookmark_register_html', $guest_register_html, $redirect_url );

			}


			$cbxwpbkmark .= '</div>';
			$cbxwpbkmark .= '</div>';


		else:

			if ( $bookmark_mode != 'no_cat' ):
				$cbxwpbkmark .= ' <div style="z-index: ' . $pop_z_index . ';"  data-type="' . $object_type . '" data-object_id="' . $object_id . '" class="cbxwpbkmarklistwrap" id="cbxwpbkmarklistwrap-' . $object_id . '">
                             <span class="addto-head"><i class="cbxwpbkmarktrig_label">' . esc_html__( 'Click Category to Bookmark', 'cbxwpbookmark' ) . '</i><i title="' . esc_html__( 'Click to close bookmark panel', 'cbxwpbookmark' ) . '"  data-object_id="' . $object_id . '" class="cbxwpbkmarktrig_close"></i></span>
                            
                            <div class="cbxwpbkmark_cat_book_list">
                                <div class="cbxlbjs cbxwpbkmark-lbjs">
									<div class="cbxlbjs-searchbar-wrapper">
										<input class="cbxlbjs-searchbar" placeholder="' . esc_html__( 'Search...', 'cbxwpbookmark' ) . '">
										<i class="cbxlbjs-searchbar-icon"></i>
									</div>
									<ul class="cbxwpbookmark-list-generic cbxlbjs-list cbxwpbkmarklist" style="" data-type="' . $object_type . '" data-object_id="' . $object_id . '">
									</ul>
								</div>
                            </div>';

				if ( $bookmark_mode == 'user_cat' ) :

					$category_default_status = intval( $settings_api->get_option( 'category_status', 'cbxwpbookmark_basics', 1 ) );
					$hide_cat_privacy        = intval( $settings_api->get_option( 'hide_cat_privacy', 'cbxwpbookmark_basics', 0 ) );

					$cat_hide_class = ( $hide_cat_privacy == 1 ) ? 'cbxwpbkmark_cat_hide' : '';

					$cbxwpbkmark .= '
								<div class="cbxwpbkmark_cat_edit_list">
									<div class="cbxlbjs cbxwpbkmark-lbjs">
										<div class="cbxlbjs-searchbar-wrapper">
											<input class="cbxlbjs-searchbar" placeholder="' . esc_html__( 'Search...', 'cbxwpbookmark' ) . '">
											<i class="cbxlbjs-searchbar-icon"></i>
										</div>
										<ul class="cbxwpbookmark-list-generic cbxlbjs-list cbxwpbkmarklist" style="" data-type="' . $object_type . '" data-object_id="' . $object_id . '">
										</ul>
									</div>
								</div>
                            	<div class="cbxwpbkmark_cat_add_form">
                            		<p class="cbxwpbkmark-form-note"> </p> 
                                    <div class="cbxwpbkmark-field-wrap">
                                        <input required placeholder="' . esc_html__( 'Type Category Name', 'cbxwpbookmark' ) . '" type="text" class="cbxwpbkmark-field cbxwpbkmark-field-text  cbxwpbkmark-field-cat cbxwpbkmark-field-cat-add" />
                                        <input required type="hidden" name="cbxwpbkmark-field-catid" class="cbxwpbkmark-field-catid" value="0" />
                                    </div>
                                    <div class="cbxwpbkmark-field-wrap">                                                                               
                                        <div class="cbxwpbkmarkaddnewcatselect ' . $cat_hide_class . '">
                                          <select class="cbxwpbkmark-field cbxwpbkmark-field-select  cbxwpbkmark-field-privacy cbxwpbkmark-field-privacy_' . $object_id . '">
                                          	<option ' . selected( $category_default_status, 1, false ) . ' value="1">' . esc_html__( 'Public Category', 'cbxwpbookmark' ) . '</option>
                                          	<option ' . selected( $category_default_status, 0, false ) . ' value="0">' . esc_html__( 'Private Category', 'cbxwpbookmark' ) . '</option>
										  </select>                                          
                                        </div>
                                        <span data-object_id="' . $object_id . '" class="cbxwpbkmark-field-create-submit" title="' . esc_html__( 'Create Category', 'cbxwpbookmark' ) . '"></span>
                                        <span class="cbxwpbkmark-field-create-close" title="' . esc_html__( 'Close', 'cbxwpbookmark' ) . '"></span>
                                    </div>
                                    <div class="cbxwpbkmark-clearfix"></div>
                                </div>
                                <div class="cbxwpbkmark_cat_edit_form">
                                	<p class="cbxwpbkmark-form-note"> </p>
                                    <div class="cbxwpbkmark-field-wrap">
                                        <input required placeholder="' . esc_html__( 'Category Name', 'cbxwpbookmark' ) . '" type="text" class="cbxwpbkmark-field cbxwpbkmark-field-text  cbxwpbkmark-field-cat cbxwpbkmark-field-cat-edit" />
                                        <input required type="hidden" name="cbxwpbkmark-field-catid" class="cbxwpbkmark-field-catid" value="0" />
                                    </div>
                                    <div class="cbxwpbkmark-field-wrap">                                        
                                        <div class="cbxwpbkmarkmanagecatselect ' . $cat_hide_class . '">
                                        	<select class="cbxwpbkmark-field cbxwpbkmark-field-select  cbxwpbkmark-field-privacy cbxwpbkmark-field-privacy_' . $object_id . '">
	                                            <option value="1">' . esc_html__( 'Public Category', 'cbxwpbookmark' ) . '</option>
	                                            <option value="0">' . esc_html__( 'Private Category', 'cbxwpbookmark' ) . '</option>
										  	</select>                                          
                                        </div>
                                        <span class="cbxwpbkmark-field-update-submit" data-object_id="' . $object_id . '"  title="' . esc_html__( 'Edit Category', 'cbxwpbookmark' ) . '"></span>
                                        <span class="cbxwpbkmark-field-update-close" title="' . esc_html__( 'Close', 'cbxwpbookmark' ) . '"></span>
                                        <span class="cbxwpbkmark-field-delete-submit" data-object_id="' . $object_id . '" href="#" title="' . esc_html__( 'Delete', 'cbxwpbookmark' ) . '"></span>
                                    </div>
                                    <div class="cbxwpbkmark-clearfix"></div>
                                </div>
								<div class="cbxwpbkmark-toolbar">
									<span class="cbxwpbkmark-toolbar-newcat" data-type="' . $object_type . '" data-object_id="' . $object_id . '" >' . esc_html__( 'New Category', 'cbxwpbookmark' ) . '</span>
									<span class="cbxwpbkmark-toolbar-listcat" data-type="' . $object_type . '" data-object_id="' . $object_id . '" >' . esc_html__( 'List Category', 'cbxwpbookmark' ) . '</span>
									<span class="cbxwpbkmark-toolbar-editcat" data-type="' . $object_type . '" data-object_id="' . $object_id . '" >' . esc_html__( 'Manage Category', 'cbxwpbookmark' ) . '</span>
									<div class="cbxwpbkmark-clearfix"></div>									
								</div><!-- end .cbxwpbkmark-toolbar -->';
				endif;

				$cbxwpbkmark .= '<p class="cbxwpbkmarkloading" style="text-align: center;"><img src="' . CBXWPBOOKMARK_ROOT_URL . 'assets/img/ajax-loader.gif' . '" alt="loading" title="' . esc_html__( 'loading categories', 'cbxwpbookmark' ) . '" /> </p>

                          </div>
                        ';
			endif;


		endif;

		$cbxwpbkmark = '<div data-object_id="' . $object_id . '" class="cbxwpbkmarkwrap ' . esc_attr( $loggedin_class ) . ' cbxwpbkmarkwrap_' . $bookmark_mode . ' cbxwpbkmarkwrap-' . $object_type . ' ' . esc_attr($extra_wrap_class) . '">' . $cbxwpbkmark . '</div>';


		return $cbxwpbkmark;
	}//end show_cbxbookmark_btn

	/**
	 * Returns bookmarks as per $instance attribues
	 *
	 * @param  array  $instance
	 *
	 * @return false|string
	 */
	public static function cbxbookmark_post_html( $instance ) {

		global $wpdb;

		$object_types                = CBXWPBookmarkHelper::object_types( true ); //get plain post type as array
		$cbxwpbookmrak_table         = $wpdb->prefix . 'cbxwpbookmark';
		$cbxwpbookmak_category_table = $wpdb->prefix . 'cbxwpbookmarkcat';

		$setting = new CBXWPBookmark_Settings_API();

		$bookmark_mode = $setting->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );

		$limit   = isset( $instance['limit'] ) ? intval( $instance['limit'] ) : 10;
		$orderby = isset( $instance['orderby'] ) ? esc_attr( $instance['orderby'] ) : 'id';
		$order   = isset( $instance['order'] ) ? esc_attr( $instance['order'] ) : 'DESC';
		$type    = isset( $instance['type'] ) ? wp_unslash( $instance['type'] ) : []; //object type(post types), multiple as array


		//old format compatibility
		if ( is_string( $type ) ) {
			$type = explode( ',', $type );
		}

		$type = array_filter( $type );


		$offset = isset( $instance['offset'] ) ? intval( $instance['offset'] ) : 0;
		$catid  = isset( $instance['catid'] ) ? wp_unslash( $instance['catid'] ) : [];
		if ( $catid == 0 ) {
			$catid = '';
		}//compatibility with previous shortcode default values
		if ( is_string( $catid ) ) {
			$catid = explode( ',', $catid );
		}
		$catid = array_filter( $catid );

		$cattitle    = isset( $instance['cattitle'] ) ? intval( $instance['cattitle'] ) : 0; //Show category title
		$allowdelete = isset( $instance['allowdelete'] ) ? intval( $instance['allowdelete'] ) : 0;


		$userid_attr = isset( $instance['userid'] ) ? intval( $instance['userid'] ) : 0;

		$userid = 0;

		//if ( $userid_attr == 0 ) {
		//$userid = get_current_user_id(); //get current logged in user id
		//} else {
		$userid = $userid_attr;
		//}


		$privacy = 2; //all

		if ( $userid == 0 || ( $userid != get_current_user_id() ) ) {
			$allowdelete = 0;
			$privacy     = 1; //only public

			$instance['privacy']     = $instance;
			$instance['allowdelete'] = $allowdelete;
		}


		ob_start();

		$main_sql             = '';
		$cat_sql              = '';
		$category_privacy_sql = '';
		$type_sql             = '';

		//category filter sql
		if ( is_array( $catid ) && sizeof( $catid ) > 0 && ( $bookmark_mode != 'no_cat' ) ) {
			$cats_ids_str = implode( ', ', $catid );
			$cat_sql      .= " AND cat_id IN ($cats_ids_str) ";
		}

		//get cats
		$cats = [];
		if ( $bookmark_mode == 'user_cat' ) {
			if ( $privacy != 2 ) {
				$cats = $wpdb->get_results( $wpdb->prepare( "SELECT *  FROM  $cbxwpbookmak_category_table WHERE privacy = %d", $privacy ), ARRAY_A );

			} else {
				$cats = $wpdb->get_results( "SELECT *  FROM  $cbxwpbookmak_category_table WHERE 1", ARRAY_A );
			}

			//category privacy sql only needed for user_cat mode
			$cats_ids = [];
			if ( is_array( $cats ) && sizeof( $cats ) > 0 ) {
				foreach ( $cats as $cat ) {
					$cats_ids[] = intval( $cat['id'] );
				}
				$cats_ids_str         = implode( ', ', $cats_ids );
				$category_privacy_sql .= " AND cat_id IN ($cats_ids_str) ";
			}
		} elseif ( $bookmark_mode == 'global_cat' ) {
			// Executing Query
			$cats = $wpdb->get_results( "SELECT *  FROM  $cbxwpbookmak_category_table WHERE 1", ARRAY_A );
		}

		//used for category title
		$cats_arr = [];
		if ( is_array( $cats ) && sizeof( $cats ) > 0 ) {
			foreach ( $cats as $cat ) {
				$cats_arr[ intval( $cat['id'] ) ] = $cat;
			}
		}

		$join = '';

		if ( $orderby == 'title' ) {

			$posts_table = $wpdb->prefix . 'posts'; //core posts table
			$join        .= " LEFT JOIN $posts_table posts ON posts.ID = bookmarks.object_id ";

			$orderby = 'posts.post_title';
		}

		if ( sizeof( $type ) == 0 ) {
			$param    = [ $userid, $offset, $limit ];
			$main_sql .= "SELECT *  FROM $cbxwpbookmrak_table AS bookmarks $join WHERE user_id = %d $cat_sql $category_privacy_sql group by object_id  ORDER BY $orderby $order LIMIT %d, %d";
		} else {
			$type_sql .= " AND object_type IN ('" . implode( "','", $type ) . "') ";

			$param    = [ $userid, $offset, $limit ];
			$main_sql .= "SELECT *  FROM $cbxwpbookmrak_table AS bookmarks $join  WHERE user_id = %d $type_sql $cat_sql $category_privacy_sql group by object_id   ORDER BY $orderby $order LIMIT %d, %d";
		}

		$items = $wpdb->get_results( $wpdb->prepare( $main_sql, $param ) );


		// checking If results are available
		if ( $items !== null && sizeof( $items ) > 0 ) {

			foreach ( $items as $item ) {

				$action_html = ( $allowdelete ) ? '&nbsp; <span class="cbxbookmark-delete-btn cbxbookmark-post-delete" data-busy="0" data-object_id="' . $item->object_id . '" data-object_type="' . $item->object_type . '" data-bookmark_id="' . $item->id . '"></span></a>' : '';

				$sub_item_class = '';

				if ( in_array( $item->object_type, $object_types ) ) {
					echo cbxwpbookmark_get_template_html( 'bookmarkpost/single.php', [
						'item'           => $item,
						'instance'       => $instance,
						'setting'        => $setting,
						'action_html'    => $action_html,
						'sub_item_class' => $sub_item_class, //used in category widget to display sub list

					] );

				} else {
					//do_action( 'cbxwpbookmark_othertype_item', $item, $instance, $setting, $action_html, $sub_item_class );
					do_action( 'cbxwpbookmark_item_othertype', $item, $instance, $setting, $action_html, $sub_item_class );
				}

			}
		} else {
			echo cbxwpbookmark_get_template_html( 'bookmarkpost/single-notfound.php', [] );

		}
		?>
		<?php

		$output = ob_get_clean();


		return $output;
	}//end cbxbookmark_post_html


	/**
	 * Returns most bookmarked posts
	 *
	 * @param  array  $instance
	 * @param  array  $attr
	 *
	 * @return false|string
	 */
	public static function cbxbookmark_most_html( $instance, $attr = [] ) {

		$setting = new CBXWPBookmark_Settings_API();

		$object_types = CBXWPBookmarkHelper::object_types( true ); //get plain post type as array

		$limit   = isset( $instance['limit'] ) ? intval( $instance['limit'] ) : 10;
		$daytime = isset( $instance['daytime'] ) ? intval( $instance['daytime'] ) : 0;
		$orderby = isset( $instance['orderby'] ) ? esc_attr( $instance['orderby'] ) : 'object_id'; //id, object_id, object_type, object_count
		$order   = isset( $instance['order'] ) ? esc_attr( $instance['order'] ) : 'DESC';

		$title = isset( $instance['title'] ) ? sanitize_text_field( $instance['title'] ) : '';

		$show_count = isset( $instance['show_count'] ) ? intval( $instance['show_count'] ) : 1;
		$show_thumb = isset( $instance['show_thumb'] ) ? intval( $instance['show_thumb'] ) : 1;

		$type = isset( $instance['type'] ) ? wp_unslash( $instance['type'] ) : []; //object type(post types), multiple as array


		//old format compatibility
		if ( is_string( $type ) ) {
			$type = explode( ',', $type );
		}

		$type = array_filter( $type );


		$ul_class = isset( $attr['ul_class'] ) ? $attr['ul_class'] : '';
		$li_class = isset( $attr['li_class'] ) ? $attr['li_class'] : '';


		$thumb_size = 'thumbnail';
		$thumb_attr = [];


		$daytime = (int) $daytime;
		ob_start();

		if ( $title != '' ) {
			echo '<h3 class="cbxwpbookmark-title cbxwpbookmark-title-most">' . $title . '</h3>';
		}
		?>


        <ul class="cbxwpbookmark-list-generic cbxwpbookmark-mostlist <?php echo esc_attr( $ul_class ); ?>">
			<?php

			global $wpdb;

			$cbxwpbookmrak_table = $wpdb->prefix . 'cbxwpbookmark';

			// Getting Current User ID
			$userid = get_current_user_id();

			$where_sql    = '';
			$datetime_sql = "";
			if ( $daytime != '0' || ! empty( $daytime ) ) {
				$time         = date( 'Y-m-d H:i:s', strtotime( '-' . $daytime . ' day' ) );
				$datetime_sql .= " created_date > '$time' ";

				$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $datetime_sql;
			}


			if ( sizeof( $type ) > 0 ) {
				$type_sql  = " object_type IN ('" . implode( "','", $type ) . "') ";
				$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $type_sql;
			}

			if ( $where_sql == '' ) {
				$where_sql = '1';
			}

			$param = [ $limit ];

			if ( $orderby == 'object_count' ) {
				$sql = "SELECT count(object_id) as totalobject, object_id, object_type FROM  $cbxwpbookmrak_table AS bookmarks WHERE $where_sql group by object_id order by totalobject $order LIMIT %d";
			} else {

				$join = '';

				if ( $orderby == 'title' ) {

					$posts_table = $wpdb->prefix . 'posts'; //core posts table
					$join        .= " LEFT JOIN $posts_table posts ON posts.ID = bookmarks.object_id ";

					$orderby = 'posts.post_title';
				}

				$sql = "SELECT count(object_id) as totalobject, object_id, object_type FROM  $cbxwpbookmrak_table AS bookmarks $join WHERE $where_sql group by object_id order by $orderby $order, totalobject $order LIMIT %d";
			}


			$items = $wpdb->get_results( $wpdb->prepare( $sql, $param ) );

			// Checking for available results
			if ( $items != null || sizeof( $items ) > 0 ) {

				foreach ( $items as $item ) {
					$show_count_html = ( $show_count == 1 ) ? '<i>(' . intval( $item->totalobject ) . ')</i>' : '';

					if ( in_array( $item->object_type, $object_types ) ) {
						echo cbxwpbookmark_get_template_html( 'bookmarkmost/single.php', [
							'item'            => $item,
							'instance'        => $instance,
							'setting'         => $setting,
							'li_class'        => $li_class,
							'show_count_html' => $show_count_html
						] );
					} else {
						//do_action( 'cbxwpbookmark_othertype_mostitem', $item, array_merge( $instance, $attr ), $setting, $li_class, $show_count_html );
						do_action( 'cbxwpbookmark_mostitem_othertype', $item, array_merge( $instance, $attr ), $setting, $li_class, $show_count_html );
					}
				}
			} else {
				echo cbxwpbookmark_get_template_html( 'bookmarkmost/single-notfound.php', [ 'li_class' => $li_class ] );
			}
			?>
        </ul>
		<?php

		$output = ob_get_clean();

		return $output;
	}//end cbxbookmark_most_html

	/**
	 * Return users/global bookmark categories
	 *
	 * @param  array  $instance
	 *
	 * @return false|string
	 */
	public static function cbxbookmark_mycat_html( $instance ) {
		global $wpdb;

		$settings_api           = new CBXWPBookmark_Settings_API();
		$user_bookmark_page_url = cbxwpbookmarks_mybookmark_page_url();
		$bookmark_mode          = $settings_api->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );

		if ( $bookmark_mode == 'no_cat' ) {
			return '';
		}

		$privacy    = isset( $instance['privacy'] ) ? intval( $instance['privacy'] ) : 1; //1 = public, 0 = private 2 = ignore
		$orderby    = isset( $instance['orderby'] ) ? $instance['orderby'] : 'cat_name';
		$order      = isset( $instance['order'] ) ? $instance['order'] : 'ASC';
		$show_count = isset( $instance['show_count'] ) ? intval( $instance['show_count'] ) : 0;
		$title      = isset( $instance['title'] ) ? sanitize_text_field( $instance['title'] ) : '';


		$display = isset( $instance['display'] ) ? intval( $instance['display'] ) : 0;  //0 = list , 1 = dropdown


		$show_bookmarks = isset( $instance['show_bookmarks'] ) ? intval( $instance['show_bookmarks'] ) : 0;  //0 = don't , 1 = show bookmarks as sublist


		$base_url = isset( $instance['base_url'] ) ? esc_url( $instance['base_url'] ) : $user_bookmark_page_url;
		if ( $base_url != '' ) {
			$user_bookmark_page_url = $base_url;
		}

		$allowedit = isset( $instance['allowedit'] ) ? intval( $instance['allowedit'] ) : 0;


		$user_id = isset( $instance['userid'] ) ? intval( $instance['userid'] ) : 0;


		$userid = $user_id;


		/*if ( $userid == 0 ) {
			$userid = get_current_user_id(); //get current logged in user id
		}*/


		if ( ! is_user_logged_in() || $bookmark_mode != 'user_cat' ) {
			$allowedit = 0;
		}


		//either
		if ( $userid == 0 || ( $userid != get_current_user_id() ) ) {
			$privacy   = 1;
			$allowedit = 0;
		}

		$output = '';


		/*if( $title != '' ) {
			$output .= '<h3 class="cbxwpbookmark-title cbxwpbookmark-title-mycat">'.$title.'</h3>';
		}*/

		//ob_start();
		?>

		<?php


		if ( ( $userid > 0 && $bookmark_mode == 'user_cat' ) || ( $bookmark_mode == 'global_cat' ) ) {

			$cbxwpbookmak_category_table = $wpdb->prefix . 'cbxwpbookmarkcat';
			$cbxwpbookmrak_table         = $wpdb->prefix . 'cbxwpbookmark';


			// Getting Current User ID
			//$userid = get_current_user_id();

			// Checking the Type of privacy
			// 2 means -- ALL -- Public and private both options in widget area

			$category_privacy_sql = '';
			if ( $privacy != 2 && $bookmark_mode == 'user_cat' ) {
				$category_privacy_sql = $wpdb->prepare( ' AND privacy = %d ', $privacy );
			}


			if ( $bookmark_mode == 'user_cat' ) {
				$items = $wpdb->get_results(
					$wpdb->prepare( "SELECT *  FROM  $cbxwpbookmak_category_table WHERE user_id = %d  $category_privacy_sql   ORDER BY $orderby $order", $userid )
				);
			} elseif ( $bookmark_mode == 'global_cat' ) {
				$items = $wpdb->get_results( "SELECT *  FROM  $cbxwpbookmak_category_table WHERE 1   ORDER BY $orderby $order" );
			}


			// Checking for available results
			if ( $items != null || sizeof( $items ) > 0 ) {
				if ( $display == 0 ) {
					//list view
					foreach ( $items as $item ) {
						$list_data_attr = '';

						$cat_pernalink   = $user_bookmark_page_url;
						$show_count_html = '';


						$action_html = ( $allowedit ) ? '<span  class="cbxbookmark-edit-btn" ></span> <span class="cbxbookmark-delete-btn"  data-id="' . $item->id . '"></span>' : '';


						$category_count_user_query = '';
						if ( $bookmark_mode == 'user_cat' ) {
							$category_count_user_query = $wpdb->prepare( " AND user_id = %d", intval( $userid ) );

						}
						$category_count_query = "SELECT count(*) as totalobject from $cbxwpbookmrak_table where cat_id = %d $category_count_user_query";
						$count_total          = $wpdb->get_var( $wpdb->prepare( $category_count_query, intval( $item->id ) ) );

						if ( $show_count == 1 ) {
							$show_count_html = '<i>(' . number_format_i18n( $count_total ) . ')</i>';
						}

						$list_data_attr .= '  data-id="' . $item->id . '" ';

						if ( $allowedit || $show_bookmarks ) {
							//$list_data_attr .= ' class="cbxbookmark-mycat-item" data-privacy="' . $item->privacy . '"  data-name="' . $item->cat_name . '" ';
							$list_data_attr .= ' data-userid="' . $userid . '"   data-privacy="' . $item->privacy . '" data-name="' . wp_unslash( $item->cat_name ) . '" ';
						}

						$cat_pernalink = add_query_arg( [
							'cbxbmcatid' => $item->id,
							'userid'     => $user_id
						], $cat_pernalink );

						//if show bookmark as sublist
						$sub_list_class = '';
						if ( $show_bookmarks ) {
							//$perpage        = 10;
							$perpage        = apply_filters( 'cbxwpbookmark_sublist_perpage', 10 );
							$total_page     = ceil( $count_total / $perpage );
							$list_data_attr .= ' data-processed="0" data-page="1" data-totalpage="' . $total_page . '" data-total="' . $count_total . '" ';
							$sub_list_class = 'cbxbookmark-category-list-item-expand';
						}


						$output .= '<li class="cbxbookmark-category-list-item ' . $sub_list_class . '" ' . $list_data_attr . '> <a href="' . esc_url( $cat_pernalink ) . '" class="cbxlbjs-item-widget" data-privacy="' . $item->privacy . '">' . wp_unslash( $item->cat_name ) . '</a>' . $show_count_html . $action_html . '</li>';
					}//end for each

					if ( ! $show_bookmarks ) {
						$output .= '<li class="cbxbookmark-category-list-item cbxbookmark-category-list-item-notfound"> <a  href="' . $user_bookmark_page_url . '" class="cbxlbjs-item-widget" >' . esc_html__( 'All Categories', 'cbxwpbookmark' ) . '</a></li>';
					}

				} elseif ( $display == 1 ) {
					//dropdown
					$selected_wpbmcatid = ( isset( $_REQUEST["cbxbmcatid"] ) && intval( $_REQUEST["cbxbmcatid"] ) > 0 ) ? intval( $_REQUEST["cbxbmcatid"] ) : '';

					$output .= '<select id="cbxlbjs-item-widget_dropdown" class="cbxlbjs-item-widget_dropdown">';

					$output .= '<option ' . selected( $selected_wpbmcatid, '', false ) . ' value="">' . esc_html__( 'All Categories', 'cbxwpbookmark' ) . '</option>';

					foreach ( $items as $item ) {

						$cat_pernalink = $cat_pernalink_format = $user_bookmark_page_url;
						if ( strpos( $cat_pernalink, '?' ) !== false ) {
							$cat_pernalink_format = $cat_pernalink . '&';
						} else {
							$cat_pernalink_format = $cat_pernalink . '?';
						}

						$show_count_html = '';

						if ( $show_count == 1 ) {


							$category_count_user_query = '';
							if ( $bookmark_mode == 'user_cat' ) {
								$category_count_user_query = $wpdb->prepare( " AND user_id = %d", intval( $userid ) );

							}

							$count_query = "SELECT count(*) as totalobject from $cbxwpbookmrak_table where cat_id = %d $category_count_user_query";
							$num         = $wpdb->get_var( $wpdb->prepare( $count_query, intval( $item->id ) ) );

							$show_count_html = ' <i>(' . number_format_i18n( $num ) . ')</i>';
						}

						$output .= '<option ' . selected( $selected_wpbmcatid, intval( $item->id ), false ) . ' class="cbxlbjs-item-widget" value = ' . intval( $item->id ) . ' data-privacy="' . $item->privacy . '"> ' . wp_unslash( $item->cat_name ) . $show_count_html . '</option>';
					}


					$output .= '</select>';
					$output .= '<script type=\'text/javascript\'>
                                    (function() {
                                        var dropdown = document.getElementById( "cbxlbjs-item-widget_dropdown" );
                                        var wpbmpage_url = "' . $cat_pernalink_format . '";
                                        var wpbmpage_root = "' . $cat_pernalink . '";
                                        var selected_cat = "' . $selected_wpbmcatid . '";
                                        
                                        function onwpbmCatChange() {
                                            if ( dropdown.options[ dropdown.selectedIndex ].value > 0 ) {
                                                location.href = wpbmpage_url + "cbxbmcatid=" + dropdown.options[ dropdown.selectedIndex ].value;
                                            }else if( dropdown.options[ dropdown.selectedIndex ].value == ""){
                                                location.href = wpbmpage_root;
                                            }
                                        }                                        
                                        
                                        dropdown.onchange = onwpbmCatChange;
                                    })();
                             </script>';
				}

			} else {
				if ( $display == 0 ) {
					$output .= '<li class="cbxbookmark-category-list-item-notfound">' . esc_html__( 'No category found.', 'cbxwpbookmark' ) . '</li>';
				} else {
					$output .= '<p>' . esc_html__( 'No category found.', 'cbxwpbookmark' ) . '</p>';
				}


			}
		} else {

			$cbxbookmark_login_link = sprintf( __( 'Please <a href="%s">login</a> to view Category', 'cbxwpbookmark' ),
				wp_login_url( $user_bookmark_page_url )
			);

			//todo: integrate the login form

			$output .= '<li>' . $cbxbookmark_login_link . '</li>';


		} ?>

		<?php

		//$output = ob_get_clean();
		return $output;
	}//end cbxbookmark_mycat_html

	/**
	 * Get author's bookmark url
	 *
	 * @param  int  $author_id
	 *
	 * @return mixed|string|void
	 */
	public static function get_author_cbxwpbookmarks_url( $author_id = 0 ) {
		$author_id = absint( $author_id );
		if ( $author_id == 0 ) {
			return '';
		}
		$get_author_cbxwpbookmarks_url = cbxwpbookmarks_mybookmark_page_url();
		$get_author_cbxwpbookmarks_url = add_query_arg( 'userid', $author_id, $get_author_cbxwpbookmarks_url );

		return apply_filters( 'get_author_cbxwpbookmarks_url', $get_author_cbxwpbookmarks_url );
	}//get_author_cbxwpbookmarks_url

	/**
	 * Get mybookmark page url
	 *
	 * @return false|string
	 */
	public static function cbxwpbookmarks_mybookmark_page_url() {
		$settings_api      = new CBXWPBookmark_Settings_API();
		$mybookmark_pageid = absint( $settings_api->get_option( 'mybookmark_pageid', 'cbxwpbookmark_basics', 0 ) );

		$mybookmark_page_url = '#';
		if ( $mybookmark_pageid > 0 ) {
			$mybookmark_page_url = get_permalink( $mybookmark_pageid );
		}

		return apply_filters( 'cbxwpbookmarks_mybookmark_page_url', $mybookmark_page_url );
	}//end cbxwpbookmarks_mybookmark_page_url

	/**
	 * Get total bookmark for any post id
	 *
	 * @param  int  $object_id
	 *
	 * @return int
	 */
	public static function getTotalBookmark($object_id  = 0 ) {
		global $wpdb;
		$cbxwpbookmrak_table = $wpdb->prefix . 'cbxwpbookmark';

		$object_id = absint( $object_id );

		if ( $object_id == 0 ) {
			global $post;
			$object_id = $post->ID;
		}

		$query = "SELECT count(DISTINCT user_id) as count FROM $cbxwpbookmrak_table WHERE object_id= %d GROUP BY object_id ";

		$count = $wpdb->get_var( $wpdb->prepare( $query, $object_id ) );

		return ( $count === null ) ? 0 : intval( $count );
	}//end getTotalBookmark


	/**
	 * Get total bookmark by user_id
	 *
	 * @param  int  $user_id
	 *
	 * @return int
	 */
	public static function getTotalBookmarkByUser( $user_id = 0 ) {
		global $wpdb;
		$cbxwpbookmrak_table = $wpdb->prefix . 'cbxwpbookmark';

		$user_id = absint( $user_id );

		if ( $user_id == 0 ) {
			return 0;
		}

		$query = "SELECT count(DISTINCT object_id) as count FROM $cbxwpbookmrak_table WHERE user_id= %d";

		$count = $wpdb->get_var( $wpdb->prepare( $query, $user_id ) );

		return ( $count === null ) ? 0 : intval( $count );
	}//end getTotalBookmarkByUser

	/**
	 * Get total bookmark by user_id by post type
	 *
	 * @param  int  $user_id
	 * @param  string  $post_type
	 *
	 * @return int
	 */
	public static function getTotalBookmarkByUserByPostype( $user_id = 0, $post_type = '' ) {
		global $wpdb;
		$cbxwpbookmrak_table = $wpdb->prefix . 'cbxwpbookmark';

		$user_id = absint( $user_id );

		if ( $user_id == 0 ) {
			return 0;
		}
		if ( $post_type == '' ) {
			return 0;
		}

		$query = "SELECT count(DISTINCT object_id) as count FROM $cbxwpbookmrak_table WHERE user_id= %d AND object_type = %s";

		$count = $wpdb->get_var( $wpdb->prepare( $query, $user_id, $post_type ) );

		return ( $count === null ) ? 0 : intval( $count );
	}//end getTotalBookmarkByUserByPostype


	/**
	 * Get total bookmark count for any category id
	 *
	 * @param  int  $cat_id
	 *
	 * @return int
	 */
	public static function getTotalBookmarkByCategory( $cat_id = 0 ) {
		global $wpdb;
		$cbxwpbookmrak_table = $wpdb->prefix . 'cbxwpbookmark';

		if ( $cat_id == 0 ) {
			return 0;
		}

		$query = "SELECT count(*) as count from $cbxwpbookmrak_table where cat_id = %d";
		$count = $wpdb->get_var( $wpdb->prepare( $query, $cat_id ) );

		return ( $count === null ) ? 0 : intval( $count );
	}//end getTotalBookmarkByCategory

	/**
	 * Get total bookmark count for any category id of any user
	 *
	 * @param  int  $cat_id
	 * @param  int  $user_id
	 *
	 * @return int
	 */
	public static function getTotalBookmarkByCategoryByUser( $cat_id = 0, $user_id = 0 ) {
		global $wpdb;
		$cbxwpbookmrak_table = $wpdb->prefix . 'cbxwpbookmark';

		$cat_id  = absint( $cat_id );
		$user_id = absint( $user_id );

		if ( $cat_id == 0 ) {
			return 0;
		}

		if ( $user_id == 0 ) {
			return 0;
		}

		$query = "SELECT count(*) as count from $cbxwpbookmrak_table where cat_id = %d AND user_id = %d";
		$count = $wpdb->get_var( $wpdb->prepare( $query, $cat_id, $user_id ) );

		return ( $count === null ) ? 0 : intval( $count );
	}//end getTotalBookmarkByCategoryByUser

	/**
	 * Is a post bookmarked at least once
	 *
	 * @param  int  $object_id
	 *
	 * @return bool
	 */
	public static function isBookmarked( $object_id = 0 ) {
		if ( $object_id == 0 ) {
			global $post;
			$object_id = $post->ID;
		}

		$total_count = intval( CBXWPBookmarkHelper::getTotalBookmark( $object_id ) );

		return ( $total_count > 0 ) ? true : false;
	}//end isBookmarked

	/**
	 * Is post bookmarked by user
	 *
	 * @param  int  $object_id
	 * @param  string  $user_id
	 *
	 * @return mixed
	 */
	public static function isBookmarkedByUser( $object_id = 0, $user_id = '' ) {
		if ( $object_id == 0 ) {
			global $post;
			$object_id = $post->ID;
		}

		//if still object id
		if ( intval( $object_id ) == 0 ) {
			return false;
		}

		if ( $user_id == '' ) {
			//$current_user = wp_get_current_user();
			$user_id = get_current_user_id();
		}

		//if user id not found or guest user
		if ( intval( $user_id ) == 0 ) {
			return false;
		}

		global $wpdb;
		$cbxwpbookmrak_table = $wpdb->prefix . 'cbxwpbookmark';

		$query = "SELECT count(DISTINCT user_id) as count FROM $cbxwpbookmrak_table WHERE object_id= %d AND user_id = %d GROUP BY object_id ";

		$count = $wpdb->get_var( $wpdb->prepare( $query, $object_id, $user_id ) );
		if ( $count !== null && intval( $count ) > 0 ) {
			return true;
		} else {
			return false;
		}
	}//end isBookmarkedByUser

	/**
	 * Is Bookmarked by User (deprecated as name is confusing)
	 *
     * @deprecated
     *
	 * @param  int  $object_id
	 * @param  string  $user_id
	 *
	 * @return bool
	 */
	public static function isBookmarkedUser( $object_id = 0, $user_id = '' ) {
		return CBXWPBookmarkHelper::isBookmarkedByUser( $object_id, $user_id );
	}//end isBookmarkedByUser

	/**
	 * Get bookmark category information by id
	 *
	 * @param $catid
	 *
	 * @return array|null|object|void
	 */
	public static function getBookmarkCategoryById( $catid = 0 ) {
		if ( intval( $catid ) == 0 ) {
			return [];
		}

		global $wpdb;
		$cbxwpbookmak_category_table = $wpdb->prefix . 'cbxwpbookmarkcat';
		$cbxwpbookmrak_table         = $wpdb->prefix . 'cbxwpbookmark';

		$category = $wpdb->get_row(
			$wpdb->prepare( "SELECT *  FROM  $cbxwpbookmak_category_table WHERE id = %d", $catid ),
			ARRAY_A
		);

		return ( $category === null ) ? [] : $category;
	}//end getBookmarkCategoryById

	/**
	 * Get the user roles for voting purpose
	 *
	 * @param  string  $useCase
	 * @param  bool  $include_guest
	 *
	 * @return array
	 */
	public static function user_roles( $plain = true, $include_guest = false ) {
		global $wp_roles;

		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/user.php' );

		}

		$userRoles = [];
		if ( $plain ) {
			foreach ( get_editable_roles() as $role => $roleInfo ) {
				$userRoles[ $role ] = $roleInfo['name'];
			}
			if ( $include_guest ) {
				$userRoles['guest'] = esc_html__( "Guest", 'cbxwpbookmark' );
			}
		} else {
			$userRoles_r = [];
			foreach ( get_editable_roles() as $role => $roleInfo ) {
				$userRoles_r[ $role ] = $roleInfo['name'];
			}

			$userRoles = [
				'Registered' => $userRoles_r,
			];

			if ( $include_guest ) {
				$userRoles['Anonymous'] = [
					'guest' => esc_html__( "Guest", 'cbxwpbookmark' )
				];
			}
		}

		return apply_filters( 'cbxwpbookmark_userroles', $userRoles, $plain, $include_guest );
	}//end user_roles

	/**
	 * Get all the registered image sizes along with their dimensions
	 *
	 * @return array $image_sizes The image sizes
	 * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
	 *
	 * @global array $_wp_additional_image_sizes
	 */
	public static function get_all_image_sizes() {
		global $_wp_additional_image_sizes;

		$default_image_sizes = get_intermediate_image_sizes();

		foreach ( $default_image_sizes as $size ) {
			$image_sizes[ $size ]['width']  = intval( get_option( "{$size}_size_w" ) );
			$image_sizes[ $size ]['height'] = intval( get_option( "{$size}_size_h" ) );
			$image_sizes[ $size ]['crop']   = get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
		}

		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
			$image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
		}

		return apply_filters( 'cbxwpbookmark_all_thumbnail_sizes', $image_sizes );
	}//end get_all_image_sizes


	/**
	 * Well textual format for available image sizes
	 *
	 * @return array
	 */
	public static function get_all_image_sizes_formatted() {
		$image_sizes     = CBXWPBookmarkHelper::get_all_image_sizes();
		$image_sizes_arr = [];

		foreach ( $image_sizes as $key => $image_size ) {
			$width      = ( isset( $image_size['width'] ) && intval( $image_size['width'] ) > 0 ) ? intval( $image_size['width'] ) : esc_html__( 'Unknown', 'cbxwpbookmark' );
			$height     = ( isset( $image_size['height'] ) && intval( $image_size['height'] ) > 0 ) ? intval( $image_size['height'] ) : esc_html__( 'Unknown', 'cbxwpbookmark' );
			$proportion = ( isset( $image_size['crop'] ) && intval( $image_size['crop'] ) == 1 ) ? esc_html__( 'Proportional', 'cbxwpbookmark' ) : '';
			if ( $proportion != '' ) {
				$proportion = ' - ' . $proportion;
			}
			$image_sizes_arr[ $key ] = $key . '(' . $width . 'x' . $height . ')' . $proportion;
		}

		return apply_filters( 'cbxwpbookmark_all_thumbnail_sizes_formatted', $image_sizes_arr );
	}//end get_all_image_sizes_formatted

	/**
	 * Get all  core tables list
	 */
	public static function getAllDBTablesList() {
		global $wpdb;

		$bookmark = $wpdb->prefix . 'cbxwpbookmark';
		$cattable = $wpdb->prefix . 'cbxwpbookmarkcat';

		$table_names                            = [];
		$table_names['Bookmark List Table']     = $bookmark;
		$table_names['Bookmark Category Table'] = $cattable;


		return apply_filters( 'cbxwpbookmark_table_list', $table_names );
	}//end getAllDBTablesList

	/**
	 * List all global option name with prefix cbxwpbookmark_
	 */
	public static function getAllOptionNames() {
		global $wpdb;

		$prefix       = 'cbxwpbookmark_';
		$option_names = $wpdb->get_results( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE '{$prefix}%'", ARRAY_A );

		return apply_filters( 'cbxwpbookmark_option_names', $option_names );
	}//end getAllOptionNames

	/**
	 * Options name only
	 *
	 * @return array
	 */
	public static function getAllOptionNamesValues()
	{
		$option_values = self::getAllOptionNames();
		$names_only    = [];

		foreach ($option_values as $key => $value) {
			$names_only[] = $value['option_name'];
		}

		return $names_only;
	}//end method getAllOptionNamesValues

	/**
	 * Return post types list, if plain is true then send as plain array , else array as post type groups
	 *
	 * @param  bool|false  $plain
	 *
	 * @return array
	 */
	public static function object_types( $plain = false ) {
		$post_type_args = [
			'builtin' => [
				'options' => [
					'public'   => true,
					'_builtin' => true,
					'show_ui'  => true,
				],
				'label'   => esc_html__( 'Built in post types', 'cbxwpbookmark' ),
			]
		];

		$post_type_args = apply_filters( 'cbxwpbookmark_post_types', $post_type_args );

		$output    = 'objects'; // names or objects, note names is the default
		$operator  = 'and';     // 'and' or 'or'
		$postTypes = [];

		foreach ( $post_type_args as $postArgType => $postArgTypeArr ) {
			$types = get_post_types( $postArgTypeArr['options'], $output, $operator );

			if ( ! empty( $types ) ) {
				foreach ( $types as $type ) {
					$postTypes[ $postArgType ]['label']                = $postArgTypeArr['label'];
					$postTypes[ $postArgType ]['types'][ $type->name ] = $type->labels->name;
				}
			}
		}


		if ( $plain ) {
			$plain_list = [];
			if ( isset( $postTypes['builtin']['types'] ) ) {

				foreach ( $postTypes['builtin']['types'] as $key => $name ) {
					$plain_list[] = $key;
				}
			}

			if ( isset( $postTypes['custom']['types'] ) ) {

				foreach ( $postTypes['custom']['types'] as $key => $name ) {
					$plain_list[] = $key;
				}
			}

			return $plain_list;
		} else {
			return $postTypes;
		}
	}//end object_types

	/**
	 * Post type formatted for customizer dropdown/multi select dropdown
	 *
	 * @return array
	 */
	public static function object_types_customizer_format() {
		$object_types = CBXWPBookmarkHelper::object_types();

		$object_types_formatted = [];

		foreach ( $object_types as $category_key => $category_items ) {
			$label = esc_attr( $category_items['label'] );

			$object_types_formatted[ $label ] = $category_items['types'];
		}

		return $object_types_formatted;
	}//end object_types_customizer_format


	/**
	 * @param $timestamp
	 *
	 * @return false|string
	 */
	public static function dateReadableFormat( $timestamp, $format = 'M j, Y' ) {
		$format = ( $format == '' ) ? 'M j, Y' : $format;

		return date( $format, strtotime( $timestamp ) );
	}//end dateReadableFormat

	/**
	 * Get all bookmarks by object id
	 *
	 * @param  int  $object_id
	 *
	 * @return array|null|object|void
	 */
	public static function getBookmarksByObject( $object_id = 0 ) {
		global $wpdb;
		$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';

		$object_id = intval( $object_id );

		$bookmarks = null;
		if ( $object_id > 0 ) {
			$join = $where_sql = $sql_select = '';
			//$join = " LEFT JOIN $table_users AS users ON users.ID = log.user_id ";

			$where_sql = $wpdb->prepare( "log.object_id=%d", $object_id );

			$sql_select = "SELECT log.* FROM $bookmark_table AS log";

			$bookmarks = $wpdb->get_results( "$sql_select $join WHERE $where_sql ", 'ARRAY_A' );
		}

		return $bookmarks;
	}//end singleBookmark

	/**
	 * Get single bookmark information by id
	 *
	 * @param  int  $bookmark_id
	 *
	 * @return array|null|object|void
	 */
	public static function singleBookmark( $bookmark_id = 0 ) {
		global $wpdb;
		$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';

		$bookmark_id = intval( $bookmark_id );

		$single_bookmark = null;
		if ( $bookmark_id > 0 ) {
			$join = $where_sql = $sql_select = '';
			//$join = " LEFT JOIN $table_users AS users ON users.ID = log.user_id ";

			$where_sql = $wpdb->prepare( "log.id=%d", $bookmark_id );

			$sql_select = "SELECT log.* FROM $bookmark_table AS log";

			$single_bookmark = $wpdb->get_row( "$sql_select $join WHERE $where_sql ", 'ARRAY_A' );
		}

		return $single_bookmark;
	}//end singleBookmark

	/**
	 * Get single bookmark information by Object id and user id
	 *
	 * @param  int  $object_id
	 * @param  int  $user_id
	 *
	 * @return array|null|object|void
	 */
	public static function singleBookmarkByObjectUser( $object_id = 0, $user_id = 0 ) {
		global $wpdb;
		$bookmark_table = $wpdb->prefix . 'cbxwpbookmark';

		$object_id = intval( $object_id );
		$user_id   = intval( $user_id );

		$single_bookmark = null;
		if ( $object_id > 0 && $user_id > 0 ) {
			$join = $where_sql = $sql_select = '';
			//$join = " LEFT JOIN $table_users AS users ON users.ID = log.user_id ";

			$where_sql = $wpdb->prepare( "log.object_id = %d AND log.user_id = %d", $object_id, $user_id );

			$sql_select = "SELECT log.* FROM $bookmark_table AS log";

			$single_bookmark = $wpdb->get_row( "$sql_select $join WHERE $where_sql ", 'ARRAY_A' );
		}

		return $single_bookmark;
	}//end singleBookmark

	/**
	 * Get single category information by id
	 *
	 * @param  int  $bookmark_id
	 *
	 * @return array|null|object|void
	 */
	public static function singleCategory( $category_id = 0 ) {
		global $wpdb;
		$category_table = $wpdb->prefix . 'cbxwpbookmarkcat';

		$category_id = intval( $category_id );

		$single_category = null;
		if ( $category_id > 0 ) {
			$join = $where_sql = $sql_select = '';
			//$join = " LEFT JOIN $table_users AS users ON users.ID = log.user_id ";

			$where_sql = $wpdb->prepare( "log.id=%d", $category_id );

			$sql_select = "SELECT log.* FROM $category_table AS log";

			$single_category = $wpdb->get_row( "$sql_select $join WHERE $where_sql ", 'ARRAY_A' );
		}

		return $single_category;
	}//end singleBookmark

	/**
	 * Array for privacy status with title
	 *
	 * @return array
	 */
	public static function privacy_status_arr() {
		$privacy_arr = [
			'1' => esc_html__( 'Public', 'cbxwpbookmark' ),
			'0' => esc_html__( 'Private', 'cbxwpbookmark' ),
		];

		return $privacy_arr;
	}//end privacy_status_arr


	/**
	 * Check Is Admin compatible with rest api
	 *
	 * @return bool
	 */
	public static function is_admin() {
		if ( isset( $GLOBALS['current_screen'] ) ) {
			return $GLOBALS['current_screen']->in_admin();
		} elseif ( defined( 'WP_ADMIN' ) ) {
			return WP_ADMIN;
		}

		return false;
	}//end is_admin

	/**
	 * @param  string  $code  name of the shortcode
	 * @param  string  $content
	 *
	 * @return string content with shortcode striped
	 */
	public static function strip_shortcode( $code = '', $content = '' ) {
		if ( $code == '' ) {
			return $content;
		}

		if ( ! has_shortcode( $content, $code ) ) {
			return $content;
		}

		global $shortcode_tags;

		$stack          = $shortcode_tags;
		$shortcode_tags = [ $code => 1 ];

		$content = strip_shortcodes( $content );

		$shortcode_tags = $stack;

		return $content;
	}//end method strip_shortcode

	/**
	 * Bookmark login form
	 *
	 * @return array
	 */
	public static function guest_login_forms() {
		$forms = [];

		$forms['wordpress'] = esc_html__( 'WordPress Core Login Form', 'cbxwpbookmark' );
		$forms['none'] = esc_html__( 'Don\'t show login form', 'cbxwpbookmark' );

		return apply_filters( 'cbxwpbookmark_guest_login_forms', $forms );
	}//end guest_login_forms

	/**
	 * Add utm params to any url
	 *
	 * @param  string  $url
	 *
	 * @return string
	 */
	public static function url_utmy( $url = '' ) {
		if ( $url == '' ) {
			return $url;
		}

		$url = add_query_arg( [
			'utm_source'   => 'plgsidebarinfo',
			'utm_medium'   => 'plgsidebar',
			'utm_campaign' => 'wpfreemium',
		], $url );

		return $url;
	}//end url_utmy

	/**
	 * New category create html markup for category shortcode/category list display
	 *
	 * @param  array  $instance
	 *
	 * @return string
	 */
	public static function create_category_html( $instance = [] ) {
		$settings_api = new CBXWPBookmark_Settings_API();

		$bookmark_mode           = $settings_api->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );
		$category_default_status = intval( $settings_api->get_option( 'category_status', 'cbxwpbookmark_basics', 1 ) );
		$hide_cat_privacy        = intval( $settings_api->get_option( 'hide_cat_privacy', 'cbxwpbookmark_basics', 0 ) );

		$cat_hide_class = ( $hide_cat_privacy == 1 ) ? 'cbxwpbkmark_cat_hide' : '';


		$create_category_html = '';

		if ( intval( $instance['allowedit'] ) && $bookmark_mode == 'user_cat' ) {
			$create_category_html .= '<div id="cbxbookmark-category-list-create-wrap">';
			$create_category_html .= '<span class="cbxbookmark-category-list-create">' . esc_html__( 'Create New Category', 'cbxwpbookmark' ) . '</span>';

			$create_category_html .= '<div class="cbxbookmark-category-list-create-form">';
			$create_category_html .= '<p class="cbxbookmark-category-list-create-msg"></p>';
			$create_category_html .= '<div class="cbxbookmark-mycat-editbox">
                <input class="cbxbmedit-catname cbxbmedit-catname-add" name="catname" value="" placeholder="' . esc_html__( 'Category title', 'cbxwpbookmark' ) . '" />                
                <select class="cbxbmedit-privacy input-catprivacy  ' . $cat_hide_class . '" name="catprivacy">
                  <option ' . selected( $category_default_status, 1, false ) . ' value="1" title="Public Category">' . esc_html__( 'Public', 'cbxwpbookmark' ) . '</option>
                  <option ' . selected( $category_default_status, 0, false ) . ' value="0" title="Private Category">' . esc_html__( 'Private', 'cbxwpbookmark' ) . '</option>
                </select>
                <a data-busy="0" href="#" class="cbxbookmark-btn cbxbookmark-cat-save">' . esc_html__( 'Create', 'cbxwpbookmark' ) . ' <span class="cbxbm_busy" style="display:none;"></span></a>
                <a href="#" class="cbxbookmark-btn cbxbookmark-cat-close">' . esc_html__( 'Close', 'cbxwpbookmark' ) . '</a>
                <div class="clear clearfix cbxwpbkmark-clearfix"></div>
            </div>';
			$create_category_html .= '</div>';

			$create_category_html .= '</div>';
		}

		return $create_category_html;
	}//end create_category_html

	/**
	 * Get user own category count
	 *
	 * @param  int  $user_id
	 *
	 * @return int
	 */
	public static function user_owned_cat_counter( $user_id = 0 ) {
		$user_id = intval( $user_id );


		$user_id = ( $user_id == 0 ) ? get_current_user_id() : $user_id;
		if ( $user_id == 0 ) {
			return 0;
		}

		global $wpdb;
		$category_table = $wpdb->prefix . 'cbxwpbookmarkcat';
		$user_cat_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $category_table WHERE user_id = %d", [ $user_id ] ) );

		return intval( $user_cat_count );
	}//end user_owned_cat_counter

	/**
	 * initialize session(we are not using session any more)
	 */
	public static function init_session() {

		if ( defined( 'DOING_CRON' ) ) {
			return;
		}

		if ( CBXWPBookmarkHelper::is_rest() ) {
			return;
		}

		/**
		 * Start sessions if not exists
		 *
		 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
		 */
		if ( version_compare( PHP_VERSION, '7.0.0', '>=' ) ) {
			if ( function_exists( 'session_status' ) && session_status() == PHP_SESSION_NONE ) {
				session_start( [
					'cache_limiter'  => 'private_no_expire',
					'read_and_close' => false,
				] );
			}
		} elseif ( version_compare( PHP_VERSION, '5.4.0', '>=' ) && version_compare( PHP_VERSION, '7.0.0', '<' ) ) {
			if ( function_exists( 'session_status' ) && session_status() == PHP_SESSION_NONE ) {
				session_cache_limiter( 'private_no_expire' );
				session_start();
			}
		} else {
			if ( session_id() == '' ) {
				if ( version_compare( PHP_VERSION, '4.0.0', '>=' ) ) {
					session_cache_limiter( 'private_no_expire' );
				}
				session_start();
			}
		}

	}//end method

	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * https://wordpress.stackexchange.com/a/317041/6343
	 *
	 * Case #1: After WP_REST_Request initialisation
	 * Case #2: Support "plain" permalink settings
	 * Case #3: It can happen that WP_Rewrite is not yet initialized,
	 *          so do this (wp-settings.php)
	 * Case #4: URL Path begins with wp-json/ (your REST prefix)
	 *          Also supports WP installations in subfolders
	 *
	 * @returns boolean
	 * @author matzeeable
	 */
	public static function is_rest() {
		$prefix = rest_get_url_prefix();
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST // (#1)
		     || isset( $_GET['rest_route'] ) // (#2)
		        && strpos( trim( $_GET['rest_route'], '\\/' ), $prefix, 0 ) === 0 ) {
			return true;
		}
		// (#3)
		global $wp_rewrite;
		if ( $wp_rewrite === null ) {
			$wp_rewrite = new WP_Rewrite();
		}

		// (#4)
		$rest_url    = wp_parse_url( trailingslashit( rest_url() ) );
		$current_url = wp_parse_url( add_query_arg( [] ) );

		return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
	}//end is_rest

	/**
	 * Bookmarks Themes Initials
	 *
	 * @return mixed|void
	 */
	public static function themes() {
		$thems = [
			'cbxwpbookmark-default'   => esc_html__( 'Default', 'cbxwpbookmark' ),
			'cbxwpbookmark-red'       => esc_html__( 'Red', 'cbxwpbookmark' ),
			'cbxwpbookmark-purple'    => esc_html__( 'Purple', 'cbxwpbookmark' ),
			'cbxwpbookmark-indigo'    => esc_html__( 'Indigo', 'cbxwpbookmark' ),
			'cbxwpbookmark-blue'      => esc_html__( 'Blue', 'cbxwpbookmark' ),
			'cbxwpbookmark-teal'      => esc_html__( 'Teal', 'cbxwpbookmark' ),
			'cbxwpbookmark-green'     => esc_html__( 'Green', 'cbxwpbookmark' ),
			'cbxwpbookmark-orange'    => esc_html__( 'Orange', 'cbxwpbookmark' ),
			'cbxwpbookmark-brown'     => esc_html__( 'Brown', 'cbxwpbookmark' ),
			'cbxwpbookmark-blue-gray' => esc_html__( 'Blue Gray', 'cbxwpbookmark' ),
		];

		return apply_filters( 'cbxwpbookmark_themes', $thems );
	}//end themes

	/**
	 * Admin page slugs
	 *
	 * @return mixed|void
	 */
	public static function admin_page_slugs() {
		//$slugs = [ 'cbxwpbookmarkdash', 'cbxwpbookmark', 'cbxwpbookmarkcats', 'cbxwpbookmark_settings' ];
		$slugs = [ 'cbxwpbookmarkdash', 'cbxwpbookmark', 'cbxwpbookmarkcats'];

		return apply_filters( 'cbxwpbookmark_admin_page_slugs', $slugs );
	}//end admin_page_slugs

	/**
	 * Get user display name
	 *
	 * @param  null  $user_id
	 *
	 * @return string
	 */
	public static function userDisplayName( $user_id = null ) {
		$current_user      = $user_id ? new WP_User( $user_id ) : wp_get_current_user();
		$user_display_name = $current_user->display_name;
		if ( $user_display_name != '' ) {
			return $user_display_name;
		}

		if ( $current_user->first_name ) {
			if ( $current_user->last_name ) {
				return $current_user->first_name . ' ' . $current_user->last_name;
			}

			return $current_user->first_name;
		}

		return esc_html__( 'Unnamed', 'cbxwpbookmark' );
	}//end method userDisplayName

	/**
	 * Get bookmarks by user id
	 *
	 * @param $user_id
	 *
	 * @return array|null|object|void
	 * @version 1.7.0
	 *
	 */
	public static function getBookmarksByUser( $user_id = 0 ) {
		$user_id = absint( $user_id );

		if ( $user_id == 0 ) {
			return [];
		}

		global $wpdb;

		$cbxwpbookmrak_table = $wpdb->prefix . 'cbxwpbookmark';

		$bookmarks = $wpdb->get_results(
			$wpdb->prepare( "SELECT *  FROM  $cbxwpbookmrak_table WHERE user_id = %d", $user_id ),
			ARRAY_A
		);

		if ( $bookmarks !== null ) {
			return $bookmarks;
		}

		return [];
	}//end getBookmarksByUser

	/**
	 * My Bookmark user of a user
	 *
	 * @param  array  $user_id
	 *
	 * @return string
	 */
	public static function myBookmarksShareUrl( $attr = [] ) {
		$share_url = '#';
		$user_id   = isset( $attr['userid'] ) ? intval( $attr['userid'] ) : 0;


		if ( $user_id == 0 ) {
			return $share_url;
		}

		$base_url = isset( $attr['base_url'] ) ? esc_url( $attr['base_url'] ) : cbxwpbookmarks_mybookmark_page_url();

		$share_url = add_query_arg( [
			'userid' => $user_id
		], $base_url );

		$cbxbmcatid = isset( $_REQUEST['cbxbmcatid'] ) ? intval( $_REQUEST['cbxbmcatid'] ) : 0;

		if ( $cbxbmcatid > 0 ) {
			$share_url = add_query_arg( [
				'cbxbmcatid' => $cbxbmcatid
			], $share_url );
		}


		return $share_url;
	}//end method myBookmarksShareUrl


	/**
	 * Number field sanitization
	 *
	 * @param $number
	 * @param $setting
	 *
	 * @return int
	 */
	public static function sanitize_number_field( $number, $setting ) {
		// Ensure $number is an absolute integer (whole number, zero or greater).
		$number = absint( $number );

		// If the input is an absolute integer, return it; otherwise, return the default
		return ( $number ? $number : $setting->default );
	}//end sanitize_number_field

	public static function text_sanitization( $input ) {
		if ( strpos( $input, ',' ) !== false ) {
			$input = explode( ',', $input );
		}

		if ( is_array( $input ) ) {
			foreach ( $input as $key => $value ) {
				$input[ $key ] = sanitize_text_field( $value );
			}
			$input = implode( ',', $input );
		} else {
			$input = sanitize_text_field( $input );
		}

		return $input;
	}//end text_sanitization

	/**
	 * Returns setting sections
	 *
	 * @return void
     * @since 1.7.14
	 */
	public static function cbxwpbookmark_setting_sections()
	{
		return apply_filters( 'cbxwpbookmark_setting_sections',
			[
				[
					'id'    => 'cbxwpbookmark_basics',
					'title' => esc_html__( 'General Settings', 'cbxwpbookmark' ),
				],
				[
					'id'    => 'cbxwpbookmark_tools',
					'title' => esc_html__( 'Tools', 'cbxwpbookmark' ),
				],
			]
		);
	}//end method cbxwpbookmark_setting_sections

	/**
	 * Return setting fields
	 *
	 * @return void
     * @since 1.7.14
	 */
    public static function cbxwpbookmark_setting_fields(){
	    $settings = new CBXWPBookmark_Settings_API();

	    global $wp_roles;
	    // now this is for meta box
	    $roles = CBXWPBookmarkHelper::user_roles( false, true );


	    $posts_definition = CBXWPBookmarkHelper::post_types_multiselect( CBXWPBookmarkHelper::post_types() );


	    $post_types_automation_default = $settings->get_option( 'cbxbookmarkposttypes', 'cbxwpbookmark_basics', [] );

	    if ( ! is_array( $post_types_automation_default ) ) {
		    $post_types_automation_default = [];
	    }


	    $posts_definition_automation = [];

	    foreach ( $posts_definition as $group_name => $post_types ) {
		    foreach ( $post_types as $post_type_key => $post_type_name ) {
			    if ( in_array( $post_type_key, $post_types_automation_default ) ) {
				    $posts_definition_automation[ $post_type_key ] = $post_type_name;
			    }
		    }
	    }

	    $table_html = '<div id="cbxwpbookmark_resetinfo_wrap">'.esc_html__('Loading ...', 'cbxwpbookmark').'</div>';


	    $pages         = get_pages();
	    $pages_options = [];
	    if ( $pages ) {
		    foreach ( $pages as $page ) {
			    $pages_options[ $page->ID ] = $page->post_title;
		    }
	    }


	    $mybookmark_pageid = absint( $settings->get_option( 'mybookmark_pageid', 'cbxwpbookmark_basics', 0 ) );

	    $mybookmark_pageid_link_html = '';
	    if ( $mybookmark_pageid > 0 ) {
		    $mybookmark_pageid_link      = cbxwpbookmarks_mybookmark_page_url();
		    $mybookmark_pageid_link_html = sprintf( __( 'Visit <a href="%s" target="_blank">My Bookmarks</a> Page', 'cbxwpbookmark' ), $mybookmark_pageid_link );
	    } else {
		    $mybookmark_pageid_link_html = esc_html__( 'My Bookmark Page doesn\'t exists.', 'cbxwpbookmark' ) . ' ' . __( 'Please <a data-busy="0" id="cbxwpbookmark_autocreate_page" class="button" href="#" target="_blank">click here</a> to create. If <strong>My Bookmark Page Method</strong> is <strong>Customizer</strong> then only page will be created without shortcode as shortcode is not needed for customizer method.', 'cbxwpbookmark' );
	    }

	    $mybookmark_customizer_url_html = '';
	    if ( $mybookmark_pageid > 0 ) {
		    $mybookmark_customizer_url      = add_query_arg( [
			    'autofocus' => [ 'panel' => 'cbxwpbookmark' ],
			    'url'       => cbxwpbookmarks_mybookmark_page_url()
		    ], admin_url( 'customize.php' ) );
		    $mybookmark_customizer_url_html = '<a href="' . esc_url( $mybookmark_customizer_url ) . '">' . esc_html__( 'Configure using customizer', 'cbxwpbookmark' ) . '</a>';
	    } else {
		    $mybookmark_customizer_url_html = __( 'To configure <strong>My Bookmarks</strong> page using customizer please create a page and set as my bookmark page using above setting.', 'cbxwpbookmark' );
	    }

	    $gust_login_forms = CBXWPBookmarkHelper::guest_login_forms();
	    $bookmarks_themes = CBXWPBookmarkHelper::themes();

	    $settings_builtin_fields =
		    [
			    'cbxwpbookmark_basics' => [
				    'basics_heading'     => [
					    'name'    => 'basics_heading',
					    'label'   => esc_html__( 'General Settings', 'cbxwpbookmark' ),
					    'type'    => 'heading',
					    'default' => '',
				    ],
				    'display_theme'      => [
					    'name'    => 'display_theme',
					    'label'   => esc_html__( 'Select Theme', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Select predefine theme.', 'cbxwpbookmark' ),
					    'type'    => 'select',
					    'default' => 'cbxwpbookmark-default',
					    'options' => $bookmarks_themes,
				    ],
				    'display_label'      => [
					    'name'    => 'display_label',
					    'label'   => esc_html__( 'Display Bookmark Label', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Display the label Bookmark or Bookmarked. This param has no shortcode method, if enabled works everywhere, if disabled then same.', 'cbxwpbookmark' ),
					    'type'    => 'select',
					    'default' => '1',
					    'options' => [
						    '1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
						    '0' => esc_html__( 'No', 'cbxwpbookmark' ),

					    ],
				    ],
				    'bookmark_label'     => [
					    'name'    => 'bookmark_label',
					    'label'   => esc_html__( 'Bookmark Label', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Example: Bookmark. If empty then label will be used from translation', 'cbxwpbookmark' ),
					    'type'    => 'text',
					    'default' => '',
				    ],
				    'bookmarked_label'   => [
					    'name'    => 'bookmarked_label',
					    'label'   => esc_html__( 'Bookmarked Label', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Example: Bookmarked. If empty then label will be used from translation', 'cbxwpbookmark' ),
					    'type'    => 'text',
					    'default' => '',
				    ],
				    'bookmark_mode'      => [
					    'name'    => 'bookmark_mode',
					    'label'   => esc_html__( 'Bookmark Mode', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Default is category belongs to user, other two mode is global category and no category quick bookmark.', 'cbxwpbookmark' ),
					    'type'    => 'select',
					    'default' => 'user_cat',
					    'options' => [
						    'user_cat'   => esc_html__( 'User owns category', 'cbxwpbookmark' ),
						    'global_cat' => esc_html__( 'Global Category', 'cbxwpbookmark' ),
						    'no_cat'     => esc_html__( 'No Category(Single Click Bookmark)', 'cbxwpbookmark' ),
					    ],
				    ],
				    'category_status'    => [
					    'name'    => 'category_status',
					    'label'   => esc_html__( 'Category Default Status', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Category Default Status If user category mode is selected', 'cbxwpbookmark' ),
					    'type'    => 'radio',
					    'default' => '1',
					    'options' => [
						    '1' => esc_html__( 'Public', 'cbxwpbookmark' ),
						    '0' => esc_html__( 'Private', 'cbxwpbookmark' ),
					    ],
				    ],
				    'hide_cat_privacy'   => [
					    'name'    => 'hide_cat_privacy',
					    'label'   => esc_html__( 'Hide Category Privacy Field', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Hide category privacy field if user category mode is selected. Default status will be used from above setting. This feature does\'t disable the category feature but hides from user interface.', 'cbxwpbookmark' ),
					    'type'    => 'radio',
					    'default' => '0',
					    'options' => [
						    '1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
						    '0' => esc_html__( 'No', 'cbxwpbookmark' ),
					    ],
				    ],
				    'cbxbookmarkpostion' => [
					    'name'    => 'cbxbookmarkpostion',
					    'label'   => esc_html__( 'Auto Integration', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Bookmark button auto integration position', 'cbxwpbookmark' ),
					    'type'    => 'select',
					    'default' => 'after_content',
					    'options' => [
						    'before_content' => esc_html__( 'Before Content', 'cbxwpbookmark' ),
						    'after_content'  => esc_html__( 'After Content', 'cbxwpbookmark' ),
						    'disable'        => esc_html__( 'Disable Auto Integration', 'cbxwpbookmark' ),
					    ],
				    ],
				    'skip_ids'           => [
					    'name'     => 'skip_ids',
					    'label'    => esc_html__( 'Skip Post Id(s)', 'cbxwpbookmark' ),
					    'desc'     => esc_html__( 'Skip to show bookmark button for post id, put post id as comma separated for multiple', 'cbxwpbookmark' ),
					    'type'     => 'text',
					    'default'  => '',
					    'desc_tip' => true,
				    ],
				    'skip_roles'         => [
					    'name'     => 'skip_roles',
					    'label'    => esc_html__( 'Skip for User Role', 'cbxwpbookmark' ),
					    'desc'     => esc_html__( 'Skip to show bookmark button for user roles', 'cbxwpbookmark' ),
					    //'type'     => 'multiselect',
					    'type'     => 'select',
					    'multi'     => 1,
					    'optgroup' => 1,
					    'options'  => $roles,
					    'default'  => [],
					    'desc_tip' => true,
				    ],
				    'showinarchive'      => [
					    'name'    => 'showinarchive',
					    'label'   => esc_html__( 'Show in Archive', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Show in Archive', 'cbxwpbookmark' ),
					    'type'    => 'radio',
					    'default' => '0',
					    'options' => [
						    '1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
						    '0' => esc_html__( 'No', 'cbxwpbookmark' ),
					    ],
				    ],
				    'showinhome'         => [
					    'name'    => 'showinhome',
					    'label'   => esc_html__( 'Show in Home', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Show in Home', 'cbxwpbookmark' ),
					    'type'    => 'radio',
					    'default' => '0',
					    'options' => [
						    '1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
						    '0' => esc_html__( 'No', 'cbxwpbookmark' ),
					    ],
				    ],

				    'cbxbookmarkposttypes'  => [
					    'name'     => 'cbxbookmarkposttypes',
					    'label'    => esc_html__( 'Post Type Selection', 'cbxwpbookmark' ),
					    'desc'     => esc_html__( 'Bookmark will work for selected post types', 'cbxwpbookmark' ),
					    //'type'     => 'multiselect',
					    'type'     => 'select',
					    'multi'     => 1,
					    'optgroup' => 1,
					    'default'  => [ 'post', 'page' ],
					    'options'  => $posts_definition,
				    ],
				    'post_types_automation' => [
					    'name'     => 'post_types_automation',
					    'label'    => esc_html__( 'Post Type Auto Integration', 'cbxwpbookmark' ),
					    'desc'     => esc_html__( 'For which post types auto integration will be used', 'cbxwpbookmark' ),
					    //'type'     => 'multiselect',
					    'type'     => 'select',
					    'multi'     => 1,
					    'optgroup' => 0,
					    'default'  => $post_types_automation_default,
					    'options'  => $posts_definition_automation,
				    ],
				    'showcount'             => [
					    'name'    => 'showcount',
					    'label'   => esc_html__( 'Show count', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Show bookmark count', 'cbxwpbookmark' ),
					    'type'    => 'radio',
					    'default' => '1',
					    'options' => [
						    '1' => esc_html__( 'Yes', 'cbxwpbookmark' ),
						    '0' => esc_html__( 'No', 'cbxwpbookmark' ),
					    ],
				    ],
				    'mybookmark_pageid'     => [
					    'name'    => 'mybookmark_pageid',
					    'label'   => esc_html__( 'My Bookmark Page', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'User\'s private(or public based on shortcode/customizer params) bookmark page.', 'cbxwpbookmark' ) . ' ' . $mybookmark_pageid_link_html,
					    'type'    => 'select',
					    'default' => 0,
					    'options' => $pages_options,
				    ],
				    'mybookmark_way'        => [
					    'name'    => 'mybookmark_way',
					    'label'   => esc_html__( 'My Bookmark Page Method', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Shortcode method is old, customizer way is new and more easy. We recommend to use customizer and remove the shortcodes from that page related with this plugin. If the shortcode still exists in the my bookmarks page and  customizer method enabled still it will work.', 'cbxwpbookmark' ) . ' ' . $mybookmark_customizer_url_html,
					    'type'    => 'select',
					    'default' => 'shortcode',
					    'options' => [
						    'customizer' => esc_html__( 'Customizer(Recommended)', 'cbxwpbookmark' ),
						    'shortcode'  => esc_html__( 'Shortcode', 'cbxwpbookmark' ),
					    ],
				    ],
				    'pop_z_index'           => [
					    'name'              => 'pop_z_index',
					    'label'             => esc_html__( 'Bookmark Popup Z-Inxdex', 'cbxwpbookmark' ),
					    'desc'              => esc_html__( 'Sometimes bookmark popup doesn\'t show properly or may not compatible with theme. Increasing the z-index value will help.', 'cbxwpbookmark' ),
					    'type'              => 'text',
					    'default'           => 1,
					    'sanitize_callback' => 'absint'
				    ],
				    'guest_login_form'      => [
					    'name'    => 'guest_login_form',
					    'label'   => esc_html__( 'Guest User Login Form', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Default guest user is shown wordpress core login form. Pro addon helps to integrate 3rd party plugins like woocommerce, restrict content pro etc.', 'cbxwpbookmark' ),
					    'type'    => 'select',
					    'default' => 'wordpress',
					    'options' => $gust_login_forms
				    ],
				    'guest_show_register'   => [
					    'name'    => 'guest_show_register',
					    'label'   => esc_html__( 'Show Register link to guest', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Show register link to guest, depends on if registration is enabled in wordpress core', 'cbxwpbookmark' ),
					    'type'    => 'radio',
					    'default' => 1,
					    'options' => [
						    1 => esc_html__( 'Yes', 'cbxwpbookmark' ),
						    0 => esc_html__( 'No', 'cbxwpbookmark' ),
					    ],
				    ],
				    'hide_for_guest'   => [
					    'name'    => 'hide_for_guest',
					    'label'   => esc_html__( 'Hide Bookmark for Guest', 'cbxwpbookmark' ),
					    'desc'    => esc_html__( 'Totally hide bookmark button for guest user, default it\'s shown but ', 'cbxwpbookmark' ),
					    'type'    => 'radio',
					    'default' => 0,
					    'options' => [
						    1 => esc_html__( 'Yes', 'cbxwpbookmark' ),
						    0 => esc_html__( 'No', 'cbxwpbookmark' ),
					    ],
				    ],

			    ],
			    'cbxwpbookmark_tools'  => [
				    'tools_heading'        => [
					    'name'    => 'tools_heading',
					    'label'   => esc_html__( 'Tools Settings', 'cbxwpbookmark' ),
					    'type'    => 'heading',
					    'default' => '',
				    ],
				    'delete_global_config' => [
					    'name'    => 'delete_global_config',
					    'label'   => esc_html__( 'On Uninstall delete plugin data', 'cbxwpbookmark' ),
					    'desc'    => '<p>' . __( 'Delete Global Config data and custom table created by this plugin on uninstall.', 'cbxwpbookmark' ) . ' ' . esc_html__( 'Details table information is here', 'cbxwpbookmark' ) . '</p>' . '<p>' . __( '<strong>Please note that this process can not be undone and it is recommended to keep full database backup before doing this.</strong>', 'cbxwpbookmark' ) . '</p>',
					    'type'    => 'radio',
					    'options' => [
						    'yes' => esc_html__( 'Yes', 'cbxwpbookmark' ),
						    'no'  => esc_html__( 'No', 'cbxwpbookmark' ),
					    ],
					    'default' => 'no',
				    ],
				    'reset_data'           => [
					    'name'    => 'reset_data',
					    'label'   => esc_html__( 'Reset all data', 'cbxwpbookmark' ),
					    'desc'    => $table_html.'<p>'.esc_html__('Reset option values and all tables created by this plugin', 'cbxwpbookmark').'<a data-busy="0" class="button secondary ml-20" id="reset_data_trigger"  href="#">'.esc_html__('Reset Data', 'cbxwpbookmark').'</a></p>',
					    'type'    => 'html',
					    'default' => 'off',
				    ],

			    ],
		    ];

	    $settings_fields = []; //final setting array that will be passed to different filters

	    $sections = self::cbxwpbookmark_setting_sections();

	    foreach ( $sections as $section ) {
		    if ( ! isset( $settings_builtin_fields[ $section['id'] ] ) ) {
			    $settings_builtin_fields[ $section['id'] ] = [];
		    }
	    }

	    foreach ( $sections as $section ) {

		    $settings_fields[ $section['id'] ] = apply_filters( 'cbxwpbookmark_global_' . $section['id'] . '_fields', $settings_builtin_fields[ $section['id'] ] );
	    }

	    $settings_fields = apply_filters( 'cbxwpbookmark_global_fields', $settings_fields ); //final filter if need

	    return $settings_fields;
    }//end method cbxwpbookmark_setting_fields

	/**
	 * Plugin reset html table
	 *
	 * @return string
	 * @since 1.7.14
	 *
	 */
    public static function setting_reset_html_table(){
	    $option_values = CBXWPBookmarkHelper::getAllOptionNames();
	    $table_names   = CBXWPBookmarkHelper::getAllDBTablesList();

	    $table_html = '<div id="cbxwpbookmark_resetinfo"';
	    $table_html .= '<p style="margin-bottom: 15px;" id="cbxwpbookmark_plg_gfig_info"><strong>'.esc_html__('Following option values created by this plugin(including addon) from WordPress core option table', 'cbxwpbookmark').'</strong></p>';

	    $table_html .= '<table class="widefat widethin cbxwpbookmark_table_data">
	<thead>
	<tr>
		<th class="row-title">'.esc_attr__('Option Name', 'cbxwpbookmark').'</th>
		<th>'.esc_attr__('Option ID', 'cbxwpbookmark').'</th>		
	</tr>
	</thead>';

	    $table_html .= '<tbody>';

	    $i = 0;
	    foreach ($option_values as $key => $value) {
		    $alternate_class = ($i % 2 == 0) ? 'alternate' : '';
		    $i++;
		    $table_html .= '<tr class="'.esc_attr($alternate_class).'">
									<td class="row-title"><input checked class="magic-checkbox reset_options" type="checkbox" name="reset_options['.$value['option_name'].']" id="reset_options_'.esc_attr($value['option_name']).'" value="'.$value['option_name'].'" />
  <label for="reset_options_'.esc_attr($value['option_name']).'">'.esc_attr($value['option_name']).'</td>
									<td>'.esc_attr($value['option_id']).'</td>									
								</tr>';
	    }

	    $table_html .= '</tbody>';
	    $table_html .= '<tfoot>
	<tr>
		<th class="row-title">'.esc_attr__('Option Name', 'cbxwpbookmark').'</th>
		<th>'.esc_attr__('Option ID', 'cbxwpbookmark').'</th>				
	</tr>
	</tfoot>
</table>';


	    if (sizeof($table_names) > 0):
		    $table_html .= '<p style="margin-bottom: 15px;" id="cbxscratingreview_info"><strong>'.esc_html__('Following database tables will be reset/deleted and then re-created.', 'cbxwpbookmark').'</strong></p>';

		    $table_html .= '<table class="widefat widethin cbxwpbookmark_table_data">
        <thead>
        <tr>
            <th class="row-title">'.esc_attr__('Table Name', 'cbxwpbookmark').'</th>
            <th>'.esc_attr__('Table Name in DB', 'cbxwpbookmark').'</th>		
        </tr>
        </thead>';

		    $table_html .= '<tbody>';


		    $i = 0;
		    foreach ($table_names as $key => $value) {
			    $alternate_class = ($i % 2 == 0) ? 'alternate' : '';
			    $i++;
			    $table_html .= '<tr class="'.esc_attr($alternate_class).'">
                                        <td class="row-title"><input checked class="magic-checkbox reset_tables" type="checkbox" name="reset_tables['.esc_attr($key).']" id="reset_tables_'.esc_attr($key).'" value="'.$value.'" />
  <label for="reset_tables_'.esc_attr($key).'">'.esc_attr($key).'</label></td>
                                        <td>'.esc_attr($value).'</td>									
                                    </tr>';
		    }

		    $table_html .= '</tbody>';
		    $table_html .= '<tfoot>
        <tr>
            <th class="row-title">'.esc_attr__('Table Name', 'cbxwpbookmark').'</th>
            <th>'.esc_attr__('Table Name in DB', 'cbxwpbookmark').'</th>		
        </tr>
        </tfoot>
    </table>';
		    $table_html .= '</div>';
	    endif;

	    return $table_html;
    }//end method setting_reset_html_table

}//end CBXWPBookmarkHelper