<?php

class PasswordGenerator
{
	public static function generate()
	{
		$colors = array('red', 'blue','green','orange','purple','yellow','pink');
		$cities = array('london','paris','perth','sydney','prague','cracow','bangkok','melbourne','chicago','detroit','jakarta','berlin','hanoi','tokyo');
		$r1 = rand(0, sizeof($colors)-1);
		$r2 = rand(0, sizeof($cities)-1);
		$r3 = rand(1, 1000);
		return $colors[$r1].$cities[$r2].$r3;
	}
}

?>