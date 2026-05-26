<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Hook the post rendering to the block
if ( function_exists( 'register_block_type' ) ) :
	register_block_type(
		'ew-block/ew-page',
		array(
			'attributes'      => array(
				'title'        => array(
					'type'    => 'string',
					'default' 	=> 'Pages', // No translation here
				),
				'post_type'    => array(
					'type'    => 'string',
					'default' => 'page',
				),
				'depth'        => array(
					'type'    => 'number',
					'default' => 0,
				),
				'number'       => array(
					'type'    => 'number',
					'default' => 10,
				),
				'offset'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'child_of'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'include'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'exclude'      => array( // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Required wp_list_pages() parameter, not a WP_Query post__not_in clause.
					'type'    => 'string',
					'default' => '',
				),
				'exclude_tree' => array(
					'type'    => 'string',
					'default' => '',
				),

				'meta_key'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Required wp_list_pages() parameter.
					'type'    => 'string',
					'default' => '',
				),
				'meta_value'   => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Required wp_list_pages() parameter.
					'type'    => 'string',
					'default' => '',
				),
				'authors'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'link_before'  => array(
					'type'    => 'string',
					'default' => '',
				),
				'link_after'   => array(
					'type'    => 'string',
					'default' => '',
				),
				'show_date'    => array(
					'type'    => 'string',
					'default' => '',
				),
				'hierarchical' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'sort_column'  => array(
					'type'    => 'string',
					'default' => 'post_title',
				),
				'sort_order'   => array(
					'type'    => 'string',
					'default' => 'ASC',
				),
				'date_format'  => array(
					'type'    => 'string',
					'default' => '',
				),
				'is_block'     => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
			'render_callback' => 'ew_page_render_shortcode',
		)
	);
endif;

if ( ! function_exists( 'ew_page_render_shortcode' ) ) :
	add_shortcode( 'ew-page', 'ew_page_render_shortcode' );

	function ew_page_render_shortcode( $atts ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- ew_ is this plugin's abbreviated prefix; wrapped in function_exists() guard.

		$atts = shortcode_atts(
			array(
				'title'         => 'Pages',
				'post_type'     => 'page',
				'depth'         => 0,
				'number'        => 10,
				'offset'        => 0,
				'child_of'      => '',
				'include'       => '',
				'exclude'       => '', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Required wp_list_pages() parameter.
				'exclude_tree'  => '',
				'meta_key'      => '', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Required wp_list_pages() parameter.
				'meta_value'    => '', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Required wp_list_pages() parameter.
				'authors'       => '',
				'link_before'   => '',
				'link_after'    => '',
				'show_date'     => '',
				'hierarchical'  => true,
				'sort_column'   => 'post_title',
				'sort_order'    => 'ASC',
				'date_format'   => '',
				'is_block'      => true,
			),
			$atts,
			'ew-page'
		);

		$instance = array();

		$instance['title'] = ( 'Pages' === $atts['title'] )
			? esc_html__( 'Pages', 'essential-widgets' )
			: sanitize_text_field( $atts['title'] );

		$instance['post_type'] = sanitize_key( $atts['post_type'] );

		$instance['depth']   = absint( $atts['depth'] );
		$instance['number']  = absint( $atts['number'] );
		$instance['offset']  = absint( $atts['offset'] );

		$instance['child_of']     = absint( $atts['child_of'] );
		$instance['include']      = sanitize_text_field( $atts['include'] );
		$instance['exclude']      = sanitize_text_field( $atts['exclude'] ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Required wp_list_pages() parameter.
		$instance['exclude_tree'] = sanitize_text_field( $atts['exclude_tree'] );

		$instance['meta_key']   = sanitize_key( $atts['meta_key'] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Required wp_list_pages() parameter.
		$instance['meta_value'] = sanitize_text_field( $atts['meta_value'] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Required wp_list_pages() parameter.

		$instance['authors'] = sanitize_text_field( $atts['authors'] );

		$instance['link_before'] = wp_kses_post( $atts['link_before'] );
		$instance['link_after']  = wp_kses_post( $atts['link_after'] );

		$instance['show_date']   = sanitize_text_field( $atts['show_date'] );
		$instance['date_format'] = sanitize_text_field( $atts['date_format'] );

		$instance['hierarchical'] = (bool) $atts['hierarchical'];
		$instance['is_block']     = (bool) $atts['is_block'];

		// Whitelist sorting values
		$allowed_order = array( 'ASC', 'DESC' );
		$instance['sort_order'] = in_array( strtoupper( $atts['sort_order'] ), $allowed_order, true )
			? strtoupper( $atts['sort_order'] )
			: 'ASC';

		$allowed_columns = array( 'post_title', 'menu_order', 'post_date', 'post_modified', 'ID' );
		$instance['sort_column'] = in_array( $atts['sort_column'], $allowed_columns, true )
			? $atts['sort_column']
			: 'post_title';

		$ew_page = new EW_Pages();

		return $ew_page->shortcode( $instance );
	}
endif;

if ( ! function_exists( 'ew_page_list' ) ) :

	/**
	 * Get nav menus for select option via custom REST API!
	 *
	 * @return array|null Array of nav menus object with label and value pair, * or null if none.
	 */
	function ew_page_list() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- ew_ is this plugin's abbreviated prefix; wrapped in function_exists() guard.
		$post_types = get_post_types(
			array(
				'public'       => true,
				'hierarchical' => true,
			),
			'objects'
		);

		$page_list = array();

		foreach ( $post_types as $page ) {
			$object        = new stdClass();
			$object->label = sanitize_text_field( $page->labels->singular_name );
			$object->value = sanitize_key( $page->name );
			$page_list[]   = $object;
		}

		return $page_list;
	}

	add_action(
		'rest_api_init',
		function () {
			register_rest_route(
				'ew-rest/v1',
				'ew-page-list',
				array(
					'methods'             => 'GET',
					'callback'            => 'ew_page_list',
					'permission_callback' => '__return_true', // for public use
				)
			);
		}
	);
endif;
