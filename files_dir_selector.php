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
/*$select = isset($_GET['select']);
if(isset($_POST['select']))
{
	$select = true;
}
//the get var to add to maintain the select otpion while moving thru file manager
$select_var = null;
if($select)
{
	$select_var = 'select=true';
}*/
$pwd = '';


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
    $folder_img = "<img border=0 src=common/images/closed_folder.gif>";
    //$dir = urlencode($dir);
    return '<a style="text-decoration:none;" href="javascript:SelectFile(\'site/UserFiles/'.$dir.'\');">'.$folder_img.$display_text.'</a>';
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Select A Directory</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="common/contentServer/contentServer<img src='common/images/resize.gif' border=0 alt='Make this big image smaller to increase website performance'>.js"></script>
		<script type="text/javascript">

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
	window.close();
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

		<ul style="padding-top:1em;">
		<?php
			echo('<li>' . get_folder_line('', '<span id=mf class=translate_me>My files</span>', $pwd) . '</li>');
			echo(get_structure_html($structure, 1, '', $pwd));
		?>
		</ul>

		<?php
			if(empty($error) == false)
			{
				echo(Message::get_error_display($error));
			}
			if(empty($success) == false)
			{
				echo(Message::get_success_display($success));
			}

			//if($select == false) echo(Globals::get_affinity_footer());
		?>
	</body>
</html>
