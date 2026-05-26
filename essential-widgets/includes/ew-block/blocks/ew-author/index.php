<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Hook the post rendering to the block
if ( function_exists( 'register_block_type' ) ) :
	register_block_type(
		'ew-block/ew-author',
		array(
			'attributes'      	=> array(
				'title' 		=> array(
					'type'    	=> 'string',
					'default' 	=> 'Authors', // No translation here
				),
				'order'         => array(
					'type'    	=> 'string',
					'default' 	=> 'ASC',
				),
				'orderby'       => array(
					'type'    	=> 'string',
					'default' 	=> 'display_name',
				),
				'number'        => array(
					'type'    	=> 'number',
					'default' 	=> 5,
				),
				'include'       => array(
					'type'    	=> 'string',
					'default' 	=> '',
				),
				'exclude'       => array( // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Required wp_list_authors() parameter, not a WP_Query post__not_in clause.
					'type'    	=> 'string',
					'default' 	=> '',
				),
				'optioncount'   => array(
					'type'    	=> 'boolean',
					'default' 	=> false,
				),
				'exclude_admin' => array(
					'type'    	=> 'boolean',
					'default' 	=> false,
				),
				'show_fullname' => array(
					'type'    	=> 'boolean',
					'default' 	=> false,
				),
				'hide_empty'    => array(
					'type'    	=> 'boolean',
					'default' 	=> true,
				),
				'style'         => array(
					'type'    	=> 'string',
					'default' 	=> 'list',
				),
				'html'          => array(
					'type'    	=> 'boolean',
					'default' 	=> true,
				),
				'feed'          => array(
					'type'    	=> 'string',
					'default' 	=> '',
				),
				'feed_type'     => array(
					'type'    	=> 'string',
					'default' 	=> '',
				),
				'feed_image'    => array(
					'type'    	=> 'string',
					'default' 	=> '',
				),
				'is_block'      => array(
					'type'    	=> 'boolean',
					'default' 	=> true,
				),
			),
			'render_callback' => 'ew_author_render_shortcode',
		)
	);
endif;

if ( ! function_exists( 'ew_author_render_shortcode' ) ) :
	add_shortcode( 'ew-author', 'ew_author_render_shortcode' );

	function ew_author_render_shortcode( $atts ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- ew_ is this plugin's abbreviated prefix; wrapped in function_exists() guard.

		$atts = shortcode_atts(
			array(
				'title'         => 'Authors',
				'order'         => 'ASC',
				'orderby'       => 'display_name',
				'number'        => 5,
				'include'       => '',
				'exclude'       => '', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Required wp_list_authors() parameter.
				'optioncount'   => false,
				'exclude_admin' => false,
				'show_fullname' => false,
				'hide_empty'    => true,
				'style'         => 'list',
				'html'          => true,
				'feed'          => '',
				'feed_type'     => '',
				'feed_image'    => '',
				'is_block'      => true,
			),
			$atts,
			'ew-author'
		);

		$instance = array();

		// Title
		$instance['title'] = ( 'Authors' === $atts['title'] )
			? esc_html__( 'Authors', 'essential-widgets' )
			: sanitize_text_field( $atts['title'] );

		// Whitelist order
		$allowed_order = array( 'ASC', 'DESC' );
		$instance['order'] = in_array( strtoupper( $atts['order'] ), $allowed_order, true )
			? strtoupper( $atts['order'] )
			: 'ASC';

		// Whitelist orderby
		$allowed_orderby = array(
			'display_name',
			'user_login',
			'user_nicename',
			'user_email',
			'ID',
			'post_count',
		);
		$instance['orderby'] = in_array( $atts['orderby'], $allowed_orderby, true )
			? $atts['orderby']
			: 'display_name';

		// Numbers
		$instance['number'] = absint( $atts['number'] );

		// Allow only IDs (numbers + commas)
		$instance['include'] = preg_replace( '/[^0-9,]/', '', sanitize_text_field( $atts['include'] ) );
		$instance['exclude'] = preg_replace( '/[^0-9,]/', '', sanitize_text_field( $atts['exclude'] ) ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Required wp_list_authors() parameter.

		// Booleans
		$instance['optioncount']   = (bool) $atts['optioncount'];
		$instance['exclude_admin'] = (bool) $atts['exclude_admin'];
		$instance['show_fullname'] = (bool) $atts['show_fullname'];
		$instance['hide_empty']    = (bool) $atts['hide_empty'];
		$instance['html']          = (bool) $atts['html'];
		$instance['is_block']      = (bool) $atts['is_block'];

		// Style whitelist
		$allowed_styles = array( 'list', 'dropdown' );
		$instance['style'] = in_array( $atts['style'], $allowed_styles, true )
			? $atts['style']
			: 'list';

		// Feed fields
		$instance['feed']       = sanitize_text_field( $atts['feed'] );
		$instance['feed_type']  = sanitize_text_field( $atts['feed_type'] );
		$instance['feed_image'] = sanitize_text_field( $atts['feed_image'] );

		$ew_author = new EW_Authors();

		return $ew_author->shortcode( $instance );
	}
endif;
