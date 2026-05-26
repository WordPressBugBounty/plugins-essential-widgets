<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Hook the post rendering to the block
if ( function_exists( 'register_block_type' ) ) :
	register_block_type(
		'ew-block/ew-post',
		array(
			'attributes'      => array(
				'title'       => array(
					'type'    => 'string',
					'default' 	=> 'Posts', // No translation here
				),
				'post_type'   => array(
					'type'    => 'array',
					'default' => array( 'post' ),
					'items'   => array( 'type' => 'string' ),
				),
				'number'      => array(
					'type'    => 'number',
					'default' => 10,
				),
				'order'       => array(
					'type'    => 'string',
					'default' => 'desc',
				),
				'orderby'     => array(
					'type'    => 'string',
					'default' => 'date',
				),
				'show_date'   => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'show_author' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'is_block'    => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
			'render_callback' => 'ew_post_render_shortcode',
		)
	);
endif;

if ( ! function_exists( 'ew_post_render_shortcode' ) ) :
	add_shortcode( 'ew-post', 'ew_post_render_shortcode' );
	function ew_post_render_shortcode( $atts ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- ew_ is this plugin's abbreviated prefix; wrapped in function_exists() guard.
		$instance['title']         = isset( $atts['title'] ) && 'Posts' === $atts['title']
			? esc_html__( 'Posts', 'essential-widgets' )
			: sanitize_text_field( $atts['title'] );
		$instance['post_type']   = array_map('sanitize_key', (array) $atts['post_type']);
		$instance['number']      = intval($atts['number']);
		$instance['show_date']   = !empty($atts['show_date']);
		$instance['show_author'] = !empty($atts['show_author']);
		$instance['order'] = ( isset( $atts['order'] ) && in_array( $atts['order'], ['ASC','DESC'], true ) )
		    ? $atts['order']
		    : 'DESC';

		$instance['orderby'] = ( isset( $atts['orderby'] ) && in_array( $atts['orderby'], ['author','name','none','type','date','ID','modified','parent','comment_count','menu_order','title'], true ) )
		    ? $atts['orderby']
		    : 'date';
		$instance['is_block']    = !empty($atts['is_block']);

		$ew_post = new EW_Posts();

		return $ew_post->shortcode( $instance );
	}
endif;

if ( ! function_exists( 'ew_post_list' ) ) :

	/**
	 * Get nav menus for select option via custom REST API!
	 *
	 * @return array|null Array of nav menus object with label and value pair, * or null if none.
	 */
	function ew_post_list() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- ew_ is this plugin's abbreviated prefix; wrapped in function_exists() guard.
		$post_types = get_post_types(
			array(
				'public'       => true,
				'hierarchical' => false,
			),
			'objects'
		);

		$post_list = array();

		foreach ( $post_types as $post ) {
			$object        = new stdClass();
			$object->label = $post->labels->singular_name;
			$object->value = $post->name;
			$post_list[]   = $object;
		}

		return $post_list;
	}

	add_action(
		'rest_api_init',
		function () {
			register_rest_route(
				'ew-rest/v1',
				'ew-post-list',
				array(
					'methods'             => 'GET',
					'callback'            => 'ew_post_list',
					'permission_callback' => '__return_true', // for public use
				)
			);
		}
	);
endif;
