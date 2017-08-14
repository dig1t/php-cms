<?php
defined('ROOT') OR exit;

class View extends Bootstrap {
	protected $path;
	protected $file;
	
	protected $values = array();
	
	protected $templateFiles = array();
	protected $css = array();
	protected $js = array();
	protected $requireJS = array();
	
	/** set default template configuration **/
	
	public $config = array(
		'name' => 'Website',
		'html' => false,
		'cdn' => '/static',
		'template' => 'default',
		'template_folder_name' => 'templates'
	);
	
	public $meta = array(
		'description' => '',
		'robots' => 'index,follow',
		'viewport' => 'width=device-width, minimum-scale=1.0'
	);
	
	public $template = array(
		/** meta **/
		'name' => 'Unknown',
		'version' => '0.0',
		
		/** settings **/
		'navigation' => true,
		'footer' => true,
		'ext' => '.html'
	);
	
	public function __construct($path, $templatePage = true) {
		if (defined('CONFIG')) {
			foreach($this->config as $key => $value) {
				if (isset(CONFIG[$key])) $this->config[$key] = CONFIG[$key];
			}
			
			if (isset($config['meta'])) {
				foreach($config['meta'] as $key => $value) {
					$this->meta[$key] = $value;
				}
			}
			
			if (isset($config['values'])) {
				foreach($config['values'] as $key => $value) {
					$this->meta[$key] = $value;
				}
			}
		}
		
		/*if (!isset($this->config['cdn'])) {
			$this->config['cdn'] = $this->getCDN();
		}*/
		
		if (defined(CONFIG['template']) && $templatePage) {
			$this->template['path'] = $this->config['template_folder_name'].DS.$this->config['template'].DS;
			$this->template['folder'] = $this->config['template_folder_name'].'/'.$this->config['template'].'/';
			
			foreach(TEMPLATE as $key => $value) {
				$this->template[$key] = $value;
			}
			
			/** template page config **/
			if (isset(TEMPLATE['template_extension_type'])) $this->template['ext'] = '.'.TEMPLATE['template_extension_type'];
			
			if (isset(TEMPLATE['pages']) && isset(TEMPLATE['pages'][$path])) {
				if (isset(TEMPLATE['css'])) {
					if (gettype(TEMPLATE['css']) === 'string') {
						$this->css(TEMPLATE['css']);
					} else {
						foreach(TEMPLATE['css'] as $file) {
							$this->css($file);
						}
					}
				}
				
				foreach(TEMPLATE['pages'][$path] as $key => $value) {
					if ($key === 'css') {
						if (gettype($value) === 'string') {
							$this->css($value);
						} else {
							foreach($value as $file) {
								$this->css($file);
							}
						}
					} else {
						$this->template[$key] = $value;
					}
				}
			}
			
			if ($this->exists(ROOT.DS.$this->template['path'].'styles'.$this->template['ext'])) {
				include($this->template['path'].'styles'.$this->template['ext']);
			} else {
				include('views'.DS.'styles.html');
			}
			
			if ($this->exists($this->template['path'].'style.css')) array_push($this->css, '/'.$this->template['folder'].'style.css');
			
			$this->setConfig('jsTemplatePath', $this->template['folder'].'assets/js');
		} else {
			$this->template['path'] = 'views'.DS;
			$this->template['folder'] = 'views'.'/';
			
			if (!isset($this->template['path'])) {
				$this->template['path'] = 'views'.DS;
			}
			
			include('views'.DS.'styles.html');
			$this->setConfig('jsTemplatePath', $this->config['cdn'].'/js');
		}
		
		$this->config['path'] = $path;
		
		if (isset($_GET['html']) && $_GET['html'] === 'true') $this->config['html'] = true;
	}
	
	private function insert($path, $templateFile = true) {
		$templatePath = $this->template['path'];
		
		if (isset($this->config['template_html_folder'])) $templatePath .= $this->config['template_html_folder'].DS;
		
		$path = $templateFile ? $templatePath.$path : $path;
		$ext = $templateFile ? $this->template['ext'] : '.html';
		
		if ($this->exists(ROOT.DS.$path.$ext)) include($path.$ext);
	}
	
	private function getAsset($path) {
		return $this->template['folder'].'assets/'.$path;
	}
	
	/*
	 * add css, js, assets and require modules to the header
	**/
	
	public function css($path = null, $asset = false, $external = false) {
		if ($path) {
			if ($external === false) $path = $asset ? $this->template['folder'].'assets/css/'.$path : $this->config['cdn'].'/'.$path;
			array_push($this->css, $path);
		}
	}
	
	public function js($path = null, $asset = false) {
		if ($path) {
			$path = $asset ? $this->template['folder'].'assets/js/'.$path : $this->config['cdn'].'/'.$path;
			array_push($this->js, $path);
		}
	}
	
	public function requireJS($module = null) {
		if ($module) array_push($this->requireJS, $module);
	}
	
	/*
	 * set template configuration
	**/
	
	public function set($key, $value) {
		$this->values[$key] = $value;
	}
	
	public function setTemplate($key, $value) {
		$this->template[$key] = $value;
	}
	
	/** set website configuration **/
	public function setConfig($key, $value) {
		$this->config[$key] = $value;
	}
	
	/** set a meta tag **/
	public function setMeta($name, $content) {
		$this->meta[$name] = $content;
	}
	
	/** util **/
	public function each($array, $callback) {
		foreach($array as $child) {
			$callback($child);
		}
	}
	
	public function render() {
		ob_start('ob_gzhandler');
		
		if (!$this->config['html']) {
			if (!isset($this->config['title'])) $this->config['title'] = $this->config['name'];
			
			/*if (authenticated) {
				$user = new user();
				$this->profile = $user->getProfile();
				
				if ($this->profile['is_admin'] === '1') {
					$this->css('css/admin.css');
					echo 'admin found';
				}
			}*/
			
			echo '<!DOCTYPE html><title>'.$this->config['title'].'</title>';
			
			foreach($this->meta as $name => $content) {
				if ($content === true) $content = 'true';
				
				echo '<meta name="'.$name.'" content="'.$content.'" />';
			}
			
			$this->insert('views'.DS.'header', false);
			
			foreach($this->css as $path) {
				echo '<link href="'.$path.'" rel="stylesheet">';
			}
			
			foreach($this->js as $path) {
				echo '<script src="'.$path.'" async></script>';
			}
			
			foreach($this->requireJS as $module) {
				echo '<script>require(["'.$module.'"])</script>';
			}
			
			if ($this->template['navigation'] === true) {
				/*if (authenticated) {
					$this->insert($this->config['navigation']);
				} else {*/
					$this->insert('navigation-guest');
				//}
			}
		}
		
		$class = ' ';
		
		if (isset($this->config['pageClass'])) $class .= 'class="'.$this->config['pageClass'].'" ';
		
		echo isset($this->config['pageModule']) ? '<main'.$class.'id="app" data-module="'.$this->config['pageModule'].'">' : '<main'.$class.'id="app">';
		
		foreach ($this->values as $key => $value) $this->$key = $value;
		
		if (isset($this->config['path'])) $this->insert($this->config['path']);
		
		if (isset($this->template['footer']) && $this->template['footer'] === true) $this->insert('footer');
		
		echo '</main>';
		echo preg_replace('/^\s+|\n|\r|\s+$/m', '', ob_get_clean());
	}
}
?>