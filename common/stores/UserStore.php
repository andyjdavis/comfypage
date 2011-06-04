<?php
require_once('common/class/store.class.php');
class UserStore extends Store
{
	public function UserStore($store_location='site/store/users')
	{
		parent::Store($store_location, 'UserLogin');
		//TODO remove this 1/1/2011
		if(file_exists('site/store/users') == false)
		{
			mkdir('site/store/users');
		}
		if(file_exists('site/store/users/trash') == false)
		{
			mkdir('site/store/users/trash');
		}
	}
	public function create_user($email, $password)
	{
		$new_user = parent::create();
		$new_user->set(USER_EMAIL, $email);
		$new_user->set(USER_PASSWORD, $password);
		return $new_user;
	}
	public function get_user($email)
	{
	    $users = $this->get_used_ids_in_store();
	    foreach($users as $id)
	    {
			$user = new UserLogin($id);
			if($user->get(USER_EMAIL) == $email)
			{
				return $user;
			}
		}
		return null;
	}
}
?>