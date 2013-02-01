<?php
/**
 * invoices Class
 *
 */
namespace Habari;
class Ads extends Posts
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => 'ad',
			'fetch_class' => 'Ad',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		return Posts::get( $paramarray );
	}
}

?>