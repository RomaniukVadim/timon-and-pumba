<?php

function get_bing_images($key) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://www.bing.com/images/search?q='.urlencode($key));
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 6);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36');
	curl_setopt($ch, CURLOPT_COOKIE, "SRCHD=AF=NOFORM;SRCHHPGUSR=CW=1265&CH=430&DPR=1&ADLT=OFF;SCRHDN=ASD=0&DURL=#;WLS=C=&N=;RMS=A=g0ACEEAAAAAQ");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
	curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
	$outch = curl_exec($ch);
	curl_close($ch);
	preg_match_all('!imgurl:&quot;(.*?)&quot;!siu', $outch, $lines2);

	$fotos = array_unique($lines2[1]);
	shuffle($fotos);
	
	return $fotos;
}

function get_bing_title($key) {
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://www.bing.com/search?format=rss&first=1&q='.urlencode($key)/*.'&setlang=ru-ru'*/);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 6);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
	curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
	$outch = curl_exec($ch);
	curl_close($ch);  

	$outch = str_ireplace('...', '.', $outch);
	$outch = str_ireplace(' .', '.', $outch);

	preg_match_all('!\<title\>(.*?)\</title\>!siu', $outch, $lines);
	$bing_titles = @array_unique($lines[1]);
	unset($bing_titles[0]);
	@shuffle($bing_titles);

	$title_parser = @trim($bing_titles[0]);
	$title_parser = str_ireplace('language:'.$lang, '', $title_parser);
	$title_parser = trim($title_parser);
	
}


