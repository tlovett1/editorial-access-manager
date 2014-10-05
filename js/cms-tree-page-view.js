/* global eam_cms_tpv */
( function( $, undefined ) {
	$( 'div.cms_tpv_container' ).bind( 'clean_node.jstree', function( event, data ) {
		var obj = ( data.rslt.obj );
		if ( obj && obj != -1 ) {
			obj.each(function( i, elm ) {
				var columns = $( elm ).data( 'columns' );
				if ( columns ) {
					var eam_text = $( 'dt:contains(' + eam_cms_tpv.column_title + ')', $.parseHTML( columns ) ).closest( 'dl' ).children( 'dd' ).text();
					console.log(eam_text);
					if ( eam_text != eam_cms_tpv.off_text && eam_text != '' ) {
						$( elm ).find( 'a:first' ).find( 'ins' ).first().after( '<span class="post_protected">&nbsp;</span>');
					}
				}
			});
		}
	});
})( jQuery );
