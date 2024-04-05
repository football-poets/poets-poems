/**
 * Poets Poems "Switcher" Javascript.
 *
 * Implements functionality for the widget's "Switcher" button.
 *
 * @package Poets_Poems
 */

/**
 * Create Poets Poems Switcher object.
 *
 * This works as a "namespace" of sorts, allowing us to hang properties, methods
 * and "sub-namespaces" from it.
 *
 * @since 0.2.1
 */
var Poets_Poems_Switcher = Poets_Poems_Switcher || {};

/**
 * Pass the jQuery shortcut in.
 *
 * @since 0.2.1
 *
 * @param {Object} $ The jQuery object.
 */
( function( $ ) {

	/**
	 * Create Settings Object.
	 *
	 * @since 0.2.1
	 */
	Poets_Poems_Switcher.settings = new function() {

		// prevent reference collisions
		var me = this;

		/**
		 * Initialise Settings.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.2.1
		 */
		this.init = function() {

			// Init localisation.
			me.init_localisation();

			// Init settings.
			me.init_settings();

		};

		/**
		 * Do setup when jQuery reports that the DOM is ready.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.2.1
		 */
		this.dom_ready = function() {

		};

		// Init localisation array.
		me.localisation = [];

		/**
		 * Init localisation from settings object.
		 *
		 * @since 0.2.1
		 */
		this.init_localisation = function() {
			if ( 'undefined' !== typeof Poets_Poems_Settings ) {
				me.localisation = Poets_Poems_Settings.localisation;
			}
		};

		/**
		 * Getter for localisation.
		 *
		 * @since 0.2.1
		 *
		 * @param {String} The identifier for the desired localisation string.
		 * @return {String} The localised string.
		 */
		this.get_localisation = function( identifier ) {
			return me.localisation[identifier];
		};

		// Init settings array.
		me.settings = [];

		/**
		 * Init settings from settings object.
		 *
		 * @since 0.2.1
		 */
		this.init_settings = function() {
			if ( 'undefined' !== typeof Poets_Poems_Settings ) {
				me.settings = Poets_Poems_Settings.settings;
			}
		};

		/**
		 * Getter for retrieving a setting.
		 *
		 * @since 0.2.1
		 *
		 * @param {String} The identifier for the desired setting.
		 * @return The value of the setting.
		 */
		this.get_setting = function( identifier ) {
			return me.settings[identifier];
		};

	};

	/**
	 * Create Switcher Object.
	 *
	 * @since 0.2.1
	 */
	Poets_Poems_Switcher.switcher = new function() {

		// Prevent reference collisions.
		var me = this;

		/**
		 * Initialise Switcher.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.2.1
		 */
		this.init = function() {

		};

		/**
		 * Do setup when jQuery reports that the DOM is ready.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.2.1
		 */
		this.dom_ready = function() {

			// Set up instance.
			me.setup();

			// Enable listeners.
			me.listeners();

		};

		/**
		 * Set up Switcher instance.
		 *
		 * @since 0.2.1
		 */
		this.setup = function() {

			var src, spinner;

			src = Poets_Poems_Switcher.settings.get_setting( 'loading' ),
			spinner = '<img src="' + src + '" id="poem-loading" style="margin-top: 1em;" />'

			// Init AJAX spinner.
			$(spinner).prependTo( $('.widget_poets_poems_featured .post') ).hide();

		};

		/**
		 * Initialise listeners.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.2.1
		 */
		this.listeners = function() {

			// Declare vars.
			var button = $( '.poem-switcher' );

			/**
			 * Add a click event listener to button.
			 *
			 * @since 0.2.1
			 *
			 * @param {Object} event The event object.
			 */
			button.on( 'click', function( event ) {

				// Prevent link action.
				if ( event.preventDefault ) {
					event.preventDefault();
				}

				// The link dialogue expects an actual wp_editor instance.
				wpActiveEditor = true;

				// Hide link elements and set some styles.
				$('#wp-link #link-options, #wp-link p.howto').hide();
				$('#wp-link-wrap #most-recent-results, #wp-link-wrap #search-results').css( 'top', '36px' );

				// Open the link modal.
				wpLink.open( 'poem-switcher-field' );

				// Override title and button in modal.
				$('#link-modal-title').html( Poets_Poems_Switcher.settings.get_localisation( 'title' ) );
				$('#wp-link-submit').val( Poets_Poems_Switcher.settings.get_localisation( 'button' ) );

				// --<
				return false;

			});

			/**
			 * Add a click event listener to dialog submit button.
			 *
			 * @since 0.2.1
			 *
			 * @param {Object} event The event object
			 */
			$('body').on( 'click', '#wp-link-submit', function( event ) {

				// Grab result.
				var atts = wpLink.getAttrs();

				// The link dialogue expects an element to focus.
				wpLink.textarea = $('body');

				// Close the link modal.
				wpLink.close();

				// Get the Post data.
				post_data = atts.href.split( '//' );

				// sanity check
				if ( post_data.length == 2 ) {

					// Hide Poem.
					$('.widget_poets_poems_featured .post-inner').css( 'visibility', 'hidden' );

					// Show spinner.
					$('#poem-loading').show();

					// Send the ID to the server.
					me.send( post_data[1] );

				}

				// Prevent defaults.
				event.preventDefault ? event.preventDefault() : event.returnValue = false;
				event.stopPropagation();
				return false;

			});

			/**
			 * Add a click event listener to dialog cancel and close buttons.
			 *
			 * @since 0.2.1
			 *
			 * @param {Object} event The event object.
			 */
			$('body').on( 'click', '#wp-link-cancel, #wp-link-close', function( event ) {

				// The link dialogue expects an element to focus.
				wpLink.textarea = $('body');

				// Close the link modal.
				wpLink.close();

				// Prevent defaults.
				event.preventDefault ? event.preventDefault() : event.returnValue = false;
				event.stopPropagation();
				return false;

			});

		};

		/**
		 * Send AJAX request.
		 *
		 * @since 0.2.1
		 *
		 * @param {Array} data The data received from the server.
		 */
		this.update = function( data ) {

			var markup, teaser;

			// Parse Poem markup.
			if ( $.parseHTML ) {
				markup = $( $.parseHTML( data.markup ) );
			} else {
				markup = $(data.markup);
			}

			// Replace Poem.
			$('.widget_poets_poems_featured .post').html( markup );

			// Hide spinner.
			$('#poem-loading').hide();

			// Show Poem.
			$('.widget_poets_poems_featured .post-inner').css( 'visibility', 'visible' );

			// Parse teaser markup.
			if ( $.parseHTML ) {
				teaser = $( $.parseHTML( data.teaser ) );
			} else {
				teaser = $(data.teaser);
			}

			// Replace teaser.
			$('.featured-poem-teaser').html( teaser );

			// Re-run setup.
			me.setup();

		};

		/**
		 * Send AJAX request.
		 *
		 * @since 0.2.1
		 */
		this.send = function( post_id ) {

			var url, data;

			// URL to post to.
			url = Poets_Poems_Switcher.settings.get_setting( 'ajax_url' ),
			ajax_nonce = Poets_Poems_Switcher.settings.get_setting( 'ajax_nonce' );

			// Data received by WordPress.
			data = {
				action: 'set_featured_poem',
				_ajax_nonce: ajax_nonce,
				post_id: post_id
			};

			// Use jQuery post method.
			$.post( url, data,

				/**
				 * AJAX callback which receives response from the server.
				 *
				 * @since 0.2.1
				 *
				 * @param {Array} data The value to send.
				 * @param {String} textStatus The status of the response.
				 */
				function( data, textStatus ) {
					if ( textStatus == 'success' ) {
						me.update( data );
					} else {
						if ( console.log ) {
							console.log( textStatus );
						}
					}
				},

				// Expected format.
				'json'

			);

		};

	};

	// Init settings.
	Poets_Poems_Switcher.settings.init();

	// Init Switcher.
	Poets_Poems_Switcher.switcher.init();

} )( jQuery );

/**
 * Trigger dom_ready methods where necessary.
 *
 * @since 0.2.1
 */
jQuery(document).ready(function($) {

	// The DOM is loaded now.
	Poets_Poems_Switcher.settings.dom_ready();
	Poets_Poems_Switcher.switcher.dom_ready();

});
