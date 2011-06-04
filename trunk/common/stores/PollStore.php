<?php
require_once('common/class/store.class.php');
class PollStore extends Store
{
	function PollStore()
	{
		parent::Store('site/store/polls', 'Poll');
	}
	public function load_all_polls()
	{
		$content_ids = $this->get_used_ids_in_store();
		$contents = array();
		foreach($content_ids as $id)
		{
			$contents[] = Load::poll($id);
		}
		return $contents;
	}
}
?>
