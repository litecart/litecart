waitFor('jQuery', ($) => {

	function escapeHtml(s) {
		return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	}

	function escapeRegex(s) {
		return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
	}

	function highlight(text, query) {
		const escaped = escapeHtml(text);
		if (!query) return escaped;
		const regex = new RegExp('(' + escapeRegex(query) + ')', 'ig');
		return escaped.replace(regex, '<u>$1</u>');
	}

	// Filter
	$('#sidebar input[name="filter"]').on({

		'input': function(){

			const query = $(this).val();
			const q = query.toLowerCase();
			const $menu = $('#sidebar-menu');

			// Restore any previously highlighted text
			$menu.find('.name').each(function() {
				const $name = $(this);
				if ($name.data('original') !== undefined) {
					$name.text($name.data('original'));
				}
			});

			// Reset visibility
			$menu.find('.app, .docs, .doc, .group').css('display', '');

			if (!query) {
				return;
			}

			$menu.find('.app').each(function() {

				const $app = $(this);
				const $appLink = $app.children('a');
				const $docsList = $app.children('.docs');
				const $docs = $docsList.children('.doc');

				const appMatches = $appLink.text().toLowerCase().includes(q);

				let hasMatchingDoc = false;
				$docs.each(function() {
					if ($(this).text().toLowerCase().includes(q)) {
						hasMatchingDoc = true;
						return false;
					}
				});

				if (appMatches) {
					// Direct app name match: show app with all its docs expanded
					$app.show();
					$docsList.show();
					$docs.show();
				} else if (hasMatchingDoc) {
					// Only doc(s) match: show app and only matching docs
					$app.show();
					$docsList.show();
					$docs.each(function() {
						if ($(this).text().toLowerCase().includes(q)) {
							$(this).show();
						} else {
							$(this).hide();
						}
					});
				} else {
					$app.hide();
				}
			});

			// Hide groups that ended up with no visible apps
			$menu.find('.group').each(function() {
				const $group = $(this);
				const hasVisible = $group.find('.app').filter(':visible').length > 0;
				$group.css('display', hasVisible ? '' : 'none');
			});

			// Underscore the matching substring inside visible app and doc names
			$menu.find('.app').filter(':visible').find('.name')
				.add($menu.find('.doc').filter(':visible').find('.name'))
				.each(function() {
					const $name = $(this);
					if ($name.data('original') === undefined) {
						$name.data('original', $name.text());
					}
					$name.html(highlight($name.data('original'), query));
				});
		}
	});

});
