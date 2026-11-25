waitFor('jQuery', ($) => {

	const config = (window._env && window._env.backend && window._env.backend.copy) || {};

	// The async clipboard needs a secure context, execCommand covers plain http
	function copyText(text) {

		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(text).catch(() => fallbackCopy(text));
		}

		return fallbackCopy(text);
	}

	function fallbackCopy(text) {

		return new Promise((resolve, reject) => {

			const field = $('<textarea>').val(text)
				.css({position: 'fixed', top: '-9999px', left: 0, opacity: 0}).appendTo('body');

			field[0].select();
			field[0].setSelectionRange(0, text.length);

			const copied = document.execCommand('copy');

			field.remove();

			copied ? resolve() : reject(new Error('Copy failed'));
		});
	}

	function addNotice(text) {

		// The server only renders the container when a notice is queued, so build it when missing
		let $container = $('.notices');

		if (!$container.length) {
			$container = $('<div class="notices notices-floating" role="status" aria-live="polite"></div>').prependTo('#content');
		}

		$container.append(
			$('<div class="notice notice-default"></div>')
				.append($('<button type="button" class="close" data-dismiss="notice"></button>')
					.attr('aria-label', config.close || 'Close')
					.append($('<i class="icon icon-times" aria-hidden="true"></i>')))
				.append(' ' + (config.info_icon || '') + ' ')
				.append($('<span></span>').text(text))
		);
	}

	// Delegated so buttons rendered after page load are handled too
	$('body').on('click', '[data-copy-target]', function() {

		const $button = $(this);
		const target = document.getElementById($button.data('copy-target'));

		if (!target) return;

		copyText(target.innerText || target.textContent).then(() => {
			const label = $button.html();
			$button.html('<i class="icon icon-check"></i> ' + (config.copied || 'Copied'));
			setTimeout(() => $button.html(label), 2000);
		}).catch((error) => {
			console.warn('Copy to clipboard failed', error);
			addNotice(config.failed || 'The text could not be copied, copy it manually instead.');
		});
	});

});
