<?php
/*
Plugin Name: WP Migrate Webhosts
Plugin URI: http://mikeschinkel.com/wordpress-plugins/wp-migrate-webhosts/
Description: Supports migration of webhosts from one domain/server to the next, i.e. staging through live website.
Version: 0.6
Author: Mike Schinkel
Author URI: http://mikeschinkel.com
*/

define('WP_MIGRATE_WEBHOSTS_VERSION','0.6');

require_once(ABSPATH . 'wp-admin/includes/post.php');

global $wp_webhosts;

class WP_MigrateWebhosts {
	static $webhosts;
	static function on_load() {
		add_action('admin_init',array(__CLASS__,'admin_init'));
		add_action('admin_menu',array(__CLASS__,'admin_menu'));
		add_action('template_redirect',array(__CLASS__,'template_redirect'));
		add_action('migrate_webhosts',array(__CLASS__,'migrate_core'));
		add_action('admin_action_migrate-webhosts',array(__CLASS__,'admin_action_migrate_webhosts'));
		add_action('wp_ajax_migrate_webhosts',array(__CLASS__,'migrate_webhosts'));
	}

	static function admin_init() {
		if (is_admin() && $_GET['action']=='migrate-webhosts') {
			wp_enqueue_script('wp-migrate-webhosts-scripts',plugins_url('/js/wp-migrate-webhosts-scripts.js',__FILE__),array('jquery'),WP_MIGRATE_WEBHOSTS_VERSION);
			wp_enqueue_style( 'wp-migrate-webhosts-styles',plugins_url('/css/wp-migrate-webhosts-styles.css',__FILE__),array(),WP_MIGRATE_WEBHOSTS_VERSION);
		}
	}

	static function migrate_webhosts() {
		global $wp_webhosts;
		$from_webhosts =  self::get_from_http_POST('from_webhosts');
		$to_webhost =     self::get_from_http_POST('to_webhost');
		foreach($from_webhosts as $from_webhost) {
			self::migrate_webhost($wp_webhosts->list[$from_webhost],$wp_webhosts->list[$to_webhost]);
		}
		echo json_encode(array(
			'result' => 'success',
		));
		die();
	}

	static function get_from_http_POST($key,$required=true) {
		if ($required && !isset($_POST[$key])) {
			echo json_encode(array(
				'result' => 'ERROR: Required key for $_POST['.$key.'] not posted by client browser.',
			));
			die();
		}
		return $_POST[$key];
	}

	static function admin_action_migrate_webhosts() {
		require_once(dirname(__FILE__) . '/migrate-webhosts.php');
	}

	static function admin_menu() {
		add_submenu_page(
			'tools.php',
			'Migrate Webhosts',
			'Migrate Webhosts',
			$capability = 10,
			self::get_base_admin_path());
	}
	static function template_redirect() {
		list($url_path) = explode('?',$_SERVER['REQUEST_URI']);
		if (trim($url_path,'/')=='wp-migrate-webhosts') {
			wp_safe_redirect(admin_url(self::get_base_admin_path()));
		}
	}

	static function get_current_webhost() {
		global $wp_webhosts;
		return $wp_webhosts->current;
	}

	static function get_base_admin_path() {
		return 'admin.php?action=migrate-webhosts';
	}

	static function get_webhosts_checkboxes_html($args=array()) {
		global $wp_webhosts;
		$args = wp_parse_args($args,array(
			'select' => 'others',   // 'others' or 'current'
		));
		extract($args);
		$current_key = $wp_webhosts->current->key;
		$html = array();
		$webhosts_list = $wp_webhosts->list;

		foreach(array_keys($webhosts_list) as $key)
			if ($key[0]=='_')
				unset($webhosts_list[$key]);

		$html[] = '<ul>';
		foreach($webhosts_list as $key => $webhost) {
			$skip = false;
			$which = '';
			if ($select=='others' && $key!=$current_key) {
				$extra = (count($webhosts_list)<=2 ? 'other from checked disabled' : '');
			} else if ($select=='current' && $key==$current_key) {
				$extra = 'class="current to" checked disabled';
				$which = 'this_';
			} else {
				$skip = true;
			}
			if (!$skip) {
				$html[] = <<<HTML
<li><p><input type="checkbox" name="{$which}webhost" value="{$key}" $extra/>&nbsp;<span class="domain">{$webhost->domain}</span></p><p class="name">{$webhost->name}</p></li>
HTML;
			}
		}
		$html[] = '</ul>';

		return implode("\n",$html);
	}


	static function migrate_webhost($from_webhost,$to_webhost) {
		$migration_funcs = self::get_webhost_migration_funcs();
		foreach($migration_funcs as $func) {
			$func_name = $func['function'];
			call_user_func($func_name,$from_webhost,$to_webhost);
		}
	}

	static function get_webhost_migration_funcs() {
		global $wp_filter;
		$all_funcs = array();
		if (isset($wp_filter['migrate_webhosts'])) {
			foreach($wp_filter['migrate_webhosts'] as $funcs) {
				$all_funcs = array_merge($all_funcs,$funcs);
			}
		}
		return $all_funcs;
	}
	static function migrate_twentyten_header($from_webhost,$to_webhost,$theme_name='Twenty Ten') {
		$header = (array)get_option("mods_$theme_name",array('header_image'=>''));
		if ($theme_name=='Twenty Ten' || isset($header['header_image'])) {
			$header['header_image'] = preg_replace("#{$from_webhost->siteurl}/(.*)#","{$to_webhost->siteurl}/$1",$header['header_image']);
			update_option("mods_$theme_name",$header);
		}
	}
	static function migrate_twentyten_themes($from_webhost,$to_webhost) {
		self::migrate_twentyten_header($from_webhost,$to_webhost);
		self::migrate_twentyten_header($from_webhost,$to_webhost,get_current_theme());
	}
	static function migrate_core($from_webhost,$to_webhost,$args=array()) {
		$args = wp_parse_args($args,array(
			'twentyten_theme' => true,
			'siteurl'         => true,
			'home'            => true,
			'options_url'     => true,
			'options_dir'     => true,
			'posts_guid'      => true,
			'posts_content'   => true,
			'posts_excerpt'   => true,
			'postmeta_url'    => true,
			'postmeta_dir'    => true,
		));
		extract($args);

		if ($twentyten_theme)
			self::migrate_twentyten_themes($from_webhost,$to_webhost);

		if ($siteurl)
			self::force_update_option('siteurl',esc_url_raw($to_webhost->siteurl));

		if ($home)
			self::force_update_option('home',esc_url_raw($to_webhost->siteurl));

		if ($options_url)
			self::migrate_webhost_via_sql($from_webhost,$to_webhost,array(
				'table' => 'options',
				'field' => 'option_value',
				'from'  => $from_webhost->siteurl,
				'to'    => $to_webhost->siteurl,
			));

		if ($options_dir)
			self::migrate_webhost_via_sql($from_webhost,$to_webhost,array(
				'table' => 'options',
				'field' => 'option_value',
				'from'  => $from_webhost->rootdir,
				'to'    => $to_webhost->rootdir,
			));

		if ($posts_guid)
			self::migrate_webhost_via_sql($from_webhost,$to_webhost,array(
				'table' => 'posts',
				'field' => 'guid',
				'from'  => $from_webhost->siteurl,
				'to'    => $to_webhost->siteurl,
			));

		if ($posts_content)
			self::migrate_webhost_via_sql($from_webhost,$to_webhost,array(
				'table' => 'posts',
				'field' => 'post_content',
				'from'  => $from_webhost->siteurl,
				'to'    => $to_webhost->siteurl,
				'begins_with'=> false,
			));

		if ($posts_excerpt)
			self::migrate_webhost_via_sql($from_webhost,$to_webhost,array(
				'table' => 'posts',
				'field' => 'post_excerpt',
				'from'  => $from_webhost->siteurl,
				'to'    => $to_webhost->siteurl,
				'begins_with'=> false,
			));

		if ($postmeta_url)
			self::migrate_webhost_via_sql($from_webhost,$to_webhost,array(
				'table' => 'postmeta',
				'field' => 'meta_value',
				'from'  => $from_webhost->siteurl,
				'to'    => $to_webhost->siteurl,
			));

		if ($postmeta_dir)
			self::migrate_webhost_via_sql($from_webhost,$to_webhost,array(
				'table' => 'postmeta',
				'field' => 'meta_value',
				'from'  => $from_webhost->rootdir,
				'to'    => $to_webhost->rootdir,
			));

	}
	static function error_die($msg) {
		header('Content-Type:text/plain');
		echo $msg;
		die();
	}
	static function force_update_option($key,$value) {
		global $wpdb;
		$wpdb->update( $wpdb->options,array('option_value'=>$value),array('option_name'=>$key));
	}
	static function migrate_webhost_via_sql($from_webhost,$to_webhost,$args=array()) {
		global $wpdb;
		$args = wp_parse_args($args,array(
			'table'       => 'postmeta',
			'field'       => 'meta_value',
			'from'        => $from_webhost->domain,
			'to'          => $to_webhost->domain,
			'begins_with' => true,
			'contains'    => true,
		));
		extract($args);

		if ($begins_with) {
			$start = strlen($from)+1;
			$sql =<<<SQL
UPDATE {$wpdb->$table}
SET $field = CONCAT('{$to}',SUBSTRING($field,$start,LENGTH($field)))
WHERE $field LIKE '{$from}%'
SQL;
			$wpdb->query($sql);
		}

		if ($contains) {
			$sql =<<<SQL
UPDATE {$wpdb->$table}
SET $field = REPLACE($field,'{$from}','{$to}')
WHERE $field LIKE '%{$from}%'
SQL;
			$wpdb->query($sql);
		}
	}
}
WP_MigrateWebhosts::on_load();

