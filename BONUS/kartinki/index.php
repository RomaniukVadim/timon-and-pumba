<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once('functions.php');

if(isset($_GET['sitemap'])) {
    if ($_GET['sitemap'] == 'main') { sitemapXmlGetMapByPage();	 } 
else {sitemapXmlGetMapByPage($_GET['sitemap'] - 1);}
exit;
}

$cpu=$_SERVER["REQUEST_URI"];
$cpu=substr($cpu,1);

$slovo=$db->querySingle('SELECT kluch from keywords where url="'.$cpu.'";');

$t1=strpos($cpu,'/');
if($t1) {
	$page=substr($cpu,$t1+1);
	$letter=urldecode(substr($cpu,0,$t1));
}

if($zapret==1&&$cpu&&!$slovo&&empty($letter)) { //Если урла в ЧПУ не пустая, а слово в базе не найдено, то показать 404 ошибку
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); 
	header('Location: /404.php'); // перенаправляем на страницу 404.php
	exit();
} //end if

if(!isset($_GET["_route_"])) $_GET["_route_"]=$cpu;

if($_GET["_route_"]) { //если это страница, созданная из тега, то забираем тег в title
	$y=explode('=',$_GET["_route_"]);
	if(isset($y[1])) {$tagtitle=urldecode($y[1]);}
}	

?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php 
			if($_GET["_route_"]=="") $t2='Каталог картинок'; //Если это главная страница
			if(!empty($letter)) $t2='Картинки на букву (цифру) "'.$letter.'"'; //Если это страница списка кеев на какую-то букву
			if(!empty($slovo)) $t2=mb_strtoupper(mb_substr($slovo, 0, 1, 'utf-8'), 'utf-8').mb_substr($slovo, 1, null, 'utf-8'); // Если это страница с картинками
			if(!empty($tagtitle)) $t2=$tagtitle; //Если это страница, созданная на основе тега
			if(!isset($t2)) $t2='Галлерея фото';
		?>
<title><?=$t2;?></title>

		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
		<link rel="stylesheet" href="/assets/css/main.css" />
		<!--[if lte IE 9]><link rel="stylesheet" href="assets/css/ie9.css" /><![endif]-->
		<!--[if lte IE 8]><link rel="stylesheet" href="assets/css/ie8.css" /><![endif]-->
	</head>
	<body>

		<!-- Wrapper -->
			<div id="wrapper">

			<?php	
								//Выводим меню из букв и цифр (меню каталога)
								if($_GET["_route_"]=="") { //если это главная страница
									$page=1; 
									$letter='а';
									foreach($bukvy as $bukva) { //выводим список букв
										echo '<a href="/'.urlencode($bukva).'/1">'.$bukva.'</a>&nbsp;';
									}
									echo '<br><br>';
								}
							?>
					
											
							<?php 
								if($slovo!=NULL) {
									$buk=strtolower(mb_substr($slovo,0,1,'UTF-8'));
									echo '<a href="/">Главная</a> / '.'<a href="/'.urlencode($buk).'/1">'.$buk.'</a>';
									echo '<h1 style="text-align: center">'.$slovo.'</h1>';  
									echo '<p>На этой странице собраны материалы по запросу '.$slovo.'.</p>';
								}
							?>
			
				<!-- Header -->
					<header id="header">
						<h1><a href="/">Главная</a></h1>
						<nav>
							<ul>
								<li><a href="#about" class="icon fa-info-circle">О нас</a></li>
								<li><a href="#contact" class="icon fa-info-circle">Контакты</a></li>
								<li><a href="/luck.php" class="icon fa-info-circle">Случайная</a></li>
							</ul>
						</nav>
					</header>

				<!-- Main -->
					<div id="main">
						
					<?php 
						if(isset($_GET[$poisk])) $slovo=urldecode($_GET[$poisk]);
						if(banned_words($slovo)) $slovo=''; //если слово "плохое", делаем его "пустым"
										
						//Получаем картинки
						if($engine=='bing') preg_match_all('!(?<=murl&quot;:&quot;).*?(?=&quot;)!siu', get_image_bing($slovo), $kartinushki);
						if($engine=='sputnik') { 
							preg_match_all('!(?<=data-source=").*?(?=")!siu', file_get_contents('http://pics.sputnik.ru/search?q='.urlencode($slovo)), $kartinushki); 
							for($c=0;$c<count($kartinushki[0]);$c++) $kartinushki[0][$c]=urldecode($kartinushki[0][$c]); 
						}
						if($engine=='yandex') {
							preg_match_all('!(?<=img_url=).*?(?=&)!siu', get_image_yandex($slovo), $kartinushki);
							for($c=0;$c<count($kartinushki[0]);$c++) $kartinushki[0][$c]=urldecode($kartinushki[0][$c]); 
						}
																
						//Получаем заголовки и описания из Бинга по нашему кею
						$aa=file_get_contents('http://www.bing.com/search?q='.urlencode($slovo).'&format=rss');
						preg_match_all('/(?<=<item><title>).*?(?=<)/', $aa, $titles);
						preg_match_all('/(?<=<description>).*?(?=<)/', $aa, $descriptions);
						unset($descriptions[0][0]);
											
						if($slovo!=NULL) { 
											
					?>
					
<?php	
									if($newkeysfile!="") { //записываем найденные кеи в файл, если в конфиге название этого файла ненулевое
										$mass=array_unique($titles[0]);
										foreach($mass as $tit) {
											$tit=ochist($tit);
											if(!empty($tit)) { //чистим кей от мусора
												file_put_contents($newkeyscat.$newkeysfile,$tit."\r\n",FILE_APPEND);
											}	
										}
									}	
									
									$kolvo=mt_rand($minimages,$maximages);
									$vsego=count($kartinushki[0]);	
									if($kolvo>$vsego) $kolvo=$vsego;
																		
									if(!empty($vsego)) {
										for($i=0;$i<$kolvo;$i++) {
											//if($i>$vsego-1) break;
											$imgurl=$kartinushki[0][$i];
											if(!empty($imgurl)) {
												if(isset($titles[0][$i])) $alt=ochist($titles[0][$i]); else $alt='';
												if(isset($descriptions[0][$i])) $desc=ochist($descriptions[0][$i]); else $desc='';

												echo '<article class="thumb">';
												echo '<a href="'.$imgurl.'" class="image">';
												echo '<img src="'.$imgurl.'" alt="'.$alt.'" />';
												echo '<h2>'.$alt.'</h2>';
												echo '</a>';
												echo '</article>';
											}
										}//end for
										if($hyperpoisk==1&&$zapret==0) {
											echo 'Тэги:';
											$mass=array_unique($titles[0]);
											foreach($mass as $tit) {
												$tit=ochist($tit);						
												if(!empty($tit)&&!banned_words($tit)) {
													echo '<a href="/?'.$poisk.'='.urlencode($tit).'">'.$tit.'</a>,';
												}	
											}
										}
									}	
							?>					

						
		<?php } ?>	


		
					</div>

					<?php 
						if(!isset($letter)) { $letter=mb_substr($slovo,0,1,'UTF-8');}
						if(!isset($page)) { $page=1; } 
						perpage($db,$letter,$page,$quantity);	
					?>		
							
				<!-- Footer -->
					<footer id="about" class="panel">
							<div>
								<section>
									<p>Приватность</p>
						<p>Конфиденциальность очень важна для нас. Для защиты частную жизни, мы разъясняем, как информация о посещении вами данного ресурса может быть собрана и использована.         Мы собираем информацию о посещениях без привязки к конкретным ip-адресам или устройствам пользователя. Лог файлы хранятся и анализируются исключетельно в агрегированном виде. На сайте не хранится и не используется информация, которая,          сама по себе, позволяют идентифицировать отдельных пользователей.          Любые куки, которые могут быть использованы этом сайте используются либо исключительно на основе каждой сессии или для поддержания          пользовательских настройки. Куки не передаются каким бы то ни было третьим сторонам.</p>
						<p>Другие участники системы</p>
						<p>Мы используем счетчики Яндекс.Метрики для сбора и анализа посещений ресурса. Яндекс.Метрика использует куки для анализа использования сайта польователями. Мы так же можем использвать некоторые другие сервисы для анализа посещаемости сайта. Если вы не хотите чтоб вас отслеживали системы аналитики, вы можете воспользоваться любым расширением для браузера, которое позволяет блокировать сбор информации сторонними сайтами.</p>
								</section>
							</div>
					</footer>
					
					
					<footer id="contact" class="panel">
						<div class="inner split">
							
							<div>
								<section>
									<p>ВНИМАНИЕ: Мы не обладаем какими либо авторскими правами или другими правами на размещенные на сайте картинки. Большинство изображений на сайте размещены автоматически, путем создания прямыс ссылок, из открытых ресурсов интернета. Если Вы являетесь автором изображений или фотографий, размещенных на данном ресурсе, имеете на них авторские права и не хотите чтоб они были размещены здесь (либо на ресурсе указан неверный источник), Вы можете воспользоваться двумя нижеизложенными вариантами.</p>
<p>Способ 1<br>
Вы можете изменить конфигурацию вашего сайта для того чтоб изображения не могли быть использованы напрямую с других сайтов. Например, вы можете использовать mod_rewrite дополнение к вебсерверу. После того как вы включите запрет на показ изображений - они автоматически пропадут из нашей выдачи.<br>
Способ 2<br>
Воспользуйтесь формой и отправьте сообщение в следующем формате:<br>
-вкратце опишите как вы относитесь к изображению (владелец, создатель, обладаете авторскими правами);<br>
-какое действие мы должны сделать - удалить изображение с сайта, добавить ссылку на правильный источник, другое;<br>
-прямой URL страницы где размещено изображение (http://<?=$_SERVER['HTTP_HOST']?>/kartinka);<br>
-прямой URL интересующей вас картинки. Его можно получить кликнув правой кнопкой мыши на изображение и далее выбрав пункт меню "скопиривать URL изображения".</p>
								</section>
							</div>
							
							<div>
								<section>
									<?php
						//Вводим переменную 'name', которую обычные люди не видят. Её могут заполнить только-спам-боты. Если она не пуста, значит, бот пытается отправить письмо через форму.
						if (isset ($_POST['mFF'])&&empty($_POST['name'])) {
						  $st=mail($email,"заполнена контактная форма на сайте ".$_SERVER['HTTP_REFERER'], "Имя: ".$_POST['nFF']."\nEmail: ".$_POST['cFF']."\nСообщение: ".$_POST['mFF']);
						  
						  echo ('<p style="color: green">Ваше сообщение получено, спасибо!</p>');
						  //var_dump($st);
						}
					?>
					
									<form method="post" >
										<input type="text" name="name" id="name" placeholder="Имя" style="display:none;" />
										<div class="field half first">
											<input type="text" name="nFF" required placeholder="Имя" x-autocompletetype="name">
										</div>
										<div class="field half">
											<input type="text" name="cFF" id="email" placeholder="Email" />
										</div>
										<div class="field">
											<textarea name="mFF" id="message" rows="4" placeholder="Текст"></textarea>
										</div>
										<ul class="actions">
											<li><input type="submit" value="Отправить" class="special" /></li>
											<li><input type="reset" value="Reset" /></li>
										</ul>
									</form>
								</section>
							</div>
							
						</div>
					</footer>
					
			</div>

		<!-- Scripts -->
			<script src="/assets/js/jquery.min.js"></script>
			<script src="/assets/js/jquery.poptrox.min.js"></script>
			<script src="/assets/js/skel.min.js"></script>
			<script src="/assets/js/util.js"></script>
			<!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
			<script src="/assets/js/main.js"></script>

	</body>
</html>