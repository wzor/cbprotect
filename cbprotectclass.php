<?php
//error_reporting(0);
class bombclick {

	var $wpdb = null;
	var $clientip = null;
	var $clickcount = null;
	var $clientfound = null;
	var $isblockedip = null;
	var $table_name = null;

	function __construct($dbcls=null)
	{
		if ($dbcls) $this->wpdb = $dbcls;
		else $this->getdbo();
		$this->table_name = $this->wpdb->prefix."AD_CLICK";
		$this->clientip = $this->getclientip();
		$this->clickcount = (isset($_POST['count']))?isset($_POST['count']):isset($_GET['count']);
		$this->clientfound = $this->checkclient();
	}



	function fs_get_wp_root_path()
	{
		$base = dirname(__FILE__);
		$path = false;

		if (@file_exists(dirname(dirname($base))."/wp-config.php"))
		{
			$path = dirname(dirname($base))."/";
		}
		else
			if (@file_exists(dirname(dirname(dirname($base)))."/wp-config.php"))
			{
				$path = dirname(dirname(dirname($base)))."/";
			}
			else
				$path = false;

		if ($path != false)
		{
			$path = str_replace("\\", "/", $path);
		}
		return $path;
	}

	function getdbo() {
		if (!isset($wpdb)) {			
			$wp_root_path = $this->fs_get_wp_root_path();
			include_once($wp_root_path . 'wp-load.php');
			include_once($wp_root_path .'wp-includes/wp-db.php');
		}
		global $wpdb;
		$this->wpdb = $wpdb;
	}


	function getclientip() {
		$clientip =   $_SERVER['REMOTE_ADDR']; 
		return $clientip;
	}

	function getFirstClickTimeStamp()
	{
		$getresult = null;
		$result = $this->wpdb->get_var( "SELECT CLICK_TIMESTAMP FROM ".esc_attr($this->table_name)." where IP_ADDRESS='".esc_attr($this->clientip)."' order by CLICK_TIMESTAMP asc limit 0,1");
		$fulldate = explode(" ",$result);
		$date =  $fulldate[0];
		$time = $fulldate[1];
		$firstclickdata[] = explode("-",$date);
		$firstclickdata[] = explode(":",$time);

		return $firstclickdata;

	}

	function dateDiff($start, $end)
	{
		$start_ts = strtotime($start);
		$end_ts = strtotime($end);
		$diff = $end_ts - $start_ts;
		return round($diff / 86400);
	}


	function checkclient()
	{
		$bannedperiod = get_option('cbprotect_ban_period');
		$daySpan = get_option('cbprotect_day_span');
		$clickdata = $this->getFirstClickTimeStamp();
		$clickfirstdate = $clickdata[0];
		$clickdateimplode = implode("-",$clickfirstdate);
		$clickdate = str_replace("-","",$clickdateimplode);

		$currentdatedata =  date('Y-m-d'); //date("2012-09-15");
		$currentdate = str_replace("-","",$currentdatedata);
		$enddatedata = strtotime ( '+'.$bannedperiod.' day' , strtotime ( $clickdateimplode ) ) ;
		$enddate =  str_replace("-","",date ( 'Y-m-d' , $enddatedata ));
		$endformat = date($enddate);

		$daysDiff = $this->dateDiff($clickdateimplode,$currentdatedata);

		$sql = "select IP_ADDRESS,BLOCKED from ".$this->table_name." where IP_ADDRESS='".$this->clientip."'"; 
		$results = $this->wpdb->get_results($sql);

		if(empty($results))
		{
			$countresult = 0;
			return $countresult;
		}

		else if(!empty($results))
		{
			foreach($results as $row)
			{
				$clickip = $row->IP_ADDRESS;

				if($daysDiff <= $daySpan)
				{
					$sqlquery = "select * from ".$this->table_name." where IP_ADDRESS ='".$clickip."' and CLICK_TIMESTAMP like '$clickdateimplode%'";
					$resultsql = $this->wpdb->get_results($sqlquery);
				}
				else if($daysDiff > $daySpan)
				{

					$sqlquery = "select * from ".$this->table_name." where IP_ADDRESS ='".$clickip."' and CLICK_TIMESTAMP like '$currentdatedata%'";
					$resultsql = $this->wpdb->get_results($sqlquery);
				}

				$countresult = count($resultsql);
				return $countresult;

			}
		}

	}


	function updateclick()
	{
		$clickcount = get_option('cbprotect_click_threshold');
		$bannedperiod = get_option('cbprotect_ban_period');
		$daySpan = get_option('cbprotect_day_span');

		$clickdata = $this->getFirstClickTimeStamp();
		$clickfirstdate = $clickdata[0];
		$clickdateimplode = implode("-",$clickfirstdate);
		$clickdate = str_replace("-","",$clickdateimplode);

		$currentdatedata = date('Y-m-d'); //date("2012-09-15"); 
		$currentdate = str_replace("-","",$currentdate1);
		$enddate = strtotime ( '+'.$bannedperiod.' day' , strtotime ( $clickdateimplode ) ) ;
		$enddate =  str_replace("-","",date ( 'Y-m-d' , $enddate ));
		$endformat = date($enddate);
		$daysDiff = $this->dateDiff($clickdateimplode,$currentdatedata);

		if ($this->clientfound < $clickcount)
		{
			$sql =	"INSERT INTO ".esc_attr($this->table_name)." (IP_ADDRESS, BLOCKED, CLICK_TIMESTAMP) values('".esc_attr($this->clientip)."',0,now())";
			$resultinsert = $this->wpdb->query($sql);
		}
		else
		{
			$setfield = '';
			if (($this->clientfound >= $clickcount || $this->clickcount >= $clickcount)) {
				$setfield = 'BLOCKED=1 ';

				$sql =	"UPDATE ".$this->table_name." SET ".$setfield." where IP_ADDRESS='".$this->clientip."'";
				$resultinsert = $this->wpdb->query($sql);
			}	
		}


		return $resultinsert;

	}

	function clientdetail($preurl) {
		$clientdetail = array(
			"client_ip"=>$this->getclientip(),
			"clickcount"=>get_option('cbprotect_click_threshold'),
			"bannedperiod"=>get_option('cbprotect_ban_period'),
			"preurl" => $preurl,
			"firstclickdate" => $this->getFirstClickTimeStamp(),
			"updatedVisitCount" => $this->checkclient(),
		);
		return $clientdetail;
	}

}
?>
