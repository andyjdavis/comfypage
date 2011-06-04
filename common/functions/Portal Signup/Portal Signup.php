<?php
define('PORTAL_SIGNUP_PASSWORD','PORTAL_SIGNUP_PASSWORD');
define('PORTAL_SIGNUP_WIZARD','WIZ');
define('PORTAL_REQUIRE_PASSWORD','REQUIRE_PASSWORD');
define('PORTAL_LINK','LINK');
class Portal_Signup extends Addon
{
	function __construct()
	{
		parent::__construct(true);
	}
	public function get_addon_description()
    {
		return 'Your visitors can create their own subdomain within your portal if they have the password that you provide.';
	}
    public function get_instructions()
    {
    	return 'Visitors can sign up for a ComfyPage that is a part of your portal. You can specify a password that is required to sign up.';
	}
	function RemoveSubdomain($s)
	{
		//if is a comfypage domain
		if(strpos($s, '.comfypage.com'))
		{
			if(substr_count($s, '.')==2)
			{
				$s = substr($s, strpos($s, '.')+1);
			}
			return $s;
		}
		else //not a comfypage domain
		{
			return $s; //return original
		}
	}
	public function get_first_stage_output($additional_inputs, $email=null, $password=null, $sitename=null, $portal_password = null, $confirm_password = null, $lang=null)
    {
    	require_once('common/lib/form_spam_blocker/fsbb.php');
		$hiddenTags = get_hidden_tags();
		$parent_site_id = Load::general_Settings(NEW_SITE_ID);
		$parent_site_id = $this->RemoveSubdomain($parent_site_id);

		$portal_password_input = null;
		if($this->get(PORTAL_REQUIRE_PASSWORD))
		{
			$portal_password_input = <<<END
<tr>
	<td valign=top align=right><p style="font-weight:bold;"><span id=SignUpPortPass class=translate_me>Portal password</span></p></td>
	<td><input type=password name=portal_password value="$portal_password"><br />
	<span style="font-size:small;"><span id=SignUpPortPassNote class=translate_me>Provided by the administrator</span></span></td>
</tr>
END;
	}
	//$lang_select = get_general_setting_html_input(LANG, $lang);
	$gs = Load::general_settings();
	$lang_select = $gs->get_input(LANG, $lang);
	return <<<END
<p style="text-align:center;">By signing up you agree to the <a target="_blank" href="http://comfypage.com/index.php?content_id=39">ComfyPage terms and conditions</a></p>
<form name=signup_form action=#signupAnchor>
$hiddenTags
$additional_inputs
<table align=center cellpadding=5 border=0 width=650px>
	<tr>
		<td align=right><p style="font-weight:bold;"><span id=signupEm class=translate_me>Email</span></p></td>
		<td><p><input name=email value="$email"><br /><br /></p></td>
	</tr>
	<tr>
		<td align=right><p style="font-weight:bold;"><span id=signupPa class=translate_me>Choose password</span></p></td>
		<td><p><input name=p1 type=password value="$password"></p></td>
	</tr>
	<tr>
		<td align=right><p style="font-weight:bold;"><span id=signupcPa class=translate_me>Confirm password</span></p></td>
		<td><p><input name=p2 type=password value="$confirm_password"></p></td>
	</tr>
	<tr>
		<td valign=top align=right><p style="font-weight:bold;"><span id=signupPrefLang class=translate_me>Preferred language</span></p></td>
		<td>
			$lang_select
		</td>
	</tr>
	<tr>
		<td valign=top align=right><p style="font-weight:bold;"><span id=signupSiNa class=translate_me>Site name</span></p></td>
		<td><input name=sitename value="$sitename">.$parent_site_id<br />
		<span style="font-size:small;"><span id=signupSiNaNote class=translate_me>eg joesdiner or billgates</span></td>
	</tr>
	$portal_password_input
	<tr>
		<td colspan=2 align=center><p><span id=signupSub class=translate_me><input type=submit value=' Create my ComfyPage '></span></p></td>
	</tr>
</table>
</form>
END;
    }
    public function get_second_stage_output($vars, $additional_inputs)
    {
		//$orig_site_id = $parent_site_id = Load::general_settings(NEW_SITE_ID);
		$parent_site_id = Load::general_settings(NEW_SITE_ID);
		//if in subdomain strip it off so we create a sibling subdomain and not a subsubdomain
		$parent_site_id = $this->RemoveSubdomain($parent_site_id);
		$error = null;

	  	$email = null;
	  	if(isset($vars['email'])) $email = $vars['email'];
	  	$email = strtolower($email);
	  	$temp = Validate::email($email);
	  	$error = Message::format_errors($error, $temp);

	  	$password = null;
	  	if(isset($vars['p1'])) $password = $vars['p1'];
	  	$temp = Validate::password($password);
	  	$error = Message::format_errors($error, $temp);

	  	$lang = null;
	  	if(isset($vars[LANG])) $lang = $vars[LANG];
	  	$temp = Validate::language($lang);
	  	$error = Message::format_errors($error, $temp);

	  	$confirm_password = null;
	  	if(isset($vars['p2'])) $confirm_password = $vars['p2'];
		if(empty($temp)) //if no problem with the first password
		{
			$temp = null;
			if($password != $confirm_password)
			{
				$temp = "Passwords don't match. Please enter them again.";
				$password = ''; //blank it
				$confirm_password = ''; //blank it
			}
		}
	  	$error = Message::format_errors($error, $temp);

		$sitename = null;
	  	if(isset($vars['sitename'])) $sitename = $vars['sitename'];
		$sitename = trim($sitename);
		$sitename = strtolower($sitename);
		$temp = Validate::sitename($sitename);
	  	$error = Message::format_errors($error, $temp);

		if($this->get(PORTAL_REQUIRE_PASSWORD))
		{
			$portal_password = null;
			if(isset($vars['portal_password']))
			{
				$portal_password = $vars['portal_password'];
			}
		  	if($portal_password != $this->get(PORTAL_SIGNUP_PASSWORD))
		  	{
		  		$temp = 'Portal password is incorrect';
		  		$error = Message::format_errors($error, $temp);
			}
		}
	  	if(empty($error))
	  	{
	  		require_once('common/ServerInterface.php');
			$subfolder_name = $sitename;
	  		$error = ServerInterface::create_subdomain($subfolder_name, $email, $password, $parent_site_id, $lang);
	  		if(!empty($error)) //if problem with that site name
	  		{
				Message::get_error_display('('.$error.')');
				$error = "<p style='font-weight:bold;'>$subfolder_name.$parent_site_id is taken. Is it yours? <a href='http://$subfolder_name.$parent_site_id/admin.php'>Log in here</a></p><p>Otherwise try another site name.</p>";
				$sitename = '';
				return '<a name=signupAnchor></a>' . Message::get_error_display($error) . $this->get_first_stage_output($additional_inputs, $email, $password, $sitename, $portal_password, $confirm_password, $lang);
	  		}
	  		$msg = <<<END
It's nice to meet you. Start creating website magic by visiting me at http://$subfolder_name.$parent_site_id/ and logging in with your email address ($email) and chosen password. If you forget your password visit the log-in page and click on "Forgotten details".

I'm your own piece of the internet so let's work together to make it special. I've arranged some helpful hints to get you started at http://help.comfypage.com/.
END;
	  		Globals::send_email_to_admin('ComfyPage website created', $msg, $email, "$subfolder_name.$parent_site_id");

			$mailingAddon = Load::addon('Mailing List');
			$mailingAddon->add_address_to_list($email);

			$portal = Load::portal_settings();
			$portal->apply_my_settings_to_portal($subfolder_name);

			$wizard = $this->get(PORTAL_SIGNUP_WIZARD);
			if($wizard && $wizard!='default')
			{
				$portal->setWizard($subfolder_name, $wizard);
			}
			return Message::get_success_display("<p>Your ComfyPage is located at $subfolder_name.$parent_site_id</p><p>Log in with your email and password.</p><p><a href='http://$subfolder_name.$parent_site_id/wizard.php'>Click here to go to your ComfyPage</a></p>");
		}
		else
		{
			return '<a name=signupAnchor></a>' . Message::get_error_display($error) . $this->get_first_stage_output($additional_inputs, $email, $password, $sitename, $portal_password, $confirm_password, $lang);
	    }
	}
    protected function get_default_settings()
	{
		$s = array();
		require_once('common/utils/PasswordGenerator.php');
		$s[PORTAL_SIGNUP_PASSWORD] = PasswordGenerator::generate();
		$s[PORTAL_SIGNUP_WIZARD] = 'default';
		$s[PORTAL_REQUIRE_PASSWORD] = 'on';
		$s[PORTAL_LINK]=null;
		return $s;
	}
	public function validate($setting_name, $setting_value)
	{
		switch($setting_name)
	  	{
			case PORTAL_SIGNUP_PASSWORD :
	  		{
	  			return Validate::password($setting_value);
			}
			default :
	  		{
	  			return null;
	  		}
	  	}
	}
	protected function get_description_dictionary()
	{
		return array
		(
		PORTAL_SIGNUP_PASSWORD => 'The portal password required by visitors before they can create a site.',
		PORTAL_SIGNUP_WIZARD => 'Which wizard should new users be sent to?',
		PORTAL_REQUIRE_PASSWORD => 'Require the portal password before allowing visitors to sign up?',
		PORTAL_LINK => '<a href="portal.php"><span id="mpo" class="translate_me">Click here for more portal options</span></a>',
		);
	}
	function get_input_internal($setting_name, $setting_value)
	{
		switch($setting_name)
	  	{
			case PORTAL_SIGNUP_WIZARD:
			{
				return HtmlInput::get_select_input($setting_name, $setting_value, array('default'=>'default','band website'=>'band website','flash'=>'flash'));

			}
			case PORTAL_REQUIRE_PASSWORD:
			{
				return HtmlInput::get_checkbox_input($setting_name, $setting_value);
			}
			case PORTAL_LINK:
			{
				return '';
			}
	  		default :
	  		{
	  			return parent::get_input_internal($setting_name, $setting_value);
	  		}
	  	}
	}
}
?>
