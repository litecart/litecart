// Banner Click Tracking

+waitFor('jQuery', ($) => {

	var mouseOverAd = null;

	$('.banner[data-id]').hover(function() {
		mouseOverAd = $(this).data('id');
	}, function() {
		mouseOverAd = null;
	});

	$('.banner[data-id]').on('click', function() {
		$.post(_env.platform.path + 'ajax/bct', 'banner_id=' + $(this).data('id'));
	});

	$(window).on('blur', function() {
		if (mouseOverAd){
			$.post(_env.platform.path + 'ajax/bct', 'banner_id=' + mouseOverAd);
		}
	});

})