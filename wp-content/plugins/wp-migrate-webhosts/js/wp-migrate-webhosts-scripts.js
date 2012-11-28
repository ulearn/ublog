/*
 Filename: wp-migrate-webhosts-scripts.js
 */
jQuery(document).ready(function($) {
	$("#migrate-webhosts").click(function(){
		var gif = $("#working-gif").addClass("working");
		var toWebHost = $("#migrate-to input[type=checkbox]").val();
		var fromWebHosts = new Array();
		$("#migrate-from input[type=checkbox]").each(function() {
			fromWebHosts.push($(this).val());
		});
		var data = {
			action: 'migrate_webhosts',
			from_webhosts: fromWebHosts,
			to_webhost: toWebHost
		};
		$.post(ajaxurl, data, function(response) {
			//alert(response);
			response = eval('(' + response + ')');
			if (response.result!='success') {
				alert('ERROR! Got this from the server: ' + response.result);
			} else {
				alert('Migration Complete!');
				$("#working-gif").removeClass("working");
			}
		});
		return false;
	});
});