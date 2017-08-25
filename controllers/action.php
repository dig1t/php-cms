<?php
defined('ROOT') OR exit;

class Action extends Bootstrap {
	public function __construct() {
		
	}
	
	public $actions = array('login', 'logout', 'register');
	public $redirect = '/';
	
	public function do() {
		$action = $this->get($this->params, 'action');
		
		in_array($action, $this->actions) ? $this->$action() : $this->redirect('/');
	}
	
	public function login() {
		echo 123;
	}
	
	public function register() {
		$name = $this->get($_POST, 'form_name');
		$email = $this->get($_POST, 'form_email');
		$phone = $this->get($_POST, 'form_phone');
		$password = $this->get($_POST, 'form_password');
		
		$err;
		
		if ($name && $email && $password) {
			$emailOptions = array('options' => array('min_range' => 6, 'max_range' => 254);
			
			if (strlen($password) === 0) $err = 'No password entered.';
			if (!filter_var($email, FILTER_VALIDATE_EMAIL, $emailOptions)) $err = 'Not a valid email.';
			if (strlen($phone) !== 10) $err = 'Not a valid phone number.'
			
			if (!$err) {
				try {
					$email_available = $this->rowCount('SELECT email FROM login WHERE email=?', array($email));
				} catch(Exception $e) {
					$err = json_encode($e);
				}
				
				
			}
		}
	}
}
?>