<?php
$app->router->add('/', array(
  'controller' => 'start_page',
  'action' => 'view'
));

$app->router->add('/action/{action:[a-z]+}', array(
	'controller' => 'action',
	'action' => 'do'
));

$app->router->add('/error', array(
  'controller' => 'error',
  'action' => 'view'
));

$app->router->add('/login', array(
  'controller' => 'account',
  'action' => 'login'
));

$app->router->add('/login/reset', array(
  'controller' => 'account',
  'action' => 'reset'
));

$app->router->add('/register', array(
  'controller' => 'account',
  'action' => 'register'
));

$app->router->add('/user/login', array(
  'controller' => 'account',
  'action' => 'login'
));

$app->router->add('/user/login/reset', array(
  'controller' => 'account',
  'action' => 'reset'
));

$app->router->add('/user/register', array(
  'controller' => 'account',
  'action' => 'register'
));

$app->router->add('/{username:[a-zA-Z0-9]+}', array(
  'controller' => 'user',
  'action' => 'view'
));

$app->router->add('/{username:[a-zA-Z0-9]+}/post/{id:[0-9]+}', array(
  'controller' => 'user',
  'action' => 'post'
));

$app->router->add('/messages', array(
  'controller' => 'messages',
  'action' => 'view'
));

// Admin dashboard routes

$app->router->add('/admin', array(
  'controller' => 'admin',
  'action' => 'dashboard'
));

$app->router->add('/admin/media', array(
  'controller' => 'admin',
  'action' => 'media'
));

$app->router->add('/admin/posts', array(
  'controller' => 'admin',
  'action' => 'posts'
));

$app->router->add('/admin/posts/{id:[a-zA-Z0-9]+}', array(
  'controller' => 'admin',
  'action' => 'post'
));
?>