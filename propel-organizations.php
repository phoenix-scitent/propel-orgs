<?php
/**
 * Plugin Name: PROPeL Organizations
 * Author: Casey Patrick Driscoll
 * Description: A plugin for adding users to organizations
 */

include 'propel-org-cpt.php';
include 'propel-org-type.php';
include 'propel-org-settings.php';


class Propel_Organizations {


	function __construct() {


		// Render fields
		add_action( 'user_new_form',
			array( $this, 'render_user_fields' ) );
		add_action( 'show_user_profile',
			array( $this, 'render_user_fields' ) );
		add_action( 'edit_user_profile',
			array( $this, 'render_user_fields' ) );
	}


	/**
	 * Renders the group fields for the user form
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-12 11:12:34
	 *
	 * @param WP_User   $user   The WP_User object
	 *
	 * @action user_new_form
	 * @action show_user_profile
	 * @action edit_user_profile
	 */
	function render_user_fields( $user ) {

		wp_localize_script( 'propel_groups_user', 'data', array( 'user_id' => $user->ID ) );

		wp_enqueue_script( 'propel_groups_user' );

		$org_types = get_categories( array( 'taxonomy' => 'org_type', 'hierarchical' => 1 ) );

		?>


		<table class="form-table">

			<?php

			foreach ( $org_types as $org_type ) { ?>
				<tr class="form-field">
					<th>
						<label for="leagues"><?php echo $org_type->name; ?></label>
					</th>
					<td>
						<select id="leagues" name="league">
							<option value="">Please select a <?php echo $org_type->slug; ?></option>

							<?php

							if ( $org_type->category_parent == 0 ) {

								$org_query = array(
									'post_type' => 'propel_org',
									'nopaging'  => 1,
									'tax_query' => array( array(
										'taxonomy'         => 'org_type',
										'field'            => 'slug',
										'terms'            => $org_type->slug,
										'include_children' => 0
									) )
								);

								$orgs = new WP_Query( $org_query );

								if ( $orgs->have_posts() ): while ( $orgs->have_posts() ):

									$selected = ''; //( get_user_meta( $user->ID, 'league', 1 ) == $league_name ) ? 'selected' : '' ;

									$orgs->the_post();

									echo '<option value="' . get_the_id() . '" ' . $selected . '>' . get_the_title() . '</option>';

								endwhile; endif;

							}


							?>
						</select>
					</td>
				</tr>

				<?php
			}
	}


	/**
	 * Generates a list of options for the 'team' select list in user profiles
	 *
	 * @author  caseypatrickdriscoll
	 *
	 * @created 2015-02-12 11:07:39
	 *
	 * @return  json with 'options' in html
	 */
	function ajax_get_teams() {

		$leagues = get_option( 'propel-groups' );
		$league  = $_POST['league'];
		$user    = $_POST['user_id'];

		$teams = $leagues[$league];

		$out = '';

		foreach ( $teams as $team ) {
			if ( isset( $user ) )
				$selected = ( get_user_meta( $user, 'team', 1 ) == $team ) ? 'selected' : '' ;

			$out .= '<option value="' . $team . '" ' . $selected . '>' . str_replace( '_', ' ', $team ) . '</option>';
		}

		wp_send_json_success( array( 'html' => $out ) );
	}
}

new Propel_Organizations();