<?php
/**
 * @package Habari
 *
 */
namespace Habari;
class Vendor extends Post
{
	public static function get($paramarray = array()) {
		$defaults = array(
			'content_type' => 'vendor',
			'fetch_fn' => 'get_row',
			'limit' => 1,
			'fetch_class' => 'Vendor',
		);
		
		$paramarray = array_merge($defaults, Utils::get_params($paramarray));
		return Posts::get( $paramarray );
	}

	public function update($minor = true) {
		parent::update($minor);
	}

	public function jsonSerialize() {
		$array = array_merge( $this->fields, $this->newfields );		
		return json_encode($array);
	}
}
?>
