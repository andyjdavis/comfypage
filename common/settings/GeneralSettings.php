<?php
// This file is part of ComfyPage - http://comfypage.com
//
// ComfyPage is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ComfyPage is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ComfyPage.  If not, see <http://www.gnu.org/licenses/>.

/**
 * general settings manager
 *
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('common/class/settings.class.php');
define('PASSWORD', 'PASSWORD');
define('ADMIN_EMAIL', 'ADMIN_EMAIL');
define('PAGE_VIEW_LIMIT', 'PAGE_VIEW_LIMIT');
define('PAGE_VIEW_LAST_RESET', 'PAGE_VIEW_LAST_RESET');
define('TEMPLATE_IN_USE', 'TEMPLATE_IN_USE');
define('FEATURE_IMAGE', 'FEATURE_IMAGE');
define('LAST_LOGIN', 'LAST_LOGIN');
define('SITE_CREATED', 'SITE_CREATED');
define('NEW_SITE_ID', 'NEW_SITE_ID');
define('LANG', 'LANG');
define('TRACKING_CODE', 'TRACKING_CODE');
define('DONE_WIZARD', 'DONE_WIZARD');
define('NEED_DO_WIZARD', 'NEED_DO_WIZARD');
define('BRANDING', 'BRANDING');
define('MOVED_TO_OWN_DOMAIN', 'MOVED_TO_OWN_DOMAIN');

define('DEFAULT_ADMIN_EMAIL', 'you@example.com');
define('DEFAULT_PASSWORD', 'password');

class GeneralSettings extends Settings
{
    private $password_on_disk;
    function __construct($loc = 'site/config/general_settings.php')
    {
        parent::__construct($loc);
    }
    function get_default_settings()
    {
        $s = array
        (
            ADMIN_EMAIL => DEFAULT_ADMIN_EMAIL,
            PASSWORD => md5(DEFAULT_PASSWORD),
            NEW_SITE_ID => Globals::old_get_site_address(),
            PAGE_VIEW_LAST_RESET => strtotime('now'),
            TEMPLATE_IN_USE => 'Default',
            FEATURE_IMAGE => '',
            LAST_LOGIN => strtotime('now'),
            SITE_CREATED => strtotime('now'),
            DONE_WIZARD => false,
            NEED_DO_WIZARD => 'default',
            BRANDING => null,
            LANG => 'en',
            TRACKING_CODE => null,
            MOVED_TO_OWN_DOMAIN => false,
        );
        return $s;
    }
    protected function get_description_dictionary()
    {
        return array
        (
            ADMIN_EMAIL => "<span id='adEmLbl' class='translate_me'>Administrator's email address</span>",
            PASSWORD => "<span id='adPassLbl' class='translate_me'>Administrator's password. At least six characters.</span>",
            LANG => '<span id="adLangLbl" class=translate_me>What language should ComfyPages administrative pages be in?</span>',
            TRACKING_CODE => '<span id="adTrackLbl" class=translate_me>Tracking code. Provided by an analytics service to track your traffic. <a href="http://getclicky.com/35004" target="_blank">Get Clicky</a> or <a href="http://www.google.com/analytics/" target="_blank">Google Analytics</a></span>',
            LAST_LOGIN => LAST_LOGIN.' Date is '.date('d-m-Y', $this->get(LAST_LOGIN)), //include readable date in description
            SITE_CREATED => SITE_CREATED.' Date is '.date('d-m-Y', $this->get(SITE_CREATED)), //include readable date in description
        );
    }
    function validate($setting_name, $setting_value)
    {
        switch($setting_name)
        {
            case ADMIN_EMAIL :
            {
                return Validate::email($setting_value);
            }
            case PASSWORD :
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
            case LANG:
            {
                return Validate::language($setting_value);
            }
            case TRACKING_CODE :
            {
                return Validate::content($setting_value);
            }
        }
        return null;
    }
    protected function get_input_internal($setting_name, $setting_value)
    {
        switch($setting_name)
        {
    	    case PASSWORD :
    	    {
                return HtmlInput::get_password_input($setting_name, "");
            }
            case LANG:
            {
                    return HtmlInput::get_select_input($setting_name, $setting_value, array('en'=>'English','zh-cn'=>'Chinese (simplified)','es'=>'Espanol','fr'=>'Francais','hi'=>'Hindi &#2361;&#2367;&#2344;&#2381;&#2342;&#2368;','id'=>'Indonesian','ko'=>'Korean','pt'=>'Portugues','ru'=>'Russian &#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081; &#1103;&#1079;&#1099;&#1082;','sr'=>'Serbian Srpski jezik','th'=>'Thai'));
            }
            case TRACKING_CODE:
            {
                    return HtmlInput::get_textarea_input($setting_name, $setting_value, 5, 50);
            }
            case MOVED_TO_OWN_DOMAIN:
            {
                    return HtmlInput::get_checkbox_input($setting_name, $setting_value);
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
	$this->password_on_disk = $this->get(PASSWORD);
	return parent::process_post($posted_data, $setting_names);
    }
    //need to take care of a hashed password
    //this technique relies on the fact that an md5ed password walidates during the parent::set() call
    public function set($setting_name, $setting_value)
    {
        //if it is a blank password then don't update the password
        //or if it is the same password typed in again then don't update
        if($setting_name == PASSWORD && (empty($setting_value) || md5($setting_value) == $this->get(PASSWORD)))
        {
            return;
        }
        if($setting_name == PASSWORD)
        {
            $temp = $this->validate(PASSWORD, $setting_value);
            if(empty($temp) == false)
            {
                $this->errors[PASSWORD] = $temp;
                return;
            }
            else
            {
                $setting_value = md5($setting_value); //hash the password
            }
        }
        parent::set($setting_name, $setting_value);
    }
    function operating_under_subdomain()
    {
        return !$this->get(MOVED_TO_OWN_DOMAIN);
    }
    function operating_under_domain()
    {
        return $this->get(MOVED_TO_OWN_DOMAIN);
    }
    function set_done_wizard($done, $wizard = 'default')
    {
        $this->set(DONE_WIZARD, $done);
        $this->set(NEED_DO_WIZARD, $wizard);
    }
}
?>