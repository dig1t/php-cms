<?php
class SEO {
	const properties = array(
		'default' => array(
			'title' => array('title', 'og:title', 'twitter:title'),
			'description' => array('description', 'og:description', 'twitter:description')
		),
		
		'opengraph' => array(
			'title' => 'og:title',
			'description' => 'og:description'
		),
		
		'twitter' => array(
			'card' => 'twitter:card',
			
			'site' => 'twitter:site',
			
			'creator' => 'twitter:creator',
			'creator:id' => 'twitter:creator:id',
			
			'title' => 'twitter:title',
			'description' => 'twitter:description',
			
			'image' => 'twitter:image',
			'image:alt' => 'twitter:image:alt',
			
			'player' => 'twitter:player',
			'player:width' => 'twitter:player:width',
			'player:height' => 'twitter:player:height',
			'player:stream' => 'twitter:stream',
			
			'player:stream:content_type' => 'twitter:stream:content_type',
			
			'app:id:iphone' => 'twitter:app:id:iphone',
			'app:id:ipad' => 'twitter:app:id:ipad',
			'app:id:googleplay' => 'twitter:app:id:googleplay',
			'app:url:iphone' => 'twitter:app:id:iphone',
			'app:url:ipad' => 'twitter:app:id:ipad',
			'app:url:googleplay' => 'twitter:app:url:googleplay',
			'app:country' => 'twitter:app:country'
		)
	);
	
	public static function getTags($property, $value, $set) {
		$tags = array();
		
		// check if property name exists in set
		if (array_key_exists($property, $set)) {
			// check if set contents is an array or a string
			if (is_string($set[$property])) {
				// set tag property as the string
				$tags[$set[$property]] = $value;
			} else {
				// set tags as all properties in array2
				foreach ($set[$property] as $name) {
					$tags[$name] = $value;
				}
			}
		} else {
			// set as given property if not found in set
			$tags[$property] = $value;
		}
		
		return $tags;
	}
	
	// HTML tag generators
	
	public static function meta($property, $content) {
		return '<meta property="'.$property.'" content="'.$content.'">';
	}
	
	public static function link($rel, $href, $title = null) {
		$content = '<link rel="'.$rel.'" href="'.$href.'"';
		
		// insert title attribute if defined or else close tag
		if (isset($title)) $content .= ' title="'.$title.'"';
		
		return $content.'>';
	}
	
	// APIs
	
	public static function OpenGraph($data) {
		$return = array();
		
		foreach ($data as $property => $value) {
			$return = array_merge($return, self::getTags($property, $value, self::properties['opengraph']));
		}
		
		return $return;
	}
	
	public static function twitter($data) {
		$return = array();
		
		foreach ($data as $property => $value) {
			array_merge($return, self::getTags($property, $value, self::properties['twitter']));
		}
		
		return $return;
	}
}

/**
 * Builds list of SEO tags
 * and echoes out with export() function
 */
class SEOBuild {
	public $metaTags = array();
	public $linkTags = array();
	
	public function add($data) {
		foreach ($data as $property => $value) {
			$this->metaTags = array_merge($this->metaTags, SEO::getTags($property, $value, SEO::properties['default']));
		}
		$this->metaTags = array_merge($this->metaTags, SEO::OpenGraph($data), SEO::twitter($data));
	}
	
	public function addOpenGraph($data) {
		$this->metaTags = array_merge($this->metaTags, SEO::OpenGraph($data));
	}
	
	public function addTwitter($data) {
		$this->metaTags = array_merge($this->metaTags, SEO::twitter($data));
	}
	
	/**
	 * Compiles and echoes all given data as meta and link tags
	 *
	 * @param array $data information about the page/website
	 * @return Array of all tags made with provided data.
	 */
	public function export() {
		foreach ($this->metaTags as $property => $value) {
			echo SEO::meta($property, $value);
		}
		
		foreach ($this->linkTags as $link) {
			$title = $link['title'] ?? NULL;
			echo SEO::link($link['rel'], $link['href'], $title);
		}
	}
}

$seo = new SEOBuild();


$seo->add(array(
	'title' => 'cat shop',
	'site' => '@catshop',
	'creator' => '@digitalscape_',
	'description' => 'A short description.'
));

$seo->export();

?>