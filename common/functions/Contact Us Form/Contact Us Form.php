<?php
define('CONTACT_FORM_YOUR_EMAIL_ADDRESS', 'your_email_address');
class Contact_Us_Form extends AddOn
{
	function __construct()
	{
		parent::__construct(true);
	}
	public function get_addon_description()
    {
		return 'Visitors send you email from your ComfyPage while keeping your email address private.';
	}
    public function get_instructions()
    {
    	  return <<<END
Messages submitted with the <i>Contact Us Form</i> will be sent to the email address option shown below. Your visitors can enter their own email address and a message.
END;
	}
	public function get_first_stage_output($additional_inputs, $email=null, $message=null)
    {
		//TODO It would be good if this spam blocker stuff was
		//auto added to any form in an add-on. Maybe can add it to the additional inputs passed in
	    require_once('common/lib/form_spam_blocker/fsbb.php');
	  	$hiddenTags = get_hidden_tags();
	  	return <<<END
<form action="#contact_us_form">
	$additional_inputs
	$hiddenTags
	<table align="center" border="0" cellpadding="5">
		<tr>
			<td>Your email address</td>
			<td><input style="width:25em;" type="text" name="email" value="$email"></td>
		</tr>
		<tr>
			<td valign=top>Your message</td>
			<td><textarea style="width:25em;" rows="8" name="message">$message</textarea></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value=" Send "></td>
		</tr>
	</table>
</form>
END;
    }
	public function get_second_stage_output($vars, $additional_inputs)
    {
		$to = $this->get(CONTACT_FORM_YOUR_EMAIL_ADDRESS);
	  	$site_id = Load::general_settings(NEW_SITE_ID);
	  	$subject = "ComfyPage message sent from $site_id";
	  	require_once('common/lib/form_spam_blocker/fsbb.php');
	  	$from_email = '';
	  	$message = '';
	  	if(isset($vars['email']))
	  	{
	  		$from_email = $vars['email'];
	  	}
	  	if(isset($vars['message']))
	  	{
	  		$message = $vars['message'];
			$message = strip_tags($message);
			$message = stripslashes($message);
	  	}
	  	//$errors = IsValidEmail($from_email);
	  	$errors = Validate::email($from_email);
	  	if(empty($errors) == false)
	  	{
	  		$errors .= "<br>";
	  	}
	  	//$errors .= RequiredField($message, 'A message');
	  	$errors .= Validate::required($message, 'A message');
	  	$success = null;
	  	if(empty($errors))
	  	{
	  		if(check_hidden_tags($vars) == true)
	  		{
				//if(send_email($to, $from_email, $subject, $message))
				if(Globals::send_email($to, $from_email, $subject, $message))
				{
					$success = 'Your message has been sent';
				}
	  			else
	  			{
					$errors = 'Sorry, the message failed to send. Please try again.';
	  			}
	  		}
	  		else
	  		{
				$errors = 'Sorry, an error occurred. Please try again.';
	  		}
	  	}
	  	//return "<a name='contact_us_form'>" . Message::get_success_display($success) . Message::get_error_display($errors) . $this->get_doodad_first_stage_output($functionKey, $doodad_settings, $additional_inputs, $from_email, $message);
	  	return "<a name='contact_us_form'>" . Message::get_success_display($success) . Message::get_error_display($errors) . $this->get_first_stage_output($additional_inputs, $from_email, $message);
    }
    protected function get_default_settings()
	{
	    $s = array();
	  	//$s[CONTACT_FORM_YOUR_EMAIL_ADDRESS] = get_admin_email();
	  	$s[CONTACT_FORM_YOUR_EMAIL_ADDRESS] = Load::general_settings(ADMIN_EMAIL);
	  	return $s;
	}
	public function validate($setting_name, $setting_value)
	{
	  	switch($setting_name)
	  	{
	  	    case CONTACT_FORM_YOUR_EMAIL_ADDRESS :
	  	    {
	  			return Validate::email($setting_value);
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
		CONTACT_FORM_YOUR_EMAIL_ADDRESS => 'The email address where you will receive messages that are sent to you',
		);
	}
}
