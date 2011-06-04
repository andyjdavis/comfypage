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
require_once('common/contentServer/content_page.php');
//require_once('common/general_settings.php');

Globals::dont_cache();
Login::logged_in();
//site_enabled_check();

//require_once('common/permissions.php');
$permissions = Load::permission_settings();
$permissions->check_permission(TEMPLATE_ALLOWED, false);

$default_tmeplate = Load::general_settings(TEMPLATE_IN_USE);
$template_to_show = Globals::get_param('TEMPLATE', $_GET, $default_tmeplate);
//if(isset($_GET['TEMPLATE']))
if($template_to_show != null)
{
	require_once('common/contentServer/template_control.php');
	if(does_template_exist($template_to_show))
	{
    	//track_user('Template previewed');
    }
}

$page = get_content_page(INDEX, $template_to_show, true);
echo($page);
exit();

?>