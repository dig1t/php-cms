<?php
defined('ROOT') OR exit;

//require_once('models/account.php');
require_once('lib/auth.php');

class account extends Bootstrap {
  public function __construct() {
		
  }
	
	public function view() {
		!$this->get($_SESSION, 'logged_in') ?? false ? $this->landing() : $this->home();
	}
	
	public function register() {
		if ($this->get($_SESSION, 'logged_in')) $this->redirect('/');
		$this->makeView('pages/register');
		$this->view->setConfig('pageModule', 'register');
		$this->view->set('form_token', Auth::formToken());
		$this->view->set('form_time', $_SESSION['form_time'] = time());
		$this->view->render();
	}
	
	public function landing() {
		$this->makeView('pages/landing');
		$this->view->setConfig('pageModule', 'landing');
		$this->view->set('background', Landing::getBackground());
		$this->view->set('form_token', Auth::formToken());
		$this->view->set('form_time', $_SESSION['form_time'] = time());
		$this->view->render();
	}
  
  public function home() {
    $this->makeView('landing');
    $this->view->setConfig('pageModule', 'landing');
    $this->view->render();
  }
}
?>