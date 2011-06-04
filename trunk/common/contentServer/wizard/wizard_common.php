<?php

define('STEP_COUNT', 4);
define('FLASH_STEP_COUNT', 3);
define('BAND_WEBSITE_STEP_COUNT', 4);

define('PURPOSE_PERSON', 'p');
define('PURPOSE_ORG', 'o');

function DoesPageExist($pages, $pageTitle)
{
	if($pages!=null)
	{
		foreach($pages as $a_page)
		{
			if($pageTitle == $a_page->get(CONTENT_TITLE))
			{
				return true;
			}
		}
	}
	return false;
}

//if page listing is in other borders remove it.  if its any other addon then leave it
function RemoveListing($margin)
{
	$page = Load::page($margin);
	$func = $page->get(CONTENT_DOODAD);
	if($func == PAGE_LISTING)
	{
		$page->set(CONTENT_DOODAD,null);
	}
}
function SetMarginMenu($margin, $otherMargin1, $otherMargin2)
{
	RemoveListing($otherMargin1);
	RemoveListing($otherMargin2);

	if($margin!=null)
	{
		Load::page($margin)->set(CONTENT_DOODAD,PAGE_LISTING);
	}
}

//sets menu orientation.  o == 0 (vertical) or 1 (horizontal)
function set_orientation($o)
{
	$page_listing = Load::addon(PAGE_LISTING);
	$page_listing->set(PAGE_LIST_ORIENTATION,$o);
	//$error = $page_listing->is_valid();
}

function set_menu_location($menu_location)
{
	if($menu_location=='t')
	{
		set_orientation(1);
		SetMarginMenu(HEADER, LEFT_MARGIN, RIGHT_MARGIN);
	}
	else if($menu_location=='r')
	{
		set_orientation(0);
		SetMarginMenu(RIGHT_MARGIN, LEFT_MARGIN, HEADER);
	}
	else
	{
		set_orientation(0);
		SetMarginMenu(LEFT_MARGIN, RIGHT_MARGIN, HEADER);
	}
}

?>
