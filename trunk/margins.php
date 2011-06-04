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
 * Edit the site margins
 *
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}
require_once('common/utils/Globals.php');
require_once('common/contentServer/content_page.php');
require_once('common/users_items/Page.php');
Globals::dont_cache();
Login::logged_in();
define('EDIT_TARGET', 'edit');
$editTarget = Globals::get_param(EDIT_TARGET, $_POST);
if($editTarget)
{
    Load::award_settings()->bestow_award(EDIT_BORDER_AWARD);
    $setting_names = array(RAW_CONTENT, CONTENT_EMBED); //what are we saving
    switch($editTarget)
    {
        case LEFT_MARGIN :
        {
            $page = Load::page(LEFT_MARGIN);
            $page->process_post($_POST, $setting_names);
            break;
        }
        case RIGHT_MARGIN :
        {
            $page = Load::page(RIGHT_MARGIN);
            $page->process_post($_POST, $setting_names);
            break;
        }
        case HEADER :
        {
            $page = Load::page(HEADER);
            $page->process_post($_POST, $setting_names);
            break;
        }
        case FOOTER :
        {
            $page = Load::page(FOOTER);
            $page->process_post($_POST, $setting_names);
            break;
        }
    }
}
$addon = Globals::get_param('addon', $_POST);
if($addon)
{
    $setting_names = array(CONTENT_DOODAD);
    $page = Load::page(LEFT_MARGIN);
    $page->process_post($_POST, $setting_names);
    $page = Load::page(RIGHT_MARGIN);
    $page->process_post($_POST, $setting_names);
    $page = Load::page(HEADER);
    $page->process_post($_POST, $setting_names);
    $page = Load::page(FOOTER);
    $page->process_post($_POST, $setting_names);
}
$editTarget = Globals::get_param(EDIT_TARGET, $_GET, -1);
$page = get_margin_edit_page($editTarget);
echo($page);
?>