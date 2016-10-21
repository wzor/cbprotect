<?php
require_once('cbprotectclass.php');

$bombclick = new bombclick();

$result['result'] = $bombclick->updateclick();
$result['message'] = ($result['result'])?'':'Error';
//_log("result in clickupdate page".$result['message']);
header( "Content-Type: application/json" );
echo json_encode (array('success' => $result['result'], 'message' => $result['message']));
exit;

?>
