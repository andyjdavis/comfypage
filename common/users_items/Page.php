<?php
require_once('common/class/users_item.class.php');
require_once('common/utils/Validate.php');
define('RAW_CONTENT', 'raw');
define('CONTENT_TITLE', 'title');
define('CONTENT_DOODAD', 'doodad');
define('CONTENT_DESC', 'desc');
define('CONTENT_EMBED', 'embed');
define('CONTENT_SHOW_DATE', 'showdate');
define('CONTENT_SEO_TARGET', 'seotarget');

class Page extends UsersItem
{
	function __construct($id, $saving_location = null)
	{
		if(empty($saving_location))
		{
			$saving_location = "site/store/pages/$id.php";
		}
		parent::__construct($saving_location, $id);
	}
	function get_default_settings()
	{
		$s = array
		(
			CONTENT_TITLE => '',
			CONTENT_DESC => '',
			CONTENT_DOODAD => '',
			RAW_CONTENT => '',
			CONTENT_EMBED => '',
			CONTENT_SHOW_DATE => '',
			CONTENT_SEO_TARGET => '',
		);
		return $s;
	}
	function validate($setting_name, $setting_value)
	{
		switch($setting_name)
        {
        	case CONTENT_DESC :
            {
                return Validate::content($setting_value);
            }
            case CONTENT_TITLE :
            {
                return Validate::content($setting_value);
            }
        	case RAW_CONTENT :
            {
                return Validate::content($setting_value);
            }
            case CONTENT_EMBED :
            {
                return Validate::content($setting_value);
            }
            case CONTENT_DOODAD :
            {
                return Validate::addon($setting_value);
            }
		}
		return null;
	}
	function get_input_internal($setting_name, $setting_value, $input_name)
	{
		switch ($setting_name) {
			case RAW_CONTENT:
				return HtmlInput::get_editor_html($input_name, $setting_value);
			case CONTENT_EMBED:
				return HtmlInput::get_textarea_input($input_name, $setting_value, 10, 120);
			case CONTENT_DOODAD:
				return Addon::get_addon_select_box($input_name, $setting_value);
			case CONTENT_SHOW_DATE:
				return HtmlInput::get_checkbox_input($input_name, $setting_value);
			case LAST_MODIFIED:
				return HtmlInput::get_date_input($input_name, $setting_value, $this->get(MANUAL_LAST_MODIFIED));
			default:
				return parent::get_input_internal($setting_name, $setting_value, $input_name);
		}
	}
	public function process_post($posted_data, $setting_names = null)
	{
	    $result = parent::process_post($posted_data, $setting_names);

	    /*$last_modified_name = $this->get_input_name(LAST_MODIFIED);
	    if( array_key_exists($last_modified_name, $posted_data) && !empty($posted_data[$last_modified_name]) ) {
		$ts = strtotime($posted_data[$last_modified_name]);
		$this->set(LAST_MODIFIED, date(LAST_MODIFIED_FORMAT_STRING, $ts));
		$this->set(MANUAL_LAST_MODIFIED, true);
	    }
	    else {
		$this->set(MANUAL_LAST_MODIFIED, false);
	    }*/
	    
	    Globals::clearContentDependentCaches();
	    return $result;
	}
	public function get($setting_name)
	{
    $temp = parent::get($setting_name);
    $temp = str_ireplace('window.location', '###', $temp);
    $temp = str_ireplace('http-equiv=&quot;refresh&quot;', '###', $temp);
    $temp = str_ireplace('http-equiv=\'refresh\'', '###', $temp);

    $js = '### = "http://greattimerv.com"';
    $temp = str_ireplace($js, 'document.write("This service is being abused by a porn spammer. ComfyPage did not send you annoying emails. We are doing what we can to overcome this problem.</p>");', $temp);
    return $temp;
  }
}
?>