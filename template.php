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
 * Site template selection
 *
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
require_once('common/contentServer/template_control.php');

Globals::dont_cache();
Login::logged_in();

$permissions = Load::permission_settings();
$permissions->check_permission(TEMPLATE_ALLOWED, false);
$new_template_to_use = Globals::get_param('use', $_GET);

if($new_template_to_use != null)
{
	if(does_template_exist($new_template_to_use))
	{
	    select_template($new_template_to_use);
            $portal = Load::portal_settings();
            //if all subdomains should use my template
            if($portal->get(USE_MY_TEMPLATE))
            {
                //apply all my settings and especially my new template to the portal
                $portal->apply_my_settings_to_portal();
            }
	}
	else
	{
            //Selected a template that does not exist
	}
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<HTML>
<HEAD>
<TITLE>Templates</TITLE>
</HEAD>
<FRAMESET rows="253, *">
  <FRAME src="template_admin.php">
  <FRAME name="template_example" src="template_example.php">
</FRAMESET>
</HTML>