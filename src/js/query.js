var $ = function(query, tag) {
	return new $.query(query, tag);
};

$.each = function(obj, v) {
	if (!obj || !v) return;
  
	if (typeof obj === 'object' && obj.length) {
		for (var i = 0, child; child = obj[i]; i++) {
    	v(child, i);
  	}
  } else {
    for (var i in obj) {
		  v(obj[i], i);
	  }
  }
};

$.eachElement = function(obj, v) {
	if (!obj || !v) return;
	
	for (var i in obj) {
		if (obj[i] instanceof HTMLElement
			|| obj[i] instanceof SVGElement
			|| obj[i] === window
			|| obj[i] === document) v(obj[i], i);
	}
};

$.has = function(obj, target) {
	if (!obj || !target) return;
  
	if (typeof obj === 'object' && obj.length) {
		for (var i = 0, child; child = obj[i]; i++) {
    	if (child === target) return true;
  	}
  } else {
    for (var i in obj) {
		  if (obj[i] === target) return true;
	  }
  }
	
	return false;
};

$.extend = function(a, b) {
	if (typeof a === 'object' && a.length) {
		for (var i = 0, c; c = b[i]; i++) {
    	a.push(c);
  	}
	} else {
		for (var i in b) {
			a[i] = b[i];
		}
	}
};

$.query = function(selector, tag) {
	var elements = [];
	
	if (!selector) {
		elements.push(document.createElement(tag || 'div'));
	} else if (typeof selector === 'string') {
		if (selector.charAt(0) === '<' && selector.charAt(selector.length - 1) === '>' && selector.length >= 3) {
			var element = document.createElement('div');
			element.innerHTML = selector;
			
			for (var i = 0, e; e = element.children[i]; i++) {
				elements.push(e);
			}
		} else {
			var q = document.querySelectorAll(selector);
			
			for (var i = 0, element; element = q[i]; i++) {
				elements.push(element);
			}
		}
	} else if (selector instanceof HTMLElement) {
		elements.push(selector);
	} else if (typeof selector === 'object' && selector.length) {
		for (var i = 0, element; element = selector[i]; i++) {
			if (element instanceof HTMLElement) elements.push(element);
		}
	} else if (typeof selector === 'object' && !selector.length) {
		if (selector === window || selector === document) elements.push(selector);
	}
	
	for (var i = 0, element; element = elements[i]; i++) {
		this[i] = element;
	}
	
	this.selector = selector;
	this.length = elements.length;
	this.context = elements;
	return this.make(this.context, this);
};

$.query.make = function(a, b) {
	if (!arguments.length) return [];
	var result = [];
	
	for (var i = 0, e; e = a[i]; i++) {
		b[i] = e;
	}
	
	return b;
};

$.query.find = function(selector) {
	var context = [];
	
	$.eachElement(this, function(element) {
		var list = element.querySelectorAll(selector);
		
		$.each(list, function(e) {
			context.push(e);
		});
	});
	
	return $.query(context);
};

$.query.clone = function() {
	var context = [];
	
	$.eachElement(this, function(element) {
		var clone = element.cloneNode(true);
		context.push(clone);
	});
	
	return $.query(context);
};

$.query.attr = function(attribute, value) {
	if (value !== null && value !== undefined) {
		attribute = $.escape(attribute);
		value = $.escape(value);
		
		$.eachElement(this.context, function(e) {
			value === '' ? e.removeAttribute(attribute) : e.setAttribute(attribute, value);
		});
	} else {
		if (attribute && this.context[0]) return this.context[0].getAttribute(attribute);
	}
	
	return this;
};

$.query.data = function(dataName, value) {
	return this.attr('data-' + dataName, value);
};

$.query.each = function(callback) {
	$.eachElement(this.context, function(e) {
		callback($(e));
	});
}

$.query.addClass = function(c) {
	var self = this;
	c = $.escape(c);
	
	c.indexOf(' ') >= 0 ? c = c.split(' ') : c = [c];
	
	$.each(c, function(className) {
		$.eachElement(self.context, function(e) {
			var classNames = e.className.split(' ');
			var index = classNames.indexOf(className);
			
			if (index <= 0) {
				e.className ? e.className += ' ' + className : e.className += className;
			}
		});
	});
	
	return this;
};

$.query.removeClass = function(c) {
	var self = this;
	c = $.escape(c);
	
	c.indexOf(' ') >= 0 ? c = c.split(' ') : c = [c];
	
	$.each(c, function(className) {
		$.eachElement(self.context, function(e) {
			var classNames = e.className.split(' ');
			var index = classNames.indexOf(className);
			
			if (index >= 0) {
				classNames.splice(index, 1);
				e.className = classNames.join(' ');
			}
		});
	});
	
	return this;
};

$.query.hasClass = function(className) {
	var isActive = false;
	
	$.eachElement(this.context, function(e) {
		var classNames = e.className.split(' ');
		var index = classNames.indexOf(className);
		
		if (index >= 0) isActive = true;
	});
	
	return isActive;
};

$.query.toggleClass = function(className) {
	className = $.escape(className);
	
	$.eachElement(this.context, function(e) {
		var classNames = e.className.split(' ');
		var index = classNames.indexOf(className);
		
		if (index < 0) {
			e.className += e.className ? ' ' + className : className;
		} else {
			classNames.splice(index, 1);
			e.className = classNames.join(' ');
		}
	});
	return this;
};

$.query.css = function(a, b) {
	var styles = {};
	var _this = this;
	
	if (typeof(a) === 'string') {
		styles[a] = b;
	} else if (typeof(a) === 'object' && a.length) {
		styles = a;
	}
	
	$.each(styles, function(value, style) {
		style = $.escape(style);
		value = $.escape(value);
		
		$.eachElement(_this.context, function(e) {
			e.style[style] += value;
		});
	});
	return this;
};

$.query.html = function(html) {
	var callback = this;
	
	if (html) this.clearChildren();
	
	$.eachElement(this.context, function(e) {
		(html) ? e.innerHTML = html : callback = e.innerHTML;
	});
	
	return callback;
};

$.query.text = function(text) {
	this.clearChildren();
	var callback = this;
	
	$.eachElement(this.context, function(e) {
		if (text !== null) {
			if (e.className === 'textContent') {
				e.textContent = text;
			} else {
				var textNode = document.createTextNode(text);
				e.appendChild(textNode);
			}
		} else {
			callback = e.innerText;
		}
	});
	
	return callback;
};

$.query.value = function(text, escape) {
	var callback = this;
	
	if (text && escape) text = $.escape(text);
	
	$.eachElement(this.context, function(e) {
		text === null || text === undefined ? callback = e.value : e.value = text;
	});
	
	return callback;
};

$.query.selectedText = function(getPosition) {
	var element = this.context[0] ? this.context[0] : null;
	var selection = this;
	
	if (element === null) return null;
	
	if (element.getSelection) {
		selection = element.getSelection();
	} else if (element.type === 'textarea') {
		if (!element.selectionStart && !element.selectionEnd) {
			selection = [
				element.selectionStart,
				element.selectionEnd,
				$.escape(element.value.substring(element.selectionStart, element.selectionEnd))
			];
			if (getPosition !== true) selection = element.value.substring(element.selectionStart, element.selectionEnd);
		}
	} else if (element.type === 'text') {
		if (document.selection) {
			selection = document.selection.createRange();
			if (!getPosition) selection = selection.text;
		} else if (!element.selectionStart && !element.selectionEnd) {
			selection = [
				element.selectionStart,
				element.selectionEnd,
				$.escape(element.value.substring(element.selectionStart, element.selectionEnd))
			];
			if (getPosition !== true) selection = element.value.substring(element.selectionStart, element.selectionEnd);
		}
	}
	
	return selection;
};

$.query.parent = function() {
	if (this.context[0] && this.context[0].parentNode) return $(this[0].parentNode);
	return null;
};

$.query.find = function(selector) {
	if (selector) {
		var element = this.context[0] ? this.context[0] : null;
		selector = element.querySelectorAll(selector);
		
		if (selector && selector[0]) {
			return $(selector);
		}
	}
};

$.query.remove = function() {
	$.eachElement(this.context, function(e) {
		if (e.parentNode) e.parentNode.removeChild(e);
	});
	return;
};

$.query.clearChildren = function() {
	$.eachElement(this.context, function(e) {
		while (e.firstChild) {
			e.removeChild(e.firstChild);
		}
	});
	return this;
}

$.query.append = function(context) {
	$.eachElement(this.context, function(e) {
		if (context instanceof HTMLElement) e.appendChild(context);
		
		if (typeof context === 'object' && context.length) {
			$.eachElement(context, function(element) {
				e.appendChild(element);
			});
		}
	});
	return this;
};

$.query.prepend = function(context) {
	$.eachElement(this.context, function(e) {
		if (context instanceof HTMLElement) e.insertBefore(context, e.firstChild);
		
		if (typeof context === 'object' && context.length) {
			$.eachElement(context, function(element) {
				e.insertBefore(element, e.firstChild);
			});
		}
	});
	return this;
};

// events

$.each('click mousedown mouseup mouseover mousemove mouseleave keydown keyup keypress blur focus resize'.split(' '), function(e) {
	$.query[e] = function(result) {
		return this.eventListener(e, result);
	}
});

$.query.ready = function(result) {
	if (document.readyState === 'complete' || document.readyState === 'interactive') {
		result();
		return this;
	}
	
	return this.eventListener('DOMContentLoaded', result);
}

$.query.bind = function(event, result) {
	var events = [];
	
	if (typeof event === 'object' && context.length) events = event;
	if (typeof event === 'string') events.push(event);
	
	$.eachElement(this.context, function(element) {
		$.each(events, function(e) {
			element.addEventListener(e, result);
		});
	});
	return this;
};

$.query.eventListener = function(event, result) {
	$.eachElement(this.context, function(e) {
		e.addEventListener(event, result);
	});
	
	return this;
};

$.query.scrollTop = function(px) {
	if (px) {
		$.eachElement(this.context, function(e) {
			e.scrollTop = px + 'px';
		});
	} else {
		if (!this[0] === document.body) {
			return (this[0]) ? this[0].scrollTop : 0;
		} else {
			return window.scrollY || window.pageYOffset;
		}
	}
	return this;
};

$.query.scrollBottom = function() {
	$.eachElement(this.context, function(e) {
		e !== document.body ? e.scrollTop = e.scrollHeight : window.scrollTo(0, document.body.scrollHeight);
	});
	return this;
};

$.query.offset = function() {
	var e = this.context[0] || window;
	var top = 0, left = 0;
	
  do {
      top += e.offsetTop  || 0;
      left += e.offsetLeft || 0;
      e = e.offsetParent;
  } while(e);
	
  return {
      top: top,
      left: left
  };
};

$.query.inViewport = function(partlyInViewport) {
	if (this.context[0]) {
		var rect = this.context[0].getBoundingClientRect();
		
		if (!partlyInViewport) {
			return (
				rect.top >= 0 &&
				rect.left >= 0 &&
				rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
				rect.right <= (window.innerWidth || document.documentElement.clientWidth)
			);
		} else {
			var inView = (
				rect.top < window.innerHeight &&
				rect.left < window.innerWidth &&
		    rect.bottom > 0 &&
				rect.right > 0
		  );
			
			return inView;
		}
	}
}

$.query.draggable = function() { // todo
	$.eachElement(this.context, function(e) {
		e.addEventListener('mousedown', mouseDown, false);
		window.addEventListener('mouseup', mouseUp, false);
		
		this.mousemove(function(event) {
			
		});
	});
	
	return this;
};

$.query.show = function(display) {
  $.eachElement(this.context, function(e) {
    $(e).css('display', display || 'block');
  });
  
  return this;
};

$.query.hide = function() {
  $.eachElement(this.context, function(e) {
    $(e).css('display', 'none');
  });
  
  return this;
};

$.each($.query, function(v, i) {
	$.query.prototype[i] = v;
});

// ajax

var buildParams = function(prefix, object, add) {
	if (object instanceof Array) {
		for (var i = 0, l = object.length; i < l; i++) {
			if (/\[\]$/.test(prefix)) {
				add(prefix, object[i]);
			} else {
				buildParams(prefix + '[' + (typeof object[i] === 'object' ? i : '') + ']', object[i], add);
			}
		}
	} else if (typeof object === 'object') {
		// serialize object item.
		for (var key in object) {
			buildParams(prefix + '[' + key + ']', object[key], add);
		}
	} else {
		// serialize scalar item.
		add(prefix, object);
	}
};

$.params = function(object) {
	var req = [],
		r20 = /%20/g,
		add = function(key, value) {
			// if function, invoke it and return its value
			value = (typeof value === 'function') ? value() : (value === null ? '' : value);
			req[req.length] = encodeURIComponent(key) + '=' + encodeURIComponent(value);
		};
		
	if (object instanceof Array) {
		for (var key in object) {
			add(key, object[key]);
		}
	} else {
		for (var prefix in object) {
			buildParams(prefix, object[prefix], add);
		}
	}
	
	return req.join('&').replace(r20, '+');
};

$.ajax = function(config) {
	var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
	var req,
		get = '',
		post = '';
	
	xhr.onreadystatechange = function() {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				if (config.success) {
					try {
						config.success(JSON.parse(xhr.responseText));
					} catch(e) {
						console.error(e);
						config.fail ? config.fail(xhr.responseText) : console.error(xhr.responseText);
					}
				}
			} else {
				config.fail ? config.fail(xhr.responseText) : console.error(xhr.responseText);
			}
		}
	}
	
	if (config.data && typeof config.method === 'string') {
		var req = $.params(config.data);
		
		if (config.method === 'GET') {
			get = req;
		} else if (config.method === 'POST') {
			config.data = req;
		}
	}
	
	try {
		xhr.open(config.method || 'GET', config.url + (get ? '?' + get : ''), config.async, config.username, config.password);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		xhr.send(config.data || null);
	} catch(e) {
		console.error(e);
	}
};

$.get = function(url, data, callback) {
	if (typeof data === 'function') {
		callback = data;
		data = {}
	}
	
	$.ajax({
		data: data,
		success: callback,
		url: url
	});
};

// storage

if (window.localStorage) {
	$.localStorage = {};
	
	$.localStorage.set = function(name, value, object) {
		if (object) value = JSON.stringify(value);
		
		localStorage.setItem(name, value);
		
		var object = localStorage.getItem(name);
	};
	
	$.localStorage.get = function(name, object) {
		var item = localStorage.getItem(name);
		
		if (item) {
			return object ? JSON.parse(item) : item;
		}
	};
	
	$.localStorage.clear = localStorage.clear;
}

// date

$.date = {};

$.date.leapYear = function(d) {
	var year = d.getUTCFullYear();
	return !((year % 4) || (!(year % 100) && (year % 400)));
};

$.date.getMonthName = function(d) {
	return['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'][d.getUTCMonth()];
};

$.date.getDaysInMonth = function(d) {
	if (!d) d = new Date();
	var month = d.getUTCMonth();
	return [31, $.date.leapYear(d) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month];
};

// tools

$.copyToClipboard = function(text) {
	if (window.clipboardData && window.clipboardData.setData) {
		return clipboardData.setData('Text', text);
	} else if (document.queryCommandSupported && document.queryCommandSupported('copy')) {
		var textarea = $('<textarea>');
		textarea.text(text);
		textarea.css('position', 'fixed');
		$('body').append(textarea);
		textarea[0].select();
		
		try {
			return document.execCommand('copy');
		} catch(e) {
			return false;
		} finally {
			textarea.remove();
		}
	}
};

$.now = function() {
	return Date.now() || new Date().getTime();
};

var urlRegex = /(https?|ftp|bitcoin|dogecoin|mailto|skype|steam):\/\/(?!\.)[-a-z0-9@:%._\+~#=]{2,256}(\.[a-z]{2,6})?\/?(?:\b([-a-z0-9@:%_\+.~#?&/=]*))?/igm;
var emailRegex = /(\w+@[a-z_]+?\.[a-z]{2,6})/igm;
var imageRegex = /.(gif|je?pg|png|webp)$/i;

$.linkify = function(text) {
	text = text.replace(urlRegex, function(url) {
		var innerUrl;
		url = $.escape(url);
		if (imageRegex.test(url)) innerUrl = '<img src="' + url + '"></img>';
		return '<a href="' + url + '" target="_blank" rel="nofollow">' + (innerUrl || url) + '</a>';
	});
	
	return text.replace(emailRegex, function(email) {
		email = $.escape(email);
		return '<a href="mailto:' + email + '" target="_blank">' + email + '</a>';
	});
};

$.getTime = function(timestamp) {
	var date = new Date(timestamp);
	var h = date.getHours(),
		m = date.getMinutes();
	
	m = (m === 0) ? '00' : m;
	if (h === 0) h = 12;
	
	return h > 12 ? (h - 12 + ':' + m +' PM') : (h + ':' + m +' AM');
};

$.invert = function(object) {
	var inverted = {};
	
	for (var i in object) {
		inverted[object[i]] = i;
	}
	
	return inverted;
};

$.urlParse = function(url) {
	var req = {};
	url = url.substring(url.indexOf('?') + 1).split('&');
	
	for (var i = 0, parameter; parameter = url[i]; i++) {
		var child = parameter.split('=');
		
		if (child[1]) {
			child[0] = decodeURIComponent(child[0]);
			child[1] = decodeURIComponent(child[1]);
			req[child[0]] = child[1];
		}
	}
	
	return req;
};

// escaper
var escapeMap = {
	'&': '&amp;',
	'<': '&lt;',
	'>': '&gt;',
	'"': '&quot;',
	"'": '&#x27;',
	'`': '&#x60;'
};
var unescapeMap = $.invert(escapeMap);

var escape = function(map) {
	var escaper = function(match) {
		return map[match];
	};
	
	var triggers = [];
	
	for (var key in map) {
		triggers.push(key);
	}
	
	var source = '(?:' + triggers.join('|') + ')';
	var testRegexp = RegExp(source);
	var replaceRegexp = RegExp(source, 'g');
	
	return function(string) {
		if (typeof string === 'number') return string;
		string = string === null ? '' : '' + string;
		return testRegexp.test(string) ? string.replace(replaceRegexp, escaper) : string;
	};
};

$.escape = escape(escapeMap);
$.unescape = escape(unescapeMap);

$.trim = function(string) {
	return string.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
};

$.timeAgo = function(timestamp) {
	switch (typeof timestamp) {
		case 'number': break;
		case 'string': timestamp = new Date(parseInt(timestamp)); break;
		case 'object': if (timestamp.constructor === Date) timestamp = timestamp.getTime(); break;
		default: timestamp = new Date();
	}
	
	var timeFormats = [
		[60, 'seconds', 1], // 60
		[120, '1 minute ago', '1 minute from now'], // 60*2
		[3600, 'minutes', 60], // 60*60, 60
		[7200, '1 hour ago', '1 hour from now'], // 60*60*2
		[86400, 'hours', 3600], // 60*60*24, 60*60
		[172800, 'Yesterday', 'Tomorrow'], // 60*60*24*2
		[604800, 'days', 86400], // 60*60*24*7, 60*60*24
		[1209600, 'Last week', 'Next week'], // 60*60*24*7*4*2
		[2419200, 'weeks', 604800], // 60*60*24*7*4, 60*60*24*7
		[4838400, 'Last month', 'Next month'], // 60*60*24*7*4*2
		[29030400, 'months', 2419200], // 60*60*24*7*4*12, 60*60*24*7*4
		[58060800, 'Last year', 'Next year'], // 60*60*24*7*4*12*2
		[2903040000, 'years', 29030400], // 60*60*24*7*4*12*100, 60*60*24*7*4*12
		[5806080000, 'Last century', 'Next century'], // 60*60*24*7*4*12*100*2
		[58060800000, 'centuries', 2903040000] // 60*60*24*7*4*12*100*20, 60*60*24*7*4*12*100
	];
	
	var seconds = (+new Date() - timestamp) / 1000,
		token = 'ago',
		listChoice = 1;
	
	if (seconds === 0) return 'Just now';
	
	if (seconds < 0) {
		seconds = Math.abs(seconds);
		token = 'from now';
		listChoice = 2;
	}
	
	var i = 0, format;
	
	while (format = timeFormats[i++]) {
		if (seconds < format[0]) {
			if (typeof format[2] === 'string') {
				return format[listChoice];
			} else {
				return Math.floor(seconds / format[2]) + ' ' + format[1] + ' ' + token;
			}
		}
	}
	
	return timestamp;
};

$.plugin = $.query.prototype;

$.each($, function(v, k) {
	$.prototype[k] = v;
});

$.AMDify = function() {
	if (window.define && typeof window.define === 'function') {
  	define('util', function() {
    	return $;
  	});
	}
};

window.$ = $;