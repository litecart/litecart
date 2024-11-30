/*
 * jQuery Placeholders
 * by LiteCart
 */

+function($) {

	let Placeholders = []

	$.fn.Placeholder = function(options){
		this.each(() => {

			this.$element = $(this)

			this.settings = $.extend({
				aspectRatio: "1:1",
			}, options, this.$element.data())

			this.refresh = function(){
				let width = this.$element.width(),
					height = width / this.settings.aspectRatio.replace(/^([0-9]*):[0-9]*$/, '$1') * this.settings.aspectRatio.replace(/^[0-9]*:([0-9]*)$/, '$1')

				width = Math.round(width)
				height = Math.round(height)

				this.$element.text(width + '\u00d7' + height + ' (' +  this.settings.aspectRatio + ')')
					.css('font-size', Math.round(height/10) + 'px')
					.width('100%')
					.height(height)
			}

			this.refresh()

			Placeholders.push(this)
		})
	}

	$('.placeholder').Placeholder()

	$(window).on('resize', () => {
		$.each(Placeholders, (i, placeholder) => {
			placeholder.refresh()
		})
	})
}(jQuery)
