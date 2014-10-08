( function( $, undefined ) {

	'use strict';

	var $rolesSelect = $( document.getElementById( 'eam_allowed_roles' ) );
	var $usersSelect = $( document.getElementById( 'eam_allowed_users' ) );
	var accessControlSelect = document.getElementById( 'eam_enable_custom_access' );
	var roleControls = document.getElementById( 'eam_control_roles' );
	var usersControls = document.getElementById( 'eam_control_users' );

	$rolesSelect.chosen( {
		width: '90%'
	});

	$usersSelect.chosen( {
		width: '90%'
	});

	function conditionallyRevealControls() {
		if ( accessControlSelect.value === 'users' ) {
			usersControls.style.display = 'block';
			roleControls.style.display = 'none';
		} else if ( accessControlSelect.value === 'roles' ) {
			usersControls.style.display = 'none';
			roleControls.style.display = 'block';
		} else {
			usersControls.style.display = 'none';
			roleControls.style.display = 'none';
		}
	}

	conditionallyRevealControls();

	accessControlSelect.onchange = conditionallyRevealControls;

})( jQuery );