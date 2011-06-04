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
require_once('common/menu.php');
require_once('common/contentServer/content_page.php');
require_once('common/controllers/controller_seo.php');

Globals::dont_cache();
//Login::logged_in();

define('CHECK_RANK_PARAM','cr');

$contentId = null;
$content = null;
$error = null;

$c = new controller_seo();

$ps = Load::page_store();

$contentId = Globals::get_param(CONTENT_ID_URL_PARAM, $_POST);
$checkingRank = Globals::get_param(CHECK_RANK_PARAM, $_POST);

$keyphrase = Globals::get_param(CONTENT_SEO_TARGET, $_POST);

if($contentId)
{
    $content = Load::page($contentId);
    $return_url = 'seo.php?'.CONTENT_ID_URL_PARAM.'='.$contentId;

    if (empty($keyphrase)) {
        $keyphrase = $content->get(CONTENT_SEO_TARGET);
    } else {
        $content->set(CONTENT_SEO_TARGET, $keyphrase);
    }

    if($checkingRank) {
	if( !empty($keyphrase) )  {
	    $error = null;
	    if( $c->can_query_google($keyphrase) ) {
		$rank = $c->get_keyphrase_rank($keyphrase, Load::general_settings(NEW_SITE_ID), SEO_MAX_RESULTS);
	    }
	    else {
		$error = "Google takes time to update its indexes. Check again in a few days.<br /><a href='$return_url'>Continue...</a>";
	    }
	} else {
	    $error = "Enter a key phrase<br /><a href='$return_url'>Continue...</a>";
	}
    }

    if( !$error ) {
	Globals::redirect($return_url);
    }
}
else {
    $contentId = Globals::get_param(CONTENT_ID_URL_PARAM, $_GET);
    $content = Load::page($contentId);

    //set a sensible default
    if( !$content->get(CONTENT_SEO_TARGET)) {
	$content->set(CONTENT_SEO_TARGET, $content->get(CONTENT_TITLE));
    }
}

if(empty($contentId)) {
    $contentId = 'INDEX';
}

$user_pages = $ps->load_users_pages();
$index_page = $ps->load_index_page();

$page_selector = HtmlInput::get_page_selector($contentId,$user_pages,$index_page);

$targetedkeyphrasecontent ='<tr><td colspan="4">Choose a phrase that someone looking for this page may type into Google. A key phrase is two or more key words.  <a target="_blank" href="https://adwords.google.com/select/KeywordToolExternal">Help choosing keywords</a></td></tr>
<tr><td colspan="4"><form method="POST">'.HtmlInput::get_hidden_input(CONTENT_ID_URL_PARAM, $content->id).HtmlInput::get_text_input(CONTENT_SEO_TARGET, $content->get(CONTENT_SEO_TARGET)).' <input type="submit" value="Save" /></form></td></tr>';

$keyphraserankcontent = '<tr><td colspan="4"><form method="POST">'.HtmlInput::get_hidden_input(CONTENT_ID_URL_PARAM, $content->id).HtmlInput::get_hidden_input(CHECK_RANK_PARAM, 1).'<input type="submit" value="Check Rank" /></form></td></tr>';

$seotarget = $content->get(CONTENT_SEO_TARGET);
if( file_exists("site/store/searchranks/{$seotarget}.php") ) {
    list($tablehtml,$ranks) = $c->get_keyword_rank_table($content->get(CONTENT_SEO_TARGET));
    if( !empty($ranks) ) {
	$keyphraserankcontent .= '<tr><td colspan="3">'.$tablehtml.'</td><td><div id="chart_div"></div></td></tr>';
    }
    else {
	$keyphraserankcontent .= '<tr><td colspan="3">'.Load::general_settings(NEW_SITE_ID).' is not in the top '.SEO_MAX_RESULTS.' for "{$content->get(CONTENT_SEO_TARGET)}"</td><td><div id="chart_div"></div></td></tr>';
    }
}

$keyphraseusecontent = '';
$keyworddensitycontent = '';
$upgrademsg = '';

//construct keyphrase use content
    $cross = 'common/images/cross.png';
    $tick = 'common/images/tick.gif';

    //is the keyphrase in a heading?
    $in_heading = null;
    $pattern = '#<h.>'.$content->get(CONTENT_SEO_TARGET).'<\/h.>#i';
    if(preg_match($pattern,$content->get(RAW_CONTENT))) {
	$in_heading = $tick;
    }
    else {
	$in_heading = $cross;
    }
    //is the keyphrase in the page title?
    $in_page_title = null;
    $pattern = '/'.$content->get(CONTENT_SEO_TARGET).'/i';
    if(preg_match($pattern,$content->get(CONTENT_TITLE))) {
	$in_page_title = $tick;
    }
    else {
	$in_page_title = $cross;
    }
    $keyphraseusecontent = <<<END
<tr><td colspan="4"><p>Your key phrase should appear in these places:</p></td></tr>
<tr><td style="width:25%;text-align:center;"><p>In the page title</p></td><td colspan="3" style="text-align:left;"><img src="$in_page_title"></td></tr>
<tr><td style="text-align:center;"><p>In a heading</p></td><td colspan="3" style="text-align:left;"><img src="$in_heading"></td></tr>
END;

    //construct keyword density content
    //
    //get page content with no toolbar
    $hide_menu = true;
    $s = get_content_page($contentId, null, $hide_menu);
    list($word_count,$sorted_array) = $c->get_sorted_array_of_words($s);
    $keyworddensitycontent .= '<tr><td colspan="4">How often a word appears alters what Google thinks the page is about. Aim to have your key words appear between 1% and 5%.</td></tr>';

    $advice = null;
    if($word_count==0 || ($sorted_array && count($sorted_array)>0 && round( ($sorted_array[key($sorted_array)]/$word_count)*100 ,1)<1) )
    {
	    $advice = Message::get_message_display('No words have a frequency greater than 1%<br />Ensure keywords appear several times to help Google decide what this page is about.');
    }

    $keyworddensitycontent .= <<<END
<tr><td colspan="4">Total words:$word_count</td></tr>
<tr><td colspan="4" style="padding:10px;text-align:center;">$advice</td></tr>
<tr><td><b>word</b></span></td><td><b>occurrences</b></td><td><b>frequency %</b></td><td><b>advice</b></td></tr>
END;

$perc = 0;
$style = null;
$advice = null;
foreach($sorted_array as $k=>$v)
{
    $perc = round( ($v/$word_count)*100 ,1);
    if($perc>5)
    {
	$style='color:red;';
	$advice = 'Keyword too frequent';
    }
    else if($perc>1 && $perc<2)
    {
	$style='color:green;';
	$advice = 'Keyword in ideal range';
    }
    else
    {
	$style = '';
	$advice = null;
    }
    $keyworddensitycontent .= '<tr style="'.$style.'"><td><span style="">'.$k.'</span></td><td><span style="">'.$v.'</span></td><td><span style="">'.$perc.'%</span></td><td>'.$advice.'</td></tr>';
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>ComfyPage Site SEO</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" >

		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
		<body>
		<?php
		echo(get_menu());
		if( $error) {
		    echo(Message::get_error_display($error));
		    echo '</body></html>';
		    exit();
		}
		else {
		?>
		    <table align="center" class="admin_table">
			<tr>
			<th colspan=2><span id="seoTitle" class="translate_me">Search Engine Optimization</span></th>
			</tr>
			<tr><td><span id="InsSpan" class="translate_me">By making some simple adjustments to your pages you can increase your chances of ranking well in Google's search results.</span></td></tr>
			<tr><td><?php echo($page_selector); ?></td></tr>
		    </table>
		    <?php
			echo $upgrademsg;
		    ?>
		    <table align="center" class="admin_table">
		    <tr><th colspan="4"><span id="KeywordTitleSpan" class="translate_me">Targeted Key Phrase</span></th></tr>
			<?
			echo $targetedkeyphrasecontent;
			?>
		    </table>

                    <table align="center" class="admin_table">
		    <tr><th colspan="4"><span id="kwrp" class="translate_me">Key Phrase Rank</span></th></tr>
			<?
			echo $keyphraserankcontent;
			?>
		    </table>
		   
		    <table align="center" class="admin_table">
		    <tr><th colspan="4"><span id="kpfi" class="translate_me">Key Phrase Use</span></th></tr>
			<?php
			echo $keyphraseusecontent;
			?>

		    </table>
		    <table align="center" class="admin_table">
		    <tr><th colspan="4"><span id="KeywordTitleSpan" class="translate_me">Keyword Density</span></th></tr>
			<?php
			echo $keyworddensitycontent;
			?>
		    </table>

		    <table align="center" class="admin_table">
		    <tr><td><?php echo($page_selector); ?></td></tr>
		    </table>
		    <script type="text/javascript">
		    //$('#keywordtable').visualize({type: 'line', 'parseDirection':'x'});
		    </script>
		    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
		    <script type="text/javascript">
		    google.load("visualization", "1", {packages:["imagesparkline"]});
		    google.setOnLoadCallback(drawChart);

		    function drawChart() {
		    var data = new google.visualization.DataTable();
		    <?php
		    $keyphrase = $content->get(CONTENT_SEO_TARGET);
		    echo "data.addColumn('number', '$keyphrase');\r\n";

		    $l = sizeof($ranks);
		    if($l && $l>0) {
			echo "data.addRows($l);\r\n";
			$r = null;
			for($i=0; $i<$l; $i++) {
			    if($ranks[$i]!=-1) {
				$r = 101-$ranks[$i];
			    }
			    else {
				$r = 0;
			    }
			    echo "data.setValue($i,0,$r);\r\n";
			}
			echo <<<END
var chart = new google.visualization.ImageSparkLine(document.getElementById('chart_div'));
chart.draw(data, {width: '600', height: '150', showAxisLines: false,  showValueLabels: false, labelPosition: 'left'});
END;
		    }
		} //end if $error
?>
</script>
</body>
</html>