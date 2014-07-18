( function( $, undefined ) {

	var $rolesSelect = $( document.getElementById( 'eam_allowed_roles' ) );
	var $usersSelect = $( document.getElementById( 'eam_allowed_users' ) );
	var $accessControlSelect = $( document.getElementById( 'eam_enable_custom_access' ) );
	var $roleControls = $( document.getElementById( 'eam_control_roles' ) );
	var $usersControls = $( document.getElementById( 'eam_control_users' ) );

	$rolesSelect.chosen( {
		width: '90%'
	});

	$usersSelect.chosen( {
		width: '90%'
	});

	function conditionallyRevealControls() {
		if ( $accessControlSelect.val() == 'users' ) {
			$usersControls.show();
			$roleControls.hide();
		} else if ( $accessControlSelect.val() == 'roles' ) {
			$usersControls.hide();
			$roleControls.show();
		} else {
			$usersControls.hide();
			$roleControls.hide();
		}
	}

	conditionallyRevealControls();

	$accessControlSelect.on( 'change', conditionallyRevealControls );

})( jQuery );