// Tabs (data-toggle="tab")

+function($) {
	'use strict'

	$.fn.Tabs = function(){
		this.each(function() {

			const self = this
			this.$element = $(this)

			this.$element.find('[data-toggle="tab"]').each(function() {
				const $link = $(this)

				$link.on('select', function() {
					self.$element.find('.active').removeClass('active')

					if ($link.hasClass('nav-link')) {
						$link.addClass('active')
					}

					$link.closest('.nav-item').addClass('active')
					$($link.attr('href')).show().siblings().hide()
				})

				$link.on('click', function(e) {
					e.preventDefault()
					history.replaceState(null, null, $link[0].hash)
					$link.trigger('select')
				})
			})

			const activeTab = this.$element.find('.active')
			if (!activeTab.length) {
				this.$element.find('[data-toggle="tab"]').first().trigger('select')
			} else {
				activeTab.trigger('select')
	}
		})
	}

	$('.tabs').Tabs()

	if (document.location.hash && document.location.hash.match(/^#tab-/)) {
		$('[data-toggle="tab"][href="' + document.location.hash +'"]').trigger('select')
	}

	$(document).on('ajaxcomplete', function() {
		$('.tabs').Tabs()
	})

}(jQuery)
