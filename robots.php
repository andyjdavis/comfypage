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
 * Provides robots.txt Use apache mod_rewrite to use this script to service requests for robots.txt
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
header ("Content-Type: text/plain");

define('FORCE_REGEN',false);

function createRobots($fp, $siteId)
{
	if(!file_exists($fp))
	{
		require_once('common/utils/Globals.php');

		$fh = fopen($fp, 'w') or die("can't open robots file");

		$s = <<<END
User-agent: *
Disallow:
Sitemap: http://$siteId/map.php
END;
		fwrite($fh, $s);
		fclose($fh);
	}
}

//this way we can return a different site name for site.comfypage.com and site.com
//robots.txt contains absolute URLs so we need seperate ones for subdomains and domains
$siteId = $_SERVER['HTTP_HOST'];

$cacheDir = 'site/cache/';
$robotsDir = $cacheDir.'robots/';
$fp = $robotsDir.$siteId.'.txt';

if(FORCE_REGEN || !file_exists($fp))
{
	require_once('common/utils/Globals.php');
	require_once('common/file.php');

	$file_admin = new FileAdmin();
	if(!$file_admin->folder_does_exist($cacheDir))
	{
		$file_admin->mkdir_r($cacheDir, 0744);
	}
	if(!$file_admin->folder_does_exist($robotsDir))
	{
		$file_admin->mkdir_r($robotsDir, 0744);
	}
	createRobots($fp, $siteId);
}

$handle = fopen($fp,'r');
$l = filesize($fp);
if($l>0)
{
	$s = fread($handle, $l);
}
fclose($handle);
echo($s);

?>