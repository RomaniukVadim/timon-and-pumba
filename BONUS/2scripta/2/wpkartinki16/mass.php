<?php

	set_time_limit(0);
	ini_set('max_execution_time', '900');
	//ini_set('max_execution_time', '0');
	ini_set('memory_limit','999M');
	//ignore_user_abort(true);

	/* подключаем основные функции вордпресс */
	define('WP_USE_THEMES', false);
	include($_SERVER['DOCUMENT_ROOT'].'/wp-blog-header.php');
	status_header(200); // обход ошибки 404
 
	require ('func.php');  
	require ('bing.php');  
	require ('func_my.php');	


$fns=glob("keys/*.txt");
$rfn=$fns[array_rand($fns)]; //берем случайный файл с кеями
$cat_id=trim(str_replace('keys/','',$rfn));
$cat_id=trim(str_replace('.txt','',$cat_id));

$keywordsfile=$rfn;

	$se = filesize($keywordsfile);
	if($se==0) {
		unlink($keywordsfile);	  	
		die;
	} 
		
		
	$kei = file($keywordsfile);
	$y=array_rand($kei);
	$keyword=trim($kei[0]);
	unset($kei[0]);
	
	file_put_contents($keywordsfile, "");
	foreach($kei as $k){
        file_put_contents($keywordsfile, trim($k)."\r\n", FILE_APPEND);
	}
		
	
	
///////////////////////////////////////////////////////////////////////////////////////////////
	$time = time();
			
	$kartinki = get_bing_images($keyword);	
	
	if(!empty($kartinki)) {
			
		$m=mt_rand(2,5);
			
		$blok='';
			
		for($i=0;$i<$m;$i++) {
			$g=1;
			$blok .= '<p><span itemscope itemtype="http://schema.org/ImageObject"><img src="'.$kartinki[$i].'" itemprop="contentUrl" class="img-responsive" title="'.$keyword.' #'.$i.' " alt="'.$keyword.' #'.$i.'" /></span></p>';
			
		}

			
		//////////////////вставка записи в блог//////////////////////////////	
			
		$time_cur = time(); //запоминаем текущее время в формате timestamp

		$sdvig = mt_rand(0,3600)*24; //сдвиг по времени секунды*часы*сутки
		$time = $time_cur + mt_rand(-$sdvig*100,0); //смещаем метку времени на трое суток плюс-минус шесть часов (для более правдивого вида).	
		//$time = $time_cur; //постим прямо сейчас
			
		$title = $keyword;
			
		$postdescr  .= '<p><span itemscope itemtype="http://schema.org/ImageObject"><img src="'.$kartinki[$m-1].'" itemprop="contentUrl" class="img-responsive" title="'.$keyword.' " alt="'.$keyword.'" /></span></p>';
			
		$postdescr  .= '<!--more-->';
		
		$content =$postdescr.$blok;
			
	 
		/* теперь создаем остальные переменные, которые нужны для публикации */
		$t = new Translit();
		$post_name = strtolower($title);
		$post_name = preg_replace("/\s*\-\s*/","_", $post_name);
		$post_name = str_replace(" ","_",$post_name);
		$post_name = $t->Transliterate($post_name);
		$post_name = strtolower($post_name);
		//$post_name = substr($post_name,0,15);
			
		//$cat_id = get_cat_ID($category);
		
		$categories = array($cat_id);  // список ID категорий
			 
		/* создаем массив и собираем все данные вместе */
		$the_post = array();
		$the_post['post_author'] = 1;
		$the_post['post_date'] = date('Y-m-d H:i:s',$time);
		$the_post['post_date_gmt'] = gmdate('Y-m-d H:i:s',$time);
		
		$the_post['post_content'] = mysql_escape_string($content);
		$the_post['post_title'] = mysql_escape_string($title);
		
		$the_post['post_category'] = $categories;
		$the_post['post_excerpt'] = ''; // 
		$the_post['post_status'] = 'publish';
		$the_post['comments_status'] = 'open';
		$the_post['ping_status'] = 'closed';
		$the_post['post_password'] = '';
		$the_post['post_name'] = $post_name;
		$the_post['to_ping'] = ''; 
		$the_post['pinged'] = '';
		$the_post['post_content_filtered'] = '';
		$the_post['guid'] = '';
		$the_post['post_type'] = 'post';
		$the_post['post_mime_type'] = '';
		$the_post['tags_input'] = $tags;
		$the_post['comment_count'] = 0;
		$the_post['filter'] = true; // говорим вордпресс "все ок, не надо что-то проверять и удалять из моего поста"
			 
		$wp_error=true;
		$post_ID = wp_insert_post($the_post,$wp_error);
		
		//var_dump($post_ID);

		if($post_ID>0){
			/* теперь сохраним рейтинг, т.к. он не является частью базовой структуры БД (нет такого поля) */
			//   add_post_meta($post_ID, 'rate', $rate, true);
			 
			$link = get_permalink($post_ID);
				//echo 'Пост успешно опубликован по адресу <a href="'.$link.'">'.$link.'</a>';
			}else{
			   echo '<hr>произошла ошибка<hr>';
			}

			
	//////////////////вставка записи в блог//////////////////////////////	
			
	} //endif
	
/*************************************************************************************/	
	$args = array(
		 'numberposts' => 20
		,'category' => $cat_id
		,'post_status' => 'publish'
		
	); 

	$result = wp_get_recent_posts($args);
	$lp=array_rand($result);

	$id=$result[$lp]["ID"];
		
	//echo ($id);
		
	$author = trim(_get_random_str('nicknames.txt'));
	$email = trim(_get_random_str('nicknames.txt')).'@gmail.com';
	$url='';
	$text = _get_random_str('comments.txt');

	
	//$time = current_time('mysql');
	
	$data = array(
		'comment_post_ID' => $id,
		'comment_author' => $author,
		'comment_author_email' => $email,
		'comment_author_url' => $url,
		'comment_content' => $text,
		'comment_type' => '',
		'comment_parent' => 0,
		//'user_id' => get_current_user_id(),
		'comment_author_IP' => '127.0.0.1',
		'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
		'comment_date' => $time,
		'comment_approved' => 1,
	);

	wp_insert_comment($data); // вернет ид комметария или false
		
	

