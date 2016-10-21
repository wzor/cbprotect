<?php
/*
 	Plugin Name: Clickbomb Protection
 	Author: WPEka Club
 	Version: 1.1
 	Description: Provides Clickbomb protection
*/

global $wpdb;
global $disable_ads;
define('PLUGIN_NAME_SLUG','CBPROTECT');
define('ADCLICK_TABLE', $wpdb->prefix."AD_CLICK");
define('CBPROTECT_VERSION', '1.0.0');
define('CBPROTECT_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('CBPROTECT_PLUGIN_INSTALL_FILE', plugin_basename(__FILE__));


include_once 'cbprotectclass.php';

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/admin.php';


function createtable_adclick()
{
	global $wpdb;
	$tablename = ADCLICK_TABLE;
	if($wpdb->get_var("show tables like $tablename")!=$tablename)
	{
		$q1 = "CREATE TABLE ".$tablename." (IP_ADDRESS varchar(20) NOT NULL , BLOCKED TINYINT(1) NOT NULL DEFAULT '0', CLICK_TIMESTAMP TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY  (IP_ADDRESS,CLICK_TIMESTAMP))";
    	$wpdb->query($q1);			
	}
}
register_activation_hook(__FILE__,'createtable_adclick');

function enqueue_plugin_scripts() {
	wp_enqueue_script( 'jquery' );
	$bombclick = new bombclick($wpdb);
	if (!is_admin()) {
		wp_enqueue_script('click-bomb-check', CBPROTECT_PLUGIN_URL. 'js/click_bomb_check.js');
		$clientdata = $bombclick->clientdetail(CBPROTECT_PLUGIN_URL.'clickupdate.php');
		if (( ($clientdata['updatedVisitCount'] >= $clientdata['clickcount']) )) {
			
			wp_enqueue_script('click-bomb-hidediv', CBPROTECT_PLUGIN_URL. 'js/hidediv.js');
		}
		wp_localize_script('click-bomb-check', 'client', $clientdata );		
		
				
	}	
}
add_action('wp_enqueue_scripts','enqueue_plugin_scripts');

?>
