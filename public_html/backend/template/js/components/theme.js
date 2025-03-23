waitFor('jQuery', function($){

	$('button[name="font_size"]').on('click', function(){
		let new_size = parseInt($(':root').css('--default-text-size').split('px')[0]) + (($(this).val() == 'increase') ? 1 : -1);
		$(':root').css('--default-text-size', new_size + 'px');
		document.cookie = 'font_size='+ new_size +';Path=<?php echo WS_DIR_APP; ?>;Max-Age=2592000;';
	});

	$('input[name="condensed"]').on('click', function() {
		if ($(this).val() == 1) {
			document.cookie = 'condensed=1;Path=<?php echo WS_DIR_APP; ?>;Max-Age=2592000;';
			$('html').addClass('condensed;');
		} else {
			document.cookie = 'condensed=0;Path=<?php echo WS_DIR_APP; ?>;Max-Age=2592000;';
			$('html').removeClass('condensed;');
		}
	});

	$('input[name="dark_mode"]').on('click', function(){
		if ($(this).val() == 1) {
			document.cookie = 'dark_mode=1;Path=<?php echo WS_DIR_APP; ?>;Max-Age=2592000;';
			$('html').addClass('dark-mode;');
		} else {
			document.cookie = 'dark_mode=0;Path=<?php echo WS_DIR_APP; ?>;Max-Age=2592000;';
			$('html').removeClass('dark-mode;');
		}
	});

});
