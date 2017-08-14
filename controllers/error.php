<?php
defined('ROOT') OR exit;

class ErrorPage extends App {
  public function __construct() {
    
  }
  
  public function view() {
    $this->makeView('error');
    $_ERROR = 'Error';
    $_MESSAGE = 'Something went wrong.';
    $_ICON = 'fa-frown-o';
    $error_id;
    
    if (isset($_GET['e'])) $error_id = $_GET['e'];
    
    switch($error_id) {
    	case 400:
    		$_ERROR = 400;
    		break;
    	case 401:
    		$_ERROR = 401;
    		$_MESSAGE = 'Unauthorized page.';
    		break;
    	case 403:
    		$_ERROR = 403;
    		$_MESSAGE = 'Access forbidden.';
    		break;
    	case 404:
    		$_ERROR = 404;
    		$_MESSAGE = 'The page you requested was not found.';
    		break;
    	case 500:
    		$_ERROR = 500;
    		$_MESSAGE = 'Something went wrong.';
    		$_ICON = 'fa-bug';
    		break;
    	case 'b':
    		$_ERROR = 'banned';
    		$_MESSAGE = 'You do not have access to the site.';
    		$_ICON = 'fa-lock';
    		break;
    }
    
    $this->view->set('icon', $_ICON);
    $this->view->set('error', $_ERROR);
    $this->view->set('message', $_MESSAGE);
    //$this->view->setConfig('pageModule', 'error');
    $this->view->render();
  }
}
?>