<?php
require_once('common/class/settings.class.php');
define('TEMPLATE_ALLOWED','TEMPLATE_ALLOWED');
define('STYLE_ALLOWED','STYLE_ALLOWED');
class PermissionSettings extends Settings
{
	function __construct($loc = 'site/config/permission_settings.php')
	{
	   parent::__construct($loc);
	}
	function get_default_settings()
	{
	    return array
		(
			TEMPLATE_ALLOWED => true,
			STYLE_ALLOWED => true,
		);
	}
    function validate($setting_name, $setting_value)
    {
		return null;
    }
    protected function get_input_internal($setting_name, $setting_value)
    {
        switch($setting_name)
		{
			case TEMPLATE_ALLOWED:
			{
				//adding a useless hidden input because at the time this set of settings were
				//made up entirely of checkboxes. That creates a problem.
				//if you click submit ona collection of checkboxrs and post the data
				//there will be no post at the other end as an unchecked checkbox doesn't post a value
				//extra hidden field overcomes this
			    return HtmlInput::get_checkbox_input($setting_name, $setting_value).HtmlInput::get_hidden_input('jancknakjna', 'acacacww', '');
			}
			case STYLE_ALLOWED:
			{
				//adding a useless hidden input because at the time this set of settings were
				//made up entirely of checkboxes. That creates a problem.
				//if you click submit ona collection of checkboxrs and post the data
				//there will be no post at the other end as an unchecked checkbox doesn't post a value
				//extra hidden field overcomes this
			    return HtmlInput::get_checkbox_input($setting_name, $setting_value).HtmlInput::get_hidden_input('jancknakjacsaccsana', 'acacacwwwwww', '');
			}
    		default :
    		{
    		    return parent::get_input_internal($setting_name, $setting_value);
    		}
		}
	}
	public function check_permission($feature_permission_name, $just_a_check = true, $msg = 'The feature is locked')
	{
		$features_permission = $this->get($feature_permission_name);
		if($just_a_check) return $features_permission;
		//not just a check
		if($features_permission == false) //if not permitted to use feature
		{
			//just exit for now
			echo($msg);
			exit();
		}
		//else do nothing. Just let things roll.
	}
}
?>