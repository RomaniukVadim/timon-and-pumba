/*
 * jsImageBox - slim and simple image modal viewer for webpages http://www.jsimagebox.ru
 * Copyright (C) 2008 c0rr, p_ann 
 * 
 * Licensed under the terms of GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 */


// Глобальный обьект в нем хранятся настройки и закешированы ссылки на DOM узлы и переменные состояния
var jsiBox = {
	// НАСТРОЙКИ //
	boxBorderColor : '#727272',          // Цвет границы бокса
	boxBorderWidth : '1px',              // Толщина границы бокса
	boxBgColor     : '#484848',          // Цвет фона бокса
	imgBgColor     : '#fff',          // Цвет подложки изображения 
	overlayColor   : '#fff',          // Цвет затемнения страницы
	nextArrow      : '&rarr;',           // Следующее изображение
	prevArrow      : '&larr;',         // Предыдущее изображение
	closeSymbol    : '&times;',          // Значок закрытия бокса
	statusString   : 'Изображение&nbsp;[num]&nbsp;из&nbsp;[total]', // Строка описания текущего соcтояния  
	selfDir        : ''	                 // Путь к каталогу кода бокса, если пустая строка то скрипт попробует автоматически вычислить путь
};

// Ф-ция добавляет HTML код бокса к текущему документу и кеширует ссылки на составные элементы
function jsiBoxInit()
{
	if (jsiBox.selfDir == '') {
		// Попробовать вычислить путь к коду - нужно для задания путей к изображениям
		var scriptNodes = document.getElementsByTagName('script');
		for (var i = 0; i < scriptNodes.length; i++) {
			if (scriptNodes[i].src && scriptNodes[i].src.match('jsibox_basic.js')) {
				jsiBox.selfDir = scriptNodes[i].src.split('jsibox_basic.js')[0];
				break;
			}
		}
	}
	var boxHTML =  '<style type="text/css">#jsiMainBox * {margin: 0; padding: 0; border: 0; text-decoration: none;} #jsiMainBox a.jsiBtn {outline: none; float:right; color: #fff; font-size: 30px; width: 40px;  vertical-align:middle;font-weight:normal; } </style>'
                  +'<div id="wrapJsiBox" style="position: absolute; top:0; left:0; display: none; z-index: 1000; background-color:'+jsiBox.overlayColor+'; opacity: 0.6; filter: alpha(opacity=\'60\');"></div>'
                  +'<div style="position: absolute; top: 0; left: 0; width: 100%; z-index: 2000;">'
                  +'  <div id="jsiMainBox" style="color: #fff;text-align:left;position: relative; display: none; margin: auto; z-index: 2; width: 400px; background:'+jsiBox.boxBgColor+'; border: '+jsiBox.boxBorderWidth+' solid '+jsiBox.boxBorderColor+'; padding-bottom: 4px;">'
                  +'      <p style="text-align: right; font: bold 10px Tahoma; padding-top: 0;width:100%;overflow:hidden;padding-bottom:0;">'
                  +'        <img src="'+jsiBox.selfDir+'img/ajax-loader2.gif" alt="" id="jsiBoxLoading" style="float:left; display:inline; margin:7px 5px 0 8px;" />'
                  +'        <a href="#" style="padding:0 8px 0 0;width:28px;line-height:32px;margin-top:-2px" onclick="return jsiBoxClose();" class="jsiBtn">'+jsiBox.closeSymbol+'</a>'
                  +'        <span style="width: 75px; float: right; height: 37px;position:relative;">'
                  +'          <a href="#" id="nextJsiBoxLink" onclick="return jsiBoxNext();" style="position:absolute;top: 0;right: 0;line-height:30px;" class="jsiBtn">'+jsiBox.nextArrow+'</a>'
                  +'          <a href="#" id="prevJsiBoxLink" onclick="return jsiBoxPrev();" style="position:absolute;top: 0;left: 0;line-height:30px;" class="jsiBtn">'+jsiBox.prevArrow+'</a>'
                  +'        </span>'
                  +'      </p>'
                  +'        <p id="jsiBoxNumberOfImage" style="margin-top:-7px; padding:0 12px 2px 0;text-align:right;"></p>'
                  +'      <div id="jsiBoxMainImageWrap" style="background:'+jsiBox.imgBgColor+'; margin: 0 8px 4px 8px; overflow: hidden; position: relative;">'
                  +'        <img src="'+jsiBox.selfDir+'img/blank.gif" id="jsiBoxMainImage" alt="" style="display: block;" />'
                  +'      </div>'
                  +'      <span id="jsiBoxTitle" style="margin:0 8px; font: normal 11px Tahoma;"></span>'
                  +'  </div>'
                  +'</div>';
	jsiBox.wrapNode = document.getElementById('wrapJsiBox');
	if (!jsiBox.wrapNode) {
		document.write(boxHTML);
	}
	// Создание контейнера для предзагрузки изображений
	jsiBox.preloadImg        = new Image();
	jsiBox.preloadImg.onload = jsiBoxDisplayMainImg;
	// Кеширование (ссылок на) DOM узлов составных элементов бокса внутри объекта
	jsiBox.wrapNode      = document.getElementById('wrapJsiBox');
	jsiBox.boxNode       = document.getElementById('jsiMainBox');
	jsiBox.progressImg   = document.getElementById('jsiBoxLoading');
	jsiBox.prevLinkNode  = document.getElementById('prevJsiBoxLink');
	jsiBox.nextLinkNode  = document.getElementById('nextJsiBoxLink');
	jsiBox.infoNode      = document.getElementById('jsiBoxNumberOfImage');
	jsiBox.wrapImgNode   = document.getElementById('jsiBoxMainImageWrap');
	jsiBox.mainImg       = document.getElementById('jsiBoxMainImage');
	jsiBox.titleNode     = document.getElementById('jsiBoxTitle');
	
	jsiBox.currentImgIndex = 0;           // Порядковый номер отображаемого в текущий момент изображения "галлереи"
	jsiBox.linkNodesArray  = new Array(); // Массив DOM узлов ссылок на изображения текущей галлереи  
}

// Запуск анимации и инициализации навигации 
function jsiBoxDisplayMainImg()
{
	// инициализация навигации
	var previousImgIndex = jsiBox.currentImgIndex - 1;
	if (previousImgIndex >= 0) {
		jsiBox.prevLinkNode.style.display = '';
	} else { 
		jsiBox.prevLinkNode.style.display = 'none'; // Скрыть ссылку "=>"
	}
	var nextImgIndex = jsiBox.currentImgIndex + 1;
	if (nextImgIndex < jsiBox.linkNodesArray.length) {
		jsiBox.nextLinkNode.style.display = '';
	} else { 
		jsiBox.nextLinkNode.style.display = 'none'; // Скрыть ссылку "<="
	} 

	if (jsiBox.linkNodesArray.length > 1) {
		// Нарисовать порядковый номер в навигации
		var info = jsiBox.statusString.replace('[num]', jsiBox.currentImgIndex + 1);
		info     = info.replace('[total]', jsiBox.linkNodesArray.length);
		jsiBox.infoNode.innerHTML = info; 
	} 
	jsiBoxDimMainImage(10);                    // Запускаем анимацию "растворения"
	jsiBox.progressImg.style.display = 'none'; // Убираем индикатор загрузки
	jsiBox.titleNode.innerHTML       = '';
	jsiBox.titleNode.style.display   = 'none'; // Убираем тайтл изображения
}

// Ф-ция анимации растворения - увеличивает прозрачность изображения от заданного в opacity значения до нуля (диапазон opacity [0-10]) 
function jsiBoxDimMainImage(opacity)
{
	var newOpacity;
	if (opacity) {
		newOpacity = opacity; // первый вызов ф-ции, задаем свойство 
	} else {
		var step   = 2;     // Шаг изменения 
		newOpacity = jsiBox.mainImg.style.opacity*10 - step; // Изменяем значение
	}
	jsiBox.mainImg.style.opacity = newOpacity/10;                          // для всех нормальных броузеров
	jsiBox.mainImg.style.filter  = 'alpha(opacity=' + newOpacity*10 + ')'; // для IE
	if (jsiBox.mainImg.style.opacity > 0) { 
		setTimeout('jsiBoxDimMainImage()', 35); // продолжим анимацию
	} else {
		jsiBox.mainImg.style.display = 'none';
		jsiBox.mainImg.style.opacity = 0;
		jsiBox.mainImg.style.filter  = 'alpha(opacity=100)';
		jsiBoxResize(); // Запуск анимации ресайза бокса
	}
}

// Ф-ция анимирует изменение размеров блока при разнице размеров загружаемых изображений
function jsiBoxResize() 
{
	var leftInnerMargin   = parseInt(jsiBox.wrapImgNode.style.marginLeft, 10) || 0; 
	var rightInnerMargin  = parseInt(jsiBox.wrapImgNode.style.marginRight, 10) || 0;
	var leftBorder        = parseInt(jsiBox.boxNode.style.borderLeftWidth, 10) || 0;
	var rightBorder       = parseInt(jsiBox.boxNode.style.borderRightWidth, 10) || 0;
	// Изменение ширины относительно внутреннего блока, однако ширину менять будем внешенему 
	var deltaWidth  = jsiBox.wrapImgNode.offsetWidth - jsiBox.preloadImg.width;
	// Вычисляется изменение высоты только для блока вокруг изображения, внешние блоки отресайзятся сами при изменении высоты внутреннего
	var deltaHeight = jsiBox.wrapImgNode.offsetHeight - jsiBox.preloadImg.height; 
	
	// Шаг изменения поставим в зависимость от расстояния, чтобы "сначала быстро, потом медленно"
	var widthResizeStep  = deltaWidth / 4;
	var heightResizeStep = deltaHeight / 4;
	widthResizeStep      = (widthResizeStep > 0) ? Math.ceil(widthResizeStep) : Math.floor(widthResizeStep); 
	heightResizeStep     = (heightResizeStep > 0) ? Math.ceil(heightResizeStep) : Math.floor(heightResizeStep);
	
	if (Math.abs(deltaWidth) > Math.abs(widthResizeStep)) {
		var newWidth              = jsiBox.boxNode.offsetWidth - leftBorder - rightBorder - widthResizeStep;
		jsiBox.boxNode.style.width = newWidth + 'px'; // Изменение ширины внешнего блока, внутренние блоки отресайзятся сами
	}		
	if (Math.abs(deltaHeight) > Math.abs(heightResizeStep)) {
		var newHeight                  = jsiBox.wrapImgNode.offsetHeight - heightResizeStep;
		jsiBox.wrapImgNode.style.height = newHeight + 'px'; // Изменение высоты внутреннего блока
	}
	
	if ((Math.abs(deltaHeight) > Math.abs(heightResizeStep)) || (Math.abs(deltaWidth) > Math.abs(widthResizeStep))) {
		setTimeout('jsiBoxResize()', 35); // Анимируем дальше
	} else {
		// Стопорим и "добиваем" нужные значения, т.к. в процессе анимации они могли быть вычислены не точно
		jsiBox.boxNode.style.width      = jsiBox.preloadImg.width + leftInnerMargin + rightInnerMargin + 'px';
		jsiBox.mainImg.style.width      = jsiBox.preloadImg.width + 'px';
		jsiBox.wrapImgNode.style.height = jsiBox.preloadImg.height + 'px';
		jsiBox.mainImg.src              = jsiBox.preloadImg.src;
		jsiBox.mainImg.style.display    = 'block';
		// Нарисуем тайтл изображения
		var imageTitle = (jsiBox.linkNodesArray[jsiBox.currentImgIndex]) ? jsiBox.linkNodesArray[jsiBox.currentImgIndex].title : '';
		if (imageTitle != '') {
			jsiBox.titleNode.style.display = 'block';
			jsiBox.titleNode.innerHTML     = imageTitle;
		}
		jsiBoxLightenMainImage(); // Запускаем анимацию "проявления" изображения
		//jsiBoxAnimSglOverlay('animation3.gif');
		//jsiBoxAnimMultiOverlay('animation3.gif');
	}  
}

// Уменьшает прозрачность изображения от текщего значения до полностью непрозрачного - эффект проявления
function jsiBoxLightenMainImage()
{
	var step        = 2; 
	var newOpacity  = jsiBox.mainImg.style.opacity*10 + step;
	
	jsiBox.mainImg.style.opacity = newOpacity/10;
	jsiBox.mainImg.style.filter  = 'alpha(opacity=' + newOpacity*10 + ')';
	
	if (jsiBox.mainImg.style.opacity < 1) {
		setTimeout('jsiBoxLightenMainImage()', 35);
	} else {
		jsiBox.mainImg.style.opacity = '';
		jsiBox.mainImg.style.filter  = '';
	}
}

// Показ предыдущего изображения "галлереи"
function jsiBoxNext() 
{
	jsiBox.progressImg.style.display = 'block'; // Показать индикатор загрузки
	
	var nextImgIndex = jsiBox.currentImgIndex + 1;
	if (nextImgIndex < jsiBox.linkNodesArray.length) {
		jsiBox.currentImgIndex = nextImgIndex;
		jsiBox.preloadImg.src  = jsiBox.linkNodesArray[nextImgIndex].href;
	}
	return false;
}

// Показ следующего изображения "галлереи"
function jsiBoxPrev() 
{
	jsiBox.progressImg.style.display = 'block'; // Показать индикатор загрузки
	
	var prevImgIndex = jsiBox.currentImgIndex - 1;
	if (prevImgIndex >= 0) {
		jsiBox.currentImgIndex = prevImgIndex;
		jsiBox.preloadImg.src  = jsiBox.linkNodesArray[prevImgIndex].href;
	}
	return false;
}

// Ф-ция закрытия бокса
function jsiBoxClose()
{
	jsiBox.wrapNode.style.display   = 'none';
	jsiBox.boxNode.style.display    = 'none';
	return false;
}

// Отправляет изображение на просмотр в боксе
function jsiBoxOpen(domNode)
{
	var docLinks = document.getElementsByTagName('a');
	jsiBox.progressImg.style.display = 'block'; // Показать индикатор загрузки
	jsiBox.linkNodesArray            = new Array(); 
	// Пройдемся по всему списку ссылок для того чтобы найти элементы с заданным rel инаполнить "галлерею"
	if (domNode.rel != '') {
		for (var i = 0; i < docLinks.length; i++){
			if (docLinks[i].rel == domNode.rel) {
				jsiBox.linkNodesArray.push(docLinks[i]); // Добавим найденный элемент в массив {TODO} IE 5 do not have push
			}
			if (docLinks[i] == domNode) {
				jsiBox.currentImgIndex = jsiBox.linkNodesArray.length - 1;
			}
		}
	} else {
		jsiBox.linkNodesArray.push(domNode);
		jsiBox.currentImgIndex = 0;
	}
	jsiBox.infoNode.innerHTML  = '&#160;';
	jsiBox.titleNode.innerHTML = '';
	// Сделать общий темный фон
	var pagesize                 = getPageSizeWithScroll();
	jsiBox.wrapNode.style.display = 'block';
	jsiBox.wrapNode.style.height  = pagesize[1] + 'px';
	jsiBox.wrapNode.style.width   = pagesize[0] + 'px';
	// отобразить бокс с учетом прокрутки
	var top = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
	jsiBox.boxNode.style.top         = (top + 200) + 'px';
	jsiBox.mainImg.src               = jsiBox.selfDir+'img/blank.gif';
	jsiBox.wrapImgNode.style.height  = '30px';
	jsiBox.boxNode.style.width       = '200px';
	jsiBox.boxNode.style.display     = 'block';
	jsiBox.preloadImg.src            = domNode.href; // Добавим изображение в очередь загрузки
	//alert('thatsit');
	return false;
}

// Вспомогательная ф-ция получения размера документа
function getPageSizeWithScroll()
{
	if( window.innerHeight && window.scrollMaxY ) {  // Firefox 
		pageWidth = document.body.clientWidth + window.scrollMaxX;
		pageHeight = window.innerHeight + window.scrollMaxY;
	} else if( document.body.scrollHeight > document.body.offsetHeight ) { // all but Explorer Mac
		pageWidth = document.body.scrollWidth;
		pageHeight = document.body.scrollHeight;
	} else { // works in Explorer 6 Strict, Mozilla (not FF) and Safari
		pageWidth = document.body.offsetWidth + document.body.offsetLeft;
		pageHeight = document.body.offsetHeight + document.body.offsetTop;
	}
	arrayPageSizeWithScroll = new Array(pageWidth, pageHeight);
	return arrayPageSizeWithScroll;
}

// Инициализируем бокс
jsiBoxInit();
