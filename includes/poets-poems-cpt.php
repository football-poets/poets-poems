<?php
/**
 * Poems Custom Post Type class.
 *
 * Handles the "Poems" Custom Post Type.
 *
 * @package Poets_Poems
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Football Poets "Poems" Custom Post Type Class
 *
 * A class that encapsulates a Custom Post Type.
 *
 * @since 0.1
 */
class Poets_Poems_CPT {

	/**
	 * Custom Post Type name.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $post_type_name = 'poem';

	/**
	 * Hierarchical taxonomy name.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $taxonomy_cat_name = 'poemcat';

	/**
	 * Free taxonomy name.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $taxonomy_tag_name = 'poemtag';

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {

	}

	/**
	 * Register hook callbacks.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// Always create Post Types.
		add_action( 'init', [ $this, 'post_type_create' ] );

		// Make sure our feedback is appropriate.
		add_filter( 'post_updated_messages', [ $this, 'post_type_messages' ] );

		// Create taxonomies.
		add_action( 'init', [ $this, 'taxonomies_create' ] );

		// Fix hierarchical taxonomy metabox display.
		add_filter( 'wp_terms_checklist_args', [ $this, 'taxonomy_fix_metabox' ], 10, 2 );

		// Add a filter to the wp-admin listing table.
		add_action( 'restrict_manage_posts', [ $this, 'taxonomy_filter_post_type' ] );

		// Filter lexia name in CommentPress Core.
		add_filter( 'commentpress_lexia_block_name', [ $this, 'filter_block_name' ], 10, 2 );

		// Filter activity items.
		add_action( 'bp_activity_before_save', [ $this, 'filter_post_activity' ], 20, 1 );
		add_filter( 'bp_activity_custom_post_type_post_action', [ $this, 'filter_post_activity_action' ], 20, 2 );
		add_action( 'bp_activity_before_save', [ $this, 'filter_comment_activity' ], 20, 1 );

		// Tweak the query for search.
		add_action( 'pre_get_posts', [ $this, 'search_query' ], 100, 1 );

		// Override template for search results.
		add_filter( 'template_include', [ $this, 'search_template' ] );

		// Add to BuddyPress member search - add it late so the sort is most effective.
		add_filter( 'bp_search_form_type_select_options', [ $this, 'search_form_options' ], 30, 1 );

		// Intercept BuddyPress search.
		add_action( 'bp_init', [ $this, 'search_redirect' ], 6 );

	}

	/**
	 * Actions to perform on plugin activation.
	 *
	 * @since 0.1
	 */
	public function activate() {

		// Pass through.
		$this->post_type_create();
		$this->taxonomies_create();

		// Go ahead and flush.
		flush_rewrite_rules();

	}

	/**
	 * Actions to perform on plugin deactivation (NOT deletion).
	 *
	 * @since 0.1
	 */
	public function deactivate() {

		// Flush rules to reset.
		flush_rewrite_rules();

	}

	// -----------------------------------------------------------------------------------

	/**
	 * Create our Custom Post Type.
	 *
	 * @since 0.1
	 */
	public function post_type_create() {

		// Only call this once.
		static $registered;

		// Bail if already done.
		if ( $registered ) {
			return;
		}

		// Define labels.
		$labels = [

			// WordPress.
			'name'                              => __( 'Poems', 'poets-poems' ),
			'singular_name'                     => __( 'Poem', 'poets-poems' ),
			'add_new'                           => __( 'Add New', 'poets-poems' ),
			'add_new_item'                      => __( 'Add New Poem', 'poets-poems' ),
			'edit_item'                         => __( 'Edit Poem', 'poets-poems' ),
			'new_item'                          => __( 'New Poem', 'poets-poems' ),
			'all_items'                         => __( 'All Poems', 'poets-poems' ),
			'view_item'                         => __( 'View Poem', 'poets-poems' ),
			'search_items'                      => __( 'Search Poems', 'poets-poems' ),
			'not_found'                         => __( 'No matching Poem found', 'poets-poems' ),
			'not_found_in_trash'                => __( 'No Poems found in Trash', 'poets-poems' ),
			'menu_name'                         => __( 'Poems', 'poets-poems' ),

			// BuddyPress.
			'bp_activity_admin_filter'          => __( 'Published a new poem', 'poets-poems' ),
			'bp_activity_front_filter'          => __( 'Poems', 'poets-poems' ),
			/* translators: 1: The user link, 2: The URL of the newly created post. */
			'bp_activity_new_post'              => __( '%1$s published a <a href="%2$s">poem</a>', 'poets-poems' ),
			/* translators: 1: The user link, 2: The URL of the newly created post, 3: The link to the site. */
			'bp_activity_new_post_ms'           => __( '%1$s published a <a href="%2$s">poem</a> on %3$s', 'poets-poems' ),
			'bp_activity_comments_admin_filter' => __( 'Comments on poems', 'poets-poems' ),
			'bp_activity_comments_front_filter' => __( 'Comments on poems', 'poets-poems' ),
			/* translators: 1: The user link, 2: The URL of the commented-on post. */
			'bp_activity_new_comment'           => __( '%1$s commented on a <a href="%2$s">poem</a>', 'poets-poems' ),
			/* translators: 1: The user link, 2: The URL of the commented-on post, 3: The link to the site. */
			'bp_activity_new_comment_ms'        => __( '%1$s commented on a <a href="%2$s">poem</a> on %3$s', 'poets-poems' ),

		];

		// Define Post Type.
		$args = [

			// Labels.
			'labels'              => $labels,

			// Defaults.
			'menu_icon'           => 'dashicons-media-text',
			'description'         => __( 'A poem post type', 'poets-poems' ),
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'show_ui'             => true,
			'show_in_nav_menus'   => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'has_archive'         => true,
			'query_var'           => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'menu_position'       => 25,
			'map_meta_cap'        => true,

			// Rewrite.
			'rewrite'             => [
				'slug'       => 'poems',
				'with_front' => false,
			],

			// Supports.
			'supports'            => [
				'title',
				'editor',
				'thumbnail',
				'comments',
				'author',
				'buddypress-activity',
			],

			// BuddyPress activity items.
			'bp_activity'         => [
				'component_id'      => 'activity',
				'action_id'         => 'new_' . $this->post_type_name,
				'comment_action_id' => 'new_' . $this->post_type_name . '_comment',
				'contexts'          => [ 'activity', 'member' ],
				'activity_comment'  => false,
				'position'          => 100,
			],

		];

		// Set up the Post Type called "Poem".
		register_post_type( $this->post_type_name, $args );

		/*
		// Maybe flush.
		flush_rewrite_rules();
		*/

		// Flag done.
		$registered = true;

	}

	/**
	 * Override messages for a custom Post Type.
	 *
	 * @since 0.1
	 *
	 * @param array $messages The existing messages.
	 * @return array $messages The modified messages.
	 */
	public function post_type_messages( $messages ) {

		// Access relevant globals.
		global $post, $post_ID;

		// Define custom messages for our custom Post Type.
		$messages[ $this->post_type_name ] = [

			// Unused - messages start at index 1.
			0  => '',

			// Item updated.
			1  => sprintf(
				/* translators: %s: The URL of the Post. */
				__( 'Poem updated. <a href="%s">View poem</a>', 'poets-poems' ),
				esc_url( get_permalink( $post_ID ) )
			),

			// Custom fields.
			2  => __( 'Custom field updated.', 'poets-poems' ),
			3  => __( 'Custom field deleted.', 'poets-poems' ),
			4  => __( 'Poem updated.', 'poets-poems' ),

			// Item restored to a revision.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			5  => isset( $_GET['revision'] ) ?

					// Revision text.
					sprintf(
						// Translators: %s: date and time of the revision.
						__( 'Poem restored to revision from %s', 'poets-poems' ),
						// phpcs:ignore WordPress.Security.NonceVerification.Recommended
						wp_post_revision_title( (int) $_GET['revision'], false )
					) :

					// No revision.
					false,

			// Item published.
			6  => sprintf(
				/* translators: %s: The URL of the Poem. */
				__( 'Poem published. <a href="%s">View poem</a>', 'poets-poems' ),
				esc_url( get_permalink( $post_ID ) )
			),

			// Item saved.
			7  => __( 'Poem saved.', 'poets-poems' ),

			// Item submitted.
			8  => sprintf(
				/* translators: %s: The preview URL. */
				__( 'Poem submitted. <a target="_blank" href="%s">Preview poem</a>', 'poets-poems' ),
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) )
			),

			// Item scheduled.
			9  => sprintf(
				/* translators: 1: The Post date, 2: The permalink. */
				__( 'Poem scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview poem</a>', 'poets-poems' ),
				/* translators: Publish box date format, see https://php.net/date */
				date_i18n( __( 'M j, Y @ G:i', 'poets-poems' ), strtotime( $post->post_date ) ),
				esc_url( get_permalink( $post_ID ) )
			),

			// Draft updated.
			10 => sprintf(
				/* translators: %s: The preview URL. */
				__( 'Poem draft updated. <a target="_blank" href="%s">Preview poem</a>', 'poets-poems' ),
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) )
			),

		];

		// --<
		return $messages;

	}

	/**
	 * Create our Custom Taxonomies.
	 *
	 * @since 0.1
	 */
	public function taxonomies_create() {

		// Only call this once.
		static $registered;

		// Bail if already done.
		if ( $registered ) {
			return;
		}

		// Define arguments.
		$arguments = [

			// Same as "category".
			'hierarchical'      => true,

			// Labels.
			'labels'            => [
				'name'              => _x( 'Poem Types', 'taxonomy general name', 'poets-poems' ),
				'singular_name'     => _x( 'Poem Type', 'taxonomy singular name', 'poets-poems' ),
				'search_items'      => __( 'Search Poem Types', 'poets-poems' ),
				'all_items'         => __( 'All Poem Types', 'poets-poems' ),
				'parent_item'       => __( 'Parent Poem Type', 'poets-poems' ),
				'parent_item_colon' => __( 'Parent Poem Type:', 'poets-poems' ),
				'edit_item'         => __( 'Edit Poem Type', 'poets-poems' ),
				'update_item'       => __( 'Update Poem Type', 'poets-poems' ),
				'add_new_item'      => __( 'Add New Poem Type', 'poets-poems' ),
				'new_item_name'     => __( 'New Poem Type Name', 'poets-poems' ),
				'menu_name'         => __( 'Poem Types', 'poets-poems' ),
			],

			// Rewrite rules.
			'rewrite'           => [
				'slug' => 'poem-types',
			],

			// Show column in wp-admin.
			'show_admin_column' => true,
			'show_ui'           => true,

		];

		// Register a hierarchical taxonomy for this CPT.
		register_taxonomy(
			$this->taxonomy_cat_name, // Taxonomy name.
			$this->post_type_name, // Post type.
			$arguments // Arguments.
		);

		// Define arguments.
		$arguments = [

			// Same as "tags".
			'hierarchical' => false,

			// Labels.
			'labels'       => [
				'name'              => _x( 'Poem Tags', 'taxonomy general name', 'poets-poems' ),
				'singular_name'     => _x( 'Poem Tag', 'taxonomy singular name', 'poets-poems' ),
				'search_items'      => __( 'Search Poem Tags', 'poets-poems' ),
				'all_items'         => __( 'All Poem Tags', 'poets-poems' ),
				'parent_item'       => __( 'Parent Poem Tag', 'poets-poems' ),
				'parent_item_colon' => __( 'Parent Poem Tag:', 'poets-poems' ),
				'edit_item'         => __( 'Edit Poem Tag', 'poets-poems' ),
				'update_item'       => __( 'Update Poem Tag', 'poets-poems' ),
				'add_new_item'      => __( 'Add New Poem Tag', 'poets-poems' ),
				'new_item_name'     => __( 'New Poem Tag', 'poets-poems' ),
				'menu_name'         => __( 'Poem Tags', 'poets-poems' ),
			],

			// Rewrite rules.
			'rewrite'      => [
				'slug' => 'poem-tags',
			],

			/*
			// Show column in wp-admin.
			'show_admin_column' => true,
			'show_ui' => true,
			*/

		];

		// Register a free taxonomy for this CPT.
		register_taxonomy(
			$this->taxonomy_tag_name, // Taxonomy name.
			$this->post_type_name, // Post type.
			$arguments // Arguments.
		);

		/*
		// Maybe flush.
		flush_rewrite_rules();
		*/

		// Flag.
		$registered = true;

	}

	/**
	 * Fix the Custom Taxonomy metabox.
	 *
	 * @see https://core.trac.wordpress.org/ticket/10982
	 *
	 * @since 0.1
	 *
	 * @param array $args The existing arguments.
	 * @param int   $post_id The WordPress Post ID.
	 */
	public function taxonomy_fix_metabox( $args, $post_id ) {

		// If rendering metabox for our taxonomy.
		if ( isset( $args['taxonomy'] ) && $args['taxonomy'] === $this->taxonomy_cat_name ) {

			// Setting 'checked_ontop' to false seems to fix this.
			$args['checked_ontop'] = false;

		}

		// --<
		return $args;

	}

	/**
	 * Add a filter for this Custom Taxonomy to the Custom Post Type listing.
	 *
	 * @since 0.1
	 */
	public function taxonomy_filter_post_type() {

		// Access current Post Type.
		global $typenow;

		// Bail if not our Post Type.
		if ( $typenow !== $this->post_type_name ) {
			return;
		}

		// Get tax object.
		$taxonomy = get_taxonomy( $this->taxonomy_cat_name );

		// Show a dropdown.
		$args = [
			/* translators: %s: The name of the taxonomy. */
			'show_option_all' => sprintf( __( 'Show All %s', 'poets-poems' ), $taxonomy->label ),
			'taxonomy'        => $this->taxonomy_cat_name,
			'name'            => $this->taxonomy_cat_name,
			'orderby'         => 'name',
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'selected'        => isset( $_GET[ $this->taxonomy_cat_name ] ) ? sanitize_text_field( wp_unslash( $_GET[ $this->taxonomy_cat_name ] ) ) : '',
			'show_count'      => true,
			'hide_empty'      => true,
			'value_field'     => 'slug',
			'hierarchical'    => 1,
		];
		wp_dropdown_categories( $args );

	}

	/**
	 * Filter the name of the "paragraph" block.
	 *
	 * The name of a block for a Poem is "verse" rather than "paragraph".
	 *
	 * @since 0.1
	 *
	 * @param str $block_name The existing name of the block.
	 * @param str $block_type The type of block - 'tag', 'line' or 'block'.
	 * @return str $block_name The modified name of the block.
	 */
	public function filter_block_name( $block_name, $block_type ) {

		// Override block identifier.
		if ( 'tag' === $block_type && get_post_type() === $this->post_type_name ) {
			$block_name = __( 'verse', 'poets-poems' );
		}

		// --<
		return $block_name;

	}

	/**
	 * Filter the Poem activity item before it gets saved.
	 *
	 * @since 0.2
	 *
	 * @param object $activity The existing activity object.
	 */
	public function filter_post_activity( $activity ) {

		// Only on new Poems.
		if ( 'new_' . $this->post_type_name !== $activity->type ) {
			return;
		}

		// Get Poem.
		$poem_id = $activity->secondary_item_id;
		$poem    = get_post( $poem_id );

		// Replace the primary link.
		$activity->primary_link = get_permalink( $poem->ID );

		$bp_excerpt_args = [
			'html'              => true,
			'filter_shortcodes' => true,
			'strip_tags'        => false,
			'remove_links'      => true,
		];

		// Reinstate formatted content.
		$activity->content = bp_create_excerpt( html_entity_decode( $poem->post_content ), 225, $bp_excerpt_args );

	}

	/**
	 * Filter the Poem activity item action string.
	 *
	 * @since 0.2
	 *
	 * @param str    $action The existing action string.
	 * @param object $activity The existing activity object.
	 * @return str $action The modified action string.
	 */
	public function filter_post_activity_action( $action, $activity ) {

		// Only on new Poems.
		if ( ( 'new_' . $this->post_type_name !== $activity->type ) ) {
			return $action;
		}

		// Get Poem ID.
		$poem_id = $activity->secondary_item_id;

		// Init author.
		$author = '';

		// Get Poets.
		$poets = poets_connections_get_poets_for_poem( $poem_id );

		// Override author link if we got any.
		if ( is_array( $poets ) && count( $poets ) > 0 ) {

			// If we got just one.
			if ( count( $poets ) === 1 ) {

				// Construct author link.
				foreach ( $poets as $poet ) {
					$link   = esc_url( get_permalink( $poet->ID ) );
					$author = '<a href="' . $link . '">' . get_the_title( $poet->ID ) . '</a>';
				}

			} else {

				// Use format of "name, name, name and name".

				// Init counter.
				$n = 1;

				// Find out how many author we have.
				$author_count = count( $poets );

				// Loop.
				foreach ( $poets as $poet ) {

					// Default to comma.
					$sep = ', ';

					// Use ampersand if we're on the penultimate.
					if ( ( $author_count - 1 ) === $n ) {
						$sep = ' &amp; ';
					}

					// If we're on the last, don't add.
					if ( $n === $author_count ) {
						$sep = '';
					}

					// Construct link to Poet.
					$link    = esc_url( get_permalink( $poet->ID ) );
					$author .= '<a href="' . $link . '">' . get_the_title( $poet->ID ) . '</a>';

					// Add separator.
					$author .= $sep;

					// Increment.
					$n++;

				}

			}

			// Construct link to site.
			$blog_url  = get_home_url( $activity->item_id );
			$blog_name = get_blog_option( $activity->item_id, 'blogname' );
			$blog_link = '<a href="' . esc_url( $blog_url ) . '">' . $blog_name . '</a>';

			// Construct new action string.
			$action = sprintf(
				/* translators: 1: The name of the author, 2: The URL of the Post, 3: The name of the site. */
				__( '%1$s published a <a href="%2$s">poem</a> on the site %3$s', 'poets-poems' ),
				$author,
				get_permalink( $poem_id ),
				$blog_link
			);

		}

		// --<
		return $action;

	}

	/**
	 * Filter the Poem Comment activity item before it gets saved.
	 *
	 * @since 0.3.1
	 *
	 * @param object $activity The existing activity object.
	 */
	public function filter_comment_activity( $activity ) {

		// Only on new Poem Comments.
		if ( 'new_' . $this->post_type_name . '_comment' !== $activity->type ) {
			return;
		}

		// Get the Comment.
		$comment_id = $activity->secondary_item_id;
		$comment    = get_comment( $comment_id );

		$bp_excerpt_args = [
			'html'              => true,
			'filter_shortcodes' => true,
			'strip_tags'        => false,
			'remove_links'      => true,
		];

		// Reinstate formatted content.
		$activity->content = bp_create_excerpt( html_entity_decode( $comment->comment_content ), 225, $bp_excerpt_args );

	}

	/**
	 * Manipulate the "Poets" search results.
	 *
	 * @since 1.0
	 *
	 * @param object $query The current query passed by reference.
	 */
	public function search_query( $query ) {

		// Bail for the usual conditions.
		if ( is_admin() || ! $query->is_search ) {
			return;
		}

		// Make sure only Poets are queried.
		if ( ! isset( $query->query['post_type'] ) ) {
			return;
		}
		if ( $query->query['post_type'] !== $this->post_type_name ) {
			return;
		}

		// Set Post Type.
		$query->set( 'post_type', $this->post_type_name );

	}

	/**
	 * Return searchs back to the "Poets" archive.
	 *
	 * @since 1.0
	 *
	 * @param str $template The template.
	 * @return str $template The modified template.
	 */
	public function search_template( $template ) {

		// Access query.
		global $wp_query;

		// Bail if not search.
		if ( ! $wp_query->is_search ) {
			return $template;
		}

		// Bail if not our Post Type.
		if ( ! isset( $wp_query->query['post_type'] ) ) {
			return $template;
		}
		if ( $wp_query->query['post_type'] !== $this->post_type_name ) {
			return $template;
		}

		// Okay, override template.
		return locate_template( 'archive-' . $this->post_type_name . '.php' );

	}

	/**
	 * Filters the options available in the search dropdown.
	 *
	 * @since 0.2
	 *
	 * @param array $options Existing array of options to add to select field.
	 * @return array $options Modified array of options to add to select field.
	 */
	public function search_form_options( $options ) {

		// Define option text.
		$text = __( 'Poems', 'poets-poems' );

		// Add Poems.
		if ( ! is_array( $options ) ) {
			$options = [ $this->post_type_name => $text ];
		} else {
			$options = [ $this->post_type_name => $text ] + $options;
		}

		// --<
		return $options;

	}

	/**
	 * Intercept BuddyPress search queries and redirect.
	 *
	 * @since 0.2
	 */
	public function search_redirect() {

		if ( ! bp_is_current_component( bp_get_search_slug() ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['search-terms'] ) ) {
			bp_core_redirect( bp_get_root_domain() );
			return;
		}

		// Get form values.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$search_terms = sanitize_text_field( wp_unslash( $_POST['search-terms'] ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$search_which = ! empty( $_POST['search-which'] ) ? sanitize_text_field( wp_unslash( $_POST['search-which'] ) ) : '';

		// Is it ours?
		if ( $search_which !== $this->post_type_name ) {
			return;
		}

		// We haven't registered the CPT yet, so this is hard-coded.
		$page = trailingslashit( home_url( '/poems' ) );

		// Pass terms through.
		$query_string = '?s=' . rawurlencode( $search_terms ) . '&post_type=' . $this->post_type_name;

		// Redirect to archive.
		bp_core_redirect( $page . $query_string );

	}

}
