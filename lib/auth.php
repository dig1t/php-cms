<?php
class Auth {
	public static function formToken($store = true) {
		$token = md5(uniqid(rand(), true));
		
		if ($store) $_SESSION['form_token'] = $token;
		return $token;
	}
	
	public static function formValid($token) {
		if (isset($_SESSION['form_token'])) {
			return $_SESSION['form_token'] === $token;
		}
	}
	
	public static function formTime() {
		return $_SESSION['form_time'] = time();
	}
	
	// Account Functions
	
	/**
	 * requests user's salt string.
	 * 
	 * @param string $user_id ID of user
	 * @return user's unique salt string.
	 */
	public function getUserSalt(string $user_id) {
		if ($this->validate($user_id, 'id')) {
			try {
				$q = $this->db->prepare('SELECT _salt FROM login WHERE user_id=?');
				$q->prep(1, $user_id, PDO::PARAM_STR, 32);
				$q->execute();
				$salt = $q->fetch(PDO::FETCH_ASSOC);
				return $salt['_salt'];
			} catch(Exception $e) {
				return null;
			}
		}
	}
	
	public function verifyPass($uid, $string) { // TODO
		if (isset($uid) && isset($string)) {
			try {
				$q = $this->prepare('SELECT _salt, password FROM login WHERE uid=?');
				$q->bindParam(1, $uid, PDO::PARAM_STR, 32);
				$q->execute();
				$cred = $q->fetch(PDO::FETCH_ASSOC);
				
				if (isset($cred['_salt']) && isset($cred['password'])) {
					$hash = $this->hashString($string, $cred['_salt']);
					if ($hash === $cred['password']) {
						return true;
					}
				}
			} catch(Exception $e) {
				return false;
			}
		}
		return false;
	}
	
	public function tokenExists($string, $table, $column) { // TODO
		$sql = 'SELECT '.$column.' FROM '.$table.' WHERE '.$column.'=?';
		$count = 0;
		
		try {
			$q = $this->db->prepare($sql);
			$q->bindParam(1, $string);
			$q->execute();
			$count = $q->rowCount();
		} catch(Exception $e) {
			$count = null;
		}
		
		return $count === 0 ? false : false;
	}
	
	public function login() {
		if ($action === 'user.login') $this->db->execute(
			'UPDATE login SET last_ip=? WHERE user_id=?',
			array($this->browser->ipAddress, $data['user_id'])
		);
	}
	
	public function logAction(string $user_id, string $action) {
		$this->db->execute(
			'INSERT INTO `user_action`(`action_id`, `user_id`, `ip_address`, `action`) VALUES(?, ?, ?, ?)',
			array($id, $user_id, $this->browser->ipAddress, $action)
		);
	}
	
	
	
	
	
	public function init($credentials) {
		$callback = array();
		$callback['success'] = false;
		
		if (!isset($credentials['login']) || isset($credentials['login']) && strlen($credentials['login']) === 0) {
			$callback['error'] = 'Your email is missing.';
		} elseif (!isset($credentials['password']) || isset($credentials['password']) && strlen($credentials['password']) === 0) {
			$callback['success'] = false;
			$callback['error'] = 'Your password is missing.';
		}
		
		$login = $this->security->sanitize($credentials['login'], 'email');
		$password = $this->security->sanitize($credentials['password'], 'password');
		
		if ($this->security->validate($password, 'password') === true && $this->security->validate($login, 'email') === true) {
			$_uid = $this->fetch('SELECT uid, auth_id FROM login WHERE email=?', array($login));
			
			if (isset($_uid['uid']) && isset($_uid['auth_id'])) {
				$login_check = $this->security->verifyPass($_uid['uid'], $password);
				
				if ($login_check === true) {
					$sid = $this->security->generateToken(32, null, 1);
					$this->newUserSession($_uid['uid'], $sid, $_uid['auth_id']);
					$callback['login_query'] = $login;
					$callback['success'] = true;
				} else {
					$this->logLogin(array('uid' => $_uid['uid'], 'success' => false));
				}
			}
		}
		
		return $callback;
	}
	
	public function register($POST, $type = 'user') {
		$callback = array();
		
		if (!isset($POST['signup_username'])) {
			$callback['error'] = 'Username is missing.';
		} elseif (!isset($POST['signup_email'])) {
			$callback['error'] = 'Email is missing.';
		} elseif (!isset($POST['signup_password'])) {
			$callback['error'] = 'Password is missing.';
		} elseif (!isset($POST['signup_name'])) {
			$callback['error'] = 'Name is missing.';
		}
		
		if (!empty($callback['error'])) {
			return $callback;
		}
		
		echo json_encode($POST);
		
		if (!$this->security->validate($POST['signup_username'], 'username')) {
			$callback['error'] = 'Username is invalid.';
		} elseif (!$this->security->validate($POST['signup_email'], 'email')) {
			$callback['error'] = 'Email is invalid.';
		} elseif (!$this->security->validate($POST['signup_password'], 'password')) {
			$callback['error'] = 'Password is invalid.';
		} elseif (!$this->security->validate($POST['signup_first_name'], 'name')) {
			$callback['error'] = 'First name is invalid.';
		} elseif (!$this->security->validate($POST['signup_last_name'], 'name')) {
			$callback['error'] = 'Last name is invalid.';
		} elseif (!$this->security->validate($POST['signup_birthday'], 'int')) {
			$callback['error'] = 'Your birthday i	s invalid.';
		} elseif (!$this->security->validate($POST['signup_gender'], 'int')) {
			$callback['error'] = 'Gender is invalid.';
		} else {
			$callback['error'] = null;
		}
		
		if (!$callback['error'] === null) {
			return $callback;
		}

		
		$username = $this->security->sanitize($POST['signup_username'], 'letters');
		$email = $this->security->sanitize($POST['signup_email'], 'email');
		$password = $this->security->sanitize($POST['signup_password'], 'password');
		$first_name = $this->security->sanitize($POST['signup_name'], 'letters');
		
		$birthday_arr = explode('-', $birthday);
		$birthday_timestamp = mktime(0, 0, 0, $birthday_arr[1], $birthday_arr[2], $birthday_arr[0]);
		
		if (!$birthday_timestamp > 0) {
			$callback['error'] = 'Invalid birthday.';
			return $callback;
		}
		
		$age = $this->util->getAge($birthday_timestamp);
		
		$email = str_replace(' ', '', $email);
		$first_name = preg_replace('!\s+!', ' ', '', $first_name);
		
		$callback = array();
		$can_register = true;
		$email_options = array('options' => array('min_range' => 6, 'max_range' => 254));
		
		if (!preg_match('/[0-9]/', $password)) {
			$callback['error'] = 'Password has no numbers.';
		}
		
		if (!preg_match('/[a-z]|[A-Z]|\d|\!@#\$%_-\.,+/', $password)) {
			$callback['error'] = 'Password does not have a correct format.';
		}
		
		if (strlen($password) < 5) {
			$callback['error'] = 'Password must have at least 6 characters.';
		}
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL, $email_options) || !$this->security->validate($email, 'email')) {
			$callback['error'] = 'Not a valid email.';
		}
		
		$_salt = $this->security->_salt();
		$password = $this->security->hashString($password, $_salt);
		
		try {
			$email_available = $this->rowCount('SELECT email FROM login WHERE email=?', array($email));
		} catch(Exception $e) {
			$callback['error'] = json_encode($e);
		}
		
		if (isset($callback['error'])) {
			$can_register = false;
		}
		
		if ($email_available === 0 && $can_register === true) {
			$uid = $this->security->generateToken(24, 'uid', 1);
			$auth_id = $this->security->generateToken(32);
			$sid = $this->security->generateToken(32);
			
			try {
				$new_login = $this->prepare('INSERT INTO `login`(`uid`, `username`, `password`, `email`, `_salt`, `sid`, `auth_id`) VALUES (?, ?, ?, ?, ?, ?, ?)');
				$new_login->bindParam(1, $uid, PDO::PARAM_STR, 24);
				$new_login->bindParam(2, $username, PDO::PARAM_STR, 32);
				$new_login->bindParam(3, $password, PDO::PARAM_STR, 64);
				$new_login->bindParam(4, $email, PDO::PARAM_STR, 254);
				$new_login->bindParam(5, $_salt, PDO::PARAM_STR, 32);
				$new_login->bindParam(6, $sid, PDO::PARAM_STR, 32);
				$new_login->bindParam(7, $auth_id, PDO::PARAM_STR, 32);
				$new_login->execute();
			} catch(Exception $e) {
				$callback['error'] = json_encode($e);
			}
			
			try {
				$profile_register = $this->prepare('INSERT INTO `profile`(`uid`, `username`, `name`) VALUES (?, ?, ?)');
				$profile_register->bindParam(1, $uid, PDO::PARAM_STR, 32);
				$profile_register->bindParam(2, $username, PDO::PARAM_STR, 32);
				$profile_register->bindParam(3, $name, PDO::PARAM_STR, 64);
				$profile_register->execute();
				
				$user_register = $this->prepare('INSERT INTO `user`(`uid`, `first_name`, `last_name`, `birthday`, `gender`) VALUES (?, ?, ?, ?, ?)');
				$user_register->bindParam(1, $uid, PDO::PARAM_STR, 24);
				$user_register->bindParam(2, $name, PDO::PARAM_STR, 32);
				$user_register->execute();
				
				$this->newUserSession($uid, $sid, $auth_id);
				$callback['user_query'] = $user_register;
				$callback['success'] = true;
			} catch(Exception $e) {
				$callback['error'] = json_encode($e);
			}
		} else {
			if ($email_available > 0) {
				$callback['error'] = 'Email is already in use.';
			}
			$callback['success'] = false;
		}
		
		return $callback;
	}
}
?>