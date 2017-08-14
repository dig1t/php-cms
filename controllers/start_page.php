<?php
defined('ROOT') OR exit;

require_once('models/landing.php');

class start_page extends Bootstrap {
  public function __construct() {
		
  }
	
	public function view() {
		$this->makeView('pages/landing');
    $this->view->setConfig('pageModule', 'landing');
		$this->view->set('background', Landing::getBackground());
    $this->view->render();
	}
  
  public function home() {
    $this->makeView('landing');
    $this->view->setConfig('pageModule', 'landing');
    $this->view->render();
  }
}
?>