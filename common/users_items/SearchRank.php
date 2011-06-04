<?php
require_once('common/class/users_item.class.php');
define('SEARCHRANK_SEARCH_TERM_ID', 'srid');
define('SEARCHRANK_ARRAY', 'SEARCHRANK_ARRAY');

//define('INDIVIDUAL_SEARCHRANK_TS', 'ts');
//define('INDIVIDUAL_SEARCHRANK', 'sr');
//represents the changing rank within Google of this site for a particular search phrase
class SearchRank extends UsersItem
{
	function __construct($id)
	{
		$id=strtolower($id);
		//not stripping out .
		$code_entities_match = array(' ','--','&quot;','!','@','#','$','%','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','/','*','+','~','`','=');
		//$code_entities_replace = array('-','-','','','','','','','','','','','','','','','','','','','','','','','','');
		$code_entities_replace = ' ';
		$id = str_replace($code_entities_match, $code_entities_replace, $id);
		parent::__construct("site/store/searchranks/$id.php", $id);
	}
	function get_default_settings()
	{
		$s = array
		(
			SEARCHRANK_SEARCH_TERM_ID => $this->id,
			SEARCHRANK_ARRAY => array(),
		);
		return $s;
	}
	function validate($setting_name, $setting_value)
	{
		return null;
	}
	function add_rank($rank, $ts)
	{
	    $ranks = $this->get(SEARCHRANK_ARRAY);

	    //$ranks[] = array(INDIVIDUAL_SEARCHRANK => $rank, INDIVIDUAL_SEARCHRANK_TS => $ts);
	    //this way we can use ksort() to ensure the array is in order
	    $ranks[$ts] = $rank;

	    $this->set(SEARCHRANK_ARRAY, $ranks);
	}
	/*function remove_rank($ts)
	{
		$comments = $this->get(COMMENT_ARRAY);
		unset($comments[$id]);
		$this->set(COMMENT_ARRAY, $comments);
	}*/
}
?>
