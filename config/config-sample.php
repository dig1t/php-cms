<?php
defined('ROOT') OR exit;

$config = array();

$config['production'] = false;

$config['name'] = 'Website Title';
$config['domain'] = 'http://domain.xyz';
$config['cdn'] = '/static';
$config['_ga'] = 'UA-XXXXX-Y';

$config['template'] = 'default';

$config['database'] = array(
	'host' => '127.0.0.1',
	'user' => 'root',
	'password' => 'password',
	'default' => 'database' // Default Database to use
);

// Meta Tags
$config['meta'] = array(
	'description' => 'Website description.',
	'keywords' => 'website, keywords, for, seo',
	'robots' => 'index,follow'
);
?>