/*
 * jQuery Context Menu
 * by LiteCart
 */

+function() {

	$.fn.contextMenu = function(config){
		this.each(function(){

			this.config = config;

			self = this;

			$(this).on('contextmenu').on({
			});
		});
	}

}();
