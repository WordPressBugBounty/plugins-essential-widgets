<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://catchplugins.com
 * @since             1.0.0
 * @package           Essential_Widgets
 *
 * @wordpress-plugin
 * Plugin Name:       Essential Widgets
 * Plugin URI:        https://catchplugins.com/plugins/essential-widgets/
 * Description:       Essential Widgets is a WordPress plugin for widgets that allows you to create and add amazing widgets with high customization option on your website without affecting your wallet.
 * Version:           3.0.1
 * Author:            Catch Plugins
 * Author URI:        https://catchplugins.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       essential-widgets
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define Version
define( 'ESSENTIAL_WIDGETS_VERSION', '3.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-essential-widgets-activator.php
 */
if ( ! defined( 'ESSENTIAL_WIDGETS_URL' ) ) {
	define( 'ESSENTIAL_WIDGETS_URL', plugin_dir_url( __FILE__ ) );
}


// The absolute path of the directory that contains the file
if ( ! defined( 'ESSENTIAL_WIDGETS_PATH' ) ) {
	define( 'ESSENTIAL_WIDGETS_PATH', plugin_dir_path( __FILE__ ) );
}


// Gets the path to a plugin file or directory, relative to the plugins directory, without the leading and trailing slashes.
if ( ! defined( 'ESSENTIAL_WIDGETS_BASENAME' ) ) {
	define( 'ESSENTIAL_WIDGETS_BASENAME', plugin_basename( __FILE__ ) );
}

function essential_widgets_activate() {
	$required = 'essential-widgets-pro/essential-widgets-pro.php';
	if ( is_plugin_active( $required ) ) {
		// Translators: %1$s and %2$s wrap the "Return to Plugins" link. The message informs the user that the Pro version is active.
		$message = __( 'Sorry, Pro version is already active. No need to activate Free version. If you still want to activate the Free version, please deactivate the Pro version first. %1$s&laquo; Return to Plugins%2$s.', 'essential-widgets' );

		$message = sprintf(
			$message,
			'<br><a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">',
			'</a>'
		);

		// Escape all HTML output before printing
		wp_die( wp_kses_post( $message ) );
	}

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-essential-widgets-activator.php';
	Essential_Widgets_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-essential-widgets-deactivator.php
 */
function essential_widgets_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-essential-widgets-deactivator.php';
	Essential_Widgets_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'essential_widgets_activate' );
register_deactivation_hook( __FILE__, 'essential_widgets_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-essential-widgets.php';


function essential_widgets_sanitize_checkbox( $checked ) {
	// Boolean check.
	return ( ( isset( $checked ) && true == $checked ) ? true : false );
}

if ( ! function_exists( 'essential_widgets_get_options' ) ) :
	function essential_widgets_get_options() {
		$defaults = essential_widgets_default_options();
		$options  = get_option( 'essential_widgets_options', $defaults );

		return wp_parse_args( $options, $defaults );
	}
endif; // essential_widgets_get_options


if ( ! function_exists( 'essential_widgets_default_options' ) ) :
	/**
	 * Return array of default options
	 *
	 * @since     1.0
	 * @return    array    default options.
	 */
	function essential_widgets_default_options( $option = null ) {
		$widget_list = essential_widgets_list();
		$default_options = array();

		foreach ( $widget_list as $key => $value ) {
			$default_options[ $key ] = 1;
		}

		if ( null == $option ) {
			return apply_filters( 'essential_widgets_options', $default_options );
		} else {
			return $default_options[ $option ];
		}
	}
endif; // essential_widgets_default_options


if ( ! function_exists( 'essential_widgets_list' ) ) :
	function essential_widgets_list() {
		$widget_list = array(
			'ew_authors'    => 'EW: Authors',
			'ew_categories' => 'EW: Category',
			'ew_menus'      => 'EW: Menu',
			'ew_pages'      => 'EW: Pages',
			'ew_posts'      => 'EW: Post',
			'ew_archives'   => 'EW: Recent Posts',
			'ew_tags'       => 'EW: Tags',
		);
		return $widget_list;
	}
endif;

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function essential_widgets_run() {

	$plugin = new Essential_Widgets();
	$plugin->run();

}
essential_widgets_run();

/* CTP tabs removal options */
require plugin_dir_path( __FILE__ ) . 'includes/ctp-tabs-removal.php';

$ctp_options = ctp_get_options();
if ( 1 == $ctp_options['theme_plugin_tabs'] ) {
	/* Adds Catch Themes tab in Add theme page and Themes by Catch Themes in Customizer's change theme option. */
	if ( ! class_exists( 'CatchThemesThemePlugin' ) && ! function_exists( 'add_our_plugins_tab' ) ) {
		require plugin_dir_path( __FILE__ ) . 'includes/CatchThemesThemePlugin.php';
	}
}

/** Add EW Blocks */
require plugin_dir_path( __FILE__ ) . 'includes/ew-block/index.php';
