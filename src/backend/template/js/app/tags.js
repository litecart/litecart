waitFor('jQuery', ($) => {
	'use strict';

	$(function() {

		$('input[data-toggle="input-tags"]').each(function() {

			const $input = $(this);

			if ($input.data('input-tags-bound')) return;
			$input.data('input-tags-bound', true);

			let allSuggestions = [];
			try {
				const raw = $input.attr('data-suggestions');
				if (raw) {
					const parsed = JSON.parse(raw);
					if (Array.isArray(parsed)) {
						allSuggestions = parsed.map(String);
					}
				}
			} catch (e) {
				allSuggestions = [];
			}

			const $wrapper = $('<div class="form-input"></div>');
			const $chips = $('<div class="tag-chips"></div>');
			const $editor = $('<input type="text" class="tag-input" autocomplete="off">');
			const $suggestions = $('<ul class="tag-suggestions" hidden></ul>');

			allSuggestions.forEach(function(value) {
				$('<li class="tag-suggestion"></li>')
					.attr('data-value', value)
					.text(value)
					.appendTo($suggestions);
			});

			$wrapper.append($chips).append($editor);
			if (allSuggestions.length) $wrapper.append($suggestions);

			function getTags() {
				return $chips.find('> .tag-chip > .tag-value').map(function() {
					return $(this).text();
				}).get();
			}

			function commit() {
				$input.val(getTags().join(','));
				$input.trigger('change');
			}

			function addTag(value) {
				value = String(value || '').trim();
				if (!value) return false;
				if (getTags().indexOf(value) !== -1) return false;

				const $value = $('<span class="tag-value"></span>').text(value);
				const $remove = $('<button type="button" class="tag-remove" aria-label="Remove">&times;</button>');
				$('<span class="tag-chip"></span>').append($value).append($remove).appendTo($chips);
				commit();
				return true;
			}

			function removeTag(value) {
				let removed = false;
				$chips.find('> .tag-chip').each(function() {
					if ($(this).find('> .tag-value').text() === value) {
						$(this).remove();
						removed = true;
					}
				});
				if (removed) commit();
			}

			function showSuggestions() {
				if (!$suggestions.length) return;

				const q = $editor.val().toLowerCase().trim();
				const tags = getTags();
				let visible = 0;

				$suggestions.find('> .tag-suggestion').each(function() {
					const $li = $(this);
					const value = String($li.data('value') || '');
					const alreadyTagged = tags.indexOf(value) !== -1;
					const matches = !q || value.toLowerCase().indexOf(q) !== -1;
					const show = !alreadyTagged && matches;
					$li.toggle(show);
					if (show) visible++;
				});

				if (visible > 0) {
					$suggestions.removeAttr('hidden');
				} else {
					$suggestions.attr('hidden', 'hidden');
				}
			}

			function hideSuggestions() {
				$suggestions.attr('hidden', 'hidden');
			}

			function commitTyped() {
				const value = $editor.val();
				if (addTag(value)) {
					$editor.val('');
					commit();
				}
				showSuggestions();
			}

			const initial = $input.val() || '';
			initial.split(/\s*,\s*/).forEach(function(v) {
				if (v) {
					const $value = $('<span class="tag-value"></span>').text(v);
					const $remove = $('<button type="button" class="tag-remove" aria-label="Remove">&times;</button>');
					$('<span class="tag-chip"></span>').append($value).append($remove).appendTo($chips);
				}
			});
			$input.val(getTags().join(','));

			$editor.on('focus', showSuggestions);

			$editor.on('input', showSuggestions);

			$editor.on('keydown', function(e) {

				if (e.key === ',' || e.key === ' ' || e.key === 'Enter') {
					if (e.key === ' ' && !$editor.val()) return;
					e.preventDefault();
					commitTyped();
					return;
				}

				if (e.key === 'Escape') {
					hideSuggestions();
					return;
				}

				if (e.key === 'Backspace' && !$editor.val()) {
					e.preventDefault();
					const tags = getTags();
					if (tags.length) removeTag(tags[tags.length - 1]);
					showSuggestions();
				}
			});

			$editor.on('blur', function() {
				hideSuggestions();
				commitTyped();
			});

			$wrapper.on('focusout', function(e) {
				if (!$wrapper[0].contains(e.relatedTarget)) {
					hideSuggestions();
				}
			});

			$suggestions.on('mousedown', '> .tag-suggestion', function(e) {
				e.preventDefault();
				const value = String($(this).data('value') || '');
				if (addTag(value)) {
					$editor.val('');
					commit();
				}
				$editor.focus();
				showSuggestions();
			});

			$chips.on('click', '> .tag-chip > .tag-remove', function(e) {
				e.preventDefault();
				const value = $(this).siblings('.tag-value').text();
				removeTag(value);
				showSuggestions();
			});

			$input.hide().after($wrapper);
		});
	});
});
