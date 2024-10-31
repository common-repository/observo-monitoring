<?php
/*
Plugin Name: Observo Monitoring
Plugin URI: https://www.observo-monitoring.com
Description: Observo Monitoring | www.observo-monitoring.com
Author: Matthias Graffe 
Author URI: https://www.justawesome.de/ueber-uns/
Text Domain: observo-monitoring
Version: 1.0.5
Domain Path: /lang/
*/

add_action( 'upgrader_process_complete', function( $upgrader_object, $options ) {
	$observo_monitoring_sysinfo_code = get_option('observo_sysinfo_code','');
	if(file_exists(plugin_dir_path(__FILE__).'observo_monitoring_config.php'))
	{
		require_once(plugin_dir_path(__FILE__).'observo_monitoring_config.php');
		if($observo_monitoring_sysinfo_code != '')
		{
			observo_monitoring_save_option('observo_sysinfo_code',$observo_monitoring_sysinfo_code);
		}
	}
	if($observo_monitoring_sysinfo_code == '')
	{
		$digits = 6;
		$observo_monitoring_sysinfo_code = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
		observo_monitoring_save_option('observo_sysinfo_code',$observo_monitoring_sysinfo_code);
	}
	if($observo_monitoring_sysinfo_code != '')
	{
		$oldDir = plugin_dir_path(__FILE__).'/phpsysinfo_'.$observo_monitoring_sysinfo_code;
		if(file_exists($oldDir))
		{
			observo_monitoring_deleteDir($oldDir);
		}
		if(file_exists(plugin_dir_path(__FILE__).'/phpsysinfo'))
		{ 
			rename(plugin_dir_path(__FILE__).'/phpsysinfo',$oldDir);  
		}		
	}  
}, 10, 2 ); 

register_activation_hook( __FILE__, 'observo_monitoring_activation_function' );
function observo_monitoring_activation_function() 
{
	$observo_monitoring_sysinfo_code = get_option('observo_sysinfo_code','');
	
	set_transient( 'observo-monitoring-admin-notice-install', true, 5 );
	if(file_exists(plugin_dir_path(__FILE__).'phpsysinfo'))
	{ 
		$digits = 6;
		if($observo_monitoring_sysinfo_code == '')
		{
			$observo_monitoring_sysinfo_code = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
		}
		rename(plugin_dir_path(__FILE__).'phpsysinfo',plugin_dir_path(__FILE__).'/phpsysinfo_'.$observo_monitoring_sysinfo_code);  
		observo_monitoring_save_option('observo_sysinfo_code',$observo_monitoring_sysinfo_code);
	}
}
add_action( 'admin_notices', 'observo_monitoring_admin_notice_install' );

function observo_monitoring_admin_notice_install(){

    if( get_transient( 'observo-monitoring-admin-notice-install' ) ){
		$settings_link = observo_monitoring_get_status_url();
        ?>
        <div class="updated notice is-dismissible">
            <p>Observo-Monitoring: <?php print $settings_link; ?></p>
        </div>
        <?php
        delete_transient( 'observo-monitoring-admin-notice-install' );
    }
}

function observo_monitoring_get_status_url()
{
	$observo_monitoring_sysinfo_code = get_option('observo_sysinfo_code','');
	
	if(file_exists(plugin_dir_path(__FILE__).'observo_monitoring_config.php'))
	{
		require_once(plugin_dir_path(__FILE__).'observo_monitoring_config.php');
	}
	return '<a href="'.rest_url('observo-monitoring/v1/get?code='.$observo_monitoring_sysinfo_code.'').'" target="_blank">Status URL</a>'; 
}

function observo_monitoring_settings_link($links) { 
  
  $settings_link = observo_monitoring_get_status_url();
  array_unshift($links, $settings_link); 
  return $links; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'observo_monitoring_settings_link' );

function observo_monitoring( $data ) {
	global $wpdb;		
	$returnData = array();
	$returnData['status'] = 'error';
	
	$observo_monitoring_sysinfo_code = get_option('observo_sysinfo_code','');
	if(file_exists(plugin_dir_path(__FILE__).'observo_monitoring_config.php'))
	{
		require_once(plugin_dir_path(__FILE__).'observo_monitoring_config.php');
	}	
	if(isset($_GET['code']) && $_GET['code'] == $observo_monitoring_sysinfo_code)
	{	
		$time_start_overall = microtime(true); 
		$query = "SELECT * FROM {$wpdb->prefix}options WHERE option_name = 'siteurl'";
		$results = $wpdb->get_results( $query );	
		if(Count($results) > 0 && $results[0]->option_id > -1)
		{
			$returnData['status'] = 'ok';
		}			
		$returnData['cpu'] = 0;
		$returnData['disk'] = 0;
		$returnData['mem'] = 0;
		
		$sysinfo = '';	
		if($observo_monitoring_sysinfo_code != '')
		{
			$sysinfo = observo_monitoring_loadUrl(plugin_dir_url(__FILE__).'/phpsysinfo_'.$observo_monitoring_sysinfo_code.'/xml.php?plugin=complete&json');
		}
		else
		{
			if(file_exists(plugin_dir_path(__FILE__).'phpsysinfo/xml.php?plugin=complete&json'))
			{
				$sysinfo = observo_monitoring_loadUrl(plugin_dir_url(__FILE__).'/phpsysinfo/xml.php?plugin=complete&json');
			}
			else
			{
				$returnData['status'] = 'sysinfo_not_found';			
			}
		}
		
		if($sysinfo != '')
		{
			$jsonSysinfo = json_decode($sysinfo,true);
			
			$diskTotal = 0;
			$diskUsed = 0;
			foreach ($jsonSysinfo['FileSystem']['Mount'] as $mount)
			{
				$diskTotal += $mount['@attributes']['Total'];
				$diskUsed += $mount['@attributes']['Used'];
			}
			//$returnData['uptime'] = round($jsonSysinfo['Vitals']['@attributes']['Uptime'],2);
			$returnData['cpu'] = round($jsonSysinfo['Vitals']['@attributes']['CPULoad'],2);
			$returnData['disk'] = round($diskUsed / $diskTotal * 100,2);
			$returnData['mem'] = round($jsonSysinfo['Memory']['@attributes']['Percent'],2);
			
			$time_end_overall = microtime(true);
			$execution_time_overall = ($time_end_overall - $time_start_overall);
			$returnData['execution_time'] = round($execution_time_overall,6);
			$returnData['plugin_dir_path'] = plugin_dir_url(__FILE__);
		}
		else
		{		
			$returnData['status'] = 'sysinfo_empty';	
		}
	}
		
	return $returnData;
}

function observo_monitoring_loadUrl($url) 
{
	if(is_callable( 'curl_init' )) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		$data = curl_exec($ch);
		curl_close($ch);
	} 
	if( empty($data) || !is_callable('curl_init') ) {
		$opts = array('http'=>array('header' => 'Connection: close'));
		$context = stream_context_create($opts);
		$headers = get_headers($url);
		$httpcode = substr($headers[0], 9, 3);
		if( $httpcode == '200' )
			$data = file_get_contents($url, false, $context);
		else{
			$data = '';
		}
	}
	return $data;
}

add_action( 'rest_api_init', function () {
  register_rest_route( 'observo-monitoring/v1', '/get', array(
    'methods' => 'GET',
    'callback' => 'observo_monitoring',
  ) );
} );

function observo_monitoring_deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            observo_monitoring_deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

function observo_monitoring_save_option($optionName,$optionValue)
{
	if (get_option($optionName) !== false) 
	{
		update_option($optionName, $optionValue );
		wp_cache_delete ( 'alloptions', 'options' );
	} else 
	{
		add_option($optionName, $optionValue);
	}		
}