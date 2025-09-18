<?php
/**
 * Football Poets Poems
 *
 * Plugin Name: Football Poets Poems
 * Description: Creates a "Poems" Custom Post Type for the Football Poets site.
 * Plugin URI:  https://github.com/football-poets/poets-poems
 * Version:     0.3.1
 * Author:      Christian Wach
 * Author URI:  https://haystack.co.uk
 * Text Domain: poets-poems
 * Domain Path: /languages
 *
 * @package Poets_Poems
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Set our version here.
define( 'POETS_POEMS_VERSION', '0.3.1' );

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
	 * @var Poets_Poems_CPT
	 */
	public $cpt;

	/**
	 * Metaboxes object.
	 *
	 * @since 0.1
	 * @access public
	 * @var Poets_Poems_Metaboxes
	 */
	public $metaboxes;

	/**
	 * Switcher object.
	 *
	 * @since 0.1
	 * @access public
	 * @var Poets_Poems_Switcher
	 */
	public $switcher;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Initialise when all plugins are loaded.
		add_action( 'plugins_loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this plugin.
	 *
	 * @since 0.3.1
	 */
	public function initialise() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && true === $done ) {
			return;
		}

		// Bootstrap plugin.
		$this->include_files();
		$this->setup_globals();
		$this->register_hooks();

		/**
		 * Broadcast that this plugin is now loaded.
		 *
		 * @since 0.3.1
		 */
		do_action( 'poets_poems/loaded' );

		// We're done.
		$done = true;

	}

	/**
	 * Include files.
	 *
	 * @since 0.1
	 */
	private function include_files() {

		// Include plugin files.
		include POETS_POEMS_PATH . 'includes/poets-poems-cpt.php';
		include POETS_POEMS_PATH . 'includes/poets-poems-metaboxes.php';
		include POETS_POEMS_PATH . 'includes/poets-poems-functions.php';
		include POETS_POEMS_PATH . 'includes/poets-poems-switcher.php';

	}

	/**
	 * Set up objects.
	 *
	 * @since 0.1
	 */
	private function setup_globals() {

		// Init objects.
		$this->cpt       = new Poets_Poems_CPT();
		$this->metaboxes = new Poets_Poems_Metaboxes();
		$this->switcher  = new Poets_Poems_Switcher( $this );

	}

	/**
	 * Register hook callbacks.
	 *
	 * @since 0.1
	 */
	private function register_hooks() {

		// Use translation.
		add_action( 'plugins_loaded', [ $this, 'translation' ] );

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

		// Only do this once.
		static $done;
		if ( isset( $done ) && true === $done ) {
			return;
		}

		// Include widget class files.
		require POETS_POEMS_PATH . 'widgets/poets-poems-widget-latest.php';
		require POETS_POEMS_PATH . 'widgets/poets-poems-widget-featured.php';
		require POETS_POEMS_PATH . 'widgets/poets-poems-widget-total.php';

		// Register widgets.
		register_widget( 'Poets_Poems_Widget_Latest' );
		register_widget( 'Poets_Poems_Widget_Featured' );
		register_widget( 'Poets_Poems_Widget_Total' );

		// We're done.
		$done = true;

	}

}

/**
 * Plugin reference getter.
 *
 * @since 0.1
 *
 * @return Poets_Poems $plugin The plugin object.
 */
function poets_poems() {

	// Store instance in static variable.
	static $plugin = false;

	// Maybe return instance.
	if ( false === $plugin ) {
		$plugin = new Poets_Poems();
	}

	// --<
	return $plugin;

}

// Instantiate the class.
poets_poems();


// Activation.
register_activation_hook( __FILE__, [ poets_poems(), 'activate' ] );

// Deactivation.
register_deactivation_hook( __FILE__, [ poets_poems(), 'deactivate' ] );
