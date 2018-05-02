<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: text/html; charset=UTF-8');
require_once('functions.php');
require_once('config.php');

set_time_limit(0);

//$db->exec('CREATE TABLE IF NOT EXISTS blog(id INTEGER PRIMARY KEY AUTOINCREMENT,kluch TEXT UNIQUE,url TEXT,pred TEXT);');

$db = mysqli_connect($host, $user, $password, $database) or die("Ошибка " . mysqli_error($db));
 
$query ="CREATE TABLE IF NOT EXISTS blog
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    kluch VARCHAR(242) NOT NULL ,
    url VARCHAR(242) NOT NULL,
	pred TEXT,
	UNIQUE(kluch)
	
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;";
$result = mysqli_query($db, $query) or die("Ошибка " . mysqli_error($db)); 

//Создадим таблицу для полных новостей
$query ="CREATE TABLE IF NOT EXISTS fullnews (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	full TEXT
)
ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;";
$result = mysqli_query($db, $query) or die("Ошибка " . mysqli_error($db)); 


///////////////////////////////////////////////////////////////////////////
//---------цикл по файлам---------------------------

foreach (glob("input/*.txt") as $file) {
	
	while (1>0) {
		$bk=filesize($file);
		if($bk==0) {
			//var_dump($bk);
			break; //если в файле закончились кеи, переходим к другому
		}
		$k1=getLastStr($file,true,1); //берем строку с кеем из текущего файла
		
		if(empty($k1)) break;
		
		$k1 = trim($k1);
		
		if(!banned_words($k1)) { //если слова нет в "банном списке" (файл bad_words.txt), то добавляем его в базу
			$k2=mb_ucfirst($k1);
			
			$slovo=$k1;
			
			//Получаем картинки
			if($engine=='bing') preg_match_all('!(?<=murl&quot;:&quot;).*?(?=&quot;)!siu', get_image_bing($slovo), $kartinushki);
			
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
			$urls2=array_slice($urls1[0],2);
			$urls2 = @array_unique($urls2);
			
			$big='';
						
			$ii=0;
			foreach($urls2 as $url) {
				if(!banned_words($url)){
					if($ii>$howmany) break;
					//echo $url;
					$con=_get_url_new($url);
					//echo $con;
					$big.=$con; //набираем тексты с сайтов в один большой текст
					$ii++;
				}	
			}
			
			//var_dump($big);
			//echo '<hr>';
			
			
			if(empty($kartinushki[0][0])) continue; //если по кею нет картинки, пропускаем этот кей

			/////////Формируем короткую новость
			$pred='<img src="'.$kartinushki[0][0].'" alt="'.$titles[0][0].'" class="kart1"><br>';
			if(!empty($descriptions[0][1])) {
				$pred.=ochist($descriptions[0][1]).'.';
			}	
			
			////////Формируем полную новость
			
			$full='';
			
			$full=$big; // кладем в полную новость спаршенный текст
			
			$kolvo=mt_rand($minimages,$maximages); $vsego=count($kartinushki[0]); if($kolvo>$vsego) $kolvo=$vsego;
																			
			if(!empty($vsego)) {
				for($i=0;$i<$kolvo;$i++) {
					$imgurl=$kartinushki[0][$i];
						if(!empty($imgurl)) {
							if(isset($titles[0][$i])) { $alt=ochist($titles[0][$i]); } else $alt='';
							if(isset($descriptions[0][$i])) { $desc=ochist($descriptions[0][$i]); } else $desc='';
							$full.='<img src="'.$imgurl.'" alt="'.$alt.'" class="kart2"><br>';
							$full.=$alt.'.<br>';
						}
				}
			}	
			
			$full.=(youtube($slovo));  //вставляем ролик из youtube по текущему кею
			
			if($hyperpoisk==1&&$zapret==0) {
							
				$full.='<div class="clearfix"></div>';
				$full.='<div style="padding: 10px;">Тэги:';
				$mass=array_unique($titles[0]);
				foreach($mass as $tit) {
					$tit=ochist($tit);						
					if(!empty($tit)&&!banned_words($tit)) {
						//$full.='<a href="/?'.$poisk.'='.urlencode($tit).'">'.$tit.'</a>,';
						$full.=$tit.', ';
					}	
				}
				$full.='</div>';
			}
			
			//////////////////////////////////////////////////////////////////////////
			if($gug==1) $prefix=mt_rand(1111111,9999999).'-';
			else $prefix='';
						
			$url1=$prefix.translit($k1).'.html';
			
			$quer="INSERT INTO fullnews SET full='$full'";
			$results = $db->query($quer);
			
			if($results!=FALSE) {
				$quer="INSERT INTO blog SET kluch='$k2', url='$url1', pred='$pred'";
				$results = $db->query($quer);
			}
			
			
			
			
			////////////////////////////////////////////////////

			file_put_contents('sitemap/sitemap_xml_adddata.csv',$k1.';'.translit($k1)."\r\n",FILE_APPEND);
			file_put_contents('sitemap/sitemap_html_adddata.csv',$k1.';'.translit($k1)."\r\n",FILE_APPEND);
			
			file_put_contents('sitemap/html-links.txt','<a href="http://'.$_SERVER["SERVER_NAME"].'/'.translit($k1).'.html">'.$k1.'</a>'."\r\n",FILE_APPEND);
			file_put_contents('sitemap/bb-links.txt','[URL="http://'.$_SERVER["SERVER_NAME"].'/'.translit($k1).'.html"]'.$k1.'[/URL]'."\r\n",FILE_APPEND);		
							
							
		}
	}	
	
	echo $file.' - OK<br>';
		
	unlink($file); //удаляем текстовик
}

//---------цикл по файлам---------------------------
if (file_exists('robots.txt')) {
    echo "<br>Файл robots.txt уже существует";
} else {
    file_put_contents('robots.txt',"User-agent: *\r\nCrawl-delay: 10\r\nUser-agent: Yandex\r\nHost: ".$_SERVER['SERVER_NAME']);
}

mysqli_close($db);
echo ('<br>The END!'); 
?>


