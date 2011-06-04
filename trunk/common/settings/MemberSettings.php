<?php
define('MEMBERS_AREA_PASSWORD', 'MEMBERS_AREA_PASSWORD');
define('MEMBERS_ONLY_PAGES', 'MEMBERS_ONLY_PAGES');
define('MAKE_PRIVATE', 'private');
define('MAKE_PUBLIC', 'public');
require_once('common/class/settings.class.php');
class MemberSettings extends Settings
{
	function MemberSettings($loc = 'site/config/members_settings.php')
	{
	   parent::Settings($loc);	
	}
	function get_default_settings()
	{
	    require_once('common/utils/PasswordGenerator.php');
		$s = array
		(
			MEMBERS_AREA_PASSWORD => PasswordGenerator::generate(),
			MEMBERS_ONLY_PAGES => array(),
		);
		return $s;
	}
    protected function get_description_dictionary()
	{
		return array
		(
		MEMBERS_AREA_PASSWORD => 'Password for protected pages',
		);
	}
    function validate($setting_name, $setting_value)
    {
        switch($setting_name)
        {
        	case MEMBERS_AREA_PASSWORD :
			{
				return Validate::password($setting_value);
			}
        }
        return null;
    }
    function is_members_only_page($id)
	{
		$mop = $this->get(MEMBERS_ONLY_PAGES);
		return in_array($id, $mop);
	}
	/*
	public function process_post($posted_data, $setting_names = null)
	{
		$make_private = Globals::get_param(MAKE_PRIVATE, $posted_data);
		if(empty($make_private) == false)
		{
			//make page private
			$this->add_members_only_page($make_private);
			require_once('common/utils/Broadcast.php');
			Globals::clearContentDependentCaches();
			return 'Page is now password protected';
		}
		$make_public = Globals::get_param(MAKE_PUBLIC, $posted_data);
		if(empty($make_public) == false)
		{
			//make page public
			$this->delete_members_only_page($make_public);
			require_once('common/utils/Broadcast.php');
			Globals::clearContentDependentCaches();
			return 'Page is now public';
		}
		return parent::process_post($posted_data, $setting_names);
	}*/
	function process_protections($posted_data)
	{
        $make_private = Globals::get_param(MAKE_PRIVATE, $posted_data);
		if(empty($make_private) == false)
		{
			//make page private
			$this->add_members_only_page($make_private);
			require_once('common/utils/Broadcast.php');
			Globals::clearContentDependentCaches();
			return 'Page is now password protected';
		}
		$make_public = Globals::get_param(MAKE_PUBLIC, $posted_data);
		if(empty($make_public) == false)
		{
			//make page public
			$this->delete_members_only_page($make_public);
			require_once('common/utils/Broadcast.php');
			Globals::clearContentDependentCaches();
			return 'Page is now public';
		}
		//return parent::process_post($posted_data, $setting_names);
	}
	function add_members_only_page($page_id)
	{
	    $this->add_item_to_array_setting(MEMBERS_ONLY_PAGES, $page_id);
	}
	function delete_members_only_page($page_id)
	{
	    $this->delete_item_from_array_setting(MEMBERS_ONLY_PAGES, $page_id);
	}
	function members_only_check_for_pages($page_id)
	{
		//if it is a public page
		if($this->is_members_only_page($page_id) == false) return; //just continue
		//if logged in
		if($this->logged_in()) return;
		//not logged in
		Globals::set_coming_from();
		Globals::redirect('members_login.php');
	}
	//Changed menu so logged in member's now get logout link there rather than in middle of screen
	/*function get_member_summary_html($content_id)
	{
		if($this->logged_in() == false)
		{
			return null;
		}
		if(Login::logged_in(false))
		{
			//these are in the toolbar
			//$summary = 'You are logged in as admin.<br /><a href="members.php">Password Protection</a>';
		}
		else
		{
			$summary = '<a href="members_logout.php">Log out</a>';
		}
		return "<span id='member_summary' class='translate_me' style=''>$summary</span>";
	}*/
	public function logged_in($redirect = false)
	{
		//if logged in as admin
		if(Login::logged_in(false)) return true;
		$key = $this->get_key();
		if(array_key_exists($key, $_SESSION) && $_SESSION[$key] != null)
		{
			return true;
		}
		else //redirect to log in
		{
			if($redirect == true)
			{
    			//setComingFrom();
				//redirect('members_login.php');
				Globals::set_coming_from();
				Globals::redirect('members_login.php');
			}
			else
			{
				return false;
			}
		}
	}
	function get_key()
	{
		$site_address = Load::general_settings(NEW_SITE_ID);
		return "$site_address-membersarea";
	}
	function log_in($password)
	{
		$correct_pass = $this->get(MEMBERS_AREA_PASSWORD);
		if($password != $correct_pass)
		{
			return false;
		}
    	//login verified
		$key = $this->get_key();
		$_SESSION[$key] = true; //set session var
		if(array_key_exists(COMING_FROM, $_SESSION) && strlen($_SESSION[COMING_FROM])>0)
		{
			//redirect($_SESSION[COMING_FROM]);
			Globals::redirect($_SESSION[COMING_FROM]);
		}
		else
		{
			Globals::redirect('index.php');
		}
	}
	function log_out()
	{
		$key = $this->get_key();
		$_SESSION[$key] = null;
	}
}
?>