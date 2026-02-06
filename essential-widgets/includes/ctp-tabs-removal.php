<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * ctp_register_settings
 * CTP Register Settings
 */
if ( ! function_exists( 'ctp_register_settings' ) ) {
	function ctp_register_settings() {
		// register_setting( $option_group, $option_name, $sanitize_callback )
		register_setting(
			'ctp-group',
			'ctp_options',
			array()
		);
	}
}
add_action( 'admin_init', 'ctp_register_settings' );

if ( ! function_exists( 'ctp_get_options' ) ) {
	/**
	 * Returns the options array for ctp_get options
	 *
	 *  @since    1.3
	 */
	function ctp_get_options() {
		$defaults = ctp_default_options();
		$options  = get_option( 'ctp_options', $defaults );

		return wp_parse_args( $options, $defaults );
	}
}

if ( ! function_exists( 'ctp_default_options' ) ) {
	/**
	 * Return array of default options
	 *
	 * @since     1.3
	 * @return    array    default options.
	 */
	function ctp_default_options( $option = null ) {
		$default_options['theme_plugin_tabs'] = 1;
		if ( null == $option ) {
			return apply_filters( 'ctp_options', $default_options );
		} else {
			return $default_options[ $option ];
		}
	}
}

if ( ! function_exists( 'ctp_switch' ) ) {
	/**
	 * AJAX switch handler
	 *
	 * @since     1.3
	 */
	function ctp_switch() {

		// Required fields check
		if ( ! isset( $_POST['security'], $_POST['value'], $_POST['option_name'] ) ) {
			wp_die( esc_html__( 'Invalid request.', 'essential-widgets' ) );
		}

		// Sanitize & verify nonce
		$nonce = sanitize_text_field( wp_unslash( $_POST['security'] ) );
		if ( ! wp_verify_nonce( $nonce, 'ew_switch_tabs_nonce' ) ) {
			wp_die( esc_html__( 'Unauthorized access!', 'essential-widgets' ) );
		}

		// Capability check
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied!', 'essential-widgets' ) );
		}

		// Sanitize remaining inputs
		$raw_value = sanitize_text_field( wp_unslash( $_POST['value'] ) );
		$value     = ( 'true' === $raw_value ) ? 1 : 0;

		$option_name = sanitize_key( wp_unslash( $_POST['option_name'] ) );

		// Update option safely
		$option_value               	= ctp_get_options();
		$option_value[ $option_name ] 	= $value;

		if ( update_option( 'ctp_options', $option_value ) ) {
			echo esc_html( (string) $value );
		} else {
			esc_html_e( 'Connection Error. Please try again.', 'essential-widgets' );
		}

		wp_die(); // Required for AJAX
	}
}
add_action( 'wp_ajax_ctp_switch', 'ctp_switch' );
