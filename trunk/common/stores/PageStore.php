<?php
require_once('common/class/store.class.php');
class PageStore extends Store
{
	private $special_ids = array(HEADER, FOOTER, LEFT_MARGIN, RIGHT_MARGIN, INDEX, ERROR_CONTENT);
	public function PageStore($store_location='site/store/pages')
	{
		parent::Store($store_location, 'Page');
	}
	public function create($id = null)
	{
		Globals::clearContentDependentCaches();
		return parent::create($id);
	}
	public function copy($users_item)
	{
		Globals::clearContentDependentCaches();
		return parent::copy($users_item);
	}
	public function delete($id)
	{
		if(in_array($id, $this->special_ids))
		{
			return "Page cannot be deleted";
		}
		Globals::clearContentDependentCaches();
		return parent::delete($id);
	}
	public function load_users_pages()
	{
            $content_ids = $this->get_used_ids_in_store();
            $contents = array();
            foreach($content_ids as $id)
            {
                //if not a special page
                if(in_array($id, $this->special_ids) == false)
                    {
                            $contents[] = Load::page($id);
                    }
            }
            uasort($contents, 'PageStore::compare_page_titles');
            return $contents;
	}
	static function compare_page_titles($a, $b)
	{
		return strcasecmp($a->get(CONTENT_TITLE), $b->get(CONTENT_TITLE));
	}
	public function load_index_page()
	{
		return Load::page(INDEX);
	}
	public function page_exists($title)
	{
		$pages = $this->load_users_pages();
		foreach($pages as $page)
		{
			if($page->get(CONTENT_TITLE)==$title)
			{
				return true;
			}
		}
		return false;
	}
}
?>