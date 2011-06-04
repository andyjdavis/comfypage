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
 *
 *

 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
//require_once('common/general_settings.php');
require_once('common/menu.php');

$gs = Load::general_settings();

if($gs->operating_under_domain())
{
	//this page is used to go from demo to paid site
	//so if not in demo then they don't need it
	exit();
}

Globals::dont_cache();
Login::logged_in();
//track_user();

$error = null;
$success = null;

define('COMPLETE_DOMAIN', 'domain');

/*$complete_domain = null;
if(isset($_POST[COMPLETE_DOMAIN])) $complete_domain = $_POST[COMPLETE_DOMAIN];
*/
$complete_domain = Globals::get_param(COMPLETE_DOMAIN, $_POST);
//they don't want us to reg it
//set_affinity_registers_domain(false);

//if they entered something
if(empty($complete_domain) == false)
{
	require_once('common/utils/Validate.php');
	$error = Validate::domain($complete_domain);
	//if no problem
	if(empty($error))
	{
		//track_user('Valid domain entered');
		Globals::redirect("register_confirm.php?domain=$complete_domain");
	}
}
else //it is empty
{
	$complete_domain = 'example.com';
}

$error = Globals::get_param('error', $_GET, $error);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Sign up for ComfyPage</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />

		<script LANGUAGE="JavaScript" SRC="common/contentServer/contentServer.js" ></script>
	</head>

	<body onLoad="document.getElementsByName('<?php echo(COMPLETE_DOMAIN); ?>')[0].select()">
		<?php
		    $menu = new Menu;
		    $menu->add_item('Start over', 'register.php');
			echo($menu->get_menu());
			echo(Message::get_error_display($error));
			echo(Message::get_success_display($success));

			//require_once('common/validation.php');
			//require_once('common/settings.php');
			$domain_name_input = HtmlInput::get_text_input(COMPLETE_DOMAIN, $complete_domain, 30);
			$message = Message::get_message_display("<b>What's the domain you've registered?</b>", 'auto', 'auto');
			echo <<<END
$message
<table align=center>
	<tr>
	    <td align=center>
			<form method=post>
			<p style='font-weight:bold;'>
				www . $domain_name_input
      		</p>
            <p>
	       		<input style="font-size:larger;" type=submit value=" OK ">
	       	</p>
	       	</form>
		</td>
	</tr>
	</table>
END;
				?>

	    <?php echo(Globals::get_affinity_footer()); ?>

	</body>
</html>