(function( $ ) {
	'use strict';
	$(document).ready(function () {
		function get_post_type_fields() {
			$('.post-type-validate').change(function () {
				if ($('.post-type-validate:checked').length) {
					$(".botamp-post-type").each(function() {
						var current_post_type = $(".botamp-get-list-post-type").val();
						current_post_type += this.value + ",";
						$(".botamp-get-list-post-type").val(current_post_type);
			    	});
			    	$('.botamp-all-fields').each(function() {
				       	var select_parent = $(this).parent();
				       	var current_field = select_parent.find(".botamp-get-list-fields").val();
						if (!this.value) {
							current_field += "post_thumbnail_url,";
						}else{
							current_field += this.value + ",";
						}
				       	select_parent.find(".botamp-get-list-fields").val(current_field);
				       	$(".botamp-show-user-fields").text($(".botamp-get-list-fields").val());
				    });  
				}
			}).change();
		}
		$(".botamp-post-type").change(function () {
		    $(".botamp-display-checkbox").html('</br><input class="post-type-validate" type="checkbox"/>');  
			get_post_type_fields();
		}).change();
	});
})(jQuery);
