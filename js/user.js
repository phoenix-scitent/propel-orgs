jQuery( document ).ready( function() {

	jQuery( '.userpro-section' ).hide();

	setChildOrgs();

	jQuery( '.propel-org' ).on( 'change', function(e) {

		if ( ! e.hasOwnProperty( 'originalEvent' ) ) return;

		if ( jQuery( e.target ).val() == 'add_organization' )
			addOrganization( e.target.id );

	} );

	jQuery( '.propel-org.parent' ).on( 'change', function(e) {

		if ( ! e.hasOwnProperty( 'originalEvent' ) ) return;

		if ( jQuery( e.target ).val() != 'add_organization' )
			setChildOrgs( e.target.id );

	} );
} );


function addOrganization( id ) {
	input = '<input type="text" id="new_propel_org_' + id +'" name="new_propel_org_' + id + '" style="width: 100% !important;margin: 15px 15px 0 0 !important;"></input>';
	jQuery( '#' + id ).after( input ).next().focus();
	jQuery( '.propel-org' ).attr( 'disabled', false );
}


function setChildOrgs() {
	parent = jQuery( '.propel-org.parent' ).val();
	parentType = jQuery( '.propel-org.parent' ).data( 'type' );

	if ( parent == '' ) return;

	jQuery( '.spinner-' + jQuery( '.propel-org.parent' ).attr( 'id' ) ).show();

	jQuery.post(
		'/wp-admin/admin-ajax.php',
		{
			'action'  : 'get_child_orgs',
			'parent'  : parent,
			'type'    : parentType,
			'user_id' : data.user_id,
			'public'  : data.public
		},
		function( response ) {
			jQuery( '.spinner-' + response.data.parent ).hide();
			jQuery( '#' + response.data.child ).html( response.data.html ).attr( 'disabled', false);
		}
	);

}
