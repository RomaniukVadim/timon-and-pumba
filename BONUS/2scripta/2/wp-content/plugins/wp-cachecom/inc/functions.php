<?php
	class WPCache{
		private $Menu_Title = "WP Cache";
		private $Page_Title = "WP Cache Settings";
		private $slug = "wp_cache";
		private $Admin_Page_Url = "wpcache/admin/index.php";
		private $WP_con_DIR = "";
		private $System_Message = "";
		private $Options = array();
		private $Cronjob_Settings;
		private $Start_Time;
		private $Block_Cache = false;

		public function __construct(){
			add_action('init', array($this, 'add_localization') );
			$this->Set_WP_Con_DIR();
			$this->Set_WP_Blog_Path();
			$this->Set_WP_Domain();
			$this->Set_Options();
			$this->Detect_New_Post();
			$this->Check_Cron_Time();
			if(is_admin()){
				$this->OptionsPanelRequest();
				$this->Set_Cronjob_Settings();
				$this->Add_Editor_Button();
			}
		}

		public function add_localization() {
			load_plugin_textdomain('wpcache', false, dirname(plugin_basename(__FILE__)).'/lang/' );
		}

		public function Add_Editor_Button(){
			add_action('admin_print_footer_scripts', array($this, 'Add_Quicktags_Editor_Button'));
			add_action('init', array($this, 'wpcache_buttonhooks'));
		}

		public function checkShortCode($content){
			preg_match("/\[NoCache\]/", $content, $NoCache);
			if(count($NoCache) > 0){
				if(is_single() || is_page()){
					$this->Block_Cache = true;
				}
				$content = str_replace("[NoCache]", "", $content);
			}
			return $content;
		}

		public function wpcache_buttonhooks() {
		   // Only add hooks when the current user has permissions AND is in Rich Text editor mode
		   if ( ( current_user_can('edit_posts') || current_user_can('edit_pages') ) && get_user_option('rich_editing') ) {
		     add_filter("mce_external_plugins", array($this, "wpcache_register_tinymce_javascript"));
		     add_filter('mce_buttons', array($this, 'wpcache_register_buttons'));
		   }
		}
		// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
		public function wpcache_register_tinymce_javascript($plugin_array) {
		   $plugin_array['wpcache'] = plugins_url('../js/button.js?v='.time(),__file__);
		   return $plugin_array;
		}

		public function wpcache_register_buttons($buttons) {
		   array_push($buttons, 'wpcache');
		   return $buttons;
		}

		public function Add_Quicktags_Editor_Button(){
			if (wp_script_is('quicktags')){ ?>
				<script type="text/javascript">
				    QTags.addButton('wpcache_not', 'NoCache', '[NoCache]', '', '', 'Block caching for this page');
			    </script>
		    <?php }
		}

		public function deactivate(){
		if(is_file(ABSPATH.".htaccess") && is_writable(ABSPATH.".htaccess")){
		    $htaccess = file_get_contents(ABSPATH.".htaccess");
		    $htaccess = preg_replace("/#\s?BEGIN\s?WPCache.*?#\s?END\s?WPCache/s", "", $htaccess);
		    $htaccess = preg_replace("/#\s?BEGIN\s?GzipWPCache.*?#\s?END\s?GzipWPCache/s", "", $htaccess);
		    file_put_contents(ABSPATH.".htaccess", $htaccess);
			}

			wp_clear_scheduled_hook("wp_cache");
			delete_option("WPCache");
			$wpcache = new WPCache();
			$wpcache->deleteCache();
		}

		public function OptionsPanelRequest(){
			if(!empty($_POST)){
				if(isset($_POST["WPCache_Page"])){
					if($_POST["WPCache_Page"] == "Options"){
						$this->saveOption();
					}else if($_POST["WPCache_Page"] == "deleteCache"){
						$this->deleteCache();
					}else if($_POST["WPCache_Page"] == "cacheTimeout"){
						$this->addCacheTimeout();	
					}
				}
			}
		}

		public function Set_WP_Con_DIR(){
			$this->WP_con_DIR = ABSPATH."wp-content";
		}

		/**
		 * Set Cache Path
		 *
		 * This function provides compatibility for
		 * WordPress Networks.
		 *
		 * Author: José SAYAGO
		 * URI: http://laelite.info
		 * Email: opensource@laelite.info
		 */
		public function Set_WP_Blog_Path(){
			// WordPress Network?
			if( is_multisite() == true ) {
				// Get Global Blog Info
				global $current_blog;
				// Subdomains?
				if( is_subdomain_install() == true ) {
					// Set subdomain as folder name
					$blog_path = $current_blog->domain;
				} else {
					// Set path as folder name
					$blog_path = $current_blog->path;
				}
				// If path is root
				if( $blog_path == '/') {
					// Set root as folder name
					$blog_path = 'root';
				}
			} else {
				// Use all for single installations
				$blog_path = 'all';
			}
			$this->WP_blog_Path = $blog_path;
		}
		/**
		 * Set WordPress Domain
		 *
		 * Detect site domain to correctly serve
		 * cached files. Required for WordPress Network
		 * compatibility.
		 *
		 * Author: José SAYAGO
		 * URI: http://laelite.info
		 * Email: opensource@laelite.info
		 */
		public function Set_WP_Domain(){
			// WordPress Network
			if( is_multisite() == true ) {
				global $current_blog;
				if( is_subdomain_install() == true ) {
					// Get the domain
					$blog_url = $current_blog->domain;
				} else {
					// Get the base domain + path
					$blog_url = $current_blog->domain.$current_blog->path;
				}
			} else {
				// WordPress base domain without www
				$blog_url =  preg_replace('/^www\./','',$_SERVER['SERVER_NAME']);
			}
			$this->WP_blog_Url = $blog_url;
		}

		public function add_Options_panel(){
			add_action('admin_menu', array($this, 'register_WPCache_menu'));
		}

		public function register_WPCache_menu(){
			if(function_exists('add_menu_page')){ 
                            add_menu_page( __( 'WP-Cache.com', 'wpcache' ), __( 'WP-Cache.com', 'wpcache' ), 'manage_options', __FILE__, array($this, 'OptionsPage'));
			}
		}

		public function OptionsPage(){
			$WPCache_Status = "";
			$WPCache_NewPost = "";
			$WPCache_TimeOut = "";
			$WPCache_Status = isset($this->Options->WPCache_Status) ? 'checked="checked"' : "";
			$WPCache_NewPost = isset($this->Options->WPCache_NewPost) ? 'checked="checked"' : "";
			$WPCache_TimeOut = isset($this->Cronjob_Settings["period"]) ? $this->Cronjob_Settings["period"] : "";
?>

<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div><h2><?php _e( 'WP-Cache.com Options', 'wpcache'); ?></h2>
				
	<?php if($this->System_Message){ ?>
		<div class="updated <?php echo $this->System_Message[1]; ?>" id="message"><p><?php echo $this->System_Message[0]; ?></p></div>
	<?php } ?>
				
<form method="post" name="wp_manager"><!-- General Options : begin -->
<input type="hidden" value="Options" name="WPCache_Page">

<div id="poststuff">
<div class="postbox">
<table class="form-table">
<tbody>

<h3><?php _e( 'General Options', 'wpcache' ); ?></h3>

<tr valign="top">
<th scope="row"><label for="home">&nbsp;&nbsp;&nbsp;<b><?php _e( 'Cache Frontend', 'wpcache' ); ?></b></label></th>
<td>
<label for="WPCache_Status">
<div class="switch toggle3">
<input type="checkbox" <?php echo $WPCache_Status; ?> id="WPCache_Status" name="WPCache_Status">
<label><i></i></label>
</div>
<i><?php _e( 'Turn "On" to enable', 'wpcache' ); ?></i><br>
<?php _e( 'This will cache all frontend posts, pages including your homepage. Cached files are only served to non logged in users.', 'wpcache' ); ?>
</label>
</td>
</tr>

<tr valign="top">
<th scope="row"><label for="home">&nbsp;&nbsp;&nbsp;<b><?php _e( 'New Post or Page', 'wpcache' ); ?></b></label></th>
<td>
<label for="WPCache_NewPost">
<div class="switch toggle3">
<input type="checkbox" <?php echo $WPCache_NewPost; ?> id="WPCache_NewPost" name="WPCache_NewPost">
<label><i></i></label>
</div>
<i><?php _e( 'Turn "On" to enable', 'wpcache' ); ?></i><br>
<?php _e( 'Clear all cache files when a post or page is published.', 'wpcache' ); ?>
</label>
</td>
</tr>
	
</tbody>
</table>	
</div>
</div>	

<p style="border-bottom: 1px dashed #CCCCCC;padding-bottom: 20px">
<input type="submit" value="Save changes" class="button-primary">
</p>

</form><!-- General Options : end -->


<form method="post" name="wp_manager"><!-- Clear Cache : begin -->
<input type="hidden" value="deleteCache" name="WPCache_Page">

<div id="poststuff">
<div class="postbox">
<table class="form-table">
<tbody>

<h3><?php _e( 'Delete Cache', 'wpcache' ); ?></h3>

<tr valign="top">
<th scope="row"><label for="home">&nbsp;&nbsp;&nbsp;<b><?php _e( 'Clear all cache', 'wpcache' ); ?></b></label></th>
<td>
<label for="WPCache_Delete_All_Cache">
<i><?php _e( 'Target folder:', 'wpcache' ); ?></i><br>

<pre style="margin-top:10px;background:#FFFFFF;padding:10px;border: 1px dashed #CCCCCC;">
<b><?php echo $this->WP_con_DIR.'/cache/'.$this->WP_blog_Path; ?></b>
</pre>

</label>
</td>
</tr>
	
</tbody>
</table>	
</div>
</div>	

<p style="padding-bottom: 20px">
<input type="submit" value="Delete Now" class="button-primary">
</p>

</form><!-- Clear Cache : end -->
				
</div>

<style>
th, td {
border-left: 1px solid #e1e1e1;
border-right: 1px solid #e1e1e1;
border-top: 1px solid #e1e1e1;
}
.form-table{margin-top: 0px;}
</style>

<?php }

		public function Check_Cron_Time(){
			add_action($this->slug,  array($this, 'setSchedule'));
			add_action($this->slug."TmpDelete",  array($this, 'actionDelete'));
		}

		public function Detect_New_Post(){
			if(isset($this->Options->WPCache_NewPost) && isset($this->Options->WPCache_Status)){
				add_filter( 'publish_post',          array( $this, 'deleteCache' ) );
				add_filter( 'delete_post',           array( $this, 'deleteCache' ) );
				add_filter( 'publish_page',          array( $this, 'deleteCache' ) );
				add_filter( 'delete_page',           array( $this, 'deleteCache' ) );
				add_filter( 'switch_theme',          array( $this, 'deleteCache' ) );
				add_filter( 'wp_create_nav_menu',    array( $this, 'deleteCache' ) );
				add_filter( 'wp_update_nav_menu',    array( $this, 'deleteCache' ) );
				add_filter( 'wp_delete_nav_menu',    array( $this, 'deleteCache' ) );
				add_filter( 'save_post',             array( $this, 'deleteCache' ) );
				add_filter( 'trackback_post',        array( $this, 'deleteCache' ) );
				add_filter( 'pingback_post',         array( $this, 'deleteCache' ) );
				add_filter( 'comment_post',          array( $this, 'deleteCache' ) );
				add_filter( 'edit_comment',          array( $this, 'deleteCache' ) );
				add_filter( 'delete_comment',        array( $this, 'deleteCache' ) );
				add_filter( 'wp_set_comment_status', array( $this, 'deleteCache' ) );
				add_filter( 'create_term',           array( $this, 'deleteCache' ) );
				add_filter( 'edit_terms',            array( $this, 'deleteCache' ) );
				add_filter( 'delete_term',           array( $this, 'deleteCache' ) );
				add_filter( 'add_link',              array( $this, 'deleteCache' ) );
				add_filter( 'edit_link',             array( $this, 'deleteCache' ) );
				add_filter( 'delete_link',           array( $this, 'deleteCache' ) );
			}
		}

		public function deleteCache(){
			if(is_dir($this->WP_con_DIR."/cache/".$this->WP_blog_Path)){
				//$this->rm_folder_recursively($this->WP_con_DIR."/cache/".$this->WP_blog_Path);
				if(is_dir($this->WP_con_DIR."/cache/tmpWPCache")){
					rename($this->WP_con_DIR."/cache/".$this->WP_blog_Path, $this->WP_con_DIR."/cache/tmpWPCache/".time());
					wp_schedule_single_event(time() + 60, $this->slug."TmpDelete");
					$this->System_Message = array("All cache files have been deleted","success");
				}else if(@mkdir($this->WP_con_DIR."/cache/tmpWPCache", 0755, true)){
					rename($this->WP_con_DIR."/cache/".$this->WP_blog_Path, $this->WP_con_DIR."/cache/tmpWPCache/".time());
					wp_schedule_single_event(time() + 60, $this->slug."TmpDelete");
					$this->System_Message = array( __( 'All cache files have been deleted', 'wpcache' ),"success");
				}else{
					$this->System_Message = array( __( 'Permission of <strong>/wp-content/cache</strong> must be <strong>755</strong>', 'wpcache' ), "error");
				}
			}else{
				$this->System_Message = array( __( 'Cache deleted', 'wpcache' ),"success");
			}
		}

		public function actionDelete(){
			if(is_dir($this->WP_con_DIR."/cache/tmpWPCache")){
				$this->rm_folder_recursively($this->WP_con_DIR."/cache/tmpWPCache");
				if(is_dir($this->WP_con_DIR."/cache/tmpWPCache")){
					wp_schedule_single_event(time() + 60, $this->slug."TmpDelete");
				}
			}
		}
		
		public function addCacheTimeout(){
			if(isset($_POST["WPCache_TimeOut"])){
				if($_POST["WPCache_TimeOut"]){
					wp_clear_scheduled_hook($this->slug);
					wp_schedule_event(time() + 120, $_POST["WPCache_TimeOut"], $this->slug);
				}else{
					wp_clear_scheduled_hook($this->slug);
				}
			}
		}

		public function setSchedule(){
			$this->deleteCache();
		}

		public function Set_Cronjob_Settings(){
			if(wp_next_scheduled($this->slug)){
				$this->Cronjob_Settings["period"] = wp_get_schedule($this->slug);
				$this->Cronjob_Settings["time"] = wp_next_scheduled($this->slug);
			}
		}		

		public function rm_folder_recursively($dir, $i = 1) {
		    foreach(scandir($dir) as $file) {
		    	if($i > 500){
		    		return true;
		    	}else{
		    		$i++;
		    	}
		        if ('.' === $file || '..' === $file) continue;
		        if (is_dir("$dir/$file")) $this->rm_folder_recursively("$dir/$file", $i);
		        else unlink("$dir/$file");
		    }
		    
		    rmdir($dir);
		    return true;
		}

		public function saveOption(){
			unset($_POST["WPCache_Page"]);
			$data = json_encode($_POST);
			//for OptionsPage() $_POST is array and json_decode() converts to stdObj
			$this->Options = json_decode($data);

			if(get_option("WPCache")){
				update_option("WPCache", $data);
			}else{
				add_option("WPCache", $data, null, "yes");
			}
			$this->System_Message = $this->modifyHtaccess($_POST);
		}

		public function Set_Options(){
			if($data = get_option("WPCache")){
				$this->Options = json_decode($data);
			}
		}

		public function modifyHtaccess($post){
			if(isset($post["WPCache_Status"]) && $post["WPCache_Status"] == "on"){
				if(!is_file(ABSPATH.".htaccess")){
					return array(".htacces was not found", "error");
				}else if(is_writable(ABSPATH.".htaccess")){
					$htaccess = file_get_contents(ABSPATH.".htaccess");
					$htaccess = $this->insertRewriteRule($htaccess);
					$this->insertGzipRule($htaccess, $post);
				}else{
					return array( __( ".htacces is not writable", 'wpcache' ), "error");
				}
				return array( __( "Options saved", 'wpcache' ), "success");
			}else{
				//disable
				$this->deleteCache();
				return array( __( "Options saved", 'wpcache' ), "success");
			}
		}

		public function insertGzipRule($htaccess, $post){
			if(isset($post["WPCache_Gzip"]) && $post["WPCache_Gzip"] == "on"){

		    	$data = "# BEGIN GzipWPCache"."\n".
		          		"<IfModule mod_deflate.c>"."\n".
		  				"AddOutputFilterByType DEFLATE text/plain"."\n".
		  				"AddOutputFilterByType DEFLATE text/html"."\n".
		  				"AddOutputFilterByType DEFLATE text/xml"."\n".
		  				"AddOutputFilterByType DEFLATE text/css"."\n".
		  				"AddOutputFilterByType DEFLATE application/xml"."\n".
		  				"AddOutputFilterByType DEFLATE application/xhtml+xml"."\n".
		  				"AddOutputFilterByType DEFLATE application/rss+xml"."\n".
		  				"AddOutputFilterByType DEFLATE application/javascript"."\n".
		  				"AddOutputFilterByType DEFLATE application/x-javascript"."\n".
		  				"</IfModule>"."\n".
						"# END GzipWPCache"."\n\n";

				preg_match("/BEGIN GzipWPCache/", $htaccess, $check);
				if(count($check) === 0){
					file_put_contents(ABSPATH.".htaccess", $data.$htaccess);
				}else{
					//already changed
				}	

			}else{

				//delete gzip rules
				$htaccess = preg_replace("/#\s?BEGIN\s?GzipWPCache.*?#\s?END\s?GzipWPCache/s", "", $htaccess);

				//echo $htaccess;
				file_put_contents(ABSPATH.".htaccess", $htaccess);
			}
		}
		
		public function insertRewriteRule($htaccess){
			preg_match("/wp-content\/cache\/".$this->WP_blog_Path."/", $htaccess, $check);
			if(count($check) === 0){
				$htaccess = $this->getHtaccess().$htaccess;
			}else{
				//already changed
			}
			return $htaccess;
		}

		public function getHtaccess(){
			$data = "# BEGIN WPCache"."\n".
					"<IfModule mod_rewrite.c>"."\n".
					"RewriteEngine On"."\n".
					"RewriteBase /"."\n".
					"RewriteCond %{HTTP_HOST} ^(www\.)?".$this->WP_blog_Url." [NC]"."\n".
					"RewriteCond %{REQUEST_METHOD} !POST"."\n".
					"RewriteCond %{QUERY_STRING} !.*=.*"."\n".
					"RewriteCond %{HTTP:Cookie} !^.*(comment_author_|wordpress_logged_in|wp-postpass_).*$"."\n".
					'RewriteCond %{HTTP:X-Wap-Profile} !^[a-z0-9\"]+ [NC]'."\n".
					'RewriteCond %{HTTP:Profile} !^[a-z0-9\"]+ [NC]'."\n".
					"RewriteCond %{DOCUMENT_ROOT}/".$this->getRewriteBase()."wp-content/cache/".$this->WP_blog_Path."/".$this->getRewriteBase()."$1/index.html -f"."\n".
					'RewriteRule ^(.*) "/'.$this->getRewriteBase().'wp-content/cache/'.$this->WP_blog_Path.'/'.$this->getRewriteBase().'$1/index.html" [L]'."\n".
					"</IfModule>"."\n".
					"# END WPCache"."\n";
			return $data;
		}

		public function getRewriteBase(){
			$tmp = str_replace($_SERVER['DOCUMENT_ROOT']."/", "", ABSPATH);
			$tmp = str_replace("/", "", $tmp);
			$tmp = $tmp ? $tmp."/" : "";
			return $tmp;
		}

		public function startCache(){
			if(isset($this->Options->WPCache_Status)){
				$this->Start_Time = microtime(true);
				ob_start(array($this, "callback"));
			}
		}

		public function ignored(){
			$ignored = array("robots.txt", "wp-login.php", "wp-cron.php", "wp-content", "wp-admin", "wp-includes");
			foreach ($ignored as $key => $value) {
				if (strpos($_SERVER["REQUEST_URI"], $value) === false) {
				}else{
					return true;
				}
			}
			return false;
		}

		public function callback($buffer){
			$buffer = $this->checkShortCode($buffer);
			if(defined('DONOTCACHEPAGE')){ // for Wordfence: not to cache 503 pages
				return $buffer;
			}else if(is_404()){
				return $buffer;
			}else if($this->ignored()){
				return $buffer;
			}else if($this->Block_Cache === true){
				return $buffer."<!-- not cached -->";
			}else if(isset($_GET["preview"])){
				return $buffer."<!-- not cached -->";
			}else if($this->checkHtml($buffer)){
				return $buffer;
			}else{
				$cachFilePath = $this->WP_con_DIR."/cache/".$this->WP_blog_Path.$_SERVER["REQUEST_URI"];
				$content = $this->cacheDate($buffer);
				$this->createFolder($cachFilePath, $content);

				return $buffer;
			}
		}

		public function checkHtml($buffer){
			preg_match('/<\/html>/', $buffer, $htmlTag);
			preg_match('/<\/body>/', $buffer, $bodyTag);
			if(count($htmlTag) > 0 && count($bodyTag) > 0){
				return 0;
			}else{
				return 1;
			}
		}

		public function cacheDate($buffer){
			return $buffer."<!-- WP-Cache.com generated this file in ".$this->creationTime()." seconds, on ".date("m-d-y G:i:s")." -->";
		}

		public function creationTime(){
			return microtime(true) - $this->Start_Time;
		}

		public function isCommenter(){
			$commenter = wp_get_current_commenter();
			return isset($commenter["comment_author_email"]) && $commenter["comment_author_email"] ? false : true;
		}

		public function createFolder($cachFilePath, $buffer, $extension = "html"){
			if($buffer && strlen($buffer) > 100){
				if (!is_user_logged_in() && $this->isCommenter()){
					if(!is_dir($cachFilePath)){
						if(is_writable($this->WP_con_DIR) || ((is_dir($this->WP_con_DIR."/cache")) && (is_writable($this->WP_con_DIR."/cache")))){
							if (!mkdir($cachFilePath, 0755, true)){

							}else{
								file_put_contents($cachFilePath."/index.".$extension, $buffer);
							}
						}else{

						}
					}else{
						if(file_exists($cachFilePath."/index.".$extension)){

						}else{
							file_put_contents($cachFilePath."/index.".$extension, $buffer);
						}
					}
				}
			}
		}
	}
 
