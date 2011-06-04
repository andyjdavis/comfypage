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

$error = null;
$success = null;

//true once move is done
$move_complete = false;

$chosen_domain = Globals::get_param('domain', $_GET);
if($chosen_domain != null)
{
	//extract reg info
	require_once('common/utils/Validate.php');
	$domain_errors = Validate::domain($chosen_domain);
	//if there's a problem
	if(empty($domain_errors) == false)
	{
		//Registration info incomplete. Sending back to beginning
		Globals::redirect('register.php');
	}
}
$confirmed_domain = Globals::get_param('confirmed_domain', $_POST);

if($confirmed_domain != null)
{
	$domain_errors = Validate::domain($confirmed_domain);
	if(empty($domain_errors))
	{
		require_once('common/ServerInterface.php');
		//Moving to own domain
		$si = new ServerInterface;
		$error = $si->move_to_domain($confirmed_domain);
		if(empty($error))
		{
			//$msg = 'For important info on the move please see http://comfypage.com/index.php?content_id=32 ';
			$msg = <<<END
You asked me to move to $confirmed_domain. You have to do two more things before my move is complete.

1. Register the address ($confirmed_domain) if you haven't already done so.
2. Connect your address with your ComfyPage so that when people visit the address they will see me.

After all steps are complete it can take up to 48 hours for your new domain to start working. For help completing the move please see http://help.comfypage.com/index.php?content_id=9918455
END;
			Globals::send_email_to_admin('Moving to your own domain', $msg);
			//send them to a page in comfypage.com that explains what's happening
			//redirect('http://comfypage.com/index.php?content_id=32');
			$move_complete = true;
			//set_new_site_id($confirmed_domain); //set the site id to the new domain
			Load::general_settings()->set(NEW_SITE_ID, strtolower($confirmed_domain));
			$success = "Your ComfyPage is ready to use $confirmed_domain";
			$gs->set(MOVED_TO_OWN_DOMAIN, true);
		}
		else
		{
			Globals::redirect("register_with_existing_domain.php?error=$error");
		}
	}
	else //errors
	{
		Globals::redirect('register.php');
	}
}

//i ran into a problem with the $chosen_domain var
//it is being set by a call to get_chosen_domain
//it was behaving as if it was set by reference
//as after set_chosen_domain was called $chosen_domain was blank
//go figure
//this gets around it by assigning it into a string
$chosen_domain_message = "Your domain will be <b>$chosen_domain</b>";

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Sign up for ComfyPage</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
		<script LANGUAGE="JavaScript" SRC="common/contentServer/contentServer.js" ></script>
	</head>

	<body>
		<?php
			$menu = new Menu;
			$menu->add_item('Start over', 'register.php');
			echo($menu->get_menu());
			echo(Message::get_error_display($error));
			echo(Message::get_success_display($success));
		?>
		<?php
			if($move_complete)
			{
		?>
			<p style="font-weight:bold;text-align:center;">There are two more things you must do.</p>
			<ol style="margin:1em 30%;">
				<li><p>Register the domain (<?php echo($confirmed_domain); ?>) if you haven't done that</p></li>
				<li><p>Connect your ComfyPage and your domain together</p></li>
			</ol>
			<p style="text-align:center;font-weight:bold;">Help with these tasks is available <a target=_blank href='http://help.comfypage.com/index.php?content_id=9918455'>here</a></p>
		<?php
			} //end if
			else
			{
		?>
		<div style="text-align:center;">
		<p><?php echo($chosen_domain_message); ?></p>
		<form method=post>
			<input type=hidden name=confirmed_domain value="<?php echo($chosen_domain); ?>">
			<input type=submit value="Continue">
		</form>
		<p style="font-size:smaller;"><a href='register_with_existing_domain.php'>Back</a></p>
		</div>
		<?php
			}//end else
		?>
		<?php echo(Globals::get_affinity_footer()); ?>

	</body>
</html>