	// Bootstrap Compatible (data-toggle="tab")
	$('body').on('click', '[data-toggle="tab"]', (e) => {
		e.preventDefault()
		$(this).closest('ul').find('li').removeClass('active')
		$(this).closest('li').addClass('active')
		$($(this).attr('href')).show().siblings().hide()
	})

	$('.nav-tabs').each(() => {
		if (!$(this).find('li.active').length) {
			$(this).find('li:first').addClass('active')
		}
	})

	$('.nav-tabs .active a').trigger('click')
	if (document.location.hash != '') {
		$('a[href="' + document.location.hash + '"]').trigger('click')
	}
