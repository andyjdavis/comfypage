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
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
require_once('common/menu.php');

Globals::dont_cache();

$error = null;
$from = null;

//will redirect if theyre not logged in
if(Login::logged_in(true) && $_POST)
{
	$message = null;
	foreach ($_POST as $var => $value)
	{
		$message .= "$var=\"$value\";\r\n";
	}

	$subject = 'ComfyPage survey';
	//$message = $_POST['message'];

	//require_once('common/validation.php');
	//$temp = IsValidEmail($from);
	//if(empty($temp) == false) $error = "$temp<br>";
	require_once('common/utils/Validate.php');
	$temp = Validate::required($message, 'Message');
	if(empty($temp) == false) $error .= "$temp<br>";

	require_once('common/lib/form_spam_blocker/fsbb.php');
	if(check_hidden_tags($_POST) == false)
	{
		$error = 'An error occurred. Please try again.';
	}

	if(empty($error) == true)
	{
		if(Globals::send_email_to_us($subject, $message))
		{
			$success = 'Thankyou for helping to improve ComfyPage<br /><a href="index.php">back to ComfyPage</a>';
			//track_user('feedback form sent', false);
			//protect against back or refresh
			//redirect("submit_survey.php?success=$success");
 		}
		else
		{
			$error = 'Sorry, the message failed to send. Please try again.';
			//track_user('Message from contact form failed', false);
		}
	}
}
else
{
	$success = Globals::get_param('success', $_GET);
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Feedback Form</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
	</head>

	<body>
		<?php
	    if(Login::logged_in(false))
	    {
	        echo(get_menu());
		}
		?>
			   <?php
					echo(Message::get_error_display($error));
					if(empty($success) == false)
					{
						echo(Message::get_success_display($success));
					}
					else
					{
			        //add spam blocking tags
			        require_once('common/lib/form_spam_blocker/fsbb.php');
			        $hidden_tags = get_hidden_tags();

		      	echo <<<END
<table width="100%"><tr><td>
<h1 align="center">Feedback Form</h1>
<p>Thank you for helping us improve ComfyPage.  Your feedback is incredibly important in making ComfyPage more user friendly and useful.</p>
<p>We welcome honest and critical feedback so please don&rsquo;t feel you have to be nice to us :)</p>
<form method="post">
$hidden_tags
Were there any parts of building your ComfyPage site that gave you trouble?<br />
<textarea name="anyTrouble" rows="10" cols="80"></textarea><br /><br />

Is there anything more you would like to be able to do with your site?<br />
<textarea name="wantMore" rows="10" cols="80"></textarea><br /><br />

Do you have any general comments or suggestions?<br />
<textarea name="generalComments" rows="10" cols="80"></textarea><br /><br />
    Thanks for taking the time to fill this out.  It helps us develop a better product for you and make your experience of ComfyPage even better. <br />
    <br />
    <center><input type="submit" value="Submit" /></center>
</form>
</td></tr></table>
END;
					}
					?>
    <?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>