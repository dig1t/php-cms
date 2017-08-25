<?php
define('ROOT', dirname(dirname(__FILE__)));
define('DS', DIRECTORY_SEPARATOR);
define('DEBUG', true);

@date_default_timezone_set('UTC');
@mb_internal_encoding('UTF-8');
@mb_http_output('UTF-8');
@error_reporting(E_ALL);

@ini_set('display_errors', defined('DEBUG') && DEBUG === true ? 'on' : 'off');
@ini_set('log_errors', 'on');
@ini_set('error_log', ROOT.DS.'tmp'.DS.'logs'.DS.'error.log');
@ini_set('session.use_trans_sid', false);

require_once('config/config.php');

//require_once('vendor/autoload.php');

require_once('lib/database.php');
require_once('lib/renderer.php');
require_once('lib/router.php');
require_once('lib/browser.php');

define('CONFIG', $config);

/**
 * Main application class
 */
class Bootstrap {
	public $userData;
	
	public function __construct() {
		session_start();
		
		$this->browser = new browser();
		
		if (isset($_SESSION['user_agent_hash'])) {
			// redirect to landing page and log out
			if (md5($this->browser->agent) !== $_SESSION['user_agent_hash']) $this->redirect('/', true);
		} else {
			$_SESSION['user_agent_hash'] = md5($this->browser->agent);
		}
	}
	
  /**
   * Run the application
   */
  public function run() {
		// If a template is being used, check for functions.php file
    if (defined(CONFIG['template']) && $this->exists(TEMPLATE_PATH.'functions.php')) include_once(TEMPLATE_PATH.'functions.php');
    
    $this->route = $this->router->parse();
    
    if (isset($this->route['controller'])) {
      try {
				// Check if the controller is a template controller,
				// which is defined in the functions.php file of the template.
        if (!defined(CONFIG['template']) ||
				!isset($route['template_controller']) ||
				!$route['template_controller'] === true) {
          $controller = ROOT.DS.'controllers'.DS.$this->route['controller'].'.php';
          
          if (file_exists($controller)) require_once($controller);
        }
        
        $this->controller = new $this->route['controller'];
        $this->controller->params = isset($this->route['params']) ? $this->route['params'] : array();
        
        if (isset($this->route['action']) && method_exists($this->controller, $this->route['action'])) {
          call_user_func(array($this->controller, $this->route['action']));
        }
      } catch(Exception $e) {
        $this->error('Cannot find controller');
      }
    } else if (isset($this->route['callback'])) {
      call_user_func($this->route['callback'], $this->route['params']);
    } else {
      header('location /error?e=404');
    }
  }
  
  // Initiate a new view
  
	/**
	 * Makes a new view to output to client
	 *
	 * @param string $path path of the view
	 * @param bool $render renders view automatically instead of using $view->render() function
	 * @param bool $templatePage new view is a custom template page
	 * @return View
	 */
  public function makeView($path, $render = false, $templatePage = true) {
    $this->view = new View($path, $templatePage);
    
    if ($render) $this->view->render();
    
    return $this->view;
  }
  
  // Security & Account functions
	
	private $__salt = '0Atr.V.J>M{oZc0gPk<Imk5Ob|bH)=oW'; // Change for every new website
	
	public function randomString($length = 24, $table = null, $column = null) {
		$keys = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$keysLen = strlen($keys);
		$token = '';
		
		$log = log($keysLen, 2);
		$bytes = (int) ($log / 8) + 1; // length in bytes
		$bits = (int) $log + 1; // length in bits
		$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
		
		for($i = 0; $i < $length; $i++){
			do {
				$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
				$rnd = $rnd & $filter;
			} while ($rnd >= $keysLen);
			
			$token .= $keys[$cryptoRandom];
		}
		
		return $table !== null && $column !== null ? ($this->isToken($token, $table, $column) ? null : $token) : $token;
	}
	
	/**
	 * Generates a new salted string
	 *
	 * @return string containing salt string
	 */
  public function _salt() {
		return md5(uniqid($this->__salt ?? time(), true));
	}
	
	/**
	 * Hashes a string using SHA-256 encryption
	 *
	 * @param string $string text to hash
	 * @return string hashed text
	 */
	public function hashString($string) {
		return hash('sha256', $string);
	}
	
  // Validators
	
	public $validateRegex = array(
		'auth' => '/^[a-zA-Z0-9]+$/',
		'id' => '/^[a-zA-Z0-9]+$/',
		'shortcut' => '/^[a-zA-Z0-9_]+$/',
		'int' => '/^[0-9]+$/',
		'name' => '/^[a-zA-Z]+$/',
		'password' => '/^[a-zA-Z0-9 ~`!@#$%^&*()_+\-=[\]\\{}|;\':",.\/<>?]+$/',
		'text' => '/^[a-zA-Z0-9 ~`!@#$%^&*()_+\-=[\]\\{}|;\':",.\/<>?\n]+$/',
		'username' => '/^[a-zA-Z0-9_]+$/',
		'name' => '/^.+$/',
		'amount' => '/^[-]?[0-9]+\$/',
		'email' => '/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/'
	);
	
	public function validate($string, $type) {
		if ($type === 'int' && !is_numeric($string)) {
			return false;
		}
		
		if (strlen($string) >= 1 && isset($this->validateRegex[$type])) {
			if (preg_match($this->validateRegex[$type], $string)) {
				return true;
			}
		}
		
		return false;
	}
  
  public function isInt($string) {
    return is_int($string);
  }
  
  public function isText($string) {
    return true;
  }
  
  // Sanitizers
  
  public function sanitizeOutputText($string) {
		return htmlentities($string, ENT_QUOTES, 'UTF-8');
	}
	
	public function stripNumbers($text) {
		return preg_replace('/[^a-zA-Z]+/', '', $text);
	}
	
	public function stripLetters($text) {
		return preg_replace('/[^0-9\-]+/', '', $text);
	}
  
  // Misc
  
	/**
	 * Echo out an error
	 *
	 * @param string $e Error to be echoed
	 */
  public function error($e) {
    echo '<error>'.$e.'</error>';
  }
  
	/**
	 * Logs error to database if in production mode or else echoes.
	 *
	 * @param string $e Error to be stored
	 */
  public function logError($e) {
    if (!DEBUG) {
			// log to db in the future
			error_log(print_r(debug_backtrace(), true));
		} else {
			$this->error($e);
		}
  }
  
	/**
	 * Get variable in array
	 *
	 * @param string $var variable to get
	 * @return variable or null
	 */
  public function get($array, $var) {
    return $array[$var] ?? null;
  }
  
	/**
	 * Checks if file exists
	 *
	 * @param string $path Path of file to be checked
	 */
  public function exists($path) {
    try {
      if (file_exists($path) === false) {
        $this->error('can\'t find '.$path.'</error>');
        return false;
      }
      
      return true;
    } catch(Exception $e) {
      $this->error($e);
    }
  }
	
	/**
	 * Redirect user to a different route
	 *
	 * @param string $path Path of new route
	 * @param bool $logout optional Logout user before redirecting
	 */
	public function redirect($path, $logout = false) {
		if ($logout) {
			session_start();
			unset($_SESSION);
			session_destroy();
		}
		
		header('location: '.$path);
	}
	
	/**
	 * Separates new lines in <p> tags
	 *
	 * @param bool $logout optional Logout user before redirecting
	 */
	public function nl2p($string) {
		$newString = '';
		
		foreach(explode('\n', $string) as $p) {
			$p = preg_replace('/(<blockquote>(.*?)<\/blockquote>)/', '</p>$1<p>', $p);
			$newString .= '<p>'.$p.'</p>';
		}
		
		return $newString;
	}
	
	public function getDay($timestamp) {
		return date('d', $timestamp);
	}
	
	public $months = array(
		1 => 'January',
		2 => 'February',
		3 => 'March',
		4 => 'April',
		5 => 'May',
		6 => 'June',
		7 => 'July',
		8 => 'August',
		9 => 'September',
		10 => 'October',
		11 => 'November',
		12 => 'December'
	);
	
	public function getMonth($timestamp, $text = true) {
		$month = date('m', $timestamp);
		
		return $text ? $this->months[$month] : $month;
	}
	
	public function getYear($timestamp) {
		return date('Y', $timestamp);
	}
}

/**
 * Application Initiator
 *
 * @return initialized application
 */
function app() {
  $app = new Bootstrap();
  
  $app->router = new Router();
  $app->db = new Database();
  
  // Load all configuration files after defining $app
  require_once('config/routes.php');
  
	// Check if a template is being used
  if (defined(CONFIG['template'])) {
    define('TEMPLATE_PATH', ROOT.DS.'templates'.DS.CONFIG['template'].DS);
    
		// Check if a config.php file for the template exists.
    if ($app->exists(TEMPLATE_PATH.'config.json')) {
      define('TEMPLATE', json_decode(file_get_contents(TEMPLATE_PATH.'config.json'), true));
    } else {
      $app->error('Template config file for '.CONFIG['template'].' not found.');
    }
  }
  
  return $app;
}
?>