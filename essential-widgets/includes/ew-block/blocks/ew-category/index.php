<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Hook the post rendering to the block
if ( function_exists( 'register_block_type' ) ) :
	register_block_type(
		'ew-block/ew-category',
		array(
			'attributes'      => array(
				'title'              => array(
					'type'    => 'string',
					'default' 	=> 'Categories', // No translation here
				),
				'taxonomy'           => array(
					'type'    => 'string',
					'default' => 'category',
				),
				'style'              => array(
					'type'    => 'string',
					'default' => '',
				),
				'include'            => array(
					'type'    => 'string',
					'default' => '',
				),
				'exclude'            => array( // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Required wp_list_categories() parameter, not a WP_Query post__not_in clause.
					'type'    => 'string',
					'default' => '',
				),
				'exclude_tree'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'child_of'           => array(
					'type'    => 'string',
					'default' => '',
				),
				'current_category'   => array(
					'type'    => 'string',
					'default' => '',
				),
				'search'             => array(
					'type'    => 'string',
					'default' => '',
				),
				'hierarchical'       => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'hide_empty'         => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'order'              => array(
					'type'    => 'string',
					'default' => 'ASC',
				),
				'orderby'            => array(
					'type'    => 'string',
					'default' => 'name',
				),
				'depth'              => array(
					'type'    => 'number',
					'default' => 0,
				),
				'number'             => array(
					'type'    => 'number',
					'default' => 10,
				),
				'feed'               => array(
					'type'    => 'string',
					'default' => '',
				),
				'feed_type'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'feed_image'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'use_desc_for_title' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'show_count'         => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'is_block'           => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
			'render_callback' => 'ew_category_render_shortcode',
		)
	);
endif;

if ( ! function_exists( 'ew_category_render_shortcode' ) ) :
	add_shortcode( 'ew-category', 'ew_category_render_shortcode' );
	function ew_category_render_shortcode( $atts ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- ew_ is this plugin's abbreviated prefix; wrapped in function_exists() guard.
	    $instance = array();

	    // Title
	    $instance['title'] = isset( $atts['title'] ) && 'Categories' === $atts['title']
	        ? esc_html__( 'Categories', 'essential-widgets' )
	        : sanitize_text_field( $atts['title'] );

	    // Sanitize & validate inputs
	    $instance['taxonomy']           = sanitize_key( $atts['taxonomy'] ?? 'category' );
	    $instance['style']              = in_array( $atts['style'] ?? 'list', ['list','none'], true ) ? $atts['style'] : 'list';
	    $instance['include']            = preg_replace('/[^0-9,]/', '', $atts['include'] ?? '');
	    $instance['exclude']            = preg_replace('/[^0-9,]/', '', $atts['exclude'] ?? ''); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Required wp_list_categories() parameter.
	    $instance['exclude_tree']       = preg_replace('/[^0-9,]/', '', $atts['exclude_tree'] ?? '');
	    $instance['child_of']           = absint( $atts['child_of'] ?? 0 );
	    $instance['current_category']   = absint( $atts['current_category'] ?? 0 );
	    $instance['search']             = sanitize_text_field( $atts['search'] ?? '' );
	    $instance['hierarchical']       = isset( $atts['hierarchical'] ) ? (bool) $atts['hierarchical'] : true;
	    $instance['hide_empty']         = isset( $atts['hide_empty'] ) ? (bool) $atts['hide_empty'] : true;
	    $instance['order']              = in_array( $atts['order'] ?? 'ASC', ['ASC','DESC'], true ) ? $atts['order'] : 'ASC';
	    $instance['orderby']            = in_array( $atts['orderby'] ?? 'name', ['count','ID','name','slug','term_group'], true ) ? $atts['orderby'] : 'name';
	    $instance['depth']              = absint( $atts['depth'] ?? 0 );
	    $instance['number']             = absint( $atts['number'] ?? 10 );
	    $instance['feed']               = sanitize_text_field( $atts['feed'] ?? '' );
	    $feed_type 						= $atts['feed_type'] ?? '';
		$instance['feed_type'] 			= in_array( $feed_type, ['', 'atom','rdf','rss','rss2'], true ) ? $feed_type : '';
	    $instance['feed_image']         = esc_url_raw( $atts['feed_image'] ?? '' );
	    $instance['use_desc_for_title'] = isset( $atts['use_desc_for_title'] ) ? (bool) $atts['use_desc_for_title'] : false;
	    $instance['show_count']         = isset( $atts['show_count'] ) ? (bool) $atts['show_count'] : false;
	    $instance['is_block']           = isset( $atts['is_block'] ) ? (bool) $atts['is_block'] : true;

	    // Generate output via widget shortcode method
	    $ew_category = new EW_Categories();
	    return $ew_category->shortcode( $instance );
	}
endif;
