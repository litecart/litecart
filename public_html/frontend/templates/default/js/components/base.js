// Stylesheet Loader
$.loadStylesheet = function(url, options) {

	options = $.extend(options || {}, {
		rel: 'stylesheet',
		href: url,
		//onload: callback,
		//onerror: fallback
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

// Keep-alive
let keepAlive = setInterval(function() {
	$.get({
		url: window._env.platform.path + 'ajax/cart.json',
		cache: false
	})
}, 60e3)

// Form required asterix
$(':input[required]').closest('.form-group').addClass('required')

// Sidebar parallax effect
if (typeof(window._env) !== 'undefined' && window._env.template.settings.sidebar_parallax_effect == true) {

	let $sidebar = $('#sidebar')
		//let sidebar_max_offset = $sidebar.parent().height() - $sidebar.height() - 200; // Failsafe 30

	$(window).on('resize scroll', function(e) {
		if ($(window).width() >= 768 && ($sidebar.parent().height() - $sidebar.height()) > 500) {
			let offset = $(this).scrollTop() * .6
			if (offset > 0) $sidebar.css('margin-top', offset + 'px')
		} else {
			$sidebar.css('margin-top', 0)
		}
	}).trigger('resize')
}


// Off-Canvas Sidebar (data-toggle="offcanvas-collapse")
$('[data-toggle="offcanvas"]').on('click', function() {
	$(this).closest('.navbar').toggleClass('expanded')
	$('body').toggleClass('offcanvas-open', $(this).closest('.navbar').hasClass('expanded'))
	$('body').css('overflow', $(this).closest('.navbar').hasClass('expanded') ? 'hidden' : '')
})

// Scroll Up
$(window).scroll(function() {
	if ($(this).scrollTop() > 300) {
		$('#scroll-up').fadeIn()
	} else {
		$('#scroll-up').fadeOut()
	}
})

$('#scroll-up').on('click', function() {
	$('html, body').animate({scrollTop: 0}, 1000, 'easeOutBounce')
	return false
})
