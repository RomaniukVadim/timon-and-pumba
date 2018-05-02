<?php
/*
Plugin Name: CSS Compress 
Plugin URI: http://dev.wp-plugins.org/wiki/css-compress
Description: Automatically compress your CSS files: GZIP and remove comments. <strong>"Comment hacks" will be removed as they are contained in comment tags..</strong>
Author: Jeff Minard
Author URI: http://thecodepro.com
Version: .3b
*/

function css_c_cb($m) {
	global $path_to_css_dir;
	
	if( strtolower(substr($m[2],0,4)) != 'http' && substr($m[2],0,1) != '/') {
		$ret = $m[1] . $path_to_css_dir . $m[2] . $m[3] . $m[4];
	} else {
		$ret = $m[0];
	}
	return $ret;
}

function css_c_clean($buffer) {
	// change relative URI's into absolute URI's
	// this is needed since the "relative"ness of the file changes.
	$regex = "/
				(url\([\s\'\"]*)
				([^\s\'\"]*)
				([\s\'\"]*)
				(.*)
			 /ix";
			 
	$buffer = preg_replace_callback($regex, "css_c_cb", $buffer);
	
	// remove comments
	// this just so happens to remove comment hacks as well. Sorry, can't be picky.
	$pattern = '!/\*[^*]*\*+([^/][^*]*\*+)*/!';
	$buffer = preg_replace($pattern, '', $buffer); 
	
	// remove new lines, tabs, and 2x/3x/4x spaces - who needs em!
	$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer); 
	
	return $buffer;
}

if( 
	 stristr($_SERVER['REQUEST_URI'], 'style.css') // it's been requested
 && !stristr($_SERVER['REQUEST_URI'], 'theme-editor.php') // it's not from the theme page. Eeep!
  ) {
	$path_to_css = '../..' . substr(strstr($_SERVER['REQUEST_URI'], "css-compress.php"), 16);
	$path_to_css_dir = strstr(substr($path_to_css, 0, -9), '/wp-content');
	
	header('Content-type: text/css');
	ob_start("ob_gzhandler");
	ob_start("css_c_clean");
	
	if(@file_exists($path_to_css)) { 
		readfile($path_to_css);
	} else {
		echo("Oh crap, the plugin messed up. Send me a message. $path_to_css");
	}
	
	ob_end_flush();
	ob_end_flush();
	exit(); 
}

function css_c_replace($a) {
	$style_path = substr($a, strlen(get_settings('siteurl')));
	$a = get_settings('siteurl') . '/wp-content/plugins/css-compress.php' . $style_path;
	return $a;
}

add_filter('stylesheet_uri', 'css_c_replace');
?>