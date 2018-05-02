<?php
include('config.php');
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

$db = mysqli_connect($host, $user, $password, $database) or die("Ошибка " . mysqli_error(db));
//$sl=$db->query('SELECT kluch from blog where url="'.$cpu.'";');
//$slovo = $sl->fetch_row()[0];

$ress=$db->query('SELECT COUNT(*) FROM blog');
$row_count = $ress->fetch_row()[0];

$korova=mt_rand(0, $row_count);
$quer = $db->query('SELECT url FROM blog LIMIT '.$korova.',1');
$query = $quer->fetch_row()[0];

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header($_SERVER["SERVER_PROTOCOL"]." 301 Moved Permanently");
header("Location: ".$query);
exit();


?>
