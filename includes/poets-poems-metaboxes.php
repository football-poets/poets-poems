<?php
/**
 * Poems Metaboxes class.
 *
 * Handles the "Poems" Metaboxes.
 *
 * @package Poets_Poems
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Football Poets "Poems" Metaboxes Class.
 *
 * A class that encapsulates all Metaboxes for this CPT.
 *
 * @package Poets_Poems
 */
class Poets_Poems_Metaboxes {

	/**
	 * Custom Post Type name.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $post_type_name = 'poem';

	/**
	 * Original database ID meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $original_id_meta_key = 'poets_poems_original_id';

	/**
	 * Author Name meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $author_name_meta_key = 'poets_poems_author_name';

	/**
	 * Author Email meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $author_email_meta_key = 'poets_poems_author_email';

	/**
	 * Author Copyright meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $author_copyright_meta_key = 'poets_poems_author_copyright';

	/**
	 * Content Notes meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $content_notes_meta_key = 'poets_poems_content_notes';

	/**
	 * Admin Review meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $admin_review_meta_key = 'poets_poems_admin_review';

	/**
	 * School Name meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $school_name_meta_key = 'poets_poems_school_name';

	/**
	 * School Teacher Name meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $school_teacher_name_meta_key = 'poets_poems_school_teacher_name';

	/**
	 * School Teacher Email meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $school_teacher_email_meta_key = 'poets_poems_school_teacher_email';

	/**
	 * Competition Name meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $competition_name_meta_key = 'poets_poems_competition_name';

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Init when this plugin is loaded.
		add_action( 'poets_poems/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this class.
	 *
	 * @since 0.3.1
	 */
	public function initialise() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && true === $done ) {
			return;
		}

		// Bootstrap class.
		$this->register_hooks();

		/**
		 * Broadcast that this class is now loaded.
		 *
		 * @since 0.3.1
		 */
		do_action( 'poets_poems/metaboxes/loaded' );

		// We're done.
		$done = true;

	}

	/**
	 * Register hook callbacks.
	 *
	 * @since 0.1
	 */
	private function register_hooks() {

		// Add meta boxes.
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		// Intercept save.
		add_action( 'save_post', [ $this, 'save_post' ], 1, 2 );

	}

	// -----------------------------------------------------------------------------------

	/**
	 * Adds meta boxes to admin screens
	 *
	 * @since 0.1
	 */
	public function add_meta_boxes() {

		// Add a meta box for legacy data.
		add_meta_box(
			'poets_poems_legacy',
			__( 'Legacy Info', 'poets-poems' ),
			[ $this, 'metabox_legacy' ],
			$this->post_type_name,
			'side'
		);

		// Add a meta box for author info.
		add_meta_box(
			'poets_poems_author',
			__( 'Poem Author', 'poets-poems' ),
			[ $this, 'metabox_author' ],
			$this->post_type_name,
			'side'
		);

		// Add a meta box for displayed content meta.
		add_meta_box(
			'poets_poems_content',
			__( 'Poem Notes', 'poets-poems' ),
			[ $this, 'metabox_content' ],
			$this->post_type_name,
			'advanced'
		);

		// Add a meta box for admin review meta.
		add_meta_box(
			'poets_poems_admin',
			__( 'Review Comments (not shown)', 'poets-poems' ),
			[ $this, 'metabox_admin' ],
			$this->post_type_name,
			'advanced'
		);

		// Add a meta box for school meta.
		add_meta_box(
			'poets_poems_school',
			__( 'School Information (not shown)', 'poets-poems' ),
			[ $this, 'metabox_school' ],
			$this->post_type_name,
			'advanced'
		);

		// Add a meta box for competition meta.
		add_meta_box(
			'poets_poems_competition',
			__( 'Competition Information (not shown)', 'poets-poems' ),
			[ $this, 'metabox_competition' ],
			$this->post_type_name,
			'advanced'
		);

	}

	/**
	 * Adds an legacy info meta box to CPT edit screens.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the current Post/Page.
	 */
	public function metabox_legacy( $post ) {

		// Get value for original ID key.
		$val = $this->get_meta( $post, '_' . $this->original_id_meta_key );

		// Show original ID.
		/* translators: %s: The original database ID. */
		echo '<p>' . sprintf( esc_html__( 'Original database ID: %d', 'poets-poems' ), (int) $val ) . '</p>';

	}

	/**
	 * Adds an author info meta box to CPT edit screens.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the current Post/Page.
	 */
	public function metabox_author( $post ) {

		// Use nonce for verification.
		wp_nonce_field( 'poets_poems_author', 'poets_poems_author_nonce' );

		// Get value for author name key.
		$val = $this->get_meta( $post, '_' . $this->author_name_meta_key );

		// Show author name in a text field.
		echo '<p><label for="' . esc_attr( $this->author_name_meta_key ) . '">' . esc_html__( 'Author Name', 'poets-poems' ) . '<br><input type="text" id="' . esc_attr( $this->author_name_meta_key ) . '" name="' . esc_attr( $this->author_name_meta_key ) . '" value="' . esc_attr( $val ) . '" /></label></p>';

		// Get value for author email key.
		$val = $this->get_meta( $post, '_' . $this->author_email_meta_key );

		// Show author email in a text field.
		echo '<p><label for="' . esc_attr( $this->author_email_meta_key ) . '">' . esc_html__( 'Author Email', 'poets-poems' ) . '<br><input type="text" id="' . esc_attr( $this->author_email_meta_key ) . '" name="' . esc_attr( $this->author_email_meta_key ) . '" value="' . esc_attr( $val ) . '" /></label></p>';

		// Get value for copyright key.
		$val = $this->get_meta( $post, '_' . $this->author_copyright_meta_key );

		// Show copyright in a text field.
		echo '<p><label for="' . esc_attr( $this->author_name_meta_key ) . '">' . esc_html__( 'Copyright', 'poets-poems' ) . '<br><input type="text" id="' . esc_attr( $this->author_copyright_meta_key ) . '" name="' . esc_attr( $this->author_copyright_meta_key ) . '" value="' . esc_attr( $val ) . '" /></label></p>';

	}

	/**
	 * Adds a content meta box to CPT edit screens.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the current Post/Page.
	 */
	public function metabox_content( $post ) {

		// Use nonce for verification.
		wp_nonce_field( 'poets_poems_content', 'poets_poems_content_nonce' );

		// Get value for notes key.
		$val = $this->get_meta( $post, '_' . $this->content_notes_meta_key );

		// Call the editor.
		wp_editor(
			$val,
			$this->content_notes_meta_key,
			$settings = [
				'media_buttons' => false,
			]
		);

	}

	/**
	 * Adds a admin meta box to CPT edit screens.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the current Post/Page.
	 */
	public function metabox_admin( $post ) {

		// Use nonce for verification.
		wp_nonce_field( 'poets_poems_admin', 'poets_poems_admin_nonce' );

		// Get value for admin review key.
		$val = $this->get_meta( $post, '_' . $this->admin_review_meta_key );

		// Call the editor.
		wp_editor(
			$val,
			$this->admin_review_meta_key,
			$settings = [
				'media_buttons' => false,
			]
		);

	}

	/**
	 * Adds a school info meta box to CPT edit screens.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the current Post/Page.
	 */
	public function metabox_school( $post ) {

		// Use nonce for verification.
		wp_nonce_field( 'poets_poems_school', 'poets_poems_school_nonce' );

		// Get value for school name key.
		$val = $this->get_meta( $post, '_' . $this->school_name_meta_key );

		// Show school name in a text field.
		echo '<p><label for="' . esc_attr( $this->school_name_meta_key ) . '">' . esc_html__( 'School Name', 'poets-poems' ) . '<br><input type="text" id="' . esc_attr( $this->school_name_meta_key ) . '" name="' . esc_attr( $this->school_name_meta_key ) . '" value="' . esc_attr( $val ) . '" class="regular-text" /></label></p>';

		// Get value for school teacher name key.
		$val = $this->get_meta( $post, '_' . $this->school_teacher_name_meta_key );

		// Show school teacher name in a text field.
		echo '<p><label for="' . esc_attr( $this->school_teacher_name_meta_key ) . '">' . esc_html__( 'Teacher Name', 'poets-poems' ) . '<br><input type="text" id="' . esc_attr( $this->school_teacher_name_meta_key ) . '" name="' . esc_attr( $this->school_teacher_name_meta_key ) . '" value="' . esc_attr( $val ) . '" class="regular-text" /></label></p>';

		// Get value for school teacher email key.
		$val = $this->get_meta( $post, '_' . $this->school_teacher_email_meta_key );

		// Show school teacher email in a text field.
		echo '<p><label for="' . esc_attr( $this->school_teacher_email_meta_key ) . '">' . esc_html__( 'Teacher Email', 'poets-poems' ) . '<br><input type="text" id="' . esc_attr( $this->school_teacher_email_meta_key ) . '" name="' . esc_attr( $this->school_teacher_email_meta_key ) . '" value="' . esc_attr( $val ) . '" class="regular-text" /></label></p>';

	}

	/**
	 * Adds an competition info meta box to CPT edit screens.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the current Post/Page.
	 */
	public function metabox_competition( $post ) {

		// Use nonce for verification.
		wp_nonce_field( 'poets_poems_competition', 'poets_poems_competition_nonce' );

		// Get value for competition name key.
		$val = $this->get_meta( $post, '_' . $this->competition_name_meta_key );

		// Show competition name in a text field.
		echo '<p><label for="' . esc_attr( $this->competition_name_meta_key ) . '">' . esc_html__( 'Competition Name', 'poets-poems' ) . '<br><input type="text" id="' . esc_attr( $this->competition_name_meta_key ) . '" name="' . esc_attr( $this->competition_name_meta_key ) . '" value="' . esc_attr( $val ) . '" class="regular-text" /></label></p>';

	}

	/**
	 * Stores our additional params.
	 *
	 * @since 0.1
	 *
	 * @param integer $post_id The ID of the Post or revision.
	 * @param integer $post The Post object.
	 */
	public function save_post( $post_id, $post ) {

		// We don't use Post ID because we're not interested in revisions.
		if ( ! $post ) {
			return;
		}

		// Unhook BuddyForms attempt to update meta in WP admin.
		if ( is_admin() && $post->post_type === $this->post_type_name ) {
			remove_action( 'save_post', 'buddyforms_metabox_admin_form_metabox_save' );
		}

		// Save our author metadata.
		$result = $this->save_author_meta( $post );

		// Save our content metadata.
		$result = $this->save_content_meta( $post );

		// Save our admin metadata.
		$result = $this->save_admin_meta( $post );

		// Save our school metadata.
		$result = $this->save_school_meta( $post );

		// Save our competition metadata.
		$result = $this->save_competition_meta( $post );

	}

	// -----------------------------------------------------------------------------------

	/**
	 * When a Post is saved, this also saves the author metadata.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post_obj The object for the Post or revision.
	 */
	private function save_author_meta( $post_obj ) {

		// If no Post, kick out.
		if ( ! $post_obj ) {
			return;
		}

		// Authenticate.
		$nonce = isset( $_POST['poets_poems_author_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['poets_poems_author_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'poets_poems_author' ) ) {
			return;
		}

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_obj->ID ) ) {
			return;
		}

		// Check for revision.
		if ( 'revision' === $post_obj->post_type ) {

			// Get parent.
			if ( 0 !== (int) $post_obj->post_parent ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// Bail if not Poem Post Type.
		if ( $post->post_type !== $this->post_type_name ) {
			return;
		}

		// Now process metadata.

		// Save author name.
		$db_key = '_' . $this->author_name_meta_key;
		$value  = isset( $_POST[ $this->author_name_meta_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->author_name_meta_key ] ) ) : '';
		$this->save_meta( $post, $db_key, $value );

		// Save author email.
		$db_key = '_' . $this->author_email_meta_key;
		$value  = isset( $_POST[ $this->author_email_meta_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->author_email_meta_key ] ) ) : '';
		$this->save_meta( $post, $db_key, $value );

		// Save copyright info.
		$db_key = '_' . $this->author_copyright_meta_key;
		$value  = isset( $_POST[ $this->author_copyright_meta_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->author_copyright_meta_key ] ) ) : '';
		$this->save_meta( $post, $db_key, $value );

	}

	/**
	 * When a Post is saved, this also saves the content metadata.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post_obj The object for the Post or revision.
	 */
	private function save_content_meta( $post_obj ) {

		// If no Post, kick out.
		if ( ! $post_obj ) {
			return;
		}

		// Authenticate.
		$nonce = isset( $_POST['poets_poems_content_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['poets_poems_content_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'poets_poems_content' ) ) {
			return;
		}

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_obj->ID ) ) {
			return;
		}

		// Check for revision.
		if ( 'revision' === $post_obj->post_type ) {

			// Get parent.
			if ( 0 !== (int) $post_obj->post_parent ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// Bail if not Poem Post Type.
		if ( $post->post_type !== $this->post_type_name ) {
			return;
		}

		// Now process metadata.

		// Save notes.
		$db_key = '_' . $this->content_notes_meta_key;
		$value  = isset( $_POST[ $this->content_notes_meta_key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $this->content_notes_meta_key ] ) ) : '';
		$this->save_meta( $post, $db_key, $value );

	}

	/**
	 * When a Post is saved, this also saves the admin metadata.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post_obj The object for the Post or revision.
	 */
	private function save_admin_meta( $post_obj ) {

		// If no Post, kick out.
		if ( ! $post_obj ) {
			return;
		}

		// Authenticate.
		$nonce = isset( $_POST['poets_poems_admin_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['poets_poems_admin_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'poets_poems_admin' ) ) {
			return;
		}

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_obj->ID ) ) {
			return;
		}

		// Check for revision.
		if ( 'revision' === $post_obj->post_type ) {

			// Get parent.
			if ( 0 !== (int) $post_obj->post_parent ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// Bail if not Poem Post Type.
		if ( $post->post_type !== $this->post_type_name ) {
			return;
		}

		// Now process metadata.

		// Save notes.
		$db_key = '_' . $this->admin_review_meta_key;
		$value  = isset( $_POST[ $this->admin_review_meta_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->admin_review_meta_key ] ) ) : '';
		$this->save_meta( $post, $db_key, $value );

	}

	/**
	 * When a Post is saved, this also saves the school metadata.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post_obj The object for the Post or revision.
	 */
	private function save_school_meta( $post_obj ) {

		// If no Post, kick out.
		if ( ! $post_obj ) {
			return;
		}

		// Authenticate.
		$nonce = isset( $_POST['poets_poems_school_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['poets_poems_school_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'poets_poems_school' ) ) {
			return;
		}

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_obj->ID ) ) {
			return;
		}

		// Check for revision.
		if ( 'revision' === $post_obj->post_type ) {

			// Get parent.
			if ( 0 !== (int) $post_obj->post_parent ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// Bail if not Poem Post Type.
		if ( $post->post_type !== $this->post_type_name ) {
			return;
		}

		// Now process metadata.

		// Save school name.
		$db_key = '_' . $this->school_name_meta_key;
		$value  = isset( $_POST[ $this->school_name_meta_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->school_name_meta_key ] ) ) : '';
		$this->save_meta( $post, $db_key, $value );

		// Save school teacher email.
		$db_key = '_' . $this->school_teacher_name_meta_key;
		$value  = isset( $_POST[ $this->school_teacher_name_meta_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->school_teacher_name_meta_key ] ) ) : '';
		$this->save_meta( $post, $db_key, $value );

		// Save copyright info.
		$db_key = '_' . $this->school_teacher_email_meta_key;
		$value  = ( isset( $_POST[ $this->school_teacher_email_meta_key ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ $this->school_teacher_email_meta_key ] ) ) : '';
		$this->save_meta( $post, $db_key, $value );

	}

	/**
	 * When a Post is saved, this also saves the competition metadata.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post_obj The object for the Post or revision.
	 */
	private function save_competition_meta( $post_obj ) {

		// If no Post, kick out.
		if ( ! $post_obj ) {
			return;
		}

		// Authenticate.
		$nonce = isset( $_POST['poets_poems_competition_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['poets_poems_competition_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'poets_poems_competition' ) ) {
			return;
		}

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_obj->ID ) ) {
			return;
		}

		// Check for revision.
		if ( 'revision' === $post_obj->post_type ) {

			// Get parent.
			if ( 0 !== (int) $post_obj->post_parent ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// Bail if not Poem Post Type.
		if ( $post->post_type !== $this->post_type_name ) {
			return;
		}

		// Now process metadata.

		// Save competition name.
		$db_key = '_' . $this->competition_name_meta_key;
		$value  = isset( $_POST[ $this->competition_name_meta_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->competition_name_meta_key ] ) ) : '';
		$this->save_meta( $post, $db_key, $value );

	}

	/**
	 * Utility to simplify metadata retrieval.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The WordPress Post object.
	 * @param string  $key The meta key.
	 * @return mixed $data The data that was saved.
	 */
	private function get_meta( $post, $key ) {

		// Init return.
		$data = '';

		// Get value if the custom field already has one.
		$existing = get_post_meta( $post->ID, $key, true );
		if ( false !== $existing ) {
			$data = get_post_meta( $post->ID, $key, true );
		}

		// --<
		return $data;

	}

	/**
	 * Utility to automate metadata saving.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The WordPress Post object.
	 * @param string  $key The meta key.
	 * @param mixed   $data The data to be saved.
	 * @return mixed $data The data that was saved.
	 */
	private function save_meta( $post, $key, $data = '' ) {

		// If the custom field already has a value.
		$existing = get_post_meta( $post->ID, $key, true );
		if ( false !== $existing ) {

			// Update the data.
			$ret = update_post_meta( $post->ID, $key, $data );

		} else {

			// Add the data.
			add_post_meta( $post->ID, $key, $data, true );

		}

		// --<
		return $data;

	}

}
