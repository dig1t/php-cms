<?php
defined('ROOT') OR exit;

require_once('models/landing.php');
require_once('lib/auth.php');

class start_page extends Bootstrap {
  public function __construct() {
		
  }
	
	public function view() {
		!$this->get($_SESSION, 'logged_in') ?? false ? $this->landing() : $this->home();
	}
	
	public function landing() {
		$this->makeView('pages/landing');
		$this->view->setConfig('pageModule', 'landing');
		$this->view->set('background', Landing::getBackground());
		$this->view->set('form_token', Auth::formToken());
		$this->view->set('form_time', Auth::formTime());
		$this->view->render();
	}
  
  public function home() {
    $this->makeView('landing');
    $this->view->setConfig('pageModule', 'landing');
    $this->view->render();
  }
}
?>