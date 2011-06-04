<?php
require_once('common/class/saveable_base.class.php');
/*
Maintain and retrieve static instances of settings/pages/products etc.
Call the relevant function like Load::general_settings() to get the whole object.
Or to get a specific setting use Load::general_settings(SETTING_NAME);
*/
class Load
{
    //returns static instance of GeneralSettings
    public static function general_settings($setting_name = null)
    {
        static $instance;
        return Load::get_settings('common/settings/GeneralSettings.php', 'GeneralSettings', $instance, null, $setting_name);
    }
    public static function member_settings()
    {
        static $instance;
        return Load::get_settings('common/settings/MemberSettings.php', 'MemberSettings', $instance);
    }
    public static function counter_settings()
    {
        static $instance;
        return Load::get_settings('common/settings/CounterSettings.php', 'CounterSettings', $instance);
    }
	public static function email_settings()
    {
        static $instance;
        return Load::get_settings('common/settings/EmailSettings.php', 'EmailSettings', $instance);
    }
    public static function permission_settings()
    {
        static $instance;
        return Load::get_settings('common/settings/PermissionSettings.php', 'PermissionSettings', $instance);
    }
    public static function portal_settings()
    {
        static $instance;
        return Load::get_settings('common/settings/PortalSettings.php', 'PortalSettings', $instance);
    }
    public static function paypal_settings()
    {
        static $instance;
        return Load::get_settings('common/settings/PayPalSettings.php', 'PayPalSettings', $instance);
    }
	public static function award_settings()
    {
        static $instance;
        return Load::get_settings('common/settings/AwardSettings.php', 'AwardSettings', $instance);
    }
    public static function payment_general_settings($setting_name = null)
    {
        static $instance;
        return Load::get_settings('common/settings/PaymentGeneralSettings.php', 'PaymentGeneralSettings', $instance, null, $setting_name);
    }
    public static function page_store()
    {
        //can't think of a better place to include this file
        //doens't seem ideal to me but I don't want to put it in globals
        //as the page class isn't always needed
        require_once('common/users_items/Page.php');
        static $instance;
        return Load::get_settings('common/stores/PageStore.php', 'PageStore', $instance);
    }
    public static function page($id)
    {
        return Load::load_stored_item('common/users_items/Page.php', 'Page', $id);
    }
    public static function product($id)
    {
        return Load::load_stored_item('common/users_items/Product.php', 'Product', $id);
    }
    public static function user($id)
    {
        return Load::load_stored_item('common/users_items/UserLogin.php', 'UserLogin', $id);
    }
    public static function addon($id)
    {
    	if($id)
		{
		    //Class name is the add-on's ID with underscores instead of spaces.
		    $class_name = str_replace(' ', '_', $id);
		    $add_on_location = "common/functions/$id/$id.php";
		    //if there is a custom addon to be loaded
		    if(file_exists("site/functions/$id/$id.php"))
		    {
                $add_on_location = "site/functions/$id/$id.php";
			}
		    return Load::load_stored_item($add_on_location, $class_name, $id);
        }
    }
    //Load a payment processor. Loads the one in use if none specified.
    public static function payment_processor($id = null)
    {
        if(empty($id))
        {
			$id = Load::payment_general_settings(SELECTED_PROCESSOR);
		}
        require_once('common/class/payment_processor.class.php');
	    $class_name = str_replace(' ', '_', $id);
	    return Load::load_stored_item("common/payment_processors/$id/$id.php", $class_name, $id);
    }
    public static function product_store()
    {
		//can't think of a better place to include this file
		//doens't seem ideal to me but I don't want to put it in globals
		//as the class isn't always needed
        require_once('common/users_items/Product.php');
        static $instance;
        return Load::get_settings('common/stores/ProductStore.php', 'ProductStore', $instance);
    }
    public static function user_store()
    {
		//can't think of a better place to include this file
		//doens't seem ideal to me but I don't want to put it in globals
		//as the class isn't always needed
        require_once('common/users_items/UserLogin.php');
        static $instance;
        return Load::get_settings('common/stores/UserStore.php', 'UserStore', $instance);
    }
    //generic method for loading stored items. Uses an array to store multiple items of the same type.
    private static function load_stored_item($include_file_path, $class_name, $id)
    {
        static $instance = array();
        //store the item in memeory in the array keyed on the id
        return Load::get_settings($include_file_path, $class_name, $instance[$id], $id);
	}
    //Generic singleton instance loader
    //Used to load singletone instances of any object ($class_name)
    //Loads an instance of $class_name into $instance(passed by reference)
    //$param1 is passed to the constructor of the object
    //if a setting name is specified then the setting will be returned instead of the whole object
    private static function get_settings($include_file_path, $class_name, &$instance, $param1 = null, $setting_name = null)
    {
        if(is_object($instance) == false)
        {
            //load the class definition
	    if( !file_exists($include_file_path) ) {
		return null;
	    }
            require_once($include_file_path);
            //load an instance of the class
            if($param1 == null)
            {
            	$instance = new $class_name; //yes, you can do this
            }
            else
            {
				$instance = new $class_name($param1);
			}
        }
        if(empty($setting_name)) //if no setting name specified
        {
			return $instance; //return whole object
		}
		else //have specified a setting name to retrieve
		{
			return $instance->get($setting_name); //return value of speciufic setting
		}
    }
}
?>