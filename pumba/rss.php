<?php
header('Content-Type: text/xml');
include('config.php');

$content = '';
$count = 0;

$db = mysqli_connect($host, $user, $password, $database) or die("Ошибка " . mysqli_error(db));

$ret = $db->query('SELECT * FROM blog ORDER BY RAND() LIMIT '.$RSSCOUNT.';');

while($row = $ret->fetch_assoc() ){
 
	$time_cur = time(); //запоминаем текущее время в формате timestamp
	$sdvig = mt_rand(0,3600)*2; //сдвиг по времени секунды*часы*сутки
	$time = $time_cur + mt_rand(-$sdvig,$sdvig); //смещаем метку времени на трое суток плюс-минус шесть часов (для более правдивого вида).	
  
	$content.= '
		<item>
		<title><![CDATA[' .$row["kluch"]. ']]></title>
		<link>http://' .$_SERVER["SERVER_NAME"].$row["url"].'</link>
		<guid>http://' . $_SERVER["SERVER_NAME"].$row["url"].'</guid>
		<description><![CDATA[' . $row["pred"] . ']]></description>
		<pubDate>' . date('r', $time) . '</pubDate>
		</item>
		';

    $count = $count + 1;
}



echo '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title>' . $_SERVER["SERVER_NAME"] . '</title>
<link>http://' . $_SERVER["SERVER_NAME"] . '/</link>
<atom:link href="http://' . $_SERVER["SERVER_NAME"] . '/rss.php" rel="self" type="application/rss+xml" />
<description>Последние посты</description>
<language>ru</language>
';
echo $content;
echo '
</channel>
</rss>';

mysqli_close($db);
