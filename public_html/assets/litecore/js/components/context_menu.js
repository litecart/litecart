/* Context Menu */

+waitFor('jQuery', ($) => {

	$.fn.contextMenu = function(config){
		this.each(function() {

			$(this).css({
				cursor: 'context-menu'
			});

			this.config = config;
			self = this;

			$(this).on('contextmenu').on({
			});
		});
	}

});