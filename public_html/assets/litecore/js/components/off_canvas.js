// Off-Canvas Sidebar (data-toggle="offcanvas-collapse")
waitFor('jQuery', ($) => {

	$('[data-toggle="offcanvas"]').on('click', function() {
		$(this).closest('.navbar').toggleClass('expanded');
		$('body').toggleClass('offcanvas-open', $(this).closest('.navbar').hasClass('expanded'));
		$('body').css('overflow', $(this).closest('.navbar').hasClass('expanded') ? 'hidden' : '');
	});

});