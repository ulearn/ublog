<?php

// This example shows how to use an action during migration

add_action('migrate_webhosts','migrate_google_api_key',10,2);
function migrate_google_api_key($from_webhost,$to_webhost) {
	/*
	  Specifically this example shows how to migrate a Google API Key
	  for the Google Maps for WordPress plugin: http://wordpress.org/extend/plugins/google-maps-for-wordpress/
	*/
	update_option('wpGoogleMaps_api_key',$to_webhost->google_api_key);
}
