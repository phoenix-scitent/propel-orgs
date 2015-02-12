jQuery( document ).ready( function() {

	jQuery( '#import-orgs' ).on( 'click', function ( e ) {
		e.preventDefault();

		jQuery.post(
			'/wp-admin/admin-ajax.php',
			{
				'action' : 'import_propel_orgs'
			},
			function ( response ) {
				console.log( response );
				return;
				location.reload();
			}
		);

	} );

} );