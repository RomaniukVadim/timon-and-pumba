<?php include('config.php'); ?>
<!DOCTYPE HTML>
<html>
<head>
<title>Контакты</title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="/assets/css/main.css" />
<link rel="stylesheet" href="/contact.css" />
<style>
#feedback-form {
  max-width: 600px;
  padding: 2%;
  border-radius: 3px;
  background: #f1f1f1;
}
#feedback-form [required] {
  width: 100%;
  box-sizing: border-box;
  margin: 2px 0 2% 0;
  padding: 2%;
  border: 1px solid rgba(0,0,0,.1);
  border-radius: 3px;
  box-shadow: 0 1px 2px -1px rgba(0,0,0,.2) inset, 0 0 transparent;
}
#feedback-form [required]:hover {
  border-color: #7eb4ea;
  box-shadow: 0 1px 2px -1px rgba(0,0,0,.2) inset, 0 0 transparent;
}
#feedback-form [required]:focus {
  outline: none;
  border-color: #7eb4ea;
  box-shadow: 0 1px 2px -1px rgba(0,0,0,.2) inset, 0 0 4px rgba(35,146,243,.5);
  transition: .2s linear;
}
#feedback-form [type="submit"] {
  padding: 2%;
  border: none;
  border-radius: 3px;
  box-shadow: 0 0 0 1px rgba(0,0,0,.2) inset;
  background: #669acc;
  color: #fff;
}
#feedback-form [type="submit"]:hover {
  background: #5c90c2;
}
#feedback-form [type="submit"]:focus {
  box-shadow: 0 1px 1px #fff, inset 0 1px 2px rgba(0,0,0,.8), inset 0 -1px 0 rgba(0,0,0,.05);
}
</style>
</head>
<body>
	<!-- Wrapper -->
		<div id="wrapper" class="divided">

		
		
				<!-- Five -->
					<section class="wrapper style1 align-center">
					
						<div class="inner">

							<nav id="fh5co-menu-wrap" role="navigation">
								<a href="/">Главная</a>&nbsp;
								<a href="/luck.php">Случайная</a>&nbsp;
								<a href="/about.php">О нас</a>&nbsp;
								<a href="/contact.php">Контакты</a>
							</nav>	
						</div>

	
						<div class="blended_grid">
<div class="pageColumnLeft">
<p>ВНИМАНИЕ: Мы не обладаем какими либо авторскими правами или другими правами на размещенные на сайте картинки. Большинство изображений на сайте размещены автоматически, путем создания прямыс ссылок, из открытых ресурсов интернета. Если Вы являетесь автором изображений или фотографий, размещенных на данном ресурсе, имеете на них авторские права и не хотите чтоб они были размещены здесь (либо на ресурсе указан неверный источник), Вы можете воспользоваться двумя нижеизложенными вариантами.</p>
<p>Способ 1<br>
Вы можете изменить конфигурацию вашего сайта для того чтоб изображения не могли быть использованы напрямую с других сайтов. Например, вы можете использовать mod_rewrite дополнение к вебсерверу. После того как вы включите запрет на показ изображений - они автоматически пропадут из нашей выдачи.<br>
Способ 2<br>
Воспользуйтесь формой и отправьте сообщение в следующем формате:<br>
-вкратце опишите как вы относитесь к изображению (владелец, создатель, обладаете авторскими правами);<br>
-какое действие мы должны сделать - удалить изображение с сайта, добавить ссылку на правильный источник, другое;<br>
-прямой URL страницы где размещено изображение (http://<?=$_SERVER['HTTP_HOST']?>/kartinka);<br>
-прямой URL интересующей вас картинки. Его можно получить кликнув правой кнопкой мыши на изображение и далее выбрав пункт меню "скопиривать URL изображения".</p>
</div>
<div class="pageColumnMid">
<?php
						//Вводим переменную 'name', которую обычные люди не видят. Её могут заполнить только-спам-боты. Если она не пуста, значит, бот пытается отправить письмо через форму.
						if (isset ($_POST['mFF'])&&empty($_POST['name'])) {
						  $st=mail($email,"заполнена контактная форма на сайте ".$_SERVER['HTTP_REFERER'], "Имя: ".$_POST['nFF']."\nEmail: ".$_POST['cFF']."\nСообщение: ".$_POST['mFF']);
						  
						  echo ('<p style="color: green">Ваше сообщение получено, спасибо!</p>');
						  //var_dump($st);
						}
					?>
					<form method="POST" id="feedback-form">
						<input type="text" name="name" placeholder="Имя" style="display:none;">
						Как к Вам обращаться:
						<input type="text" name="nFF" required placeholder="Имя" x-autocompletetype="name">
						Ваш Email:
						<input type="email" name="cFF" required placeholder="Адрес электронной почты" x-autocompletetype="email">
						Ваше сообщение:
						<textarea name="mFF" required rows="5"></textarea>
						<input type="submit" value="Отправить">
					</form>
</div>

</div>	
							
							
				
				
					
				
					</section>

				<!-- Seven -->
					<section class="wrapper style1 align-center">
						<div class="inner medium">
							
					
							
							<?php include('rand.php');?>
											

						</div>
					</section>

				<!-- Footer -->
					<footer class="wrapper style1 align-center">
						<div class="inner">
							<ul class="icons">
								<li><a href="#" class="icon style2 fa-twitter"><span class="label">Twitter</span></a></li>
								<li><a href="#" class="icon style2 fa-facebook"><span class="label">Facebook</span></a></li>
								<li><a href="#" class="icon style2 fa-instagram"><span class="label">Instagram</span></a></li>
								<li><a href="#" class="icon style2 fa-linkedin"><span class="label">LinkedIn</span></a></li>
								<li><a href="#" class="icon style2 fa-envelope"><span class="label">Email</span></a></li>
							</ul>
							<p>&copy; 2017</p>
						</div>
					</footer>

			</div>

		<!-- Scripts -->
			<script src="/assets/js/jquery.min.js"></script>
			<script src="/assets/js/jquery.scrollex.min.js"></script>
			<script src="/assets/js/jquery.scrolly.min.js"></script>
			<script src="/assets/js/skel.min.js"></script>
			<script src="/assets/js/util.js"></script>
			<script src="/assets/js/main.js"></script>

	</body>
</html>