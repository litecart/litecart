waitFor('jQuery', ($) => {

	// Sidebar Filter
	$('#sidebar input[name="filter"]').on({

		'input': function(){

			let query = $(this).val();

			if ($(this).val() == '') {
				$('#box-apps-menu .app').css('display', 'block');
				return;
			}

			$('#box-apps-menu .app').each(function(){
				var regex = new RegExp(''+ query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')  +'', 'ig');
				console.log()
				if (regex.test($(this).text())) {
					$(this).show();
				} else {
					$(this).hide();
				}
			});
		}
	});

	// AJAX Search
	let timer_ajax_search = null;
	let xhr_search = null;

	$('#search input[name="query"]').on({

		'focus': function(){
			if ($(this).val()) {
				$('#search.dropdown').addClass('open');
			}
		},

		'blur': function(){
			if (!$('#search').filter(':hover').length) {
				$('#search.dropdown').removeClass('open');
			} else {
				$('#search.dropdown').on('blur', function() {
					$('#search.dropdown').removeClass('open');
				});
			}
		},

		'input': function(){

			if (xhr_search) {
				xhr_search.abort();
			}

			let $searchField = $(this);

			if ($searchField.val()) {

				$('#search .results').html([
					'<div class="loader-wrapper text-center">',
					'  <div class="loader" style="width: 48px; height: 48px;"></div>',
					'</div>'
				].join('\n'));

				$('#search.dropdown').addClass('open');

			} else {
				$('#search .results').html('');
				$('#search.dropdown').removeClass('open');
				return;
			}

			clearTimeout(timer_ajax_search);

			timer_ajax_search = setTimeout(function() {
				xhr_search = $.ajax({
					type: 'get',
					async: true,
					cache: false,
					url: _env.backend.url + 'search_results.json?query=' + $searchField.val(),
					dataType: 'json',

					beforeSend: function(jqXHR) {
						jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
					},

					error: function(jqXHR, textStatus, errorThrown) {
						$('#search .results').text(textStatus + ': ' + errorThrown);
					},

					success: function(json) {

						$('#search .results').html('');

						if (!$('#search input[name="query"]').val()) {
							$('#search .results').html('Search');
							return;
						}

						// Defense-in-depth: only allow http(s), mailto, root-relative or scheme-less
						// URLs as result links. Today all providers build links via document::ilink()
						// (relative), so this guards against future providers leaking javascript:,
						// data: or protocol-relative (//) URLs.
						function safe_link(url) {
							if (typeof url !== 'string') return '#';
							if (url.startsWith('//')) return '#';                          // Protocol-relative — inherits page scheme
							if (/^(https?:\/\/|mailto:)/i.test(url)) return url;           // Allowed absolute schemes
							if (url.startsWith('/')) return url;                           // Root-relative
							if (!/^[a-z][a-z0-9+.\-]*:/i.test(url)) return url;            // Scheme-less = relative
							return '#';                                                    // Everything else (javascript:, data:, vbscript:, ...)
						}

						$.each(json, function(i, group) {

							if (!group.results.length) return;

							// Build group header and list via DOM APIs so untrusted fields
							// (group.name, result.title, result.description, result.link)
							// can't break out of text/attribute context.
							var $heading = $('<h3>').text(group.name);
							var $ul = $('<ul>').addClass('flex flex-rows flex-nogap').attr('data-group', group.name);

							$('#search .results').append($heading).append($ul);

							$.each(group.results, function(i, result) {

								var $a = $('<a>')
									.addClass('list-group-item')
									.attr('href', safe_link(result.link))
									.css({
										'border-inline-start': '3px solid ' + group.theme.color,
										'background': group.theme.color + '11'
									});

								$('<small>').addClass('id float-end').text('#' + result.id).appendTo($a);
								$('<div>').addClass('title').text(result.title).appendTo($a);
								$('<div>').addClass('description').append($('<small>').text(result.description)).appendTo($a);

								$('<li>').addClass('result').append($a).appendTo($ul);
							});
						});

						if ($('#search .results').html() == '') {
							$('#search .results').html('<p class="text-center no-results"><em>:(</em></p>');
						}
					},
				});
			}, 500);
		}
	});

});
