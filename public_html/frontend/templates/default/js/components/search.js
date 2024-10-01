	// AJAX Search
	$('.navbar-search :input').on('focus', function(){
		$(this).closest('.dropdown').addClass('open');
	});

	$('.navbar-search :input').blur(function(){
		$(this).closest('.dropdown').removeClass('open');
	});

	let xhrAjaxSearch;
	$('.navbar-search :input').on('input', function(){

		let $navbar_search = $(this).closest('.navbar-search');

		if (xhrAjaxSearch) {
			xhrAjaxSearch.abort();
		}

		$navbar_search.find('.dropdown-menu').html('');

		if (!$(this).val()) {
			$navbar_search.find('.dropdown-menu').append(
				$('<li></li>').text($navbar_search.data('hint'))
			);
			return;
		}

		xhrAjaxSearch = $.ajax({
			url: window._env.platform.url + 'ajax/search_results.json',
			type: 'get',
			data: {query: $(this).val()},
			cache: false,
			async: true,
			dataType: 'json',
			beforeSend: function(jqXHR) {
				jqXHR.overrideMimeType('text/html;charset=' + $('meta[charset]').attr('charset'));
			},
			success: function(results) {
				if (results) {
					$.each(results, function(i, result){
						$.each(result, function(i, row){
							$navbar_search.find('.dropdown-menu').append([
								'<li><a href="'+ row.url +'">',
								'  <img src="'+ row.thumbnail +'" style="height: 1em;"> ' + row.name,
								'</a></li>',
							].join('\n'));
						});
					});
				} else {
					$navbar_search.find('.dropdown-menu').html('<li>:(</li>');
				}
			}
		});
	}).trigger('input');
