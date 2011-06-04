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
 * ComfyPage frontpage
 *

 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
require_once('common/contentServer/style.php');
include_once('common/menu.php');

Globals::dont_cache();
Login::logged_in();
//site_enabled_check();
//track_user();

//require_once('common/permissions.php');
$permissions = Load::permission_settings();
$permissions->check_permission(STYLE_ALLOWED, false);

//tags that present in the format menu of the editor
$used_tags = array('h1', 'h2', 'h3');
//special case tag
define('BODY', 'body');
define('P', 'p');
define('A', 'a');

define('DELIMIT', '_');

define('COLOR', 'color');
define('FONT_FAMILY', 'font-family');
define('FONT_SIZE', 'font-size');
define('WIDTH', 'width');
define('BG_COLOR', 'background' . DELIMIT . 'color');
define('MARGIN', 'margin');

$saved = null;

if(count($_POST) > 0)
{
	$properties = array(COLOR);
	$stylesToSave = array();
	$style = null;

	foreach($used_tags as $tag)
	{
		foreach($properties as $property)
		{
			//$stylesToSave[$tag][$property] = $_POST[$tag . DELIMIT . $property];
			$stylesToSave[$tag][$property] = Globals::get_param($tag . DELIMIT . $property, $_POST);
		}
	}

	//body tag
	$properties = array(COLOR, FONT_FAMILY/*, BG_COLOR*/, MARGIN);

	foreach($properties as $property)
	{
		//$stylesToSave[BODY][$property] = $_POST[BODY . DELIMIT . $property];
		$stylesToSave[BODY][$property] = Globals::get_param(BODY . DELIMIT . $property, $_POST);
	}

	//$stylesToSave[BODY]['background-color'] = $_POST['body' . DELIMIT . BG_COLOR];
	$stylesToSave[BODY]['background-color'] = Globals::get_param('body' . DELIMIT . BG_COLOR, $_POST);

	//$stylesToSave[BODY]['background-color'] = $_POST['tdfooter' . DELIMIT . BG_COLOR];

	//p tag
	$properties = array(FONT_SIZE);

	foreach($properties as $property)
	{
		//$stylesToSave[P][$property] = $_POST[P . DELIMIT . $property];
		$stylesToSave[P][$property] = Globals::get_param(P . DELIMIT . $property, $_POST);
	}

	//'a' tag
	$properties = array(COLOR);

	foreach($properties as $property)
	{
		//$stylesToSave[A][$property] = $_POST[A . DELIMIT . $property];
		$stylesToSave[A][$property] = Globals::get_param(A . DELIMIT . $property, $_POST);
	}

	//$properties = array(BG_COLOR);
	$td_tags = array('header', 'leftMargin', 'rightMargin', 'footer', 'centre');

	foreach($td_tags as $td_tag)
	{
	    //$stylesToSave['td' . '.' . $td_tag]['background-color'] = $_POST['td' . $td_tag . DELIMIT . BG_COLOR];
	    $stylesToSave['td' . '.' . $td_tag]['background-color'] = Globals::get_param('td' . $td_tag . DELIMIT . BG_COLOR, $_POST);
	}

	//$stylesToSave['td.leftMargin'][WIDTH] = $_POST['tdleftMargin' . DELIMIT . WIDTH];
	$stylesToSave['td.leftMargin'][WIDTH] = Globals::get_param('tdleftMargin' . DELIMIT . WIDTH, $_POST);
	//$stylesToSave['td.rightMargin'][WIDTH] = $_POST['tdrightMargin' . DELIMIT . WIDTH];
	$stylesToSave['td.rightMargin'][WIDTH] = Globals::get_param('tdrightMargin' . DELIMIT . WIDTH, $_POST);

	saveSheet('site/site.css', $stylesToSave);
//	delete_all_pages_from_cache();
	$saved = 'Saved';
	//track_user('Saved new style settings');
	Load::award_settings()->bestow_award(EDIT_STYLES_AWARD);
}

$styles = loadSheet('site/site.css');

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Site Style Editor</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
		<!--<link href="site/site.css" rel="stylesheet" type="text/css" />-->

		<link rel="stylesheet" href="common/contentServer/js_color_picker_v2/js_color_picker_v2.css" media="screen">

		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>

		<script src="common/contentServer/js_color_picker_v2/color_functions.js"></script>
		<script type="text/javascript" src="common/contentServer/js_color_picker_v2/js_color_picker_v2.js"></script>

		<style>
		    <?php
		        //browser was caching the css file
		        //this works around the caching
				echo('<!--');
		        echo(file_get_contents('site/site.css'));
				echo('-->');
		    ?>
		</style>

		<script type="text/javascript">

		//used by font family selector written by common/style.php
		function changeFont(selectorId)
		{
			select = document.getElementById(selectorId);
			selectedIndex = select.selectedIndex;
			selectedFont = select.options[selectedIndex].value;

			target = document.getElementById("examplep");
			target.style.fontFamily = selectedFont;

			target = document.getElementById("exampleh1");
			target.style.fontFamily = selectedFont;

			target = document.getElementById("exampleh2");
			target.style.fontFamily = selectedFont;

			target = document.getElementById("exampleh3");
			target.style.fontFamily = selectedFont;
		}

		//selectorId is the id of the select tag
		function changeSize(selectorId)
		{
			select = document.getElementById(selectorId);
			selectedIndex = select.selectedIndex;
			selectedSize = select.options[selectedIndex].value;

			target = document.getElementById("examplep");
			target.style.fontSize = selectedSize;
		}

		function changeGutterWidth(selectorId)
		{
			select = document.getElementById(selectorId);
			selectedIndex = select.selectedIndex;
			selectedSize = select.options[selectedIndex].value;

			target = document.getElementById("bordertableTable");
			target.style.margin = selectedSize;
		}

		</script>
	</head>

	<!--<body style='background : grey; margin : 0;'>-->
	<body style='margin : 0;'>
	<?php echo(get_menu()); ?>

	<?php echo(Message::get_noscript_message()); ?>
	<table width=100%><tr><td class=centre>
	    <center>

	    <?php if($saved != null) echo(Message::get_success_display($saved)); ?>

	    <form action=editstyle.php method=post>
	    <p style='margin-top:1em;'>
	    	<table style='border:solid black 1px;' cellpadding=2 cellspacing=10 border=0>
		    <tr>
				<td><span id=siteFont class=translate_me>Site font</span></td>
				<td>
					<?php WriteFontFamily(BODY . DELIMIT . FONT_FAMILY, getStyleValue(BODY, FONT_FAMILY, $styles)); ?>
		        </td>
		    </tr>

   		    <tr>
				<td><span id=norFonSi class=translate_me>Normal font size</span></td>
				<td>
					<?php WriteSize(P . DELIMIT . FONT_SIZE, getStyleValue(P, FONT_SIZE, $styles)); ?>
		        </td>
		        <td>
					<p id=examplep>Sample text</p>
		        </td>
		    </tr>

		    <tr>
				<td><span id=norFonCo class=translate_me>Normal font colour</span></td>
				<td>
					<?php WriteColour(BODY . DELIMIT . COLOR, getStyleValue(BODY, COLOR, $styles)); ?>
		        </td>
		    </tr>

		    <tr>
		        <td><span id=liCo class=translate_me>Hyperlink colour</span></td>
		        <td>
					<?php WriteColour(A . DELIMIT . COLOR, getStyleValue(A, str_replace(DELIMIT, '-', COLOR), $styles)); ?>
		        </td>

		       	<td>
		            <a style='text-decoration:underline;' id=examplea>Sample link</a>
		        </td>
		    </tr>

				<?php
				    foreach($used_tags as $tag)
				    {
				    	$rand = rand();
				    	echo('<tr>');

				        echo('<td><span id=t'.$rand.' class=translate_me>'. translateTag($tag) . '</span></td>');

				        echo('<td>');
								WriteColour($tag . DELIMIT . COLOR, getStyleValue($tag, COLOR, $styles));
								echo('</td>');

								echo('<td style="text-align:center;">');
								echo('<' . $tag . ' id=example' . $tag . '><span id=s'.$rand.' class=translate_me>Sample text</span></' . $tag . '>');
								echo('</td>');

				        echo('</tr>');
					}
				?>

					    <tr>
		        <td><span id=sgCo class=translate_me>Site gutter colour</span></td>
		        <td>
		            <?php
						WriteColour(BODY . DELIMIT . BG_COLOR, getStyleValue(BODY, str_replace(DELIMIT, '-', BG_COLOR), $styles));
						echo(' ' . Message::get_help_link(6059651));
					?>
		        </td>
		    </tr>

		    <tr>
		        <td><span id=sgWi class=translate_me>Site gutter width</span></td>
		        <td>
		            <?php
						WriteGutterWidth(BODY . DELIMIT . MARGIN, getStyleValue(BODY, str_replace(DELIMIT, '-', MARGIN), $styles));
						echo(' ' . Message::get_help_link(2689919));
					?>
		        </td>
		    </tr>

			    <tr>
			    <td colspan=3 id=bordertable style='padding:0; background : <?php echo(GetStyleValue(BODY, 'background-color', $styles)); ?>;'>
			        <table id=bordertableTable width=100% cellspacing=0 style='margin : <?php echo(GetStyleValue(BODY, MARGIN, $styles)); ?>;'>
					<tr>
						<td id=exampletdheader colspan=3 class=header>
							<p style='text-align:center;'>
								<span id=head class=translate_me>Header</span>
								<?php WriteColour('tdheader' . DELIMIT . BG_COLOR, getStyleValue('td.header', str_replace(DELIMIT, '-', BG_COLOR) , $styles)); ?>
							</p>
						</td>
					</tr>

					<tr>
					<!-- set td width to auto as it screws with the table layout if it uses the site.css values -->
						<td id=exampletdleftMargin class=leftMargin style='width:auto;'>
							<p style='line-height : 200%; text-align:center;'>
								<span id=lefty class=translate_me>Left margin</span><br>
								<?php
                  WriteColour('tdleftMargin' . DELIMIT . BG_COLOR, getStyleValue('td.leftMargin', str_replace(DELIMIT, '-', BG_COLOR), $styles));
                  echo('<br>Width ');
                  WriteWidth('tdleftMargin' . DELIMIT . WIDTH, getStyleValue('td.leftMargin', WIDTH, $styles));
                  echo(' ' . Message::get_help_link(1616716));
                ?>
							</p>
						</td>
						<td id=exampletdcentre class=centre>
							<p style='text-align:center;'>
								<span id=theCentre class=translate_me>Centre</span>
								<?php WriteColour('tdcentre' . DELIMIT . BG_COLOR, getStyleValue('td.centre', str_replace(DELIMIT, '-', BG_COLOR), $styles)); ?>
							</p>
						</td>
						<td id=exampletdrightMargin class=rightMargin style='width:auto;'>
							<p style='line-height : 200%; text-align:center;'>
								<span id=righty class=translate_me>Right margin</span><br>
								<?php WriteColour('tdrightMargin' . DELIMIT . BG_COLOR, getStyleValue('td.rightMargin', str_replace(DELIMIT, '-', BG_COLOR), $styles));
                  echo('<br>Width ');
                  WriteWidth('tdrightMargin' . DELIMIT . WIDTH, getStyleValue('td.rightMargin', WIDTH, $styles));
                  echo(' ' . Message::get_help_link(1616716));
                ?>
							</p>
						</td>
					</tr>

					<tr>
						<td id=exampletdfooter colspan=3 class=footer>
							<p style='text-align:center;'>
								<span id=theFooter class=translate_me>Footer</span>
								<?php WriteColour('tdfooter' . DELIMIT . BG_COLOR, getStyleValue('td.footer', str_replace(DELIMIT, '-', BG_COLOR), $styles)); ?>
							</p>
						</td>
					</tr>
					</table>
				</td>
				</tr>
				<tr>
		   		<td>
		   			<span id=saveButton class=translate_me><input style="font-size:larger;" type=submit value=' Save '></span> <a href=editstyle.php>Discard changes</a>
				</td>
		   	</tr>
		</table>
			</p>
		</form>
		</center>
		</td></tr></table>

		<?php echo(Globals::get_affinity_footer()); ?>
	</body>
</html>