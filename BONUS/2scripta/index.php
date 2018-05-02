<?php
//различные установки скрипта
header('Content-Type: text/html; charset=UTF-8');
#ini_set('error_reporting', E_ALL);
#ini_set('display_errors', 1);
#ini_set('display_startup_errors', 1);
require_once('config.php');
require_once('functions.php');

class MyDB extends SQLite3    { 
	function __construct()       {
       $this->open('base.db');
    }
}
$db = new MyDB();
if(!$db){ echo $db->lastErrorMsg(); }

$cpu=$_SERVER["REQUEST_URI"];
$cpu=substr($cpu,1);

$slovo=$db->querySingle('SELECT kluch from keywords where url="'.$cpu.'";');

$t1=strpos($cpu,'/');
if($t1) {
	$page=substr($cpu,$t1+1);
	$letter=urldecode(substr($cpu,0,$t1));
}

if($_GET["_route_"]==NULL) $_GET["_route_"]=$cpu;

?>


<!DOCTYPE html>
<html>
<head>
	<title><?php if(!empty($slovo)) {echo $slovo;} else echo 'Каталог картинок';?></title>
	<!--for-mobile-apps-->
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		
		<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
	<!--//for-mobile-apps-->
	
	<!-- Custom-Theme-Files -->
    <!-- Bootstrap-CSS --> 			<link rel="stylesheet" href="/css/bootstrap.min.css">
    <!-- JQuery --> 				<script src="/js/jquery.min.js"></script>
    <!-- Bootstrap-Main --> 		<script src="/js/bootstrap.min.js">		</script>
    <!-- Index-Page-Styling --> 	<link rel="stylesheet" href="/css/style.css" type="text/css" media="all">
	<!-- Font-awesome-Styling --> 	<link rel="stylesheet" href="/css/font-awesome.css" type="text/css" media="all">

	
<!-- Js for Responsive slider -->	
	<script src="/js/modernizr.js" type="text/javascript"></script>
	<script src="/js/responsiveslides.min.js"></script>
	<script> 
		// You can also use "$(window).load(function() {"
		$(function () {
		  // Slideshow 1
		  $("#slider1").responsiveSlides({
			 auto: true,
			 nav: true,
			 speed: 500,
			 namespace: "callbacks",
		  });
		});
	</script>

	<!--JS for animate-->
	<link href="/css/animate.css" rel="stylesheet" type="text/css" media="all">
	<script src="/js/wow.min.js"></script>
		<script>
			new WOW().init();
		</script>
	<!--//end-animate-->

	<script src="/js/jquery.countdown.js"></script>
	<script src="/js/script.js"></script>
	<link rel="stylesheet" href="/css/jquery.countdown.css" />
	<link rel="stylesheet" href="/css/swipebox.css">
	
</head>

<body>

<div class="header">

		<div class="nav">
			<nav class="navbar navbar-inverse navbar-fixed-top">
				<div class="container">
				<!-- Brand and toggle get grouped for better mobile display -->
						<div class="navbar-header">
							<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
						   <a class="navbar-brand" href="/">Главная</a>
						</div>

				<!-- Collect the nav links, forms, and other content for toggling -->
				
					<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
						
						<ul class="nav navbar-nav navbar-right menu slide">
							<?php	
							//Выводим меню из букв и цифр (меню каталога)
							if($_GET["_route_"]=="") { //если это главная страница
								$page=1; 
								$letter='а';
								foreach($bukvy as $bukva) { //выводим список букв
									echo '<li><a href="/'.urlencode($bukva).'/1">'.$bukva.'</a></li>';
								}
								echo '<br><br>';
							}
						?>
						</ul>
					       
					</div><!-- navbar-collapse -->
				</div><!-- container -->
			</nav>
			<div class="clearfix"></div>
		</div> <!-- Nav Ends -->
		
		
</div><!--//header-->


<!--это пример тизера-->
<div style="text-align: center;"><img src="http://pro-wordpress.ru/wp-content/uploads/2015/06/tizer2.png" style="height:150px; width: auto;"></div>
<!--это пример тизера-->




<?php
	if(isset($_GET[$poisk])) $slovo=urldecode($_GET[$poisk]);

	$tmp=get_image_bing($slovo);
	preg_match_all('!imgurl:&quot;(.*?)&quot;!siu', $tmp, $kartinushki);
	preg_match_all('/(?<=t1=").*?(?=")/', $tmp, $titles);
	
	if($slovo!=NULL&&!empty($kartinushki[0])) { 
	//если это страница с картинками и картинки найдены Бингом. Если пофиг на то, нашел что-то Бинг по запросу или пусто, тогда из этого условия вырезаем фразу "&&!empty($kartinushki[0])"
?>		

	
<div class="h-grid5-w3layouts"><!--h-grid5 gallery-->
	<div class="container">
		<div class="h-grid5-padding">
			
			<?php echo '<h1 style="text-align: center">'.$slovo.'</h1>'; ?>
			
			<div class="h-grid5">

<?php	
	if($newkeyscat.$newkeysfile!="") { //записываем найденные кеи в файл, если в конфиге название этого файла ненулевое
		foreach($titles[0] as $tit) {
			$tit=trim(strip_tags(str_replace('...','',$tit)));
			if(!empty($tit)) {
				file_put_contents($newkeyscat.$newkeysfile,$tit."\r\n",FILE_APPEND);
			}	
		}
	}	
	
	$kolvo=mt_rand($minimages,$maximages);
		
	for($i=0;$i<$kolvo;$i++) {
		$imgurl=$kartinushki[1][$i];
		if(!empty($imgurl)) {
			$alt=trim(str_replace('...','',$titles[0][$i]));
			echo '<div class="col-md-3  h-grid5-all " >';
			echo '<a href="'.$imgurl.'" rel="'.$alt.'" class="swipebox hovereffect">';
			echo '<img src="'.$imgurl.'" class="hex" alt="'.$alt.'">';
			echo '</a></div>';
		}
	}
	
	if($hyperpoisk==1) {
		echo '<div class="clearfix"> </div>';
		echo 'Тэги:';
		foreach($titles[0] as $tit) {
			$tit=trim(strip_tags(str_replace('...','',$tit)));
			if(!empty($tit)) {
				echo '<a href="/?'.$poisk.'='.urlencode($tit).'">'.$tit.'</a>,';
			}	
		}
		
	}
	
?>
				
			  <div class="clearfix"> </div>
			</div>
			
			
			
		
			
		  <div class="clearfix"> </div>
		</div>
		<!-- swipe box js -->
		<script src="/js/jquery.swipebox.js"></script>
				<script type="text/javascript">
					;( function( $ ) {

						$( '.swipebox' ).swipebox();

					} )( jQuery );
				</script>
		<!-- //swipe box js -->
	</div>
</div><!--//h-grid5-->	
	
<?php }?>

<hr>

	
<div class="footer-w3l">
	<div class="container">
		<ul class="social-agile">
			<?php if($letter===NULL) {$letter=mb_substr($slovo,0,1,'UTF-8');} perpage($db,$letter,$page,$quantity);	?>
		
		</ul>
		<div class="rights-wthree">
				<p>© 2017</p>
		</div>
	  <div class="clearfix"> </div>
	</div>
</div>
	
	
</body>
</html>