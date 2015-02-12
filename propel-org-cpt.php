<?php


class Propel_Org {


	function __construct() {


		add_action( 'init', array( $this, 'create_post_type' ) );


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
			'supports'            => array( 'title', ),
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
}

new Propel_Org();