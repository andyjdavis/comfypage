<?php
class Visitor_Comments extends Addon
{
	function __construct()
	{
	    require_once('common/users_items/Comments.php');
		parent::__construct(false);
	}
	public function get_addon_description()
    {
		return 'Visitors can leave comments on you site to encourage discussion.';
	}
    public function get_instructions()
    {
  		return <<<END
END;
	}
	public function get_first_stage_output($additional_inputs, $name = null, $message = null)
    {
    	//first output previous comments
		$previousComments = '';

		$loggedIn = Login::logged_in(false);

		$page_id = Globals::get_param(CONTENT_ID_URL_PARAM, $_GET, INDEX);
		//bit of a hack to make it work on the addon config page
		if(strstr($_SERVER['SCRIPT_FILENAME'], 'function.php'))
		{
			$page_id = 'nonexistent_testing_id';
		}
		$comments = new Comments($page_id);

		$outerDivStart = '<div style="text-align: center;"><div style="margin: 0 auto;width:80%;"><b>Visitor Comments</b><br />';
		$outerDivEnd = '</div></div>';
		require_once('common/lib/form_spam_blocker/fsbb.php');
	  	$hiddenTags = get_hidden_tags();
		$deleteIcon = null;
		//$i = 0;
		if($comments)
		{
		    $comment_array = $comments->get(COMMENT_ARRAY);
		    $keys = array_keys($comment_array);
			foreach($keys as $key)
			{
			    $comment = $comment_array[$key];
				if($comment)
				{
					if($loggedIn)
					{
						//always null until we figure out why deleting works on my machine but deletes ALL comments on this page on the server
						$deleteIcon = '<form name="del'.$key.'" id="del'.$key.'" action="#visitor_comments_form">';
						$deleteIcon .= $additional_inputs;
						$deleteIcon .= $hiddenTags;
						$deleteIcon .= '<input type="hidden" name="del" value="'.$key.'" />';
						$deleteIcon .= '<a href="Javascript:document.del'.$key.'.submit()"><img src="common/images/Delete.gif" title="Delete Comment" /></a></form>&nbsp;&nbsp;&nbsp;';
						//$i++;
					}
					else
					{
						$deleteIcon = null;
					}
					$previousComments .= '<div style="text-align:left;">'.$deleteIcon.$comment[INDIVIDUAL_COMMENT_COMMENT].'</div><div style="text-align:right;">'.$comment[INDIVIDUAL_COMMENT_NAME].'</div><hr />';
				}
			}
		}
		//now output controls to let user add another comment
	  	$form = <<<END
	Make a comment
<form action="#visitor_comments_form">
	$additional_inputs
	$hiddenTags
	<table align=center border=0 cellpadding=5>
		<tr>
			<td>Name</td>
			<td><input style="width:25em;" type="text" name="name" value="$name"></td>
		</tr>
		<tr>
			<td valign=top>Comment</td>
			<td><textarea style="width:25em;" rows="8" name="message">$message</textarea></td>
		</tr>
		<tr>
			<td></td>
			<td><input type=submit value=" Comment "></td>
		</tr>
	</table>
</form>
END;
		return $outerDivStart.$previousComments.$form.$outerDivEnd;
    }
	public function get_second_stage_output($vars, $additional_inputs)
    {
	  	require_once('common/lib/form_spam_blocker/fsbb.php');
	  	$name = '';
	  	$message = '';
		$del = '';
		$errors = null;
		if(Login::logged_in(false) && isset($vars['del']))
		{
			$del = $vars['del'];
		}
		else
		{
			if(isset($vars['name']))
			{
				$name = $vars['name'];
			}
			if(isset($vars['message']))
			{
				$message = $vars['message'];
				$message = strip_tags($message);
				$message = stripslashes($message);
			}
			$errors = Validate::required($name, 'A name (not necessarily your real name)');
			$errors = Message::format_errors($errors, Validate::required($message, 'A comment'));
		}
	  	$success = null;
	  	if(empty($errors))
	  	{
	  		if(check_hidden_tags($vars))
	  		{
				$page_id = Globals::get_param(CONTENT_ID_URL_PARAM, $_GET, INDEX);
				//var_dump(strstr($_SERVER['SCRIPT_FILENAME'], 'function.php'));
				//bit of a hack to make it work on the addon config page
				if(strstr($_SERVER['SCRIPT_FILENAME'], 'function.php'))
				{
					$page_id = 'nonexistent_testing_id';
				}
				$comments = new Comments($page_id);
				if($del==="0" || !empty($del))
				{
					$comments->remove_comment($del);
				}
				else
				{
					$comments->add_comment($name, $message);
				}
				//save_comment($comments);
				if($del==="0" || !empty($del))
				{
					$success = 'Deleted';
				}
				else
				{
					$success = 'Comment added';
				}
				$comments->commit();
				$del = $name = $message = null;
	  		}
	  		else
	  		{
				$errors = 'Sorry, an error occurred. Please try again.';
	  		}
	  	}
	  	return "<a name='visitor_comments_form'></a>" . Message::get_success_display($success) . Message::get_error_display($errors) . $this->get_first_stage_output($additional_inputs, $name, $message);
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
