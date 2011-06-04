<?
header('Cache-Control: no-cache');
header('Pragma: no-cache');
header("Content-type: text/css");

echo file_get_contents('../../site/site.css');

/*
require_once('../../common/style.php');

define('MARGIN', 'margin');

$styleString = null;

if(array_key_exists(MARGIN, $_GET))
{
	$originalStyles = loadSheet('../../site/site.css');
	
	switch($which)
	{
		case LEFT_MARGIN :
		{
			$changeTo = getStyleValue('td.leftMargin', 'background-color', $originalStyles);
			break;
		}
		case RIGHT_MARGIN :
		{
			$changeTo = getStyleValue('td.rightMargin', 'background-color', $originalStyles);
			break;
		}
		case HEADER :
		{
			$changeTo = getStyleValue('td.header', 'background-color', $originalStyles);
			break;
		}
		case FOOTER :
		{
			$changeTo = getStyleValue('td.footer', 'background-color', $originalStyles);
			break;
		}
		case CENTRE :
		{
			$changeTo = getStyleValue('td.centre', 'background-color', $originalStyles);
			break;
		}
		default :
		{
		    //no change
		    $changeTo = getStyleValue('body', 'background-color', $originalStyles);
			break;
		}
	}
	
	setStyleValue('body', 'background-color', $originalStyles, $changeTo);
	$styleString = ConvertStylesToString($originalStyles);
}
else
{
	//output plain old css
	$styleString = file_get_contents('../../site/site.css');
}

echo($styleString);*/

?>
