<?php
//TODO Convert LIST_OF_EMAILS to an array setting. It is currently a newline delimited string.
//When you do this use $this->add_item_to_array_setting and $this->delete_item_from_array_setting
//Then delete get_addresses_from_single_string, remove_address_from_list, add_address_to_list
//Remember to add conversion code so the old value is converted to an array
define('FROM_EMAIL_ADDRESS', 'from_email_address');
define('LIST_OF_EMAILS', 'LIST_OF_EMAILS');
define('EMAIL_SUBJECT', 'EMAIL_SUBJECT');
define('MESSAGE_TRAILER', 'MESSAGE_TRAILER');
class Mailing_List extends Addon
{
	function __construct()
	{
		parent::__construct(true);
	}
	public function get_addon_description()
    {
		return 'Visitors can subscribe to your mailing list. You can send everyone on the list a message to keep them up to date.';
	}
    public function get_instructions()
    {
    	return <<<END
This add-on maintains a list of email addresses. Visitors can subscribe and unsubscribe from the list.
<p>Send emails to your mailing list <a href=mail.php>here</a></p>
END;
	}
	public function get_first_stage_output($additional_inputs, $email = 'Your email address')
    {
        require_once('common/lib/form_spam_blocker/fsbb.php');
		$hiddenTags = get_hidden_tags();
		return <<<END
<form action=#abc>
	$additional_inputs
	$hiddenTags
	<table align=center border=0 cellpadding=5>
		<tr>
			<td align=center>
				<input type=text size=30 name=list_email value="$email">
			</td>
		</tr>

		<tr>
			<td align=center>
				<input type=submit name=action value="Subscribe">
				<input type=submit name=action value="Unsubscribe">
			</td>
		</tr>
	</table>
</form>
END;
    }
    public function get_second_stage_output($vars, $additional_inputs)
    {
	  	require_once('common/lib/form_spam_blocker/fsbb.php');
	  	$the_email = '';
	  	$action = $vars['action'];
	  	if(isset($vars['list_email']))
	  	{
	  		$the_email = $vars['list_email'];
	  	}
	  	$errors = Validate::email($the_email);
	  	if(empty($errors) == false)
	  	{
	  		$errors .= "<br>";
	  	}
	  	$success = null;
	  	if(empty($errors))
	  	{
	  		if(check_hidden_tags($vars) == true)
	  		{
	  		    //subscribe/unsubscribe here
	  			if($action == 'Subscribe')
	  			{
	  			    $this->add_address_to_list($the_email);
	  			    //$this->add_item_to_array_setting(LIST_OF_EMAILS, $the_email);
	  			    $success = "$the_email subscribed";
	  			}
	  			else //unsubscribe
	  			{
					$this->remove_address_from_list($the_email);
					//$this->delete_item_from_array_setting(LIST_OF_EMAILS, $the_email);
					$success = "$the_email unsubscribed";
	  			}
	  		}
	  		else
	  		{
	              $errors = 'Sorry, an error occurred. Please try again.';
	  		}
	  	}
	  	return "<a name=abc>" . Message::get_success_display($success) . Message::get_error_display($errors) . $this->get_first_stage_output($additional_inputs, $the_email);
    }
    protected function get_default_settings()
	{
	    //not sure why we had a special function to get the domain
	    //should just use the site ID
	  	//$domain = getDomainWithoutWWW();
	  	$domain = Load::general_settings(NEW_SITE_ID);
	  	$default_from = "noreply@$domain";
	  	$s = array();
	  	$s[FROM_EMAIL_ADDRESS] = $default_from;
	  	$s[EMAIL_SUBJECT] = 'ComfyPage powered mailing list message';
	  	$s[LIST_OF_EMAILS] = "person1@example.com\nperson2@example.com";
	  	$s[MESSAGE_TRAILER] = "To unsubscribe please visit http://$domain/";
	  	return $s;
	}
	public function validate($setting_name, $setting_value)
	{
		switch($setting_name)
		{
			case FROM_EMAIL_ADDRESS :
			{
				return Validate::email($setting_value);
			}
			case LIST_OF_EMAILS :
			{
				$addresses = $this->get_addresses_from_single_string($setting_value);
				$errors = null;
				$error = null;
				foreach($addresses as $address)
				{
					$error = Validate::email($address);
					//if(empty($error) == false)
					//{
						//$errors .= "$error<br>";
					$errors = Message::format_errors($errors, $error);
					//}
				}
				return $errors;
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
		FROM_EMAIL_ADDRESS => 'The FROM address used in the emails you send. If you want to receive replies (including messages about undelivered emails then enter an email address you actually use)',
		LIST_OF_EMAILS => 'List of addresses on the list. Type one per line.',
		EMAIL_SUBJECT => 'Subject of emails sent out from this list',
		MESSAGE_TRAILER => 'This message is attached to the bottom of each message you send out',
		);
	}
	function get_input_internal($setting_name, $setting_value)
	{
	  	switch($setting_name)
	  	{
	  		case LIST_OF_EMAILS :
	       	{
	       		$addys = $this->get_addresses_from_single_string($setting_value);
	  		    $no_white_space = null;
	  		    for($i=0; $i<count($addys); $i++)
	  		    {
	                  if($i != count($addys) - 1)
	                  {
	  					$addys[$i] = $addys[$i] . "\n";
	  				}
	  				$no_white_space .= $addys[$i];
	  			}
	  			return HtmlInput::get_textarea_input($setting_name, $no_white_space);
	  		}
	  		case MESSAGE_TRAILER :
	       	{
	  			return HtmlInput::get_textarea_input($setting_name, $setting_value);
	  		}
	  		default :
	  		{
	  			return parent::get_input_internal($setting_name, $setting_value);
	  		}
	  	}
	}
	function add_address_to_list($email)
	{
		$addys = $this->get(LIST_OF_EMAILS);
		if(stristr($addys, $email) == false)
		{
			$addys = "$addys\n$email";
		}
		$this->set(LIST_OF_EMAILS, $addys);
	}

	function remove_address_from_list($email)
	{
		$addys = $this->get(LIST_OF_EMAILS);
		$email_with_newline = "$email\n";
		$addys = str_ireplace($email_with_newline, null, $addys);
		$addys = str_ireplace($email, null, $addys);
		$addys = trim($addys);
		$this->set(LIST_OF_EMAILS, $addys);
	}
	//takes a single string that is meant to be email addresses seperated by newlines
	//gets rid of whitespace and blank lines
	//does not validate addresses
	function get_addresses_from_single_string($single)
	{
		$addresses = explode("\n", $single);
		$filtered_addresses = array();
		foreach($addresses as $address)
		{
			$address = trim($address);
			if(empty($address) == false)
			{
				$filtered_addresses[] = $address;
			}
		}
		return $filtered_addresses;
	}
}
?>