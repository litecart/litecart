// Number Formatting
Number.prototype.toText = function(decimals = 0) {
	var n = this,
		c = decimals,
		d = '.',
		t = ',',
		s = n < 0 ? '-' : '',
		i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '',
		f = n - i,
		j = (j = i.length) > 3 ? j % 3 : 0

	return s + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + ((c && f) ? d + Math.abs(f).toFixed(c).slice(2) : '')
}

// Money Formatting
Number.prototype.toMoney = function() {
	var n = this,
		c = _env.currency.decimals || 2,
		d = _env.language.decimal_point || '.',
		t = _env.language.thousands_separator || ',',
		p = _env.currency.prefix || '',
		x = _env.currency.suffix || '',
		s = n < 0 ? '-' : '',
		i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '',
		f = n - i,
		j = (j = i.length) > 3 ? j % 3 : 0

	return s + p + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (c ? d + Math.abs(f).toFixed(c).slice(2) : '') + x
}

// Escape HTML
String.prototype.escapeHTML = function() {

	let entityMap = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#39;',
		'/': '&#x2F;',
		'`': '&#x60;',
	}

	return this.replace(/[&<>"'\/]/g, function (s) {
		return entityMap[s]
	})
}

// Escape Attribute
String.prototype.escapeAttr = function() {
	return this.escapeHTML().replace(/\r\n?|\n/g, '\\n')
}