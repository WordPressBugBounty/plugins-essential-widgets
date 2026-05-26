<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Register block and shortcode
if ( function_exists( 'register_block_type' ) ) :
	register_block_type(
		'ew-block/ew-archive',
		array(
			'attributes'      => array(
				'title'           => array(
					'type'    => 'string',
					'default' => 'Archives',
				),
				'limit'           => array(
					'type'    => 'number',
					'default' => 10,
				),
				'type'            => array(
					'type'    => 'string',
					'default' => 'monthly',
				),
				'post_type'       => array(
					'type'    => 'string',
					'default' => 'post',
				),
				'order'           => array(
					'type'    => 'string',
					'default' => 'asc',
				),
				'format'          => array(
					'type'    => 'string',
					'default' => 'html',
				),
				'before'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'after'           => array(
					'type'    => 'string',
					'default' => '',
				),
				'show_post_count' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'is_block'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
			'render_callback' => 'ew_archive_render_shortcode',
		)
	);
endif;

// Shortcode callback
if ( ! function_exists( 'ew_archive_render_shortcode' ) ) :
	add_shortcode( 'ew-archive', 'ew_archive_render_shortcode' );
	function ew_archive_render_shortcode( $atts ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- ew_ is this plugin's abbreviated prefix; wrapped in function_exists() guard.
		// Sanitize all attributes
		$instance = array(
			'title'           => sanitize_text_field( $atts['title'] ?? '' ),
			'limit'           => intval( $atts['limit'] ?? 10 ),
			'type'            => sanitize_key( $atts['type'] ?? 'monthly' ),
			'post_type'       => sanitize_key( $atts['post_type'] ?? 'post' ),
			'order'           => sanitize_key( $atts['order'] ?? 'asc' ),
			'format'          => sanitize_key( $atts['format'] ?? 'html' ),
			'before'          => wp_kses_post( $atts['before'] ?? '' ),
			'after'           => wp_kses_post( $atts['after'] ?? '' ),
			'show_post_count' => ! empty( $atts['show_post_count'] ) ? 1 : 0,
			'is_block'        => ! empty( $atts['is_block'] ) ? true : false,
		);

		// Whitelist options
		$allowed_types   = array( 'alpha', 'daily', 'monthly', 'postbypost', 'weekly', 'yearly' );
		$allowed_orders  = array( 'ASC', 'DESC', 'asc', 'desc' );
		$allowed_formats = array( 'custom', 'html', 'option' );

		$instance['type']   = in_array( $instance['type'], $allowed_types, true ) ? $instance['type'] : 'monthly';
		$instance['order']  = in_array( $instance['order'], $allowed_orders, true ) ? $instance['order'] : 'asc';
		$instance['format'] = in_array( $instance['format'], $allowed_formats, true ) ? $instance['format'] : 'html';

		$ew_archive = new EW_Archives();

		return $ew_archive->shortcode( $instance );
	}
endif;
