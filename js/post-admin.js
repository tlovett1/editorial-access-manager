( function( $, undefined ) {

	var $rolesSelect = $( document.getElementById( 'eam_allowed_roles' ) );
	var $usersSelect = $( document.getElementById( 'eam_allowed_users' ) );
	var $accessControlEnableCheckbox = $( document.getElementById( 'eam_enable_custom_access' ) );
	var $accessControls = $( document.getElementById( 'eam_custom_access_controls' ) );

	$rolesSelect.chosen( {
		disable_search_threshold: 99,
		width: '90%'
	});

	$usersSelect.chosen( {
		width: '90%'
	});

	/*$accessControlEnableCheckbox.on( 'change', function() {
		if ( $accessControls.is( ':visible' ) ) {
			$accessControls.hide();
		} else {
			$accessControls.show();
		}
	} );*/

})( jQuery );