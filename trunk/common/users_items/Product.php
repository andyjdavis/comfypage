<?php
define('PRODUCT_TITLE', 'PRODUCT_TITLE');
define('PRODUCT_RAW', 'PRODUCT_RAW');
define('PRODUCT_PRICE', 'PRODUCT_PRICE');
define('PRODUCT_SELLER_PRODUCT_ID', 'PRODUCT_SELLER_PRODUCT_ID');
define('PRODUCT_IMAGE', 'PRODUCT_IMAGE');
require_once('common/class/users_item.class.php');
class Product extends UsersItem
{
	function __construct($id, $saving_location = null)
	{
		if(empty($saving_location))
		{
			$saving_location = "site/store/products/$id.php";
		}
	   parent::__construct($saving_location, $id);
	}
	function get_default_settings()
	{
		$s = array
		(
		    PRODUCT_TITLE => null,
		    PRODUCT_PRICE => 1,
		    PRODUCT_SELLER_PRODUCT_ID => null,
		    PRODUCT_IMAGE => null,
		    PRODUCT_RAW => null,
		);
		return $s;
	}
	function validate($item_name, $item_value)
	{
	    if($item_name == PRODUCT_PRICE)
	    {
			return Validate::price($item_value);
		}
		else if($item_name == PRODUCT_IMAGE)
		{
		    if(empty($item_value) == false) //if they specified something
			{
				if(file_exists($item_value) == false)
				{
					return 'Selected image does not exist';
				}
			}
		}
		return null;
	}
	function get_input_internal($setting_name, $setting_value, $input_name)
    {
        if($setting_name == PRODUCT_RAW)
        {
			return HtmlInput::get_editor_html($input_name, $setting_value);
		}
        return parent::get_input_internal($setting_name, $setting_value, $input_name);
    }
    function get_thumb_path()
	{
		return "site/store/products/images/$this->id.jpg";
	}
	function delete_thumb()
	{
		$path = $this->get_thumb_path();
		if(file_exists($path))
		{
			unlink($path);
		}
	}
	function get_thumb_html($default = '')
	{
		$img = $this->get_thumb_path();
		if(file_exists($img))
		{
            $default = "<img height=75 src='$img'>";
		}
		return $default;
	}
	function get_image_html($default = '')
	{
		$img = $this->get(PRODUCT_IMAGE);
		if(file_exists($img))
		{
            $default = "<img height=250 src='$img'>";
		}
		return $default;
	}
}
?>