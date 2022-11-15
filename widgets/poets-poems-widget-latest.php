<?php
/**
 * "Latest Poems" Widget class.
 *
 * Handles functionality for the "Latest Poems" Widget.
 *
 * @package Poets_Poems
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Football Poets "Latest Poems" Widget Class.
 *
 * @since 0.2
 */
class Poets_Poems_Widget_Latest extends WP_Widget {

	/**
	 * Constructor registers widget with WordPress.
	 *
	 * @since 0.2
	 */
	public function __construct() {

		// Define args.
		$args = [
			'description' => __( 'Use this widget to show a list of the latest poems.', 'poets-poems' ),
		];

		// Init parent.
		parent::__construct(
			'poets_poems_latest', // Base ID.
			__( 'Latest Poems', 'poets-poems' ), // Name.
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

		// Define args for query.
		$query_args = [
			'post_type' => $poets_poems->cpt->post_type_name,
			'post_status' => 'publish',
			'posts_per_page' => '10',
			'orderby' => 'date',
			'order' => 'DESC',
		];

		// Do query.
		$poems = new WP_Query( $query_args );

		// Get Poet as well.
		p2p_type( 'poets_to_poems' )->each_connected( $poems, [], 'poets' );

		// Did we get any results?
		if ( $poems->have_posts() ) :

			// Get widget title.
			$title = apply_filters( 'widget_title', $instance['title'] );

			// Show before.
			echo $args['before_widget'];

			// If we have a title, show it.
			if ( ! empty( $title ) ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

			?>
			<ol>
			<?php

			while ( $poems->have_posts() ) :
				$poems->the_post();
				?>

				<li id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<a href="<?php the_permalink(); ?>"><?php the_title( '<span class="entry-title">', '</span>' ); ?><br>
					<?php

					global $post;
					if ( ! empty( $post->poets ) ) {
						foreach ( $post->poets as $poet ) {
							echo '<span class="post-author">' . get_the_title( $poet ) . '</span><br>';
						}
					}

					?>
					<span class="post-date"><?php echo esc_html( get_the_date( __( 'jS F Y', 'poets-poems' ) ) ); ?></span></a>
				</li><!-- #post-## -->

			<?php endwhile; ?>
			<li class="poets-poems-archive-link"><a href="<?php echo get_post_type_archive_link( 'poem' ); ?>"><span class="entry-title"><?php esc_html_e( 'Read all recent poems', 'poets-poems' ); ?> &rarr;</span></a></li>
			</ol>
			<?php

			// Show after.
			echo $args['after_widget'];

			// Reset the post globals as this query will have stomped on it.
			wp_reset_postdata();

		// End check for Poems.
		endif;

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
			$title = __( 'Latest Poems', 'poets-poems' );
		}

		?>

		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'poets-poems' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
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

// Register this widget.
register_widget( 'Poets_Poems_Widget_Latest' );
