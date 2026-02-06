<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://catchplugins.com
 * @since      1.0.0
 *
 * @package    Essential_Widgets
 * @subpackage Essential_Widgets/admin
 */

class Essential_Widgets_Admin {

    /**
     * Plugin name.
     *
     * @var string
     */
    private $plugin_name;

    /**
     * Plugin version.
     *
     * @var string
     */
    private $version;

    /**
     * Constructor.
     *
     * @param string $plugin_name
     * @param string $version
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

	/**
     * Register hooks for admin.
     */
    public function hooks() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_menu', array( $this, 'add_plugin_settings_menu' ) );
        add_filter( 'plugin_action_links', array( $this, 'add_plugin_meta_links' ), 10, 2 );
        add_action( 'wp_ajax_ew_switch', array( $this, 'ew_switch' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_filter( 'plugin_action_links_' . ESSENTIAL_WIDGETS_BASENAME, [ $this, 'action_links' ] );

    }

    /**
     * Enqueue admin styles.
     *
     * @param string $hook_suffix
     */
    public function enqueue_styles( $hook_suffix ) {
		// Only load on plugin admin page
        if ( 'toplevel_page_essential-widgets' === $hook_suffix ) {
            wp_enqueue_style(
                $this->plugin_name . '-display-dashboard-admin',
                plugin_dir_url( __FILE__ ) . 'css/essential-widgets-dasbhoard-admin.css',
                array(),
                $this->version,
                'all'
            );

            wp_enqueue_style(
                $this->plugin_name . '-display-dashboard',
                plugin_dir_url( __FILE__ ) . 'css/admin-dashboard.css',
                array(),
                $this->version,
                'all'
            );
        }

		// Always load on widgets and customizer screens
        global $pagenow;
        if ( in_array( $pagenow, array( 'widgets.php', 'customize.php' ), true ) ) {
            wp_enqueue_style(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'css/essential-widgets-admin.css',
                array(),
                $this->version,
                'all'
            );
        }
	}

	/**
     * Enqueue admin scripts.
     *
     * @param string $hook_suffix
     */
    public function enqueue_scripts( $hook_suffix ) {
		if ( 'toplevel_page_essential-widgets' === $hook_suffix ) {
            wp_enqueue_script(
                'minHeight',
                plugin_dir_url( __FILE__ ) . 'js/jquery.matchHeight.min.js',
                array( 'jquery' ),
                $this->version,
                false
            );

            wp_enqueue_script(
                $this->plugin_name . '-admin-script',
                plugin_dir_url( __FILE__ ) . 'js/admin-scripts.js',
                array( 'minHeight', 'jquery' ),
                $this->version,
                false
            );
        }

		global $pagenow;
        if ( in_array( $pagenow, array( 'widgets.php', 'customize.php' ), true ) ) {
            wp_enqueue_script(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'js/essential-widgets-admin.js',
                array( 'jquery' ),
                $this->version,
                false
            );
        }
	}

    /**
     * Add plugin menu in admin.
     */
    public function add_plugin_settings_menu() {
        add_menu_page(
            esc_html__( 'Essential Widgets', 'essential-widgets' ),
            esc_html__( 'Essential Widgets', 'essential-widgets' ),
            'manage_options',
            'essential-widgets',
            array( $this, 'settings_page' ),
            '',
            99
        );
    }

	/**
     * Render the settings page.
     */
    public function settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'essential-widgets' ) );
        }

        require plugin_dir_path( __FILE__ ) . 'partials/essential-widgets-admin-display.php';
    }

	/**
     * Register plugin settings.
     */
    public function register_settings() {
        register_setting(
            'essential-widgets-group',
            'essential_widgets_options',
            array( $this, 'sanitize_callback' )
        );
    }

	/**
     * Ajax switch handler (On/Off toggle).
     */
    public function ew_switch() {
        $post_data = wp_unslash( $_POST );

        if ( empty( $post_data['security'] ) || ! wp_verify_nonce( sanitize_text_field( $post_data['security'] ), 'ew_nonce' ) ) {
            wp_die( esc_html__( 'Unauthorized access!', 'essential-widgets' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied!', 'essential-widgets' ) );
        }

        $value       = ( isset( $post_data['value'] ) && 'true' === $post_data['value'] ) ? 1 : 0;
        $option_name = isset( $post_data['option_name'] ) ? sanitize_text_field( $post_data['option_name'] ) : '';

        if ( empty( $option_name ) ) {
            wp_die( esc_html__( 'Invalid option.', 'essential-widgets' ) );
        }

        $option_value               = essential_widgets_get_options();
        $option_value[ $option_name ] = $value;

        if ( update_option( 'essential_widgets_options', $option_value ) ) {
            echo esc_html( $value );
        } else {
            esc_html_e( 'Connection Error. Please try again.', 'essential-widgets' );
        }

        wp_die();
    }

	/**
     * Add plugin meta links on plugin page.
     *
     * @param array  $meta_fields
     * @param string $file
     * @return array
     */
    public function add_plugin_meta_links( $meta_fields, $file ) {
        if ( ESSENTIAL_WIDGETS_BASENAME === $file ) {
            $meta_fields[] = '<a href="' . esc_url( 'https://catchplugins.com/support-forum/forum/essential-widgets-free-wordpress-plugin/' ) . '" target="_blank">' . esc_html__( 'Support Forum', 'essential-widgets' ) . '</a>';
            $meta_fields[] = '<a href="' . esc_url( 'https://wordpress.org/support/plugin/essential-widgets/reviews/#new-post' ) . '" target="_blank" title="' . esc_attr__( 'Rate', 'essential-widgets' ) . '">'
                . '<i class="ct-rate-stars">'
                . str_repeat('<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>', 5)
                . '</i></a>';

            $stars_color = '#ffb900';

            echo '<style>'
                . '.ct-rate-stars{display:inline-block;color:' . esc_attr( $stars_color ) . ';position:relative;top:3px;}'
                . '.ct-rate-stars svg{fill:' . esc_attr( $stars_color ) . ';}'
                . '.ct-rate-stars svg:hover{fill:' . esc_attr( $stars_color ) . ';}'
                . '.ct-rate-stars svg:hover ~ svg{fill:none;}'
                . '</style>';
        }

        return $meta_fields;
    }

	/**
     * Dummy sanitize callback.
     *
     * @param array $input
     * @return array
     */
    public function sanitize_callback( $input ) {
        if ( ! is_array( $input ) ) {
            return array();
        }

        $sanitized = array();
        foreach ( $input as $key => $value ) {
            $sanitized[ sanitize_key( $key ) ] = sanitize_text_field( $value );
        }

        return $sanitized;
    }

    public function action_links( $links ) {
        $settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=essential-widgets' ) ) . '">' . esc_html__( 'Settings', 'essential-widgets' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

}
