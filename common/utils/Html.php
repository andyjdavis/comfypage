<?php
class HtmlInput
{
    public static function get_text_input($input_name, $input_value, $size = 35)
    {
    	return <<<END
<input type=text name="$input_name" id="$input_name" value="$input_value" size=$size />
END;
    }
    public static function get_array_input($input_name, $array)
    {
        $all_vals = '';
        foreach($array as $val)
        {
            $all_vals .= "$val\n";
        }
        return HtmlInput::get_textarea_input($input_name, $all_vals, 10, 90);
    }
    public static function get_checkbox_input($input_name, $input_value, $display_text = null)
    {
    	$i = "<label><input type=checkbox name=$input_name";
    	if($input_value)
    	{
    		$i .= ' checked ';
    	}
    	$i .= "> $display_text</label>";
    	return $i;
    }
	public static function get_file_input($input_name, $input_value)
	{
		return <<<END
<input type=file name="$input_name" value="$input_value" size=35>
END;
	}
    public static function get_password_input($input_name, $input_value)
    {
    	return <<<END
<input type=password name="$input_name" value="$input_value" size=35>
END;
    }
    public static function get_textarea_input($input_name, $input_value = '', $rows = 10, $cols = 40, $readonly = false)
    {
    	$readonly_attribute = '';
    	if($readonly) $readonly_attribute = ' readonly ';
        return <<<END
<textarea name="$input_name" cols=$cols rows=$rows $readonly_attribute>$input_value</textarea>
END;
    }
    public static function get_select_input($input_name, $input_value, $options_array, $onchange_JS_code = null)
    {
    	$onchangeJS = null;
    	if($onchange_JS_code)
    	{
    		$onchangeJS = "onchange=\"Javascript:$onchange_JS_code\"";
    	}
    	$s = "<select name=\"$input_name\" $onchangeJS>";
    	
    	if(is_array($options_array) == true)
    	{
    		$keys = array_keys($options_array);
    		$value = null;
    		$display_value = null;
    		
    		for($i = 0; $i<count($keys); $i++)
    		{
    		    $value = $keys[$i];
    		    $display_value = $options_array[$value];
    		    
    			$s .= "<option value=\"$value\" ";
    			
    			if($value == $input_value)
    			{
    				$s .= " selected ";
    			}
    			
    			$s .= ">";
    			$s .= "$display_value</option>";
    		}
    	}
    	
    	$s .= "</select>";
    	return $s;
    }
	public static function get_date_input($input_name, $input_value, $prepopulate_hidden) {
		$val = '';
		if($prepopulate_hidden) {
		    $val = $input_value;
		}
		//$s = HtmlInput::get_text_input($input_name, $val);
		$s = HtmlInput::get_hidden_input($input_name,$val);
		
		//day selector
		//$options_array
		//$s .= HtmlInput::get_select_input($input_name, $input_value, $options_array, $onchange_JS_code);
		
		//something like "Sat, 16 Jan 2010"
		$day = substr($input_value, 5, 2);
		$month = substr($input_value, 8, 3);
		$year = substr($input_value, 12, 4);

		$monthnames = array(
1 => 'Jan',
2 => 'Feb',
3 => 'Mar',
4 => 'Apr',
5 => 'May',
6 => 'Jun',
7 => 'Jul',
8 => 'Aug',
9 => 'Sep',
10 => 'Oct',
11 => 'Nov',
12 => 'Dec');

		for($i=1;$i<13;$i++) {
		    if($monthnames[$i]==$month){
			    $month = $i;
		    }
		}

  		$s .= <<<END
<Script Language="JavaScript">

function date_editor_changed() {
	var d = document.getElementById("mday");
	day = d.options[d.selectedIndex].text;

	var m = document.getElementById("mmonth");
	month = m.options[m.selectedIndex].text;

	var y = document.getElementById("myear");
	year = y.options[y.selectedIndex].text;

	document.getElementById("$input_name").value = day+" "+month+" "+year;
}

function get_days_in_month() {
	var month_select = document.getElementById("mmonth");
	if(month_select.options[1].selected)
		return 28;
	else if(month_select.options[8].selected||month_select.options[3].selected||month_select.options[5].selected||month_select.options[10].selected)
		return 30;
	else
		return 31;
}

function date_editor_populate()
{
var temp=0;

var day=$day;
var month=$month;
var year=$year;

mday = document.getElementById("mday");
mmonth = document.getElementById("mmonth");
myear = document.getElementById("myear");

//month
for(var i=1;i<13;i++) {
	if(i==month) {
		mmonth.options[i-1].selected=true;
		break;
	}
}

//day
var days = get_days_in_month();
for (var i=0; i<days ; i++)
{
	var x= String(i+1);
	mday.options[i] = new Option(x,x);
	if(x==day){
		mday.options[i].selected=true;
	}
}

//year
var y= String(year);
myear.options[1] = new Option(y,y);
var y= String(year-1);
myear.options[0] = new Option(y,y);
var y= String(year + 1);
myear.options[2] = new Option(y,y);
}

function date_editor_repopulate()
{
var t3= get_days_in_month();

var d = document.getElementById("mday");
for(i=0;i<31;i++){
	d.options[i]=null;
}
for (var i=0; i <t3 ; i++)
	{
	var x= String(i+1);
	d.options[i] = new Option(x);
	}
}
</script>
<SELECT id="mday" NAME="mday" onchange="date_editor_changed();"></SELECT>&nbsp;<SELECT id="mmonth" NAME="mmonth" onChange="date_editor_repopulate();date_editor_changed();">
<Option value=1>January</Option>
<Option value=2>February</Option>
<Option value=3>March</Option>
<Option value=4>April</Option>
<Option value=5>May</Option>
<Option value=6>June</Option>
<Option value=7>July</Option>
<Option value=8>August</Option>
<Option value=9>September</Option>
<Option value=10>October</Option>
<Option value=11>November</Option>
<Option value=12>December</Option>
</SELECT>&nbsp;<SELECT id="myear" NAME="myear" onchange="date_editor_changed();"></SELECT>

<Script Language="JavaScript">
date_editor_populate();
</Script>
END;
		return $s;
	}
    public static function get_hidden_input($input_name, $input_value, $display_text=null)
	{
		return "<input type=hidden name=\"$input_name\" value=\"$input_value\">$display_text";
	}
	function get_radio_input($input_name, $input_value, $options_array, $input_seperator = '&nbsp;&nbsp;&nbsp;')
	{
		$s = null;
		if(is_array($options_array) == true)
		{
			$keys = array_keys($options_array);
			$value = null;
			$display_value = null;
			for($i = 0; $i<count($keys); $i++)
			{
			    $value = $keys[$i];
			    $display_value = $options_array[$value];
				$s .= "<label><input type=radio name=\"$input_name\" value=\"$value\" ";
				if($value == $input_value)
				{
					$s .= " checked ";
				}
				$s .= ">";
				$s .= "$display_value</label>$input_seperator";
			}
		}
		return $s;
	}
	function get_dir_input($input_name, $input_value, $size = 35)
	{
		return <<<END
<script language="Javascript">
function SetUrl(url)
{
	$("#$input_name").val(url);
}
</script>
<input type="text" name="$input_name" id="$input_name" value="$input_value" size="$size" />
<a href="#" onclick="Javascript:window.open('files_dir_selector.php','dir_selector','location=1,status=1,scrollbars=1,width=300,height=400'); return false;">Select Directory</a>
END;
	}
	public function get_page_selector($contentId,$user_pages,$index_page) {
	    $page_selector = '<form method="GET">page: <select name="'.CONTENT_ID_URL_PARAM.'">';
	    $selected = null;
	    if($contentId=='INDEX')
	    {
		    $selected = 'selected';
	    }
	    $page_selector.='<option '.$selected.' value="'.$index_page->id.'">home</option>';
	    foreach($user_pages as $page)
	    {
		    $selected = null;
		    if($contentId==$page->id)
		    {
			    $selected = 'selected';
		    }
		    $page_selector.='<option '.$selected.' value="'.$page->id.'">'.$page->get(CONTENT_TITLE).'</option>';
	    }
	    $page_selector.='</select> <input type="submit" value="switch page" /> <a href="edit.php?'.CONTENT_ID_URL_PARAM.'='.$contentId.'">Edit current page</a></form>';
	    return $page_selector;
	}
	public function get_editor_html($id, $raw, $width='100%')
	{
		require_once('common/contentServer/FCKeditor/fckeditor.php');

		$oFCKeditor = new FCKeditor($id);
		$oFCKeditor->Value = $raw;
		$oFCKeditor->BasePath = 'common/contentServer/FCKeditor/';
		$oFCKeditor->Height = '500';
		$oFCKeditor->Width = $width;
		//$oFCKeditor->setLang(get_site_language());
		$oFCKeditor->Config['AutoDetectLanguage'] = false ;
		$oFCKeditor->Config['DefaultLanguage'] = Load::general_settings(LANG);

		return $noScript = Message::get_noscript_message().$oFCKeditor->CreateHtml();
	}
	//TODO make this work on any page (particularly ss2.php. Might just be we aren't including the JS on that page.
	//TODO make this more generic. It relies on the $options_array being filled with page objects
	function get_sortable_input($input_name, $input_value, $options_array)
	{
		$id = null;
		$title = null;

		$s = '<script type="text/javascript" src="common/lib/jquery/jquery-ui-personalized-1.6rc2.min.js"></script>

		<script type="text/javascript">
			$(document).ready(function(){
				$("#selectedPages").sortable({
				    connectWith: ["#otherPages"]
				});
				$("#otherPages").sortable({
				    connectWith: ["#selectedPages"]
				});

				$("#selectedPages").bind("sortreceive", changedSortableList);
				$("#otherPages").bind("sortreceive", changedSortableList);

				$("#selectedPages").mouseout(changedSortableList);
			});

			function changedSortableList(e)
			{
				var s = $("#selectedPages").sortable("serialize");

				var a = s.split("&");

				for(var i=0; i< a.length; i++)
				{
					//remove "page[]=" from the beginning
					a[i] = a[i].substring(7);
				}

				$("#'.$input_name.'").val(a.join(","));
			}
		</script>
		<style>ul { list-style: none;background-color:#DDDDDD;padding:30px;border:1px solid black; }
	li { background: #727EA3; color: #FFF; width: 100px; margin: 5px; font-size: 12px; font-family: Arial; padding: 3px; }</style>

		';

		$s .= '<input type="hidden" name="'.$input_name.'" id="'.$input_name.'" value="'.$input_value.'" size=30 />';
		//$s .= '<div style="align:center;">Use the mouse to add pages to your menu and to reorder them</div>';
		$s .= '<div style="float: left;text-align:center;"><b>Your Menu</b><ul id="selectedPages" style="width:8em;cursor: hand; cursor: pointer;">';
		if($input_value==null)
		{
			//$s .= '<li id="page_INDEX" class="page">Home</li>';
		}
		else
		{
			$page_array = split(",", $input_value);
			foreach($page_array as $page_id)
			{
				foreach($options_array as $i=>$content)
				{
					if($content->id==$page_id)
					{
						$id = $content->id;
						$title = $content->get(CONTENT_TITLE);
						$s .= '<li id="page_' . $id . '" class="page">' . $title . '</li>';

						unset($options_array[$i]);
					}
				}
			}
		}
		$s .= '</ul></div><div style="text-align:center;float: left; margin-left: 50px;"><b>Other Pages</b><ul id="otherPages" style="width:8em;cursor: hand; cursor: pointer;">';
		foreach($options_array as $content)
		{
			$id = $content->id;
			$title = $content->get(CONTENT_TITLE);
			$s .= '<li id="page_' . $id . '" class="page">' . $title . '</li>';
		}
		$s .= '</ul></div>';

		return $s;
	}
}
class Message
{
    //concatenates error messages
    //one per line
    public static function format_errors($existing_errors, $new_error)
    {
    	if(empty($existing_errors))
    	{
    		return $new_error;
    	}
    	else
    	{
    		if(empty($new_error))
    		{
    			return $existing_errors;
    		}
    		else
    		{
    			return "$existing_errors<br>$new_error";
    		}
    	}
    }
    public static function get_error_display($message)
    {
        return Message::get_message_display($message, 'red', '#FFDEDE');
	}
	public static function get_success_display($message)
	{
    	return Message::get_message_display($message, 'green', '#DEFFDE');
	}
    public static function get_message_display($message, $border_colour = 'Gainsboro', $fill_colour = 'white', $pre = true, $font_colour = 'Navy')
	{
		if(empty($message))
		{
			return null;
		}
	 	$error_display = '<center>';
		$error_display .= '<table cellpadding=10 align=center style="text-align:center;margin:0.5em;background:' . $fill_colour . ';color:black;border:solid ' .$border_colour. ' 3px;">';
		$error_display .= '<tr>';
		$error_display .= "<td style='font-family:arial;color:$font_colour;'>";
		$closeSpan = false;
		//if there are no translation tags within the message put one around it
		if(stripos($message, 'translate_me') === FALSE)
		{
			$closeSpan = true;
			$rand = rand();
			$error_display .= "<span id=message_$rand class=translate_me>";
		}
		$error_display .= $message;
		if($closeSpan)
		{
			$error_display .= '</span>';
		}
		$error_display .= '</td>';
		$error_display .= '</tr>';
		$error_display .= '</table>';
	 	$error_display .= '</center>';
		return $error_display;
	}
	function get_help_link($topicId, $linkText = 'What?', $anchorName = null)
	{
		$link = "<a target=comfyhelp href=http://help.comfypage.com/index.php?content_id=$topicId";
		if($anchorName != null && strlen($anchorName) > 0)
		{
			$link .= '#' . $anchorName;
		}
		$link .= ' ><span id=help_'.$topicId.' class="translate_me">' . $linkText . '</span></a>';
		return $link;
	}
	function get_noscript_message()
	{
		$noScript = "Your browser is not configured to use ComfyPage. Enable Javascript in your browser. " . Message::get_help_link(1250179, 'How do I do this?', 'javascript');
		return '<noscript>'.Message::get_error_display($noScript).'</noscript>';
	}
	public static function get_language_JS_block()
	{
		$language = Load::general_settings(LANG);
		if($language != null && $language != 'en')
		{
			return <<<END
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script LANGUAGE=JavaScript SRC='common/languages/$language/translations.js'></script>
END;
		}
		return null;
	}
}
class Format
{
	public static function price($price, $currency_code, $show_currency_symbol = false, $show_currency_code = false, $decimal_place = true)
	{
		$formatted_price = null;
		$currency_symbol = null;
		$number_of_decimals = 2;
		if($decimal_place == false)
		{
	        $number_of_decimals = 0;
		}
		//add currency cases here if they need specific formatting
		switch($currency_code)
		{
			case 'EUR' :
		    {
				$formatted_price = number_format($price, $number_of_decimals);
				$currency_symbol = '&euro;';
				break;
			}
			case 'GBP' :
		    {
				$formatted_price = number_format($price, $number_of_decimals);
				$currency_symbol = '&pound;';
				break;
			}
		    case 'JPY' :
		    {
				$formatted_price = number_format($price, 0);
				$currency_symbol = '&yen;';
				break;
			}
			default :
			{
			    //australian dollar/us dollar formatting with 2 decimal places
				$formatted_price = number_format($price, $number_of_decimals);
				$currency_symbol = '$';
				break;
			}
		}
		if($show_currency_symbol)
		{
			$formatted_price = "$currency_symbol$formatted_price";
		}
		if($show_currency_code)
		{
			$formatted_price = "$formatted_price $currency_code";
		}
		return $formatted_price;
	}
}
?>
