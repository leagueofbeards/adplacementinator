<?php
namespace Habari;
class AdPlacementInator extends Plugin
{
	public function action_init() {
		DB::register_table('ads');
		DB::register_table('ad_analytics');
		DB::register_table('user_ads');
		DB::register_table('ad_plans');
	}

	public function action_plugin_activation( $plugin_file ) {
		Post::add_new_type( 'ad' );
		$this->create_ads_table();
		$this->create_users_ads_table();
		$this->create_ad_analytics_table();
		$this->create_ad_plans_table();
	}
	
	public function action_plugin_deactivation ( $file='' ) {
		Post::deactivate_post_type( 'ad' );
	}
	
	public function filter_post_type_display($type, $g_number)	{
		switch($type) {
			case 'ad':
				switch($g_number) {
					case 'singular':
						return _t('Ad');
					case 'plural':
						return _t('Ads');
				}
				break;
		}
		return $type;
	}
	
	public function filter_post_get($out, $name, $ad) {
		if('ad' == Post::type_name($ad->get_raw_field('content_type'))) {
			switch($name) {
			}
		}
		
		return $out;
	}

	private function create_ads_table() {
		$sql = "CREATE TABLE {\$prefix}ads (
			id int unsigned NOT NULL AUTO_INCREMENT,
			post_id int unsigned NOT NULL,
			active int unsigned NOT NULL,
			featured int unsigned NOT NULL,
			size varchar(255) NULL,
			link varchar(255) NULL,
			link_text varchar(255) NULL,
			img_url varchar(255) NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `post_id` (`post_id`)
			) DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;";

		DB::dbdelta( $sql );
	}

	private function create_ad_analytics_table() {
		$sql = "CREATE TABLE {\$prefix}ad_analytics (
			id int unsigned NOT NULL AUTO_INCREMENT,
			ad_id int unsigned NOT NULL,
			impressions int unsigned NOT NULL,
			views int unsigned NOT NULL,
			clicks int unsigned NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `ad_id` (`ad_id`)			
			) DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;";
		DB::dbdelta( $sql );
	}

	private function create_users_ads_table() {
		$sql = "CREATE TABLE {\$prefix}ad_users (
			id int unsigned NOT NULL AUTO_INCREMENT,
			ad_id int unsigned NOT NULL,
			user_id int unsigned NOT NULL,
			date varchar(255) NULL,
			clicked int unsigned NOT NULL,
			PRIMARY KEY (`id`)
			) DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;";

		DB::dbdelta( $sql );
	}

	private function create_ad_plans_table() {
		$sql = "CREATE TABLE {\$prefix}ad_plans (
			id int unsigned NOT NULL AUTO_INCREMENT,
			post_id int unsigned NOT NULL,
			units int unsigned NOT NULL,
			remote_id varchar(255) NULL,
			active int unsigned NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `post_id` (`post_id`)			
			) DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;";

		DB::dbdelta( $sql );
	}

	public function filter_posts_get_paramarray($paramarray) {
		$queried_types = Posts::extract_param($paramarray, 'content_type');
		if($queried_types && in_array('ad', $queried_types)) {
			$paramarray['post_join'][] = '{ads}';
			$default_fields = isset($paramarray['default_fields']) ? $paramarray['default_fields'] : array();
			$default_fields['{ads}.active'] = 0;
			$default_fields['{ads}.featured'] = 0;
			$default_fields['{ads}.size'] = '';
			$default_fields['{ads}.link'] = '';
			$default_fields['{ads}.link_text'] = '';
			$default_fields['{ads}.image_url'] = '';
			$default_fields['{ads}.impressions'] = '';
			$default_fields['{ads}.views'] = '';
			$default_fields['{ads}.clicks'] = '';
			$paramarray['default_fields'] = $default_fields;
		}
		
		return $paramarray;
	}

	public function filter_post_schema_map_ad($schema, $post) {
		$schema['ads'] = $schema['*'];
		$schema['ads']['post_id'] = '*id';
		return $schema;
	}
	
	public function filter_default_rewrite_rules( $rules ) {
		$this->add_rule('"ad"/click/id', 'click_redirect');
        return $rules;
    }
    
    public function theme_route_click_redirect($theme) {
	    $person = User::identify();
	    $ad = Add::get( array('id' => $theme->matched_rule->named_arg_values['id'] ) );
    }
}
?>