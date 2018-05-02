<?php 
include "a.charset.php";

function addNL($s){   //рандом после каждой 3-5 точкой
    $b = '';
    $sp = rand(3,5);
    for($i = 0, $il = strlen($s); $i < $il;++$i){
        $b .= $s[$i];
        if($s[$i] == '.'){
            --$sp;
            if(!$sp){
                $b .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;';
                $sp = rand(3,5);
            }
        }
    }
    return $b;
}

function a(&$arr){
            if(is_object($arr)){
                $prop = get_object_vars($arr);
                $b = true;
            }elseif(is_array($arr)){
                $prop = array_keys($arr);
            }else{
                $arr = json_decode($arr);
                $prop = get_object_vars($arr);
                $b = true;
            }
 
            foreach($prop as $k => $v) {
                if($b)
                    $el = &$arr->$k;
                else
                    $el = &$arr[$k];
                if(is_array($el)||is_object($el))
                    a($el);
                else
                    $el = iconv('cp1251','utf-8',$el);
            }
    return $el;
        }
			
			
			
function _get_random_str($file) { 

        $filesize = filesize($file);

        if ($filesize < 1)
            return false;

        if ($filesize < 1024)
            $filesize = 1024;
// открывем файл
        $fp2 = fopen($file, 'r');
        // сносим указатель на случайную позицию. но так чтобы это не оказалась последняя строка 
        $fseek = mt_rand(0, $filesize - 1024);
        fseek($fp2, $fseek); 
        
        fgets($fp2, 3024); // читаем до конца строки 
        $str = fgets($fp2, 3024); 
        fclose($fp2);

        return  $str;
}



function _get_url($url) { 

$ch = curl_init();
/* загружаемый URL */
curl_setopt($ch, CURLOPT_URL, $url);
/* отключаем (0) заголовки в выдаче */
curl_setopt($ch, CURLOPT_HEADER, 0);
/* лимит выполнения cURL-функции в секундах (15) */
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
/* возврат результат запроса в качестве строки из curl_exec()
вместо прямого вывода в браузер */
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
/* отключение реакции на HTTP-код больше или равного 400 */
curl_setopt($ch, CURLOPT_FAILONERROR, 1);
/* задаем содержание заголовка User-Agent */
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U;  i686; ru-RU; rv:1.9.2.3) Gecko/20100416  /1.9.2.3-0.2mdv2010.0 (2010.0) Firefox/3.6.3');
/* включение следования по заголовку Loction */
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
/* остановка cURL при проверке сертификата узла сети */
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$result = curl_exec($ch);
curl_close($ch);
//return $result;
if(strlen($result) > 60000){
$post1 = "";
return $post1;
}

$search = array ("'<h[^>]*?>.*?</h[^>]*?>'si",
                 "'<a[^>]*?>.*?</a>'si",  // Вырезает javaScript 
                 "'<!--.*?-->'si",  // Вырезает javaScript 
                 "'<option[^>]*?>.*?</option>'si",  // Вырезает javaScript 
                 "'<script[^>]*?>.*?</script>'si",  // Вырезает javaScript 
                 "'<[\/\!]*?[^<>]*?>'si",           // Вырезает HTML-теги 
                 "'([\r\n])[\s]+'",                 // Вырезает пробельные символы 
                 "'&(quot|#34);'i",                 // Заменяет HTML-сущности 
                 "'&(amp|#38);'i", 
                 "'&(lt|#60);'i", 
                 "'&(gt|#62);'i", 
                 "'&(nbsp|#160);'i", 
                 "'&(iexcl|#161);'i", 
                 "'&(cent|#162);'i", 
                 "'&(pound|#163);'i", 
                 "'&(copy|#169);'i", 
                 "'&#(\d+);'e");                    // интерпретировать как php-код 
 
$replace = array ("",
                  "",
                  "",
                  "", 
                  "", 
                  "", 
                  "\\1", 
                  "\"", 
                  "&", 
                  "<", 
                  ">", 
                  " ", 
                  chr(161), 
                  chr(162), 
                  chr(163), 
                  chr(169), 
                  "chr(\\1)"); 

		$text = preg_replace($search, $replace, $result); 
		
				
		$cod = detect_encoding($text);
		
		if($cod !='cp1251') {
			$text =  utf8win1251($text) ; // перекодируем в 1251
		}
						
		//$text =  charset_x_win($text); // перекодируем в 1251
		

		//$text = htmlspecialchars_decode($text);

		//(\b[A-ZА-Я](?:\w+[,;:]? ){3,}\w{2,}[.!?])
		///[A-Z][\w\d]*(?:[\s,-]+[\w\d]+)(?:[\s,-]+[\w\d]+)(?:[\s,-]+[\w\d]+)*[.!?](?=\s|<|$)/
	
	/*
	/(\b[A-ZА-Я](?:\w+[,;:]? ){3,}\w{2,}[.!?])/
	/[A-Z][\w\d]*(?:[\s,-]+[\w\d]+)(?:[\s,-]+[\w\d]+)(?:[\s,-]+[\w\d]+)*[.!?](?=\s|<|$)/ 
	*/
	
	
		$text = strip_tags($text);
		//echo $text.'<hr>';
				
	
		//preg_match_all("/.*?\.(?=(\ [A-ZА-ЯЁ]))/m", $text ,$post1); //тоже вроде работает
		
		//preg_match_all("/(?![^\.]\s+)(?![^\.]\s+[\(\"`'])([\"\`\']?[А-Я][^\.\!\?]*(.)*?)(?=[\.\!\?](\s|\Z))/", $text ,$post1);  стремный вариант
		
		//var_dump($post1);
		//echo '<hr>';
		
		preg_match_all("/[А-Я][а-я\d]*(?:[\s,-]+[а-я\d]+)(?:[\s,-]+[а-я\d]+)(?:[\s,-]+[а-я\d]+)*[.!](?=\s|<|$)/", $text ,$post1);  //моя рабочая
		
		
		//preg_match_all('/(?<=[.?!]|^).*?(?=([.?!])\s{0,3}[А-Я]|$)/s', $text ,$post1); //лучшая
		
		$e = 0;
		$max = count($post1[0]);
		for ($l=0; $l < $max; $l++) {
			if(trim($post1[0][$l])=='.') {
				 unset($post1[$l]);
			}
		}
	
		return $post1;
}



?>