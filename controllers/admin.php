<?php
defined('ROOT') OR exit;

class Admin extends App {
  public function __construct() {
    
  }
  
  public function view($name = 'dashboard') {
    $this->makeView($name, false, false);
    
    $this->view->setConfig('title', 'Admin / '.ucwords($name));
    $this->view->setConfig('template_html_folder', 'admin');
    $this->view->setConfig('pageClass', 'admin');
    $this->view->setConfig('pageModule', 'admin/'.$name);
    $this->view->setTemplate('footer', false);
    
    $this->view->css('css/admin.css');
    $this->view->set('view', $name);
  }
  
  /*
  <div class="breadcrumbs">
    <a href="/admin">Home</a><divider>/</divider><a href="/media">Media</a>
  </div>
  */
  
  public function breadcrumbs($path = null, $url = null) {
    $html = '<div class="breadcrumbs"><a href="/admin">Home</a>';
    
    if (isset($path)) {
      if (!isset($url)) $url = str_replace('/', ' ', $path);
      
      $url = explode(' ', $url);
      $path = explode('/', $path);
      
      foreach($path as $i => $thing) {
        $html.= '<divider>/</divider><a ';
        
        if ($i + 1 === count($path)) {
          $html.= 'class="disabled"';
        } else {
          $html.= 'href="/admin/'.$url[$i].'"';
        }
        
        $html.= '>'.ucwords($thing).'</a>';
      }
    }
    
    $this->view->set('breadcrumbs', $html.'</div>');
  }
  
  public function getPosts($page = 1, $length = 10) {
    $at = ($page - 1) * $length;
    $q = $this->db->prepare('SELECT * FROM post LIMIT ?, ?');
    $q->bindParam(1, $at, PDO::PARAM_INT);
    $q->bindParam(2, $length, PDO::PARAM_INT);
    $q->execute();
    
    return $q->fetchAll(PDO::FETCH_ASSOC);
  }
  
  public function getPost($id) {
    if (isset($id)) {
      $q = $this->db->prepare('SELECT * FROM post WHERE post_id=?');
      $q->bindParam(1, $id, PDO::PARAM_STR);
      $q->execute();
      
      return $q->fetch(PDO::FETCH_ASSOC);
    }
  }
  
  // View functions
  
  public function dashboard() {
    $this->view();
    $this->breadcrumbs();
    
    $this->view->render();
  }
  
  public function media() {
    $this->view('media');
    $this->breadcrumbs('media');
    
    $this->view->render();
  }
  
  public function posts() {
    $this->view('posts');
    $this->breadcrumbs('posts');
    
    $page = 1;
    
    if (isset($_GET['page']) && $this->isInt($_GET['page'])) $page = $_GET['page'];
    
    $list = $this->getPosts($page);
    
    $this->view->set('posts', $list);
    
    $this->view->render();
  }
  
  public function post() {
    $this->view('post');
    $this->breadcrumbs('posts/post');
    $id = '';
    
    if (isset($this->params['id'])) $id = $this->params['id'];
    
    $post = $this->getPost($id);
    
    $this->view->set('post', $post);
    $this->view->render();
  }
}
?>