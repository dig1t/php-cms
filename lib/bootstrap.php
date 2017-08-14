<?php
define('ROOT', dirname(dirname(__FILE__)));
define('DS', DIRECTORY_SEPARATOR);
define('DEBUG', true);

@date_default_timezone_set('America/Chicago');
@mb_internal_encoding('UTF-8');
@mb_http_output('UTF-8');
@error_reporting(E_ALL);

@ini_set('display_errors', defined('DEBUG') && DEBUG === true ? 'on' : 'off');
@ini_set('log_errors', 'on');
@ini_set('error_log', ROOT.DS.'tmp'.DS.'logs'.DS.'error.log');

require_once('config/config.php');

//require_once('vendor/autoload.php');

require_once('lib/database.php');
require_once('lib/renderer.php');
require_once('lib/router.php');

define('CONFIG', $config);

/**
 * Main application class
 */
class Bootstrap {
	public function __construct() {
		session_start();
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
  
  // Security functions
  
  private $__salt = '0Atr.V.J>M{oZc0gPk<Imk5Ob|bH)=oW'; // Change for every new website
  
	/**
	 * Generates a new salted string
	 *
	 * @return string containing salt string
	 */
  public function _salt() {
		return md5(uniqid($this->__salt, true));
	}
  
  // Validators
  
  public function isInt($string) {
    return is_int($string);
  }
  
  public function isText($string) {
    return true;
  }
  
  // Sanitizers
  
  public function sanitizeOutputText($string) {
		return htmlentities(stripslashes($string), ENT_QUOTES, 'UTF-8');
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
			// log to db
			error_log(print_r(debug_backtrace(), true));
		} else {
			$this->error($e);
		}
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