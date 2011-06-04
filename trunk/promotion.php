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
//require_once('common/credit.php');
include_once('common/menu.php');

//Globals::dont_cache();
//track_user(null, false);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
	<title>Promotion</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
	<?php
		echo(Message::get_language_JS_block());
	?>
	<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
    </head>
    <body>
    <?php
        if(Login::logged_in(false)) {
	    echo(get_menu());
	}
    ?>
    <table class="admin_table" align=center>
	<tr>
		<th colspan=3><span id="promotingHeader" class="translate_me">Promoting Your ComfyPage</span></th>
	</tr>
	<tr>
	    <td colspan=3><p><span id="theintro" class="translate_me">These resources will help you get more people to your site.</span></p></td>
	</tr>
    </table>
    <table class="admin_table" align=center>
	<tr>
	  <th colspan=3><span id="sn1" class="translate_me">Social News Sites</span></td>
	</tr>
	<tr>
	  <td colspan=3><p><span id="sn2" class="translate_me">Social news sites let you submit interesting pages. Submitting pages brings people to your site and can improve search engine rankings.</span></p></td>
	</tr>
	<tr>
	  <td style="text-align:center;"><p><a href="http://digg.com" target="_blank">Digg.com</a></p></td>
	  <td style="text-align:center;"><p><a href="http://propeller.com" target="_blank">Propeller.com</a></p></td>
	  <td style="text-align:center;"><p><a href="http://reddit.com" target="_blank">Reddit.com</a></p></td>
	</tr>
    </table>
	<!--<tr>
	    <th colspan=3><span id="f1" class="translate_me">Follow the Conversation</span></th>
	</tr>
	<tr>
	    <td colspan=3><p><span id="f2" class="translate_me"><i>What people are saying about us</i> is a handy free tool to keep track of what is being said about you on the Internet.</span></p></td>
	</tr>
	<tr><td colspan=3 style="text-align:center;"><p><a href="http://whatpeoplearesayingaboutus.com" target=_blank>What people are saying about us</a></p></td></tr>
 -->
    <table class="admin_table" align="center">
	<tr>
	    <th colspan=3><span id="sn3" class="translate_me">Search Engines</span></th>
	</tr>
	<tr>
	    <td colspan=3><p><span id="sn4" class="translate_me">Search engines will find your site by following links to it from other sites (like the social news sites above). You can also specifically ask to be indexed.</span></p></td>
	</tr>
	<tr>
	    <td style="text-align:center;width:50%;"><p><a href="http://www.google.com/addurl/" target="_blank">Google</a></p></td>
	    <td style="width:0%;">&nbsp;</td>
	    <td style="text-align:center;width:50%;"><p><a href="http://search.live.com/docs/submit.aspx" target="_blank">Live Search</a></p></td>
	</tr>
    </table>
    <?php echo(Globals::get_affinity_footer(false)); ?>
    </body>
</html>
