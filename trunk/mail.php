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
include_once('common/menu.php');

Globals::dont_cache();
Login::logged_in();

$error = null;
$success = null;

//should this be a config flag so portals can turn it off?
$mailing_list_enabled = true;

$properly_configured = false;
$selected_list = null;
$mailing_lists = get_mailing_lists();
$message = null;
$subject = null;
$only_one_list = false;

$selected_list = Globals::get_param('list', $_GET);
//if POST list is set use that otherwise stick with GET list
$selected_list = Globals::get_param('list', $_POST, $selected_list);

//if no list selected and there is only one list
if(count($mailing_lists) == 1)
{
    //auto select the single list
    $selected_list = $mailing_lists[0];
    $only_one_list = true;
}

if($selected_list != null)
{
    if(Addon::exists($selected_list))
    {
        $mailingDoodad = Load::addon($selected_list);
        //$list_settings = get_doodad_settings($selected_list);
        $subject = $mailingDoodad->get(EMAIL_SUBJECT);
        $from_email = $mailingDoodad->get(FROM_EMAIL_ADDRESS);
        $message = $mailingDoodad->get(MESSAGE_TRAILER);
        if(empty($message) == false) $message = "\n\n\n$message";
        $address_list = $mailingDoodad->get_addresses_from_single_string($mailingDoodad->get(LIST_OF_EMAILS));
        //$validation_errors = $mailingDoodad->validate_doodad_settings($list_settings);
        $properly_configured = empty($validation_errors);
        if($properly_configured == false)
        {
            $error = <<<END
<p>Selected mailing list is not configured</p>
<p><a href="function.php?function=$selected_list">Configure the list</a></p>
END;
            if(count($mailing_lists) > 1)
            {
                $error .= '<p><a href=mail.php>Select another list</a></p>';
            }
        }
    }
    else
    {
        //not an allowed mailing list so reset it
        $selected_list = null;
    }
}

if(isset($_POST['ready']))
{
    if($mailing_list_enabled == false)
    {
        $error = 'You must upgrade to use the mailing list. <a href="service_levels.php">Upgrade now.</a>';
    }
    else
    {
	//now sending emails within <body>
    }
}
$success = Globals::get_param('success', $_GET);
$buy_list = Globals::get_param('buy_list', $_GET);
if($buy_list != null)
{
	require_once('common/credit.php');
	$credit_control->user_wants_a(MAILING_LIST);
}
function get_mailing_lists()
{
    $mailing_lists = array();
    $doodad_list = Addon::get_function_keys();
    $list_key = 'Mailing List';
    foreach($doodad_list as $name)
    {
		if(strstr($name, $list_key))
		{
            $mailing_lists[] = $name;
		}
	}
	return $mailing_lists;
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Mailing list</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>
	<body>
	<?php
	if(isset($_POST['ready']))
	{
	    if($mailing_list_enabled != false) {
		set_time_limit(900);

		// Start output buffering
		ob_start();

		$message = Globals::get_param('message', $_POST);
		//decode because quotes and other things were being turned into html sequences like '&quot;'
		$message = htmlspecialchars_decode($message);
		$message = stripslashes($message);

		$subject = Globals::get_param('subject', $_POST);
		$subject = htmlspecialchars_decode($subject);
		$subject = stripslashes($subject);

		$i = 0;
		foreach($address_list as $address) {
		    Globals::send_email($address, $from_email, $subject, $message);
		    $i++;

		    if ($i%10==0) {
			echo "<p>&nbsp;$i emails sent</p>";
			flush();
			ob_flush();
		    }
		}
		//echo '<a href="mail.php?success=Emails sent">Emails sent. Click here to continue</a>';
		echo '<p>&nbsp;</p>&nbsp;<a href="mail.php">Emails sent. Click here to continue</a>';
		echo '</body></html>';
		exit();
		//Globals::redirect('mail.php?success=Emails sent');
	    }
	}
	//echo(Message::get_help_link(9918447) . '&nbsp;&nbsp;&nbsp;');
	$menu = new Menu;
	//if only one list available and there is a list currently selected
	if($only_one_list == false && empty($selected_list) == false)
	{
	    //display link to choose a different list
		//echo('<a href=mail.php>Select another list</a>&nbsp;&nbsp;&nbsp;');
		$menu->add_item('Select another list', 'mail.php');
	}
	echo($menu->get_menu());

	echo(Message::get_error_display($error));
	echo(Message::get_success_display($success));

			    //none selected
			    if(empty($selected_list))
			    {
			    ?>
	    			<!-- LIST of mailing lists -->
		        <table class=admin_table align="center">
		        	<tr>
        				<td>
        					<p><span id=sml class=transelate_me>Select a mailing list to send email to</span></p>
        					<?php
					        	foreach($mailing_lists as $list)
					        	{
											echo("<a href='mail.php?list=$list'>$list</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
										}
        					?>
								</td>
        			</tr>
        		</table>
					<?php
					}   //end none selected
	    		?>
			    <?php
			    if($properly_configured)
			    {
	    		?>
	    			<!-- SELECTED LIST -->
		        <table class=admin_table align="center">
		        <tr>
		        	<td class="admin_section"><b><?php echo($selected_list); ?></b> <span id="sel" class=translate_me>selected</span>&nbsp;&nbsp;&nbsp;&nbsp;<a href="function.php?function=<?php echo($selected_list); ?>"><span id="conf" class=translate_me>Configure this list</span></a></td>
		        	<td class="admin_section"><span id=lm class=translate_me>List membership: <b><?php echo(count($address_list)); ?></b></span></td>
		        </tr>
		        </table>

		        <!-- MESSAGE -->
		        <form method=post style="padding:0;margin:0;">
		        <input type=hidden value="<?php echo($selected_list); ?>" name=list>
		        <table class=admin_table align="center">
		        <tr>
		        	<td><span id="fr" class=translate_me>From</span></td>
		        	<td><?php echo($from_email); ?></td>
		        </tr>
		        <tr>
		        	<td><span id="sub" class=translate_me>Subject</span></td>
		        	<td><input type=text size=40 value="<?php echo($subject); ?>" name=subject></td>
		        </tr>
		        <tr>
		        	<td valign=top><span id="mes" class=translate_me>Message</span></td>
		        	<td><textarea rows=15 cols=100 name=message><?php echo($message); ?></textarea>
		        	<?php 
		        		//require_once('common/contentServer/content_page.php');
		        		//echo(get_editor_html('message', $message));
		        	?></td>
		        </tr>
		        <tr>
		        	<td colspan=2><input type=submit value=Send name=ready></td>
		        </tr>
		        </table>
		        </form>
	        <?php
	        }   //end properly configured
	        else
	        {
						echo('<br>');
					}
        	?>
        
        	<table class=admin_table align="center">
				    <tr>
				        <th colspan=3>Instructions</th>
				    </tr>
				    <tr>
	        		<td>
								<p>A mailing list lets you send a mass email to your visitors. There are two steps for using a mailing list.</p>
								<ol>
									<li>
										<p>Collect email addresses</p>
										<ul>
										<li><p>Use the mailing list add-ons to allow visitor's to subscribe to your list</p></li>
										<li><p>Use the mailing list add-on to manually enter email addresses</p></li>
										</ul>
									</li>
				
									<li>
										<p>Send messages to everyone on the list using this page</p>
									</li>
								</ol>
							</td>
	    			</tr>
					</table>
	  <?php echo(Globals::get_affinity_footer()); ?>  
	</body>
</html>