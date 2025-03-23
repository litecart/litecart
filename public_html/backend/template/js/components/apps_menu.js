// Filter
+waitFor('jQuery', ($) => {

	$('#sidebar input[name="filter"]').on({

		'input': function(){

			let query = $(this).val();

			if ($(this).val() == '') {
				$('#box-apps-menu .app').css('display', 'block');
				return;
			}

			$('#box-apps-menu .app').each(function() {
				var regex = new RegExp(''+ query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')  +'', 'ig');

				if (regex.test($(this).text())) {
					$(this).show();
				} else {
					$(this).hide();
				}
			});
		}
	});

});