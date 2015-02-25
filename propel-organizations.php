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

		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'wp_enqueue_scripts', function() {
			wp_register_script(
				'propel_orgs_userpro',
				plugin_dir_url( __FILE__ ) . '/js/user.js',
				array( 'jquery' )
			);
			wp_register_script(
				'propel_orgs_woocommerce',
				plugin_dir_url( __FILE__ ) . '/js/user.js',
				array( 'jquery' )
			);
		} );

		add_action( 'wp_ajax_get_child_orgs', array( $this, 'ajax_get_child_orgs' ) );
		add_action( 'wp_ajax_nopriv_get_child_orgs', array( $this, 'ajax_get_child_orgs' ) );


		// Render fields
		add_action( 'user_new_form',
			array( $this, 'render_user_fields' ) );
		add_action( 'show_user_profile',
			array( $this, 'render_user_fields' ) );
		add_action( 'edit_user_profile',
			array( $this, 'render_user_fields' ) );

		add_action( 'userpro_before_form_submit',
			array( $this, 'render_userpro_fields' ), 1 );

		add_action( 'woocommerce_after_checkout_billing_form',
			array( $this, 'render_woocommerce_fields' ) );


		// Save fields
		add_action( 'personal_options_update',
			array( $this, 'save_user_fields' ) );
		add_action( 'edit_user_profile_update',
			array( $this, 'save_user_fields' ) );
		add_action( 'user_register',
			array( $this, 'save_user_fields' ) );

		add_action( 'woocommerce_checkout_update_order_meta',
			array( $this, 'save_woocommerce_user_fields' ) );
	}


	function load_scripts( $page ) {

		$pages = array( 'user-new.php', 'user-edit.php', 'profile.php' );

		if ( in_array( $page, $pages ) )

			wp_register_script(
				'propel-orgs-user',
				plugin_dir_url( __FILE__ ) . '/js/user.js',
				array( 'jquery' )
			);

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

		wp_localize_script( 'propel-orgs-user', 'data', array( 'user_id' => $user->ID ) );

		wp_enqueue_script( 'propel-orgs-user' );

		$org_types = get_categories( array( 'taxonomy' => 'org_type', 'hierarchical' => 1 ) );

		?>


		<table class="form-table">

			<?php

			foreach ( $org_types as $org_type ) {

				$org = get_user_meta( $user->ID, 'propel_org_' . $org_type->slug, 1 );

				if ( $org_type->parent == 0 ) {
					$parent = 'parent';
					$disabled = '';
				} else {
					$parent = '';
					$disabled = 'disabled';
				}
				?>
				<tr class="form-field">
					<th>
						<label for="<?php echo $org_type->slug; ?>"><?php echo $org_type->name; ?></label>
					</th>
					<td>
						<select
							class="propel-org <?php echo $parent; ?>"
							id="<?php echo $org_type->slug; ?>"
							name="propel_org_<?php echo $org_type->slug; ?>"
							data-type="<?php echo $org_type->term_id; ?>"
							<?php echo $disabled;?> >

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

									$orgs->the_post();

									$selected = $org == get_the_id() ? 'selected' : '';

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
	 * Renders the org fields for the userpro form
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-12 14:43:46
	 *
	 * @param  Array   $user   The WP_User object
	 *
	 * @action userpro_after_fields
	 */
	function render_userpro_fields( $args ) {
		global $wp_query;

		$page = $wp_query->post->post_name;

		if ( $page == 'login' || $page == 'profile' || $_POST['action'] == 'userpro_shortcode_template' || $_POST['action'] == 'userpro_process_form' ) return;

		$user = wp_get_current_user();

		wp_localize_script( 'propel_orgs_userpro', 'data', array( 'args' => $args ) );

		wp_enqueue_script( 'propel_orgs_userpro' );

		$org_types = get_categories( array( 'taxonomy' => 'org_type', 'hierarchical' => 1 ) );


		foreach ( $org_types as $org_type ) {

			$org = get_user_meta( $user->ID, 'propel_org_' . $org_type->slug, 1 );

			if ( $org_type->parent == 0 ) {
				$parent = 'parent';
				$disabled = '';
			} else {
				$parent = '';
				$disabled = 'disabled';
			}
			?>

			<div class="userpro-field">
				<div class="userpro-label">
					<label for="<?php echo $org_type->slug; ?>"><?php echo $org_type->name; ?></label>
				</div>
				<div class="userpro-input">
					<select
							class="propel-org <?php echo $parent; ?>"
							id="<?php echo $org_type->slug; ?>"
							name="propel_org_<?php echo $org_type->slug; ?>"
							data-type="<?php echo $org_type->term_id; ?>"
							style="height: 30px !important;"
							<?php echo $disabled;?> >

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

										$orgs->the_post();

										$selected = $org == get_the_id() ? 'selected' : '';

										echo '<option value="' . get_the_id() . '" ' . $selected . '>' . get_the_title() . '</option>';

									endwhile; endif;

								}


								?>
								<option value="add_organization">+ Add <?php echo $org_type->name; ?>...</option>
						</select>
					<div class="userpro-clear"></div>
				</div>
				<div class="userpro-clear"></div>
			</div>

	<?php
		}
	}


	/**
	 * Renders the league and team fields for the woocommerce review order form
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-12 15:16:12
	 *
	 * @param  Array   $args
	 *
	 * @action woocommerce_after_checkout_billing_form
	 */
	function render_woocommerce_fields( $args ) {

		$user = wp_get_current_user();

		if ( $user->ID != 0 ) return;

		wp_localize_script( 'propel_orgs_woocommerce', 'data', array( 'args' => $args ) );

		wp_enqueue_script( 'propel_orgs_woocommerce' );

		$org_types = get_categories( array( 'taxonomy' => 'org_type', 'hierarchical' => 1 ) );


		foreach ( $org_types as $org_type ) {

			$org = get_user_meta( $user->ID, 'propel_org_' . $org_type->slug, 1 );

			if ( $org_type->parent == 0 ) {
				$parent = 'parent';
				$disabled = '';
			} else {
				$parent = '';
				$disabled = 'disabled';
			}

			?>

			<p class="form-row">
				<label for="<?php echo $org_type->slug; ?>"><?php echo $org_type->name; ?></label>
				<select
						class="propel-org <?php echo $parent; ?>"
						id="<?php echo $org_type->slug; ?>"
						name="propel_org_<?php echo $org_type->slug; ?>"
						data-type="<?php echo $org_type->term_id; ?>"
						<?php echo $disabled; ?> >

						<option value="">Please select a <?php echo $org_type->slug; ?></option>

					<?php

			if ( $org_type->category_parent == 0 ) {

				$org_query = array(
					'post_type' => 'propel_org',
					'nopaging' => 1,
					'tax_query' => array( array(
						'taxonomy' => 'org_type',
						'field' => 'slug',
						'terms' => $org_type->slug,
						'include_children' => 0
					) )
				);

				$orgs = new WP_Query( $org_query );

				if ( $orgs->have_posts() ): while ( $orgs->have_posts() ):

					$orgs->the_post();

					$selected = $org == get_the_id() ? 'selected' : '';

					echo '<option value="' . get_the_id() . '" ' . $selected . '>' . get_the_title() . '</option>';

				endwhile; endif;

			}


			?>
						</select>
			</p>

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
	function ajax_get_child_orgs() {

		$parent     = $_POST['parent'];
		$parentType = $_POST['type'];
		$user       = $_POST['user_id'];

		$type = get_categories(
			array(
				'taxonomy' => 'org_type',
				'hierarchical' => 1,
				'child_of' => $parentType
			)
		);

		$org_query = array(
			'post_type'   => 'propel_org',
			'nopaging'    => 1,
			'post_parent' => $parent,
			'tax_query'   => array( array(
				'taxonomy'         => 'org_type',
				'field'            => 'slug',
				'terms'            => $type[0]->slug,
				'include_children' => 0
			) )
		);

		$child_orgs = new WP_Query( $org_query );

		$org = get_user_meta( $user, 'propel_org_' . $type[0]->slug, 1 );

		$out = '';

		if ( $child_orgs->have_posts() ): while ( $child_orgs->have_posts() ):

			$child_orgs->the_post();

			$selected = $org == get_the_id() ? 'selected' : '';

			$out .= '<option value="' . get_the_id() . '" ' . $selected . '>' . get_the_title() . '</option>';

		endwhile; endif;

		wp_send_json_success( array( 'html' => $out, 'child' => $type[0]->slug ) );
	}


	/**
	 * Saves the organization user meta information
	 *
	 * @author  caseypatrickdriscoll
	 *
	 * @created 2015-02-12 13:58:15
	 *
	 * @param   int   $user_id   The user id
	 *
	 * @action  edit_user_profile_update
	 * @action  personal_options_update
	 * @action  user_register
	 */
	function save_user_fields( $user_id ) {

		foreach ( $_POST as $key => $value) {

			if ( substr( $key, 0, 11 ) == "propel_org_" && $value != "add_organization" ) {
				update_user_meta( $user_id, $key, $value );
			}

			if ( substr( $key, 0, 15 ) == "new_propel_org_" ) {

				$org_type = str_replace( 'new_propel_org_', '', $key );

				add_filter( 'userpro_pre_profile_update_filters', array( $this, 'update_form_array' ) );

				$org = array(
					'post_title'  => $value,
					'post_status' => 'draft',
					'post_type'   => 'propel_org',
				);

				$exists = get_page_by_title( $value, OBJECT, 'propel_org' );

				if ( ! empty( $exists ) ) {
					update_user_meta( $user_id, 'propel_org_' . $org_type, $exists->ID );
					continue;
				}

				$org = wp_insert_post( $org );

				$_POST['propel_org_' . $org_type] = $org;

				update_user_meta( $user_id, 'propel_org_' . $org_type, $org );

				$type = get_term_by( 'name', $org_type, 'org_type' );

				wp_set_object_terms( $org, (int)$type->term_id, 'org_type' );
			}

		}

	}


	/**
	 * Saves the orgs user meta information after purchase
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-02-12 15:16:29
	 *
	 * @param int   $order_id   The order id
	 *
	 * @action woocommerce_checkout_update_order_meta
	 */
	function save_woocommerce_user_fields( $order_id ) {
		$order = new WC_Order( $order_id );
		$user_id = $order->user_id;

		foreach ( $_POST as $key => $value) {
			if ( substr( $key, 0, 11) == "propel_org_" ) {
				update_usermeta( $user_id, $key, $value );
			}
		}
	}


	/**
	 * When UserPro creates a new user, they update the new user at the end of their 'new_user' function process.
	 * This 'userpro_update_user_profile' process was overwriting our just written 'propel_org_' user meta data
	 * 
	 * To make sure the correct data is retained, we have to filter their 'form' array, a duplicate of $_POST
	 *
	 * @author  caseypatrickdriscoll
	 *
	 * @created 2015-02-25 10:44:35
	 *
	 * @filter  userpro_pre_profile_update_filters
	 * 
	 * @param   Array   $form      The UserPro form array, a duplicate of the $_POST request
	 * @param   int     $user_id   The created user
	 * 
	 * @return  Array  $form       The amended UserPro form array
	 */
	function update_form_array( $form, $user_id ) {

		foreach ( $form as $key => $value ) {

			if ( substr( $key, 0, 11 ) == "propel_org_" ) {

				$form[$key] = $_POST[$key];

			}

		}

		return $form;

	}

}

new Propel_Organizations();