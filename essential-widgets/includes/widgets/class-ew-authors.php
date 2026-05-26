<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Custom Author Widget
 *
 * @package Essential_Widgets
 */


/**
 * Custom Author Widget
 */
if (!class_exists('EW_Authors')) :
	class EW_Authors extends WP_Widget // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- EW_ is this plugin's abbreviated prefix; wrapped in class_exists() guard.
	{

		/**
		 * Holds widget settings defaults, populated in constructor.
		 *
		 * @var array
		 */
		protected $defaults;

		public function __construct()
		{

			// Translators: %s is the number of topics.
			$topic_count_text = _n_noop( '%s topic', '%s topics', 'essential-widgets' );

			// Set up defaults.
			$this->defaults = array(
				'title'         => esc_attr__('Authors', 'essential-widgets'),
				'order'         => 'ASC',
				'orderby'       => 'display_name',
				'number'        => '',
				'include'       => '',
				'exclude'       => '', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Required wp_list_authors() parameter, not a WP_Query post__not_in clause.
				'optioncount'   => false,
				'exclude_admin' => false,
				'show_fullname' => true,
				'hide_empty'    => true,
				'style'         => 'list',
				'html'          => true,
				'feed'          => '',
				'feed_type'     => '',
				'feed_image'    => ''
			);

			$widget_ops = array(
				'classname'   => 'essential-widgets ew-author ewauthor',
				'description' => esc_html__('Displays a list of author', 'essential-widgets'),
			);

			$control_ops = array(
				'id_base' => 'ew-author'
			);

			parent::__construct(
				'ew-author', // Base ID
				__('EW: Authors', 'essential-widgets'), // Name
				$widget_ops,
				$control_ops
			);
		}

		public function form($instance)
		{
			// Merge the user-selected arguments with the defaults.
			$instance = wp_parse_args((array) $instance, $this->defaults);

			$order = array(
				'ASC'  => esc_attr__('Ascending',  'essential-widgets'),
				'DESC' => esc_attr__('Descending', 'essential-widgets')
			);

			$orderby = array(
				'display_name' => esc_attr__('Display Name', 'essential-widgets'),
				'email'        => esc_attr__('Email',        'essential-widgets'),
				'ID'           => esc_attr__('ID',           'essential-widgets'),
				'nicename'     => esc_attr__('Nice Name',    'essential-widgets'),
				'post_count'   => esc_attr__('Post Count',   'essential-widgets'),
				'registered'   => esc_attr__('Registered',   'essential-widgets'),
				'url'          => esc_attr__('URL',          'essential-widgets'),
				'user_login'   => esc_attr__('Login',        'essential-widgets')
			);

			$style = array(
				'list' => esc_attr__('List', 'essential-widgets'),
				'none' => esc_attr__('Plain', 'essential-widgets')
			);

			$feed_type = array(
				''     => '',
				'atom' => esc_attr__('Atom',    'essential-widgets'),
				'rdf'  => esc_attr__('RDF',     'essential-widgets'),
				'rss'  => esc_attr__('RSS',     'essential-widgets'),
				'rss2' => esc_attr__('RSS 2.0', 'essential-widgets')
			); ?>

			<p>
				<label>
					<?php esc_html_e('Title:', 'essential-widgets'); ?>
					<input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr($instance['title']); ?>" placeholder="<?php echo esc_attr($this->defaults['title']); ?>" />
				</label>
			</p>

			<p>
				<label>
					<?php esc_html_e('Order:', 'essential-widgets'); ?>

					<select class="widefat" id="<?php echo esc_attr($this->get_field_id('order')); ?>" name="<?php echo esc_attr($this->get_field_name('order')); ?>">

						<?php foreach ($order as $option_value => $option_label) : ?>

							<option value="<?php echo esc_attr($option_value); ?>" <?php selected($instance['order'], $option_value); ?>><?php echo esc_html($option_label); ?></option>

						<?php endforeach; ?>

					</select>
				</label>
			</p>

			<p>
				<label>
					<?php esc_html_e('Order By:', 'essential-widgets'); ?>

					<select class="widefat" id="<?php echo esc_attr($this->get_field_id('orderby')); ?>" name="<?php echo esc_attr($this->get_field_name('orderby')); ?>">

						<?php foreach ($orderby as $option_value => $option_label) : ?>

							<option value="<?php echo esc_attr($option_value); ?>" <?php selected($instance['orderby'], $option_value); ?>><?php echo esc_html($option_label); ?></option>

						<?php endforeach; ?>

					</select>
				</label>
			</p>

			<p>
				<label>
					<?php esc_html_e('Number:', 'essential-widgets'); ?>
					<input type="number" class="widefat code" size="5" min="0" name="<?php echo esc_attr($this->get_field_name('number')); ?>" value="<?php echo esc_attr($instance['number']); ?>" placeholder="0" />
				</label>
			</p>

			<p>
				<label>
					<?php esc_html_e('Display as:', 'essential-widgets'); ?>

					<select class="widefat" id="<?php echo esc_attr($this->get_field_id('style')); ?>" name="<?php echo esc_attr($this->get_field_name('style')); ?>">

						<?php foreach ($style as $option_value => $option_label) : ?>

							<option value="<?php echo esc_attr($option_value); ?>" <?php selected($instance['style'], $option_value); ?>><?php echo esc_html($option_label); ?></option>

						<?php endforeach; ?>

					</select>
				</label>
			</p>

			<p>
				<label>
					<?php esc_html_e('Include:', 'essential-widgets'); ?>
					<input type="text" class="widefat code" name="<?php echo esc_attr($this->get_field_name('include')); ?>" value="<?php echo esc_attr($instance['include']); ?>" placeholder="1,2,3&hellip;" />
				</label>
			</p>

			<p>
				<label>
					<?php esc_html_e('Exclude:', 'essential-widgets'); ?>
					<input type="text" class="widefat code" name="<?php echo esc_attr($this->get_field_name('exclude')); ?>" value="<?php echo esc_attr($instance['exclude']); ?>" placeholder="1,2,3&hellip;" />
				</label>
			</p>

			<p class="button-primary ect-toggle-btn more">
				<span class="ect-more-text"><?php esc_html_e( 'More Options', 'essential-widgets' ); ?><i class="dashicons dashicons-arrow-down"></i></span>
				<span class="ect-hide-text"><?php esc_html_e( 'Hide Options', 'essential-widgets' ); ?><i class="dashicons dashicons-arrow-up"></i></span>

			<div class="advanced-section">

				<p>
					<label>
						<?php esc_html_e('Feed Type:', 'essential-widgets'); ?>

						<select class="widefat" name="<?php echo esc_attr($this->get_field_name('feed_type')); ?>">

							<?php foreach ($feed_type as $option_value => $option_label) : ?>

								<option value="<?php echo esc_attr($option_value); ?>" <?php selected($instance['feed_type'], $option_value); ?>><?php echo esc_html($option_label); ?></option>

							<?php endforeach; ?>

						</select>
					</label>
				</p>

				<p>
					<label>
						<?php esc_html_e('Feed Text:', 'essential-widgets'); ?>
						<input type="text" class="widefat code" name="<?php echo esc_attr($this->get_field_name('feed')); ?>" value="<?php echo esc_attr($instance['feed']); ?>" />
					</label>
				</p>

				<p>
					<label>
						<?php esc_html_e('Feed Image:', 'essential-widgets'); ?>
						<input type="url" class="widefat code" name="<?php echo esc_attr($this->get_field_name('feed_image')); ?>" value="<?php echo esc_attr($instance['feed_image']); ?>" placeholder="<?php echo esc_attr(home_url('images/example.png')); ?>" />
					</label>
				</p>

				<p>
					<label>
						<input type="checkbox" <?php checked($instance['html'], true); ?> name="<?php echo esc_attr($this->get_field_name('html')); ?>" />
						<?php esc_html_e('HTML?', 'essential-widgets'); ?>
					</label>
				</p>

				<p>
					<label>
						<input type="checkbox" <?php checked($instance['optioncount'], true); ?> name="<?php echo esc_attr($this->get_field_name('optioncount')); ?>" />
						<?php esc_html_e('Show post count?', 'essential-widgets'); ?>
					</label>
				</p>

				<p>
					<label>
						<input type="checkbox" <?php checked($instance['exclude_admin'], true); ?> name="<?php echo esc_attr($this->get_field_name('exclude_admin')); ?>" />
						<?php esc_html_e('Exclude admin?', 'essential-widgets'); ?>
					</label>
				</p>

				<p>
					<label>
						<input type="checkbox" <?php checked($instance['show_fullname'], true); ?> name="<?php echo esc_attr($this->get_field_name('show_fullname')); ?>" />
						<?php esc_html_e('Show full name?', 'essential-widgets'); ?>
					</label>
				</p>

				<p>
					<label>
						<input type="checkbox" <?php checked($instance['hide_empty'], true); ?> name="<?php echo esc_attr($this->get_field_name('hide_empty')); ?>" />
						<?php esc_html_e('Hide empty?', 'essential-widgets'); ?>
					</label>
				</p>

			</div><!-- .advanced-section -->

			<div style="clear:both;">&nbsp;</div>
<?php
		}

		public function update($new_instance, $old_instance)
		{

			// Sanitize title.
			$instance['title'] = sanitize_text_field($new_instance['title']);

			// Strip tags.
			$instance['feed'] = wp_strip_all_tags( $new_instance['feed'] );

			// Whitelist options.
			$order     = array('ASC', 'DESC');
			$orderby   = array('display_name', 'email', 'ID', 'nicename', 'post_count', 'registered', 'url', 'user_login');
			$style     = array('list', 'none');
			$feed_type = array('', 'atom', 'rdf', 'rss', 'rss2');

			$instance['order']     = in_array($new_instance['order'], $order, true)         ? $new_instance['order']     : 'ASC';
			$instance['orderby']   = in_array($new_instance['orderby'], $orderby, true)   ? $new_instance['orderby']   : 'display_name';
			$instance['style']     = in_array($new_instance['style'], $style, true)         ? $new_instance['style']     : 'list';
			$instance['feed_type'] = in_array($new_instance['feed_type'], $feed_type, true) ? $new_instance['feed_type'] : '';

			// Integers.
			$instance['number'] = intval($new_instance['number']);

			// Only allow integers and commas.
			$instance['include'] = preg_replace('/[^0-9,]/', '', $new_instance['include']);
			$instance['exclude'] = preg_replace('/[^0-9,]/', '', $new_instance['exclude']); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Required wp_list_authors() parameter, not a WP_Query post__not_in clause.

			// URLs.
			$instance['feed_image'] = esc_url_raw($new_instance['feed_image']);

			// Checkboxes.
			$instance['html']          = isset($new_instance['html'])          ? 1 : 0;
			$instance['optioncount']   = isset($new_instance['optioncount'])   ? 1 : 0;
			$instance['exclude_admin'] = isset($new_instance['exclude_admin']) ? 1 : 0;
			$instance['show_fullname'] = isset($new_instance['show_fullname']) ? 1 : 0;
			$instance['hide_empty']    = isset($new_instance['hide_empty'])    ? 1 : 0;

			// Return sanitized options.
			return $instance;
		}

		public function widget($args, $instance)
		{
			// Merge instance with defaults.
			$instance = wp_parse_args( $instance, $this->defaults );

			// Escape widget wrapper attributes.
			echo wp_kses_post( $args['before_widget'] );

			// If a title was input by the user, display it safely.
			if ( ! empty( $instance['title'] ) ) {
				echo wp_kses_post( $args['before_title'] );

				// Apply filters to title, then escape it for output
				$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
				echo esc_html( $title );

				echo wp_kses_post( $args['after_title'] );
			}

			// Output the main content of the widget (shortcode output)
			echo wp_kses_post( $this->shortcode( $instance ) );

			// Close the widget wrapper safely.
			echo wp_kses_post( $args['after_widget'] );
		}

		public function shortcode( $atts )
		{

			$atts = wp_parse_args( $atts, $this->defaults );

			// Normalize arguments
			$args = array();

			$args['order']     = in_array( $atts['order'], array( 'ASC', 'DESC' ), true ) ? $atts['order'] : 'ASC';
			$args['orderby']   = sanitize_key( $atts['orderby'] );
			$args['number']    = absint( $atts['number'] );
			$args['include']   = preg_replace( '/[^0-9,]/', '', $atts['include'] );
			$args['exclude']   = preg_replace( '/[^0-9,]/', '', $atts['exclude'] ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Required wp_list_authors() parameter, not a WP_Query post__not_in clause.
			$args['html']      = (bool) $atts['html'];
			$args['echo']      = false;
			$args['optioncount']   = (bool) $atts['optioncount'];
			$args['exclude_admin'] = (bool) $atts['exclude_admin'];
			$args['show_fullname'] = (bool) $atts['show_fullname'];
			$args['hide_empty']    = (bool) $atts['hide_empty'];
			$args['feed']          = sanitize_text_field( $atts['feed'] );
			$args['feed_type']     = sanitize_text_field( $atts['feed_type'] );
			$args['feed_image']    = esc_url( $atts['feed_image'] );

			$ew_author = '';

			// Block-only title
			if ( isset( $atts['is_block'] ) && true === $atts['is_block'] && ! empty( $atts['title'] ) ) {
				$ew_author .= '<h2 class="ew-author-block-title">' . esc_html( $atts['title'] ) . '</h2>';
			}

			// Get authors HTML safely
			$authors = wp_list_authors( $args );
			$authors = str_replace( array( "\r", "\n", "\t" ), '', $authors );

			$authors = str_replace( '</a> (', '</a> <span>(', $authors );
			$authors = str_replace( ')', ')</span>', $authors );

			// Wrap output
			if ( 'list' === $atts['style'] && $args['html'] ) {
				$ew_author .= '<ul class="authors">' . wp_kses_post( $authors ) . '</ul>';
			} elseif ( 'none' === $atts['style'] && $args['html'] ) {
				$ew_author .= '<p class="authors">' . wp_kses_post( $authors ) . '</p>';
			}

			return $ew_author;
		}
	} // end Author_Widget class
endif;

if (!function_exists('ew_authors_register')) :
	/**
	 * Intiate Author_Widget Class.
	 *
	 * @since 1.0.0
	 */
	function ew_authors_register() // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- ew_ is this plugin's abbreviated prefix.
	{
		register_widget('EW_Authors');
	}
	add_action('widgets_init', 'ew_authors_register');
endif;
