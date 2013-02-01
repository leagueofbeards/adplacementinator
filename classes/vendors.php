<?php
/**
 * invoices Class
 *
 */
namespace Habari;
class Vendors extends Posts
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => 'vendor',
			'fetch_class' => 'Vendor',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		return Posts::get( $paramarray );
	}
}

?>