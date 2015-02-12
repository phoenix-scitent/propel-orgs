<?php

class Propel_Org_Settings {

	private $settings;

	function __construct() {

		add_action( 'admin_menu', array( $this, 'register_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

		add_action( 'wp_ajax_import_propel_orgs', array( $this, 'ajax_import_propel_orgs' ) );


	}


	/**
	 * Loads the script
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @return void
	 */
	function load_scripts( $hook ) {

		if ( $hook != 'propel_org_page_import-propel-orgs' ) return;

		wp_enqueue_style( 'propel-groups-styles', plugin_dir_url( __FILE__ ) . 'style.css' );

		wp_enqueue_script( 'propel-orgs-scripts', plugins_url( '/js/import.js', __FILE__ ), array( 'jquery' ) );
	}


	/**
	 * Registers the 'Propel Orgs' menu
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @return void
	 */
	function register_menu() {

		add_submenu_page(
			'edit.php?post_type=propel_org',
			'Import PROPeL Orgs',
			'Import',
			'edit_others_posts',
			'import-propel-orgs',
			array( $this, 'render' )
		);

	}


	/**
	 * Renders the PROPeL Settings page
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-12 10:01:40
	 *
	 * @action add_settings_menu
	 */
	function render() {
		wp_enqueue_script( 'propel_groups_settings' );

		$current = isset( $_GET['tab'] ) ? $_GET['tab'] : 'import';

		$tabs = array(
			'import' => 'Import'
		);

		echo '<div class="wrap">';

		echo '<h2>PROPeL Groups</h2>';

		echo '<h2 class="nav-tab-wrapper">';
		foreach( $tabs as $tab => $name ) {
			$class = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?page=propel-groups&tab=$tab'>$name</a>";

		}
		echo '</h2>';
		?>

		<?php

		switch ( $current ) {

			case 'import':
				self::render_import_tab();
				break;

		}

		?>
    </div>
    <?php

	}


	static function render_import_tab() { ?>
		<h3>Import Organizations</h3>

		<p>Assumes an 'orgs.csv' file in the 'propel-organizations' plugin folder.</p>

		<p>Values should be listed as the example below:</p>
		
		<pre>[tag_id, parent_tag_id, tag_name, tag_value, sort, tag_other, createdate]</pre>
		<pre>1,0,League,Jefferson Swim League,1,1,2010-01-14 15:15:00.000</pre>

		<a id="import-orgs" class="button button-primary">Import Orgs</a>
	<?php


	}


	function ajax_import_propel_orgs() {
		$path = plugin_dir_path( __FILE__ ) . 'orgs.csv';

		$file = fopen( $path, 'r' );

		ini_set( 'auto_detect_line_endings', TRUE );

		// tag_id, parent_tag_id, tag_name, tag_value, sort, tag_other, createdate


		$lines = Array();

		while ( ( $line = fgetcsv( $file, 1000, "," ) ) !== FALSE ) {

			$num = $line[0];

			$lines[$num] = $line;

		}


		foreach ( $lines as $line ) {

			$org = array(
				'post_title' => $line[3],
				'post_status' => 'publish',
				'post_type' => 'propel_org',
			);

			if ( $line[1] > 0 ) {
				$parent = $lines[$line[1]][3];

				$parent = get_page_by_title( $parent, OBJECT, 'propel_org' );

				$org['post_parent'] = $parent->ID;

			}




			$org = wp_insert_post( $org );

			$type = get_term_by( 'name', $line[2], 'org_type' );

			wp_set_object_terms( $org, (int)$type->term_id, 'org_type' );


		}



	}

}

new Propel_Org_Settings();