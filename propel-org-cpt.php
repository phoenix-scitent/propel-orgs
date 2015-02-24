<?php


class Propel_Org {


	function __construct() {


		add_action( 'init', array( $this, 'create_post_type' ) );


		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );


		add_action( 'save_post', array( $this, 'save_meta_box_data' ) );


		add_filter( 'gettext', array( $this, 'custom_enter_title' ) );



	}



	function create_post_type() {
		$labels = array(
			'name'                => _x( 'PROPeL Orgs', 'Post Type General Name', 'propel' ),
			'singular_name'       => _x( 'PROPeL Org', 'Post Type Singular Name', 'propel' ),
			'menu_name'           => __( 'PROPeL Orgs', 'propel' ),
			'parent_item_colon'   => __( 'Parent PROPeL Org:', 'propel' ),
			'all_items'           => __( 'All PROPeL Orgs', 'propel' ),
			'view_item'           => __( 'View PROPeL Org', 'propel' ),
			'add_new_item'        => __( 'Add New PROPeL Org', 'propel' ),
			'add_new'             => __( 'Add New', 'propel' ),
			'edit_item'           => __( 'Edit PROPeL Org', 'propel' ),
			'update_item'         => __( 'Update PROPeL Org', 'propel' ),
			'search_items'        => __( 'Search PROPeL Orgs', 'propel' ),
			'not_found'           => __( 'Not found', 'propel' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'propel' ),
		);
		$args = array(
			'label'               => __( 'propel_org', 'propel' ),
			'description'         => __( 'An organization in the OKM', 'propel' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'page-attributes' ),
			'taxonomies'          => array( 'org_type' ),
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-networking',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		register_post_type( 'propel_org', $args );
	}


	/**
	 * Registers the meta boxes needed for the propel_org cpt
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-24 11:11:29
	 *
	 * @return void
	 */
	function add_meta_boxes() {

		add_meta_box(
			'propel_org_org_id',
			__( 'Org ID', 'propel' ),
			array( $this, 'render_org_id_meta_box' ),
			'propel_org',
			'side'
		);

	}


	/**
	 * Renders the org_id meta box
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-24 11:12:15
	 *
	 * @param  WP_Post   $post   The post object
	 *
	 * @return void
	 */
	function render_org_id_meta_box( $post ) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'propel_org_org_id', 'propel_org_org_id_nonce' );

		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		$value = get_post_meta( $post->ID, '_org_id', true );

		echo '<input type="text" id="propel_org_org_id" name="propel_org_org_id" value="' . esc_attr( $value ) . '" size="10" />';
	}


	/**
	 * When the post is saved, saves the meta data.
	 *  - Thanks, http://codex.wordpress.org/Function_Reference/add_meta_box
	 *
	 * @author  caseypatrickdriscoll
	 *
	 * @created 2015-02-24 11:14:14
	 *
	 * @param   int   $post_id    The ID of the post being saved.
	 *
	 * @return  void
	 */
	function save_meta_box_data( $post_id ) {

		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['propel_org_org_id_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['propel_org_org_id_nonce'], 'propel_org_org_id' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'propel_org' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}

		}

		/* OK, it's safe for us to save the data now. */

		// Make sure that it is set.
		if ( ! isset( $_POST['propel_org_org_id'] ) ) {
			return;
		}

		// Sanitize user input.
		$org_id = sanitize_text_field( $_POST['propel_org_org_id'] );

		// Update the meta field in the database.
		update_post_meta( $post_id, '_org_id', $org_id );
	}


	/**
	 * Filters for a different title entry text
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-24 11:28:48
	 * 
	 * @param  string   $input   The given title text
	 *
	 * @return string   $input   The new title text
	 */
	function custom_enter_title( $input ) {

		global $post_type;

		if( is_admin() && 'Enter title here' == $input && 'propel_org' == $post_type )
			return 'Enter org name';

		return $input;
	}

}

new Propel_Org();