<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Hook the post rendering to the block
if ( function_exists( 'register_block_type' ) ) :
	register_block_type(
		'ew-block/ew-menu',
		array(
			'attributes'      => array(
				'title'           => array(
					'type'    => 'string',
					'default' 	=> 'Navigation', // No translation here
				),
				'menu'            => array(
					'type'    => 'string',
					'default' => '',
				),
				'container'       => array(
					'type'    => 'string',
					'default' => 'div',
				),
				'container_id'    => array(
					'type'    => 'string',
					'default' => '',
				),
				'container_class' => array(
					'type'    => 'string',
					'default' => '',
				),
				'menu_id'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'menu_class'      => array(
					'type'    => 'string',
					'default' => 'nav-menu',
				),
				'depth'           => array(
					'type'    => 'number',
					'default' => 0,
				),
				'before'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'after'           => array(
					'type'    => 'string',
					'default' => '',
				),
				'link_before'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'link_after'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'fallback_cb'     => array(
					'type'    => 'string',
					'default' => 'wp_page_menu',
				),
				'is_block'        => array(
					'type'    => 'boolean',
					'default' => true,
				),

			),
			'render_callback' => 'ew_menu_render_shortcode',
		)
	);
endif;

if ( ! function_exists( 'ew_menu_render_shortcode' ) ) :
	add_shortcode( 'ew-menu', 'ew_menu_render_shortcode' );
	function ew_menu_render_shortcode( $atts ) {
		$instance['title'] 			= isset( $atts['title'] ) && 'Navigation' === $atts['title']
    		? esc_html__( 'Navigation', 'essential-widgets' )
    		: sanitize_text_field( $atts['title'] );

		$instance['menu']            = isset( $atts['menu'] ) ? sanitize_text_field( $atts['menu'] ) : '';
		$instance['container']       = isset( $atts['container'] ) ? sanitize_key( $atts['container'] ) : 'div';
		$instance['container_id']    = isset( $atts['container_id'] ) ? sanitize_html_class( $atts['container_id'] ) : '';
		$instance['container_class'] = isset( $atts['container_class'] ) ? sanitize_html_class( $atts['container_class'] ) : '';
		$instance['menu_id']         = isset( $atts['menu_id'] ) ? sanitize_html_class( $atts['menu_id'] ) : '';
		$instance['menu_class']      = isset( $atts['menu_class'] ) ? sanitize_html_class( $atts['menu_class'] ) : 'nav-menu';
		$instance['depth']           = isset( $atts['depth'] ) ? absint( $atts['depth'] ) : 0;
		$instance['before']          = isset( $atts['before'] ) ? wp_kses_post( $atts['before'] ) : '';
		$instance['after']           = isset( $atts['after'] ) ? wp_kses_post( $atts['after'] ) : '';
		$instance['link_before']     = isset( $atts['link_before'] ) ? wp_kses_post( $atts['link_before'] ) : '';
		$instance['link_after']      = isset( $atts['link_after'] ) ? wp_kses_post( $atts['link_after'] ) : '';
		$instance['fallback_cb']     = isset( $atts['fallback_cb'] ) && function_exists( $atts['fallback_cb'] ) ? $atts['fallback_cb'] : 'wp_page_menu';
		$instance['is_block']        = !empty( $atts['is_block'] ) ? (bool) $atts['is_block'] : true;

		$ew_menu = new EW_Menus();

		return $ew_menu->shortcode( $instance );
	}
endif;


if ( ! function_exists( 'ew_menu_list' ) ) :
	/**
	 * Get nav menus for select option via custom REST API!
	 *
	 * @return array|null Array of nav menus object with label and value pair, * or null if none.
	 */
	function ew_menu_list() {
		$menus = wp_get_nav_menus();

		$menu_list = array();

		foreach ( $menus as $menu ) {
			$object        = new stdClass();
			$object->label = sanitize_text_field( $menu->name );
			$object->value = absint( $menu->term_id );
			$menu_list[]   = $object;
		}

		return $menu_list;
	}

	add_action(
		'rest_api_init',
		function () {
			register_rest_route(
				'ew-rest/v1',
				'ew-menu-list',
				array(
					'methods'             => 'GET',
					'callback'            => 'ew_menu_list',
					'permission_callback' => '__return_true', // for public use
				)
			);
		}
	);
endif;
