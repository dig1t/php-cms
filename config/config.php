<?php
defined('ROOT') OR exit;

$config = array();

$config['production'] = false;

$config['name'] = 'Site Title';
$config['domain'] = 'http://domain.xyz';
$config['cdn'] = '/assets';
$config['_ga'] = 'UA-XXXXX-Y';

$config['template'] = 'default';

$config['database'] = array(
	'host' => '127.0.0.1',
	'user' => 'root',
	'password' => '9CsrwKCCshdnsFKf',
	'default' => 'cms'
);

$config['meta'] = array(
	'keywords' => 'keyword1, keyword2',
	'robots' => 'index,follow',
	'mobile-web-app-capable' => true, // allow website to be used as a mobile app
	'viewport' => 'width=device-width, minimum-scale=1.0' // enable responsive design
);

$config['seo'] = array(
	'title' => $config['name'],
	'description' => 'Website description.'
);

$config['stripe'] = array(
	'api_key' => 'sk_test_v7aj5Q6eeemfpr1vAPftYHXO',
  'token' => 'tok_18u3SS2Di3EoaXWN9dmhMB7Q'
);
?>