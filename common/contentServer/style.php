<?php
require_once('common/utils/Globals.php');

function getStyleValue($tag, $property, $styles)
{
	if(array_key_exists($tag, $styles))
	{
		if(array_key_exists($property, $styles[$tag]))
		{
			return $styles[$tag][$property];
		}
		else
		{
			return null;
		}
	}
	else
	{
		return null;
	}
}

function setStyleValue($tag, $property, &$styles, $newValue)
{
	$styles[$tag][$property] = $newValue;
}

/*
Loads a css located at $cssFile.
The stylesheet must be in the format shown in the example just below. NO SPACES!
Returns it as an associative array of arrays.
Keyed on the tag.

For example, this style sheet...
body{color:red;font-family:arial;}
h1{color:yellow;}

...will produce this return value...
//TODO UPDATE THIS. NO LONGER USES STYLE OBJECT
styles['body'][0] = Style(property=color, value=red)
styles['body'][1] = Style(property=font-family, value=arial)
styles['h1'][0] = Style(property=color, value=yellow)
*/
function loadSheet($cssFile)
{
	$lines = file($cssFile);
	$styles = array();
	
	$tag = null;
	$property = null;
	$value = null;
	
	foreach($lines as $line)
	{
	    $line = rtrim($line);
		$tag = strtok($line, '{');
		$temp = strtok(':');
		$styles[$tag] = array();
		
		while($temp !== false)
		{
			$value = strtok(';');

			if($temp == '}')
			{
				$temp = false;
			}
			else
			{
				$styles[$tag][$temp] = $value;
			}
			
			$temp = strtok(':');
		}
	}

	return $styles;
}

function saveSheet($saveHere, $styles)
{
	$css_string = convertStylesToString($styles);
	file_put_contents($saveHere, $css_string);
}

function convertStylesToString($styles)
{
	$tag_keys = array_keys($styles);
	$css_string = '';
	
	foreach($tag_keys as $tag)
	{
		$property_keys = array_keys($styles[$tag]);
		$css_string .= $tag . '{';

		foreach($property_keys as $property)
		{
		    $css_string .= $property . ':' . $styles[$tag][$property] . ';';
		}
		
		$css_string .= '}';
		$css_string .= "\n"; //new line should be in ""
	}
	
	return $css_string;
}

//show user the same tag names that the editor uses
function translateTag($tag)
{
	if($tag == 'h1')
	{
		return 'Heading 1';
	}
	else if($tag == 'h2')
	{
		return 'Heading 2';
	}
	else if($tag == 'h3')
	{
		return 'Heading 3';
	}
	else
	{
		return 'TODO';
	}
}

function WriteFontFamily($name, $selected)
{
	echo('<select onChange=javascript:changeFont("' . $name . '"); id=' . $name . ' name=' . $name . '>');

	$fonts = array('Arial' => 'arial', 'Garamond' => 'garamond', 'Monospace' => 'monospace', 'Tahoma' => 'tahoma', 'Times New Roman' => 'times', 'Trebuchet MS' => 'trebuchet ms', 'Verdana' => 'verdana');
	$keys = array_keys($fonts);

	foreach($keys as $key)
	{
		echo('<option value=' . $fonts[$key]);

		if($selected == $fonts[$key])
		{
			echo(' selected ');
		}

		echo('>' . $key . '</option>');
	}

	echo('</select>');
}

function WriteWidth($name, $selected)
{
	echo('<select id=' . $name . ' name=' . $name . '>');
	
	$widths = array('Hidden' => '0%', 'Thinnest' => '5%', 'Thin' => '10%', 'Medium' => '15%', 'Wide' => '20%', 'Widest' => '25%');
	$keys = array_keys($widths);

	foreach($keys as $key)
	{
		echo('<option value=' . $widths[$key]);

		if($selected == $widths[$key])
		{
			echo(' selected ');
		}

		echo('>' . $key . '</option>');
	}

	echo('</select>');
}

function WriteColour($name, $selected)
{
	echo('<input value="' . $selected . '" readonly type=hidden name="' . $name . '">');
	//echo(' <span id=c'.$name.' class=translate_me><input type=button value="Pick Colour" onclick="showColorPicker(this,document.forms[0].' . $name . ')" /></span>');
	echo(' <a href="#" onclick="showColorPicker(this,document.forms[0].' . $name . ');return false;" ><span id=c'.$name.' class=translate_me>Pick Colour</span></a>');
}

function WriteSize($name, $selected)
{
	echo('<select onChange=javascript:changeSize("' . $name . '"); id=' . $name . ' name=' . $name . '>');

	for($i=6; $i<=16; $i++)
	{
		echo('<option ');

		if($i == $selected)
		{
			echo(' selected ');
		}

		echo(' value=' . $i . 'pt>' . $i . '</option>');
	}
	echo('</select>');
}

function WriteGutterWidth($name, $selected)
{
	echo('<select onChange=javascript:changeGutterWidth("' . $name . '"); id=' . $name . ' name=' . $name . '>');

	$widths = array('None' => '0em', 'Thin' => '.2em', 'Medium' => '.5em', 'Wide' => '1em');
	$keys = array_keys($widths);

	foreach($keys as $key)
	{
		echo('<option value=' . $widths[$key]);

		if($selected == $widths[$key])
		{
			echo(' selected ');
		}

		echo('>' . $key . '</option>');
	}

	echo('</select>');
}
?>
