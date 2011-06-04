<?php

$files = new FileAdmin;

define('PWD', 'PWD');
define('OUR_ROOT', 'site/UserFiles/');

define('MAX_SIZE_MB', 2);

class FileAdmin
{
	var $allowed_types = array('js','kmz','jpg','jpeg','css','gif','png','bmp','tif','zip','sit','rar','gz','tar','mov','mpg','avi','asf','mpeg','wmv','aif','aiff','wav','mp3','swf','ppt','rtf','doc','pdf','xls','txt','xml','xsl','dtd');
	
	function get_folder_structure($start_at = '')
	{
	    $start_at = $this->get_real_path($start_at);
	    $files = $this->get_file_list($start_at, true, false);
	    natcasesort($files);
		return $files;
	}
	
	function get_files($start_at = '')
	{
	    $start_at = $this->get_real_path($start_at);
	    $files = $this->get_file_list($start_at, false, true);
	    natcasesort($files);
		return $files;
	}
	
	function get_folders($start_at = '')
	{
	    $start_at = $this->get_real_path($start_at);
		return $this->get_file_list($start_at, true, false);
	}
	
	//this function is necessary because mkdir on linux isnt recursive for some stupid reason
	function mkdir_r($dirName, $rights=0777)
	{
		$dirs = explode('/', $dirName);
		$dir='';
		foreach ($dirs as $part)
		{
			$dir.=$part.'/';
			if (!is_dir($dir) && strlen($dir)>0)
			{
				mkdir($dir, $rights);
			}
		}
	}
	
	function get_file_list($start_at, $want_dirs = true, $want_files = true)
	{
		if(file_exists($start_at) == false)
		{
			return array();
		}
		$dir = dir($start_at);
		$list = array();
		while (false !== $entry = $dir->read())
		{
			// Skip pointers
			if($entry == '.' || $entry == '..' || $entry == '.svn')
			{
				continue;
			}
			$childEntryPath = "$start_at/$entry";
			if (is_dir($childEntryPath))
			{
			    if($want_dirs)
			    {
					$list[$entry] = $this->get_file_list($childEntryPath, $want_dirs, $want_files);
				}
			}
			else // not a dir
			{
			    if($want_files)
			    {
					$list[$entry] = $entry;
				}
			}
		}
		return $list;
	}
	
	function folder_does_exist($folder)
	{
		$path = $this->get_real_path($folder);
		return file_exists($path);
	}
	
	function delete($file)
	{
	    $size = null;
	    $file = $this->get_real_path($file);
	    if(file_exists($file))
	    {
		    $size = filesize($file);
		    //delete_file($file);
		    if(file_exists($file))
		    {
				unlink($file);
			}
		    return $size;
	    }
	}
	function create_folder($name)
	{
	    $name = $this->get_real_path($name);
	    if(file_exists($name) == false)
	    {
	    	mkdir($name);
	    }
	}
	function delete_folder($file, $is_userfile = true)
	{
	    $size = null;
	    if($is_userfile)
		{
			$file = $this->get_real_path($file);
		}
	    if(file_exists($file))
	    {
	        $size = Globals::dirsize($file);
	    	Globals::empty_dir($file);
	    	rmdir($file);
	    }
	    return $size;
	}
	//verfiy a file/folder name is safe to use
	function is_filename_ok($file)
	{
		$generic_msg = 'Illegal file name';
		if(empty($file)) return 'No file name specified';
		if(strpos($file, '.') === 0) return $generic_msg; //if it starts with a '.'
		if(strpos($file, '..') !== false) return $generic_msg; //if it contains a '..'
		if(strpos($file, '/') === 0) return $generic_msg; //if it starts with a '/'
		if(strpos($file, '~') !== false) return $generic_msg; //if it contains a '~'
		return null;
	}
	function get_real_path($path_relative_to_root)
	{
	    $path_relative_to_root = trim($path_relative_to_root, '/');
		return OUR_ROOT . $path_relative_to_root;
	}
	function get_http_path($file)
	{
	    $real = $this->get_real_path($file);
		$addy = Load::general_settings(NEW_SITE_ID);
		return "http://$addy/$real";
	}
	
	function get_allowed_types()
	{
		return $this->allowed_types;
	}
	
	function upload_file($pwd)
	{
	    $upload_path = 'site/UserFiles/';
		if(empty($pwd) == false)
		{
			$upload_path = "$upload_path$pwd/";
		}
		//check the upload
		$name = $_FILES['userfile']['name'];
		//get rid of slashes otherwise they will be replaced with a dash too
		$name = stripslashes($name);
		//replace horrid troublesome chars with good char (dash)
		$name = ereg_replace('[^A-Za-z0-9.]', '-', $name);
		$pathinfo = pathinfo($name);
		//get the name without extension
		$name_wo_ext = $pathinfo['filename'];
		//get rid of dots in filename. They cause some upload problem server side. PHP doesn't have a problem maybe something lower level
		$name_wo_ext = str_replace('.', '-', $name_wo_ext);
		//reconstruct happy filename
		$_FILES['userfile']['name'] = "$name_wo_ext." . $pathinfo['extension'];
		$ext = $pathinfo['extension'];
		$size = $_FILES['userfile']['size'];
		$error_code = $_FILES['userfile']['error'];
		if(in_array(strtolower($ext), $this->allowed_types) == false)
		{
			return "File extension ($ext) not allowed";
		}
		if($error_code != 0)
		{
		    switch($error_code)
		    {
				case UPLOAD_ERR_INI_SIZE:
				{
					return "File too big. Max is " . MAX_SIZE_MB . " MB";
				}
				default :
				{
					return "An error occured. Please Try again.";
				}
			}
		}
		//do the upload
		require_once('common/lib/file_upload/upload.class.php');
		$file = new FileUpload();
		foreach($this->allowed_types as $at)
		{
			$file->AddGoodFileType(".$at");
		}
		$file->SetMaxFileSize(MAX_SIZE_MB * 1024 * 1024);
		$file->SetOverwrite(true);
		$file->UploadFile('userfile', $upload_path, '');
		return null;
	}
	
	function get_upload_size()
	{
		return $_FILES['userfile']['size'];
	}
	
	function get_file_count($dirname = OUR_ROOT)
	{
	    $count = 0;
		// Loop through the folder
		$dir = dir($dirname);
		while (false !== $entry = $dir->read())
		{
			// Skip pointers
			if ($entry == '.' || $entry == '..' || $entry == '.svn')
			{
				continue;
			}

			$childEntryPath = "$dirname/$entry";
			if (is_dir($childEntryPath))
			{
				$count += $this->get_file_count($childEntryPath);
			}
			else
			{
				$count++;
			}
  		}

	  // Clean up
	  $dir->close();
	  return $count;
	}
}

	function findexts ($filename)
	{
		$filename = strtolower($filename) ;
		$exts = split("[/\\.]", $filename) ;
		$n = count($exts)-1;
		$exts = $exts[$n];
		return $exts;
	}
?>
