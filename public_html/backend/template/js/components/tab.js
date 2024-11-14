// Tabs (data-toggle="tab")
+function($) {
	'use strict';
	$.fn.Tabs = function(){
		this.each(function(){

			let self = this;

			this.$element = $(this);

			this.$element.find('[data-toggle="tab"]').each(function(){
				let $link = $(this);

				$link.on('select', function(){
					self.$element.find('.active').removeClass('active');

					if ($link.hasClass('nav-link')) {
						$link.addClass('active');
					}

					$link.closest('.nav-item').addClass('active');

					$($link.attr('href')).show().siblings().hide();
				});

				$link.on('click', function(e) {
					e.preventDefault();
					history.replaceState(null, null, this.hash);
					$link.trigger('select');
				});
			});

			if (!this.$element.find('.active').length) {
				this.$element.find('[data-toggle="tab"]').first().select();
			} else {
				this.$element.find('[data-toggle="tab"].active').select();
			}
		});
	}

	$('.nav-tabs').Tabs();

	if (document.location.hash && document.location.hash.match(/^#tab-/)) {
		$('[data-toggle="tab"][href="' + document.location.hash +'"]').trigger('select');
	}

	$(document).on('ajaxcomplete', function(){
		$('.nav-tabs').Tabs();
	});
}(jQuery);
