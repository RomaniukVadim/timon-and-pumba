<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
include_once('functions.php');
include_once('config.php');
if(isset($_GET['sitemap'])) {
    if ($_GET['sitemap'] == 'main') { sitemapXmlGetMapByPage();	 } 
	else {sitemapXmlGetMapByPage($_GET['sitemap'] - 1);}
exit;
}
if (isset($_GET['htmlmap'])) {
    if ($_GET['htmlmap'] == 'main') {
        $page = 0;
    } else {
        $page = (int) $_GET['htmlmap'];
        if ($page > 1) {
            $page--;
        }
    }
    $item = 'map';
	sitemapGetMapByPage($page);
    exit;
}
$page='';
$slovo='';
$cpu=$_SERVER["REQUEST_URI"];
$cpu=substr($cpu,1);
$s=($db->querySingle('SELECT id,kluch , url , pred FROM blog WHERE url="'.$cpu.'"', true)); //$s["kluch"], $s["url"], $s["pred"]
if(isset($s["kluch"])) $slovo=$s["kluch"];
if(!isset($_GET["_route_"])) $_GET["_route_"]=$cpu;
$short=0;
if($_GET["_route_"]=="") $short=1; //страница, значит выводить короткие новости
$t1=strpos($cpu,'/');
if($t1) {
	$page=substr($cpu,$t1+1);
	$short=1; //страница, значит выводить короткие новости
}
if($zapret==1&&$cpu&&!$slovo&&$page=='') { //Если урла в ЧПУ не пустая, а слово в базе не найдено, то показать 404 ошибку
	header("HTTP/1.x 404 Not Found");
	header("Status: 404 Not Found");
	@require_once($_SERVER['DOCUMENT_ROOT'].'/404.php');
	exit();
} //end if
if($_GET["_route_"]) { //если это страница, созданная из тега, то забираем тег в title
	$y=explode('=',$_GET["_route_"]);
	if(isset($y[1])) {$tagtitle=urldecode($y[1]);}
}	
?>


<!DOCTYPE html>
<html lang="ru">
<head>
<?php 
	if($_GET["_route_"]=="") $t2='Блог'; //Если это главная страница
	if(!empty($slovo)) $t2=mb_ucfirst($slovo); // Если это страница с картинками
	if(!isset($t2)) $t2='Блог';
?>
<title><?=$t2;?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="utf-8">

<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
<!-- bootstrap-css -->
<link href="/css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
<!--// bootstrap-css -->
<!-- css -->
<link rel="stylesheet" href="/css/style.css" type="text/css" media="all" />
<!--// css -->
<!-- font-awesome icons -->
<link href="css/font-awesome.css" rel="stylesheet"> 
<!-- //font-awesome icons -->
<!-- font -->
<link href="//fonts.googleapis.com/css?family=Playball&amp;subset=latin-ext" rel="stylesheet">
<link href="//fonts.googleapis.com/css?family=Raleway:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
<link href='//fonts.googleapis.com/css?family=Roboto+Condensed:400,700italic,700,400italic,300italic,300' rel='stylesheet' type='text/css'>
<!-- //font -->
<script src="/js/jquery-1.11.1.min.js"></script>
<script src="/js/bootstrap.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(".scroll").click(function(event){		
			event.preventDefault();
			$('html,body').animate({scrollTop:$(this.hash).offset().top},1000);
		});
	});
</script> 
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<![endif]-->
</head>
<body>
	<!-- banner -->
	<div class="banner about-banner">
		<div class="header">
			<div class="container">
				<div class="header-left">
					<div class="w3layouts-logo">
						&nbsp;
					</div>
				</div>
				<div class="header-right">
					<div class="agileinfo-social-grids">
						<ul>
							<li><a href="#"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#"><i class="fa fa-twitter"></i></a></li>
							<li><a href="#"><i class="fa fa-rss"></i></a></li>
							<li><a href="#"><i class="fa fa-vk"></i></a></li>
						</ul>
					</div>
				</div>
				<div class="clearfix"> </div>
			</div>
		</div>
		<div class="header-bottom">
			<div class="container">
				<div class="top-nav">
						<nav class="navbar navbar-default">
								<div class="navbar-header">
									<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
										<span class="sr-only">Меню</span>
										<span class="icon-bar"></span>
										<span class="icon-bar"></span>
										<span class="icon-bar"></span>
									</button>
								</div>
							<!-- Collect the nav links, forms, and other content for toggling -->
							<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
								<ul class="nav navbar-nav">
									<li><a class="list-border" href="/">Главная</a></li>
									<li><a class="list-border" href="/luck.php">Случайная новость</a></li>
								</ul>	
								<div class="clearfix"> </div>
							</div>	
						</nav>		
				</div>
			</div>
		</div>
	</div>
	<!-- //banner -->
		
	<!-- blog -->
	<div class="blog">
		<div class="container">
			<div class="agile-blog-grids">
				<div class="col-md-8 agile-blog-grid-left">
				
				<?php 
					if($short==1) { if(!isset($page)) { $page=1; } perpage($db,$page,$quantity);}	//если это вывод вывод коротких новостей
					
					
					if(isset($s["kluch"]) or isset($_GET[$poisk])) { //а это вывод полной новости
						if(isset($s["kluch"])) $slovo=$s["kluch"];
						if(isset($_GET[$poisk])) $slovo=urldecode($_GET[$poisk]);
						if(banned_words($slovo)) $slovo=''; //если слово "плохое", делаем его "пустым"
						//Получаем картинки
						if($engine=='bing') preg_match_all('!(?<=murl&quot;:&quot;).*?(?=&quot;)!siu', get_image_bing($slovo), $kartinushki);
						if($engine=='yandex') {
							preg_match_all('!(?<=img_url=).*?(?=&)!siu', get_image_yandex($slovo), $kartinushki);
							for($c=0;$c<count($kartinushki[0]);$c++) $kartinushki[0][$c]=urldecode($kartinushki[0][$c]); 
						}
						//Получаем заголовки и описания из Бинга по нашему кею
						$aa=get_rss_bing($slovo);
						preg_match_all('/(?<=<item><title>).*?(?=<)/', $aa, $titles);
						preg_match_all('/(?<=<description>).*?(?=<)/', $aa, $descriptions);
						preg_match_all('/(?<=<link>).*?(?=<)/', $aa, $urls1);
						unset($descriptions[0][0]);
						unset($urls1[0][0]);unset($urls1[0][1]);
						$im='';
						$kolvo=mt_rand($minimages,$maximages); $vsego=count($kartinushki[0]); if($kolvo>$vsego) $kolvo=$vsego;
						if(!empty($vsego)) {
							for($i=0;$i<$kolvo;$i++) {
								$imgurl=$kartinushki[0][$i];
									if(!empty($imgurl)) {
										if(isset($titles[0][$i])) { $alt=ochist($titles[0][$i]); } else $alt='';
										if(isset($descriptions[0][$i])) { $desc=ochist($descriptions[0][$i]); } else $desc='';
										$im.='<img src="'.$imgurl.'" alt="'.$alt.'" class="kart2"><br>';
										$im.=$alt.'.<br>';
									}
							}
						}	
						
						echo '<h1>'.$slovo.'</h1>';
						echo $im; // выводим блок картинок
						echo (youtube($slovo));  //выводим ролик из youtube по текущему кею
						include('rand_images.php'); //выводим случайные новости с картинкой
						if($hyperpoisk==1&&$zapret==0) {
							echo '<div class="clearfix"></div>';
							echo '<div>Тэги:';
							$mass=array_unique($titles[0]);
							foreach($mass as $tit) {
								$tit=ochist($tit);						
								if(!empty($tit)&&!banned_words($tit)) {
									echo '<a href="/?'.$poisk.'='.urlencode($tit).'">'.$tit.'</a>,';
								}	
							}
							echo '</div>';
						}
						}
				?>
				
				
					
				</div>
				
				
				<div class="col-md-4 agile-blog-grid-right">
					<div class="categories">
						Последние записи
						<?php include('rand.php');?>
					</div>
					
				</div>
				<div class="clearfix"> </div>
			</div>
		</div>
	</div>
	<!-- //blog -->
	
	<!-- footer -->
	<div class="footer">
		<div class="container">
			<div class="agile-footer-grids">
				
				&nbsp;
				
				<div class="clearfix"> </div>
			</div>
		</div>
	</div>
	<!-- //footer -->
	<!-- copyright -->
	<div class="copyright">
		<div class="container">
			<p>© 2017</p>
			
			<!--LiveInternet counter--><script type="text/javascript"><!--
document.write("<a href=\'//www.liveinternet.ru/click\' "+
"target=_blank><img src=\'//counter.yadro.ru/hit?t26.1;r"+
escape(document.referrer)+((typeof(screen)=="undefined")?"":
";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
";"+Math.random()+
"\' alt=\'\' title=\'LiveInternet: показано число посетителей за"+
" сегодня\' "+
"border=\'0\' width=\'88\' height=\'15\'><\/a>")
//--></script><!--/LiveInternet-->
		</div>
	</div>
	<!-- //copyright -->
	<script src="/js/SmoothScroll.min.js"></script>
	<script type="text/javascript" src="/js/move-top.js"></script>
	<script type="text/javascript" src="/js/easing.js"></script>
	<!-- here stars scrolling icon -->
	<script type="text/javascript">
		$(document).ready(function() {
			/*
				var defaults = {
				containerID: 'toTop', // fading element id
				containerHoverID: 'toTopHover', // fading element hover id
				scrollSpeed: 1200,
				easingType: 'linear' 
				};
			*/
								
			$().UItoTop({ easingType: 'easeOutQuart' });
								
			});
	</script>
	<!-- //here ends scrolling icon -->
</body>	
</html>