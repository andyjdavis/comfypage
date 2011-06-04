<?php
define('APPOINTMENT_REQUEST_YOUR_EMAIL_ADDRESS', 'your_email_address');
class Appointment_Request extends AddOn
{
	function __construct()
	{
		parent::__construct(true);
	}
	public function get_addon_description()
    {
		return 'Let your visitors send you an email requesting an appointment without you having to divulge your email address';
	}
    public function get_instructions()
    {
    	return 'Specify the email address where you will receive appointment requests. Visitor appointment requests will be emailed to you.';
	}
	private function GetTimeOptions($startHour, $maxHour, $ampm)
	{
  		$timeOptions = '';

	  	for($i=$startHour;$i<=$maxHour;$i++)
	  	{	
	  		$timeOptions .= "<Option value=\"$i:00 $ampm\">$i:00 $ampm</Option>";
	  		$timeOptions .= "<Option value=\"$i:30 $ampm\">$i:30 $ampm</Option>";
		}
		
		return $timeOptions;
	}
    public function get_first_stage_output($additional_inputs, $email=null, $phone=null, $message=null)
    {
		require_once('common/lib/form_spam_blocker/fsbb.php');
    
		$hiddenTags = get_hidden_tags();
  	
		$timeOptions = $this->GetTimeOptions(8, 12, 'am');
		$timeOptions .= $this->GetTimeOptions(1, 12, 'pm');
		$timeOptions .= $this->GetTimeOptions(1, 7, 'am');
  	
  		return <<<END
<Script Language="JavaScript">

function populate(inForm)
{
var temp=0;
var today= new Date();
var day= today.getDate();
var month= today.getMonth();
var year= today.getFullYear();
//t2= prompt("Enter the number of years to fetch",1);



for (var i=0; i <31 ; i++)
	{
	var x= String(i+1);
	
	inForm.day.options[i] = new Option(x,x);
	}

for (var i=0; i <31 ; i++)
	{
	var d=0;
	d=inForm.day.options[i].value;
	if(d=day){
		inForm.day.options[i].selected=true;
		break;}
	}

//for (var i=0,j=year; i <t2 ; i++, j--)
	{
	var y= String(year);
	inForm.year.options[1] = new Option(y,y);
	var y= String(year-1);
	inForm.year.options[0] = new Option(y,y);
	var y= String(year + 1);
	inForm.year.options[2] = new Option(y,y);
		
	}
for(var i=0;i<12;i++)
	{
	
	if(i=month)
		{inForm.month.options[i].selected=true;
	break;}
	
	}

}

function populate2(inForm2)
{
var t3=0;


if(inForm2.month.options[1].selected)

t3=28;
else if(inForm2.month.options[8].selected||inForm2.month.options[3].selected||inForm2.month.options[5].selected||inForm2.month.options[10].selected)
t3=30;
else
t3=31;


for(i=0;i<31;i++){
inForm2.day.options[i]=null;
}

for (var i=0; i <t3 ; i++)
	{
	var x= String(i+1);
	inForm2.day.options[i] = new Option(x);
		
	}
}
</script>

<form NAME="appointmentForm">
	$additional_inputs
	$hiddenTags    
	<table align=center border=0 cellpadding=5>
	<tr>
			<td>date</td>
			<td><SELECT NAME="day"></SELECT> <SELECT NAME="month" onChange = populate2(appointmentForm)>
<Option value=1>January</Option>
<Option value=2>February</Option>
<Option value=3>March</Option>
<Option value=4>April</Option>
<Option value=5>May</Option>
<Option value=6>June</Option>
<Option value=7>July</Option>
<Option value=8>August</Option>
<Option value=9>September</Option>
<Option value=10>October</Option>
<Option value=11>November</Option>
<Option value=12>December</Option>
</SELECT> <SELECT NAME="year"></SELECT>
			</td>
		</tr>
		<tr>
			<td>time</td>
			<td><SELECT NAME="time">
$timeOptions
</SELECT>
			</td>
		</tr>
		<tr>
			<td>email address</td>
			<td><input style="width:20em;" type=text name=email value="$email"></td>
		</tr>
		<tr>
			<td>phone number</td>
			<td><input style="width:20em;" type=text name=phone value="$phone"></td>
		</tr>
		<tr>
			<td valign=top>notes</td>
			<td><textarea style="width:25em;" rows=8 name=message>$message</textarea></td>
		</tr>

		<tr>
			<td></td>
			<td><input type=submit value=" Request Appointment "></td>
		</tr>
	</table>
</form>

<Script Language="JavaScript">
populate(appointmentForm);
</Script>
END;
	}
    public function get_second_stage_output($vars, $additional_inputs)
    {
    	$to = $this->get(APPOINTMENT_REQUEST_YOUR_EMAIL_ADDRESS);
	  	$subject = 'Message from ComfyPage appointment request form';
	  	require_once('common/lib/form_spam_blocker/fsbb.php');
	  	
	  	$from_email = '';
	  	$phone = '';
	  	$message = '';
	  	
	  	$time = '';
	  	$day = '';
	  	$month = '';
	  	$year = '';
	  	
	  	$errors = '';
	  	
	  	if(isset($vars['email']))
	  	{
	  		$from_email = $vars['email'];
	  	}
	  	if(isset($vars['phone']))
	  	{
	  		$phone = $vars['phone'];
	  		$phone = htmlspecialchars($phone);
	  	}
	  	if(isset($vars['message']))
	  	{
	  		$message = $vars['message'];
	  		$message = htmlspecialchars($message);
	  	}
	  	if(isset($vars['time']))
	  	{
	  		$time = $vars['time'];
	  	}
	  	if(isset($vars['day']))
	  	{
	  		$day = (int)$vars['day'];
	  	}
	  	if(isset($vars['month']))
	  	{
	  		$month = (int)$vars['month'];
	  	}
	  	if(isset($vars['year']))
	  	{
	  		$year = (int)$vars['year'];
	  	}
	  	
	  	//perform some security checking on date components
	  	$allowedDays = array();
	  	for($i = 0; $i <= 31; $i++)
	  	{
	  		$allowedDays[] = $i;
		}
	  	
	  	$allowedMonths = array();
	  	for($i = 0; $i <= 12; $i++)
	  	{
	  		$allowedMonths[] = $i;
		}
	  	
	  	//check day and month are within allowed values
	  	//year will be zero if isnt numeric but we cant define allowed years
		  if(!in_array($day, $allowedDays) 
				|| !in_array($month, $allowedMonths)
				|| !$year)
			{
				$errors .= 'An invalid date was submitted<br />';
			}
	  	
	  	$errors .= Validate::email($from_email);
	  	if(!empty($errors))
	  	{
	  		$errors .= '<br />';
	  	}
	  	
	  	//$errors .= RequiredField($phone, 'Phone number');
	  	
	  	$dateTimeString = "$time $day/$month/$year(dd/mm/yyyy)";
	  	
	  	$combinedMessage = 'You have received an appointment request.  Phone Number: ' . $phone;
	  	$combinedMessage .=' Email: ' . $from_email;
	  	$combinedMessage .=' When: ' . $dateTimeString;
	  	$combinedMessage .=' Notes: ' . $message;
	  
	  	$success = null;
	  	if(empty($errors))
	  	{
	  		if(check_hidden_tags($vars) == true)
	  		{
	  			//echo($combinedMessage);
				if(Globals::send_email($to, $from_email, $subject, $combinedMessage))
				{
	  				$success = 'Your appointment request has been sent';
	  			}
	  			else
	  			{
					$errors = 'The appointment request failed to send. Please try again.';
	  			}
	  		}
	  		else
	  		{
		  		$errors = 'An error occurred. Please try again.';
	  		}
	  	}
	  	
	  	return Message::get_success_display($success) . Message::get_error_display($errors) . $this->get_first_stage_output($additional_inputs, $from_email=null, $phone=null, $message=null);
	}
	protected function get_default_settings()
	{
	    $s = array();
	  	$s[APPOINTMENT_REQUEST_YOUR_EMAIL_ADDRESS] = Load::general_settings(ADMIN_EMAIL);
	  	return $s;
	}
	public function validate($setting_name, $setting_value)
	{
		switch($setting_name)
		{
			case APPOINTMENT_REQUEST_YOUR_EMAIL_ADDRESS :
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
		APPOINTMENT_REQUEST_YOUR_EMAIL_ADDRESS => 'The email address where you will receive appointment requests',
		);
	}
}
?>
