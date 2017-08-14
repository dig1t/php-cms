<?php
class SEO {
	public $properties = array(
		'og' => array(
			'title' => array('title', 'og:title', 'twitter:title'),
			'description' => array('description', 'og:description', 'twitter:description'),
			'' => '',
		),
		
		'twitter' = array(
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
			'app:country' => 'twitter:app:country',
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
		return '<meta property="'.$property.'" content="'.$content.'">"';
	}
	
	public static function link($rel, $href, $title = null) {
		$content = '<link rel="'.$rel.'" href="'.$href.'"';
		
		// insert title attribute if defined or else close tag
		isset($title) ? $content .= ' title="'.$title.'">' : $content .= '>';
		
		return $content;
	}
	
	// APIs
	
	public static function OpenGraph($data) {
		return $this->getTags($property, $value, $this->properties['og'])
	}
	
	public static function twitter($data) {
		return $this->getTags($property, $value, $this->properties['twitter']);
	}
}

/**
 * Builds list of SEO tags
 * and echoes out with export() function
 */
class SEOBuild extends SEO {
	public $metaTags = array();
	
	public static function addOpenGraph($data) {
		foreach ($data as $property => $value) {
			array_merge($this->metaTags, $this->OpenGraph($data));
		}
	}
	
	public static function addTwitter($data) {
		foreach ($data as $property => $value) {
			array_merge($this->metaTags, $this->twitter($data));
		}
	}
	
	/**
	 * Compiles and echoes all given data as meta and link tags
	 *
	 * @param array $data information about the page/website
	 * @return Array of all tags made with provided data.
	 */
	public function export() {
		foreach ($this->metaTags as $property => $value) {
			echo $this->meta($property, $value);
		}
		
		foreach ($this->linkTags as $link) {
			$title = $link['title'] ?? : NULL;
			echo $this->link($link['rel'], $link['href'], $title);
		}
	}
}

$seo = new SEOBuild();


$seo->make(array(
	'title' => 'cat shop',
	'site' => '@catshop',
	'creator' => '@digitalscape_',
	'description' => 'A short description.'
));

$seo->export();

?>