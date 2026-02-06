<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'EW_Archives' ) ) :
class EW_Archives extends WP_Widget {

	protected $defaults;

	public function __construct() {
		$this->defaults = array(
			'title'           => esc_attr__( 'Archives', 'essential-widgets' ),
			'limit'           => 10,
			'type'            => 'monthly',
			'post_type'       => 'post',
			'order'           => 'DESC',
			'format'          => 'html',
			'before'          => '',
			'after'           => '',
			'show_post_count' => false,
		);

		parent::__construct(
			'ew-archive',
			__( 'EW: Archives', 'essential-widgets' ),
			array(
				'classname'   => 'essential-widgets widget_archive ew-archive ewarchive',
				'description' => esc_html__( 'Displays a list of categories', 'essential-widgets' ),
			),
			array(
				'id_base' => 'ew-archive',
			)
		);
	}

	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$post_types = get_post_types( array( 'name' => 'post' ), 'objects' );
		$post_types = array_merge(
			$post_types,
			get_post_types( array( 'publicly_queryable' => true, '_builtin' => false ), 'objects' )
		);

		$type = array(
			'alpha'      => esc_attr__( 'Alphabetical', 'essential-widgets' ),
			'daily'      => esc_attr__( 'Daily', 'essential-widgets' ),
			'monthly'    => esc_attr__( 'Monthly', 'essential-widgets' ),
			'postbypost' => esc_attr__( 'Post By Post', 'essential-widgets' ),
			'weekly'     => esc_attr__( 'Weekly', 'essential-widgets' ),
			'yearly'     => esc_attr__( 'Yearly', 'essential-widgets' ),
		);

		$order = array(
			'ASC'  => esc_attr__( 'Ascending', 'essential-widgets' ),
			'DESC' => esc_attr__( 'Descending', 'essential-widgets' ),
		);

		$format = array(
			'custom' => esc_attr__( 'Plain', 'essential-widgets' ),
			'html'   => esc_attr__( 'List', 'essential-widgets' ),
			'option' => esc_attr__( 'Dropdown', 'essential-widgets' ),
		);

		?>
		<p>
			<label><?php esc_html_e( 'Title:', 'essential-widgets' ); ?>
				<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" placeholder="<?php echo esc_attr( $this->defaults['title'] ); ?>" />
			</label>
		</p>

		<p>
			<label><?php esc_html_e( 'Limit:', 'essential-widgets' ); ?>
				<input type="number" class="widefat" min="0" name="<?php echo esc_attr( $this->get_field_name('limit') ); ?>" value="<?php echo intval($instance['limit']); ?>" />
			</label>
		</p>

		<p>
			<label><?php esc_html_e( 'Type:', 'essential-widgets' ); ?>
				<select class="widefat" name="<?php echo esc_attr( $this->get_field_name('type') ); ?>">
					<?php foreach ( $type as $option_value => $option_label ) : ?>
						<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['type'], $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>

		<p>
			<label><?php esc_html_e( 'Post Type:', 'essential-widgets' ); ?>
				<select class="widefat" name="<?php echo esc_attr( $this->get_field_name('post_type') ); ?>">
					<?php foreach ( $post_types as $post_type ) : ?>
						<option value="<?php echo esc_attr( $post_type->name ); ?>" <?php selected( $instance['post_type'], $post_type->name ); ?>><?php echo esc_html( $post_type->labels->singular_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>

		<p>
			<label><?php esc_html_e( 'Order:', 'essential-widgets' ); ?>
				<select class="widefat" name="<?php echo esc_attr( $this->get_field_name('order') ); ?>">
					<?php foreach ( $order as $option_value => $option_label ) : ?>
						<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['order'], $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>

		<p>
			<label><?php esc_html_e( 'Display as:', 'essential-widgets' ); ?>
				<select class="widefat" name="<?php echo esc_attr( $this->get_field_name('format') ); ?>">
					<?php foreach ( $format as $option_value => $option_label ) : ?>
						<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['format'], $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>

		<p>
			<label><?php esc_html_e( 'Before:', 'essential-widgets' ); ?>
				<input type="text" class="widefat" name="<?php echo esc_attr( $this->get_field_name('before') ); ?>" value="<?php echo esc_attr( $instance['before'] ); ?>" />
			</label>
		</p>

		<p>
			<label><?php esc_html_e( 'After:', 'essential-widgets' ); ?>
				<input type="text" class="widefat" name="<?php echo esc_attr( $this->get_field_name('after') ); ?>" value="<?php echo esc_attr( $instance['after'] ); ?>" />
			</label>
		</p>

		<p>
			<label>
				<input type="checkbox" <?php checked( $instance['show_post_count'], true ); ?> name="<?php echo esc_attr( $this->get_field_name('show_post_count') ); ?>" />
				<?php esc_html_e( 'Show post count?', 'essential-widgets' ); ?>
			</label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['post_type'] = sanitize_key( $new_instance['post_type'] );

		$allowed_types   = array( 'alpha', 'daily', 'monthly', 'postbypost', 'weekly', 'yearly' );
		$allowed_orders  = array( 'ASC', 'DESC' );
		$allowed_formats = array( 'custom', 'html', 'option' );

		$instance['type']   = in_array( $new_instance['type'], $allowed_types, true ) ? $new_instance['type'] : 'monthly';
		$instance['order']  = in_array( $new_instance['order'], $allowed_orders, true ) ? $new_instance['order'] : 'DESC';
		$instance['format'] = in_array( $new_instance['format'], $allowed_formats, true ) ? $new_instance['format'] : 'html';

		$instance['limit'] = intval( $new_instance['limit'] );
		$instance['limit'] = max( 0, $instance['limit'] ); // ensure non-negative integer

		$instance['before'] = current_user_can( 'unfiltered_html' ) ? $new_instance['before'] : wp_kses_post( $new_instance['before'] );
		$instance['after']  = current_user_can( 'unfiltered_html' ) ? $new_instance['after'] : wp_kses_post( $new_instance['after'] );

		$instance['show_post_count'] = ! empty( $new_instance['show_post_count'] ) ? 1 : 0;

		return $instance;
	}

	public function widget( $args, $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults );

		echo wp_kses_post( $args['before_widget'] );

		if ( ! empty( $instance['title'] ) ) {
			echo wp_kses_post( $args['before_title'] );
			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
			echo esc_html( $title );
			echo wp_kses_post( $args['after_title'] );
		}

		echo wp_kses_post( $this->shortcode( $instance ) );

		echo wp_kses_post( $args['after_widget'] );
	}

	public function shortcode( $atts ) {
		$atts['echo'] = false;

		// Ensure limit is integer and not zero
		$atts_copy = $atts;
		$atts_copy['limit'] = intval( $atts_copy['limit'] ?? 0 );
		if ( $atts_copy['limit'] <= 0 ) {
			unset( $atts_copy['limit'] ); // prevent SQL error
		}

		$atts_copy['post_type'] = sanitize_key( $atts_copy['post_type'] ?? 'post' );

		$archives = str_replace( array("\r","\n","\t"), '', wp_get_archives( $atts_copy ) );
		$archives = str_replace( '</a>&nbsp;(', '</a> <span>(', $archives );
		$archives = str_replace( ')', ')</span>', $archives );

		$dropdown_id = esc_attr( uniqid( 'wp-block-archives-' ) );
		$ew_archives = '';

		if ( ! empty( $atts['is_block'] ) && ! empty( $atts['title'] ) ) {
			$ew_archives .= '<h2 class="ew-archive-block-title">' . esc_html( $atts['title'] ) . '</h2>';
		}

		if ( ! empty( $atts['before'] ) ) {
			$ew_archives .= wp_kses_post( $atts['before'] );
		}

		if ( 'option' === $atts['format'] ) {
			$class = 'wp-block-archives-dropdown';
			switch ( $atts['type'] ) {
				case 'yearly': $label = __( 'Select Year', 'essential-widgets' ); break;
				case 'monthly': $label = __( 'Select Month', 'essential-widgets' ); break;
				case 'daily': $label = __( 'Select Day', 'essential-widgets' ); break;
				case 'weekly': $label = __( 'Select Week', 'essential-widgets' ); break;
				default: $label = __( 'Select Post', 'essential-widgets' ); break;
			}
			$label = esc_html( $label );

			$block_content = '<label class="screen-reader-text" for="' . $dropdown_id . '">' . esc_html( $atts['title'] ) . '</label>
				<select id="' . $dropdown_id . '" name="archive-dropdown" onchange="document.location.href=this.options[this.selectedIndex].value;">
					<option value="">' . $label . '</option>' . $archives . '</select>';

			$ew_archives .= '<div class="' . esc_attr( $class ) . '">' . wp_kses_post( $block_content ) . '</div>';
		} elseif ( 'html' === $atts['format'] ) {
			$ew_archives .= '<ul class="ew-archives">' . $archives . '</ul>';
		} elseif ( 'custom' === $atts['format'] ) {
			$ew_archives .= $archives;
		}

		if ( ! empty( $atts['after'] ) ) {
			$ew_archives .= wp_kses_post( $atts['after'] );
		}

		return $ew_archives;
	}
}
endif;

if ( ! function_exists( 'ew_archives_register' ) ) :
function ew_archives_register() {
	register_widget( 'EW_Archives' );
}
add_action( 'widgets_init', 'ew_archives_register' );
endif;
