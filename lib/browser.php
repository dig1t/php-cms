<?php
class browser {
	public $mobileOSRegex = '/mobi|ip(hone|od|ad)|android|blackberry/';
	
	public $osRegex = array(
		'/windows/i'    => 'Windows',
		'/cros/i'       => 'Chromebook',
		'/android/i'    => 'Android',
		'/linux/i'	    => 'Linux',
		'/unix/i'	    => 'Unix',
		'/ipod/i'	    => 'iPod',
		'/ipad/i'	    => 'iPad',
		'/iphone/i'	    => 'iPhone',
		'/mac/i'	    => 'Mac',
		'/blackberry/i' => 'BlackBerry'
	);
	
	public $browserRegex = array(
		'/navigator(.*)/i' => 'Navigator',
		'/firefox(.*)/i'   => 'Firefox',
		'/msie(.*)/i'      => 'Internet Explorer',
		'/chrome(.*)/i'    => 'Google Chrome',
		'/crios(.*)/i'     => 'Chrome iOS',
		'/safari(.*)/i'    => 'Safari',
		'/maxthon(.*)/i'   => 'Maxthon',
		'/opera(.*)/i'     => 'Opera'
	);
	
	private $ipEnvs = array(
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR'
	);
	
	public $os;
	public $browser;
	public $mobile;
	public $ipAddress;
	
	public function __construct() {
		$this->agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
		
		if (preg_match(strtolower($this->mobileOSRegex), $this->agent)) {
			$this->mobile = true;
		}
		
		foreach($this->osRegex as $key => $value) {
			if (preg_match(strtolower($key), $this->agent)) {
				$this->os = $value;
				break;
			}
		}
		
		foreach($this->browserRegex as $key => $value){
			if (preg_match(strtolower($key), $this->agent)) {
				$this->browser = $value;
				break;
			}
		}
		
		foreach($this->ipEnvs as $env) {
			if (getenv($env)) {
				$this->ipAddress = getenv($env);
				
				if ($this->ipAddress == '::1') $this->ipAddress = '127.0.0.1';
				
				break;
			}
		}
		
		if (empty($this->browser)) {
			$this->browser = 'unknown';
			$this->mobile = true;
		}
		
		if (empty($this->os)) {
			$this->os = 'unknown';
			$this->mobile = true;
		}
	}
}
?>