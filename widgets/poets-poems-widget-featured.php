<?php
/**
 * "Featured Poem" Widget class.
 *
 * Handles functionality for the "Featured Poem" Widget.
 *
 * @package Poets_Poems
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Football Poets "Featured Poem" Widget Class.
 *
 * @since 0.2
 */
class Poets_Poems_Widget_Featured extends WP_Widget {

	/**
	 * Constructor registers widget with WordPress.
	 *
	 * @since 0.2
	 */
	public function __construct() {

		// Define args.
		$args = [
			'description' => __( 'Use this widget to show the poem in the "Featured" category.', 'poets-poems' ),
		];

		// Init parent.
		parent::__construct(
			'poets_poems_featured', // Base ID.
			__( 'Featured Poem', 'poets-poems' ), // Name.
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
			'post_type'      => $poets_poems->cpt->post_type_name,
			'post_status'    => 'publish',
			'posts_per_page' => '1',
			'orderby'        => 'date',
			'order'          => 'DESC',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'tax_query'      => [
				[
					'taxonomy' => $poets_poems->cpt->taxonomy_cat_name,
					'field'    => 'slug',
					'terms'    => 'featured',
				],
			],
		];

		// Do query.
		$poems = new WP_Query( $query_args );

		// Get Poet as well.
		p2p_type( 'poets_to_poems' )->each_connected( $poems, [], 'poets' );

		// Did we get any results?
		if ( $poems->have_posts() ) :

			// Get widget title.
			$title = apply_filters( 'widget_title', $instance['title'] );

			// Add switcher.
			ob_start();
			$poets_poems->switcher->switcher_button();
			$title .= ob_get_contents();
			ob_end_clean();

			// Show before.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['before_widget'];

			// If we have a title, show it.
			if ( ! empty( $title ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $args['before_title'] . $title . $args['after_title'];
			}

			while ( $poems->have_posts() ) :
				$poems->the_post();

				?>

				<div id="post-<?php the_ID(); ?>" class="post">
					<div class="post-inner">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title( '<span class="entry-title">', '</span>' ); ?></a></h3>
						<div class="poem-meta">
							<?php

							global $post;
							if ( ! empty( $post->poets ) ) {
								foreach ( $post->poets as $poet ) {
									$link = '<a href="' . esc_url( get_permalink( $poet ) ) . '">' . get_the_title( $poet ) . '</a>';
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo '<cite class="fn post-author">' . $link . '</cite>';
								}
							}

							?>
							<span class="post-date"><?php echo esc_html( get_the_date( __( 'jS F Y', 'poets-poems' ) ) ); ?></span>
						</div><!-- /.poem-meta -->

						<div class="poem-content">
							<?php the_content(); ?>
						</div><!-- /.poem-content -->

						<p class="search_meta"><?php comments_popup_link( __( 'Be the first to leave a comment &#187;', 'poets-poems' ), __( '1 Comment &#187;', 'poets-poems' ), __( '% Comments &#187;', 'poets-poems' ) ); ?></p>

						<?php /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
						<?php echo $this->notes(); ?>
					</div>
				</div>

				<?php

			endwhile;

			// Show after.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['after_widget'];

			// Reset the Post globals as this query will have stomped on it.
			wp_reset_postdata();

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
			$title = __( 'Featured Poem', 'poets-poems' );
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

	/**
	 * Get notes for a Poem.
	 *
	 * @since 0.2.2
	 *
	 * @return str $markup The markup representing the Poem notes.
	 */
	public function notes() {

		// Init return.
		$markup = '';

		// Notes key.
		$key = '_poets_poems_content_notes';

		// Get value if the custom field has one.
		$notes    = '';
		$existing = get_post_meta( get_the_ID(), $key, true );
		if ( false !== $existing ) {
			$notes = get_post_meta( get_the_ID(), $key, true );
		}

		// Maybe show content.
		if ( ! empty( $notes ) ) {
			$markup .= '<div class="poem_meta">' . "\n";
			$markup .= '<h4>' . __( 'Notes', 'poets-poems' ) . '</h4>' . "\n";
			$markup .= '<div class="poem_content_notes">' . "\n";
			$markup .= apply_filters( 'commentpress_poets_richtext_content', $notes ) . "\n";
			$markup .= '</div>' . "\n";
			$markup .= '</div>' . "\n";
		}

		// --<
		return $markup;

	}

}
