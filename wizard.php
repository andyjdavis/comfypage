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
 * Site creation wizard
 *
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
require_once('common/utils/Load.php');
$gs = Load::general_settings();
Globals::dont_cache();

$defaultPage = 1;
$page = Globals::get_param('page', $_GET);

$wizard = $gs->get(NEED_DO_WIZARD);

if($wizard && $wizard!='default')
{
    $path = 'common/contentServer/wizard/' . $wizard . '/' . $page . '.php';
}
else
{
    $path = 'common/contentServer/wizard/' . $page . '.php';
}

if(!file_exists($path))
{
    Globals::redirect('wizard.php?page=' . $defaultPage);
}

require($path);
?>