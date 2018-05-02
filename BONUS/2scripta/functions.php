<?php

////////////////////////////////////////////////////////////////////////////////////////////////////////////

function perpage($db,$letter,$page,$quantity) {
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
$num=$db->querySingle('SELECT COUNT() from keywords where letter="'.$letter.'";');
   
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
    SELECT * FROM keywords where letter="$letter"
     LIMIT $quantity OFFSET $list;
EOF;
   $ret = $db->query($sql);

   
// Выводим все записи текущей страницы
	while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
		echo '<div><a class="links1" href="/'.$row["url"].'">'.$row["kluch"].'</a></div>';
    }   


echo 'Страницы: ';

// _________________ начало блока 1 _________________

// Выводим ссылки "назад" и "на первую страницу"
if ($page>=1) {

    // Значение page= для первой страницы всегда равно единице, 
    // поэтому так и пишем
    echo '<a href="/'.urlencode($letter).'/1"><<</a> &nbsp; ';

    // Так как мы количество страниц до этого уменьшили на единицу, 
    // то для того, чтобы попасть на предыдущую страницу, 
    // нам не нужно ничего вычислять
    echo '<a href="/'.urlencode($letter).'/'.$page.'">< </a> &nbsp; ';
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
        if ($j==($page+1)) echo '<a href="/'.urlencode($letter).'/'.$j.'">' . $j . '</a> &nbsp; ';

        // Ссылки на остальные страницы
        else echo '<a href="/'.urlencode($letter).'/'.$j .'">' . $j . '</a> &nbsp; ';
    }
}

// Выводим ссылки "вперед" и "на последнюю страницу"
if ($j>$page && ($page+2)<$j) {

    // Чтобы попасть на следующую страницу нужно увеличить $pages на 2
    echo '<a href="/'.urlencode($letter).'/'.($page+2).'"> ></a> &nbsp; ';

    // Так как у нас $j = количество страниц + 1, то теперь 
    // уменьшаем его на единицу и получаем ссылку на последнюю страницу
    echo '<a href="/'.urlencode($letter).'/'.($j-1).'">>></a> &nbsp; ';
}
	
}


function byletter($db,$bukva,$limit,$offset){
$sql =<<<EOF
    SELECT * from keywords where letter='$bukva' LIMIT $limit OFFSET $offset;
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
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://www.bing.com/images/search?q='.urlencode($slovo));
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 6);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36');
	curl_setopt($ch, CURLOPT_COOKIE, "SRCHD=AF=NOFORM;SRCHHPGUSR=CW=1265&CH=430&DPR=1&ADLT=OFF;SCRHDN=ASD=0&DURL=#;WLS=C=&N=;RMS=A=g0ACEEAAAAAQ");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
	curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
	$out = curl_exec($ch);
	curl_close($ch);
	
	//preg_match_all('!imgurl:&quot;(.*?)&quot;!siu', $out, $lines);
	//preg_match_all('/(?<=t1=").*?(?=")/', $out, $tit);
	//return array($kartinki);
	
	return $out;
	
}
	
	
	



?>