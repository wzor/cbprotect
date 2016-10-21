<?php
add_action( 'admin_menu', 'cbprotect_admin_menu' );

function cbprotect_admin_menu() {
		cbprotect_load_menu();
}

function cbprotect_load_menu() {
	$current_theme = get_current_theme();
	$pluginpath = CBPROTECT_PLUGIN_INSTALL_FILE;
	$active_plugin = get_option('active_plugins');
	
	$plugin_key = array_keys($active_plugin,"$pluginpath");
	$active_plugin_key = $plugin_key[0];
	
	add_submenu_page('plugins.php', __('Click Bomb Protection Configuration'), __('CBProtect Configuration'), 'manage_options', 'cbprotect-config', 'cbprotect_conf');
	
}


	global $wp_version;
	
	// all admin functions are disabled in old versions
	if ( !function_exists('is_multisite') && version_compare( $wp_version, '3.0', '<' ) ) {
	
		function cbprotect_version_warning() {
			echo "
			<div id='cbprotect-warning' class='updated fade'><p><strong>".sprintf(__('CBProtect %s requires WordPress 3.0 or higher.'), CBPROTECT_VERSION) ."</strong> ".sprintf(__('Please <a href="%s">upgrade WordPress</a> to a current version.'), 'http://codex.wordpress.org/Upgrading_WordPress'). "</p></div>
			";
		}
		add_action('admin_notices', 'cbprotect_version_warning');
	
		return;
	}

function cbprotect_admin_init() {
    global $wp_version;
    
    // all admin functions are disabled in old versions
    if ( !function_exists('is_multisite') && version_compare( $wp_version, '3.0', '<' ) ) {
        
        function cbprotect_version_warning() {
			echo "
			<div id='cbprotect-warning' class='updated fade'><p><strong>".sprintf(__('CBProtect %s requires WordPress 3.0 or higher.'), CBPROTECT_VERSION) ."</strong> ".sprintf(__('Please <a href="%s">upgrade WordPress</a> to a current version.'), 'http://codex.wordpress.org/Upgrading_WordPress'). "</p></div>
			";
		}
		add_action('admin_notices', 'cbprotect_version_warning');
		return;    
    }
}
add_action('admin_init', 'cbprotect_admin_init');

add_action( 'admin_enqueue_scripts', 'cbprotect_load_js_and_css' );

function cbprotect_load_js_and_css() {
	global $hook_suffix;

	if (
		 $hook_suffix == 'plugins_page_cbprotect-config'
	) {
		wp_register_style( 'cbprotect.css', CBPROTECT_PLUGIN_URL . 'cbprotect.css', array(), '1.0.0.1' );
		wp_enqueue_style('cbprotect.css');
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script('cbprotect-validate',CBPROTECT_PLUGIN_URL . 'js/jquery.validate.js');
		wp_enqueue_script('cbprotect-form',CBPROTECT_PLUGIN_URL . 'js/form_validate_script.js');
	
	}
}


function cbprotect_nonce_field($action = -1) { return wp_nonce_field($action); }
$cbprotect_nonce = 'cbprotect-update-key';

function cbprotect_conf() {
	global $cbprotect_nonce;

	if ( isset($_POST['submit']) ) {
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
			die(__('Cheatin&#8217; uh?'));

		check_admin_referer( $cbprotect_nonce );
		$key = preg_replace( '/[^a-h0-9]/i', '', $_POST['key'] );
		$home_url = parse_url( get_bloginfo('url') );
		

		if ( isset( $_POST['clickthreshold'] ) )
			update_option( 'cbprotect_click_threshold', $_POST['clickthreshold'] );
		
		if ( isset( $_POST['cbprotect_ban_period'] ) )
			update_option( 'cbprotect_ban_period', $_POST['cbprotect_ban_period'] );
		
		if ( isset( $_POST['cbprotect_day_span'] ) )
			update_option( 'cbprotect_day_span', $_POST['cbprotect_day_span'] );

	} 

?>
<?php if ( !empty($_POST['submit'] ) ) : ?>
<div id="message" class="updated fade"><p><strong><?php _e('Options saved.') ?></strong></p></div>
<?php endif; ?>

	<div class="wrap rm_wrap">
		<span class="headtext1"><?php _e('ClickBomb Protection Configuration'); ?></span>
		<div class="rm_opts">
			<form action="" method="post" id="cbprotect-conf" style="margin: auto; width: 960px; ">
				<div class="rm_section">
					<div class="clearfix" style="display: block;">
						<div class="rm_input rm_textvalidate">
							<div class="label">
								<label for="clickthreshold"><?php _e('Click Limit'); ?></label>
							</div>
							<div class="rm_field">
								<input id="clickthreshold" name="clickthreshold" class="required number" type="text" value="<?php if(get_option('cbprotect_click_threshold') == 0 || get_option('cbprotect_click_threshold') == null) { echo "2"; } else { echo get_option('cbprotect_click_threshold');} ?>" /> 
							</div>
							<div class="rm_desc"><small>
								(<?php _e('The max click count above which the clicks will be blocked'); ?>)
								</small>
							</div>
						</div>

						<div class="rm_input rm_text">

							<div class="label">
								<label for="cbprotect_ban_period"><?php _e('Ban Duration (In Days)'); ?> </label>
							</div>
							<div class="rm_field">
								<input name="cbprotect_ban_period" class="required number" id="cbprotect_ban_period" type="text" value="<?php if(get_option('cbprotect_ban_period') == 0 || get_option('cbprotect_ban_period') == null ) { echo "1"; } else{ echo get_option('cbprotect_ban_period'); } ?>" />
							</div>
							<div class="rm_desc"><small>(<?php _e('The duration in days for which the IP Address will be banned'); ?>)
								</small>
							</div>
						</div>
						
						
						<div class="rm_input rm_text">

							<div class="label">
								<label for="cbprotect_day_span"><?php _e('Click Duration (In Days)'); ?> </label>
							</div>
							<div class="rm_field">
								<input name="cbprotect_day_span" class="required number" id="cbprotect_day_span" type="text" value="<?php if(get_option('cbprotect_day_span') == 0 || get_option('cbprotect_day_span') == null) { echo "7"; } else{ echo get_option('cbprotect_day_span'); } ?>" />
							</div>
							<div class="rm_desc"><small>(<?php _e('Consider Clicks since the last n days.'); ?>)
								</small>
							</div>
						</div>
					</div>
				</div>
				<p class="submit"><input type="submit" name="submit" value="<?php _e('Update options &raquo;'); ?>" /></p>

				<?php cbprotect_nonce_field($cbprotect_nonce); ?>
			</form>

		</div>
	</div>
<?php unblockIP(); ?>
<form method="post" id="ipblocktable">
	<div>
		<span class="headtext2"><?php _e('Blocked IP Addresses'); ?></span>
		<table id="ipdata">
			<tr>
				<th style='width: 40%;'><strong>IP Address</strong></th>
				<th style='width: 20%;'><strong>Click Count</strong></th>
				<th style='width: 20%;'><strong>Last Clicked On</strong></th>
				<th style='width: 20%;'><strong>Unblock IP's</strong></th>
			</tr>
			<?php 
				global $wpdb;
				
				$table_adclick = ADCLICK_TABLE;
				$sql = "select *,max(CLICK_TIMESTAMP) as CLICK_TIMESTAMP from ".$table_adclick." where BLOCKED=1 group by IP_ADDRESS";
				$result = $wpdb->get_results($sql);
				
				if(!empty($result))
				{
				foreach($result as $row)
				{
					$ip	= $row->IP_ADDRESS;
					$timestamp = $row->CLICK_TIMESTAMP;
					$blocked = $row->BLOCKED;
					$query = "select * from ".$table_adclick." where IP_ADDRESS ='".$ip."' and BLOCKED=1 order by CLICK_TIMESTAMP desc";
					$results = $wpdb->get_results($query);
					$countresult = count($results);	
			?>
					<tr>
						<td><?php echo $ip; ?><input type='hidden' name='ipaddress' id='ipaddress' value='<?php echo $ip; ?>' />
						</td>
						<td><?php echo $countresult; ?></td>
					  	<td><?php echo ($timestamp=='0000-00-00 00:00:00')?'':date('Y-m-d  H:i:s',strtotime($timestamp));?></td>
						<td><div class='check'><input id="checkbox[]" type='checkbox' name='checkbox[]' value="<?php echo $ip; ?>" <?php echo ($blocked ?' checked="checked" ':''); ?>/></div><br /></td>
					</tr>
		<?php	}
				}
		 ?>
		</table>
	</div>
	<div class='btnUnblock'>
		<p>
			<input type='submit' name='btnUnblock' class='button-primary' value='Unblock IP' />
		</p>
	</div>
</form>

<?php

}

function unblockIP()
{
	if (isset($_POST['btnUnblock']))
	{
		$ipaddr = $_POST['ipaddress'];
		$checkbox = $_POST['checkbox']; 
				
		global $wpdb;
		$table_bomb_click = ADCLICK_TABLE;
	
		for($i=0;$i<count($checkbox);$i++)
		{
			if($checkbox[$i] != "")
			{
				$checkboxdata = $checkbox[$i]; 
				$strSQL = "DELETE FROM $table_bomb_click WHERE IP_ADDRESS ='".$checkboxdata."' ";
				$results = $wpdb->query($strSQL);
			}
		}
	
	}
}


?>