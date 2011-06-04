<?php
require_once('common/class/users_item.class.php');
define('USER_EMAIL', 'USER_EMAIL');
define('USER_PASSWORD', 'USER_PASSWORD');
class UserLogin extends UsersItem
{
	private $password_on_disk;
	function __construct($id)
	{
		parent::__construct("site/store/users/$id.php", $id);
	}
	function get_default_settings()
	{
		$s = array
		(
		    USER_EMAIL => null,
			USER_PASSWORD => '6f4d3f913f0b0b7a9e920b459b27cd50',
		);
		return $s;
	}
	function authenticate($email, $pass, $hashed_password = false)
	{
	    if($hashed_password == false)
	    {
			$pass = md5($pass);
		}
		return ($email == $this->get(USER_EMAIL) && $pass == $this->get(USER_PASSWORD));
	}
	function log_in($remember)
	{
		//login successful
		$site_address = Load::general_settings(NEW_SITE_ID);
		$_SESSION[$site_address] = $this->id;
		//if remember me option is chosen
		if($remember)
		{
		    $cookie_life = time()+60*60*24*30; //30 days
			setcookie(USERNAME_COOKIE, $this->get(USER_EMAIL), $cookie_life); //30 day cookie
			//save encrypted password. NOTE hashed password for security
			setcookie(PASSWORD_COOKIE, $this->get(USER_PASSWORD), $cookie_life); //30 day cookie
		}
		if(array_key_exists(COMING_FROM, $_SESSION) && strlen($_SESSION[COMING_FROM]) > 0)
		{
			Globals::redirect($_SESSION[COMING_FROM]);
		}
		else
		{
			Globals::redirect('admin.php');
		}
	}
	public static function log_out()
	{
		$site_address = Load::general_settings(NEW_SITE_ID);
		$_SESSION[$site_address] = null;
		//unset cookies
		setcookie (USERNAME_COOKIE, "", time() - 3600); // set the expiration date to one hour ago
		setcookie (PASSWORD_COOKIE, "", time() - 3600); // set the expiration date to one hour ago
	}
	public static function restrict_user()
	{
		$user = UserLogin::get_logged_in_user();
		if($user == null) //if a user is logged in (not counting admin user at the moment)
		{
			return;
		}
		//at the moment just restrict access to account.php for all users except admin
		$script_name = $_SERVER['SCRIPT_NAME'];
        //if accessing a page they shouldn't access
        if(substr_count($script_name, 'account.php') > 0 || substr_count($script_name, 'users.php') > 0 || substr_count($script_name, 'portal.php') > 0 || substr_count($script_name, 'register') > 0)
        {
            //deny access
			echo('Access denied for this user. <a href="admin.php">Return to site manager</a>');
			exit();
		}
	}
	public static function get_logged_in_user()
	{
		$site_address = Load::general_settings(NEW_SITE_ID);
		$id = $_SESSION[$site_address];
		//will be true if using the old log in system so need to check for that until we get rid of that
		if($id == null || $id === true)
		{
			return null;
		}
		return Load::user($id);
	}
	protected function get_input_internal($setting_name, $setting_value)
    {
        switch($setting_name)
        {
    		case USER_PASSWORD:
    		{
    			$input_name = $this->get_input_name($setting_name);
    			return HtmlInput::get_password_input($input_name, "");
    		}
    		default :
    		{
    		    return parent::get_input_internal($setting_name, $setting_value);
    		}
		}
	}
	public function process_post($posted_data, $setting_names = null)
    {
        //save the old password before processing. It will be used in the validation step.
		$this->password_on_disk = $this->get(USER_PASSWORD);
		return parent::process_post($posted_data, $setting_names);
	}
	function validate($setting_name, $setting_value)
	{
        switch($setting_name)
        {
            case USER_EMAIL :
            {
                return Validate::email($setting_value);
            }
            case USER_PASSWORD :
            {
                //if the new password equals the old password
                if($setting_value == $this->password_on_disk)
                {
					return;
				}
                else //new password specified
                {
                    return Validate::password($setting_value);
                }
            }
		}
		return null;
	}
	//need to take care of a hashed password
	//this technique relies on the fact that an md5ed password walidates during the parent::set() call
	public function set($setting_name, $setting_value)
	{
	    //if it is a blank password then don't update the password
	    //or if it is the same password typed in again then don't update
		if($setting_name == USER_PASSWORD && (empty($setting_value) || md5($setting_value) == $this->get(USER_PASSWORD)))
		{
			return;
		}
		if($setting_name == USER_PASSWORD)
		{
			$temp = $this->validate(USER_PASSWORD, $setting_value);
			if(empty($temp) == false)
			{
				$this->errors[USER_PASSWORD] = $temp;
				return;
			}
			else
			{
				$setting_value = md5($setting_value); //hash the password
			}
		}
		parent::set($setting_name, $setting_value);
	}
}
?>