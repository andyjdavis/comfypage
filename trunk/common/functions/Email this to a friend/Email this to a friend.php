<?php
class Email_this_to_a_friend extends Addon
{
	function __construct()
	{
		parent::__construct(false);
	}
	public function get_addon_description()
    {
		return "Increase your ComfyPage's popularity by letting visitors email your site address to their friends.";
	}
	public function get_instructions()
    {
    	  return null;
	}
	public function get_first_stage_output($additional_inputs, $to_email=null, $from_email = null)
    {
		if(empty($to_email))
		{
			$to_email = "Friend's email";
		}
		if(empty($from_email))
		{
			$from_email = "Your email";
		}
		require_once('common/lib/form_spam_blocker/fsbb.php');
		$hiddenTags = get_hidden_tags();
		return <<<END
		<form style="text-align:center;">
		$additional_inputs
		$hiddenTags
		<input style="width:10em;" type="text" name="from_email" value="$from_email"><br>
		<input style="width:10em;" type="text" name="to_email" value="$to_email"><br>
		<input type="submit" value="Email this page">
		</form>
END;
    }
    public function get_second_stage_output($vars, $additional_inputs)
    {
	  	require_once('common/lib/form_spam_blocker/fsbb.php');
	  	$to_email = '';
	  	$from_email = '';
	  	if(isset($vars['to_email']))
	  	{
	  		$to_email = $vars['to_email'];
	  	}
	  	if(isset($vars['from_email']))
	  	{
	  		$from_email = $vars['from_email'];
	  	}
	  	$coming_from = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		if(array_key_exists('QUERY_STRING', $_SERVER))
		{
			$coming_from .= '?' . $_SERVER['QUERY_STRING'];
		}
		$message = "Check out $coming_from";
	  	$subject = "$from_email wants you to see this";
	  	$errors = '';
	  	$from_error = Validate::email($from_email);
	  	$errors = Message::format_errors($errors, $from_error);
	  	$to_error = Validate::email($to_email);
	  	$errors = Message::format_errors($errors, $to_error);
	  	$success = null;
	  	if(empty($errors))
	  	{
	  		if(check_hidden_tags($vars) == true)
	  		{
	  		    if(Globals::send_email($to_email, $from_email, $subject, $message))
	  		    {
	  				$success = 'Message sent';
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
	  	return Message::get_success_display($success) . Message::get_error_display($errors) . $this->get_first_stage_output($additional_inputs, $to_email, $from_email);
    }
    protected function get_default_settings()
	{
	    return array();
	}
	public function validate($setting_name, $setting_value)
	{
		return null;
	}
}
?>