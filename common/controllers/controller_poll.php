<?php
//require_once('common/Poll.class.php');

define('POLL_RESPONSE','pollresponse');
define('COOKIE_POLL_RESPONSE','cookiepollresponse');

class controller_poll
{
	function get_poll_ids()
	{
		/*require_once('common/file.php');
		$file_admin = new FileAdmin();
		$files = $file_admin->get_file_list('site/store/polls', false, true);
		if($files)
		{
			$ret = array();
			foreach($files as $file)
			{
				$ret[] = trim($file,'~.php');
			}
		}
		return $ret;*/
		return Load::poll_store()->get_used_ids_in_store();
	}
	
	function get_poll($id)
	{
		/*$p = null;
		if($this->poll_exists($id))
		{
			$p = new Poll($id);
		}
		return $p;*/
		Load::poll($id);
	}
	
	/*function create_poll($poll)
	{}*/
	
	function poll_exists($id)
	{
		/*$p = 'site/store/polls/'.$id.'.php';
		return file_exists($p);*/
		return Load::poll_store()->store_item_exists($id);
	}
	
	function increment_poll($poll, $poll_id, $val)
	{
		$poll->increment_val($val);
			
		//set session variable to prevent repeated submissions
		$_SESSION[POLL_RESPONSE.$poll_id] = $val;

		//also set a cookie as session var is not working for some users
		$Month = 2592000 + time();
		setcookie(COOKIE_POLL_RESPONSE.$poll_id, $val, $Month);
	}
	
	function user_previously_voted($id)
	{
		return array_key_exists(POLL_RESPONSE.$id, $_SESSION) || isset($_COOKIE[COOKIE_POLL_RESPONSE.$id]);
	}
	
	function get_user_previous_response($id)
	{
		return $_SESSION[POLL_RESPONSE.$id] | $_COOKIE[COOKIE_POLL_RESPONSE.$id];
	}
	
	function is_there_a_next_question($poll, $vote)
	{
		$child_polls = $poll->get_children();
		$on_select = $poll->get_on_select_urls();
		return  ($child_polls!=null && array_key_exists($vote, $child_polls)) || ( $on_select!=null && array_key_exists($vote, $on_select) ) ;
	}
	
	function get_next_question_url($poll, $vote)
	{
		$on_select = $poll->get_on_select_urls();
		$children = $poll->get_children();
		if( $on_select==null || !array_key_exists($vote, $on_select))
		{
			if(array_key_exists($vote, $children))
			{
				return 'poll_participate.php?i='.$children[$vote];
			}
			else
			{
				return null;
			}
		}
		else
		{
			return $on_select[$val];
		}
	}
}
?>