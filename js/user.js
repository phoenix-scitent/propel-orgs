jQuery( document ).ready( function() {

	jQuery( '.userpro-section' ).hide();

	setTeams();

	jQuery( '#leagues' ).on( 'change', function(e) {
		setTeams();
	} );

} );

function setTeams() {
	league = jQuery( '#leagues' ).val();

	if ( league == '' ) return;

	jQuery.post(
		'/wp-admin/admin-ajax.php',
		{
			'action'  : 'get_teams',
			'league'  : league,
			'user_id' : data.user_id
		},
		function( response ) {
			if ( response.data.html.length > 0 ) 
				jQuery( '#team' ).html( response.data.html ).attr( 'disabled', false);
			else
				jQuery( '#team' ).html( '<option value="">League has no teams</option>' ).attr( 'disabled', true );
		}
	);

}