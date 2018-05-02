<?php 
include "a.charset.php";

function addNL($s){   //������ ����� ������ 3-5 ������
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
// �������� ����
        $fp2 = fopen($file, 'r');
        // ������ ��������� �� ��������� �������. �� ��� ����� ��� �� ��������� ��������� ������ 
        $fseek = mt_rand(0, $filesize - 1024);
        fseek($fp2, $fseek); 
        
        fgets($fp2, 3024); // ������ �� ����� ������ 
        $str = fgets($fp2, 3024); 
        fclose($fp2);

        return  $str;
}



function _get_url($url) { 

$ch = curl_init();
/* ����������� URL */
curl_setopt($ch, CURLOPT_URL, $url);
/* ��������� (0) ��������� � ������ */
curl_setopt($ch, CURLOPT_HEADER, 0);
/* ����� ���������� cURL-������� � �������� (15) */
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
/* ������� ��������� ������� � �������� ������ �� curl_exec()
������ ������� ������ � ������� */
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
/* ���������� ������� �� HTTP-��� ������ ��� ������� 400 */
curl_setopt($ch, CURLOPT_FAILONERROR, 1);
/* ������ ���������� ��������� User-Agent */
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U;  i686; ru-RU; rv:1.9.2.3) Gecko/20100416  /1.9.2.3-0.2mdv2010.0 (2010.0) Firefox/3.6.3');
/* ��������� ���������� �� ��������� Loction */
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
/* ��������� cURL ��� �������� ����������� ���� ���� */
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$result = curl_exec($ch);
curl_close($ch);
//return $result;
if(strlen($result) > 60000){
$post1 = "";
return $post1;
}

$search = array ("'<h[^>]*?>.*?</h[^>]*?>'si",
                 "'<a[^>]*?>.*?</a>'si",  // �������� javaScript 
                 "'<!--.*?-->'si",  // �������� javaScript 
                 "'<option[^>]*?>.*?</option>'si",  // �������� javaScript 
                 "'<script[^>]*?>.*?</script>'si",  // �������� javaScript 
                 "'<[\/\!]*?[^<>]*?>'si",           // �������� HTML-���� 
                 "'([\r\n])[\s]+'",                 // �������� ���������� ������� 
                 "'&(quot|#34);'i",                 // �������� HTML-�������� 
                 "'&(amp|#38);'i", 
                 "'&(lt|#60);'i", 
                 "'&(gt|#62);'i", 
                 "'&(nbsp|#160);'i", 
                 "'&(iexcl|#161);'i", 
                 "'&(cent|#162);'i", 
                 "'&(pound|#163);'i", 
                 "'&(copy|#169);'i", 
                 "'&#(\d+);'e");                    // ���������������� ��� php-��� 
 
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
			$text =  utf8win1251($text) ; // ������������ � 1251
		}
						
		//$text =  charset_x_win($text); // ������������ � 1251
		

		//$text = htmlspecialchars_decode($text);

		//(\b[A-Z�-�](?:\w+[,;:]? ){3,}\w{2,}[.!?])
		///[A-Z][\w\d]*(?:[\s,-]+[\w\d]+)(?:[\s,-]+[\w\d]+)(?:[\s,-]+[\w\d]+)*[.!?](?=\s|<|$)/
	
	/*
	/(\b[A-Z�-�](?:\w+[,;:]? ){3,}\w{2,}[.!?])/
	/[A-Z][\w\d]*(?:[\s,-]+[\w\d]+)(?:[\s,-]+[\w\d]+)(?:[\s,-]+[\w\d]+)*[.!?](?=\s|<|$)/ 
	*/
	
	
		$text = strip_tags($text);
		//echo $text.'<hr>';
				
	
		//preg_match_all("/.*?\.(?=(\ [A-Z�-ߨ]))/m", $text ,$post1); //���� ����� ��������
		
		//preg_match_all("/(?![^\.]\s+)(?![^\.]\s+[\(\"`'])([\"\`\']?[�-�][^\.\!\?]*(.)*?)(?=[\.\!\?](\s|\Z))/", $text ,$post1);  �������� �������
		
		//var_dump($post1);
		//echo '<hr>';
		
		preg_match_all("/[�-�][�-�\d]*(?:[\s,-]+[�-�\d]+)(?:[\s,-]+[�-�\d]+)(?:[\s,-]+[�-�\d]+)*[.!](?=\s|<|$)/", $text ,$post1);  //��� �������
		
		
		//preg_match_all('/(?<=[.?!]|^).*?(?=([.?!])\s{0,3}[�-�]|$)/s', $text ,$post1); //������
		
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