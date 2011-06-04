<?php
define('USERNAME_COOKIE', 'username');
define('PASSWORD_COOKIE', 'blogspot'); //a cunning name to mask the cookie
class Login
{
	function log_in($email, $password, $remember = false)
	{
	    //TODO Eventually we will stop using the general settings log in details
	    //All users will use the UserLogin class. For now rely on the old system.
	    //log in using the old way with the general settings
		if(Login::check_login($email, $password))
		{
			//login successful
			$site_address = Load::general_settings(NEW_SITE_ID);
			$_SESSION[$site_address] = true;
			Login::i_logged_in();
			//if remember me option is chosen
			if($remember)
			{
			    $cookie_life = time()+60*60*24*30; //30 days
                            setcookie(USERNAME_COOKIE, $email, $cookie_life); //30 day cookie
                            //save encrypted password. NOTE hashed password for security
                            setcookie(PASSWORD_COOKIE, md5($password), $cookie_life); //30 day cookie
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
		else //main log in failed
		{
		    //try the users' logins
		    $us = Load::user_store();
			$user = null;
			$log_in_result = false;
			foreach($us->get_used_ids_in_store() as $id)
			{
				$user = new UserLogin($id);
				if($user->authenticate($email, $password))
				{
					Login::i_logged_in();
				    $user->log_in($remember);
					$log_in_result = true;
					break;
				}
			}
		    //search the store for the correct user and log them in
		    if($log_in_result)
		    {
				//nothing. above function redirects on success
			}
			else
			{
				return false;
			}
		}
	}
	public static function logged_in($redirect = true)
	{
            $site_address = Load::general_settings(NEW_SITE_ID);

            if($_SESSION && array_key_exists($site_address, $_SESSION) && $_SESSION[$site_address] != null)
            {
                require_once('common/users_items/UserLogin.php');
                UserLogin::restrict_user(); //only let user see pages they are allowed to see

                return true;
            }
            //if cookies are set
            else if(isset($_COOKIE[USERNAME_COOKIE]) && isset($_COOKIE[PASSWORD_COOKIE]))
            {
                //if cookie details are correct log in details
                    if(Login::check_login($_COOKIE[USERNAME_COOKIE], $_COOKIE[PASSWORD_COOKIE], true))
                    {
                        //TODO manage permissions here
                        //log in to session so we aren't using cookie log in all the time. Makes me feel better.
                            $_SESSION[$site_address] = true;
                            return true;
                    }
                    else //check users' logins
                    {
                            //check user logins
                            $us = Load::user_store();
                            $user = null;
                            $log_in_result = false;
                            foreach($us->get_used_ids_in_store() as $id)
                            {
                                    $user = new UserLogin($id);
                                    if($user->authenticate($_COOKIE[USERNAME_COOKIE], $_COOKIE[PASSWORD_COOKIE], true))
                                    {
                                        $_SESSION[$site_address] = $user->id;
                                            $log_in_result = true;
                                            break;
                                    }
                            }
                            return $log_in_result;
                    }
                    return false;
            }
            else //redirect to log in
            {
                    if($redirect == true)
                    {
                            Globals::set_coming_from();
                            Globals::redirect('login.php');
                    }
                    else
                    {
                            return false;
                    }
            }
	}
	public static function i_logged_in()
	{
		$time = strtotime('now');
		$gs = Load::general_settings();
		$gs->set(LAST_LOGIN, $time);
	}
	public static function log_out()
	{
	    //old way of loggin out
		$site_address = Load::general_settings(NEW_SITE_ID);
		$_SESSION[$site_address] = null;
		//unset cookies
		setcookie (USERNAME_COOKIE, "", time() - 3600); // set the expiration date to one hour ago
		setcookie (PASSWORD_COOKIE, "", time() - 3600); // set the expiration date to one hour ago
		//new way of logging out for users
		//TODO eventually get rid of the above stuff and just have below stuff
		require_once('common/users_items/UserLogin.php');
	    UserLogin::log_out();
	}
	private static function check_login($email, $password, $hashed_password = false)
	{
	    $gs = Load::general_settings();
	    $email_on_disk = $gs->get(ADMIN_EMAIL);
	    $password_on_disk = $gs->get(PASSWORD);
		$email_correct = (strtolower($email) == strtolower($email_on_disk));
		//if password is not hashed yet. Will already be hashed if pulled out of a cookie.
		if($hashed_password == false)
		{
		    //hash it
            $password = md5($password);
		}
		$password_correct = ($password == $password_on_disk);
		return ($email_correct && $password_correct);
	}
}
?>