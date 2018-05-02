<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: text/html; charset=UTF-8');
require_once('functions.php');
require_once('config.php');
set_time_limit(0);

$db = new SQLite3('data/base.db') or die('Unable to open database');
$__SQLITEDB = $db;
$db->exec('CREATE TABLE IF NOT EXISTS blog(id INTEGER PRIMARY KEY AUTOINCREMENT,kluch TEXT UNIQUE,url TEXT,pred TEXT);');



$files=glob("cron-files/*.txt");
$k = file($files[0]);
if(empty($k)) die;
	
foreach ($k as $k1) { //цикл по кеям внутри текущего файла
        $k1 = trim($k1);
		
		if(!banned_words($k1)) { //если слова нет в "банном списке" (файл bad_words.txt), то добавляем его в базу
			$k2=mb_ucfirst($k1);
			
			$slovo=$k1;
			
			//Получаем картинки
			if($engine=='bing') preg_match_all('!(?<=murl&quot;:&quot;).*?(?=&quot;)!siu', get_image_bing($slovo), $kartinushki);
			if($engine=='sputnik') { 
				preg_match_all('!(?<=data-source=").*?(?=")!siu', file_get_contents('http://pics.sputnik.ru/search?q='.urlencode($slovo)), $kartinushki); 
				for($c=0;$c<count($kartinushki[0]);$c++) $kartinushki[0][$c]=urldecode($kartinushki[0][$c]); 
			}
			if($engine=='yandex') {
				preg_match_all('!(?<=img_url=).*?(?=&)!siu', get_image_yandex($slovo), $kartinushki);
				for($c=0;$c<count($kartinushki[0]);$c++) $kartinushki[0][$c]=urldecode($kartinushki[0][$c]); 
			}
																	
			//Получаем заголовки и описания из Бинга по нашему кею
			$aa=get_rss_bing($slovo);
			
			//var_dump($aa);
			
			preg_match_all('/(?<=<item><title>).*?(?=<)/', $aa, $titles);
			preg_match_all('/(?<=<description>).*?(?=<)/', $aa, $descriptions);
			preg_match_all('/(?<=<link>).*?(?=<)/', $aa, $urls1);
			unset($descriptions[0][0]);
			unset($urls1[0][0]);unset($urls1[0][1]);
			
			if(empty($kartinushki[0][0])) continue; //если по кею нет картинки, пропускаем этот кей
			
			$pred='<img src="'.$kartinushki[0][0].'" alt="'.$titles[0][0].'" class="kart1"><br>';
			
			//var_dump($descriptions[0][1]);
			
			if(!empty($descriptions[0][1])) {
				$pred.=ochist($descriptions[0][1]).'.';
			}	
			
			if($gug==1) $prefix=mt_rand(1111111,9999999).'-';
			else $prefix='';
			
			dbSqlite_biginsertIGNORE('blog', array( 
				'kluch'=>$k2,
				'url'=> $prefix.translit($k1).'.html',
				'pred'=> $pred,
			));
			
			////////////////////////////////////////////////////

			file_put_contents('sitemap/sitemap_xml_adddata.csv',$k1.';'.translit($k1)."\r\n",FILE_APPEND);
			file_put_contents('sitemap/html-links.txt','<a href="http://'.$_SERVER["SERVER_NAME"].'/'.translit($k1).'">'.$k1.'</a>'."\r\n",FILE_APPEND);
			file_put_contents('sitemap/bb-links.txt','[URL="http://'.$_SERVER["SERVER_NAME"].'/'.translit($k1).'"]'.$k1.'[/URL]'."\r\n",FILE_APPEND);		
		}
	}
	
	
dbSqlite_biginsertIGNOREEnd();
unlink($files[0]);

?>


