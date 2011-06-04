<?php
require_once('common/class/users_item.class.php');
require_once('common/utils/Validate.php');
define('POLL_VALS', 'POLL_VALS');
define('POLL_DESC', 'POLL_DESC');
define('POLL_COLOURS','POLL_COLOURS');
define('POLL_LABELS','POLL_LABELS');
define('POLL_CHILDREN','POLL_CHILDREN');
define('POLL_ON_SELECT', 'POLL_ON_SELECT');
class Poll extends UsersItem
{
	function __construct($id, $saving_location = null)
	{
		if(empty($saving_location))
		{
			$saving_location = "site/store/polls/$id.php";
		}
		parent::__construct($saving_location, $id);
	}
	function get_default_settings()
	{
		$s = array
		(
			POLL_VALS => array(),
			POLL_DESC => null,
			POLL_COLOURS => array(),
			POLL_LABELS => array(),
			POLL_CHILDREN => array(),
			POLL_ON_SELECT => array(),
		);
		return $s;
	}
	function validate($setting_name, $setting_value)
	{
		switch($setting_name)
        {
            /*case CONTENT_DOODAD :
            {
                return Validate::addon($setting_value);
            }*/
		}
		return null;
	}
	function get_input_internal($setting_name, $setting_value, $input_name)
    {
       /* if($setting_name == RAW_CONTENT)
        {
			return HtmlInput::get_editor_html($input_name, $setting_value);
		}
        else if($setting_name == CONTENT_EMBED)
        {
            return HtmlInput::get_textarea_input($input_name, $setting_value, 10, 120);
        }
        else if($setting_name == CONTENT_DOODAD)
        {
            return Addon::get_addon_select_box($input_name, $setting_value);
        }*/
        return parent::get_input_internal($setting_name, $setting_value, $input_name);
    }
	function get_vals()
	{
		return $this->get(POLL_VALS);
	}

	function get_description()
	{
		return $this->get(POLL_DESC);
	}

	function get_colours()
	{
		return $this->get(POLL_COLOURS);
	}

	function get_labels()
	{
		return $this->get(POLL_LABELS);
	}

	function get_children()
	{
		return $this->get(POLL_CHILDREN);
	}

	function get_on_select_urls()
	{
		return $this->get(POLL_ON_SELECT);
	}

	function increment_val($id)
	{
		$vals = $this->get_vals();

		if(array_key_exists($id, $vals))
		{
			$vals[$id]++;
		}
		else
		{
			$vals[$id] = 1;
		}

		$this->set(POLL_VALS, $vals);
	}
}
?>
