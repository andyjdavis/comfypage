<?php
define('MENU_IMAGE_HEIGHT','24');
define('MENU_TEXT_SIZE','14');
class Menu
{
	var $first_items = array();
	var $middle_items = array();
	var $last_items = array();
	function Menu()
	{
	    $this->first_items['Home page'] = 'index.php';
	    $this->first_items['Site manager'] = 'admin.php';
	    $this->first_items['Shop manager'] = 'product_admin.php';
	    $this->last_items['Help'] = 'contact.php';
	    //$this->last_items['Help'] = 'http://help.comfypage.com';
	    //$this->last_items['Contact a human'] = 'contact.php';
	    $this->last_items['Log out'] = 'logout.php';
	}
	//add item to the menu
	function add_item($text, $target)
	{
		$this->middle_items[$text] = $target;
	}
	//get the full menu
	function get_menu($on_this_page = null, $hide_standard_menu_items = false)
	{
	    $menu = null;
	    $menu = '<div style="vertical-align:top;height:'.(MENU_IMAGE_HEIGHT+3).'px;background:white;padding-top:1px;border-bottom:solid #BBBBBB 2px;font-family:arial;font-size:'.MENU_TEXT_SIZE.';text-align:center;">';
	    if(!$hide_standard_menu_items)
	    {
			$menu .= '<span style="position:absolute; left:0;">'.$this->get_menu_piece($this->first_items, $on_this_page).'</span>';
		}
		$menu .= '<span style="position: relative; text-align: center; width: 100%;top:20%;">'.$this->get_menu_piece($this->middle_items, $on_this_page, false).'</span>';
		if(!$hide_standard_menu_items)
		{
			$menu .= '<span style="position:absolute; right:0;">'.$this->get_menu_piece($this->last_items, $on_this_page);
		}
		$menu .= '</span></div>';
		return $menu;
	}
	//convenience funtion
	function get_menu_piece($items, $on_this_page, $use_images = true)
	{
	    $piece = null;
		foreach($items as $text => $target)
		{
			$r = rand();
			$size = MENU_IMAGE_HEIGHT;
			//if it's the menu item for where we are (ie should be disabled)
			if($text == $on_this_page)
			{
				if($use_images)
				{
					$piece .= "<img src='common/images/$text.png' height='$size' width='$size' alt='$text' title='$text' >";
				}
				else
				{
					$piece .= "<b><span class='translate_me' id='m_$r'>$text</span></b>";
				}
			}
			else
			{
				//hard-code all the settings so it is immune to stylesheets etc.
				if($use_images)
				{
					$piece .= "<a target=_parent style='color:blue;text-decoration:none;' href='$target'><img src='common/images/$text.png' height='$size' width='$size' alt='$text' title='$text' border='0'></a>";
				}
				else
				{
					$piece .= "<a target=_parent style='color:blue;text-decoration:none;' href='$target'><span class='translate_me' id='m_$r' style='font-size:".MENU_TEXT_SIZE.";'>$text</span></a>&nbsp;&nbsp;&nbsp;"; //menu item that is a link
				}
			}
			$piece .= '&nbsp;&nbsp;&nbsp;';
		}
		return $piece;
	}
}
//convenience function
function get_menu($on_this_page = null)
{
	$menu = new Menu;
	return $menu->get_menu($on_this_page);
}
?>
