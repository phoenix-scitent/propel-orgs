jQuery( document ).ready( function() {

//	jQuery( '.userpro-section' ).hide();

	setChildOrgs();

	jQuery( '.propel-org.parent' ).on( 'change', function(e) {
		setChildOrgs();
	} );

} );

function setChildOrgs() {
	parent = jQuery( '.parent' ).val();
	parentType = jQuery( '.parent' ).data( 'type' );

	if ( parent == '' ) return;

	jQuery.post(
		'/wp-admin/admin-ajax.php',
		{
			'action'  : 'get_child_orgs',
			'parent'  : parent,
			'type'    : parentType,
			'user_id' : data.user_id
		},
		function( response ) {
			if ( response.data.html.length > 0 )
				jQuery( '#' + response.data.child ).html( response.data.html ).attr( 'disabled', false);
			else
				jQuery( '#' + response.data.child ).html( '<option value="">League has no teams</option>' ).attr( 'disabled', true );
		}
	);

}