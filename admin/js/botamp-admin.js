(function( $ ) {
	'use strict';
	$( document ).ready( function () {
		$( 'tr.botamp-content-post-type' ).removeAttr( 'class' );
		$( '.botamp-post-type' ).change( function () {
			show_fields_this_post_type();
		}).change();
		function show_fields_this_post_type() {
			$( 'div.botamp-content-mapping' ).find( 'table' ).css( "display", "none" );
			$( ".botamp-post-type" ).each( function() {
				$('div.botamp-content-mapping').find('#botamp-form-table-'+this.value).css("display", "block");
	    	});
		}
	});
})(jQuery);
