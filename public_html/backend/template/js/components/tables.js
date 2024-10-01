	// Data-Table Toggle Checkboxes
	$('body').on('click', '.data-table *[data-toggle="checkbox-toggle"], .data-table .checkbox-toggle', function() {
		$(this).closest('.data-table').find('tbody td:first-child :checkbox').each(function() {
			$(this).prop('checked', !$(this).prop('checked')).trigger('change');
		});
		return false;
	});

	$('body').on('click', '.data-table tbody tr', function(e) {
		if ($(e.target).is('a') || $(e.target).closest('a').length) return;
		if ($(e.target).is('.btn, :input, th, .fa-star, .fa-star-o')) return;
		$(this).find(':checkbox, :radio').first().trigger('click');
	});

	// Data-Table Shift Check Multiple Checkboxes
	let lastTickedCheckbox = null;
	$('.data-table td:first-child :checkbox').on('click', function(e){

		let $chkboxes = $('.data-table td:first-child :checkbox');

		if (!lastTickedCheckbox) {
			lastTickedCheckbox = this;
			return;
		}

		if (e.shiftKey) {
			let start = $chkboxes.index(this);
			let end = $chkboxes.index(lastTickedCheckbox);
			$chkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastTickedCheckbox.checked);
		}

		lastTickedCheckbox = this;
	});

	// Data-Table Dragable
	$('body').on('click', '.table-dragable tbody .grabable', function(e){
		e.preventDefault();
		return false;
	});

	$('body').on('mousedown', '.table-dragable tbody .grabable', function(e){
		let tr = $(e.target).closest('tr'), sy = e.pageY, drag;
		if ($(e.target).is('tr')) tr = $(e.target);
		let index = tr.index();
		$(tr).addClass('grabbed');
		$(tr).closest('tbody').css('unser-input', 'unset');
		function move(e) {
			if (!drag && Math.abs(e.pageY - sy) < 10) return;
			drag = true;
			tr.siblings().each(function() {
				let s = $(this), i = s.index(), y = s.offset().top;
				if (e.pageY >= y && e.pageY < y + s.outerHeight()) {
					if (i < tr.index()) s.insertAfter(tr);
					else s.insertBefore(tr);
					return false;
				}
			});
		}
		function up(e) {
			if (drag && index != tr.index()) {
				drag = false;
			}
			$(document).off('mousemove', move).off('mouseup', up);
			$(tr).removeClass('grabbed');
			$(tr).closest('tbody').css('unser-input', '');
		}
		$(document).mousemove(move).mouseup(up);
	});

	// Data-Table Sorting (Page Reload)
	$('.table-sortable thead th[data-sort]').on('click', function(){
		let params = {};

		window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
			params[key] = value;
		});

		params.sort = $(this).data('sort');

		window.location.search = $.param(params);
	});
