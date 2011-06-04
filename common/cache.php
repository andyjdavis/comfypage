<?php
$cache = new Cache;
define('IMAGE_CACHE', 'site/cache/UserFiles/');
define('USERFILES_ROOT', 'site/UserFiles/');

require_once('common/file.php');

class Cache
{
	function get_thumb_path($pwd, $file_name)
	{
		 $path_to_thumb = IMAGE_CACHE;
		if(!empty($pwd))
			$path_to_thumb .= $pwd.'/';

		$path_to_thumb .= $file_name;

		//if thumb doesn't exist. It won't if the image was uploaded pre our caching
		if(file_exists($path_to_thumb) == false)
		{
			//create the thumb
			$this->create_thumb("$pwd$file_name");
		}
		return $path_to_thumb;
	}

	function create_thumb($original_path)
	{
		require_once('common/lib/image_resize/image_resize.php');
		if(can_be_resized($original_path) == false) return;
		//where the thumb is to be located
		$original_path = ltrim($original_path, '/'); //get rid of slash on start of file if it has one
		$thumb_location = IMAGE_CACHE.$original_path;
		$path_info = pathinfo($thumb_location);
		//get path to dir that contains the thumb
		$path_without_file = $path_info['dirname'];
		//need to recursively create dirs in the cache
		if(file_exists($path_without_file) == false)
		{
			$file_admin = new FileAdmin();
			$file_admin->mkdir_r($path_without_file, 0744);
			//mkdir($path_without_file, 0644, true);
		}
		resize_to_a_width(USERFILES_ROOT.$original_path, 100, true, true, $thumb_location);
	}

	function delete_thumb($file)
	{
		$file = ltrim($file, '/'); //get rid of slash on start of file if it has one
		$thumb_location = IMAGE_CACHE.$file;
		if(file_exists($thumb_location))
		{
			unlink($thumb_location);
		}
	}
}
?>