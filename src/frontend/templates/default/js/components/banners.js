// Banner Click Tracking

waitFor('jQuery', ($) => {

	var mouseOverAd = null;

	$('.banner[data-id]').hover(function() {
		mouseOverAd = $(this).data('id');
	}, function() {
		mouseOverAd = null;
	});

	$('.banner[data-id]').on('click', function() {
		$.post(_env.platform.path + 'ajax/event', {
			type: 'banner_click',
			description: 'User clicked a banner',
			data: {
				banner_id: $(this).data('id')
			}
		}, {dataType: 'json'});
	});

	$(window).on('blur', function() {
		if (mouseOverAd) {
			$.post(_env.platform.path + 'ajax/event', {
				type: 'banner_click',
				description: 'User clicked a banner',
				data: {
					banner_id: mouseOverAd
				}
			}, {dataType: 'json'});
		}
	});

});
