<?php
include('config.php');
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

$slovo=$db->querySingle('SELECT kluch from keywords where url="'.$cpu.'";');
$row_count = $db->querySingle('SELECT COUNT(*) FROM keywords;');
$korova=mt_rand(0, $row_count);
$query = $db->querySingle('SELECT url FROM keywords LIMIT '.$korova.',1');

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header($_SERVER["SERVER_PROTOCOL"]." 301 Moved Permanently");
header("Location: ".$query);
exit();

?>
