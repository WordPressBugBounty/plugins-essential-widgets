<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class CatchThemesThemePlugin {
	public function __construct() {
		remove_action( 'wp_ajax_query-themes', array( $this, 'wp_ajax_query_themes' ), 1 );
		add_action( 'wp_ajax_query-themes', array( $this, 'wp_ajax_custom_query_themes' ), 1 );

		add_action( 'admin_enqueue_scripts', array( $this, 'our_themes_script' ) );

		if ( ! is_multisite() ) {
			add_action( 'customize_register', array( $this, 'customize_register' ) );
		}

		global $wp_customize;
		remove_action( 'wp_ajax_customize_load_themes', array( $wp_customize, 'handle_load_themes_request' ) );
		add_action( 'wp_ajax_customize_load_themes', array( $this, 'handle_load_themes_request' ) );

		add_filter( 'install_plugins_tabs', array( $this, 'add_our_plugins_tab' ), 1 );
		add_filter( 'install_plugins_table_api_args_catchplugins', array( $this, 'catchplugins' ), 1 );
		add_action( 'install_plugins_catchplugins', array( $this, 'plugins_table' ) );
	}

	/* Adds Catch Themes tab in Add Theme page to show all themes by Catch Themes in wordpress.org
	 * taken from wp-admin/includes/ajax-action.php wp_ajax_query_themes().
	 * Ajax handler for getting themes from themes_api().
	 *
	 * @since 3.9.0
	 *
	 * @global array $themes_allowedtags
	 * @global array $theme_field_defaults
	 */
	public function wp_ajax_custom_query_themes() {
		global $themes_allowedtags, $theme_field_defaults;

		// Capability check
		if ( ! current_user_can( 'install_themes' ) ) {
			wp_send_json_error();
		}

		// Nonce verification
		check_ajax_referer( 'ew_query_themes_nonce', 'nonce' );

		// Copy superglobal ONCE
		$post_data = wp_unslash( $_POST );

		// Now sanitize
		$post_data = map_deep( $post_data, 'sanitize_text_field' );

		// From this point on, DO NOT use $_POST again
		if ( empty( $post_data['request'] ) || ! is_array( $post_data['request'] ) ) {
			wp_send_json_error();
		}

		$args = wp_parse_args(
			$post_data['request'],
			array(
				'per_page' => 20,
				'fields'   => array_merge(
					(array) $theme_field_defaults,
					array(
						'reviews_url' => true,
					)
				),
			)
		);

		if ( isset( $args['browse'] ) && 'catchthemes' === $args['browse'] && ! isset( $args['user'] ) ) {
			$args['author'] = 'catchthemes';
			unset( $args['browse'] );
		}

		$old_filter = isset( $args['browse'] ) ? $args['browse'] : 'search';

		$args = apply_filters( 'install_themes_table_api_args_' . $old_filter, $args );

		$api = themes_api( 'query_themes', $args );

		if ( is_wp_error( $api ) ) {
			wp_send_json_error();
		}

		foreach ( $api->themes as &$theme ) {
			$theme->name        = wp_kses( $theme->name, $themes_allowedtags );
			$theme->author      = wp_kses( $theme->author['display_name'], $themes_allowedtags );
			$theme->version     = wp_kses( $theme->version, $themes_allowedtags );
			$theme->description = wp_kses( $theme->description, $themes_allowedtags );
		}

		wp_send_json_success( $api );
	}

	public function our_themes_script( $hook_suffix ) {

		if ( 'theme-install.php' === $hook_suffix ) {
			wp_enqueue_script( 'our-themes-script', plugin_dir_url( __FILE__ ) . '../js/our-themes.js', array( 'jquery' ), '2018-05-16' );
		}
	}

	/* Add Catch Themes Section in Theme in Customizer */
	public function customize_register( $wp_customize ) {
		$wp_customize->add_section(
			new WP_Customize_Themes_Section(
				$wp_customize,
				'catchthemes',
				array(
					'title'      => __( 'Themes by CatchThemes', 'essential-widgets' ),
					'action'     => 'catchthemes',
					'capability' => 'install_themes',
					'panel'      => 'themes',
					'priority'   => 6,
				)
			)
		);
	}




	/**
	 * Load themes into the theme browsing/installation UI.
	 * taken from wp-includes/cllass-wp-customize-manager.php
	 * @since 4.9.0
	 */
	public function handle_load_themes_request() {
		check_ajax_referer( 'switch_themes', 'nonce' );
		if ( ! current_user_can( 'switch_themes' ) ) {
			wp_die( -1 );
		}

		if ( empty( $_POST['theme_action'] ) ) {
			wp_send_json_error( 'missing_theme_action' );
		}
		$theme_action = sanitize_key( $_POST['theme_action'] );
		$themes       = array();
		$args         = array();

		// Define query filters based on user input.
		if ( ! array_key_exists( 'search', $_POST ) ) {
			$args['search'] = '';
		} else {
			$args['search'] = sanitize_text_field( wp_unslash( $_POST['search'] ) );
		}

		if ( ! array_key_exists( 'tags', $_POST ) ) {
			$args['tag'] = '';
		} else {
			$args['tag'] = array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['tags'] ) );
		}

		if ( ! array_key_exists( 'page', $_POST ) ) {
			$args['page'] = 1;
		} else {
			$args['page'] = absint( $_POST['page'] );
		}

		require_once ABSPATH . 'wp-admin/includes/theme.php';

		if ( 'installed' === $theme_action ) {

			// Load all installed themes from wp_prepare_themes_for_js().
			$themes = array( 'themes' => wp_prepare_themes_for_js() );
			foreach ( $themes['themes'] as &$theme ) {
				$theme['type']   = 'installed';
				$theme['active'] = ( isset( $_POST['customized_theme'] ) && $_POST['customized_theme'] === $theme['id'] );
			}
		} elseif ( 'catchthemes' === $theme_action ) {

			// Load WordPress.org themes from the .org API and normalize data to match installed theme objects.
			if ( ! current_user_can( 'install_themes' ) ) {
				wp_die( -1 );
			}

			// Arguments for all queries.
			$wporg_args = array(
				'per_page' => 100,
				'fields'   => array(
					'reviews_url' => true, // Explicitly request the reviews URL to be linked from the customizer.
				),
			);

			$args = array_merge( $wporg_args, $args );

			if ( '' === $args['search'] && '' === $args['tag'] ) {
				$args['browse'] = 'new'; // Sort by latest themes by default.
			}

			$args['author'] = 'catchthemes';

			// Load themes from the .org API.
			$themes = themes_api( 'query_themes', $args );
			if ( is_wp_error( $themes ) ) {
				wp_send_json_error();
			}

			// This list matches the allowed tags in wp-admin/includes/theme-install.php.
			$themes_allowedtags                     = array_fill_keys(
				array( 'a', 'abbr', 'acronym', 'code', 'pre', 'em', 'strong', 'div', 'p', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'img' ),
				array()
			);
			$themes_allowedtags['a']                = array_fill_keys( array( 'href', 'title', 'target' ), true );
			$themes_allowedtags['acronym']['title'] = true;
			$themes_allowedtags['abbr']['title']    = true;
			$themes_allowedtags['img']              = array_fill_keys( array( 'src', 'class', 'alt' ), true );

			// Prepare a list of installed themes to check against before the loop.
			$installed_themes = array();
			$wp_themes        = wp_get_themes();
			foreach ( $wp_themes as $theme ) {
				$installed_themes[] = $theme->get_stylesheet();
			}
			$update_php = network_admin_url( 'update.php?action=install-theme' );

			// Set up properties for themes available on WordPress.org.
			foreach ( $themes->themes as &$theme ) {
				$theme->install_url = add_query_arg(
					array(
						'theme'    => $theme->slug,
						'_wpnonce' => wp_create_nonce( 'install-theme_' . $theme->slug ),
					),
					$update_php
				);

				$theme->name        = wp_kses( $theme->name, $themes_allowedtags );
				$theme->version     = wp_kses( $theme->version, $themes_allowedtags );
				$theme->description = wp_kses( $theme->description, $themes_allowedtags );
				$theme->stars       = wp_star_rating(
					array(
						'rating' => $theme->rating,
						'type'   => 'percent',
						'number' => $theme->num_ratings,
						'echo'   => false,
					)
				);
				$theme->num_ratings = number_format_i18n( $theme->num_ratings );
				$theme->preview_url = set_url_scheme( $theme->preview_url );

				// Handle themes that are already installed as installed themes.
				if ( in_array( $theme->slug, $installed_themes, true ) ) {
					$theme->type = 'installed';
				} else {
					$theme->type = $theme_action;
				}

				// Set active based on customized theme.
				$theme->active = ( isset( $_POST['customized_theme'] ) && $_POST['customized_theme'] === $theme->slug );

				// Map available theme properties to installed theme properties.
				$theme->id            = $theme->slug;
				$theme->screenshot    = array( $theme->screenshot_url );
				$theme->authorAndUri  = wp_kses( $theme->author['display_name'], $themes_allowedtags );
				$theme->compatibleWP  = is_wp_version_compatible( $theme->requires ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				$theme->compatiblePHP = is_php_version_compatible( $theme->requires_php ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

				if ( isset( $theme->parent ) ) {
					$theme->parent = $theme->parent['slug'];
				} else {
					$theme->parent = false;
				}
				unset( $theme->slug );
				unset( $theme->screenshot_url );
				unset( $theme->author );
			} // End foreach().
		} elseif ( 'wporg' === $theme_action ) {

			// Load WordPress.org themes from the .org API and normalize data to match installed theme objects.
			if ( ! current_user_can( 'install_themes' ) ) {
				wp_die( -1 );
			}

			// Arguments for all queries.
			$wporg_args = array(
				'per_page' => 100,
				'fields'   => array(
					'reviews_url' => true, // Explicitly request the reviews URL to be linked from the customizer.
				),
			);

			$args = array_merge( $wporg_args, $args );

			if ( '' === $args['search'] && '' === $args['tag'] ) {
				$args['browse'] = 'new'; // Sort by latest themes by default.
			}

			// Load themes from the .org API.
			$themes = themes_api( 'query_themes', $args );
			if ( is_wp_error( $themes ) ) {
				wp_send_json_error();
			}

			// This list matches the allowed tags in wp-admin/includes/theme-install.php.
			$themes_allowedtags                     = array_fill_keys(
				array( 'a', 'abbr', 'acronym', 'code', 'pre', 'em', 'strong', 'div', 'p', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'img' ),
				array()
			);
			$themes_allowedtags['a']                = array_fill_keys( array( 'href', 'title', 'target' ), true );
			$themes_allowedtags['acronym']['title'] = true;
			$themes_allowedtags['abbr']['title']    = true;
			$themes_allowedtags['img']              = array_fill_keys( array( 'src', 'class', 'alt' ), true );

			// Prepare a list of installed themes to check against before the loop.
			$installed_themes = array();
			$wp_themes        = wp_get_themes();
			foreach ( $wp_themes as $theme ) {
				$installed_themes[] = $theme->get_stylesheet();
			}
			$update_php = network_admin_url( 'update.php?action=install-theme' );

			// Set up properties for themes available on WordPress.org.
			foreach ( $themes->themes as &$theme ) {
				$theme->install_url = add_query_arg(
					array(
						'theme'    => $theme->slug,
						'_wpnonce' => wp_create_nonce( 'install-theme_' . $theme->slug ),
					),
					$update_php
				);

				$theme->name        = wp_kses( $theme->name, $themes_allowedtags );
				$theme->version     = wp_kses( $theme->version, $themes_allowedtags );
				$theme->description = wp_kses( $theme->description, $themes_allowedtags );
				$theme->stars       = wp_star_rating(
					array(
						'rating' => $theme->rating,
						'type'   => 'percent',
						'number' => $theme->num_ratings,
						'echo'   => false,
					)
				);
				$theme->num_ratings = number_format_i18n( $theme->num_ratings );
				$theme->preview_url = set_url_scheme( $theme->preview_url );

				// Handle themes that are already installed as installed themes.
				if ( in_array( $theme->slug, $installed_themes, true ) ) {
					$theme->type = 'installed';
				} else {
					$theme->type = $theme_action;
				}

				// Set active based on customized theme.
				$theme->active = ( isset( $_POST['customized_theme'] ) && $_POST['customized_theme'] === $theme->slug );

				// Map available theme properties to installed theme properties.
				$theme->id            = $theme->slug;
				$theme->screenshot    = array( $theme->screenshot_url );
				$theme->authorAndUri  = wp_kses( $theme->author['display_name'], $themes_allowedtags );
				$theme->compatibleWP  = is_wp_version_compatible( $theme->requires ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				$theme->compatiblePHP = is_php_version_compatible( $theme->requires_php ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

				if ( isset( $theme->parent ) ) {
					$theme->parent = $theme->parent['slug'];
				} else {
					$theme->parent = false;
				}
				unset( $theme->slug );
				unset( $theme->screenshot_url );
				unset( $theme->author );
			} // End foreach().
		} // End if().

		/**
		 * Filters the theme data loaded in the customizer.
		 *
		 * This allows theme data to be loading from an external source,
		 * or modification of data loaded from `wp_prepare_themes_for_js()`
		 * or WordPress.org via `themes_api()`.
		 *
		 * @since 4.9.0
		 *
		 * @see wp_prepare_themes_for_js()
		 * @see themes_api()
		 * @see WP_Customize_Manager::__construct()
		 *
		 * @param array                $themes  Nested array of theme data.
		 * @param array                $args    List of arguments, such as page, search term, and tags to query for.
		 * @param WP_Customize_Manager $manager Instance of Customize manager.
		 */
		$themes = apply_filters( 'customize_load_themes', $themes, $args, $wp_customize );

		wp_send_json_success( $themes );
	}

	public function add_our_plugins_tab( $tabs ) {
		// Translators: Tab label in the Plugin Installer for listing Catch Plugins.
		$tabs['catchplugins'] = _x( 'Catch Plugins', 'Plugin Installer', 'essential-widgets' );

		return $tabs;
	}


	public function catchplugins() {
		/* From CORE Start */
		global $paged, $tab;
		wp_reset_vars( array( 'tab' ) );

		$defined_class = new WP_Plugin_Install_List_Table();
		$paged         = $defined_class->get_pagenum();

		$per_page = 30;
		//$installed_plugins = catch_get_installed_plugins();

		$args = array(
			'page'     => $paged,
			'per_page' => $per_page,
			'fields'   => array(
				'last_updated'    => true,
				'icons'           => true,
				'active_installs' => true,
			),
			// Send the locale and installed plugin slugs to the API so it can provide context-sensitive results.
			'locale'   => get_user_locale(),
			//'installed_plugins' => array_keys( $installed_plugins ),
		);
		/* From CORE End */

		// Add author filter for our plugins
		$args['author'] = 'catchplugins';

		return $args;
	}

	public function plugins_table() {
		global $wp_list_table;
		printf(
			'<p class="catch-plugins-list">%s</p>',
			sprintf(
				// Translators: %s is the URL to the Catch Plugins website.
				wp_kses_post( __( 'You can use any of our free plugins or premium plugins from <a href="%s" target="_blank">Catch Plugins</a>.', 'essential-widgets' ) ),
				esc_url( 'https://catchplugins.com/' )
			)
		);
		?>
		<form id="plugin-filter" method="post">
			<?php $wp_list_table->display(); ?>
		</form>
		<?php
	}
}

$catchthemes_theme_plugin = new CatchThemesThemePlugin();
