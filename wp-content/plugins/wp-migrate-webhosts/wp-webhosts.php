<?php

global $wp_webhosts;
$wp_webhosts = new WP_WebHosts();

class WP_WebHosts {
	var $defaults;
	var $current;
	var $aliases = array();
	var $index = array();
	var $list = array();
	function __construct() {
		$this->WP_WebHosts();
	}
	function WP_WebHosts() {
	}
	static function error_die($msg) {
		header('Content-Type:text/plain');
		echo $msg;
		die();
	}
}
function get_webhost_defaults() {
	global $wp_webhosts;
	return $wp_webhosts->defaults;
}
function register_webhost_defaults($defaults) {
	global $wp_webhosts;
	$wp_webhosts->index = array();
	$wp_webhosts->defaults = (object)array_merge(array(
		'name'      => 'Default Configuration',
		'domain'    => 'www.example.com',
		'alias'     => 'example.com',
		'database'  => 'wordpress',
		'user'      => 'user',
		'password'  => 'password',
		'host'      => 'localhost',
		'rootdir'   => '/',
		'sitepath'  => '',
	),$defaults);
}
function register_webhost($key,$webhost) {
	global $wp_webhosts;

	$webhost = (object)array_merge((array)$wp_webhosts->defaults,$webhost);

	$webhost->key = $key;
	$wp_webhosts->list[$key] = &$webhost;

	register_webhost_alias($key,$webhost->domain,$webhost); // The first aliase is itself

	if (!isset($webhost->alias) || empty($webhost->alias))
		$webhost->alias = array();
	else if (is_string($webhost->alias))
		$webhost->alias = array($webhost->alias);

	if (!is_array($webhost->alias))
		WP_WebHosts::error_die("Alias passed but is not a string nor is it an array. Must terminate.");
	else
		foreach($webhost->alias as $alias)
			register_webhost_alias($key,$alias);

	$webhost->domain = trim($webhost->domain,'/');
	$webhost->sitepath = trim($webhost->sitepath,'/');
	$webhost->rootdir = trim($webhost->rootdir,'/');

	if (!isset($webhost->homepath))
		$webhost->homepath = $webhost->sitepath;
	$webhost->homepath = trim($webhost->homepath,'/');

	if (!isset($webhost->siteurl))
		$webhost->siteurl = "http://{$webhost->domain}/{$webhost->sitepath}";
	$webhost->siteurl = trim($webhost->siteurl,'/');

	if (!isset($webhost->home))
		$webhost->home = "http://{$webhost->domain}/{$webhost->homepath}";
	$webhost->home = trim($webhost->home,'/');

	return $webhost;

}
function register_webhost_alias($key,$domain,&$webhost=false) {
	global $wp_webhosts;
	if (!$webhost)
		$webhost = &$wp_webhosts->list[$key];
	$wp_webhosts->aliases[$key][$domain] = &$webhost;
	$wp_webhosts->index[$domain] = &$webhost;
}
function get_current_webhost() {
	global $wp_webhosts;
	return $wp_webhosts->current;
}

function set_current_webhost(&$webhost) {
	global $wp_webhosts;
	$wp_webhosts->current = &$webhost;
}

function initialize_current_webhost() {
	global $wp_webhosts;
	$this_domain = $_SERVER['SERVER_NAME'];

	if (!isset($wp_webhosts->index[$this_domain]))
		WP_WebHosts::error_die("The domain $this_domain has not been registered with register_webhost() in /wp-config.php so WordPress must terminate.");

	$wp_webhosts->current = $wp_webhosts->index[$this_domain];
}
