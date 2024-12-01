	// Stylesheet Loader
	$.loadStylesheet = function(url, options, callback, fallback) {

		options = $.extend(options || {}, {
			rel: 'stylesheet',
			href: url,
			onload: callback,
			onerror: fallback
		})

		$('<link>', options).appendTo('head')
	}

	// JavaScript Loader
	$.loadScript = function(url, options) {

		options = $.extend(options || {}, {
			method: 'GET',
			dataType: 'script',
			cache: true
		})

		return jQuery.ajax(url, options)
	}

	// Escape HTML
	function escapeHTML(string) {

		let entityMap = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#39;',
			'/': '&#x2F;',
			'`': '&#x60;',
		}

		return String(string).replace(/[&<>"'\/]/g, function (s) {
			return entityMap[s]
		})
	}

	// Escape HTML
	function escapeAttr(string) {
		return escapeHTML(string).replace(/\r\n?|\n/g, '\\n')
	}

	// Money Formatting
	Number.prototype.toMoney = function() {
		var n = this,
			c = _env.session.currency.decimals,
			d = _env.session.language.decimal_point,
			t = _env.session.language.thousands_separator,
			p = _env.session.currency.prefix,
			x = _env.session.currency.suffix,
			u = _env.session.currency.code,
			s = n < 0 ? '-' : '',
			i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '',
			f = n - i,
			j = (j = i.length) > 3 ? j % 3 : 0

		return s + p + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (c ? d + Math.abs(f).toFixed(c).slice(2) : '') + x
	}

	// Keep-alive
	let keepAlive = setInterval(function() {
		$.get({
			url: window._env.platform.path + 'ajax/cart.json',
			cache: false
		})
	}, 60e3)
