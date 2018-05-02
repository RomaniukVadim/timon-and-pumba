<?php
	ini_set('memory_limit','512M');

function vzyatpredlozhenya ($ot,$do) {
	$t ='';
	$kolvo = mt_rand($ot,$do); //определяем, сколько брать предложений
	$textovka = file('./txt/'.mt_rand(0,9).'.txt');
	$r = array_rand($textovka,$kolvo);
	foreach($r as $p) {
		$t.=trim($textovka[$p]).'<br>';
	}
	return $t;	
}

function poluchit_horoshuyu_kartinku($images) {
		foreach($images as $im) {
			if(!empty($im)) {
					$zag = get_headers($im);
					if($zag[0]=='HTTP/1.1 200 OK') {
							return $im;			
					} //end if
			} //end if		
	 	} //end foreach
	 	return 'nifiga';
}

function get_good_image($images) {
		foreach($images as $im) {
			if(!empty($im)) {
				try {
				    $img = AcImage::createImage($im);	
						return $im;   
				} 
				catch(Exception $e) {
    				//echo $e->getMessage();
    				
				}
		}
}
	return 'nifiga';

}

function sohranit_kartinku($imageurl,$keyword) {
		$image = $imageurl;
		$file = basename($image);
		$file = './img/'.$file;
		$imgcontent = file_get_contents($image);
				
		if(empty($imgcontent)) {
			continue;
		}
				

		$keyword1 = $keyword; 
		$newname = translit($keyword1);
		$newname = str_replace(' ','-',$newname);
		$newname = '../img/'.$newname.'-'.mt_rand(1111,9999).'.jpg';
				
		//echo $newname.'<br>';
				
		$fp = fopen($newname , 'w+');
		fwrite($fp,$imgcontent);
		fclose($fp); 	
		
		return $newname;
		
}

function resizeToVariable($sourceImage,$newHeight,$newWidth,$destImage)
{
    list($width,$height) = getimagesize($image);
    $img = imagecreatefromjpeg($sourceImage);
    // create a new temporary image
    $tmp_img = imagecreatetruecolor($newHeight,$newWidth);
    // copy and resize old image into new image
    imagecopyresized( $tmp_img, $img, 0, 0, 0, 0,$newHeight,$newWidth, $width, $height );
    // use output buffering to capture outputted image stream
    ob_start();
    imagejpeg($tmp_img);
    $i = ob_get_clean(); 
    // Save file
    $fp = fopen ($destImage,'w');
    fwrite ($fp, $i);
    fclose ($fp);
}

function utf8win1251($s){
  $out="";$c1="";$byte2=false;
  for ($c=0;$c<strlen($s);$c++) {
	$i=ord($s[$c]);
	if ($i<=127) $out.=$s[$c];
	if ($byte2) {
	  $new_c2=($c1&3)*64+($i&63);
	  $new_c1=($c1>>2)&5;
	  $new_i=$new_c1*256+$new_c2;
	  if ($new_i==1025) $out_i=168; else
	  if ($new_i==1105) $out_i=184; else $out_i=$new_i-848;
	  $out.=chr($out_i);
	  $byte2=false;
	} if (($i>>5)==6) {$c1=$i; $byte2=true;}
  } return $out;
}
			
function detect_encoding($string, $pattern_size = 50)
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

function findcategory($f) {
	$f= trim($f); 
	$c = file('categories.txt');
	
	foreach ($c as $ca) {
		$categ = explode(';',$ca);
		$t1 = trim($categ[0]);
		$t2 = trim($categ[1]);
				
			if($f == $t1) {
				return $t2;
			}
		}
	return '';
}

function get_keyword($keywordsfile) {
    $file=file($keywordsfile);
    $fp=fopen($keywordsfile,"w");
    $keyword = $file[0];
    unset($file[0]);
    fputs($fp,implode("",$file));
    fclose($fp);
    return trim($keyword);
}

function translit($cyr_str) {
	//nastart('translit');
	$razd="-";
	$cyr_str=strtolower($cyr_str);
	$tr =  array("А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",               
	"Д"=>"d","Е"=>"e","Ж"=>"zh","З"=>"z","И"=>"i",               
	"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",               
	"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",               
	"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"c","Ч"=>"ch",               
	"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"y","Ь"=>"",               
	"Э"=>"e","Ю"=>"u","Я"=>"ya","а"=>"a","б"=>"b",               
	"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"zh",               
	"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",               
	"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",               
	"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",               
	"ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"",               
	"ы"=>"y","ь"=>"","э"=>"e","ю"=>"u","я"=>"ya", " " => $razd); 
	$text= strtr($cyr_str, $tr); 
	$text=preg_replace("/[^a-z0-9_ -]*/", "", $text);
	//nastop('translit');
	return $text;
}


// класс для транслитерации русских букв
class Translit{
   var $cyr = array(
      "Щ",  "Ш", "Ч", "Ц","Ю", "Я", "Ж", "А","Б","В","Г","Д","Е","Ё","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х", "Ь","Ы","Ъ","Э","Є","Ї",
      "щ",  "ш", "ч", "ц","ю", "я", "ж", "а","б","в","г","д","е","ё","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х", "ь","ы","ъ","э","є","ї");
   var $lat = array(
      "Sch","Sh","Ch","Ts","Yu","Ya","Zh","A","B","V","G","D","Ye","Yo","Z","I","Y","K","L","M","N","O","P","R","S","T","U","F","H","","Y","","E","Je","Ji",
      "sch","sh","ch","ts","yu","ya","zh","a","b","v","g","d","ye","yo","z","i","y","k","l","m","n","o","p","r","s","t","u","f","h","","y","","e","je","ji"
   );
   function Transliterate($str){
      for($i=0; $i<count($this->cyr); $i++){
         $c_cyr = $this->cyr[$i];
         $c_lat = $this->lat[$i];
         $str = str_replace($c_cyr, $c_lat, $str);
      }
      $str = preg_replace("/([qwrtpsdfghklzxcvbnmQWRTPSDFGHKLZXCVBNM]+)[yY]e/", "\${1}e", $str);
      $str = preg_replace("/([qwrtpsdfghklzxcvbnmQWRTPSDFGHKLZXCVBNM]+)[yY]/", "\${1}y", $str);
      $str = preg_replace("/([eyuioaEYUIOA]+)[Kk]h/", "\${1}h", $str);
      $str = preg_replace("/^kh/", "h", $str);
      $str = preg_replace("/^Kh/", "H", $str);
      return $str;
   }
}

?>