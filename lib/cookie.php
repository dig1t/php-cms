<?php
class Cookie {
	public static function get($name) {
		return $_COOKIE[$name];
	}
	
	public static function set($name, $value, $time) {
		$domain = $_SERVER['HTTP_HOST'] != 'localhost' ? $_SERVER['HTTP_HOST'] : false;
		
		setcookie($name, $value, 0, '/', $domain);
	}
}
?>