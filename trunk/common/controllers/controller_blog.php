<?php
define('BLOG_FORCE_REGEN',true);

define('BLOG_POST_ID','id');

//function used to order blog posts by date written
function blog_cmp($a, $b)
{
    if ($a == $b) {
        return 0;
    }
    return ( strtotime($a->get(LAST_MODIFIED)) > strtotime($b->get(LAST_MODIFIED)) ) ? -1 : 1;
}

class controller_blog
{
	private function get_blog_post_array($max=0)
	{
		$ps = Load::page_store();
		$user_pages = $ps->load_users_pages();
		$user_pages[] = Load::page_store()->load_index_page();
		$to_ret = array();
		$i = 0;
		foreach($user_pages as $content)
		{
			if( !$content->get(CONTENT_SHOW_DATE) )
			{
				continue;
			}
			$to_ret[] = $content;
			$i++;
		}

		//sort the blog posts by modification time
		usort($to_ret, "blog_cmp");

		if($max>0 && sizeof($to_ret)>$max)
		{
			$to_ret = array_slice($to_ret,0,$max);
		}
		return $to_ret;
	}

	public function get_blog_posts($max=0)
	{
		$cacheDir = 'site/cache/';
		$blogDir = $cacheDir.'blog/';
		$fp = $blogDir.'posts.php';
		if(BLOG_FORCE_REGEN || !file_exists($fp))
		{
			require_once('common/file.php');

			$file_admin = new FileAdmin();
			if(!$file_admin->folder_does_exist($cacheDir))
			{
				$file_admin->mkdir_r($cacheDir, 0744);
			}
			if(!$file_admin->folder_does_exist($blogDir))
			{
				$file_admin->mkdir_r($blogDir, 0744);
			}
			$pages = $this->get_blog_post_array();

			$to_output = array();
			$setting_names = $page_settings = null;
			foreach($pages as $page)
			{
				$page_settings = array();
				$setting_names = $page->get_setting_names();
				foreach($setting_names as $setting_name)
				{
					$page_settings[$setting_name] = $page->get($setting_name);
				}
				$page_settings[LAST_MODIFIED] = $page->get(LAST_MODIFIED);
				$page_settings[BLOG_POST_ID] = $page->id;
				$to_output[] = $page_settings;
			}
			$array_as_string = var_export($to_output, true);
			$php_content = '<?php $blog_posts = '.$array_as_string.'; ?'.'>'; //not a variable name. I want '$blog_posts' be printed as '$blog_posts'
	    	file_put_contents($fp, $php_content);
		}

		require($fp);
		return $blog_posts;
	}

	public function get_blog_recent_articles($current_content_id, $title, $max = 3)
	{
		return $this->get_blog_listing($current_content_id, $title, 3, true);
	}

	public function get_blog_titles_links($current_content_id, $title, $max=0, $include_content=false)
	{
		return $this->get_blog_listing($current_content_id, $title, $max, false);
	}
	
	private function get_blog_listing($current_content_id, $title, $max=0, $include_content=false)
	{
		$page_array = $this->get_blog_posts($max);

		$output = $classname = null;
		$menu_seperator = '</p><p>';
		$classname = 'cp_menu_link cp_blog_link';

		$output .= '<p>';
		$i = 0;
		foreach($page_array as $content)
		{
			$content_id = $content[BLOG_POST_ID];
			$content_title = $content[CONTENT_TITLE];

			//turn Mon, 28 Dec 2009 18:21:35 +0800 into 28 Dec 2009
			//$content_modified = substr($content->get(LAST_MODIFIED),-27,12);
			$content_modified = substr($content[LAST_MODIFIED],-27,12);

			$classname_to_use = $classname;
			if($current_content_id==$content_id)
			{
				$classname_to_use .= '_curr';
			}
			$output .= "$menu_seperator<a class='$classname_to_use' href='index.php?content_id=$content_id'>$content_title</a> $content_modified";
			if($include_content)
			{
				$output .= $content[RAW_CONTENT];
			}
			$i++;
		}
		$output .= '</p>';
		return "<h3>$title</h3>$output";
	}
}
?>
