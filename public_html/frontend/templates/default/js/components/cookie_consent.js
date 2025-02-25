if (typeof window.areCookiesAccepted == 'undefined') {
	window.areCookiesAccepted = function(type){
		var classes = document.cookie.replace(/.*cookies_accepted=([^;]+).*/, '$1').split(',');
		if (classes.includes(type)) return true;
	}
}

var classes = document.cookie.replace(/.*cookies_accepted=([^;]+).*/, '$1').split(',');

$('#site-cookie-consent').on({

	'open': function(e) {
		e.preventDefault();
		$(this).hide();
		$(this).fadeIn();
	},

	'openExpanded': function(e) {
		e.preventDefault();
		$(this).hide().find('.privacy-classes').addClass('expanded');
		$(this).fadeIn();
	},

	'close': function(e) {
		e.preventDefault();
		$(this).fadeOut(function(){
			$(this).find('.privacy-classes').removeClass('expanded');
		});
	},

	'click': function(e) {
		if ($(e.target).is('button[name="customize"]')) {
			e.preventDefault();
			$(this).find('.privacy-classes').toggleClass('expanded');
			//$(this).trigger('openExpanded');
		}
	},

	'cookiesAccepted': function(e){

		var consented_classes = $.map($('box-cookie-consent input[name="privacy_classes[]"]:checked'), function(element) {
			return element.val();
		});

		var consented_parties = $.map($('box-cookie-consent input[name="third_parties[]"]:checked'), function(element) {
			return element.val();
		});

		console.log('Cookies are accepted');

		if (areCookiesAccepted('necessary')) {
			$('.require-consent[data-cookie-class="necessary"]').each(function(){
				$(this).replaceWith($(this).data('content'));
			});
		}

		if (areCookiesAccepted('functionality')) {
			$('.require-consent[data-cookie-class="functionality"]').each(function(){
				$(this).replaceWith($(this).data('content'));
			});
		}

		if (areCookiesAccepted('experience')) {
			$('.require-consent[data-cookie-class="experience"]').each(function(){
				$(this).replaceWith($(this).data('content'));
			});
		}

		if (areCookiesAccepted('measurement')) {
			$('.require-consent[data-cookie-class="measurement"]').each(function(){
				$(this).replaceWith($(this).data('content'));
			});
		}

		if (areCookiesAccepted('marketing')) {
			$('.require-consent[data-cookie-class="marketing"]').each(function(){
				$(this).replaceWith($(this).data('content'));
			});
		}
	}
});

$('box-cookie-consent').hasConsent = function(class, third_party_id){
	return (_env.cookie_consents && (_env.cookie_consents.classes.includes(class) || _env.cookie_consents.classes.includes(third_party_id)));
};

$('#site-cookie-consent button[name="customize"]').click(function(){
	$('#site-cookie-consent .privacy-classes').toggleClass('expanded');
});

$('#site-cookie-consent input[name="privacy_classes[]"]').change(function(){
	var state = $(this).prop('checked');
	$(this).closest('.privacy-class').find('input[name="third_parties[]"]').each(function(){
		$(this).prop('disabled', state);
		$(this).prop('checked', state);
	});
}).trigger('change');

/*
// Removed as it doesn't play well with some embedded third party components
$('#site-cookie-consent button[name="accept_cookies"]').click(function(e){
	e.preventDefault();

	let classes = $('#site-cookie-consent :checkbox:checked').map(function(){ return $(this).val(); }).get();
	console.log('Accepting cookies', classes);

	document.cookie = 'cookies_accepted=' + classes.join(',') + '; Max-Age=' + (365 * 24 * 60 * 60 * 1000) +'; Path=<?php echo WS_DIR_APP; ?>; SameSite=Lax';
	$(document).trigger('cookiesAccepted');
	$('#site-cookie-consent').fadeOut();
});

$('#site-cookie-consent button[name="reject_cookies"]').click(function(e){
	e.preventDefault();

	$('#site-cookie-consent').fadeOut();
	document.cookie = 'cookies_accepted=necessary; Expires=0; Path=/; SameSite=Lax';
});
*/

// Init conditional placeholders

$('.require-consent[data-privacy-party]').each(function(){

	var $output = $([
		'<p>This element requires a privacy consent for third party &quot;'+ $(this).data('privacy-party') +'&quot;).</p>',
		'<button class="btn btn-default"></button>'
	].join('\n'));

	$('button').text("<?php echo functions::escape_js(language::translate('text_click_here_to_manage_cookie_consents', 'Click here to manage your cookie consents')); ?>");

	$(this).html($output);

	$(this).on('click', 'button', function(){
		$('#site-cookie-consent').trigger('openExpanded');
	});
});

// Autoload?
if (document.cookie.match(/cookies_accepted=/)) {
	$(document).trigger('cookiesAccepted');
}
