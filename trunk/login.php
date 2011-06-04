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
 * Log in page
 *
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
require_once('common/utils/Login.php');

Globals::dont_cache();
Globals::self_install_checks();

$error = false;
$user = Globals::get_param('user', $_POST);
if($user != null)
{
    require_once('common/lib/form_spam_blocker/fsbb.php');
    //check submission is by a human
    if(check_hidden_tags($_POST) == false)
    {
        $error = true;
    }
    else
    {
        $password = Globals::get_param('password', $_POST);
        $remember = Globals::get_param('remember', $_POST);
        if(!Login::log_in($user, $password, $remember))
        {
            $error = true;
        }
    }
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Log In</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>
	
	<body onLoad="document.getElementById('user').focus()">
	<br>
	   <?php
		if($error==true)
		{
	       echo(Message::get_error_display('<div style="text-align:center;">Log in failed<br />Is this your site?<br />You are trying to log into <strong>'.Load::general_settings(NEW_SITE_ID).'</strong></div>'));
       }
	   ?>
    <form method=post>
    <?php
        //add spam blocking tags
        require_once('common/lib/form_spam_blocker/fsbb.php');
        $hidden_tags = get_hidden_tags();
        echo($hidden_tags);
    ?>
    	<table align="center" cellpadding="5" cellspacing="2">
    	<tr>
	        <td colspan="2" align="center"><h3><span id=cpclil class=translate_me>Login to edit your site</span></h3></td>
	        </tr>
	        <tr>
	          <td><span id=cpce class=translate_me>Email</span></td>
	          <td><input type=text name=user id=user size="30"></td>
	        </tr>
	        <tr>
	          <td><span id=cpcp class=translate_me>Password</span></td>
	          <td><input type=password name=password  size="30"></td>
	        </tr>
			<tr>
	          <td><span id=cpcp111 class=translate_me style="font-size:smaller;">Remember me</span></td>
	          <td><input type="checkbox" name="remember"></td>
	        </tr>
	        <tr>
	          <td colspan=2 style="text-align:center;"><span id=cpclib class=translate_me><input type=submit value='  Log In  '></span></td>
	        </tr>
      	</table>
    </form>
    
    <p style="text-align:center;"><a href=forgot.php style="font-size:smaller"><span id=cpchtl class=translate_me>Having trouble logging in?</span></a></p>
    <br /><br /><br />
    <?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>