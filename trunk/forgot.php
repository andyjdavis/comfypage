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
require_once('common/utils/PasswordGenerator.php');

Globals::dont_cache();

$error = null;
$success = null;

$their_email = Globals::get_param('email', $_POST);
if($their_email != null)
{
    require_once('common/lib/form_spam_blocker/fsbb.php');
	//check submission is by a human
	if(check_hidden_tags($_POST) == false)
	{
		$error = 'An error occurred. Please try again';
	}
	else
	{
	    $gs = Load::general_settings();
		$their_email = strtolower($their_email);
		$saved_email = strtolower($gs->get(ADMIN_EMAIL));
		$user = Load::user_store()->get_user($their_email);
		if($their_email == $saved_email)
		{
		    require_once('common/utils/Validate.php');
			$new_password = PasswordGenerator::generate();
			$body = 'Your new ComfyPage password is ' . $new_password;
			if(Globals::send_email_to_admin('ComfyPage login help', $body))
			{
				$success = "<p>A new password has been emailed to the administrator's email address</p><p>Check your inbox and junk email</p><p><a href=admin.php>Log in</a></p>";
				$gs->set(PASSWORD, $new_password);
			}
			else
			{
				$error = '<p>Email sending failed. Please try again.</p>';
			}
		}
		else if($user != null)
		{
		    require_once('common/utils/Validate.php');
			$new_password = PasswordGenerator::generate();
			$body = 'Your new ComfyPage password is ' . $new_password;
			if(Globals::send_email($user->get(USER_EMAIL), 'your_comfypage@comfypage.com', 'ComfyPage login help', $body))
			{
				$success = "<p>A new password has been emailed to the email address</p><p>Check your inbox and junk email</p><p><a href=admin.php>Log in</a></p>";
				$user->set(USER_PASSWORD, $new_password);
			}
			else {
				$error = '<p>Email sending failed. Please try again.</p>';
			}
		}
		else
		{
			$error = "The email address is not correct";
		}
	}
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Forgotten Details</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />

		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>

	<body onLoad="document.getElementById('email').focus()">

		<?php
			echo(Message::get_error_display($error));
			echo(Message::get_success_display($success));
			//if not successful
			if(empty($success) == true)
			{
				echo(Message::get_message_display("<p>Enter your ComfyPage administrator email address and a new password will be emailed to you</p>", 'auto', 'auto'));
		?>

		<form method="post">
			<?php
				//add spam blocking tags
				require_once('common/lib/form_spam_blocker/fsbb.php');
				$hidden_tags = get_hidden_tags();
				echo($hidden_tags);
			?>
			<table align="center" cellpadding="5" cellspacing="2">
				<tr>
					<td><span id="cpe" class="translate_me">Email address</span></td>
					<td><input type="text" name="email" id="email"></td>
				</tr>
				<tr>
					<td colspan=2><span id=cps class=translate_me><input type=submit value='Submit'></span></td>
				</tr>
			</table>
		</form>
		<?php
			}
		?>
    	<?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>