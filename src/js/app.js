"use strict";

if (window.$) $.AMDify();

// Parallax
(function($) {
  var Parallax = function(config) {
		this.posY = 0;
    this.speed = 0.5;
    this.screenY = window.innerHeight;
		
		for (var i in config) {
			if (config[i]) this[i] = config[i];
		}
		
		this.init();
  };
	
  Parallax.init = function(data) {
    this.scroll();
  };
  
  Parallax.check = function() {
    var actualHeight = this.screenY;
    
    this.ok = true;
    if (this.style.backgroundSize === '' || this.element.backgroundSize === 'auto') {
      if (this.height < this.element.offsetHeight) {
        this.ok = false;
      } else {
        actualHeight = this.height;
				
        if (this.height < this.screenY) {
          actualHeight = actualHeight + ((this.screenY - this.element.offsetHeight) * this.speed);
        }
      }
    } else if (this.element.backgroundSize === 'cover') {
      if (this.screenY < this.element.offsetHeight) {
        this.ok = false;
      }
    }
		
    this.diff = -(actualHeight - this.element.offsetHeight) * this.speed;
    this.diff = this.diff - (this.element.offsetHeight * 0);
  }
  
  Parallax.scroll = function() {
    var per, position;
    
    if (window.pageYOffset !== void 0) {
      this.posY = window.pageYOffset;
    } else {
      this.posY = (document.documentElement || document.body.parentNode || document.body).scrollTop;
    }
		
    this.check();
		
    if (this.ok && this.style.backgroundAttachment === 'fixed' && this.obj.inViewport(true)) {
      per = (this.posY - this.element.offsetTop + this.screenY) / (this.element.offsetHeight + this.screenY);
      position = this.diff * (per - 0.5);
			
      if (this.style.backgroundSize !== 'cover') {
        position = position + ((this.screenY - this.img.height) / 2);
      }
			
      position = (Math.round(position * 100) / 100) + 'px';
    } else {
      position = 'center';
    }
    
    this.element.style.backgroundPosition = 'center ' + position;
  };
	
	$.each(Parallax, function(v, k) {
		Parallax.prototype[k] = v;
	});
  
  $.parallax = function(e, speed) {
		var element = $(e);
		var style = e.currentStyle || window.getComputedStyle(e, false),
			url = style.backgroundImage.slice(5, -2);
		
		if (!url) {
			url = element.data('image');
			element.css('background-image', 'url(' + url + ')');
		}
		
		console.log(url);
		
    if (url) {
      var img = new Image();
      
      img.onload = function() {
        var parallax = new Parallax({
          element: e,
					obj: element,
					img: img,
          width: this.naturalWidth,
          height: this.naturalHeight,
          style: style,
					speed: speed
        });
				
				window.addEventListener('scroll', function() {
		      parallax.scroll();
		    });
				
		    window.addEventListener('resize', function() {
		      parallax.screenY = window.innerHeight;
		      parallax.scroll();
		    });
      };
			
      img.src = url;
    }
  };
	
	$.plugin.parallax = function(e, speed) {
		$.eachElement(this, function(v, k) {
			$.parallax(v, speed);
		});
	};
	
	$.tooltip = function(element, text) {
		if (element instanceof HTMLElement) element = $(element);
		
		var hovering;
		
		element.mouseover(function() {
			hovering = true;
			
			setTimeout(function() {
				if (!hovering) return;
				
				if (!text) text = element.data('tooltip');
				var pos = element[0].getBoundingClientRect();
				
				if (!text) return;
				
				tooltipText[0].innerText = text;
				tooltip[0].style.display = 'block';
				
				var width = tooltip[0].offsetWidth;
				var height = tooltip[0].offsetHeight;
				var tooltipOffset = 10;
				
				var top = pos.top - height - tooltipOffset;
				var left = pos.left + (pos.width/2) - (width/2);
				
				var nubClass = 'bottom';
				var overridePos = element.data('tooltip-pos');
				
				if (top < 0 && !overridePos || overridePos === 'top') {
					top = pos.top + pos.height + tooltipOffset;
					left = (pos.left + (pos.width/2)) - (width / 2);
					nubClass = 'top';
				}
				
				if ((left > window.innerWidth || tooltip[0].offsetWidth + left > window.innerWidth && !overridePos) || overridePos === 'right') {
					left = pos.left - width - tooltipOffset;
					top = pos.top - (height/2) + (pos.height/2);
					nubClass = 'right';
				}
				
				if ((left < 0 || tooltip[0].offsetWidth + left > window.innerWidth && !overridePos) || overridePos === 'left') {
					left = pos.left + pos.width + tooltipOffset;
					top = pos.top - (height/2) + (pos.height/2);
					nubClass = 'left';
				}
				
				tooltipNub[0].className = 'nub '+nubClass;
				tooltip[0].style.top = top+'px';
				tooltip[0].style.left = left+'px';
			}, 100);
		});
		
		element.mouseleave(function() {
			hovering = false;
			tooltip[0].style.display = 'none';
		});
	};
	
	$.carousel = function(config) {
		var err, errored;
		
		config.err ? err = function(e) {
			config.err(e);
			errored = true;
		} : err = function() {
			errored = true
		};
		
		if (!config.container || (config.container && !config.container.length)) err('Carousel container missing.');
		if (!config.images && !config.hasSlides) err('Carousel slides are missing.');
		if (!config.hasSlides && (!typeof(config.images) === 'object' || !config.images.length)) err('Must be an array.');
		if (!config.hasSlides && config.images.length <= 0) err('No images in array.');
		
		if (errored) return;
		
		var moving = false;
		
		var slides = [];
		var currentSlide = 0;
		
		if (!config.interval) config.interval = 4000;
		
		// main function
		
		var context = config.container;
		var inner = $('<div class="inner">');
		var controls = $('<div class="controls">');
		var slideContainer = $('<ul>');
		
		if (!config.hasSlides) {
			for (var i = 0, image; image = config.images[i]; i++) {
				var container = $('<li>');
				var img = $('<img>');
				
				img.attr('src', image);
				container.append(img);
				slides.push(container);
			}
		} else {
			var parent = config.container.parent();
			var newContainer = $('<div class="carousel">');
			var _slides = config.container.find('li');
			
			newContainer.attr('data-carousel', config.container.attr('data-carousel'));
			config.container.remove();
			config.container = newContainer;
			parent.append(newContainer);
			context = newContainer;
			
			for (var i = 0, slide; slide = _slides[i]; i++) {
				slides.push($(slide));
			}
		}
		
		for (var i = 0, slide; slide = slides[i]; i++) {
			slideContainer.append(slide);
		}
		
		slides[0].toggleClass('active');
		
		var left = $('<div class="previous">');
		var right = $('<div class="next">');
		
		left.append($('<span class="fa fa-angle-left">'));
		right.append($('<span class="fa fa-angle-right">'));
		
		controls.append(left);
		controls.append(right);
		
		inner.append(slideContainer);
		inner.append(controls);
		context.append(inner);
		
		var slide = function(e, dir) {
			if (moving) return;
			moving = true;
			var current = slides[currentSlide];
			
			if (dir === 'left') {
				currentSlide--;
				if (currentSlide < 0) currentSlide = slides.length - 1;
				
				var previous = slides[currentSlide];
				
				current.toggleClass('right');
				previous.toggleClass('previous');
				
				setTimeout(function() {
					previous.toggleClass('right');
				}, 1);
				
				setTimeout(function() {
					current.toggleClass('active');
					current.toggleClass('right');
					previous.toggleClass('active');
					previous.toggleClass('previous');
					previous.toggleClass('right');
					moving = false;
				}, 800);
			} else {
				currentSlide++;
				if (currentSlide >= slides.length) currentSlide = 0;
				
				var next = slides[currentSlide];
				
				current.toggleClass('left');
				next.toggleClass('next');
				
				setTimeout(function() {
					next.toggleClass('left');
				}, 1);
				
				setTimeout(function() {
					current.toggleClass('active');
					current.toggleClass('left');
					next.toggleClass('active');
					next.toggleClass('next');
					next.toggleClass('left');
					moving = false;
				}, 800);
			}
		}
		
		right.click(slide);
		
		left.click(function(e) {
			slide(e, 'left');
		});
		
		setInterval(function() {
			slide();
		}, config.interval);
	};
})($);

// SVG Framework Plugin
(function($) {
	var namespaceURI = 'http://www.w3.org/2000/svg';
	
	var make = function(type, config) {
		var obj = document.createElementNS(namespaceURI, type);
		
		if (config.width) obj.setAttributeNS(null, 'width', config.width);
		if (config.height) obj.setAttributeNS(null, 'height', config.height);
		
		return obj;
	};
	
	var svg = $.svg = function(width, height) {
		return svg.workspace(width, height)
  };
	
	svg.query = function(config) {
		if (!config) console.log(1);
		if (!config.context) console.log(2);
		if (!config.context instanceof SVGElement) console.log(3);
		
		if (!config || !config.context || !config.context instanceof SVGElement) return;
		
		this[0] = config.context;
		this.length = 1;
		this.context = config.context;
		
		//if (config.type) this.type = config.type;
		
		return this;
	};
	
	svg.query.append = function(object) {
		if (object instanceof HTMLElement) this.context.appendChild(context);
		
		if (typeof object === 'object' && object.length) {
			var _this = this;
			
			$.eachElement(object, function(e) {
				_this.context.appendChild(e);
			});
		}
		
		return this;
	};
	
	// only works for svg elements
	svg.query.viewBox = function(minX, minY, width, height) {
		if (!this.type === 'svg') return;
		
		var viewBox = [minX || 0, minY || 0, width || 100, height || 100].join(' ');
		
		this.context.setAttributeNS(null, 'viewBox', viewBox);
	};
	
	var colorNames = [
		'bisque', 'indigo',
		'maroon', 'orange',
		'orchid', 'purple',
		'salmon', 'sienna',
		'silver', 'tomato',
		'violet', 'yellow'
	];
	
	svg.query.fill = function(color) {
		if (color.length === 3 || color.length === 6) color = '#' + color;
		
		this.context.setAttributeNS(null, 'fill', color);
	};
	
	svg.query.stroke = function(color) {
		if (color.length === 3 || color.length === 6) color = '#' + color;
		
		this.context.setAttributeNS(null, 'stroke', color);
	};
	
	svg.query.strokeOpacity = function(value) {
		this.context.setAttributeNS(null, 'stroke-opacity', value);
	};
	
	svg.query.strokeWidth = function(value) {
		this.context.setAttributeNS(null, 'stroke-width', value);
	};
	
	svg.query.dashArray = function(value) {
		this.context.setAttributeNS(null, 'stroke-dasharray', value);
	};
	
	svg.query.dashOffset = function(value) {
		this.context.setAttributeNS(null, 'stroke-dashoffset', value);
	};
	
	svg.query.pos = function(x, y) {
		var typeX = 'x';
		var typeY = 'y';
		
		if (this.context instanceof SVGCircleElement || this.context instanceof SVGEllipseElement) {
			typeX = 'cx'
			typeY = 'cy'
		}
		
		if (x) this.context.setAttributeNS(null, typeX, x);
		if (y) this.context.setAttributeNS(null, typeY, y);
	};
	
	svg.query.rad = function(radius) {
		if (!radius) radius = 0;
		
		this.context.setAttributeNS(null, 'r', radius);
	};
	
	svg.workspace = function(width, height) {
		var obj = document.createElementNS(namespaceURI, 'svg');
		var width = width || '100%';
		var height = height || '100%';
		
		obj.setAttribute('width', width);
		obj.setAttribute('height', height);
		
		return new svg.query({
			context: obj,
			type: 'svg'
		});
	};
	
  svg.box = function(width, height) {
		var obj = make('rect', {
			width: width || '100%',
			height: height || '100%'
		});
		
		return new svg.query({
			context: obj,
			type: 'rect'
		});
  };
	
	svg.circle = function(width, height) {
		var obj = make('circle', {
			width: width || '100%',
			height: height || '100%'
		});
		
		return new svg.query({
			context: obj,
			type: 'circle'
		});
	};
	
	svg.line = function() {
		var obj = make('line');
		
		return new svg.query({
			context: obj,
			type: 'line'
		});
	};
	
	svg.path = function() {
		var obj = make('path');
		
		return new svg.query({
			context: obj,
			type: 'path'
		});
	};
	
	$.each(svg.query, function(v, k) {
		svg.query.prototype[k] = v;
	});
	
	$.each(svg, function(v, k) {
		svg.prototype[k] = v;
	});
	
	$.extend(svg, {
		chart: {
			bar: function(config) {
				return new svg.query({
					context: obj,
					type: 'bar-graph'
				});
			},
			
			line: function(config) {
				var workspace = svg.workspace(config.width, config.height);
				
				return workspace;
			},
			
			pie: function(config) {
				return new svg.query({
					context: workspace,
					type: 'line-chart'
				});
			},
			
			progress: function(config) {
				return new svg.query({
					context: workspace,
					type: 'progress-bar'
				});
			}
		}
	});
})($);

// Modal Plugin
(function($) {
  $.modal = function() {
    
  };
  
  var modal = {
    align: $('<div class="align">'),
    closeButton: $('<button class="fa fa-close close">'),
    closeToggle: $('<div class="background-close">'),
    container: $('<div class="container">'),
    content: $('<div class="content">'),
    header: $('<div class="header">').hide(),
    main: $('<div class="main">'),
    modal: $('<div class="modal">')
  };
  
  modal.modal.append(modal.align);
  modal.align.append(modal.closeToggle);
  modal.align.append(modal.container);
  modal.container.append(modal.closeButton);
  modal.container.append(modal.main);
  modal.main.append(modal.header);
  modal.main.append(modal.content);
  $('body').append(modal.modal);
  
  $.modal.reset = function() {
    modal.content.attr('class', 'content'); // remove modal classes
    modal.content.clearChildren();
    modal.header.hide();
    modal.header.text('');
  };
  
  $.modal.close = function(e) {
    if (e && e.preventDefault) e.preventDefault();
    modal.modal.removeClass('open');
    $.modal.reset();
  };
  
  modal.closeButton.click($.modal.close);
  modal.closeToggle.click($.modal.close);
  
  $.modal.keypress = function(e) {
    e = e || window.event;
    if (e.keyCode === 27) $.modal.close();
  };
  
  $.modal.add = function(name, className, f) {
    $.modal[name] = function() {
      f.apply($.modal, arguments);
      modal.modal.addClass('open');
      modal.content.addClass(className);
      document.addEventListener('keydown', this.keypress);
    };
  };
  
  $.modal.add('alert', 'alert', function(text, callback) {
    var alertText = $('<div class="alert">'),
      ok = $('<button class="submit">').text('OK');
    
    modal.header.text('Alert');
    modal.header.show();
    alertText.text(text);
    modal.content.append(alertText);
    modal.content.append(ok);
    
    ok.click(function(e) {
      e.preventDefault();
      $.modal.close();
      if (callback) callback(e);
    });
  });
  
  $.modal.add('image', 'image', function(source, header) {
    if (header) {
      header.show();
      modal.header.text(header);
    }
    
    var image = $('<img class="image">').attr('src', source);
    
    modal.content.append(image);
  });
  
  $.each($.modal, function(v, k) {
    $.modal[k] = v;
  });
})($);

// Speech Recognition
define('speech-recognition', ['util'], function($) {
	var speech = function() {
		
	};
	
	var optionalParam = /\s*\((.*?)\)\s*/g;
	var optionalRegex = /(\(\?:[^)]+\))\?/g;
	var namedParam = /(\(\?)?:\w+/g;
	var splatParam = /\*\w+/g;
	var escapeRegExp = /[\-{}\[\]+?.,\\\^$|#]/g;
	var commandToRegExp = function(command) {
		command = command.replace(escapeRegExp, '\\$&')
  	.replace(optionalParam, '(?:$1)?')
  	.replace(namedParam, function(match, optional) {
    	return optional ? match : '([^\\s]+)';
  	})
  	.replace(splatParam, '(.*?)')
  	.replace(optionalRegex, '\\s*$1?\\s*');
		return new RegExp('^' + command + '$', 'i');
	};
	
	speech.config = {
		lang: 'en'
	};
	
	speech.commands = [];
	
	speech.init = function() {
		if (SpeechRecognition) {
			this.engine = new SpeechRecognition();
			this.engine.continuous = true;
			this.engine.interimResults = false;
			this.engine.lang = this.config.lang;
			this.engine.onend = function(e) {
				return;
			}
			this.engine.onresult = this.onResult;
			this.engine.start();
		} else {
			$.modal.alert('Your browser doesn\'t support speech recognition. Update your browser to be able to use this feature.');
		}
	};
	
	speech.onResult = function(e) {
		if (e.results.length > 0) {
			for (var i = 0, result; result = e.results[i]; i++) {
	    	if (result.isFinal) {
					speech.result({
						text: result[0].transcript,
						confidence: result[0].confidence
					});
	      }
	    }
		}
	};
	
	speech.result = function(e) {
		for (var i = 0, command; command = speech.commands[i]; i++) {
			var parsedCommand = command.regex.exec(e.text);
			
			if (parsedCommand) {
				var parameters = parsedCommand.slice(1);
				command.callback.apply(this, parameters);
			}
		}
	};
	
	speech.listen = function(command, callback) {
		this.commands.push({
			regex: commandToRegExp(command),
			original: command,
			callback: callback
		});
	};
	
	speech.setConfig = function(k, v) {
		this.config[k] = v;
	};
	
	$.each(speech, function(v, k) {
		speech.prototype[k] = v;
	});
	
	return speech;
});

require(['util'], function($) {
	if (templatePath) require.config({baseUrl: templatePath});
	
	window.tooltip = $('<div class="tooltip">');
	window.tooltipText = $('<span>');
	window.tooltipNub = $('<div class="nub">');
	
	tooltip.append(tooltipNub);
	tooltip.append(tooltipText);
	$('body').append(tooltip);
	
	function app() {
		var elements = $('[data-tooltip]');
		var tooltipListeningTo = [];
		
		function listeningTo(e) {
			for (var i = 0, element; element = tooltipListeningTo[i]; i++) {
				if (element === e) return true;
			}
		}
		
		for (var i = 0, element; element = elements[i]; i++) {
			if (!listeningTo(element)) $.tooltip(element);
		}
		
		var parallaxObjects = $('.parallax');
		
		for (var i = 0, obj; obj = parallaxObjects[i]; i++) {
			$.parallax(obj);
		}
		
		var module = $('#app');
		if (module) module = module.data('module');
		if (module) this.loadModule(module);
	}
	
	app.baseUrl = '/api';
	
	app.pages = {};
	app.user = {};
	app.cookies = {};
	
	app.loadModule = function(moduleToLoad) {
		if (!moduleToLoad) return;
		if (!app.pages[moduleToLoad]) {
			app.pages[moduleToLoad] = function() {};
			
			require(['pages/' + moduleToLoad], function(moduleInit) {
				if (moduleInit) {
					app.pages[moduleToLoad] = moduleInit;
					moduleInit();
				}
			});
		} else {
			app.pages[moduleToLoad]();
		}
	};
	
	app.load = function(page) {
		var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4 && xhr.status === 200) {
				var htmlContainer = document.createElement('div');
				htmlContainer.innerHTML = xhr.responseText;
				
				var page = htmlContainer.children[0],
					appElement = document.getElementById('app');
				
				appElement.parentNode.replaceChild(page, appElement);
				init.loadModule(page.getAttribute('data-module'));
			}
		}
		
		xhr.open('GET', page + '?html=true');
		xhr.send();
	}
	
	app.cookies = function() {
		var cookies = document.cookie.split(';');
		
		for (var i = 0, cookie; cookie = cookies[i]; i++) {
			var _cookie = cookie.split('=');
			
			if (_cookie.length === 2) {
				var key = decodeURIComponent(_cookie[0].replace(/^ /, ''));
				var val = decodeURIComponent(_cookie[1]);
				
				if (key === 'user_id' || key === 'user_auth') this.user[key] = val;
				this.cookies[key] = val;
			}
		}
	}
	
	$.each(app, function(v, k) {
		app.prototype[k] = v;
	});
	
	$(document).ready(function() {
		window.app = new app();
	});
});

if (window.requireGA) {
	define('ga', function(require) {
		var module;
		window.GoogleAnalyticsObject = 'ga';
		window.ga = function() { (window.ga.q = window.ga.q || []).push(arguments); };
		window.ga.l = 1 * new Date();
		window.ga('create', requireGA._ga, requireGA.domain);
		window.ga('send', 'pageview');
		module = function() { window.ga.apply(this, arguments); };
		require(['//www.google-analytics.com/analytics.js']);
		return module;
	});
} else {
	define('ga', function() {
		return function() {};
	});
}

window.requestAnimFrame = window.requestAnimationFrame
	|| window.webkitRequestAnimationFrame
	|| window.mozRequestAnimationFrame
	|| window.msRequestAnimationFrame
	|| window.oRequestAnimationFrame
	|| function(e) {
		window.setTimeout(e, 1e3 / 60)
	};

window.SpeechRecognition = window.SpeechRecognition
	|| window.webkitSpeechRecognition
	|| window.mozSpeechRecognition
	|| window.msSpeechRecognition
	|| window.oSpeechRecognition;