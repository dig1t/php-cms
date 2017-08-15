<?php
require_once('lib/SEO.php');

$seo = new SEOBuild();

$seo->add(array(
	'title' => 'cat website',
	'description' => 'A short description.',
	
	// automatically added to opengraph & twitter tags
	'image' => 'http://placekitten.com.s3.amazonaws.com/homepage-samples/408/287.jpg',
	'image:alt' => 'kitten'
));

$seo->addTwitter(array(
	'card' => 'summary', // type of twitter card
	'site' => '@cats', // website profile
	'creator' => '@digitalscape_', // author profile
));

// echo out all given tags
$seo->export();
?>