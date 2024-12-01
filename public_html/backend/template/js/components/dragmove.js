	// Dragmove

	$('style').first().append([
		'.dragmove-horizontal {',
		'  cursor: e-resize;',
		'  user-select: none;',
		'}',
		'.dragmove-vertical {',
		'  cursor: n-resize;',
		'  user-select: none;',
		'}',
		'.dragmove-vertical.grabbed,',
		'.dragmove-horizontal.grabbed	{',
		'  user-input: unset;',
		'  box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.5);',
		'}',
	].join('\n'))

	$('body').on('click', '.dragmove', function(e) {
		e.preventDefault()
		return false
	})

	$('body').on('mousedown', '.dragmove-vertical, .dragmove-horizontal', function(e) {

		let $item = $(e.target).closest('.dragmove'),
			sy = e.pageY,
			drag

		if ($(e.target).is('.dragmove')) {
			$item = $(e.target)
		}

		let index = $item.index()

		$item.addClass('grabbed')
		$item.closest('tbody').css('user-input', 'unset')

		function move(e) {

			if (!drag && Math.abs(e.pageY - sy) < 10) return
			drag = true

			$item.siblings().each(function() {

				let s = $(this), i = s.index(), y = s.offset().top

				if (e.pageY >= y && e.pageY < y + s.outerHeight()) {
					if (i < $item.index()) s.insertAfter($item)
					else s.insertBefore($item)
					return false
				}
			})
		}

		function up(e) {

			if (drag && index != $item.index()) {
				drag = false
			}

			$(document).off('mousemove', move).off('mouseup', up)
			$item.removeClass('grabbed')
			$item.closest('tbody').css('user-input', '')
		}

		$(document).mousemove(move).mouseup(up)
	})
