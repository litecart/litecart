// Form Input Tags
+waitFor('jQuery', ($) => {
	$('input[data-toggle="tags"]').each(function() {

		let $originalInput = $(this)

		let $tagField = $(
			'<div class="form-input">\
				<ul class="tokens">\
					<span class="input" contenteditable></span>\
				</ul>\
			</div>'
		)

		$tagField.tags = []

		$tagField.add = function(input){

			input = input.trim()

			if (!input) return

			$tagField.tags.push(input)

			let $tag = $(
				'<li class="tag">\
					<span class="value"></span>\
					<span class="remove">x</span>\
				</li>')

			$('.value', $tag).text(input)
			$('.input', $tagField).before($tag)

			$tagField.trigger('change')
		}

		$tagField.remove = function(input){

			$tagField.tags = $.grep($tagField.tags, function(value) {
				return value != input
			})

			$('.tag .value', $tagField).each(function() {
				if ($(this).text() == input) {
					$(this).parent('.tag').remove()
				}
			})

			$tagField.trigger('change')
		}

		let tags = $.grep($originalInput.val().split(/\s*,\s*/), function(value) {
			return value
		})

		$.each(tags, function() {
			$tagField.add(this)
		})

		$tagField.on('keypress', '.input', function(e) {
			if (e.which == 44 || e.which == 13) { // Comma or enter
				e.preventDefault()
				$tagField.add($(this).text())
				$(this).text('')
			}
		})

		$tagField.on('blur', '.input', function() {
			$tagField.add($(this).text())
			$(this).text('')
		})

		$tagField.on('click', '.remove', function(e) {
			$tagField.remove($(this).siblings('.value').text())
		})

		$tagField.on('change', function() {
			$originalInput.val($tagField.tags.join(','))
		})

		$(this).hide().after($tagField)
	})
})