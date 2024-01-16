<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * CBX Bookmarks - Bookmark Category Block Widget
 */
class CBXWPBookmarkCategory_Block {
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


	/**
	 * Constructor.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		global $wp_version;


		$this->init_mycat_block();

	}//end of construct

	/**
	 * Register my bookmark category block
	 */
	public function init_mycat_block() {
		$order_options = [];

		$order_options[] = [
			'label' => esc_html__( 'Descending Order', 'cbxwpbookmark' ),
			'value' => 'DESC',
		];

		$order_options[] = [
			'label' => esc_html__( 'Ascending Order', 'cbxwpbookmark' ),
			'value' => 'ASC',
		];

		$orderby_options   = [];
		$orderby_options[] = [
			'label' => esc_html__( 'Category ID', 'cbxwpbookmark' ),
			'value' => 'id',
		];
		$orderby_options[] = [
			'label' => esc_html__( 'Category Name', 'cbxwpbookmark' ),
			'value' => 'cat_name',
		];
		$orderby_options[] = [
			'label' => esc_html__( 'Privacy', 'cbxwpbookmark' ),
			'value' => 'privacy',
		];

		$display_options   = [];
		$display_options[] = [
			'label' => esc_html__( 'List', 'cbxwpbookmark' ),
			'value' => 0,
		];
		$display_options[] = [
			'label' => esc_html__( 'Dropdown', 'cbxwpbookmark' ),
			'value' => 1,
		];

		$privacy_options   = [];
		$privacy_options[] = [
			'label' => esc_html__( 'All', 'cbxwpbookmark' ),
			'value' => 2,
		];
		$privacy_options[] = [
			'label' => esc_html__( 'Public only', 'cbxwpbookmark' ),
			'value' => 1,
		];
		$privacy_options[] = [
			'label' => esc_html__( 'Private only', 'cbxwpbookmark' ),
			'value' => 0,
		];


		wp_register_style( 'cbxwpbookmark-block', CBXWPBOOKMARK_ROOT_URL . 'assets/css/cbxwpbookmark-block.css', [], filemtime( CBXWPBOOKMARK_ROOT_PATH . 'assets/css/cbxwpbookmark-block.css' ) );

        wp_register_script( 'cbxwpbookmark-mycat-block',
			CBXWPBOOKMARK_ROOT_URL . 'assets/js/blocks/cbxwpbookmark-mycat-block.js',
			[
				'wp-blocks',
				'wp-element',
				'wp-components',
				'wp-editor',
				//'jquery',
				//'codeboxrflexiblecountdown-public'
			],
			filemtime( CBXWPBOOKMARK_ROOT_PATH . 'assets/js/blocks/cbxwpbookmark-mycat-block.js' ) );


		$js_vars = apply_filters( 'cbxwpbookmark_mycat_block_js_vars',
			[
				//'cbxbookmark_lang'        => get_user_locale(),
				'block_title'      => esc_html__( 'CBX Bookmark Categories', 'cbxwpbookmark' ),
				'block_category'   => 'cbxwpbookmark',
				'block_icon'       => 'universal-access-alt',
				'general_settings' => [
					'heading'         => esc_html__( 'Block Settings', 'cbxwpbookmark' ),
					'title'           => esc_html__( 'Title', 'cbxwpbookmark' ),
					'title_desc'      => esc_html__( 'Leave empty to hide', 'cbxwpbookmark' ),
					'order'           => esc_html__( 'Order', 'cbxwpbookmark' ),
					'order_options'   => $order_options,
					'orderby'         => esc_html__( 'Order By', 'cbxwpbookmark' ),
					'orderby_options' => $orderby_options,
					'display'         => esc_html__( 'Display Format', 'cbxwpbookmark' ),
					'display_options' => $display_options,
					'privacy'         => esc_html__( 'Privacy', 'cbxwpbookmark' ),
					'privacy_options' => $privacy_options,
					'show_count'      => esc_html__( 'Show Count', 'cbxwpbookmark' ),
					'allowedit'       => esc_html__( 'Allow Edit', 'cbxwpbookmark' ),
					'show_bookmarks'  => esc_html__( 'Show Bookmarks', 'cbxwpbookmark' ), //show bookmark as sublist on click on category
					'base_url'        => esc_html__( 'My Bookmark Page Url(Base Url)', 'cbxwpbookmark' )
				],
			] );

		wp_localize_script( 'cbxwpbookmark-mycat-block', 'cbxwpbookmark_mycat_block', $js_vars );

		register_block_type( 'codeboxr/cbxwpbookmark-mycat-block',
			[
				'editor_script'   => 'cbxwpbookmark-mycat-block',
				'editor_style'    => 'cbxwpbookmark-block',
				'attributes'      => apply_filters( 'cbxwpbookmark_mycat_block_attributes',
					[
						'title'          => [
							'type'    => 'string',
							'default' => esc_html__( 'Bookmark Categories', 'cbxwpbookmark' ),
						],
						'order'          => [
							'type'    => 'string',
							'default' => 'ASC',
						],
						'orderby'        => [
							'type'    => 'string',
							'default' => 'cat_name',
						],
						'display'        => [
							'type'    => 'integer',
							'default' => 0,
						],
						'privacy'        => [
							'type'    => 'integer',
							'default' => 2,
						],
						'show_count'     => [
							'type'    => 'boolean',
							'default' => false,
						],
						'allowedit'      => [
							'type'    => 'boolean',
							'default' => false,
						],
						'show_bookmarks' => [
							'type'    => 'boolean',
							'default' => false,
						],
						'base_url'       => [
							'type'    => 'string',
							'default' => cbxwpbookmarks_mybookmark_page_url(),
						],
					] ),
				'render_callback' => [ $this, 'mycat_block_render' ],
			] );
	}//end init_mycat_block

	/**
	 * Getenberg server side render for my bookmark category block
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function mycat_block_render( $attr ) {
		$arr = [];

		$arr['title']    = isset( $attr['title'] ) ? sanitize_text_field( $attr['title'] ) : '';
		$arr['base_url'] = isset( $attr['base_url'] ) ? sanitize_text_field( $attr['base_url'] ) : cbxwpbookmarks_mybookmark_page_url();
		$arr['order']    = isset( $attr['order'] ) ? sanitize_text_field( $attr['order'] ) : 'DESC';
		$arr['orderby']  = isset( $attr['orderby'] ) ? sanitize_text_field( $attr['orderby'] ) : 'cat_name';
		$arr['display']  = isset( $attr['display'] ) ? intval( $attr['display'] ) : 0;
		$arr['privacy']  = isset( $attr['privacy'] ) ? intval( $attr['privacy'] ) : 2;

		$arr['show_count'] = isset( $attr['show_count'] ) ? $attr['show_count'] : 'false';
		$arr['show_count'] = ( $arr['show_count'] == 'true' ) ? 1 : 0;

		$arr['allowedit'] = isset( $attr['allowedit'] ) ? $attr['allowedit'] : 'false';
		$arr['allowedit'] = ( $arr['allowedit'] == 'true' ) ? 1 : 0;

		$arr['show_bookmarks'] = isset( $attr['show_bookmarks'] ) ? $attr['show_bookmarks'] : 'false';
		$arr['show_bookmarks'] = ( $arr['show_bookmarks'] == 'true' ) ? 1 : 0;


		$attr_html = '';
		foreach ( $arr as $key => $value ) {
			$attr_html .= ' ' . $key . '="' . $value . '" ';
		}

		return do_shortcode( '[cbxwpbookmark-mycat ' . $attr_html . ']' );
	}//end mycat_block_render
}//end method CBXWPBookmarkCategory_Block