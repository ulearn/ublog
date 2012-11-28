<?php
/*
Plugin Name: Network Publisher
Plugin URI: http://wordpress.org/extend/plugins/network-publisher/
Description: Automatically publish your blog posts to multiple Social Networks including Twitter, Facebook Profile, Facebook Pages, LinkedIn, MySpace, Yammer, Yahoo, Identi.ca, and <a href="http://www.linksalpha.com/user/networks" target="_blank">more</a>. Click <a href="http://help.linksalpha.com/wordpress-plugin-network-publisher">here</a> for instructions. Email us at post@linksalpha.com if you have any queries.
Version: 4.2.1
Author: LinksAlpha
Author URI: http://www.linksalpha.com
*/

/*
    Copyright (C) 2010 LinksAlpha.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('NETWORKPUB_WP_PLUGIN_URL', 					networkpub_get_plugin_dir());
define('NETWORKPUB_WIDGET_NAME', 					'Network Publisher');
define('NETWORKPUB_WIDGET_NAME_INTERNAL', 			'networkpub');
define('NETWORKPUB_WIDGET_NAME_POSTBOX', 			'Postbox');
define('NETWORKPUB_WIDGET_NAME_POSTBOX_INTERNAL', 	'networkpubpostbox');
define('NETWORKPUB_WIDGET_PREFIX', 					'networkpub');
define('NETWORKPUB', 								__('Automatically publish your blog posts to 30+ Social Networks including Facebook, Twitter, LinkedIn, Yahoo, Yammer, MySpace, Identi.ca'));
define('NETWORKPUB_ERROR_INTERNAL', 				'internal error');
define('NETWORKPUB_ERROR_INVALID_URL', 				'invalid url');
define('NETWORKPUB_ERROR_INVALID_KEY', 				'invalid key');
define('NETWORKPUB_CURRENTLY_PUBLISHING',        	__('You are currently Publishing your Blog to'));
define('NETWORKPUB_SOCIAL_NETWORKS',        		__('Social Networks'));
define('NETWORKPUB_SOCIAL_NETWORK',        			__('Social Network'));
define('NETWORKPUB_PLUGIN_VERSION', 				'4.2.1');

$networkpub_settings['api_key'] = 	array('label'=>'API Key:', 'type'=>'text', 'default'=>'');
$networkpub_settings['id'] = 		array('label'=>'id', 'type'=>'text', 'default'=>'');
$options = 							get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);


function networkpub_init() {
	if ( is_admin() ) {
		wp_enqueue_style('thickbox');
		wp_enqueue_script('jquery');
		wp_enqueue_script('thickbox');
		wp_register_script('networkpubjs', NETWORKPUB_WP_PLUGIN_URL .'networkpub.js');
		wp_enqueue_script('networkpubjs');
		wp_register_script('postmessagejs', NETWORKPUB_WP_PLUGIN_URL .'jquery.ba-postmessage.min.js');
		wp_enqueue_script('postmessagejs');
		wp_register_style('networkpubcss', NETWORKPUB_WP_PLUGIN_URL . 'networkpub.css');
		wp_enqueue_style('networkpubcss');
		add_action('admin_menu', 'networkpub_pages');
		add_action('activate_{$plugin}', 'networkpub_pushpresscheck');
		add_action("activated_plugin", "networkpub_pushpresscheck");
		register_deactivation_hook( __FILE__, 'networkpub_deactivate' );
	}
}

add_action('init', 'networkpub_init');
register_activation_hook( __FILE__, 'networkpub_activate' );
add_action('admin_notices', 'networkpub_warning');
add_action('init', 'networkpub_remove');
add_action('{$new_status}_{$post->post_type}', 'networkpub_ping');
add_action('publish_post', 'networkpub_ping');
add_action('future_to_publish', 'networkpub_ping');

add_action('xmlrpc_publish_post', 'networkpub_post_xmlrpc');
add_action('{$new_status}_{$post->post_type}', 'networkpub_post');
add_action('publish_post', 'networkpub_post');
add_action('future_to_publish', 'networkpub_post');

add_action('{$new_status}_{$post->post_type}', 'networkpub_convert');
add_action('publish_post', 'networkpub_convert');
add_action('future_to_publish', 'networkpub_convert');

add_action('admin_menu', 'networkpub_create_post_meta_box' );
add_action('save_post', 'networkpub_save_post_meta_box', 5, 2 );
add_action('save_post', 'networkpub_post_publish_status', 4, 2 );


function networkpub_create_post_meta_box() {
	add_meta_box( 'networkpub_meta_box', 'Network Publisher', 'networkpub_post_meta_box', 'post', 'side', 'high' );
}


function networkpub_post_meta_box( $object, $box ) {
	//Publish
	$curr_val_publish = get_post_meta( $object->ID, '_networkpub_meta_publish', true );
	if($curr_val_publish == '') {
		$curr_val_publish = 1;
	}
    $html  = '<div style="padding:2px;">';
	if($curr_val_publish) {
		$html .= '<input type="checkbox" name="networkpub_meta_cb_publish" id="networkpub_meta_cb_publish" checked />';
	} else {
		$html .= '<input type="checkbox" name="networkpub_meta_cb_publish" id="networkpub_meta_cb_publish" />';
	}
	$html .= '&nbsp;<label for="networkpub_meta_cb_publish">Publish this Post to <a href="plugins.php?page=networkpub">configured Networks</a></label>';
	$html .= '</div>';
	//Content
	$curr_val_content = get_post_meta( $object->ID, '_networkpub_meta_content', true );
	if($curr_val_content == '') {
		$curr_val_content = 0;
	}
	$html .= '<div style="padding:2px;">';
	if($curr_val_content) {
		$html .= '<input type="checkbox" name="networkpub_meta_cb_content" id="networkpub_meta_cb_content" checked />';
	} else {
		$html .= '<input type="checkbox" name="networkpub_meta_cb_content" id="networkpub_meta_cb_content" />';
	}
	$html .= '&nbsp;<label for="networkpub_meta_cb_content">Use Excerpt for publishing to Networks</label>';
	//Hidden
	$html .= '<input type="hidden" name="networkpub_meta_nonce" value="'. wp_create_nonce( plugin_basename( __FILE__ ) ).'" />';
	$html .= '</div>';
	echo $html;
}


function networkpub_save_post_meta_box( $post_id, $post ) {
	if ( !wp_verify_nonce( $_POST['networkpub_meta_nonce'], plugin_basename( __FILE__ ) ) ) {
		return $post_id;	
	}
	if ( !current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	//Publish
	if($_POST['networkpub_meta_cb_publish']) {
		$new_meta_value_publish = 1;
	} else {
		$new_meta_value_publish = 0;
	}
	update_post_meta( $post_id, '_networkpub_meta_publish', $new_meta_value_publish );
	//Content
	if($_POST['networkpub_meta_cb_content']) {
		$new_meta_value_content = 1;
	} else {
		$new_meta_value_content = 0;
	}
	update_post_meta( $post_id, '_networkpub_meta_content', $new_meta_value_content);
}


function networkpub_post_publish_status($post_id, $post) {
	add_post_meta( $post_id, '_networkpub_meta_published', 'new', true);
}


function networkpub_warning() {
	$options = get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);
	if(empty($options['api_key'])) {
		if (!isset($_POST['submit'])) {
			echo "
			<div class='updated fade' style='width:80%;'>
				<p>
					<strong>".__('<a href="http://wordpress.org/extend/plugins/network-publisher/" target="_blank">Network Publisher</a> plugin is almost ready.')."</strong> ".
					sprintf(__('You must <a href="%1$s">enter API key</a> (under Plugins->Network Publisher) for automatic posting of your blog articles to 30+ Social Networks including Twitter, Facebook Profile, Facebook Pages, LinkedIn, MySpace, Yammer, Yahoo, Identi.ca, etc. to work.'),
					"plugins.php?page=networkpub")."
				</p>
			</div>
			";
		}
	}
}


function networkpub_ping($id) {
	if(!$id) {
		return;
	}
	$options = get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);
	if(empty($options['id']) or empty($options['api_key'])) {
		return;
	}
	$link = 'http://www.linksalpha.com/a/ping?id='.$options['id'];
	$response_full = networkpub_http($link);
	return;
}


function networkpub_convert($id) {
	if(!$id) {
		return;
	}
	$options = get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);
	if(!empty($options['id_2'])) {
		return;
	}
	if(empty($options['id']) or empty($options['api_key'])) {
		return;
	}
	// Build Params
	$link = 'http://www.linksalpha.com/a/networkpubconvert';
	$params = array('id'=>$options['id'],
					'api_key'=>$options['api_key'],
					'plugin'=>'nw',
					);
	//HTTP Call
	$response_full = networkpub_http_post($link, $params);
	$response_code = $response_full[0];
	if ($response_code != 200) {
		return;
	}
	$response = networkpub_json_decode($response_full[1]);
	if ($response->errorCode > 0) {
		return;
	}
	//Update options
	$options['id_2'] = $response->results;
	//Save
	update_option(NETWORKPUB_WIDGET_NAME_INTERNAL, $options);
	return;
}


function networkpub_post($post_id) {
	//Get options
	$options = get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);
	if(!is_array($options)) {
		return;
	}
	//Publishing enabled?
	if(array_key_exists('networkpub_enable', $options)) {
		$networkpub_enable_value = $options['networkpub_enable'];
	} else {
		$networkpub_enable_value = 1;
	}
	if(!$networkpub_enable_value) {
		return;
	}
	//Network keys
	if (empty($options['api_key']) or empty($options['id_2'])) {
		return;
	}
	$id = $options['id_2'];
	$api_key = $options['api_key'];
	//Post Published?
	$post_data = get_post( $post_id, ARRAY_A );
	if(!in_array($post_data['post_status'], array('future', 'publish'))) {
		return;	
	}
	//Post meta - networkpub_meta_publish
	$networkpub_meta_publish = get_post_meta( $post_id, '_networkpub_meta_publish', true );
	if($networkpub_meta_publish == "") {
	} elseif ($networkpub_meta_publish == 0) {
		return;
	}
	//Post meta - networkpub_meta_published
	$networkpub_meta_published = get_post_meta( $post_id, '_networkpub_meta_published', true );
	if($networkpub_meta_published == 'done') {
		return;
	}
	//Post meta - networkpub_meta_content
	$networkpub_meta_content = get_post_meta( $post_id, '_networkpub_meta_content', true );
	//Post data: id, content and title
	$post_title = $post_data['post_title'];
	if($networkpub_meta_content) {
		$post_content = $post_data['post_excerpt'];
	} else {
		$post_content = $post_data['post_content'];
	}
	//Post data: Permalink
	$post_link = get_permalink($post_id);
	//Post data: Categories
	$post_categories_array = array();
	$post_categories_data = get_the_category( $post_id );
	foreach($post_categories_data as $category) {
		$post_categories_array[] = $category->cat_name;
	}
	$post_categories = implode(",", $post_categories_array);
	//Post tags
	$post_tags_array = array();
	$post_tags_data = wp_get_post_tags( $post_id );
	foreach($post_tags_data as $tag) {
		$post_tags_array[] = $tag->name;
	}
	$post_tags = implode(",", $post_tags_array);
	//Post Geo
	if(function_exists('get_wpgeo_latitude')) {
		if(get_wpgeo_latitude( $post_id ) and get_wpgeo_longitude( $post_id )) {
			$post_geotag = get_wpgeo_latitude( $post_id ).' '.get_wpgeo_longitude( $post_id );
		}
	}
	if(!isset($post_geotag)) {
		$post_geotag = '';
	}
	// Build Params
	$link = 'http://www.linksalpha.com/a/networkpubpost';
	$params = array('id'=>$id,
					'api_key'=>$api_key,
					'post_id'=>$post_id,
					'post_link'=>$post_link,
					'post_title'=>$post_title,
					'post_content'=>$post_content,
					'plugin'=>'nw',
					'plugin_version'=>networkpub_version(),
					'post_categories'=>$post_categories,
					'post_tags'=>$post_tags,
					'post_geotag'=>$post_geotag
					);
	//Featured Image
	$post_image = networkpub_thumbnail_link( $post_id );
	if($post_image) {
		$params['post_image'] = $post_image;
	}
	//HTTP Call
	$response_full = networkpub_http_post($link,$params);
	$response_code = $response_full[0];
	if($response_code == 200) {
		update_post_meta( $post_id, '_networkpub_meta_published', 'done' );
	}
	return;
}


function networkpub_post_xmlrpc($post_id) {
	//Get options
	$options = get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);
	if(!is_array($options)) {
		return;
	}
	//Publishing enabled?
	if(array_key_exists('networkpub_enable', $options)) {
		$networkpub_enable_value = $options['networkpub_enable'];
	} else {
		$networkpub_enable_value = 1;
	}
	if(!$networkpub_enable_value) {
		return;
	}
	//Network keys
	if (empty($options['api_key']) or empty($options['id_2'])) {
		return;
	}
	$id = $options['id_2'];
	$api_key = $options['api_key'];
	//Post Published?
	$post_data = get_post( $post_id, ARRAY_A );
	if(!in_array($post_data['post_status'], array('future', 'publish'))) {
		return;	
	}
	//Post meta - networkpub_meta_published
	$networkpub_meta_published = get_post_meta( $post_id, '_networkpub_meta_published', true );
	if($networkpub_meta_published == 'done') {
		return;
	}
	//Post meta - networkpub_meta_content
	$networkpub_meta_content = get_post_meta( $post_id, '_networkpub_meta_content', true );
	//Post data: id, content and title
	$post_title = $post_data['post_title'];
	if($networkpub_meta_content) {
		$post_content = $post_data['post_excerpt'];
	} else {
		$post_content = $post_data['post_content'];
	}
	//Post data: Permalink
	$post_link = get_permalink($post_id);
	//Post data: Categories
	$post_categories_array = array();
	$post_categories_data = get_the_category( $post_id );
	foreach($post_categories_data as $category) {
		$post_categories_array[] = $category->cat_name;
	}
	$post_categories = implode(",", $post_categories_array);
	//Post tags
	$post_tags_array = array();
	$post_tags_data = wp_get_post_tags( $post_id );
	foreach($post_tags_data as $tag) {
		$post_tags_array[] = $tag->name;
	}
	$post_tags = implode(",", $post_tags_array);
	//Post Geo
	if(function_exists('get_wpgeo_latitude')) {
		if(get_wpgeo_latitude( $post_id ) and get_wpgeo_longitude( $post_id )) {
			$post_geotag = get_wpgeo_latitude( $post_id ).' '.get_wpgeo_longitude( $post_id );
		}
	}
	if(!isset($post_geotag)) {
		$post_geotag = '';
	}
	// Build Params
	$link = 'http://www.linksalpha.com/a/networkpubpost';
	$params = array('id'=>$id,
					'api_key'=>$api_key,
					'post_id'=>$post_id,
					'post_link'=>$post_link,
					'post_title'=>$post_title,
					'post_content'=>$post_content,
					'plugin'=>'nw',
					'plugin_version'=>networkpub_version(),
					'post_categories'=>$post_categories,
					'post_tags'=>$post_tags,
					'post_geotag'=>$post_geotag
					);
	//Featured Image
	$post_image = networkpub_thumbnail_link( $post_id );
	if($post_image) {
		$params['post_image'] = $post_image;
	}
	//HTTP Call
	$response_full = networkpub_http_post($link,$params);
	update_post_meta( $post_id, '_networkpub_meta_published', 'done' );
	$response_code = $response_full[0];
	if($response_code == 200) {
		update_post_meta( $post_id, '_networkpub_meta_published', 'done' );
	}
	return;
}


function networkpub_conf() {
	global $networkpub_settings;
	if ( isset($_POST['submit']) ) {
		if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
			die(__('Cheatin&#8217; uh?'));
		}
		$field_name = sprintf('%s_%s', NETWORKPUB_WIDGET_PREFIX, 'api_key');
		if(array_key_exists($field_name, $_POST)) {
			$value = strip_tags(stripslashes($_POST[$field_name]));
			if($value) {
				$networkadd = networkpub_add($value);
			}	
		} else {
			if(array_key_exists('networkpub_enable', $_POST)) {
				$networkpub_enable_value = 1;	
			} else {
				$networkpub_enable_value = 0;
			}
			networkpub_enable($networkpub_enable_value);
		}
	}
	$options = get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);
	if(is_array($options)) {
		if(array_key_exists('networkpub_enable', $options)) {
			$networkpub_enable = $options['networkpub_enable'];
			if($networkpub_enable) {
				$networkpub_enable = 'checked';	
			} else {
				$networkpub_enable = '';
			}
		} else {
			$networkpub_enable = 'checked';
		}
	} else {
		$networkpub_enable = 'checked';
	}
	$html  = '
			<div id="networkpub_msg"></div>
			<div class="wrap">
				<span><div class="icon32" id="networkpub_laicon"><br /></div><h2>'.NETWORKPUB_WIDGET_NAME.'</h2></span>
			</div>
			<div style="width:76%;float:left;">
				<div style="padding:10px 10px 6px 10px;background-color:#FFFFFF;margin-bottom:15px;margin-top:0px;border:1px solid #F0F0F0;">
					<div style="width:130px;float:left;font-weight:bold;">
						<a href="'.networkpub_postbox_url().'">Postbox</a>&nbsp;&nbsp;|&nbsp;&nbsp;Share
					</div>
					<div style="width:600px">
						<iframe id="websites_iframe" height="25px" width="450px" scrolling="no" frameborder="0px" src="http://www.linksalpha.com/social?link=http%3A//wordpress.org/extend/plugins/network-publisher/"></iframe>
					</div>
				</div>
				<div>
					<div class="networkpub_started">
						<div style="padding:0px 0px 5px 0px;"><b>Network Publisher</b> makes it easy and painless to Publish your Blog Posts to Social Networks. To configure:</div>
						<div><b>1.</b>&nbsp;Connect to your Social Networks at <a target="_blank" href="http://www.linksalpha.com/user/networks">LinksAlpha.com</a></div>
						<div><b>2.</b>&nbsp;Get your <a target="_blank" href="http://www.linksalpha.com/user/your_api_key">User API Key</a> or <a target="_blank" href="http://www.linksalpha.com/user/lists">Networks List API Key</a> and enter it below.</div>
						<div style="padding:5px 0px 0px 0px;">Once setup, your Blog posts content appears on the social networks as soon as you hit the Publish button.</div>
						<div>You can <a href="http://help.linksalpha.com/wordpress-plugin-network-publisher" target="_blank">read more about this process at LinksAlpha.com.</a></div>
					</div>
					<div class="networkpub_header">
						<big><strong>Setup</strong></big>
					</div>
					<div style="padding-left:0px;margin-bottom:40px;">
						<div class="networkpub_content_box">
							<form action="" method="post" id="networkpubadd" name="networkpubadd">
							<fieldset class="rts_fieldset">
								<legend>API Key</legend>';

	$curr_field = 'api_key';
	$field_name = sprintf('%s_%s', NETWORKPUB_WIDGET_PREFIX, $curr_field);
	$html .= 				   '<div>
									<label for="'.$field_name.'">
										Get your <a href="http://www.linksalpha.com/user/your_api_key" target="_blank"><b>User API Key</b></a> or <a target="_blank" href="http://www.linksalpha.com/user/lists"><b>Networks List API Key</b></a> and enter it in the box below.
									</label>
								</div>

								<div style="padding-bottom:10px">
									<input style="width:400px;  border-width:1px;border-color:gray;border-style:solid" class="widefat" id="'.$field_name.'" name="'.$field_name.'" type="text" />
								</div>
								<div>
									You need to get key from LinksAlpha and copy it here. You CANNOT generate your own key.
									<a href="http://help.linksalpha.com/wordpress-plugin-network-publisher" target="_blank">Read more about the simple and quick process here</a>.
								</div>
								<div>
									<br/>
									<span style="color:#d12424;">Getting Errors?</span> See help page <a href="http://help.linksalpha.com/errors" target="_blank">here</a>
								</div>
							</fieldset>
							<div style="padding-top:20px;">
								<input type="submit" name="submit" class="button-primary" value="Add API Key" />
							</div>
							<input type="hidden" value="'.NETWORKPUB_WP_PLUGIN_URL.'" id="networkpub_plugin_url" />
							</form>
						</div>
						<div style="font-size:14px;margin:10px 0px 0px 0px;padding:5px;" id="networkpub_remove">&nbsp;</div>
						<div style="padding:5px 0px 0px 0px;">
							<div class="networkpub_header">
								<big><strong>Currently Publishing</strong></big>
							</div>
							<div class="networkpub_content_box">'.networkpub_load().'</div>
						</div>
						<div style="padding:40px 0px 0px 0px;">
							<div class="networkpub_header">
								<big><strong>Enable/Disable Publishing</strong></big>
							</div>
							<div class="networkpub_content_box">
								<div style="padding-bottom:10px;">
									<form action="" method="post" id="networkpubadd" name="networkpubadd">
										<div>
											<input type="checkbox" id="networkpub_enable" name="networkpub_enable" '.$networkpub_enable.' /><label for="networkpub_enable">&nbsp;&nbsp;Check this box to Enable publishing</label>
										</div>
										<div style="padding-top:5px;">
											<input type="submit" name="submit" class="button-primary" value="Update" />
										</div>
									</form>
								</div>
								<div>
									<div style="padding-bottom:5px;">Notes:</div>
									<ol>
										<li>You should typically use this option when you are making mass updates to your posts to prevent them from getting published to the configured networks</li>
										<li>Deactivating the plugin will not disable publishing. You need to use this checkbox to disable publishing</li>
									</ol>
								</div>
							</div>
						</div>
						<div style="font-size:14px;margin:20px 0px 0px 0px;">
							Note: If you decide to stop using this plugin permanently, pls also remove your blog URL by logging into <a href="http://www.linksalpha.com/publish" target="_blank">http://www.linksalpha.com/publish</a>.Otherwise, your blog posts may continue to get posted even after you remove this plugin.
						</div>
					</div>
				</div>
			</div>
			<div style="float:left;vertical-align:top;padding-left:4%;">
				<div style="margin-bottom:20px;margin-right:25px;text-align:center;">
					<a href="'.networkpub_postbox_url().'" style="text-decoration:none;">
						<div class="networkpub_button_yellow">View Postbox</div>
					</a>
				</div>
				<div class="networkpub_header"><big><strong>Supported Networks</strong></big></div>
				<div class="la_content_box_3">
					'.networkpub_supported_networks().'
				</div>
			</div>';
	echo $html;
}


function networkpub_add($api_key) {
	if (!$api_key) {
		$errdesc = networkpub_error_msgs('invalid key');
		echo $errdesc;
		return;
	}
	$url = get_bloginfo('url');
	if (!$url) {
		$errdesc = networkpub_error_msgs('invalid url');
		echo $errdesc;
		return;
	}
	$desc = get_bloginfo('description');
	$options = get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);
	if(!empty($options['id'])) {
		$id = $options['id'];
	} elseif (!empty($options['id_2'])) {
		$id = $options['id_2'];
	} else {
		$id = '';
	}
	
	$url_parsed = parse_url($url);
	$url_host = $url_parsed['host'];
	if( substr_count($url, 'localhost') or strpos($url_host, '192.168.') === 0 or strpos($url_host, '127.0.0') === 0 or (strpos($url_host, '172.') === 0 and (int)substr($url_host, 4, 2) > 15 and (int)substr($url_host, 4, 2) < 32 ) or strpos($url_host, '10.') === 0 ) {
		$errdesc = networkpub_error_msgs('localhost url');
		echo $errdesc;
		return FALSE;
	}
	$link   = 'http://www.linksalpha.com/a/networkpubaddone';
	// Build Params
	$params = array('url'=>urlencode($url),
					'key'=>$api_key,
					'plugin'=>'nw',
					'id'=>$id);
	//HTTP Call
	$response_full = networkpub_http_post($link,$params);
	$response_code = $response_full[0];
	if ($response_code != 200) {
		$errdesc = networkpub_error_msgs($response_full[1]);
		echo $errdesc;
		return FALSE;
	}
	$response = networkpub_json_decode($response_full[1]);
	if ($response->errorCode > 0) {
		$errdesc = networkpub_error_msgs($response->errorMessage);
		echo $errdesc;
		return FALSE;
	}
	//Update options - Site id
	$options['id_2'] = $response->results->id;
	//Update options - Network Keys
	if(empty($options['api_key'])) {
		$options['api_key'] = $response->results->api_key;	
	} else {
		$option_api_key_array = explode(',', $options['api_key']);
		$option_api_key_new = $response->results->api_key;
		$option_api_key_new_array = explode(',', $option_api_key_new);
		foreach($option_api_key_new_array as $key=>$val) {
			if(!in_array($val, $option_api_key_array)) {
				$options['api_key'] = $options['api_key'].','.$val;
			}
		}
	}
	//Save
	update_option(NETWORKPUB_WIDGET_NAME_INTERNAL, $options);
	//Return
	echo '<div class="updated fade" style="width:94%;margin-left:5px;padding:5px;text-align:center">API Key has been added successfully</div>';
	return;
}


function networkpub_load() {
	$options = get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);
	if (empty($options['api_key'])) {
		$html = '<div class="msg_error">You have not added an API Key</div>';
		return $html;
	}
	$link = 'http://www.linksalpha.com/a/networkpubget';
	$body = array('key'=>$options['api_key'], 'version'=>2);
	$response_full = networkpub_http_post($link, $body);
	$response_code = $response_full[0];
	if ($response_code != 200) {
		$errdeschtml = networkpub_error_msgs('misc');
		return $errdeschtml;
	}
	$response = networkpub_json_decode($response_full[1]);
	if($response->errorCode > 0) {
		$html = '<div class="msg_error">Error occured while trying to load the API Keys. Please try again later.</div>';
		return $html;
	}
	if(count($response->results_deleted)) {
		$option_api_key_array = explode(',', $options['api_key']);
		foreach($response->results_deleted as $row) {
			if(in_array($row, $option_api_key_array)) {
				$pos = $option_api_key_array[$row];
				unset($option_api_key_array[$pos]);
			}
		}
		$api_key = implode(",", $option_api_key_array);
		$options['api_key'] = $api_key;
		update_option(NETWORKPUB_WIDGET_NAME_INTERNAL, $options);
	}
	if(!count($response->results)) {
		return '<div class="msg_error">You have not added an API Key</div>';
	}
	if(count($response->results) == 1) {
		$html = '<div style="padding:0px 10px 10px 10px;">'.NETWORKPUB_CURRENTLY_PUBLISHING.'&nbsp;'.count($response->results).'&nbsp;'.NETWORKPUB_SOCIAL_NETWORK.'</div>';	
	} else {
		$html = '<div style="padding:0px 10px 10px 10px;">'.NETWORKPUB_CURRENTLY_PUBLISHING.'&nbsp;'.count($response->results).'&nbsp;'.NETWORKPUB_SOCIAL_NETWORKS.'</div>';
	}
	$html .= '<table class="networkpub_added"><tr><th>Network Account</th><th>Options</th><th>Remove</th></tr>';
	$i = 1;
	foreach($response->results as $row) {
		$html .= '<tr id="r_key_'.$row->api_key.'">';
		if($i&1) {
			$html .= '<td>';
		} else {
			$html .= '<td style="background-color:#F7F7F7;">';
		}
		$html .= '<a target="_blank" href="'.$row->profile_url.'">'.$row->name.'</a></td>';
		if($i&1) {
			$html .= '<td style="text-align:center;">';
		} else {
			$html .= '<td style="text-align:center;background-color:#F7F7F7;">';
		}
		$html .= '<a href="http://www.linksalpha.com/a/networkpuboptions?api_key='.$row->api_key.'&id='.$options['id_2'].'&version='.networkpub_version().'&KeepThis=true&TB_iframe=true&height=465&width=650" title="Publish Options" class="thickbox" type="button" />Options</a></td>';
		if($i&1) {
			$html .= '<td style="text-align:center;">';
		} else {
			$html .= '<td style="text-align:center;background-color:#F7F7F7;">';
		}
		$html .= '<a href="#" id="key_'.$row->api_key.'" class="networkpubre">Remove</a></td>';
		$html .= '</tr>';
		$i++;
	}
	$html .= '</table>';
	return $html;
}


function networkpub_remove() {
	$options = get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);
	if (!empty($_POST['networkpub_key'])) {
		$key_full = $_POST['networkpub_key'];
		$key_only = substr($key_full, 4);
		$link = 'http://www.linksalpha.com/a/networkpubremove';
		$body = array('id'=>$options['id_2'], 'key'=>$key_only);
		$response_full = networkpub_http_post($link, $body);
		$response_code = $response_full[0];
		if ($response_code != 200) {
			$errdesc = networkpubnw_error_msgs($response_full[1]);
			echo $errdesc;
			return;
		}
		$api_key = $options['api_key'];
		$api_key_array = explode(',', $api_key);
		$loc = array_search($key_only, $api_key_array);
		if($loc !== FALSE) {
			unset($api_key_array[$loc]);
		}
		$api_key = implode(",", $api_key_array);
		$options['api_key'] = $api_key;
		update_option(NETWORKPUB_WIDGET_NAME_INTERNAL, $options);
		echo $key_full;
		return;
	}
}


function networkpub_enable($networkpub_enable_value) {
	$options = get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);
	$options['networkpub_enable'] = $networkpub_enable_value;
	update_option(NETWORKPUB_WIDGET_NAME_INTERNAL, $options);
	return;
}

function networkpub_pages() {
	if ( function_exists('add_submenu_page') ) {
		$page = add_submenu_page('plugins.php', NETWORKPUB_WIDGET_NAME, NETWORKPUB_WIDGET_NAME, 'manage_options', NETWORKPUB_WIDGET_NAME_INTERNAL, 'networkpub_conf');
		if ( is_admin() ) {
			$page = add_submenu_page('edit.php', 	NETWORKPUB_WIDGET_NAME_POSTBOX, NETWORKPUB_WIDGET_NAME_POSTBOX, 'manage_options', NETWORKPUB_WIDGET_NAME_POSTBOX_INTERNAL, 'networkpub_postbox');
		}
	}
}


function networkpub_postbox() {
	$html  = '<div class="wrap"><div class="icon32" id="networkpub_laicon"><br /></div><h2>'.NETWORKPUB_WIDGET_NAME.' - '.NETWORKPUB_WIDGET_NAME_POSTBOX.'</h2></div>';
	$html .= '<iframe id="networkpub_postbox" src="http://www.linksalpha.com/post?source=wordpress&sourcelink='.urlencode(networkpub_postbox_url()).'#'.urlencode(networkpub_postbox_url()).'" width="1050px;" height="700px;" scrolling="no" style="border:none !important;" frameBorder="0"></iframe>';
	$html .= '<div style="padding:10px 10px 6px 10px;background-color:#FFFFFF;margin-bottom:15px;margin-top:0px;border:1px solid #F0F0F0;width:1005px;">
				<div style="width:130px;float:left;font-weight:bold;">
					Share this Plugin 
				</div>
				<div style="width:600px">
					<iframe id="websites_iframe" height="25px" width="450px" scrolling="no" frameborder="0px" src="http://www.linksalpha.com/social?link=http%3A//wordpress.org/extend/plugins/network-publisher/"></iframe>
				</div>
			  </div>';
	echo $html;
	return;
}


function networkpub_supported_networks() {
	$html = '';
	$response_full = networkpub_http('http://www.linksalpha.com/a/networkpubsupported');
	$response_code = $response_full[0];
	if ($response_code != 200) {
		return $html;
	}
	$response = $response_full[1];
	$content = networkpub_json_decode($response);
	if(!$content) {
		return $html;	
	}
	$html .= '<ul style="bullet-style:none ! important;">';
	foreach($content as $key=>$val) {
		$html .= '<li style="padding:3px;"><a href="'.$val->link.'" target="_blank" style="text-decoration:none ! important;"><img src="http://www.linksalpha.com/images/'.$val->type.'_icon.png" style="vertical-align:bottom;border:0px;" />&nbsp;'.$val->name.'</a></li>';
	}
	$html .= '</ul>';
	return $html;
}


function networkpub_json_decode($str) {
	if (function_exists("json_decode")) {
	    return json_decode($str);
	} else {
		if (!class_exists('Services_JSON')) {
			require_once("JSON.php");
		}
	    $json = new Services_JSON();
	    return $json->decode($str);
	}
}


function networkpub_http($link) {
	if (!$link) {
		return array(500, 'invalid url');
	}
	if( !class_exists( 'WP_Http' ) ) {
		include_once( ABSPATH . WPINC. '/class-http.php' );
	}
	if (class_exists('WP_Http')) {
		$request = new WP_Http;
		$headers = array( 'Agent' => NETWORKPUB_WIDGET_NAME.' - '.get_bloginfo('url') );
		$response_full = $request->request( $link );
		$response_code = $response_full['response']['code'];
		if ($response_code == 200) {
			$response = $response_full['body'];
			return array($response_code, $response);
		}
		$response_msg = $response_full['response']['message'];
		return array($response_code, $response_msg);
	}
	require_once(ABSPATH.WPINC.'/class-snoopy.php');
	$snoop = new Snoopy;
	$snoop->agent = NETWORKPUB_WIDGET_NAME.' - '.get_bloginfo('url');
	if($snoop->fetchtext($link)){
		if (strpos($snoop->response_code, '200')) {
			$response = $snoop->results;
			return array(200, $response);
		}
	}
	return array(500, 'internal error');
}


function networkpub_http_post($link, $body) {
	if (!$link) {
		return array(500, 'invalid url');
	}
	if( !class_exists( 'WP_Http' ) ) {
		include_once( ABSPATH . WPINC. '/class-http.php' );
	}
	if (class_exists('WP_Http')) {
		$request = new WP_Http;
		$headers = array( 'Agent' => NETWORKPUB_WIDGET_NAME.' - '.get_bloginfo('url') );
		$response_full = $request->request( $link, array( 'method' => 'POST', 'body' => $body, 'headers'=>$headers) );
		if(isset($response_full->errors)) {
			return array(500, 'internal error');
		}
		$response_code = $response_full['response']['code'];
		if ($response_code == 200) {
			$response = $response_full['body'];
			return array($response_code, $response);
		}
		$response_msg = $response_full['response']['message'];
		return array($response_code, $response_msg);
	}
	require_once(ABSPATH.WPINC.'/class-snoopy.php');
	$snoop = new Snoopy;
	$snoop->agent = NETWORKPUB_WIDGET_NAME.' - '.get_bloginfo('url');
	if($snoop->submit($link, $body)){
		if (strpos($snoop->response_code, '200')) {
			$response = $snoop->results;
			return array(200, $response);
		}
	}
	return array(500, 'internal error');
}


function networkpub_error_msgs($errMsg) {

	$arr_errCodes  = explode(";", $errMsg);
	$errCodesCount = count($arr_errCodes);

	switch (trim($arr_errCodes[0])) {

		case 'internal error':
			$html = '<div class="msg_error">	
					<b>'.__('Please try again').'</b>&nbsp;'.__('There was an unknown error. Please try again.
					You can also email us at').'&nbsp;<a href="mailto:post@linksalpha.com">post@linksalpha.com</a>&nbsp;'.__('with error description (your blog URL and the error)').'.
				</div>';
			return $html;		
			break;
	
		case 'invalid url':
			$html  = '<div class="msg_error"><b>'.__('Your blog URL is invalid').':</b>'.$arr_errCodes[$errCodesCount-1];			
			if($errCodesCount == 3) {
				$html .= '.&nbsp;'.__('Error Code').'&nbsp;='.$arr_errCodes[$errCodesCount-2];
			}			
			$html .= '<div>
					'.__('You can also').'&nbsp;<a href="http://www.linksalpha.com/user/siteadd" target="_blank">'.__('Click here').'</a>'.__(' to enter blog URL on LinksAlpha manually.
					  Also ensure that in ').'<b>'.__('Settings').'->'.__('General').'->"'.__('Blog address (URL)').'"</b> '.__('the URL is filled-in correctly').'.</div> 
					  <div>'.__('If you still face issues then email us at').'&nbsp;<a href="mailto:post@linksalpha.com">post@linksalpha.com</a>&nbsp;'.__('with error description').'.</div>';			
			return $html;
			break;
		
		case 'localhost url':
			$html  = '<div class="msg_error"><div><b>'.__('Website/Blog inaccessible').'</b></div>';
			$html .= '<div>'.__('You are trying to use the plugin on ').'<b>localhost</b> '.__('or behind a').' <b>'.__('firewall').'</b>, '.__('which is not supported. Please install the plugin on a Wordpress blog on a live server').'.</div>
				  </div>';
			return $html;
			break;
			
		case 'remote url error':		
			$html  = '<div class="msg_error"><div><b>'.__('Remote URL error').': </b>'.$arr_errCodes[$errCodesCount-1];
			if($errCodesCount == 3) {
				$html .= '. '.__('Error Code').'&nbsp;='.$arr_errCodes[$errCodesCount-2];
			}
			$html .= '</div>
					<div>
						<b>'.__('Description:').'</b>
						<b>'.__('Please try again').'. </b> '.__('Your site either did not respond (it is extremely slow) or it is not operational').'.
					</div>
					<div>
						'.__('You can also').' <a href="http://www.linksalpha.com/user/siteadd" target="_blank">'.__('Click here').'</a> '.__('to enter blog URL on LinksAlpha manually').'. 
						'.__('Also ensure that in').' <b>'.__('Settings').'->'.__('General').'->"'.__('Blog address (URL)').'"</b> '.__('the URL is filled-in correctly').'. 
					</div>
					<div>
						'.__('If you still face issues then email us at').' <a href="mailto:post@linksalpha.com">post@linksalpha.com</a> '.__('with error description').'.
					</div>
				</div>';
			return $html;		
			break;
			
		case 'feed parsing error':
			$html  = '<div class="msg_error"><div><b>'.__('Feed parsing error').': </b>'.$arr_errCodes[$errCodesCount-1];			
			if($errCodesCount == 3) {
				$html .= '. '.__('Error Code').'=&nbsp;'.$arr_errCodes[$errCodesCount-2];
			}
			$html .= '	</div>
					<div>
						<b>'.__('Description').': </b>
						'.__('Your RSS feed has errors. Pls go to').' <a href=http://beta.feedvalidator.org/ target="_blank">href=http://beta.feedvalidator.org/</a> '.__('to validate your RSS feed').'.
					</div>
					<div>
						'.__('If it comes out to be correct, try again and email as at ').'<a href="mailto:post@linksalpha.com">post@linksalpha.com</a> '.__('with your blog URL and error description').'.
					</div>
				</div>';			
			return $html;		
			break;

		case 'feed not found':
			$html ='<div class="msg_error">
					<div>
						<b>'.__('We could not find feed URL for your blog').'.</b>
					</div>
					<div>
						<a href="http://www.linksalpha.com/user/siteadd" target="_blank">'.__('Click here').'</a> '.__('to enter feed URL on LinksAlpha manually').'.
						'.__('Also ensure that in ').'<b>'.__('Settings').'->'.__('General').'->"'.__('Blog address (URL)').'"</b> '.__('the URL is filled-in correctly').'.
					</div>
					<div>
						'.__('If you still face issues then email us at ').'<a href="mailto:post@linksalpha.com">post@linksalpha.com</a> '.__('with error description').'
					</div>
				</div>';
			return $html;		
			break;
			
		case 'invalid key':
			$html = '<div class="msg_error">
					<div>
						<b>'.__('Invalid Key').': </b>'.__('the key that you entered is incorrect. Please try again').'.
					</div>
					<div>
						<span style="color:#d12424;">'.__('Getting Errors').'?</span> '.__('See help page').' <a href="http://help.linksalpha.com/errors" target="_blank">'.__('here').'</a>
					</div>
					<div>
						'.__('Or').', <a href="http://www.linksalpha.com/user/siteadd" target="_blank">'.__('Click here').'</a> '.__('to enter your blog URL on LinksAlpha manually').'.
						'.__('If you still face issues then email us at ').'<a href="mailto:post@linksalpha.com">post@linksalpha.com</a> '.__('with error description').'
					</div>
				</div>';			
			return $html;
			break;
			
		case 'subscription upgrade required':
			$html = '<div class="msg_error">
					<b>'.__('Upgrade account').'.</b> '.__('Please').' <a href="http://www.linksalpha.com/account" target="_blank">'.__('upgrade your subscription').'</a> '.__('to continue using current number of networks and websites').'.
				</div>';
			return $html;
			break;
		
		default:
			$html = '<div class="msg_error">
						<div><b>Not able to connect to LinksAlpha.com</b></div>
						<div>
							<div style="padding-bottom:3px;">
								Your website is not able to connect to LinksAlpha.com. This might be due to - 
							</div>
							<div>
								<ul style="list-style-type:circle;padding:0px 0px 0px 20px;margin:0px;">
									<li style="padding-bottom:0px;"><div>An issue with your hosting company where they might be preventing HTTP calls to external sites</div></li>
									<li><div>A plugin you are using has overwritten the .htaccess preventing access to external websites</div></li>
								</ul>
							</div>
							<div>As an alternative you can configure publishing as described here - <a href="http://support.linksalpha.com/entries/169997-simplest-way-to-publish" target="_blank">http://support.linksalpha.com/entries/169997-simplest-way-to-publish</a></div>
						</div>
					</div>';
			return $html;
			break;
	}
}


function networkpub_get_plugin_dir() {
	if ( version_compare($wp_version, '2.8', '<') ) {
		$path = dirname(plugin_basename(__FILE__));
		if ( $path == '.' )
		$path = '';
		$plugin_path = trailingslashit( plugins_url( $path ) );
	}
	else {
		$plugin_path = trailingslashit( plugins_url( '', _FILE_) );
	}
	return $plugin_path;
}


function networkpub_activate() {
	$networkpub_eget = get_bloginfo('admin_email'); $networkpub_uget = get_bloginfo('url'); $networkpub_nget = get_bloginfo('name');
	$networkpub_dget = get_bloginfo('description'); $networkpub_cget = get_bloginfo('charset'); $networkpub_vget = get_bloginfo('version');
	$networkpub_lget = get_bloginfo('language');
	$link='http://www.linksalpha.com/a/bloginfo';
	$networkpub_bloginfo = array('email'=>$networkpub_eget, 'url'=>$networkpub_uget, 'name'=>$networkpub_nget, 'desc'=>$networkpub_dget, 'charset'=>$networkpub_cget, 'version'=>$networkpub_vget, 'lang'=>$networkpub_lget,  'plugin'=>'nw');
	networkpub_http_post($link, $networkpub_bloginfo);
	//Convert
	$options = get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);
	if(empty($options['id_2']) or empty($options['api_key'])) {
		return;
	}
	// Build Params
	$link = 'http://www.linksalpha.com/a/networkpubconvertdirect';
	$params = array('id'=>$options['id_2'],
					'key'=>$options['api_key'],
					'plugin'=>'nw',
					);
	//HTTP Call
	$response_full = networkpub_http_post($link, $params);
	$response_code = $response_full[0];
	return;
}


function networkpub_deactivate() {
	$options = get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);
	if(empty($options['id_2']) or empty($options['api_key'])) {
		return;
	}
	// Build Params
	$link = 'http://www.linksalpha.com/a/networkpubconvertfeed';
	$params = array('id'=>$options['id_2'],
					'key'=>$options['api_key'],
					);
	//HTTP Call
	$response_full = networkpub_http_post($link, $params);
	$response_code = $response_full[0];
	return;
}


function networkpub_pushpresscheck() {
	$active_plugins = get_option('active_plugins');
	$pushpress_plugin = 'pushpress/pushpress.php';
	$this_plugin_key = array_search($pushpress_plugin, $active_plugins);
	if ($this_plugin_key) {
		$options = get_option(NETWORKPUB_WIDGET_NAME_INTERNAL);
		if(array_key_exists('id', $options)) {
			if(!empty($options['id_2'])) {
				$link = 'http://www.linksalpha.com/a/pushpress';
				$body = array('id'=>$options['id_2']);
				$response_full = networkpub_http_post($link, $body);
				$response_code = $response_full[0];	
			}	
		}
	}
}


function networkpub_postbox_url() {
	if ( version_compare($wp_version, '3.0.0', '<') ) {
		$admin_url = site_url().'/wp-admin/edit.php?page='.NETWORKPUB_WIDGET_NAME_POSTBOX_INTERNAL;	
	} else {
		$admin_url = site_url().'/edit.php?page='.NETWORKPUB_WIDGET_NAME_POSTBOX_INTERNAL;
	}
	return $admin_url;
	
}


function networkpub_version() {
	return NETWORKPUB_PLUGIN_VERSION;
}


function networkpub_thumbnail_link( $post_id ) {
	if(!function_exists('get_post_thumbnail_id')) {
		return False;
	}
	$src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
	if($src) {
		$src = $src[0];
		return $src;	
	} else {
		return False;
	}
}


register_deactivation_hook( __FILE__, 'networkpub_deactivate' );

?>
