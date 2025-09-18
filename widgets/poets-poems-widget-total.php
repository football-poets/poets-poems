<?php
/**
 * "Total Poems" Widget class.
 *
 * Handles functionality for the "Total Poems" Widget.
 *
 * @package Poets_Poems
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Football Poets "Total Poems" Widget Class.
 *
 * @since 0.2
 */
class Poets_Poems_Widget_Total extends WP_Widget {

	/**
	 * Constructor registers widget with WordPress.
	 *
	 * @since 0.2
	 */
	public function __construct() {

		// Define args.
		$args = [
			'description' => __( 'Use this widget to show the total number of poems.', 'poets-poems' ),
		];

		// Init parent.
		parent::__construct(
			'poets_poems_total', // Base ID.
			__( 'Total Poems', 'poets-poems' ), // Name.
			$args
		);

	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @since 0.2
	 *
	 * @param array $args An array of standard parameters for widgets in this theme.
	 * @param array $instance An array of settings for this widget instance.
	 */
	public function widget( $args, $instance ) {

		// Access plugin.
		$poets_poems = poets_poems();

		// Get widget title.
		$title = apply_filters( 'widget_title', $instance['title'] );

		// Show before.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_widget'];

		// If we have a title, show it.
		if ( ! empty( $title ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Define args for query.
		$query_args = [
			'post_type'      => $poets_poems->cpt->post_type_name,
			'post_status'    => 'publish',
			'posts_per_page' => '10',
		];

		// Do query.
		$poems = new WP_Query( $query_args );

		?>
		<p>
			<?php

			echo sprintf(
				/* translators: %s: The number of Poems wrapped in a <strong> tag. */
				esc_html__( 'There are currently %s poems on this site.', 'poets-poems' ),
				'<strong>' . esc_html( $poems->found_posts ) . '</strong>'
			);

			?>
		</p>
		<p>
			<?php

			echo sprintf(
				/* translators: 1: The opening anchor tag, 2: The closing anchor tag. */
				esc_html__( 'Read our %1$sFeatured Poem%2$s', 'poets-poems' ),
				'<a class="button" style="text-transform: uppercase" href="#poets_poems_featured-2">',
				'</a>'
			);

			?>
		</p>
		<?php

		// Show after.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget'];

		// Reset the Post globals as this query will have stomped on it.
		wp_reset_postdata();

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @since 0.2
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		// Get title.
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'Total Poems', 'poets-poems' );
		}

		?>

		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'poets-poems' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<?php

	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @since 0.2
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array $instance Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		// Never lose a value.
		$instance = wp_parse_args( $new_instance, $old_instance );

		// --<
		return $instance;

	}

}
