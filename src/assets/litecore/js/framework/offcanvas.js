// Off-Canvas Sidebar (data-toggle="offcanvas")
waitFor('jQuery', ($) => {

	$('[data-toggle="offcanvas"]').on('click', function() {
		const $navbar = $(this).closest('.navbar');
		const $button = $(this);
		$navbar.toggleClass('expanded');
		const isExpanded = $navbar.hasClass('expanded');
		$('body').toggleClass('offcanvas-open', isExpanded);
		$('body').css('overflow', isExpanded ? 'hidden' : '');
		$button.attr('aria-expanded', isExpanded);
	});

});