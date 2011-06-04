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
 * Member's area (password protected pages) login page
 *
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');

Globals::dont_cache();
Globals::self_install_checks();

$error = false;
$password = Globals::get_param('password', $_POST);
if($password != null)
{
    require_once('common/lib/form_spam_blocker/fsbb.php');
    //check submission is by a human
    if(check_hidden_tags($_POST) == false)
    {
        $error = 'Please try again';
    }
    else
    {
        $members = Load::member_settings();
        if($members->log_in($password) == false)
        {
            $error = "<p>Log in failed</p><p>Password is case sensitive</p>";
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
	
	<body onLoad="document.getElementById('password').focus()">
	<br>
	   <?php
	       echo(Message::get_error_display($error));
	   ?>
    <form method=post>
    <?php
        //add spam blocking tags
        require_once('common/lib/form_spam_blocker/fsbb.php');
        $hidden_tags = get_hidden_tags();
        echo($hidden_tags);
    ?>
    	<table align=center cellpadding=5 cellspacing=2>
    	<tr>
	        <td colspan=2 align=center><h3><span id=member_area_login class=translate_me>Member's area log-in</span></h3></td>
	        </tr>
	        <tr>
	          <td><span id=member_password class=translate_me>Password</span></td>
	          <td><input type=password name=password id=password></td>
	        </tr>
	        <tr>
	          <td colspan=2 style="text-align:center;"><input type=submit value='  Log In  '></td>
	        </tr>
      	</table>
    </form>
	<div style="text-align:center;font-size:smaller;">
		<a href=admin.php><span id=member_login_site_manager class=translate_me>Site manager</span></a>&nbsp;&nbsp;&nbsp;
		<a href=http://www.comfypage.com/>Get a free ComfyPage website</a>
	</div>
	</body>
</html>