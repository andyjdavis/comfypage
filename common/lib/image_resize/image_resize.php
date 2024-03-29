<?php
define('IMAGE_TOO_BIG', 400);

//640 * 480 = 307200
//600 * 400 = 240000
//512 * 384 = 196608
define('MAX_VOLUME', 240000);

function get_image_volume($file)
{
	$vol = 0;

	$img_info = @getimagesize($file);
	if($img_info)
	{
		$current_width = $img_info[0];
		$current_height = $img_info[1];

		$vol =  $current_width * $current_height;
	}

	return $vol;
}

function should_be_resized($file)
{
	if(can_be_resized($file) == false)
	{
		return false;
	}
	/*
	//return filesize($file) > (BIG_FILE_SIZE_IN_KB * 1024);
	$img_info = getimagesize($file);
	$current_width = $img_info[0];
	$current_height = $img_info[1];
	//return true if width and height are over the threshold
	return $current_width > IMAGE_TOO_BIG && $current_height > IMAGE_TOO_BIG;
	*/

	$curr_vol = get_image_volume($file);
	if($curr_vol > MAX_VOLUME)
	{
		return true;
	}

	return false;
}
function can_be_resized($file)
{
	$path_info = pathinfo($file);
    $ext = $path_info['extension'];
	$ext = strtolower($ext);
	$exts = resizeable_extensions();
	return in_array($ext, $exts);
}

function do_image_resize_for_me($file)
{
	$percentage_change = 0;

	$curr_vol = get_image_volume($file);
	if($curr_vol > MAX_VOLUME)
	{
		$percentage_change = MAX_VOLUME / $curr_vol;
	}
	$percentage_change = sqrt($percentage_change);

	if($percentage_change!=0)
	{
		resize_image($file, $percentage_change);
	}
}
function resizeable_extensions()
{
	return array
			(
				'gif',
				'jpg',
				'jpeg',
				'png',
			);
}
//takes a filename and a percentage (0.1 = 10%)
//copies over the file with another image of the new size
function resize_image($filename, $percent, $write_file = true, $write_to = null)
{
	if($write_file) //if want to write the image to disk
	{
		if($write_to == null) //if no new place to write image to
		{
			$write_to = $filename; //write back to same image
		}
	}
	if(can_be_resized($filename) == false)
	{
		return;
	}
	$path_info = pathinfo($filename);
    $ext = $path_info['extension'];
    $ext = strtolower($ext);

	// Get new sizes
	list($width, $height) = getimagesize($filename);
	$newwidth = $width * $percent;
	$newheight = $height * $percent;
	// Load
	//TODO remove the @ symbol and make sure this function works in all contexts
	$thumb = @imagecreatetruecolor($newwidth, $newheight);

	if(!$thumb)
	{
		return;
	}

//	ini_set("memory_limit","80M");

	switch($ext)
	{
		case 'gif':
		{
			$source = imagecreatefromgif($filename);
			break;
		}
		case 'jpg':
		case 'jpeg':
		{
		    //ignoring the error generated by this function
		    //see http://php.net/manual/en/function.imagecreatefromjpeg.php
			ini_set('gd.jpeg_ignore_warning', 1);
			$source = imagecreatefromjpeg($filename);
			break;
		}
		case 'png':
		{
			//return;
			$source = imagecreatefrompng($filename);
			break;
		}
		default :
		{
			return;
		}
	}

	//image may fail to open.  Usually due to a problem within the file we can't do anything about
	if($source)
	{
		// Resize
		if(imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height))
		{
			// Output
			switch($ext)
			{
				case 'gif':
				{
					if($write_file)
					{
						imagegif($thumb,$write_to);
					}
					else
					{
						imagegif($thumb);
					}
					break;
				}
				case 'jpg':
				case 'jpeg':
				{
					if($write_file)
					{
						imagejpeg($thumb,$write_to);
					}
					else
					{
						imagejpeg($thumb);
					}
					break;
				}
				case 'png':
				{
					if($write_file)
					{
						imagepng($thumb,$write_to);
					}
					else
					{
						imagepng($thumb);
					}
					break;
				}
			}
		}
	}

	//mem intensive ops so destroy the images
	imagedestroy($thumb);
	if($source)
	{
		imagedestroy($source);
	}
}
function resize_to_a_width($file, $target_width = 100, $stretch = true, $write_file = false, $write_to = null)
{
	$img_info = @getimagesize($file);
	if($img_info)
	{
		$current_width = $img_info[0];
		if($current_width > 0) //don't divide by zero
		{
			//if image is smaller than target and don't want it stretched
			if($target_width > $current_width && !$stretch)
			{
				$target_width = $current_width; //no size change
			}
			$percent = $target_width / $current_width;
			resize_image($file, $percent, $write_file, $write_to);
		}
	}
}
function resize_to_a_height($file, $target_height = 100, $stretch = true)
{
	$img_info = getimagesize($file);
	$current_height = $img_info[1];
	if($current_height > 0) //don't divide by zero
	{
		//if image is smaller than target and don't want it stretched
		if($target_height > $current_height && !$stretch)
		{
			$target_height = $current_height; //no size change
		}
		$percent = $target_height / $current_height;
		resize_image($file, $percent, false);
	}
}
?>
