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
 * ComfyPage site map generator
 *
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
header ("Content-Type: text/xml");

define('FORCE_REGEN',false);
$s = '';

//this way we can return a different sitemap for site.comfypage.com and site.com
//sitemap contains absolute URLs so we need seperate maps
$siteId = $_SERVER['HTTP_HOST'];

$cacheDir = 'site/cache/';
$sitemapsDir = $cacheDir.'sitemaps/';
$fp = $sitemapsDir.$siteId.'.xml';

if(FORCE_REGEN || !file_exists($fp))
{
    require_once('common/utils/Globals.php');
    require_once('common/file.php');

    $file_admin = new FileAdmin();
    if(!$file_admin->folder_does_exist($cacheDir))
    {
        $file_admin->mkdir_r($cacheDir, 0744);
    }
    if(!$file_admin->folder_does_exist($sitemapsDir))
    {
        $file_admin->mkdir_r($sitemapsDir, 0744);
    }
    createSitemap($fp);
}

$handle = fopen($fp,'r');
$l = filesize($fp);
if($l>0)
{
    $s = fread($handle, $l);
}
fclose($handle);
echo($s);

function createSitemap($fp)
{
	//do it this way so absolute URLs will reflect the domain that was requested
	//could be either sitename.com or sitename.comfypage.com need both to work.
	$siteId = $_SERVER['HTTP_HOST'];

	$fh = fopen($fp, 'w') or die("can't open sitemap file");

	$s = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<urlset 
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9">
END;
	fwrite($fh, $s);
	
	$dFromFile = null;
	$ts = null;
	$dFormatted = null;
	$s = '';

	require_once('common/utils/Load.php');
	$pages = Load::page_store()->load_users_pages();
	$pages[] = Load::page_store()->load_index_page();
	if($pages)
	{
		$ms = Load::member_settings();
		foreach($pages as $page)
		{
			$is_private = $ms->is_members_only_page($page->id);
			if(//$page_id!='HEADER'
			//	&& $page_id!='FOOTER'
			//	&& $page_id!='LEFT_MARGIN'
			//	&& $page_id!='RIGHT_MARGIN'
			//	&& $page_id!='ERROR'
				!$is_private)
			{
				$dFromFile = $page->get(LAST_MODIFIED);
				$ts = strtotime($dFromFile);
				$dFormatted = date("Y-m-d", $ts);
				$s .= <<<END
<url>
      <loc>http://$siteId/index.php?content_id={$page->id}</loc>
      <lastmod>$dFormatted</lastmod>
      <changefreq>weekly</changefreq>
</url>
END;
			}
		}
	}
	else
	{
		//site has no pages yet.  Add login.php to avoid an invalid xml document
		$dFormatted = date("Y-m-d");
		$s .= <<<END
<url>
      <loc>http://$siteId/login.php</loc>
      <lastmod>$dFormatted</lastmod>
      <changefreq>weekly</changefreq>
</url>
END;
	}
	
	$s .= '</urlset>';
	fwrite($fh, $s);
	fclose($fh);
}
?>
