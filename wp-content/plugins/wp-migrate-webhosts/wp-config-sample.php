//The following code would be used in place of the defines for DB_NAME, DB_USER, DB_PASSWORD and DB_HOST

require_once(ABSPATH . 'wp-content/plugins/wp-migrate-webhosts/wp-webhosts.php');
register_webhost_defaults(array(
	'database'  => 'sample_site_db',
	'user'      => 'sample_site_user',
	'host'      => 'localhost',
	'sitepath'  => '',
));
register_webhost('development',array(
	'name'      => 'Local Development on the Mac',
  'password'  => 'abc123',
	'host'      => '127.0.0.1:3306',    // WordPress has issues with local installs on the Mac using "localhost"
	'domain'    => 'sample-site.dev',   // This requires an entry in hosts for sample-site.dev to be set to 127.0.0.1
	'rootdir'   => '/Users/MacUserName/Sites/sample-site/',
	'google_api_key' => 'abc123',
));
register_webhost('staging',array(
	'name'      => 'Staging Site',
  'password'  => 'complex-password',
	'domain'    => 'staging.sample-site.com',
	'rootdir'   => '/home/sample-site/htdocs/staging/',
	'google_api_key' => 'def456',
));
register_webhost('production',array(
	'name'      => 'Staging Site',
  'password'  => 'very-complex-password',
	'domain'    => 'www.sample-site.com',
	'rootdir'   => '/home/sample-site/htdocs/',
	'google_api_key' => 'xyz987',
));
require_once(ABSPATH . 'wp-content/plugins/wp-migrate-webhosts/set-webhost.php');

