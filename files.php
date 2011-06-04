<?php
// This file is part of ComfyPage - http://comfypage.com
//
// ComfyPage is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ComfyPage is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ComfyPage.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
require_once('common/file.php');
require_once('common/cache.php');
include_once('common/menu.php');

Globals::dont_cache();
Login::logged_in();

$success = null;

//select is true if you want to display the select option
$select = isset($_GET['select']);
if(isset($_POST['select']))
{
    $select = true;
}
//the get var to add to maintain the select otpion while moving thru file manager
$select_var = null;
if($select)
{
    $select_var = 'select=true';
}
$pwd = Globals::get_param('folder', $_GET);
$pwd = Globals::get_param('folder', $_POST, $pwd);
if($pwd == '.' || $pwd == '..' || $pwd == '/')
{
    $pwd = '';
}
$new_folder = Globals::get_param('new_folder', $_POST);

if($new_folder != null)
{
    $temp = Globals::get_param('folder', $_POST);
    $pwd = urldecode($temp);
    $new_pwd = "$pwd/$new_folder";
    $new_folder_error = $files->is_filename_ok($new_folder);
    if(empty($new_folder_error))
    {
        $files->create_folder($new_pwd);
        $success = 'Folder created';
        $pwd = trim($new_pwd, '/'); //change working dir
    }
    else
    {
        $error = $new_folder_error;
    }
}
$delete_folder = Globals::get_param('delete_folder', $_GET);

if($delete_folder != null)
{
    $folder_name_errors = $files->is_filename_ok($delete_folder);
    if(empty($folder_name_errors))
    {
        $size = $files->delete_folder($delete_folder);
        require_once('common/cache.php');
        $files->delete_folder(IMAGE_CACHE . $delete_folder, false);
        $success = 'Folder deleted';
        //redirect to get rid of get vars in addy
        Globals::redirect("files.php?folder=$pwd&$select_var");
    }
    else
    {
        $error = $folder_name_errors;
    }
}
$delete_file = Globals::get_param('delete', $_GET);

if($delete_file != null)
{
    $file_name_errors = $files->is_filename_ok($delete_file);
    if(empty($file_name_errors))
    {
        $size = $files->delete("$pwd/$delete_file");
        require_once('common/cache.php');
        $cache->delete_thumb("$pwd/$delete_file");
        $success = 'File deleted';
        //redirect to get rid of get vars in addy
        Globals::redirect("files.php?folder=$pwd&$select_var");
    }
    else
    {
        $error = $file_name_errors;
    }
}
$resize_file = Globals::get_param('resize', $_GET);
if($resize_file != null)
{
 	$file_name_errors = $files->is_filename_ok($resize_file);
 	if(empty($file_name_errors))
 	{
		require_once('common/lib/image_resize/image_resize.php');
		$real_path = $files->get_real_path("$pwd/$resize_file");
		do_image_resize_for_me($real_path);
		$success = 'Image resized';
		//redirect to get rid of get vars in addy
		Globals::redirect("files.php?folder=$pwd&$select_var&success=Image resized");
	}
	else
	{
		$error = $file_name_errors;
	}
}
if(isset($_FILES['userfile']))
{
    $error = $files->upload_file($pwd);
	if(empty($error))
	{
		require_once('common/cache.php');
		$name = $_FILES['userfile']['name'];
		$name = ltrim($name, '/'); //get rid of slash on start of file if it has one
		$success = 'Upload done';
		Load::award_settings()->bestow_award(UPLOAD_IMAGE_AWARD);
		//if they uploaded while selecting a file
		if($select)
		{
			$file = $_FILES['userfile']['name'];
			if(empty($pwd))
		    {
				$rel_path = "$pwd$file";
			}
			else
			{
	            $rel_path = "$pwd/$file";
			}
			//show link letting them select the just uploaded file
			$success = "$success<br><a href='javascript:SelectFile(\"site/UserFiles/$rel_path\");'>Select uploaded file</a>";
		}
		$size = $files->get_upload_size();
	}
	else
	{
            //Upload file failed
	}
}
$success = Globals::get_param('success', $_GET, $success);

$structure = $files->get_folder_structure();
$file_list = $files->get_files($pwd);

function get_structure_html($structure, $depth = 0, $base_path = '', $pwd = null)
{
    //if not starting at top
    if(empty($base_path) == false)
    {
        //add the base path with a slash
        $base_path = "$base_path/";

        //this is done only when not at the top
        //because if bas path is blank we don't want to add the slash
    }

    $html = '<ul>';
    $folder_names = array_keys($structure);
    natcasesort($folder_names);
    foreach($folder_names as $folder_name)
    {
        $full_path = "$base_path$folder_name";
            $html .= '<li>' . get_folder_line($full_path, $folder_name, $pwd) . '</li>';
            $html .= get_structure_html($structure[$folder_name], $depth + 1, $full_path, $pwd);
    }
    $html .= '</ul>';
    return $html;
}
function get_folder_line($dir, $display_text = null, $pwd = null)
{
    global $select_var;

    $folder_img = "<img border=0 src=common/images/closed_folder.gif>";
    if($pwd == $dir)
    {
        $display_text = "<b>$display_text</b>";
        $folder_img = "<img border=0 src=common/images/open_folder.gif>";
    }
    //make sure path is properly encoded
    $dir = urlencode($dir);
    if(empty($display_text))
    {
        $display_text = $dir;
    }
    return "<a style='text-decoration:none;' href='files.php?folder=$dir&$select_var'>$folder_img $display_text</a>";
}
function get_file_row($file, $path, $pwd, $select_on = false, $resizeable = false, $real_path)
{
	if($file==null)
	{
		return '<tr><td><i>(Empty folder)</i></td><td></td><td></td><td></td></tr>';
	}

	$rand = rand();

	//echo("pwd==$pwd");
	$pwd_end = strlen($pwd)-1;
	if($pwd_end > -1)
	{
		if($pwd[$pwd_end]=='/')
		{
			$pwd = substr($pwd, 0, $pwd_end);
		}
		//echo("pwd==$pwd");
	}

	global $select_var;
	$select = null;
	if($select_on)
	{
	    if(empty($pwd))
	    {
			$rel_path = "$pwd$file";
		}
		else
		{
            $rel_path = "$pwd/$file";
		}
		$select = '<a href="javascript:SelectFile(\'site/UserFiles/' . $rel_path . '\');"><span id=s'.$rand.' class=translate_me>Select</span></a>';
	}
	$thumb = null;
	if(can_be_resized($path))
	{
	    /*$path_to_thumb = 'site/cache/UserFiles/';
	    if(!empty($pwd))
	    	$path_to_thumb .= $pwd.'/';
	    $path_to_thumb .= $file;

	    //if thumb doesn't exist. It won't if the image was uploaded pre our caching
	    if(file_exists($path_to_thumb) == false)
	    {
	        //create the thumb
			require_once('common/cache.php');
			$cache = new Cache;
			$cache->create_thumb("$pwd/$file");
		}

            $cache = new Cache;
            $path_to_thumb = $cache->get_thumb_path($pwd, $file);
             */
            $path_to_thumb = $path;
            $thumb = "<img width='50px' src='$path_to_thumb'>";
	}
	$preview = "<a target='previewit' href='$path'><span id=p_$rand class=translate_me>Preview</span></a>";
	$pwd = urlencode($pwd);
	$delete = "<a onclick='return confirmDeletion(\"Delete?\");' href='files.php?folder=$pwd&delete=$file&$select_var'><span id=d_$rand class=translate_me>Delete</span></a>";
	$resize = null;
	if($resizeable)
	{
		$resize = "<a style='color:red;font-weight:bold;' href='files.php?folder=$pwd&resize=$file&$select_var'><img src='common/images/resize.gif' border=0 alt='Make this big image smaller to increase website performance'></a>";
	}
	return "<tr><td style='width:50px;'>$thumb</td><td style='width:50px;font-weight:bold;'>$file</td><td style='width:50px;' align=center>$select</td><td style='width:50px;' align=center>$preview</td><td style='width:50px;' align=center>$delete</td><td>$resize</td></tr>";
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>File Manager</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="common/contentServer/contentServer<img src='common/images/resize.gif' border=0 alt='Make this big image smaller to increase website performance'>.js"></script>
		<script type="text/javascript">
			function confirmDeletion(msg)
			{
				return confirm(msg);
			}
			function SelectFile( fileUrl )
{
	// window.opener.SetUrl( url, width, height, alt);
	if(window.opener.SetUrl)
	{
		window.opener.SetUrl( fileUrl ) ;
	}

	//if opened by the "create link" dialog then the protocol combo box exists
	if(window.opener.GetE)
	{
		if(window.opener.GetE('cmbLinkProtocol') != null)
		{
		    //as we are creating a relative link
		    //set the protocol to nothing
			window.opener.GetE('cmbLinkProtocol').value = '';
		}
	}
	window.close() ;
}
        </script>
        <style>
        ul{list-style:none;margin-left:0.7em;padding-left:0.7em;}
        </style>

        <?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>
	<body>
	    <?php
	    if($select == false)
	    {
	        //$help = Message::get_help_link(9918436, 'Get help with files');
			echo(get_menu());
		}
	    ?>
		<table width=100% border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td width="200px" valign=top>
					<!-- FOLDER TREE -->
					<ul style="padding-top:1em;">
					<?php
					    echo('<li>' . get_folder_line('', '<span id=mf class=translate_me>My files</span>', $pwd) . '</li>');
						echo(get_structure_html($structure, 1, '', $pwd));
					?>
					</ul>
					<!-- NEW FOLDER -->
					<form action=files.php method=post style='margin:0;text-align:center'>
						<input value='New folder name' name=new_folder>
						<input type=hidden value='<?php echo(urlencode($pwd)); ?>' name=folder>
						<span id=nsfs class=translate_me><input type=submit value='Create sub-folder'></span>
						<?php if($select) echo('<input type=hidden value=true name=select>'); ?>
					</form>

					<?php
					if(empty($pwd) == false) //if not in root
					{
						echo "<p style='text-align:center;'><a onclick='return confirmDeletion(\"Delete folder and contents?\");' href='files.php?delete_folder=" . urlencode($pwd) . "&$select_var'>Delete current folder</a></p>";
					} //end if not in root
					?>

					<?php
					if(empty($error) == false)
					{
						echo(Message::get_error_display($error));
					}
					if(empty($success) == false)
					{
						echo(Message::get_success_display($success));
					}
					?>
				</td>
				<td valign=top style="border-left:solid black 1px;">
				<!-- FILE LIST -->
	    			<table align=center cellpadding=5 width="90%" cellspacing=2 style='border:solid black 0px;' border=0>
	    				<tr>
							<td colspan=6>
							    <!-- UPLOAD -->
								<form enctype="multipart/form-data" method=POST style='margin:0;'>
									<input type=file name=userfile>
									<input type=hidden value="<?php echo($pwd); ?>" name=folder>
									<span id=su class=translate_me><input type=submit value=Upload></span> <?php //if($file_limit_reached) echo('Upload will cost ' . FILE_COST . ' credits'); ?>
									<?php if($select) echo('<input type=hidden value=true name=select>'); ?>
									<span id=mfs class=translate_me>Max file size <?php echo(MAX_SIZE_MB); ?> MB</span>
								</form>
								<?php //echo("$files_used_count of $file_limit files stored"); ?>
							</td>
						</tr>
						<?php
						if(empty($file_list))
						{
							echo(get_file_row(null, null, null, null, null, null));
						}
						require_once('common/lib/image_resize/image_resize.php');
						$has_large_image = false;

						foreach($file_list as $file)
						{
							if(empty($pwd))
						  	{
								$path = $file;
							}
							else
							{
								$path = "$pwd/$file";
							}
							$http_path = $files->get_http_path($path);
							$real_path = $files->get_real_path($path);
							//$resizeable = should_be_resized($real_path);
							$resizeable = false;
							$has_large_image = $has_large_image || $resizeable;
							echo(get_file_row($file, $http_path, $pwd, $select, $resizeable, $real_path));
						}
						?>
					</table>
				</td>
			</tr>
		</table>

		<?php
			if($has_large_image)
			{
		?>
		<p style="padding:0.5em 30%;font-size:smaller;"><b>What does this icon <img src='common/images/resize.gif' border=0 alt='Make this big image smaller to increase website performance'> mean?</b> It means the image is big and if you click on the icon the image will be made smaller. Smaller images make your website quicker and easier for visitors to use.</p>
		<?php
			}
		?>

		<?php if($select == false) echo(Globals::get_affinity_footer()); ?>
	</body>
</html>
