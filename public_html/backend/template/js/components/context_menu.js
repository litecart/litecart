/*
 * jQuery Context Menu
 * by LiteCart
 */

+function() {

	$.fn.contextMenu = function(config){
		this.each(() => {

			this.config = config

			self = this

			$(this).on('contextmenu').on({
			})
		})
	}

}()
