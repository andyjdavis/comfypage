<?php
require_once('common/class/store.class.php');
class ProductStore extends Store
{
	function ProductStore()
	{
		parent::Store('site/store/products', 'Product');
	}
	public function load_all_products()
	{
		$content_ids = $this->get_used_ids_in_store();
		$contents = array();
		foreach($content_ids as $id)
		{
			$contents[] = Load::product($id);
		}
		uasort($contents, 'ProductStore::compare_product_titles');
		return $contents;
	}
	static function compare_product_titles($a, $b)
	{
		return strcasecmp($a->get(PRODUCT_TITLE), $b->get(PRODUCT_TITLE));
	}
}
?>