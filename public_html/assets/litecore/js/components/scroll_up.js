// Scroll Up
+waitFor('jQuery', ($) => {

	$(window).scroll(function() {
		if ($(this).scrollTop() > 300) {
			$('#scroll-up').fadeIn();
		} else {
			$('#scroll-up').fadeOut();
		}
	});

	$('#scroll-up').on('click', function() {
		$('html, body').animate({scrollTop: 0}, 1000, 'easeOutBounce');
		return false;
	});

});