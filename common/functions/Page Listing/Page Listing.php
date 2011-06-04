<?php
define('PAGE_LIST_AUTO', 'auto');

define('PAGE_LIST_ORIENTATION', 'PAGE_LIST_ORIENTATION');
define('PAGE_LIST_HORIZ', 1);
define('PAGE_LIST_VERT', 0);

define('PAGE_LIST', 'LIST');
class Page_Listing extends Addon
{
	function __construct()
	{
		parent::__construct(true);
	}
	public function get_addon_description()
    {
		return 'Displays an automatic or custom menu of all your pages';
	}
    public function get_instructions()
    {
    	return 'Displays a listing of your pages either across or down the page. It can automatically generate a list or you can sort the list yourself.';
	}
	public function get_first_stage_output($additional_inputs)
    {
		$current_content_id = Globals::get_param(CONTENT_ID_URL_PARAM, $_GET, INDEX);
		//holds the pages to include in the menu
		$page_array = null;

		$ps = Load::page_store();
		$user_pages = $ps->load_users_pages();
		$user_pages[INDEX] = $ps->load_index_page();

		$output = null;
		$menu_seperator = null;
		$classname = null;

		if($this->get(PAGE_LIST_ORIENTATION) == PAGE_LIST_HORIZ)
		{
                    $divattributes= 'style="text-align: center;" class="menu menuhorizontal"';
                    $menu_seperator = '&nbsp;&nbsp;&nbsp;';
		}
		else
		{
                    $divattributes= 'class="menu menuvertical"';
                    $menu_seperator = '</p><p>';
		}
                $output .= "<div $divattributes>";
		$output .= '<p>';
		
		if(!$this->get(PAGE_LIST_AUTO))
		{
			$commaList = $this->get(PAGE_LIST);
			if($commaList)
			{
				$ids = explode(',', $commaList);
				if($ids)
				{
					foreach($ids as $id)
					{
						foreach($user_pages as $content)
						{
							if($content->id == $id)
							{
								$page_array[] = $content;
							}
						}
					}
				}
			}
		}
		//if we either want auto menu or manual menu failed for some reason
		if(!$page_array)
		{
			$index_page = $user_pages[INDEX];
			$index_id = $index_page->id;
			$index_title = $index_page->get(CONTENT_TITLE);
			if($index_title=='My ComfyPage Website')
			{
				$index_title = 'Home';
			}
			$classname = 'cp_menu_link';
			if($current_content_id == 'INDEX')
			{
				$classname .= '_curr';
			}
			$output .= "<strong><a class='$classname' href='$index_id.htm'>$index_title</a></strong>$menu_seperator";
			//$output .= "<strong><a class='$classname' href='index.php?content_id=$index_id'>$index_title</a></strong>";
			//weve already output the index page remove it from the array to output
			unset($user_pages[INDEX]);
			$page_array = $user_pages;
		}
		foreach($page_array as $content)
		{
			$content_id = $content->id;
			$content_title = $content->get(CONTENT_TITLE);
			$classname = 'cp_menu_link';
			if($current_content_id==$content_id)
			{
				$classname .= '_curr';
			}
			if($output!=null)
			{
				$output .= $menu_seperator;
			}
			//$output .= "<a class='$classname' href='$content_id.htm'>$content_title</a>";
			$output .= "<a class='$classname' href='index.php?content_id=$content_id'>$content_title</a>";
		}
		$output .= '</p>';
		if($this->get(PAGE_LIST_ORIENTATION) == PAGE_LIST_HORIZ)
		{
			$output .= '</div>';
		}
		return $output;
    }
    protected function get_default_settings()
	{
		$s = array();
		$s[PAGE_LIST_ORIENTATION] = PAGE_LIST_VERT;
		$s[PAGE_LIST_AUTO] = true;
		$s[PAGE_LIST] = null;
		return $s;
	}
	public function validate($setting_name, $setting_value)
	{
		switch($setting_name)
	  	{
			case PAGE_LIST_ORIENTATION:
			{
				if( $setting_value==PAGE_LIST_HORIZ || $setting_value==PAGE_LIST_VERT )
				{
					return null;
				}
				else
				{
					return 'Page Listing must be horizontal or vertical';
				}
			}
			case PAGE_LIST_AUTO:
			{
				if( $setting_value==true || $setting_value==false)
				{
					return null;
				}
				else
				{
					return '"Page list automatic" must be true or false';
				}
			}
			case PAGE_LIST:
			{
				//validated in validate_doodad_settings()
				return null;
			}
			default :
	  		{
	  			return null;
	  		}
	  	}
	}
	protected function get_description_dictionary()
	{
		return array
		(
		PAGE_LIST_AUTO => 'Automatic page order',
		PAGE_LIST_ORIENTATION => 'Horizontal or vertical page listing',
		PAGE_LIST => "Manual order of your pages (Drag and drop the items. Don't forget to turn off auto page ordering above)",
		);
	}
	function get_input_internal($setting_name, $setting_value)
	{
		switch($setting_name)
	  	{
			case PAGE_LIST_AUTO:
			{
				return  HtmlInput::get_checkbox_input($setting_name, $setting_value, 'Auto page order');
			}
			case PAGE_LIST_ORIENTATION :
			{
				return  HtmlInput::get_radio_input($setting_name, $setting_value, $this->get_orientations());
			}
			case PAGE_LIST:
			{
				return  HtmlInput::get_sortable_input($setting_name, $setting_value, $this->get_pagelist());
			}
	  		default :
	  		{
	  			return parent::get_input_internal($setting_name, $setting_value);
	  		}
	  	}
	}
	function get_orientations()
	{
		return array
		(
			PAGE_LIST_HORIZ => 'Horizontal',
			PAGE_LIST_VERT => 'Vertical'
		);
	}
	function get_pagelist()
	{
	    $ps = Load::page_store();
		$pages = $ps->load_users_pages();
		$pages[INDEX] = $ps->load_index_page();
		return $pages;
	}
}
?>