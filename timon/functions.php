<?php 
include('config.php');

function sitemapHTMLFToFunc($line) {
    if (!$line) {
        return '';
    }
	$tmp=explode(';',$line);
    return '<a href="'.$tmp[1].'.html">'.$tmp[0].'</a><br />';
}

function sitemapHTMLPutUrlsInBase($txt) {
    file_put_contents('sitemap/sitemap_html_adddata.csv', $txt, FILE_APPEND | LOCK_EX);
}

function sitemapHTMLAddUrls() {

    global $MAPHTML_MAX_ONE_PAGE;

    set_time_limit(0);
//ini_set('memory_limit', '128M');
    ini_set('ignore_user_abort', '1');

    if (!file_exists('sitemap/sitemap_html_adddata.csv') || filesize('sitemap/sitemap_html_adddata.csv') < 10) {
        return;
    }

    if (file_exists('sitemap/sitemap_html.db')) {
        list($countInPage, $lastPageI) = explode('|', file_get_contents('sitemap/sitemap_html.db'));
    } else {
        $countInPage = $lastPageI = 0;
    }

    $i_item = $countInPage;
    $i_page = $lastPageI;

// блок, в котором только html данные о ссылках
    $fpMap = gzopen('sitemap/maphtml_' . $i_page . '.block', "a9");
    $fp = fopen('sitemap/sitemap_html_adddata.csv', 'r');

    while ($line = fgets($fp)) {
        if ($i_item > $MAPHTML_MAX_ONE_PAGE) {

            $i_item = 0;
            $i_page++;

            $fpMap = gzopen('sitemap/maphtml_' . $i_page . '.block', "a9");
        }

        gzwrite($fpMap, sitemapHTMLFToFunc(trim($line)));

        $i_item++;
    }

    fclose($fp);
//  fclose($fpMap);
    gzclose($fpMap);
// очистка 
    file_put_contents('sitemap/sitemap_html_adddata.csv', '');

    file_put_contents('sitemap/sitemap_html.db', $i_item . '|' . $i_page);
}

function sitemapGetMapByPage($page_num) {
    global $MAPHTML_CACHE_TIME;

    if (!file_exists('sitemap/sitemap_links.block')) {
        sitemapHTMLAddUrls();
    }

    if (!is_file('sitemap/maphtml_' . $page_num . '.block')) {
        echo 'Ничего не найдено';
        return;
    }

    echo file_get_gzcontents('sitemap/maphtml_' . $page_num . '.block');

    list($countInPage, $lastPageI) = explode('|', file_get_contents('sitemap/sitemap_html.db'));

    $pages_html = '<div  class="map_pages">';
    $pages_html .= '<a href="/sitemap.html">1</a> ';

    for ($i = 1; $i <= $lastPageI; $i++) {
        $pages_html .= '<a href="/sitemap_' . ($i + 1) . '.html">' . ($i + 1) . '</a> ';
    }
    $pages_html .= '</div>';

    echo $pages_html;

    return;
}



///////////////////////////////////////////////////////////////////////////////////////////
function getLastStr($file, $remove = false, $count = 1) {


    if (!file_exists($file)) {
        return array();
    }
    $f = fopen($file, 'r+');
    $cursor = -1;
    $return = array();
    $filesize = filesize($file);
    for ($i = 0; $i < $count && $filesize >= abs($cursor); ++$i) {

        //var_dump($i , $cursor, );
        $line = '';
        fseek($f, $cursor, SEEK_END);
        $char = fgetc($f);

        /**
         * Trim trailing newline chars of the file
         */
        if ($char === "\n" || $char === "\r") {
            while ($char === "\n" || $char === "\r") {
                fseek($f, $cursor--, SEEK_END);
                $char = fgetc($f);
            }
        } else {
            $cursor--;
        }


        /**
         * Read until the start of file or first newline char
         */
        while ($char !== false && $char !== "\n" && $char !== "\r") {
            /**
             * Prepend the new char
             */
            $line = $char . $line;
            fseek($f, $cursor--, SEEK_END);
            $char = fgetc($f);
        }

//fclose($f);
        if (!$line) {
            break;
        }

        $return [] = $line;
    }


    if ($remove) {
        clearstatcache();

        if ((filesize($file) + $cursor) < 0) {
            ftruncate($f, 0);
        } else {

            ftruncate($f, filesize($file) + $cursor + 1);
        }
    }

    if ($count == 1) {
        return $return[0];
    }
    return $return;
}


function dbSqlite_biginsertIGNORE($table, $data, $maxInsert = 10) {
    global $__SQLITEDB, $__insertQ, $__insertI, $__fmysql_real_escape_string;


    if (!$__fmysql_real_escape_string) {
        $__fmysql_real_escape_string = create_function('$value', 'global $__SQLITEDB; return "\'" . $__SQLITEDB->escapeString ($value) ."\'";'); //addslashes
    }
    if (!$__insertQ) {
        $__insertQ = 'BEGIN TRANSACTION;';
    }

    $__insertQ .= ' INSERT OR IGNORE  INTO ' . $table . ' (`' . implode('`,`', array_keys($data)) . '`) VALUES  (' . implode(',', array_map($__fmysql_real_escape_string, $data)) . '); ';
    $__insertI++;
    if ($__insertI >= $maxInsert) {
        $__insertQ .= 'COMMIT;';
        $__SQLITEDB->exec($__insertQ);
        $__insertQ = '';
        $__insertI = 0;
        //sleep(3);
    }
}

function dbSqlite_biginsertIGNOREEnd() {
    global $__SQLITEDB, $__insertQ, $__insertI;
    if ($__insertQ) {
        $__insertQ .= 'COMMIT;';
        $__SQLITEDB->exec($__insertQ);
        $__insertQ = '';
        $__insertI = 0;
    }
}


function detect_encoding($string, $pattern_size = 3000)
{
    $list = array('cp1251', 'utf-8', 'ascii', '855', 'KOI8R', 'ISO-IR-111', 'CP866', 'KOI8U');
    $c = strlen($string);
    if ($c > $pattern_size)
    {
        $string = substr($string, floor(($c - $pattern_size) /2), $pattern_size);
        $c = $pattern_size;
    }

    $reg1 = '/(\xE0|\xE5|\xE8|\xEE|\xF3|\xFB|\xFD|\xFE|\xFF)/i';
    $reg2 = '/(\xE1|\xE2|\xE3|\xE4|\xE6|\xE7|\xE9|\xEA|\xEB|\xEC|\xED|\xEF|\xF0|\xF1|\xF2|\xF4|\xF5|\xF6|\xF7|\xF8|\xF9|\xFA|\xFC)/i';

    $mk = 10000;
    $enc = 'ascii';
    foreach ($list as $item)
    {
        $sample1 = @iconv($item, 'cp1251', $string);
        $gl = @preg_match_all($reg1, $sample1, $arr);
        $sl = @preg_match_all($reg2, $sample1, $arr);
        if (!$gl || !$sl) continue;
        $k = abs(3 - ($sl / $gl));
        $k += $c - $gl - $sl;
        if ($k < $mk)
        {
            $enc = $item;
            $mk = $k;
        }
    }
    return $enc;
}


function shortstory($row){
	//var_dump($row);
	//array(4) { ["id"]=> int(1) ["kluch"]=> string(39) "камни амулеты для рак" ["url"]=> string(35) "9029002-kamni-amulety-dlya-rak.html" ["pred"]=> string(347) "
	
	echo '<div class="short">';
	echo '<div class="posttitle"><a href="/'.$row["url"].'">'.$row["kluch"].'</a></div>';
	echo $row["pred"];
	echo '</div>';
}

if (!function_exists('mb_ucfirst') && extension_loaded('mbstring'))
{
    /**
     * mb_ucfirst - преобразует первый символ в верхний регистр
     * @param string $str - строка
     * @param string $encoding - кодировка, по-умолчанию UTF-8
     * @return string
     */
    function mb_ucfirst($str, $encoding='UTF-8')
    {
        $str = mb_ereg_replace('^[\ ]+', '', $str);
        $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
               mb_substr($str, 1, mb_strlen($str), $encoding);
        return $str;
    }
}


function perpage($db,$page,$quantity) {
// Устанавливаем количество записей, которые будут выводиться на одной странице
// Поставьте нужное вам число. Для примера я указал одну запись на страницу
//$quantity=10;

// Ограничиваем количество ссылок, которые будут выводиться перед и
// после текущей страницы
$limit=3;

// Если значение page= не является числом, то показываем
// пользователю первую страницу
if(!is_numeric($page)) $page=1;

// Если пользователь вручную поменяет в адресной строке значение page= на нуль,
// то мы определим это и поменяем на единицу, то-есть отправим на первую
// страницу, чтобы избежать ошибки
if ($page<1) $page=1;

// Узнаем количество всех доступных записей 
$num=$db->querySingle('SELECT COUNT() from blog;');
   
// Вычисляем количество страниц, чтобы знать сколько ссылок выводить
$pages = $num/$quantity;

// Округляем полученное число страниц в большую сторону
$pages = ceil($pages);

// Здесь мы увеличиваем число страниц на единицу чтобы начальное значение было
// равно единице, а не нулю. Значение page= будет
// совпадать с цифрой в ссылке, которую будут видеть посетители
$pages++; 

// Если значение page= больше числа страниц, то выводим первую страницу
if ($page>$pages) $page = 1;

// Переменная $list указывает с какой записи начинать выводить данные.
// Если это число не определено, то будем выводить
// с самого начала, то-есть с нулевой записи
if (!isset($list)) $list=0;

// Чтобы у нас значение page= в адресе ссылки совпадало с номером
// страницы мы будем его увеличивать на единицу при выводе ссылок, а
// здесь наоборот уменьшаем чтобы ничего не нарушить.
$list=--$page*$quantity;

// Делаем запрос подставляя значения переменных $quantity и $list

$sql =<<<EOF
    SELECT * FROM blog 
     LIMIT $quantity OFFSET $list;
EOF;
   $ret = $db->query($sql);

   
// Выводим все записи текущей страницы
	while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
		//echo '<div><a class="links1" href="/'.$row["url"].'">'.$row["kluch"].'</a></div>';
		shortstory($row); //выводим короткую новость
    }   


// _________________ начало блока 1 _________________

// Выводим ссылки "назад" и "на первую страницу"
if ($page>=1) {

    // Значение page= для первой страницы всегда равно единице, 
    // поэтому так и пишем
    echo '<a href="/page/1"><<</a> &nbsp; ';

    // Так как мы количество страниц до этого уменьшили на единицу, 
    // то для того, чтобы попасть на предыдущую страницу, 
    // нам не нужно ничего вычислять
    echo '<a href="/page/'.$page.'">< </a> &nbsp; ';
}

// __________________ конец блока 1 __________________

// На данном этапе номер текущей страницы = $page+1
$eto = $page+1;

// Узнаем с какой ссылки начинать вывод
$start = $eto-$limit;

// Узнаем номер последней ссылки для вывода
$end = $eto+$limit;

// Выводим ссылки на все страницы
// Начальное число $j в нашем случае должно равнятся единице, а не нулю
for ($j = 1; $j<$pages; $j++) {

    // Выводим ссылки только в том случае, если их номер больше или равен
    // начальному значению, и меньше или равен конечному значению
    if ($j>=$start && $j<=$end) {

        // Ссылка на текущую страницу выделяется жирным
        if ($j==($page+1)) echo '<a href="/page/'.$j.'">' . $j . '</a> &nbsp; ';

        // Ссылки на остальные страницы
        else echo '<a href="/page/'.$j .'">' . $j . '</a> &nbsp; ';
    }
}

// Выводим ссылки "вперед" и "на последнюю страницу"
if ($j>$page && ($page+2)<$j) {

    // Чтобы попасть на следующую страницу нужно увеличить $pages на 2
    echo '<a href="/page/'.($page+2).'"> ></a> &nbsp; ';

    // Так как у нас $j = количество страниц + 1, то теперь 
    // уменьшаем его на единицу и получаем ссылку на последнюю страницу
    echo '<a href="/page/'.($j-1).'">>></a> &nbsp; ';
}
	
}


function byletter($db,$bukva,$limit,$offset){
$sql =<<<EOF
    SELECT * from blog LIMIT $limit OFFSET $offset;
EOF;
   $ret = $db->query($sql);
   return $ret;
}   

function translit($string) 
  { 
    $table = array( 
                'А' => 'A', 
                'Б' => 'B', 
                'В' => 'V', 
                'Г' => 'G', 
                'Д' => 'D', 
                'Е' => 'E', 
                'Ё' => 'YO', 
                'Ж' => 'ZH', 
                'З' => 'Z', 
                'И' => 'I', 
                'Й' => 'J', 
                'К' => 'K', 
                'Л' => 'L', 
                'М' => 'M', 
                'Н' => 'N', 
                'О' => 'O', 
                'П' => 'P', 
                'Р' => 'R', 
                'С' => 'S', 
                'Т' => 'T', 
                'У' => 'U', 
                'Ф' => 'F', 
                'Х' => 'H', 
                'Ц' => 'C', 
                'Ч' => 'CH', 
                'Ш' => 'SH', 
                'Щ' => 'CSH', 
                'Ь' => '', 
                'Ы' => 'Y', 
                'Ъ' => '', 
                'Э' => 'E', 
                'Ю' => 'YU', 
                'Я' => 'YA', 
 
                'а' => 'a', 
                'б' => 'b', 
                'в' => 'v', 
                'г' => 'g', 
                'д' => 'd', 
                'е' => 'e', 
                'ё' => 'yo', 
                'ж' => 'zh', 
                'з' => 'z', 
                'и' => 'i', 
                'й' => 'j', 
                'к' => 'k', 
                'л' => 'l', 
                'м' => 'm', 
                'н' => 'n', 
                'о' => 'o', 
                'п' => 'p', 
                'р' => 'r', 
                'с' => 's', 
                'т' => 't', 
                'у' => 'u', 
                'ф' => 'f', 
                'х' => 'h', 
                'ц' => 'c', 
                'ч' => 'ch', 
                'ш' => 'sh', 
                'щ' => 'csh', 
                'ь' => '', 
                'ы' => 'y', 
                'ъ' => '', 
                'э' => 'e', 
                'ю' => 'yu', 
                'я' => 'ya',
                ' ' => '-' 
    ); 
 
    $output = str_replace( 
        array_keys($table), 
        array_values($table),$string 
    ); 
 
    return $output; 
}

function get_image_bing($slovo) {
	global $proxy, $pfile;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://www.bing.com/images/search?q='.urlencode($slovo));
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 6);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36');
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_0 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7A341 Safari/528.16');
	curl_setopt($ch, CURLOPT_COOKIE, "SRCHD=AF=NOFORM;SRCHHPGUSR=CW=1265&CH=430&DPR=1&ADLT=OFF;SCRHDN=ASD=0&DURL=#;WLS=C=&N=;RMS=A=g0ACEEAAAAAQ");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
	curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
	
	if($proxy==1) {
		$p=getRandLine($pfile);
		$t=explode(';',$p); //$t[0]-ip, $t[1]-port, $t[2]-user, $t[3]-pass
		if(isset($t[0])) curl_setopt($ch, CURLOPT_PROXY, $t[0]);
		if(isset($t[1])) curl_setopt($ch, CURLOPT_PROXYPORT, $t[1]);
		if(isset($t[2])) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $t[2].':'.$t[3]);
	}
	
	$out = curl_exec($ch);
	curl_close($ch);
	
	//preg_match_all('!imgurl:&quot;(.*?)&quot;!siu', $out, $lines);
	//preg_match_all('/(?<=t1=").*?(?=")/', $out, $tit);
	//return array($kartinki);
	
	return $out;
	
}

function get_rss_bing($slovo) {
	global $proxy, $pfile;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://www.bing.com/search?q='.urlencode($slovo).'&format=rss');
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 6);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36');
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_0 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7A341 Safari/528.16');
	curl_setopt($ch, CURLOPT_COOKIE, "SRCHD=AF=NOFORM;SRCHHPGUSR=CW=1265&CH=430&DPR=1&ADLT=OFF;SCRHDN=ASD=0&DURL=#;WLS=C=&N=;RMS=A=g0ACEEAAAAAQ");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
	curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);

	if($proxy==1) {
		$p=getRandLine($pfile);
		$t=explode(';',$p); //$t[0]-ip, $t[1]-port, $t[2]-user, $t[3]-pass
		if(isset($t[0])) curl_setopt($ch, CURLOPT_PROXY, $t[0]);
		if(isset($t[1])) curl_setopt($ch, CURLOPT_PROXYPORT, $t[1]);
		if(isset($t[2])) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $t[2].':'.$t[3]);
	}

	$out = curl_exec($ch);
	curl_close($ch);
	
	//preg_match_all('!imgurl:&quot;(.*?)&quot;!siu', $out, $lines);
	//preg_match_all('/(?<=t1=").*?(?=")/', $out, $tit);
	//return array($kartinki);
	
	return $out;
	
}

function get_image_yandex($slovo) {
	global $proxy, $pfile;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://yandex.ru/images/smart/search?p=0&text='.urlencode($slovo).'&nl=1&redircnt=1486402875.2');
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 6);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36');
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_0 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7A341 Safari/528.16');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
	curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	if($proxy==1) {
		$p=getRandLine($pfile);
		$t=explode(';',$p); //$t[0]-ip, $t[1]-port, $t[2]-user, $t[3]-pass
		if(isset($t[0])) curl_setopt($ch, CURLOPT_PROXY, $t[0]);
		if(isset($t[1])) curl_setopt($ch, CURLOPT_PROXYPORT, $t[1]);
		if(isset($t[2])) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $t[2].':'.$t[3]);
	}

	
	$out = curl_exec($ch);
	curl_close($ch);
		
	return $out;
	
}
	
function banned_words($string){
	$words = file('bad_words.txt');
	 foreach($words as $word){
	  // обезопаснивание слова дл¤ регул¤рки
	  $find = array('\\', '^', '$', '(', ')', '<', '[', '{', '|', '>', '.', '*', '+', '?', '/');
	  $replace = array('\\\\', '\^', '\$', '\(', '\)', '\<', '\[', '\{', '\|', '\>', '\.', '\*', '\+', '\?', '\/');
	  $word = str_replace($find, $replace, $word);
	  $word = trim($word);
		
	  // слово целиком (точное), т.е. „»—“ќ слово из списка (без префиксов и суффиксов)
	  //if(!empty($word) and preg_match('/\W'.$word.'\W/Uism', $string)) return true;

	  // вообще вхождение (неточное), т.е. crackZ, crackS и т.п.
	 	 
	 //if(!empty($word) and preg_match('/'.$word.'/Uism', $string)) return true;
	 if(!empty($word) and preg_match('/'.$word.'/i', $string)) return true;
	 
	 }
	 return false;
} //end of function	
	


// запись в XML карту сайта 
function sitemapXmlGetMapByPage($page_num = false) {
	global $MAPXML_INCLUDE_IMG,$SITE_URL;
	
	
    sitemapXMLAddUrls();

    if ($page_num === false) {

        if (!file_exists('sitemap/sitemap_xml.db')) {
            exit;
        }
        list($countInPage, $lastPageI) = explode('|', file_get_contents('sitemap/sitemap_xml.db'));

        $items = '';
        for ($i = 0; $i <= $lastPageI; $i++) {
            $items .= '<sitemap>
<loc>' . $SITE_URL . 'sitemap_' . ($i + 1) . '.xml</loc>
<lastmod>' . date(DATE_ATOM, filemtime('sitemap/mapxml_' . $i . '.block')) . '</lastmod>
</sitemap>';
        }


        $xml = '<?xml version="1.0" encoding="UTF-8"?> 
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
' . $items . '
</sitemapindex>

';
        header('Content-Type: application/xml');

        echo $xml;
        exit;
    }

    list($codeStart, $codeEnd) = array(
        0 => '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' . ($MAPXML_INCLUDE_IMG ? 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ' : '') . 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
',
        1 => "\n" . '</urlset>'
    );

    //if ($_DEBUG || (!file_exists(GSLON_PATH . '/sitemap/sitemap_xml_adddata.block')  //
    //        || ( time() - filemtime(GSLON_PATH . '/sitemap/sitemap_xml_adddata.block') > $MAPHTML_CACHE_TIME ))) {
    sitemapXMLAddUrls();
    //}



    if (!is_file('sitemap/mapxml_' . $page_num . '.block')) {
        header('HTTP/1.1 404 Not Found');
        header('Status: 404 Not Found');
        exit;
    }

    header('Content-Type: application/xml');

    echo $codeStart;

    // readfile(GSLON_PATH . '/sitemap/mapxml_' . $page_num . '.block');
    echo file_get_gzcontents('sitemap/mapxml_' . $page_num . '.block');
    echo $codeEnd;

    exit;
}

function sitemapXMLToFunc($line) {

    global $SITE_URL;

	$tmp4=explode(';',$line);
	$url='http://'.$_SERVER["SERVER_NAME"].'/'.trim($tmp4[1]);

    $xml = '<url>
		<loc>' . $url.'.html</loc>
		<changefreq>weekly</changefreq>
		<priority>0.2</priority>
	</url>';

    return $xml;
}

function sitemapXMLAddUrls() {

    global $MAPXML_MAX_ONE_PAGE;

    set_time_limit(0);
    //ini_set('memory_limit', '128M');
    ini_set('ignore_user_abort', '1');

    if (!file_exists('sitemap/sitemap_xml_adddata.csv') || filesize('sitemap/sitemap_xml_adddata.csv') < 10) {

        return;
    }

    if (file_exists('sitemap/sitemap_xml.db')) {
        list($countInPage, $lastPageI) = explode('|', file_get_contents('sitemap/sitemap_xml.db'));
    } else {
        $countInPage = $lastPageI = 0;
    }

    $i_item = $countInPage;
    $i_page = $lastPageI;

    $fpMap = gzopen('sitemap/mapxml_' . $i_page . '.block', "a9");

    $fp = fopen('sitemap/sitemap_xml_adddata.csv', 'r');

    while ($line = fgets($fp)) {


        if ($i_item > $MAPXML_MAX_ONE_PAGE) {

            $i_item = 0;
            $i_page++;
            $fpMap = gzopen('sitemap/mapxml_' . $i_page . '.block', 'w9');
        }

        // list($videoId, $title, $image, $desc) = explode('|%|', trim($line));
        //fwrite($fpMap, sitemapXMLToFunc(trim($line)));

        gzwrite($fpMap, sitemapXMLToFunc(trim($line)));
        $i_item++;
    }


    fclose($fp);
    // fclose($fpMap);
    gzclose($fpMap);
    // очистка 
    file_put_contents('sitemap/sitemap_xml_adddata.csv', '');


    file_put_contents('sitemap/sitemap_xml.db', $i_item . '|' . $i_page);
}

function file_get_gzcontents($file) {
    $sfp = gzopen($file, "rb");
    if (!$sfp) {
        return '';
    }
    $data = '';
    while (!gzeof($sfp)) {
        $data .= gzread($sfp, 4096);
    }
    gzclose($sfp);
    return $data;
}

function file_put_gzcontents($f, $content) {
    $zp = gzopen($f, "w9");
    gzwrite($zp, $content);
    gzclose($zp);
}

function ochist($str) {
	$str = trim(preg_replace('/[^\p{L}0-9\!\s]/iu', '', $str)); 
	return $str;
}

function inet($url, $post = array(), $opt = array(), $repeat = 3, $getFullInfo = false) {
    $uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";



    if (!isset($opt[CURLOPT_HTTPHEADER])) {

        $opt[CURLOPT_ENCODING] = "gzip,deflate";


        $header = array();

        $header[] = 'Accept: ' . 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';

        $header[] = "Accept-Encoding: gzip,deflate";
        $header[] = "Accept-Charset: " . 'windows-1251,utf-8;q=0.7,*;q=0.7';
        $header[] = 'Accept-Language: ' . 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3';
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 300";
        $opt[CURLOPT_HTTPHEADER] = $header;
    }



    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);

    if ($opt) {
        curl_setopt_array($ch, $opt);
    }

    $content = curl_exec($ch);
    $err = curl_errno($ch);


    $errmsg = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    /*
      if ($repeat > 0 && ( $err || !trim($content) )) {

      echo 'ERROR!';
      var_dump($url, $errmsg, $info, $opt);
      return '';
      // exit;
      return getUrl($url, $opt, $repeat - 1);
      } */
    $header = '';
    while (strpos($content, "HTTP/1.") === 0) {



        $ph = strpos($content, "\r\n\r\n");

        $header .= substr($content, 0, $ph) . "\r\n\r\n";
        $content = substr($content, $ph + 4);
    }
    // echo $header;
    // var_dump($ph, $header);
    //list($header, $content) = explode("\r\n\r\n", $content);

    if ($getFullInfo) {
        return array(
            'header' => $header,
            'info' => $info,
            'content' => $content
        );
    }

    return $content;
}

function youtube($key) {

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://www.youtube.com/results?sp=CAASAggC&q='.urlencode($key));
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
	curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
	$outch = curl_exec($ch);
	curl_close($ch);

	preg_match_all('!data-context-item-id="(.*?)"!siu', $outch, $lines);
	$countresult = count($lines[1]);
	if ($countresult > 0) {$countresult = $countresult - 1;}
	$randnum = mt_rand(0, $countresult);
	$youtubeurl = @trim($lines[1][$randnum]);

	// название:
	preg_match_all('!\<h3 class="yt-lockup-title "\>(.*?)\</h3\>!siu', $outch, $lines);
	$youtubetitle = @trim(strip_tags($lines[1][$randnum]));
	$youtubetitle = str_ireplace('Duration:', '', $youtubetitle);
	$youtubetitle = str_ireplace('Продолжительность:', '', $youtubetitle);
	$youtubetitle = str_ireplace('- Playlist', '', $youtubetitle);
	$youtubetitle = trim($youtubetitle);

	if ($youtubeurl != '') {
		$content_parser = '<p>'.$youtubetitle.'</p>
	<iframe width="100%" height="400" src="https://www.youtube.com/embed/'.$youtubeurl.'" frameborder="0" allowfullscreen></iframe>';
	} else {
		$content_parser = '';
	}
	return $content_parser;
}

function getRandLine($patch) {
    //открываем файл для чтения
    $proxyfile = fopen($patch, 'r');

    //ставим указатель в конец файла
    fseek($proxyfile, 0, SEEK_END);

    // получаем размер файла (для файлов огромного размера filesize() может не работать)
    $len = ftell($proxyfile);
    //  $len = filesize($patch );
    // Генерим случайное число в диапазоне от нуля до размера файла (минус 20 добавлено, чтоб не попасть в самый конец файла)
    $posrand = mt_rand(0, $len) - 10;

    if ($posrand < 0 || $posrand == 0) {

        fseek($proxyfile, 0);
        // Возвращаем следующую строку, которая уже точно будет полной и применяем к ней trim()
        $proxy = trim(fgets($proxyfile));

        //закрываем файл
        fclose($proxyfile);
        return $proxy;
    }

    // Перемещаем указатель в случайную позицию
    fseek($proxyfile, $posrand);

    // получаем строку, на которую мы попали указателем (строка может быть неполной, так как случайная позиция указателя может быть в любом месте строки)
    $line = fgets($proxyfile);

    // Возвращаем следующую строку, которая уже точно будет полной и применяем к ней trim()
    $proxy = trim(fgets($proxyfile));
    if (!$proxy) {
        fclose($proxyfile);
        return getLastStr($patch);
    }
    //закрываем файл
    fclose($proxyfile);
    return $proxy;
}


?>