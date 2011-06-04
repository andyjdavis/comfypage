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
 * ComfyPage frontpage
 *

 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
//require_once('common/general_settings.php');
require_once('common/contentServer/template_control.php');
require_once('common/menu.php');

Globals::dont_cache();
Login::logged_in();
//track_user(null, false);

//require_once('common/permissions.php');
$permissions = Load::permission_settings();
$permissions->check_permission(TEMPLATE_ALLOWED, false);

$error = null;
$content_id = null;

$logoImgTag = null;
$encLogoPath = null;

$submitDisplay = 'none';

$gs = Load::general_settings();

if($_GET)
{
	$encLogoPath = Globals::get_param('logoPath', $_GET);
	$content_id = Globals::get_param('content_id', $_GET);
	$revert = Globals::get_param('revert', $_GET);

	//require_once('common/portal.php');

	if(!empty($revert))
	{
		set_default_feature_image($gs->get(TEMPLATE_IN_USE));
		$portal = Load::portal_settings();
		//if($portal->get_use_my_template())
		if($portal->get(USE_MY_TEMPLATE))
		{
			$portal->apply_my_settings_to_portal();
		}
		$submitDisplay = 'inline';
	}
	if(!empty($encLogoPath))
	{
		$decLogoPath = urldecode($encLogoPath);
		//$logoImgTag = "<img src=\"$decLogoPath\" />";
		$gs->set(FEATURE_IMAGE, $decLogoPath);
		$portal = Load::portal_settings();
		if($portal->get(USE_MY_TEMPLATE))
		{
			$portal->apply_my_settings_to_portal();
		}
		//redirect back to wherever they came from
		$url = null;
		if(!empty($content_id))
		{
			$url = 'Location:index.php?content_id='.$content_id;
		}
		else
		{
			$url = 'Location:index.php';
		}
		header($url);
	}
}

//if the havent set a new logo get the old one
if(empty($encLogoPath))
{
	$img_path = $gs->get(FEATURE_IMAGE);

	if(!empty($img_path))
	{
		$logoImgTag = "<img id=imgFeature src=\"$img_path\" alt=\"feature image\" />";
		$encLogoPath = urlencode($img_path);
	}
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Update Feature Image</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />

		<script language=Javascript>

		function SelectFile()
		{
			var sOptions = "toolbar=no,status=no,resizable=yes,dependent=yes,scrollbars=yes" ;
			sOptions += ",width=" + 640 ;
			sOptions += ",height=" + 480 ;

			window.open( 'files.php?select=1', 'files', sOptions );
		}

		function SetUrl(url)
		{
			document.getElementById("logoPath").value = url;
			document.getElementById("imgFeature").src = url;

			document.getElementById("submitButton").style.display = "inline";
		}
		</script>
	</head>

	<body>
		<?php
		    echo(get_menu());
			echo('<br /><br /><br />');
			echo(Message::get_error_display($error));
			//echo(Message::get_success_display($success));

			echo <<<END
			<div align=center>
			<form method=get name=theForm style="padding:0; margin:0;">
			$logoImgTag<br />
			<input type=hidden name=content_id id=content_id value=$content_id>
			<input type=hidden name=logoPath id=logoPath value="$encLogoPath">
			<br />
			<a href="#" onclick="Javascript:SelectFile();">Change Feature Image</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="feature_image.php?content_id=$content_id&revert=1">Use Default Feature Image</a>
			<br /><br />
      <input name=submitButton type=submit value=" Save " id=submitButton style="display:$submitDisplay;" />
      </div>
END;
					?>
    <?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>