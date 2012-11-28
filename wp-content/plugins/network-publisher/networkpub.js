jQuery(document).ready(function($){
	$(".networkpubre").bind("click", function(e) {
		$("#networkpub_remove").html('<div class="updated fade" style="padding:5px;text-align:center">Removing...</div>')
		var key = $(this).attr("id");
		$(this).parent().parent().css('opacity','.30');
		if(key) {
			var blog_url = $("#networkpub_plugin_url").val();
			$.post(blog_url+"networkpub_ajax.php", {action:"networkpub_remove", networkpub_key:key}, function(data) {
				if(data != key) {
					$("#networkpub_remove").html('<div class="updated fade" style="padding:5px;text-align:center"><div>Following error occured while removing the API Key - '+data+'</div><div>As a workaround, you can remove this publishing at the following link: <a href="http://www.linksalpha.com/user/publish">LinksAlpha Publisher</a></div></div>');
				} else {
					var dr = data.split("_");
					$("#r_key_"+dr[1]).remove();
					$("#networkpub_remove").html('<div class="updated fade" style="padding:5px;text-align:center">API Key has been removed successfully</div>')
				}
		    });
		} 
		return false;
	});
	
	$.receiveMessage(
		function(e){
			$("#networkpub_postbox").height(e.data.split("=")[1]+'px');
		},
		'http://www.linksalpha.com'
	);
})