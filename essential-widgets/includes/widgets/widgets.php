<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-scope variables used immediately below; file is included inside a function scope.

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * The template for adding Custom Sidebars and Widgets
 *
 * @package Essential_Widgets
 */

// Load Archives Widget

$option = essential_widgets_get_options();
$widget_list = essential_widgets_list();

foreach( $widget_list as $key => $value ) {
	if( 1 === (int) $option[ $key ] ) {
		$widget_file = str_replace( '_', '-', $key );

		// Enqueue active widget files
		include plugin_dir_path( __FILE__ ) . 'class-' . $widget_file . '.php';
	}
}
