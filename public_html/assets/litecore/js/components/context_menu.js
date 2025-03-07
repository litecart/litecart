/*
 * jQuery Context Menu
 * by LiteCart
 */

+waitFor('jQuery', ($) => {

	$.fn.contextMenu = function(config){
		this.each(function() {

			this.config = config
			self = this

			$(this).on('contextmenu').on({
			})
		})
	}

})