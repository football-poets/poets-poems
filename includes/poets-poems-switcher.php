<?php
/**
 * Featured Poem Switcher class.
 *
 * Handles functionality for the Featured Poem Switcher.
 *
 * @package Poets_Poems
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Football Poets "Poems" Switcher Class
 *
 * A class that encapsulates functionality for the Featured Poem Switcher, which
 * is enabled via the Insert/Edit dialog separated from TinyMCE.
 *
 * @package Poets_Poems
 */
class Poets_Poems_Switcher {

	/**
	 * Plugin object.
	 *
	 * @since 0.2.2
	 * @access public
	 * @var Poets_Poems
	 */
	public $plugin;

	/**
	 * Form nonce action.
	 *
	 * @since 0.2.5
	 * @access private
	 * @var string
	 */
	private $nonce_action = 'poets_poems_switcher_nonce';

	/**
	 * Constructor.
	 *
	 * @since 0.2.1
	 *
	 * @param Poets_Poems $parent The plugin object.
	 */
	public function __construct( $parent ) {

		// Store reference.
		$this->plugin = $parent;

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 0.2.1
	 */
	public function register_hooks() {

		// Filter the insert/edit link modal.
		add_filter( 'wp_link_query_args', [ $this, 'switcher_query_post_type' ] );

		// Filter the returned value.
		add_filter( 'wp_link_query', [ $this, 'switcher_query_results' ], 10, 2 );

		// Save new Featured Poem.
		add_action( 'wp_ajax_set_featured_poem', [ $this, 'switcher_set_featured_poem' ] );

		// Filter the content of the homepage.
		add_filter( 'the_content', [ $this, 'featured_poem_teaser' ] );

	}

	/**
	 * Add a switcher button for the Featured Poem.
	 *
	 * @see _WP_Editors::wp_link_dialog()
	 *
	 * @since 0.2.1
	 */
	public function switcher_button() {

		// Bail if not at least editor.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		// Add a button.
		echo ' <a href="#" class="poem-switcher" style="float: right; text-decoration: none;">' . esc_html__( 'Choose New', 'poets-poems' ) . '</a>';

		// Add a hidden text field.
		echo '<input type="text" style="display: none !important;" id="poem-switcher-field">';

		// Need the class file.
		require_once ABSPATH . 'wp-includes/class-wp-editor.php';
		add_action(
			'wp_footer',
			function() {
				_WP_Editors::wp_link_dialog();
			}
		);

		// Enqueue script and style.
		wp_enqueue_script( 'wplink' );
		wp_enqueue_style( 'editor-buttons' );

		// Enqueue custom javascript.
		wp_enqueue_script(
			'poets-poems-switcher-js',
			POETS_POEMS_URL . 'assets/js/poets-poems-switcher.js',
			[ 'wplink' ],
			POETS_POEMS_VERSION,
			true // In footer.
		);

		// Init localisation.
		$localisation = [
			'title'  => __( 'Choose Featured Poem', 'poets-poems' ),
			'button' => __( 'Set Featured Poem', 'poets-poems' ),
		];

		// Init settings.
		$settings = [
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( $this->nonce_action ),
			'loading'    => POETS_POEMS_URL . 'assets/images/loading.gif',
		];

		// Localisation array.
		$vars = [
			'localisation' => $localisation,
			'settings'     => $settings,
		];

		// Localise the WordPress way.
		wp_localize_script(
			'poets-poems-switcher-js',
			'Poets_Poems_Settings',
			$vars
		);

	}

	/**
	 * Filter the Post Types in the Poem switcher.
	 *
	 * @since 0.2.1
	 *
	 * @param array $query The existing WP_Query params.
	 * @return array $query The modified WP_Query params.
	 */
	public function switcher_query_post_type( $query ) {

		// Only on homepage.
		$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
		if ( trailingslashit( site_url() ) !== $referrer ) {
			return $query;
		}

		// Show only Poems.
		$query['post_type'] = [ $this->plugin->cpt->post_type_name ];

		// --<
		return $query;

	}

	/**
	 * Filter the result of the link modal selection.
	 *
	 * @since 0.2.1
	 *
	 * @param array $results The existing WP_Query params.
	 * @param array $query The existing WP_Query params.
	 * @return array $query The modified WP_Query params.
	 */
	public function switcher_query_results( $results, $query ) {

		// Only on homepage.
		$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
		if ( trailingslashit( site_url() ) !== $referrer ) {
			return $results;
		}

		// Alter the data.
		foreach ( $results as &$result ) {
			$result['permalink'] = $result['ID'];
		}

		// --<
		return $results;

	}

	/**
	 * Set featured Poem as a result of the link modal selection.
	 *
	 * @since 0.2.1
	 */
	public function switcher_set_featured_poem() {

		// Init data.
		$data = [
			'success' => 'false',
		];

		// Since this is an AJAX request, check security.
		$result = check_ajax_referer( $this->nonce_action, false, false );
		if ( false === $result ) {
			wp_send_json( $data );
		}

		// Only allow requests from homepage.
		$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
		if ( trailingslashit( site_url() ) !== $referrer ) {
			wp_send_json( $data );
		}

		// Bail if not at least editor.
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json( $data );
		}

		// Get Post ID.
		$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : 0;

		// Sanity checks.
		if ( ! is_numeric( $post_id ) ) {
			wp_send_json( $data );
		}
		if ( 0 === (int) $post_id ) {
			wp_send_json( $data );
		}

		// Cast as integer.
		$post_id = (int) $post_id;

		// Remove all featured Poems.
		$this->featured_poem_unset();

		// Assign new Poem to featured category.
		wp_set_object_terms( $post_id, 'featured', $this->plugin->cpt->taxonomy_cat_name, true );

		// Buffer the markup.
		ob_start();
		$this->get_poem_markup();
		$content = ob_get_contents();
		ob_end_clean();

		// Add to data.
		$data['markup'] = $content;

		// Add teaser to data.
		$data['teaser'] = $this->get_teaser_markup();

		// Init data.
		$data['success'] = 'true';

		// Send data to browser.
		wp_send_json( $data );

	}

	/**
	 * Unset current featured Poem.
	 *
	 * @since 0.2.1
	 */
	private function featured_poem_unset() {

		// Define args for query.
		$query_args = [
			'post_type'      => $this->plugin->cpt->post_type_name,
			'post_status'    => 'publish',
			'posts_per_page' => '1',
			'orderby'        => 'date',
			'order'          => 'DESC',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'tax_query'      => [
				[
					'taxonomy' => $this->plugin->cpt->taxonomy_cat_name,
					'field'    => 'slug',
					'terms'    => 'featured',
				],
			],
		];

		// Do query.
		$poems = new WP_Query( $query_args );

		// Did we get any results?
		if ( $poems->have_posts() ) {

			// Remove from taxonomy.
			while ( $poems->have_posts() ) :
				$poems->the_post();
				wp_remove_object_terms( get_the_ID(), 'featured', $this->plugin->cpt->taxonomy_cat_name );
			endwhile;

		}

		// Reset the Post globals as this query will have stomped on it.
		wp_reset_postdata();

	}

	/**
	 * Construct Poem markup.
	 *
	 * @since 0.2.1
	 */
	private function get_poem_markup() {

		// Define args for query.
		$query_args = [
			'post_type'      => $this->plugin->cpt->post_type_name,
			'post_status'    => 'publish',
			'posts_per_page' => '1',
			'orderby'        => 'date',
			'order'          => 'DESC',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'tax_query'      => [
				[
					'taxonomy' => $this->plugin->cpt->taxonomy_cat_name,
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

			while ( $poems->have_posts() ) :
				$poems->the_post();

				?>

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

				<?php

			endwhile;

			// Reset the Post globals as this query will have stomped on it.
			wp_reset_postdata();

		endif;

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
			$notes = $existing;
		}

		// Maybe show content.
		if ( ! empty( $notes ) ) {
			$markup .= '<div class="poem_meta">' . "\n";
			$markup .= '<h4>' . esc_html__( 'Notes', 'poets-poems' ) . '</h4>' . "\n";
			$markup .= '<div class="poem_content_notes">' . "\n";
			$markup .= apply_filters( 'commentpress_poets_richtext_content', $notes ) . "\n";
			$markup .= '</div>' . "\n";
			$markup .= '</div>' . "\n";
		}

		// --<
		return $markup;

	}

	/**
	 * Add a pointer to the Featured Poem to the homepage content.
	 *
	 * @since 0.2.1
	 *
	 * @param str $content The existing Post content.
	 * @return str $content The modified Post content.
	 */
	public function featured_poem_teaser( $content ) {

		// Bail if not home page.
		if ( ! is_front_page() ) {
			return $content;
		}
		if ( ! in_the_loop() ) {
			return $content;
		}

		// Get the ID of the Front Page.
		$front_page_id = get_option( 'page_on_front' );
		if ( empty( $front_page_id ) ) {
			return $content;
		}

		// Bail if this is not the Front Page.
		if ( (int) get_the_ID() !== (int) $front_page_id ) {
			return $content;
		}

		// Bail if the token isn't present.
		if ( false === strpos( $content, '<!--featured-->' ) ) {
			return $content;
		}

		// Get Featured Poem teaser.
		$teaser = $this->get_teaser_markup();

		// Bail if there isn't one.
		if ( false === $teaser ) {
			return $content;
		}

		// Wrap in span.
		$teaser = '<span class="featured-poem-teaser">' . $teaser . '</span>';

		// Replace into content.
		$content = str_replace( '<!--featured-->', $teaser, $content );

		// --<
		return $content;

	}

	/**
	 * Construct teaser markup.
	 *
	 * @since 0.2.1
	 *
	 * @return str|bool $teaser The teaser content - or false on failure.
	 */
	private function get_teaser_markup() {

		// Get Featured Poem.
		$poem = poets_poems_get_featured();

		// Bail if there isn't one.
		if ( false === $poem ) {
			return false;
		}

		// Get Poet for this Poem.
		$poet = poets_poems_get_poet( $poem );

		// Bail if there isn't one.
		if ( false === $poet ) {
			return false;
		}

		// Construct details.
		$details = sprintf(
			/* translators: 1: The title of the Poem, 2: The name of the Poet. */
			__( 'Featured Poem &amp; Image: %1$s by %2$s', 'poets-poems' ),
			esc_html( $poem->post_title ),
			esc_html( $poet->post_title )
		);

		// Construct teaser.
		$teaser = ' <strong><a href="#poets_poems_featured-2">' . $details . '</a></strong>.';

		// --<
		return $teaser;

	}

}
