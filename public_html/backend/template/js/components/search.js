// Filter
$('#sidebar input[name="filter"]').on({

	'input': function(){

		let query = $(this).val()

		if ($(this).val() == '') {
			$('#box-apps-menu .app').css('display', 'block')
			return
		}

		$('#box-apps-menu .app').each(function(){
			var regex = new RegExp(''+ query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')  +'', 'ig')
			console.log()
			if (regex.test($(this).text())) {
				$(this).show()
			} else {
				$(this).hide()
			}
		})
	}
})

// AJAX Search
let timer_ajax_search = null
let xhr_search = null

$('#search input[name="query"]').on({

	'focus': function(){
		if ($(this).val()) {
			$('#search.dropdown').addClass('open')
		}
	},

	'blur': function(){
		if (!$('#search').filter(':hover').length) {
			$('#search.dropdown').removeClass('open')
		} else {
			$('#search.dropdown').on('blur', () => {
				$('#search.dropdown').removeClass('open')
			})
		}
	},

	'input': function(){

		if (xhr_search) {
			xhr_search.abort()
		}

		let $searchField = $(this)

		if ($searchField.val()) {

			$('#search .results').html([
				'<div class="loader-wrapper text-center">',
				'  <div class="loader" style="width: 48px; height: 48px;"></div>',
				'</div>'
			].join('\n'))

			$('#search.dropdown').addClass('open')

		} else {
			$('#search .results').html('')
			$('#search.dropdown').removeClass('open')
			return
		}

		clearTimeout(timer_ajax_search)

		timer_ajax_search = setTimeout(function() {
			xhr_search = $.ajax({
				type: 'get',
				async: true,
				cache: false,
				url: window._env.backend.url + 'search_results.json?query=' + $searchField.val(),
				dataType: 'json',

				beforeSend: (jqXHR) => {
					jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'))
				},

				error: (jqXHR, textStatus, errorThrown) => {
					$('#search .results').text(textStatus + ': ' + errorThrown)
				},

				success: (json) => {

					$('#search .results').html('')

					if (!$('#search input[name="query"]').val()) {
						$('#search .results').html('Search')
						return
					}

					$.each(json, function(i, group){

						if (group.results.length) {

							$('#search .results').append(
								'<h4>'+ group.name +'</h4>' +
								'<ul class="flex flex-rows" data-group="'+ group.name +'"></ul>'
							)

							$.each(group.results, function(i, result){

								var $li = $([
									'<li class="result">',
									'  <a class="list-group-item" href="'+ result.link +'" style="border-inline-start: 3px solid '+ group.theme.color +'; background: '+ group.theme.color +'11;">',
									'    <small class="id float-end">#'+ result.id +'</small>',
									'    <div class="title">'+ result.title +'</div>',
									'    <div class="description"><small>'+ result.description +'</small></div>',
									'  </a>',
									'</li>'
								].join('\n'))

								$('#search .results ul[data-group="'+ group.name +'"]').append($li)
							})
						}
					})

					if ($('#search .results').html() == '') {
						$('#search .results').html('<p class="text-center no-results"><em>:(</em></p>')
					}
				},
			})
		}, 500)
	}
})
