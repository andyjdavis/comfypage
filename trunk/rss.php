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
 * Produces the site RSS feed
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');

define('FORCE_REGEN', false);

//code below class defs

class RSS
{
    var $title;
    var $link;
    var $description;
    var $language = "en-us";
    var $pubDate;
    var $items;
    var $tags;

    function RSS()
    {
            $this->items = array();
            $this->tags  = array();
    }

    function addItem($item)
    {
            $this->items[] = $item;
    }

    function setPubDate($when)
    {
            if(strtotime($when) == false)
                    $this->pubDate = date("D, d M Y H:i:s ", $when) . "GMT";
            else
                    $this->pubDate = date("D, d M Y H:i:s ", strtotime($when)) . "GMT";
    }

    function getPubDate()
    {
            if(empty($this->pubDate))
                    return date("D, d M Y H:i:s ") . "GMT";
            else
                    return $this->pubDate;
    }

    function addTag($tag, $value)
    {
            $this->tags[$tag] = $value;
    }

    function out()
    {
            $out  = $this->header();
            $out .= "<channel>\n";
            $out .= "<title>" . $this->title . "</title>\n";
            $out .= "<link>" . $this->link . "</link>\n";
            $out .= "<description>" . $this->description . "</description>\n";
            $out .= "<language>" . $this->language . "</language>\n";
            $out .= "<pubDate>" . $this->getPubDate() . "</pubDate>\n";
            $out .= '<atom:link href="http://'.$this->title.'/rss.php" rel="self" type="application/rss+xml" />';

            foreach($this->tags as $key => $val) $out .= "<$key>$val</$key>\n";
            foreach($this->items as $item) $out .= $item->out();

            $out .= "</channel>\n";

            $out .= $this->footer();

            $out = str_replace("&", "&amp;", $out);

            return $out;
    }

    function serve($contentType = "application/xml")
    {
            $xml = $this->out();
            header("Content-Type: $contentType");
            echo $xml;
    }

    function header()
    {
            $out  = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
            $out .= '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
            return $out;
    }

    function footer()
    {
            return '</rss>';
    }
}

class RSSItem
{
    var $title;
    var $link;
    var $description;
    var $pubDate;
    var $guid;
    var $tags;
    var $attachment;
    var $length;
    var $mimetype;

    function RSSItem()
    {
            $this->tags = array();
    }

    function setPubDate($when)
    {
            $t = strtotime($when);
            if($t == false) {
                    $this->pubDate = date('D, d M Y H:i:s ', $when) . "GMT";
            }
            else {
                    $this->pubDate = date("D, d M Y H:i:s ", $t) . "GMT";
            }
    }

    function getPubDate()
    {
            if(empty($this->pubDate))
                    return date("D, d M Y H:i:s ") . "GMT";
            else
                    return $this->pubDate;
    }

    function addTag($tag, $value)
    {
            $this->tags[$tag] = $value;
    }

    function out()
    {
            $out = "<item>\n";
            $out .= "<title>" . $this->title . "</title>\n";
            $out .= "<link>" . $this->link . "</link>\n";
            $out .= "<description>" . $this->description . "</description>\n";
            $out .= "<pubDate>" . $this->getPubDate() . "</pubDate>\n";

            if($this->attachment != "")
                    $out .= "<enclosure url='{$this->attachment}' length='{$this->length}' type='{$this->mimetype}' />";

            if(empty($this->guid)) $this->guid = $this->link;
            $out .= "<guid>" . $this->guid . "</guid>\n";

            foreach($this->tags as $key => $val) $out .= "<$key>$val</$key\n>";
            $out .= "</item>\n";
            return $out;
    }

    function enclosure($url, $mimetype, $length)
    {
            $this->attachment = $url;
            $this->mimetype   = $mimetype;
            $this->length     = $length;
    }
}

function CreateRSS($fp, $siteId)
{
    $feed = new RSS();
    $feed->title       = $siteId;
    $feed->link        = "http://$siteId";
    $feed->description = "pages from $siteId";

    require_once('common/utils/Load.php');
    $pages = Load::page_store()->load_users_pages();
    $pages[] = Load::page_store()->load_index_page();
    $page_html = null;
    $ms = Load::member_settings();
    for($i=0; $i<count($pages); $i++)
    {
            $is_private = $ms->is_members_only_page($pages[$i]->id);
            if(!$is_private)
            {
                    $item = new RSSItem();
                    $item->title = $pages[$i]->get(CONTENT_TITLE);
                    $item->link  = 'http://'.$siteId.'/'.$pages[$i]->id.'.htm';
                    $item->setPubDate($pages[$i]->get(LAST_MODIFIED));

                    $page_html = html_entity_decode(str_replace('&nbsp;', ' ', $pages[$i]->get(RAW_CONTENT)));
                    $page_html .= '<br /><a href="'.$item->link.'">Click here to read this on the web.</a>';

                    $item->description = "<![CDATA[ $page_html ]]>";
                    $feed->addItem($item);
            }
    }
    //$s = $feed->serve();
    $s = $feed->out();

    $fh = fopen($fp, 'w') or die("can't open robots file");
    fwrite($fh, $s);
    fclose($fh);
}

//this way we can return a different site name for site.comfypage.com and site.com
//feed (like sitemaps) contains absolute URLs so we need seperate ones for subdomains and domains
$siteId = $_SERVER['HTTP_HOST'];

$cacheDir = 'site/cache/';
$rssDir = $cacheDir.'rss/';
$fp = $rssDir.$siteId.'.xml';
if(FORCE_REGEN || !file_exists($fp))
{
    require_once('common/file.php');

    $file_admin = new FileAdmin();
    if(!$file_admin->folder_does_exist($cacheDir))
    {
            $file_admin->mkdir_r($cacheDir, 0744);
    }
    if(!$file_admin->folder_does_exist($rssDir))
    {
            $file_admin->mkdir_r($rssDir, 0744);
    }
    CreateRSS($fp, $siteId);
}

$handle = fopen($fp,'r');
$l = filesize($fp);
if($l>0)
{
    $s = fread($handle, $l);
}
fclose($handle);

header("Content-Type: application/xml");
echo($s);
?>