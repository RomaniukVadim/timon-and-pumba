<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: text/html; charset=UTF-8');
require_once('functions.php');
require_once('config.php');

set_time_limit(0);

$cs=0; //счетчик вход. файлов
foreach (glob("input/*.txt") as $file) {
	
	$cl = 0; //текущая строка
	$cf = 0; //текущий файл (выходной)

	$outdir='cron-files/';
	$source = fopen($file, 'r');	
	$outhndl = fopen($outdir.'cron'.$cs.'-'.$cf.'.txt', 'w');
	while (!feof($source)) {
		fputs($outhndl, fgets($source, 1024));//записываем в новый файл что считали с входного
		$cl++;
		if ($cl == $skolko_keev_postit_po_cronu){//как только набралось N строк
			$cl = 0;
			fclose($outhndl);//закрываем выходной файл
			$outhndl = fopen($outdir.'cron'.$cs.'-'.$cf.'.txt', 'w');//открываем новый...
			$cf++;
		} //end if
	} //endwhile
	$cs++;
	fclose($source);
	echo $file.' - OK<br>';
	unlink($file);
	
}//end foreach

echo 'The END :)';