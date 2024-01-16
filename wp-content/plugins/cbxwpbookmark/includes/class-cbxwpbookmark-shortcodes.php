<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The customizer specific functionality of the plugin.
 *
 * @link       codeboxr.com
 * @since      1.0.0
 *
 * @package    cbxwpbookmark
 * @subpackage cbxwpbookmark/includes
 */


/**
 * This class handles all shortcodes
 *
 * Class CBXWPBookmark_Shortcodes
 */
class CBXWPBookmark_Shortcodes {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	private $settings_api;

	/**
	 * Constructor.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$this->version = current_time( 'timestamp' ); //for development time only
		}

		$this->settings_api = new CBXWPBookmark_Settings_API();

		global $wp_version;

		add_action( 'init', [ $this, 'init_shortcodes' ] );
	}//end of construct

	/**
	 * Init all shortcodes
	 */
	public function init_shortcodes() {
		//bookmark button using shortcode
		add_shortcode( 'cbxwpbookmarkbtn', [ $this, 'button_shortcode' ] );     //bookmark button(old)


		//show bookmark list using shortcode
		add_shortcode( 'cbxwpbookmark', [ $this, 'mybookmark_shortcode' ] );    //my bookmarks(old)


		//show bookmark categories using shortcode
		add_shortcode( 'cbxwpbookmark-mycat', [ $this, 'category_shortcode' ] );//bookmark category(old but can be use as new)

		//show most bookmarked posts using shortcode
		add_shortcode( 'cbxwpbookmark-most', [ $this, 'most_shortcode' ] );     //bookmarked post
	}//end init_shortcodes

	/**
	 * Render bookmark button - shortcode callback
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function button_shortcode( $attr ) {
		// Checking Available Parameter
		global $post;

		$attr = shortcode_atts(
			[
				'object_id'        => intval( $post->ID ),
				'object_type'      => $post->post_type,
				'show_count'       => 1,
				'extra_wrap_class' => '',
				'skip_ids'         => '',
				'skip_roles'       => '' //example 'administrator, editor, author, contributor, subscriber'
			], $attr, 'cbxwpbookmarkbtn' );

		extract( $attr );


		return show_cbxbookmark_btn( $object_id, $object_type, $show_count, $extra_wrap_class, $skip_ids, $skip_roles );
	}//end button_shortcode

	/**
	 * Bookmarked Posts shortcode callback
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function mybookmark_shortcode( $attr ) {
		// Checking Available Parameter
		global $wpdb;
		$cbxwpbookmrak_table         = $wpdb->prefix . 'cbxwpbookmark';
		$cbxwpbookmak_category_table = $wpdb->prefix . 'cbxwpbookmarkcat';


		$setting       = $this->settings_api;
		$bookmark_mode = $setting->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );

		$current_user_id = get_current_user_id();

		$attr = shortcode_atts(
			[
				'title'          => esc_html__( 'All Bookmarks', 'cbxwpbookmark' ),
				'order'          => 'DESC',
				'orderby'        => 'id', //id, object_id, object_type, title
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
				'userid'         => $current_user_id,
				'offset'         => 0
			], $attr, 'cbxwpbookmark' );

		$attr['title'] = $title = sanitize_text_field( $attr['title'] );


		//if the url has cat id (cbxbmcatid get param) thenm use it or try it from shortcode
		$attr['catid'] = ( isset( $_GET['cbxbmcatid'] ) && $_GET['cbxbmcatid'] != null ) ? sanitize_text_field( $_GET['cbxbmcatid'] ) : $attr['catid'];

		if ( $attr['catid'] == 0 ) {
			$attr['catid'] = '';
		}//compatibility with previous shortcode default values
		$attr['catid'] = array_filter( explode( ',', $attr['catid'] ) );


		$userid_temp = $attr['userid'];

		//let's find out if the userid is email or username
		if ( is_email( $userid_temp ) ) {
			//possible email
			$user_temp = get_user_by( 'email', $userid_temp );

			if ( $user_temp !== false ) {
				$userid_temp = absint( $user_temp->ID );

				if ( $userid_temp > 0 ) {
					$attr['userid'] = $userid_temp;
				}
			} else {
				//email but user not found so reset it to guest
				$attr['userid'] = 0;
			}

		} elseif ( ! is_numeric( $userid_temp ) ) {
			if ( ( $user_temp = get_user_by( 'login', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} elseif ( ( $user_temp = get_user_by( 'slug', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} else {
				$attr['userid'] = 0;
			}
		}


		//get userid from url linked from other page
		//if ( isset( $_GET['userid'] ) && absint( $_GET['userid'] ) > 0 ) {

		if ( isset( $_GET['userid'] ) ) {
			$userid_temp = $_GET['userid'];

			if ( is_numeric( $userid_temp ) ) {
				//if user id is used
				$attr['userid'] = absint( $userid_temp );
			} elseif ( ( $user_temp = get_user_by( 'login', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} elseif ( ( $user_temp = get_user_by( 'slug', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} else {
				$attr['userid'] = 0;
			}


		}

		$attr['userid'] = absint( $attr['userid'] );

		//determine if we allow user to delete
		$allow_delete_all_html  = '';
		$attr['allowdeleteall'] = $allow_delete_all = absint( $attr['allowdeleteall'] );
		if ( $allow_delete_all && is_user_logged_in() && $attr['userid'] == $current_user_id ) {
			$allow_delete_all      = 1;
			$allow_delete_all_html = '<a data-list="1" data-busy="0" class="cbxwpbookmark_deleteall cbxwpbookmark_deleteall_list" href="#" class="">' . esc_html__( 'Delete All', 'cbxwpbookmark' ) . '</a>';
		}


		//if ( $attr['userid'] == '' || $attr['userid'] == 0 ) {
		/*if ( $attr['userid'] == 0 ) {
			$attr['userid'] = $current_user_id;
		}*/


		$attr['type'] = array_filter( explode( ',', $attr['type'] ) );

		extract( $attr );

		$limit = intval( $limit );
		if ( $limit == 0 ) {
			$limit = 10;
		}


		$show_loadmore_html = '';
		//$loadmore_busy_icon = '';

		$wpbm_ajax_icon = CBXWPBOOKMARK_ROOT_URL . 'assets/img/busy.gif';

		$privacy = 2; //all
		if ( $userid == 0 || ( $userid != get_current_user_id() ) ) {
			$privacy     = 1;
			$allowdelete = 0;

			$attr['privacy']     = $privacy;
			$attr['allowdelete'] = $allowdelete;
		}

		$total_sql            = '';
		$cat_sql              = '';
		$category_privacy_sql = '';
		$type_sql             = '';


		//if ($catid != 0 && $bookmark_mode != 'no_cat')
		if ( is_array( $catid ) && sizeof( $catid ) > 0 && ( $bookmark_mode != 'no_cat' ) ) {
			$cats_ids_str = implode( ',', $catid );
			$cat_sql      .= " AND cat_id IN ($cats_ids_str) ";
		}

		//get cats
		if ( $bookmark_mode == 'user_cat' ) {
			//same user seeing
			if ( $privacy != 2 ) {
				$cats = $wpdb->get_results( $wpdb->prepare( "SELECT *  FROM  $cbxwpbookmak_category_table WHERE privacy = %d", intval( $privacy ) ), ARRAY_A );
			} else {
				$cats = $wpdb->get_results( "SELECT *  FROM  $cbxwpbookmak_category_table WHERE 1", ARRAY_A );
			}

			$cats_ids = [];
			if ( is_array( $cats ) && sizeof( $cats ) > 0 ) {
				foreach ( $cats as $cat ) {
					$cats_ids[] = intval( $cat['id'] );
				}

				$cats_ids_str         = implode( ', ', $cats_ids );
				$category_privacy_sql .= " AND cat_id IN ($cats_ids_str) ";
			}
		}


		if ( sizeof( $type ) == 0 ) {
			$param = [ $userid ];
			//$total_sql .= "SELECT COUNT(*) FROM (select count(*) as totalobject FROM $cbxwpbookmrak_table  WHERE user_id = %d $cat_sql $category_privacy_sql group by object_id  ORDER BY $orderby $order) AS TotalData";
			$total_sql .= "SELECT COUNT(*) FROM (select count(*) as totalobject FROM $cbxwpbookmrak_table  WHERE user_id = %d $cat_sql $category_privacy_sql group by object_id) AS TotalData";
		} else {
			$type_sql .= " AND object_type IN ('" . implode( "','", $type ) . "') ";


			$param = [ $userid ];
			//$total_sql .= "SELECT COUNT(*) FROM (select count(*) as totalobject FROM $cbxwpbookmrak_table  WHERE user_id = %d $cat_sql $type_sql $category_privacy_sql group by object_id   ORDER BY $orderby $order) AS TotalData";
			$total_sql .= "SELECT COUNT(*) FROM (select count(*) as totalobject FROM $cbxwpbookmrak_table  WHERE user_id = %d $cat_sql $type_sql $category_privacy_sql group by object_id) AS TotalData";

		}

		$total_count = intval( $wpdb->get_var( $wpdb->prepare( $total_sql, $param ) ) );

		$total_page = ceil( $total_count / $limit );

		$extra_css_class = '';
		if ( $attr['loadmore'] == 1 && $total_page > 1 ) {
			$extra_css_class    = 'cbxwpbookmark-mylist-sc-more';
			$offset             += $limit;
			$loadmore_busy_icon = '<span data-busy="0" class="cbxwpbm_ajax_icon">' . esc_html__( 'Loading ...', 'cbxwpbookmark' ) . '<img src = "' . $wpbm_ajax_icon . '"/></span>';
			$show_loadmore_html = '<p class="cbxbookmark-more-wrap"><a href="#" class="cbxbookmark-more" data-cattitle="' . $cattitle . '" data-order="' . $order . '" data-orderby="' . $orderby . '"  data-userid="' . $userid . '" data-limit="' . $limit . '" data-offset="' . $offset . '" data-catid="' . implode( ',', $catid ) . '" data-type="' . implode( ',',
					$type ) . '" data-totalpage="' . $total_page . '" data-currpage="1" data-allowdelete="' . intval( $allowdelete ) . '">' . esc_html__( 'Load More', 'cbxwpbookmark' ) . '</a>' . $loadmore_busy_icon . '</p>';
		}


		//if only bookmark mode is user or global cat
		if ( intval( $cattitle ) && $bookmark_mode != 'no_cat' ) {
			if ( sizeof( $catid ) == 0 ) {
				//$category_title = '<h3 class="cbxwpbookmark-title cbxwpbookmark-postlist">' . esc_html__( 'All Bookmarks', 'cbxwpbookmark' ) . '</h3>';
			} else {
				if ( sizeof( $catid ) == 1 ) {
					$cat_info = CBXWPBookmarkHelper::getBookmarkCategoryById( reset( $catid ) );

					if ( is_array( $cat_info ) && sizeof( $cat_info ) > 0 ) {
						$catcount_html = '';
						if ( $catcount ) {
							$cat_bookmark_count = CBXWPBookmarkHelper::getTotalBookmarkByCategory( reset( $catid ) );
							$catcount_html      = '<i>(' . number_format_i18n( $cat_bookmark_count ) . ')</i>';
						}

						$title = wp_unslash($cat_info['cat_name']) . $catcount_html;
					}
				}

			}
		}


		$share_url_html = '';

		if ( intval( $attr['showshareurl'] ) == 1 ) {
			$share_url      = CBXWPBookmarkHelper::myBookmarksShareUrl( $attr );
			$share_url_html = '<a class="cbxwpbookmark_share" href="' . $share_url . '">' . esc_html__( 'Share', 'cbxwpbookmark' ) . '</a>';
		}


		$title_html = $title . $allow_delete_all_html . $share_url_html;


		if ( $title_html != '' ) {
			$title = '<h3 class="cbxwpbookmark-title cbxwpbookmark-title-postlist">' . $title_html . '</h3>';
		}

		return '<div class="cbxwpbookmark-mylist-wrap">' . $title . '<ul class="cbxwpbookmark-list-generic cbxwpbookmark-mylist cbxwpbookmark-mylist-sc ' . $extra_css_class . '" >' . cbxbookmark_post_html( $attr ) . '</ul>' . $show_loadmore_html . '</div>';
	}//end mybookmark_shortcode

	/**
	 * Shows any user's bookmarked categories using shortcode
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function category_shortcode( $attr ) {
		$setting       = new CBXWPBookmark_Settings_API();
		$bookmark_mode = $setting->get_option( 'bookmark_mode', 'cbxwpbookmark_basics', 'user_cat' );

		$current_user_id = get_current_user_id();

		$attr = shortcode_atts(
			[
				'title'          => esc_html__( 'Bookmark Categories', 'cbxwpbookmark' ),//if empty title will not be shown
				'order'          => 'ASC',                                               //DESC, ASC
				'orderby'        => 'cat_name',                                          //other possible values  id, cat_name, privacy
				'privacy'        => 2,                                                   //1 = public 0 = private  2= ignore
				'display'        => 0,                                                   //0 = list  1= dropdown,
				'show_count'     => 0,
				'allowedit'      => 0,
				'show_bookmarks' => 0,//show bookmark as sublist on click on category
				'userid'         => $current_user_id,
				'base_url'       => cbxwpbookmarks_mybookmark_page_url()
			], $attr, 'cbxwpbookmark-mycat'
		);

		$userid_temp = $attr['userid'];

		//let's find out if the userid is email or username
		if ( is_email( $userid_temp ) ) {
			$user_temp = get_user_by( 'email', $userid_temp );
			if ( $user_temp !== false ) {
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = $userid_temp;
				}
			} else {
				//email but user not found so reset it to guest
				$attr['userid'] = 0;
			}

		} elseif ( ! is_numeric( $userid_temp ) ) {
			if ( ( $user_temp = get_user_by( 'login', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} elseif ( ( $user_temp = get_user_by( 'slug', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} else {
				$attr['userid'] = 0;
			}
		}

		//if the shortcode page linked with user id
		//if ( isset( $_GET['userid'] ) && absint( $_GET['userid'] ) > 0 ) {
		//$attr['userid'] = absint( $_GET['userid'] );
		//}

		//if ( $attr['userid'] == '' || $attr['userid'] == 0 ) {
		//$attr['userid'] = $current_user_id;
		//}

		if ( isset( $_GET['userid'] ) ) {
			$userid_temp = $_GET['userid'];

			if ( is_numeric( $userid_temp ) ) {
				//if user id is used
				$attr['userid'] = absint( $userid_temp );
			} elseif ( ( $user_temp = get_user_by( 'login', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} elseif ( ( $user_temp = get_user_by( 'slug', $userid_temp ) ) !== false ) {
				//user_login
				$userid_temp = absint( $user_temp->ID );
				if ( $userid_temp > 0 ) {
					$attr['userid'] = absint( $userid_temp );
				}
			} else {
				$attr['userid'] = 0;
			}


		}

		$attr['userid'] = absint( $attr['userid'] );

		$output = '';

		//if other than no_cat mode we will have category
		if ( $bookmark_mode != 'no_cat' ) {
			$output .= '<div class="cbxbookmark-category-list-wrap">';
			if ( $attr['title'] != '' ) {
				$output .= '<h3 class="cbxwpbookmark-title cbxwpbookmark-title-mycat">' . $attr['title'] . '</h3>';
			}

			$create_category_html = '';

			if ( $bookmark_mode == 'user_cat' && is_user_logged_in() && ( absint( $attr['userid'] ) == $current_user_id ) ) {
				$create_category_html = CBXWPBookmarkHelper::create_category_html( $attr );
			}

			$output .= ( intval( $attr['display'] ) == 0 ) ? $create_category_html : '';
			$output .= ( intval( $attr['display'] ) == 0 ) ? '<ul class="cbxwpbookmark-list-generic cbxbookmark-category-list cbxbookmark-category-list-' . $bookmark_mode . ' cbxbookmark-category-list-sc">' : '';
			$output .= cbxbookmark_mycat_html( $attr );
			$output .= ( intval( $attr['display'] ) == 0 ) ? '</ul>' : '';
			$output .= '</div>';
		} else {
			//this message is better to hide
			$output .= __( '<strong>Sorry, This widget is not compatible as per setting. This widget can be used only if bookmark mode is "User owns category" or "Global Category"</strong>', 'cbxwpbookmark' );
		}


		return $output;
	}//end category_shortcode

	/**
	 * Most bookmarked post shortcode
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function most_shortcode( $attr ) {
		$attr = shortcode_atts(
			[
				'title'      => esc_html__( 'Most Bookmarked Posts', 'cbxwpbookmark' ),    //if empty title will not be shown
				'order'      => 'DESC',
				'orderby'    => 'object_count',    //id, object_id, object_type, object_count, title
				'limit'      => 10,
				'type'       => '',    //db col name object_type,  post types eg, post, page, any custom post type, for multiple comma separated
				'daytime'    => 0,     // 0 means all time,  any numeric values as days
				'show_count' => 1,
				'show_thumb' => 1,
				'ul_class'   => '',
				'li_class'   => ''
			], $attr, 'cbxwpbookmark-most' );

		$style_attr = [
			'ul_class' => esc_attr( $attr['ul_class'] ),
			'li_class' => esc_attr( $attr['li_class'] )
		];

		$attr['type'] = array_filter( explode( ',', $attr['type'] ) );

		return '<div class="cbxwpbookmark-mostlist-wrap">' . cbxbookmark_most_html( $attr, $style_attr ) . '</div>';
	}//end most_shortcode
}//end class CBXWPBookmark_Shortcodes