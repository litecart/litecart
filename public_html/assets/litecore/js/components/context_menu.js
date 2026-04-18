waitFor('jQuery', ($) => {

	// Context Menu
	$.fn.contextMenu = function(config){
		this.each(function() {

			$(this).css({
				cursor: 'context-menu'
			});

			this.config = config;
			const self = this;

			$(this).on('contextmenu').on({
			});
		});
	}

});