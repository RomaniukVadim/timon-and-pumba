<?php
/**
Plugin Name: ¤ Kama SpamBlock
Version: 1.3.3 (14-03-2011)
Plugin URI: http://wp-kama.ru/?p=95
Description: Плагин блокирует автоспам в комментариях, трекбэки и пинги проверяются на наличие обратной ссылки. Настроек не имеет, начитает рабоать сразу после активации. Подробнее <a href='http://wp-kama.ru/id_95/plagin-dlya-blokirovki-spama-v-kommentariyah-dlya-wordpress.html'>смотрите тут</a>. 
Author: Kama
Author URI: http://wp-kama.ru/
*/  



class kama_spamblock 
{

	/*=== Можно отредактировать ===*/
	var $commentform_id = 'commentform';	// ID формы комментария.
	var $sibmit_button_id = 'submit';		// ID кнопки сабмита комментария.
	var $disable_block_for_user = 1;		// Свободное комментирование для залогиненых пользователей: 1 - да, 0 - включать бот-проверку
	

	
	/*=== Дальше не редактируем ===*/
	private $nonce;
	
	function kama_spamblock(){
		add_action( 'init', array($this, 'init') );
	}
	
	function init(){
		add_action('wp_footer', 			array($this, 'kama_comment_protection') 	  );
		add_action('comment_id_not_found', 	array($this, 'post_id_not_entered'), 		 0);
		$this->nonce = preg_replace('@\d@', '', md5(date('dm')) );
		
		global $user_ID, $user_level;
		if( $user_ID && $disable_block_for_user ) return; // не блокировать для пользователей
		if( (int)$user_level >= 7 ) return; // не блокировать для уровеней администратора и редактора
		
		add_action('preprocess_comment', 	array($this, 'kama_check_nonce_on_comment'), 0);
	}
	
	/* Для вывода ошибки со страницы проверки плагина. Если не указан ID */
	function post_id_not_entered(){
		wp_die("Post id not defined!");
	}

	/* Блок */
	function kama_check_nonce_on_comment($commentdata){		
		// защита от трекбэков и пингов
		if( $commentdata['comment_type']=='trackback' || $commentdata['comment_type']=='pingback' ){
			$home_url = get_option('home');
			$pars = @file_get_contents( $commentdata['comment_author_url'] );
			if(!$pars)
				return $commentdata;
			if( !preg_match("@<a[^>]+href=['\"]$home_url@si", $pars) )
				die();
		}
		// защита от спама. Проверять тип коммента как 'comment' не получится
		else 
		{
			if( !isset($_GET[$this->nonce]) )
				wp_die("Your comment is blocked! Are you bot, or JavaScript disabled in your browser?");
		}
		return $commentdata;
	}
	
	
	function kama_comment_protection(){	
		global $post;
		if( is_singular() && 'open'==$post->comment_status ){
			$home = get_option('home');
			return print <<<HTML
<script type='text/javascript'><!--//<![CDATA[
	function {$this->nonce}() {
		document.getElementById('{$this->commentform_id}').setAttribute('action', '{$home}/wp-comments-post.php?{$this->nonce}');
		document.forms['{$this->commentform_id}'].submit();
	}
	document.getElementById('{$this->sibmit_button_id}').onclick = {$this->nonce};
//]]>-->
</script>
HTML;
		}
		return false;
	}

}

new kama_spamblock();