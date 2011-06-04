<?php
require_once('common/class/settings.class.php');
define('ADD_PAGE_AWARD', 'ADD_PAGE_AWARD');
define('EDIT_PAGE_AWARD', 'EDIT_PAGE_AWARD');
define('UPLOAD_IMAGE_AWARD', 'UPLOAD_IMAGE_AWARD');
define('EDIT_BORDER_AWARD', 'EDIT_BORDER_AWARD');
define('SET_ADD_ON_AWARD', 'SET_ADD_ON_AWARD');
define('PASSWORD_PROTECT_PAGE_AWARD', 'PASSWORD_PROTECT_PAGE_AWARD');
define('ADD_PRODUCT_AWARD', 'ADD_PRODUCT_AWARD');
define('EDIT_STYLES_AWARD', 'EDIT_STYLES_AWARD');
define('ADD_TRACKING_CODE_AWARD', 'ADD_TRACKING_CODE_AWARD');
define('NUMBER_OF_LEVELS', 2);
class AwardSettings extends Settings
{
	function __construct($loc = 'site/config/awards.php')
	{
	   parent::__construct($loc);
	}
	function get_default_settings()
	{
		$s = array
		(
			ADD_PAGE_AWARD => false,
			EDIT_PAGE_AWARD => false,
			UPLOAD_IMAGE_AWARD => false,
			EDIT_BORDER_AWARD => false,
			SET_ADD_ON_AWARD => false,
			PASSWORD_PROTECT_PAGE_AWARD => false,
			ADD_PRODUCT_AWARD => false,
			EDIT_STYLES_AWARD => false,
			ADD_TRACKING_CODE_AWARD => false,
		);
		return $s;
	}
	function bestow_award($award_name)
	{
		$this->set($award_name, true);
	}
	protected function get_description_dictionary()
	{
		return array
		(
			ADD_PAGE_AWARD => 'Add a new page',
			EDIT_PAGE_AWARD => 'Edit and save a page',
			UPLOAD_IMAGE_AWARD => 'Upload an file or image',
			EDIT_BORDER_AWARD => 'Edit the borders (header, footer, left or right margin)',
			SET_ADD_ON_AWARD => 'Add a ComfyPage Add-on to a page',
			PASSWORD_PROTECT_PAGE_AWARD => 'Password protect a page',
			ADD_PRODUCT_AWARD => 'Create a product in the shop manager',
			EDIT_STYLES_AWARD => 'Edit and save the styles of your ComfyPage',
			ADD_TRACKING_CODE_AWARD => 'Insert visitor tracking code',
		);
	}
	function validate($setting_name, $setting_value)
	{
		return null;
	}
	protected function get_input_internal($setting_name, $setting_value, $input_name = null)
	{
	    //adding a crap hidden input to ensure something is always posted becuase unticked checkboxes don't post nothing
	    return HtmlInput::get_checkbox_input($setting_name, $setting_value).'<input type="hidden" name="blah" value="blah" />';
	}
	/*public function achieved_first_trophy()
	{
		return $this->get(ADD_PAGE_AWARD) && $this->get(EDIT_PAGE_AWARD) && $this->get(UPLOAD_IMAGE_AWARD) && $this->get(EDIT_BORDER_AWARD);
	}*/
	public function get_level_achieved()
	{
	    //for($i=NUMBER_OF_LEVELS-1; $i>=0; $i--)
	    for($i=0; $i<NUMBER_OF_LEVELS; $i++)
	    {
	        $awards = $this->get_awards_in_level($i);
	        //$achieved = true;
	        foreach($awards as $award)
	        {
	            if($this->get($award) == false)
	            {
	            	//$achieved = false;
					return $i - 1;
				}
			}
			/*if($achieved)
			{
			    return $i;
			}*/
	    }
	    //achieved all levels
	    return NUMBER_OF_LEVELS - 1;
		/*
	    if($this->get(SET_ADD_ON_AWARD) && $this->get(PASSWORD_PROTECT_PAGE_AWARD) && $this->get(ADD_PRODUCT_AWARD) && $this->get(EDIT_STYLES_AWARD) && $this->get(ADD_TRACKING_CODE_AWARD))
	    {
			return 2;
		}
		else if($this->get(ADD_PAGE_AWARD) && $this->get(EDIT_PAGE_AWARD) && $this->get(UPLOAD_IMAGE_AWARD) && $this->get(EDIT_BORDER_AWARD))
		{
		    return 1;
		}
		else
		{
		    return 0;
		}
		*/
	}
	public function get_awards_in_level($level)
	{
	    switch($level)
	    {
			case 0:
			{
				return array(ADD_PAGE_AWARD, EDIT_PAGE_AWARD, UPLOAD_IMAGE_AWARD, EDIT_BORDER_AWARD);
			}
			case 1:
			{
				return array(SET_ADD_ON_AWARD, PASSWORD_PROTECT_PAGE_AWARD, ADD_PRODUCT_AWARD, EDIT_STYLES_AWARD, ADD_TRACKING_CODE_AWARD);
			}
		}
	}
}
?>