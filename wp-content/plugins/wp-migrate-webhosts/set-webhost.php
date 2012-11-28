<?php

if (isset($wp_webhosts)) {
	if (defined('DB_NAME') || defined('DB_USER') || defined('DB_PASSWORD') || defined('DB_HOST')) {
		header('Content-Type:text/plain');
		echo 'You are including webhosts.php from the WP Migrate Webhosts plugin in /wp-config.php but you have also defined  DB_NAME, DB_USER, DB_PASSWORD and/or DB_HOST in /wp-config.php.';
		echo "\n";
		echo 'You cannot define DB_NAME, DB_USER, DB_PASSWORD or DB_HOST in /wp-config.php when using this module.';
		exit;
	} else if (count(get_webhost_defaults())==0) {
		header('Content-Type:text/plain');
		echo 'You are including webhosts.php from the WP Migrate Webhosts plugin in /wp-config.php but have not called register_webhost_defaults() to define the defaults for your webhost.';
		echo "\n";
		echo 'See readme.txt in the WP Migrate Webhosts plugin directory for instructions.';
		exit;
	} else {
		initialize_current_webhost();

		global $wp_webhosts;  // Use the $wp_webhosts global var with "->current" property to avoid adding another variable to the global namespace

		define('DB_NAME',     $wp_webhosts->current->database);
		define('DB_USER',     $wp_webhosts->current->user);
		define('DB_PASSWORD', $wp_webhosts->current->password);
		define('DB_HOST',     $wp_webhosts->current->host);

		define('WP_SITEURL',  $wp_webhosts->current->siteurl);
		define('WP_HOME',     $wp_webhosts->current->home);
	}
}
