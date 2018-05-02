<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: text/html; charset=UTF-8');
require_once('functions.php');
require_once('config.php');
set_time_limit(0);
$db = new SQLite3('base.db') or die('Unable to open database');
$__SQLITEDB = $db;
$db->exec('CREATE TABLE IF NOT EXISTS keywords(letter TEXT,kluch TEXT UNIQUE,url TEXT);');

function dbSqlite_biginsertIGNORE($table, $data, $maxInsert = 1000) {
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

///////////////////////////////////////////////////////////////////////////
//---------цикл по файлам---------------------------

foreach (glob("input/*.txt") as $file) {
    $k = file($file);
    
	foreach ($k as $k1) { //цикл по кеям внутри текущего файла
        $k1 = trim($k1);
        dbSqlite_biginsertIGNORE('keywords', array( 
            'letter'=>mb_strtolower(mb_substr($k1,0,1,'UTF-8'), 'utf-8') ,
            'kluch'=>$k1 ,
            'url'=> translit($k1),
            ));
		file_put_contents('sitemap/sitemap_xml_adddata.csv',$k1.';'.translit($k1)."\r\n",FILE_APPEND);
    }
	dbSqlite_biginsertIGNOREEnd();
	echo $file.' - OK';
	echo('<hr>');
	unlink($file);
}

//---------цикл по файлам---------------------------
echo ('The END!'); 
?>


