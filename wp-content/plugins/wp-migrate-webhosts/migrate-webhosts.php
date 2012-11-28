<?php
	require_once(migrate_webhosts_admin_path('admin.php'));

	//if ( !current_user_can('migrate_webhosts') )  //TODO: Add Permissions Functionality
	//	wp_die(__('You do not have sufficient permissions to migrate webhosts for this site.'));

	global $parent_file,$submenu_file,$title;
	$parent_file = 'tools.php';
	$submenu_file = WP_MigrateWebhosts::get_base_admin_path();
	$title = 'Migrate Webhosts';

	/* if ($_GET['migrate']=='now') {
		WP_MigrateWebhosts::migrate_webhosts();
		wp_safe_redirect(admin_url("$submenu_file&migrate=done"));
	} */

	require_once(migrate_webhosts_admin_path('admin-header.php'));
	echo '<div class="wrap migrate-webhosts-div">';
	screen_icon();
	echo '<h2>'.esc_html( $title ).'</h2>';
//	if ($_GET['migrate']=='done') {
//		echo '<div id="message" class="updated">Migration Complete.</div>';
//	} else {
		migrate_webhosts_postbox();
//	}
	echo '</div><div class="clear"></div>';
	migrate_webhosts_disclaimer();
	include(migrate_webhosts_admin_path('admin-footer.php'));


function migrate_webhosts_postbox() {
	$checkboxes = WP_MigrateWebhosts::get_webhosts_checkboxes_html(array('select'=>'others'));
	$current_webhost = WP_MigrateWebhosts::get_webhosts_checkboxes_html(array('select'=>'current'));
	$migrate_link = WP_MigrateWebhosts::get_base_admin_path() . '&migrate=now';
	$working_gif = admin_url('/images/wpspin_light.gif');
	$html =<<<HTML
<div id="migrate-from" class="migration-locations postbox">
	<h3 class="side-title hndle">Migrate <span class="bigger">FROM:<span></h3>
	<div class="inner">
		$checkboxes
	</div>
</div>
<div id="migrate-to" class="migration-locations postbox">
	<h3 class="side-title hndle">Migrate <span class="bigger">TO:</span></h3>
	<div class="inner">
		{$current_webhost}
	</div>
</div>
<div class="clear"></div>
<div id="migrate-button-div">
	<img id="working-gif" src="$working_gif" height="16" width="16">
	<a href="#migrate-webhosts" id="migrate-webhosts" class="button-primary">Migrate Webhost(s)</a>
</div>
HTML;
	echo $html;
}
function migrate_webhosts_admin_path($relative_path='') {
	$admin_path = ABSPATH . '/wp-admin/' . trim($relative_path,'/');
	return $admin_path;
}
function migrate_webhosts_disclaimer() {
?>
<div class="note">
	<h2>Notes:</h2>	
	<h3>This plugin is for Developers, it is <em>NOT</em> for End-Users</h3>
	<p>
		This plugin was <strong>NOT</strong> designed to be used by end-users.
		Instead this plugin was designed as a	convenience for experienced WordPress developers to use
		during development of complex	WordPress-based systems. This plugin's goal is to streamline the
		process for a developer when they need to frequently deploy a development site to a staging
		server, vice versa or other similar scenarios.
	</p>
	<h3>Any user of this plugin should have solid MySQL skills, just in case</h3>
	<p>
		This plugin converts content using mostly simplistic string matching that is probably 99%	effective
		but in special cases it may convert content incorrectly during migration and thus "corrupt" content
		in the process. Because of this <strong>only people who have the MySQL skills</strong> required to
		correct any potential corruption in the case it occurs should use this plugin and even those people
		should have a database backup before attempting migration.
	</p>
	<h3>It supports Hooks for Custom Migrations so it's Extensible</h3>
	<p>
		If you do have the skills to correct any potential corruption you
		will likely find this plugin very useful, <strong>especially because you can write your own custom
		migration routines</strong> using "hooks", even for other people's plugins. Like any hook in WordPress
		you can store in your theme's function.php file or your own plugin. Look for the file "sample-migrations.php"
		to see how.
	</p>
	<h3>An Envisioned Scenario: Local Development Site with a Staging Server Online</h3>
	<p>
		A common <strong> use-case for this plugin</strong> is for someone with at least a development
		website on their local machine and a live server somewhere on the web and probably a staging server
		too.
	</p>
	<h3>A Common Usage Pattern: FTP Upload, MySQL Transfer then <em>Migrate</em></h3>
	<p>
		The envisioned usage is for during development the developer gets to a step ready to deploy their
		local development version to a staging server (or live server, or from staging to live.) First the
		developer will FTP upload all the changed files and will next transfer the MySQL database
		<strong><em>as a whole</em></strong> from the local development site to the staging or live server.
	</p>
	<h3>It can go Backwards too, from Live Server to Development Site</h3>
	<p>
		Another use-case is to download files and database from the live server back down to the development.
		This is especially useful when getting an existing live site newly working on a development box.
	</p>
	<h3><em>IMPORTANT</em>: No Partial Migrations to Sites with Live Data!!!</h3>
	<p>
		To be clear, this plugin does not currently handle the use-case of migrating from a development
		or staging server back to a live server while keeping the live server's data intact. This plugin
		currently only handled the process of moving all data from dev or staging up to live and back down
		again. If there is demand (read: "<a href="http://mikeschinkel.com/consulting/"><em>paying demand</em></a>")
		we may (be willing to) adress that in the future.
	</p>
</div>
<?php
}
