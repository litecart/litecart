// Sidebar parallax effect
+waitFor('jQuery', ($) => {

	if (_env && _env.template.settings.sidebar_parallax_effect == true) {

		let $sidebar = $('#sidebar')
			//let sidebar_max_offset = $sidebar.parent().height() - $sidebar.height() - 200; // Failsafe 30

		$(window).on('resize scroll', function(e) {
			if ($(window).width() >= 768 && ($sidebar.parent().height() - $sidebar.height()) > 500) {
				let offset = $(this).scrollTop() * .6
				if (offset > 0) $sidebar.css('margin-top', offset + 'px')
			} else {
				$sidebar.css('margin-top', 0)
			}
		}).trigger('resize')
	}

})