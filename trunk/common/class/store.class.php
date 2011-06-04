<?php
abstract class Store
{
	private $store_location;
	//name of the class of the objects in this store
	//class must extend SaveableBase
	private $saveable_class;
    function Store($store_location, $saveable_class)
    {
        $this->store_location = $store_location;
        $this->saveable_class = $saveable_class;
    }
	private function is_store_file($file_name)
	{
		$path_info = pathinfo($file_name);
		$ext = null;
		if(array_key_exists('extension', $path_info))
		{
			$ext = $path_info['extension'];
			$ext = strtolower($ext);
		}
		if($ext != 'php')
		{
			return false;
		}
		return true;
	}
	public function delete($id, $put_in_trash = true)
	{
		$file = $this->get_path_to_file_in_store($id);
		if(file_exists($file))
		{
		    if($put_in_trash)
		    {
				$file_in_trash = $this->get_path_to_file_in_trash($id);
				@copy($file, $file_in_trash);
			}
			unlink($file);
		}
		return null;
	}
	public function copy($users_item)
	{
		$new_item = $this->create();
		$setting_names = $users_item->get_setting_names();
		foreach($setting_names as $name)
		{
			$new_item->set($name, $users_item->get($name));
		}
		return $new_item;
	}
	public function create($id = null)
	{
		//if an id wasn't specified
		if(empty($id))
		{
			$id = $this->get_new_id_for_store($this->store_location);
		}
		$slash = null;
		if($this->store_location[strlen($this->store_location)-1]!='/')
		{
			$slash = '/';
		}
		$new_object = new $this->saveable_class($id,$this->store_location.$slash."$id.php");
		return $new_object;
	}
	private function get_path_to_file_in_store($id)
	{
		return "$this->store_location/$id.php";
	}
	private function get_path_to_file_in_trash($id)
	{
		return "$this->store_location/trash/$id.php";
	}
	private function get_new_id_for_store()
	{
		$ids_in_store = $this->get_used_ids_in_store($this->store_location);
		$ids_in_trash = $this->get_used_ids_in_trash($this->store_location);
		$next_highest = 1;
		//find next highest id in store
		foreach($ids_in_store as $id)
		{
			if($id >= $next_highest)
			{
				$next_highest = $id + 1;
			}
		}
		//same for trash
		//don't want to create new items with the same id as one in the trash
		foreach($ids_in_trash as $id)
		{
			if($id >= $next_highest)
			{
				$next_highest = $id + 1;
			}
		}
		return $next_highest;
	}
	public function get_used_ids_in_store()
	{
	    $ids_in_store = array();
	    if( file_exists($this->store_location) ) { //if dir doesnt exist should we return an empty array or give an error?
		$files_in_store = scandir($this->store_location);
		for($i = 0; $i<count($files_in_store); $i++)
		{
		    if($this->is_store_file($files_in_store[$i]))
		    {
				$ids_in_store[$i] = strtok($files_in_store[$i], '.');
			}
		}
	    }
	    else {
		mkdir($this->store_location);
	    }
	    return $ids_in_store;
	}
	public function get_used_ids_in_trash()
	{
	    $ids_in_store = array();

	    $path = $this->store_location.'/trash/';
	    if(file_exists($path)) {
		$files_in_store = scandir($path);
		for($i = 0; $i<count($files_in_store); $i++)
		{
		    if($this->is_store_file($files_in_store[$i]))
		    {
				$ids_in_store[$i] = strtok($files_in_store[$i], '.');
			}
		}
	    }
	    return $ids_in_store;
	}
	public function store_item_exists($id)
	{
	    return in_array($id, $this->get_used_ids_in_store());
	}
	public function restore_from_trash($id)
	{
		$file = $this->get_path_to_file_in_store($id);
		$file_in_trash = $this->get_path_to_file_in_trash($id);
		//can call this multiple times even when item is already restored
		//amd this if statement stops it cracking the shits
		if(file_exists($file_in_trash))
		{
			copy($file_in_trash, $file);
			unlink($file_in_trash);
		}
	}
	public function delete_from_trash($id)
	{
		$file_in_trash = $this->get_path_to_file_in_trash($id);
		if(file_exists($file_in_trash))
		{
			unlink($file_in_trash);
		}
	}
	public function empty_trash()
	{
		$ids = $this->get_used_ids_in_trash();
		foreach($ids as $id)
		{
			$this->delete_from_trash($id);
		}
		return $ids;
	}
}
?>