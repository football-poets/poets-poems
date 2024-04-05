<?php
/**
 * Football Poets "Poems" Theme functions.
 *
 * Global scope functions that are available to the theme can be found here.
 *
 * @package Poets_Poems
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Get Featured Poem.
 *
 * @since 0.2.3
 *
 * @return WP_Post|bool The Featured Poem object or false if none found.
 */
function poets_poems_get_featured() {

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

	// How did we do?
	$poem = false;
	if ( isset( $poems->post ) ) {
		$poem = $poems->post;
	}

	// Prevent weirdness.
	wp_reset_postdata();

	// --<
	return $poem;

}

/**
 * Get the Poet for a given Poem.
 *
 * @since 0.2.3
 *
 * @param WP_Post $poem The Poem object.
 * @return WP_Post|bool The Poet object or false if none found.
 */
function poets_poems_get_poet( $poem ) {

	// Define query args.
	$query_args = [
		'connected_type'  => 'poets_to_poems',
		'connected_items' => $poem,
		'nopaging'        => true,
		'no_found_rows'   => true,
	];

	// The query.
	$query = new WP_Query( $query_args );

	// How did we do?
	$poet = false;
	if ( isset( $query->post ) ) {
		$poet = $query->post;
	}

	// Prevent weirdness.
	wp_reset_postdata();

	// --<
	return $poet;

}

/**
 * Get most recent Poem.
 *
 * @since 0.1.1
 *
 * @return WP_Post|bool The current Poem object or false if none found.
 */
function poets_poems_get_latest() {

	// Access plugin.
	$poets_poems = poets_poems();

	// Define args.
	$args = [
		'numberposts' => 1,
		'post_type'   => $poets_poems->cpt->post_type_name,
	];

	// Do query.
	$latest = get_posts( $args );

	// How did we do?
	if ( $latest ) {
		return array_pop( $latest );
	}

	// Fallback.
	return false;

}
