/* Privacy Consent Manager */
waitFor('jQuery', ($) => {

	$.fn.privacyConsent = function(privacyClasses, consents) {
		var $element = $(this);

		$element.privacyClasses = privacyClasses || [];
		$element.consents = consents || [];

		$element.on('open', function(e) {
			e.preventDefault();
			$element.hide();
			$element.fadeIn();
		});

		$element.on('openExpanded', function(e) {
			e.preventDefault();
			$element.hide().find('.privacy-classes').addClass('expanded');
			$element.fadeIn();
		});

		$element.on('close', function(e) {
			e.preventDefault();
			$element.fadeOut(function(){
				$element.find('.privacy-classes').removeClass('expanded');
			});
		});

		$element.on('cookiesAccepted', function(e) {
			$('.require-consent').each(function() {
				if (hasPrivacyConsent($(this).data('privacy-class'), $(this).data('third-party-id'))) {
					$(this).replaceWith($(this).data('content'));
				}
			});

			$('script[type="application/x-privacy-script"]').each(function() {
				if (hasPrivacyConsent($(this).data('privacy-class'), $(this).data('third-party-id'))) {
					$newElement = $('<script>').attr('src', $(this).attr('src')).html($(this).prop('innerHTML'));
					$(this).replaceWith($newElement);
				}
			});

			$('script[type="application/x-privacy-content"]').each(function() {
				if (hasPrivacyConsent($(this).data('privacy-class'), $(this).data('third-party-id'))) {
					$(this).replaceWith($(this).prop('innerHTML'));
				}
			});

			$('.require-consent[data-privacy-class][third-party-id]').each(function() {
				if (hasPrivacyConsent($(this).data('privacy-class'), $(this).data('third-party-id'))) {
					$(this).replaceWith($(this).data('content'));
				}
			});
		});

		$element.hasConsent = function(privacyClass, third_party_id) {

		};

		$element.find('button[name="customize"]').click(function() {
			$element.find('.privacy-classes').toggleClass('expanded');
		});

		$element.find('input[name^="consents"][value="all"]').change(function() {
			var state = $(this).prop('checked');
			$(this).closest('.privacy-class').find('input[name^="consents"][value!="all"]').each(function() {
				$(this).prop('disabled', state);
				if (state) {
					$(this).prop('checked', true);
				}
			});
		}).trigger('change');

		$(document).ready(function() {
			$('.require-consent[data-privacy-class][third-party-id]').each(function() {
				var $output = $([
					'<p>This element requires a privacy consent for class &quot;' + $(this).data('privacy-class') + '&quot;).</p>',
					'<button class="btn btn-default"></button>'
				].join('\n'));

				$('button', $output).text("<?php echo functions::escape_js(language::translate('text_click_here_to_manage_cookie_consents', 'Click here to manage your cookie consents')); ?>");

				$('button', $output).on('click', function() {
					$element.trigger('openExpanded');
				});

				$(this).html($output);
			});

			if (document.cookie.match(/privacy_consents=/)) {
				$element.trigger('cookiesAccepted');
			}
		});

		return this;
	};

});