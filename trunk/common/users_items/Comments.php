<?php
require_once('common/class/users_item.class.php');
define('COMMENT_PAGE_ID', 'COMMENT_PAGE_ID');
define('COMMENT_ARRAY', 'COMMENT_ARRAY');

define('INDIVIDUAL_COMMENT_NAME', 'ICN');
define('INDIVIDUAL_COMMENT_COMMENT', 'ICC');
//represents a set of comments for a single page
class Comments extends UsersItem
{
	function __construct($id)
	{
		parent::__construct("site/store/comments/$id.php", $id);
	}
	function get_default_settings()
	{
		$s = array
		(
			COMMENT_PAGE_ID => $this->id,
			COMMENT_ARRAY => array(),
		);
		return $s;
	}
	function validate($setting_name, $setting_value)
	{
		return null;
	}
	function add_comment($name, $message)
	{
		$comments = $this->get(COMMENT_ARRAY);
		$comments[] = array(INDIVIDUAL_COMMENT_NAME => $name, INDIVIDUAL_COMMENT_COMMENT => $message);
		$this->set(COMMENT_ARRAY, $comments);
	}
	function remove_comment($id)
	{
		$comments = $this->get(COMMENT_ARRAY);
		unset($comments[$id]);
		$this->set(COMMENT_ARRAY, $comments);
	}
}
?>