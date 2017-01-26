(function( $ ) {
	'use strict';
	$( document ).ready( function () {
		function get_post_type_fields() {
			$( '.post-type-validate' ).change( function () {
				if ( $( '.post-type-validate:checked' ).length ) {
					var is_post_type_verified = false;
					$( ".botamp-post-type" ).each( function() {
						var current_post_type = $( ".botamp-get-list-post-type" ).val();
						current_post_type = current_post_type.replace( / /g, '' );
						if ( current_post_type.search( this.value ) === -1 ) {
							current_post_type += this.value + ",";
						} else {
							is_post_type_verified = true;
						}
						$( ".botamp-get-list-post-type" ).val( current_post_type );
			    	});
			    	$( '.botamp-all-fields' ).each( function() {
				       	var select_parent = $( this ).parent();
				       	var current_field = select_parent.find( ".botamp-get-list-fields" ).val();
				       	current_field = current_field.replace( / /g, '' );
				       	if ( is_post_type_verified === false ) {
				       		if ( ! this.value ) {
								current_field += "post_thumbnail_url,";
							} else {
								current_field += this.value + ",";
							}
				       	}
				       	select_parent.find( ".botamp-get-list-fields" ).val( current_field );
				    } );
				}
			}).change();
		}
		function verified_post_type_fields() {
			var is_post_type_verified = false, is_post_type_position = 0;
			$( ".botamp-post-type" ).each( function() {
				var current_post_type = $( ".botamp-get-list-post-type" ).val();
				current_post_type = current_post_type.replace( / /g, '' );
				if ( current_post_type.search( this.value ) != -1 ) {
					$( ".post-type-validate" ).attr( 'checked' , true );
					is_post_type_verified = true;
					if ( current_post_type.replace( /,$/, '' ).split( "," ).indexOf( this.value ) != -1 ) {
						is_post_type_position = current_post_type.replace( /,$/, '' ).split( "," ).indexOf( this.value );
					}
				}
			});
			$( '.botamp-all-fields' ).each( function() {
		       	var select_parent = $( this ).parent();
		       	var current_field = select_parent.find( ".botamp-get-list-fields" ).val();
		       	current_field = current_field.replace( / /g, '' );
		       	if ( is_post_type_verified === true ) {
		       		var val_selected = current_field.replace( /,$/, '' ).split( "," )[ is_post_type_position ];
		       		select_parent.find( '.botamp-all-fields option[value=' + val_selected + ']' ).attr( "selected","selected" );
		       	} else {
		       		select_parent.find( '.botamp-all-fields option' ).removeAttr( "selected" );
		       	}
		    });
		}
		function remove_post_type_fields() {
			$( '.post-type-validate' ).change( function () {
				if ( ! $( '.post-type-validate:checked' ).length ) {
					var is_post_type_verified = false, is_post_type_position = 0;
					$( ".botamp-post-type" ).each( function() {
						var current_post_type = $( ".botamp-get-list-post-type" ).val();
						current_post_type = current_post_type.replace( / /g, '' );
						if ( current_post_type.search( this.value ) != -1 ) {
							is_post_type_verified = true;
							if ( current_post_type.replace( /,$/, '' ).split( "," ).indexOf( this.value ) != -1 ) {
								is_post_type_position = current_post_type.replace( /,$/, '' ).split( "," ).indexOf( this.value );
							}
							current_post_type = current_post_type.replace( this.value + ',','' );
							$( ".botamp-get-list-post-type" ).val( current_post_type );
						}
					});
					$( '.botamp-all-fields' ).each( function() {
						var select_parent = $( this ).parent();
						var current_field = select_parent.find( ".botamp-get-list-fields" ).val();
						current_field = current_field.replace( / /g, '' );
				       	if ( is_post_type_verified === true ) {
				       		var val_selected = current_field.replace( /,$/, '' ).split( "," )[ is_post_type_position ];
				       		current_field = current_field.replace( val_selected + ',','' );
				       		select_parent.find( ".botamp-get-list-fields" ).val( current_field );
				       		select_parent.find( '.botamp-all-fields option' ).removeAttr( "selected" );
				       	}
				    });
				}
			}).change();
		}
		$( ".botamp-post-type" ).change( function () {
			$( ".post-type-validate" ).removeAttr( 'checked' );
			verified_post_type_fields();
			get_post_type_fields();
			remove_post_type_fields();
		}).change();
	});
})(jQuery);
