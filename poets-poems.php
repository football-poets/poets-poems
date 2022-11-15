<?php
/**
 * Plugin Name: Football Poets Poems
 * Plugin URI: http://footballpoets.org
 * Description: Creates a "Poems" Custom Post Type for the Football Poets site.
 * Author: Christian Wach
 * Version: 0.2.5
 * Author URI: https://haystack.co.uk
 * Text Domain: poets-poems
 * Domain Path: /languages
 *
 * @package Poets_Poems
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Set our version here.
define( 'POETS_POEMS_VERSION', '0.2.5' );

// Store reference to this file.
if ( ! defined( 'POETS_POEMS_FILE' ) ) {
	define( 'POETS_POEMS_FILE', __FILE__ );
}

// Store URL to this plugin's directory.
if ( ! defined( 'POETS_POEMS_URL' ) ) {
	define( 'POETS_POEMS_URL', plugin_dir_url( POETS_POEMS_FILE ) );
}

// Store PATH to this plugin's directory.
if ( ! defined( 'POETS_POEMS_PATH' ) ) {
	define( 'POETS_POEMS_PATH', plugin_dir_path( POETS_POEMS_FILE ) );
}

/**
 * Football Poets "Poems" Plugin Class.
 *
 * A class that encapsulates plugin functionality.
 *
 * @since 0.1
 */
class Poets_Poems {

	/**
	 * Custom Post Type object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $cpt The Custom Post Type object.
	 */
	public $cpt;

	/**
	 * Metaboxes object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $metaboxes The Metaboxes object.
	 */
	public $metaboxes;

	/**
	 * Switcher object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $metaboxes The Switcher object.
	 */
	public $switcher;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Include files.
		$this->include_files();

		// Setup globals.
		$this->setup_globals();

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Include files.
	 *
	 * @since 0.1
	 */
	public function include_files() {

		// Include CPT class.
		include_once POETS_POEMS_PATH . 'includes/poets-poems-cpt.php';

		// Include Metaboxes class.
		include_once POETS_POEMS_PATH . 'includes/poets-poems-metaboxes.php';

		// Include Theme functions.
		include_once POETS_POEMS_PATH . 'includes/poets-poems-functions.php';

		// Include Switcher class.
		include_once POETS_POEMS_PATH . 'includes/poets-poems-switcher.php';

	}

	/**
	 * Set up objects.
	 *
	 * @since 0.1
	 */
	public function setup_globals() {

		// Init CPT object.
		$this->cpt = new Poets_Poems_CPT();

		// Init Metaboxes object.
		$this->metaboxes = new Poets_Poems_Metaboxes();

		// Init Switcher object.
		$this->switcher = new Poets_Poems_Switcher( $this );

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// Use translation.
		add_action( 'plugins_loaded', [ $this, 'translation' ] );

		// Hooks that always need to be present.
		$this->cpt->register_hooks();
		$this->metaboxes->register_hooks();
		$this->switcher->register_hooks();

		// Add widgets.
		add_action( 'widgets_init', [ $this, 'register_widgets' ] );

	}

	/**
	 * Load translation if present.
	 *
	 * @since 0.1
	 */
	public function translation() {

		// Allow translations to be added.
		// phpcs:ignore WordPress.WP.DeprecatedParameters.Load_plugin_textdomainParam2Found
		load_plugin_textdomain(
			'poets-poems', // Unique name.
			false, // Deprecated argument.
			dirname( plugin_basename( POETS_POEMS_FILE ) ) . '/languages/'
		);

	}

	/**
	 * Perform plugin activation tasks.
	 *
	 * @since 0.1
	 */
	public function activate() {

		// Pass through.
		$this->cpt->activate();

	}

	/**
	 * Perform plugin deactivation tasks.
	 *
	 * @since 0.1
	 */
	public function deactivate() {

		// Pass through.
		$this->cpt->deactivate();

	}

	/**
	 * Register widgets for this plugin.
	 *
	 * @since 0.1
	 */
	public function register_widgets() {

		// Include widgets.
		require_once POETS_POEMS_PATH . 'widgets/poets-poems-widget-latest.php';
		require_once POETS_POEMS_PATH . 'widgets/poets-poems-widget-featured.php';
		require_once POETS_POEMS_PATH . 'widgets/poets-poems-widget-total.php';

	}

}

/**
 * Plugin reference getter.
 *
 * @since 0.1
 *
 * @return Poets_Poems $poets_poems The plugin object.
 */
function poets_poems() {
	static $poets_poems;
	if ( ! isset( $poets_poems ) ) {
		$poets_poems = new Poets_Poems();
	}
	return $poets_poems;
}

// Instantiate the class.
poets_poems();


// Activation.
register_activation_hook( __FILE__, [ poets_poems(), 'activate' ] );

// Deactivation.
register_deactivation_hook( __FILE__, [ poets_poems(), 'deactivate' ] );
