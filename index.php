<?php
/**
 * @author Digitalscape
 * @package CMS Framework
 */

require_once('lib/bootstrap.php');

//$app = app();

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

$app->run();
?>