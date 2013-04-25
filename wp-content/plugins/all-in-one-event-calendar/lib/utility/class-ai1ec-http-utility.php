<?php

/**
 * Utility handling HTTP(s) automation issues
 *
 * @author     Timely Network Inc
 * @since      2.0
 *
 * @package    AllInOneEventCalendar
 * @subpackage AllInOneEventCalendar.Lib.Utility
 */
class Ai1ec_Http_Utility
{

	/**
	 * @var Ai1ec_Http_Utility Singletonian instance of self
	 */
	static protected $_instance = NULL;

	/**
	 * Get singletonian instance of self
	 *
	 * @return Ai1ec_Http_Utility Singletonian instance of self
	 */
	static public function instance() {
		if ( ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Inject time.ly certificate to cURL resource handle
	 *
	 * @param resource $curl Instance of cURL resource
	 *
	 * @return void Method does not return
	 */
	public function curl_inject_certificate( $curl ) {
		// verify that the passed argument
		// is resource of type 'curl'
		if (
			is_resource( $curl ) &&
			'curl' === get_resource_type( $curl )
		) {
			// set CURLOPT_CAINFO to AI1EC_CA_ROOT_PEM
			curl_setopt( $curl, CURLOPT_CAINFO, AI1EC_CA_ROOT_PEM );
		}
	}

}
