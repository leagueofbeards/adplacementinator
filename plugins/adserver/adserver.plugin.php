<?php
namespace Habari;

class AdPlacementInator extends Plugin
{
	public function action_init() {
		DB::register_table('ads');
		DB::register_table('ad_analytics');
		DB::register_table('ad_users');
		DB::register_table('ad_plans');
		$this->ad_pages();
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
				case 'show_ad':
					$this->increment_views( $ad );
					$out = $ad->image_url;
				break;
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
			image_url varchar(255) NULL,
			views int unsigned NOT NULL,
			clicks int unsigned NOT NULL,
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
			vendor int unsigned NOT NULL,
			clicked int unsigned NOT NULL,
			ip varchar(45) NULL,
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

	private function ad_pages() {
		$this->add_template('ads', dirname($this->get_file()) . '/admin/ads.php');
		$this->add_template('add_ad', dirname($this->get_file()) . '/admin/add_ad.php');
	}

	private function record_analytics($args, $vars) {
		$check = DB::query("SELECT id FROM {ad_analytics} WHERE ad_id = ?", array($args['ad_id']) );
		if( $check == true ) {
			DB::update( DB::table('ad_analytics'), $args, array('ad_id' => $args['ad_id']) );
		} else {
			DB::insert( DB::table('ad_analytics'), $args );
		}
		
		DB::insert( DB::table('ad_users'), $vars );
	}

	private function increment_clicks($ad) {
		$user = User::identify();
		$clicks = $ad->clicks + 1;
		$views = $ad->views;
		$vars = array( 'ad_id' => $ad->id, 'user_id' => $user->id, 'clicked' => 1, 'date' => DateTime::date_create( date(DATE_RFC822) ), 'ip' => Utils::get_ip());
		$args = array( 'ad_id' => $ad->id, 'impressions' => $views, 'clicks' => $clicks );
		$this->record_analytics( $args, $vars );
		$ad->clicks = $clicks;
		$ad->update();
	}

	private function increment_views($ad) {
		$user = User::identify();
		$views = $ad->views + 1;
		$clicks = $ad->clicks;
		$vars = array( 'ad_id' => $ad->id, 'user_id' => $user->id, 'clicked' => 0, 'date' => DateTime::date_create( date(DATE_RFC822) ), 'ip' => Utils::get_ip());
		$args = array('ad_id' => $ad->id, 'impressions' => $views, 'clicks' => $clicks);
		$this->record_analytics( $args, $vars );
		$n_ad = Ad::get( array('id' => $ad->id) );
		$n_ad->views = $views;
		$n_ad->update();
	}

	public function filter_admin_access_tokens( array $require_any, $page ) {
		switch ($page) {
			case 'ads' :
				$require_any = array('post_entry' => true);
			break;
			case 'add_ad' :
				$require_any = array('post_entry' => true);
			break;
		}
		
		return $require_any;
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
			$default_fields['{ads}.image_url'] = '';
			$default_fields['{ads}.views'] = '';
			$default_fields['{ads}.clicks'] = '';
			$default_fields['{ads}.vendor_id'] = '';
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
		$this->add_rule('"ad"/id', 'click_redirect');
		
        return $rules;
    }

    public static function sizes() {
	    return array('Super Wide Leaderboard' => '970.90', 'Leaderboard' => '728.90');
    }

    public static function random_ad() {
    	$ad = Ad::get( array('active' => 1, 'orderby' => 'RAND()'));
    	return $ad;
    }
    
    public function theme_route_click_redirect($theme) {
	    $person = User::identify();
	    $ad = Ad::get( array('id' => $theme->matched_rule->named_arg_values['id'] ) );
	    $this->increment_clicks( $ad );
	    Utils::redirect( $ad->link ); exit();
    }
    
    public function action_auth_ajax_add_advert($data) {
   		$user = User::identify();
		$vars = $data->handler_vars;
				
		if( $vars['active'] == 'yes' ) {
			$active = 1;
		} else {
			$active = 0;
		}
		
		$upload_dir = Site::get_path('user') . '/files/uploads/ads/';
		$image = Common::upload_image( $_FILES, $upload_dir );
				
		$postdata= array(
			'title' 		=>	$vars['vendor'] + time(),
			'slug'			=>	Utils::slugify( $vars['vendor_id'] + time() ),
			'content'		=>	$vars['vendor'] + time(),
			'active'		=>	$active,
			'vendor_id'		=>	$vars['vendor'],
			'image_url'		=>	$image->document,
			'link'			=>	$vars['link'],
			'size'			=>	$vars['size'],
			'user_id'		=>	$user->id,
			'pubdate'		=>	DateTime::date_create( date(DATE_RFC822) ),
			'status'		=>	Post::status( 'published' ),
			'content_type'	=> Post::type('ad'),
		);
		
		$ad = Ad::create( $postdata );

		Utils::redirect( Site::get_url('admin') . '/add_ad?id=' . $ad->id );
    }
    
    public function action_auth_ajax_update_advert($data) {
   		$user = User::identify();
		$vars = $data->handler_vars;
				
		$image = '';
		
		if( $vars['active'] == 'yes' ) {
			$active = 1;
		} else {
			$active = 0;
		}
		
		$ad = Ad::get( array('id' => $vars['ad_id']) );
				
		if( $_FILES['uploaded']['name'] != '' ) {
			$upload_dir = Site::get_path('user') . '/files/uploads/ads/';
			$image = Common::upload_image( $_FILES, $upload_dir );
		}

		$postdata= array(
			'active'		=>	$active,
			'vendor_id'		=>	$vars['vendor'],
			'image_url'		=>	is_object($image) ? $image->document : $ad->image_url,
			'link'			=>	$vars['link'],
			'size'			=>	$vars['size'],
			'updated'		=>	DateTime::date_create( date(DATE_RFC822) ),
		);
		
		foreach( $postdata as $key => $value ) {
			$ad->$key = $value;
		}

		$ad->update();
		
		Utils::redirect( Site::get_url('admin') . '/add_ad?id=' . $ad->id );
    }
}
?>