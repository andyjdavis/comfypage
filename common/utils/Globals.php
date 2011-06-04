<?php
//a spot for including commonly used files
require_once('common/utils/Html.php');
require_once('common/utils/Login.php');
require_once('common/utils/Load.php');
require_once('common/settings/GeneralSettings.php');
require_once('common/settings/CounterSettings.php');
require_once('common/class/addon.class.php');

if(Login::logged_in(false))
{
    require_once('common/menu.php');
}
//this used to be product_id but was changed to content_id
//for the sake of doodads in borders that post back to themself
//the content id is included in any form doodad that posts
//back to itself. The form input is named content_id so
//products are now identified with that as well
define('PRODUCT_ID_URL_PARAM', 'content_id');
define('COMING_FROM', 'comingFrom');
define('COMING_FROM_POST', 'comingFromPost');
define('HEADER', 'HEADER');
define('LEFT_MARGIN', 'LEFT_MARGIN');
define('RIGHT_MARGIN', 'RIGHT_MARGIN');
define('FOOTER', 'FOOTER');
define('INDEX', 'INDEX');
define('ERROR_CONTENT', 'ERROR');
define('CONTENT_ID_URL_PARAM', 'content_id');
define('FUNCTION_GET_PARAM', 'function');
class Globals
{
	public static function set_coming_from($page = null)
	{
		if(empty($page))
		{
			$page = $_SERVER['PHP_SELF'];
		}
		$coming_from = "http://" . $_SERVER['HTTP_HOST'] . $page;
		if(array_key_exists('QUERY_STRING', $_SERVER))
		{
			$coming_from .= '?' . $_SERVER['QUERY_STRING'];
		}
		$_SESSION[COMING_FROM] = $coming_from;
		$_SESSION[COMING_FROM_POST] = $_POST;
	}
	public static function redirect($url)
	{
		session_write_close();
		header("Location: $url");
		exit();
	}
	public static function dont_cache()
	{
		// disable any caching by the browser
		header('Expires: Mon, 14 Oct 2002 05:00:00 GMT');              // Date in the past
		header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT'); // always modified
		header('Cache-Control: no-store, no-cache, must-revalidate');  // HTTP 1.1
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');                                    // HTTP 1.0
	}
	function get_dirs_in_a_dir($dir)
	{
		$files = array();
		$dirlist = opendir($dir);
		while( ($file = readdir($dirlist)) !== false)
		{
			//don't include subversion dirs or php files
			$phpPosition = strpos($file, '.php');
			if($file != '.' && $file != '..' && $file != '.svn' && ($phpPosition===false || $phpPosition==0) )
			{
				$files[] = $file;
			}
		}
		natcasesort($files);
		closedir($dirlist);
		return $files;
	}
	public static function send_email($to, $from, $subject, $body, $html = false)
	{
		$header = '';
		if (!defined('PHP_EOL'))
		{
			define ('PHP_EOL', strtoupper(substr(PHP_OS,0,3) == 'WIN') ? "\r\n" : "\n");
		}
		if($html)
		{
			$header .= 'MIME-Version: 1.0' . "\r\n";
			$header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		}
		$body = Globals::linewrap($body, 60);
		$header .= "From: $from".PHP_EOL;
		$header .= "X-Mailer: php".PHP_EOL;
		$header .= "Reply-To: $from".PHP_EOL;
		$header .= "Return-Path: $from".PHP_EOL;
		return mail($to, $subject, $body, $header);
	}
	private static function linewrap($string, $width)
	{
		$array = explode("\n", $string);
		$string = "";
		foreach($array as $key => $val)
		{
			$string .= wordwrap($val, $width);
			$string .= "\n";
		}
		return $string;
	}
	//can optionally specify an address to send to
	//will default to this comfypage's admin email
	public static function send_email_to_admin($subject, $message, $send_to = null, $site_id = null)
	{
	    $gs = Load::general_settings();
		if(empty($send_to))
		{
			//$send_to = get_admin_email();
			$send_to = $gs->get(ADMIN_EMAIL);;
		}
		if(empty($message))
		{
			return;
		}
		if(empty($site_id))
		{
			$site_id = $gs->get(NEW_SITE_ID);
		}
		$message = <<<END
This is your ComfyPage speaking. Find me at $site_id.

$message

Get help at http://help.comfypage.com.
You can send a human at ComfyPage a message via your ComfyPage site.
END;
	return Globals::send_email($send_to, 'your_comfypage@comfypage.com', $subject, $message);
}
public static function send_email_to_us($subject, $message, $from = 'noreply@comfypage.com')
{
	if(empty($message))
	{
		return;
	}
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$gs = Load::general_settings();
	$site_id = $gs->get(NEW_SITE_ID);
	$admin_email = $gs->get(ADMIN_EMAIL);

	$cwd = getcwd();
	$cwd = str_replace("\\", "/", $cwd); //replace windows slashes
	$cwd = explode("/", $cwd); //split string on "/"
	$cwd = $cwd[count($cwd) - 1]; //get just the parent dir name
	$message = <<<END
$message

Diagnostic info
---------------
HTTP_USER_AGENT: $user_agent
SITE ID: $site_id
COMFYPAGE_ADDRESS: $cwd
ADMIN_EMAIL: $admin_email
END;

                Globals::send_email_to_admin($subject, $message);
	}
	//gets a parameter from GET or POST
	//if the param value is an array then it is not used
	//i found out you can use arrays in get/post values
	//e.g. http://my.comfypage.com/agentsteal/mail.php?success[]
	//can expose problems if you don't expect it
	//true if you want html special chars to convert to their appropriate code
	//good for prtoecting against html input
	//when editing a page we need the html to stay as html so set to false
	static function get_param($param_name, $all_params, $default_value = null, $html_special_chars = true)
	{
		$return_value = $default_value;
		if(isset($all_params[$param_name]))
		{
			$temp = $all_params[$param_name];
			if(is_array($temp) == false) //if not an array
			{
				$return_value = $temp; //return the value
				if($html_special_chars)
				{
					//Convert special characters to HTML entities
					$return_value = htmlspecialchars($return_value);
				}
			}
		}
		return $return_value;
	}
	//the old way of getting the site address
	//once we have run the script to update all domains with their
	//correct site id we don't need this function anymore
	//it will be replaced by the subdomain creation function in the server interface that
	//will set the correct site id
	//NOTE also used by paypal functions
	static function old_get_site_address()
	{
		$domain = $_SERVER['HTTP_HOST'];
		$ruri = $_SERVER['REQUEST_URI'];
		$last_slash = strripos($ruri, '/');
		$without_script = substr($ruri, 0, $last_slash);
		//get rid of www.
		$domain = str_ireplace('www.', '', $domain);
		return $domain.$without_script;
	}
	static function get_affinity_google_client_id()
	{
		return 'pub-4271888678667754';
	}
	function self_install_checks()
	{
            if(file_exists('site/store/pages/INDEX.php') == false)
            {
                //regenerate_default_content();
                Globals::copyr('common/default_data/pages', 'site/store/pages');
            }
            if(file_exists('site/site.css') == false)
            {
                require_once('common/contentServer/template_control.php');
                select_template(Load::general_settings(TEMPLATE_IN_USE));
            }
            return null;
	}
	//recursive copy
	public static function copyr($source, $dest)
	{
		// Simple copy for a file
		if (is_file($source))
		{
			$c = copy($source, $dest);
			//chmod($dest, 0777);
			return $c;
		}
		// Make destination directory
		if (!is_dir($dest))
		{
			$oldumask = umask(0);
			mkdir($dest, 0777);
			umask($oldumask);
		}
		// Loop through the folder
		$dir = dir($source);
		while (false !== $entry = $dir->read())
		{
			// Skip pointers
			if ($entry == '.' || $entry == '..' || $entry == '.svn')
			{
				continue;
			}
			// Deep copy directories
			if ($dest !== "$source/$entry")
			{
				Globals::copyr("$source/$entry", "$dest/$entry");
			}
		}
		// Clean up
		$dir->close();
		return true;
	}
 	function get_affinity_footer($show_dl_firefox = true)
	{
		return <<<END
<div style="text-align : center;">
<p style="font-size : 8pt; font-family : arial;">
Copyright &copy; 2006 onwards Affinity Software<br />
<a href="http://www.comfypage.com/">Powered by ComfyPage</a>
</p>
</div>
END;
	}
	function t($s)
	{
		return '<span id=t'.rand().' class=translate_me>'.$s.'</span>';
	}
	function get_displayable_date($timestamp, $with_countdown = false)
	{
		$display = date("jS F Y", $timestamp);
		if($with_countdown)
		{
			$time_diff = $timestamp - time();
			$time_diff = floor($time_diff/60/60/24);
			$display .= " ($time_diff days)";
		}
		return $display;
	}
	function dirsize($dirname)
	{
	    if (!is_dir($dirname) || !is_readable($dirname)) {
	        return false;
	    }
	    $dirname_stack[] = $dirname;
	    $size = 0;
	    do {
	        $dirname = array_shift($dirname_stack);
	        $handle = opendir($dirname);
	        while (false !== ($file = readdir($handle))) {
	            if ($file != '.' && $file != '..' && is_readable($dirname . DIRECTORY_SEPARATOR . $file)) {
	                if (is_dir($dirname . DIRECTORY_SEPARATOR . $file)) {
	                    $dirname_stack[] = $dirname . DIRECTORY_SEPARATOR . $file;
	                }
	                $size += filesize($dirname . DIRECTORY_SEPARATOR . $file);
	            }
	        }
	        closedir($handle);
	    } while (count($dirname_stack) > 0);
	    return $size;
	}
	function empty_dir($dirname)
	{
		if( !is_dir($dirname) ) {
			return true;
		}
	  // Loop through the folder
	  $dir = dir($dirname);
	  while (false !== $entry = $dir->read())
	  {
	    // Skip pointers
	    if ($entry == '.' || $entry == '..')
	    {
	      continue;
	    }
	    $childEntryPath = "$dirname/$entry";
	    if (is_dir($childEntryPath))
	    {
	      Globals::empty_dir($childEntryPath);
	      rmdir($childEntryPath);
	    }
	    else
	    {
	    	unlink($childEntryPath);
		}
	  }
	  // Clean up
	  $dir->close();
	  return true;
	}
	static function scandir_legacy($dir, $sortorder = 0)
	{
	   if(is_dir($dir))
	   {
	       $dirlist = opendir($dir);
	        $files = array();
	       while( ($file = readdir($dirlist)) !== false)
	       {
	           if(!is_dir($file))
	           {
	               $files[] = $file;
	           }
	       }

	       ($sortorder == 0) ? asort($files) : arsort($files);

	        closedir($dirlist);
	       return $files;
	   }
	   else
	   {
				return false;
				break;
	   }
	}
	public static function clearContentDependentCaches()
	{
		$cacheDir = 'site/cache';

		//remove cached rss feed
	    Globals::empty_dir("$cacheDir/rss");

		//remove cached sitemaps
		Globals::empty_dir("$cacheDir/sitemaps");

		//remove cached blog post array
		$fp = "$cacheDir/blog/posts.php";
		if(file_exists($fp)) {
			unlink($fp);
		}

		require_once('common/utils/Broadcast.php');
		Broadcast::notifyOthersOfAddedOrRemovedContent();
	}
	//check if the site is out of the trial period
	public static function site_trial_over() {
		$gs = Load::general_settings();
		$sitecreated = $gs->get(SITE_CREATED);
		
		//if their 2 week trial is over
		if ((time()-1209600) > $sitecreated) {
			return true;
		}
		return false;
	}
}
?>
