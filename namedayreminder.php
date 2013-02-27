<?php
/*
Plugin Name: Name day reminder
Plugin URI: http://www.namedayreminder.com
Description: Name day reminder
Version: 0.2
Author: John and Kaspars
Author URI: http://www.namedayreminder.com
*/
class NameDayReminderWidget extends WP_Widget 
{
	function NameDayReminderWidget()
	{
		$widget_ops = array('classname' => 'NameDayReminderWidget', 'description' => 'Displays name days' );
		$this->WP_Widget('NameDayReminderWidget', 'Name days', $widget_ops);
	}
 
	function form($instance)
	{
 		$url = plugins_url('style.css', __FILE__);
		echo '<link rel="stylesheet" href="' . $url . '" type="text/css" media="screen" />';

		$domain = $_SERVER['SERVER_NAME'];
		$domain = preg_replace('#^(http(s)?://)?w{3}\.#', '', $domain);
		global $current_user;
		get_currentuserinfo();
		global $wpdb;
		$date = date('d-m-Y H:i:s');
		$id = 123;
	
		$table_name = $wpdb->prefix."name_day_reminder"; 

		if ($_POST['NameDayReminder-Submit']) {
			$show_radio = $_POST['group1'];
			$api_key 	= $_POST['apiKey'];
			$country	= $_POST['country'];
			if ($show_radio == 'row') {
				$row_radio 	= 1;
				$list_radio = 0;
			} else {
				$row_radio	= 0;
				$list_radio = 1;
			}
			$options['title'] = htmlspecialchars($_POST['NameDayReminder-WidgetTitle']);
			update_option("NameDayReminderWidget", $options);
			$update_name=$wpdb->get_results("UPDATE $table_name SET api_key='$api_key',country_id='$country',show_row='$row_radio',show_list='$list_radio' WHERE id = 1");
		}
			
		$select_name=$wpdb->get_row("SELECT api_key,country_id,country_text,show_row,show_list FROM $table_name WHERE id = 1",ARRAY_A);
		
		$country	= $select_name['country_id'];
		$row_radio 	= $select_name['show_row'];
		$list_radio = $select_name['show_list'];
		$api_key 	= $select_name['api_key'];	
		
		if (!$api_key) {
			echo "<p id='err_msg'>before using - Enter API key!</p>";
			echo "<p id='notif_msg'>Get api key <a href='http://www.namedayreminder.com/get-api/' target='_blank'>here!</a></p>";
		} else {
			$postdata 		= http_build_query(array('body' => '{"jsonrpc":"2.0","method":"CheckApiKey","params":{"user":"","domain":"'.$domain.'","apiKey":"'.$api_key.'"},"id": "'.$id.'"}'));
			$opts 			= array('http' => array('method'  => 'POST','header'  => 'Content-type: application/x-www-form-urlencoded','content' => $postdata));
			$context  		= stream_context_create($opts);
			$get_api_answer	= file_get_contents("http://namedayreminder.com/api_new/service.php", false, $context);
			$api_obj 		= json_decode($get_api_answer,true);
			if (isset($api_obj['error'])) {
				echo "<p id='err_msg'>".$api_obj['error']['message']."</p>";
			} else {
				echo "<p id='green_msg'>$api_obj[message]</p>";
			}
		}	
		
		$options = get_option("NameDayReminderWidget");
  
		if (!is_array( $options )) {
			$options = array('title' => 'Name day reminder','api_key' => ''); 
		}     
		
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
    $title = $instance['title'];
    $title = (!isset($title)) ? 'Name day reminder' : $title;
    $_SESSION['has_api'] = 0;
    
    $postdata 		= http_build_query(array('body' => '{"jsonrpc":"2.0","method":"GetCountryList","params":{"user":"","domain":"'.$domain.'","apiKey":"'.$api_key.'"},"id": "'.$id.'"}'));
    $opts 			= array('http' => array('method'  => 'POST','header'  => 'Content-type: application/x-www-form-urlencoded','content' => $postdata));
    $context  		= stream_context_create($opts);
    $get_countries 	= file_get_contents("http://namedayreminder.com/api_new/service.php", false, $context);
    $countries_obj 	= json_decode($get_countries,true);
    $countries_list = $countries_obj['result'];    
?>
	<p id="nameday_title">
		<label for="<?php echo $this->get_field_id('title'); ?>">Widget title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
		</label>
	</p>
	<p id="nameday_subtitle">
		<label for="NameDayReminder-WidgetTitle">Subtitle: </label>
		<input type="text" id="NameDayReminder-WidgetTitle" name="NameDayReminder-WidgetTitle" value="<?php echo $options['title'];?>" />
		<input type="hidden" id="NameDayReminder-Submit" name="NameDayReminder-Submit" value="1" />
	</p>
	<p id="nameday_api_key">
		<label for="NameDayReminder-apiKey">API Key: </label>
		<input type="text" id="apiKey" name="apiKey" value="<?php echo $api_key; ?>" />
	</p>
	<p id="nameday_show_days">		
		<label for="NameDayReminder-Show">Show name days: </label><br>
		<input type="radio" name="group1" value="row" <?php checked( '1', $row_radio ); ?>/> Row<br>
		<input type="radio" name="group1" value="list" <?php checked( '1', $list_radio ); ?> /> List
	</p>  
	<p id="nameday_countries">
	<?php 
	if (!empty($api_key)) {
		echo "<label for='NameDayReminder-Country'>Choose country: </label><br>";
		echo "<select name='country'>";
		foreach ($countries_list as $ac_key=>$ac_val) {
			foreach ($ac_val as $acc_key=>$acc_val) {
				$sel = selected($acc_key, $country);
				echo "<option value='$acc_key' $sel >$acc_val[country]</option>";
			}
		}
		echo "</select>";
	}
	?>		
	</p>  
<?php
	}
	function widget($args, $instance)
	{
		$url = plugins_url('style.css', __FILE__);
		echo '<link rel="stylesheet" href="' . $url . '" type="text/css" media="screen" />';
		
		$domain = $_SERVER['SERVER_NAME'];
		$domain = preg_replace('#^(http(s)?://)?w{3}\.#', '', $domain);
		global $wpdb;
		$date = date('d-m-Y H:i:s');
		$id = 123;
		$table_name = $wpdb->prefix."name_day_reminder";
		$select_name=$wpdb->get_row("SELECT api_key,country_id,country_text,show_row,show_list FROM $table_name WHERE id = 1",ARRAY_A);
		
		$country	= $select_name['country_id'];
		$row_radio 	= $select_name['show_row'];
		$list_radio = $select_name['show_list'];
		$api_key 	= $select_name['api_key'];
		
		extract($args, EXTR_SKIP);
		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
 
		if (!empty($title))
		echo $before_title . $title . $after_title;;
 
		$options = get_option("NameDayReminderWidget");
		echo $options['title'];
		
			$postdata 		= http_build_query(array('body' => '{"jsonrpc":"2.0","method":"GetTodayNameDaysByCountry","params":{"user":"","domain":"'.$domain.'","apiKey":"'.$api_key.'","country":"'.$country.'"},"id": "'.$id.'"}'));
			$opts 			= array('http' => array('method'  => 'POST','header'  => 'Content-type: application/x-www-form-urlencoded','content' => $postdata));
			$context  		= stream_context_create($opts);
			$get_namedays 	= file_get_contents("http://namedayreminder.com/api_new/service.php", false, $context);
			$namedays_obj 	= json_decode($get_namedays,true);
			if (isset($namedays_obj['error'])) {
			echo "<p id='notif_message'>Something went wrong, contact administrator!</p>";
			} elseif (!isset($namedays_obj['result'])) {
				echo "<p id='nameday_message'>$namedays_obj[message]</p>";
			} else {
				$namedays_list 	= $namedays_obj['result'];
				if ($list_radio) {
					echo "<ul class='name-days'>";
					foreach ($namedays_list as $ac_key=>$ac_val) {
						foreach ($ac_val as $acc_key=>$acc_val) {
							echo "<li class='name-day'>$acc_val</li>";
						}
					}					
					echo "</ul>";
				} else if ($row_radio) {
					$i = 0;
					echo "<span class='name-days'>";
					foreach ($namedays_list as $ac_key=>$ac_val) {
						foreach ($ac_val as $acc_key=>$acc_val) {
							if ($i==0) { echo "$acc_val"; } else {echo ",$acc_val";}
							$i++;
						}
					}	
					echo "</span>";
				}
			}	
			echo $after_widget;
	}	
}
// -----------------------INSTALLATION-----------------------------------
class PluginInstalation {
	static function install () {
	global $wpdb;
	$table_name = $wpdb->prefix."name_day_reminder"; 
	
	$sql = "
		CREATE TABLE $table_name (
  			id mediumint(9) NOT NULL AUTO_INCREMENT,
  			api_key varchar(500) NOT NULL,
  			country_id varchar(50) NOT NULL,
			country_text varchar(50) NOT NULL,
			show_row int(2),
			show_list int(2),
			UNIQUE KEY id (id)
		) CHARSET=UTF8 ;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	add_option("jal_db_version", $jal_db_version);
}	
	static function pluginInstallData() {
		global $wpdb;
		$table_name = $wpdb->prefix."name_day_reminder";
	
		$rows_affected = $wpdb->insert( $table_name, array( 'country_id' => 'AT', 'show_row' => 0, 'show_list' => 1 ) );
	}	
	static function pluginUninstall() {
		global $wpdb;
		$table_name = $wpdb->prefix."name_day_reminder";
		$wpdb->query("DROP TABLE IF EXISTS $table_name");
	}	
}
//------------------------INITIALIZATIONS-------------------------------------------------------------
register_activation_hook( __FILE__, array('PluginInstalation', 'install') );
register_activation_hook( __FILE__, array('PluginInstalation', 'pluginInstallData') );
register_deactivation_hook( __FILE__, array('PluginInstalation', 'pluginUninstall') );
add_action( 'widgets_init', create_function('', 'return register_widget("NameDayReminderWidget");') );

?>