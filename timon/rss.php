<?php

header('Content-Type: text/xml');

include('config.php');

/*
if ($BASETYPE == 'sqlite') {
    $list = $db->query('SELECT * FROM pages WHERE content != "" ORDER BY RANDOM() LIMIT 25;');
    $list_arr = array();
    while ($p1 = $list->fetchArray()) {

        list($page_key, ) = explode('%', $p1['page_key']);
        $p1['key'] = $page_key;
        $list_arr[] = $p1;
    }
} else {
    $list = db_query('SELECT * FROM pages WHERE content != "" ORDER BY RANDOM() LIMIT 25;');
    $list_arr = array();
    while ($p1 = mysql_fetch_assoc($list)) {

        list($page_key, ) = explode('%', $p1['page_key']);
        $p1['key'] = $page_key;
        $list_arr[] = $p1;
    }
}
*/

$content = '';
$count = 0;

$ret = $db->query('SELECT * FROM blog ORDER BY RANDOM() LIMIT '.$RSSCOUNT.';');
while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
	//echo '<div><a class="links1" href="/'.$row["url"].'">'.$row["kluch"].'</a></div>';
 
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

//$db->close(); // закрываем базу

