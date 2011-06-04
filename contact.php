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

Globals::dont_cache();
Login::logged_in();

$contact_enabled = true;

$error = null;
$from = null;
$message = null;

if(Login::logged_in(false))
{
    $from = Load::general_settings(ADMIN_EMAIL);
}

if($contact_enabled == false && $_POST)
{
    $error = "You must upgrade to contact the creators of ComfyPage";
}
else if($_POST)
{
    require_once('common/lib/form_spam_blocker/fsbb.php');
    if(check_hidden_tags($_POST) == false)
    {
        $error = 'An error occurred. Please try again.';
    }

    require_once('common/utils/Validate.php');

    $from = Globals::get_param('email', $_POST);
    $message = Globals::get_param('message', $_POST);
    $feeling = Globals::get_param('feeling', $_POST);

    $temp = Validate::email($from);
    if(empty($temp) == false) $error = "$temp<br>";

    $temp = Validate::required($message, 'Message');
    if(empty($temp) == false) $error .= "$temp<br>";

    if(empty($error) == true)
    {
        $subject = 'ComfyPage support request from NOT logged in user';
        if(Login::logged_in(false))
        {
            $subject = 'ComfyPage support request from logged in user ('.Load::general_settings(NEW_SITE_ID).')';
        }

        $message_to_send = "I am feeling - $feeling. \r\n$message";
        if(Globals::send_email_to_us($subject, $message_to_send, $from))
        {
            $success = 'Your message has been sent';
            //protect against back or refresh
            Globals::redirect("contact.php?success=$success");
        }
        else
        {
            $error = 'Sorry, the message failed to send. Please try again.';
        }
    }
}
$success = Globals::get_param('success', $_GET);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Contact Support</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>
	<body>
            <?php
	    if(Login::logged_in(false))
	    {
                echo(get_menu('Contact a human'));
            }
            ?>
			<form target=_blank action="http://www.google.com/search" method="get" style="text-align:center;margin: 0pt; padding: 0pt;">
			<table align="center" class="admin_table" border="0" style="text-align:center;">
				<tr><td>
            	<h1><span id="needHelp" class="translate_me">Need help?</span></h1>
				<p>First, see if you can find answers on help.comfypage.com</p>
				<p><input type="text" value="" maxlength="255" size="60" name="q" /> <span id="searchHelp" class="translate_me"><input type="submit" value="Search Help" /></span> <input type="hidden" value="help.comfypage.com" name="sitesearch" /></p>
            </form>
			   <?php
					echo(Message::get_error_display($error));
					if(empty($success) == false)
					{
						echo(Message::get_success_display($success));
					}
					else
					{
						echo('An error occurred.');
					}
				?>
			    <form method="post" style="padding:o;margin:0;">
			    <?php
			        //add spam blocking tags
			        require_once('common/lib/form_spam_blocker/fsbb.php');
			        $hidden_tags = get_hidden_tags();
			        echo($hidden_tags);
			    ?>
		    		<table align=center cellpadding=5 cellspacing=2>
			        <tr>
			          <td><span id="yourEmailAddress" class="translate_me">Your email address</span></td>
			          <td><input type=text name=email id=email size=40 value="<?php echo($from); ?>"></td>
			        </tr>
					<tr>
			          <td><span id="feeling" class="translate_me">How are you feeling?</span></td>
			          <td>
						<?php
						//todo include emoticons in select http://technology.amis.nl/blog/994/html-select-item-with-icons-in-addition-to-just-text-labels-applying-the-css-background-style-to-the-html-option-element
						?>
						<select name="feeling" id="feeling">
							<option value="dont know">don't know</option>
							<option value="happy">happy</option>
							<option value="confused">confused</option>
							<option value="angry">angry</option>
							<option value="disappointed">disappointed</option>
						</select>
						</td>
			        </tr>
			        <tr>
			          <td valign=top><span id="m" class="translate_me">Message</span></td>
			          <td><textarea name=message rows=7 cols=60><?php echo($message); ?></textarea></td>
			        </tr>
			        <tr>
			          <td></td><td style="text-align:center;"><span id="sendM" class="translate_me"><input type=submit value=' Send Message '></span> <?php //echo($credit_message); ?></td>
			        </tr>
					<tr>
			          <td></td><td style="text-align:center;"><span id="back" class="translate_me">
					  <?php
					  if( !Login::logged_in(false))
						{
							echo '<p><a href="/"><span id="ret" class="translate_me">or return to site</span></a></p>';
						}
					  ?>
					  </span></td>
			        </tr>
		      	</table>
		      	<p style='text-align:center;margin-bottom:0;'><span id="gotFeed" class="translate_me"><b>Got feedback?</b> Use the <a href=submit_survey.php>feedback form</a></span>.</p>
				</td></tr>
			</table>
    			</form>
    <?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>
