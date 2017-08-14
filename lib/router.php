<?php
defined('ROOT') OR exit;

class Router {
  protected $routes = array();
  
  protected $controller;
  protected $method;
  
  protected $params = [];
  protected $prefix = '/';
  
  protected $paramExp = '/({([a-zA-Z0-9]+)(:?([^}]*)?}{1})?)/';
  
  public function prefix($prefix = '/') {
    $this->prefix = $prefix;
  }
  
  public function add($pattern, $options) {
    $tokens = array();
    $pattern = preg_replace_callback($this->paramExp, function($matches) use(&$tokens) {
      if (!$matches[4]) $matches[4] = '[\w]+';
      $tokens[] = $matches[2];
      return '('.$matches[4].')';
    }, preg_replace('/\//', '\/$1', ltrim($this->prefix.$pattern, '/')));
    $this->routes[] = array(
      'pattern' => $pattern,
      'options' => $options,
      'tokens' => $tokens
    );
  }
  
  public function parse($url = '/') {
    if ($url === '/' && isset($_GET['url'])) $url = $_GET['url'];
    
    $url = filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL);
    
    foreach($this->routes as $route) {
      preg_match('/^'.$route['pattern'].'$/', $url, $matches);
      
      if ($matches) {
        array_shift($matches);
        $route['params'] = array();
        
        foreach($route['tokens'] as $i => $token) {
          if (isset($matches[$i])) $route['params'][$token] = $matches[$i];
        }
        
        return array_merge($route['options'], array(
          'params' => $route['params']
        ));
      }
    }
  }
}
?>