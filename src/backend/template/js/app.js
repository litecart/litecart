/*!
	LiteCart v3.0.0 - Superfast, lightweight e-commerce platform built built with for simplicity.
	Link: https://www.litecart.net/
	License: CC-BY-ND-4.0
	Author: T. Almroth, LiteCart AB
*/

waitFor('jQuery', ($) => {

	// CSRF token for AJAX requests
	$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
		if (!/^(GET|HEAD|OPTIONS)$/i.test(options.type) && window._env && window._env.csrf_token) {
			jqXHR.setRequestHeader('X-CSRF-Token', window._env.csrf_token);
		}
	});

});

waitFor('jQuery', ($) => {

	$.fn.categoryPicker = function(config){
		this.each(function() {

			let xhr = null;
			const cfg = config;

			const $self = $(this);

			$self.find('.dropdown input[type="search"]').on({

				'focus': function(e) {
					$self.find('.dropdown').addClass('open');
				},

				'input': function(e) {
						let $dropdownMenu = $self.find('.dropdown-content');

						$dropdownMenu.html('');

						if (xhr) {
							xhr.abort();
						}

						if ($(this).val() == '') {

							$.getJSON(cfg.link, function(result) {

								$dropdownMenu.html(
									'<h3 style="margin-top: 0;">'+ result.name +'</h3>'
								);

								$.each(result.subcategories, function(i, category) {
									$dropdownMenu.append([
										'<div class="category flex" style="align-items: center;" data-id="'+ category.id + '" data-name="'+ category.path.join(' &gt; ') + '">',
										'\t' + cfg.icons.folder + '<a href="#" data-link="'+ cfg.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>',
										'\t<button name="add" class="btn btn-default btn-sm" type="button">'+ cfg.translations.add +'</button>',
										'</div>',
									].join('\n'));
								});
							});

							return;
						}

						xhr = $.ajax({
							type: 'get',
							async: true,
							cache: true,
							url: cfg.link + '&query=' + $(this).val(),
							dataType: 'json',

							beforeSend: function(jqXHR) {
								jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
							},

							error: function(jqXHR, textStatus, errorThrown) {
								if (errorThrown == 'abort') return;
								alert(errorThrown);
							},

							success: function(result) {

								if (!result.subcategories?.length) {
									$dropdownMenu.html(
										'<div class="text-center no-results"><em>:(</em></div>'
										);
										return;
									}

									$dropdownMenu.html(
										'<h3 style="margin-top: 0;">'+ cfg.translations.search_results +'</h3>'
									);

									$.each(result.subcategories, function(i, category) {
										$dropdownMenu.append([
											'<div class="category flex" style="align-items: center;" data-id="'+ category.id + '" data-name="'+ category.path.join(' &gt; ') + '">',
											'\t' + cfg.icons.folder + '<a href="#" data-link="'+ cfg.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>',
											'\t<button name="add" class="btn btn-default btn-sm" type="button">'+ cfg.translations.add +'</button>',
											'</div>',
										].join('\n'));
									});
								},
							});
						}
			});

			$self.on('click', '.dropdown-content a', function(e) {
				e.preventDefault();

				let $dropdownMenu = $(this).closest('.dropdown-content');

				$.getJSON($(this).data('link'), function(result) {

						$dropdownMenu.html(
							'<h3 style="margin-top: 0;">'+ result.name +'</h3>'
						);

						if (result.parent) {
							$dropdownMenu.append([
								'<div class="flex" style="align-items: center;" data-id="'+ result.parent.id +'" data-name="'+ result.parent.name +'">',
								'\t' + cfg.icons.back + '<a href="#" data-link="'+ cfg.link +'?parent_id='+ result.parent.id +'" style="flex-grow: 1;">'+ result.parent.name +'</a>',
								'</div>',
							].join('\n'));
						}

						$.each(result.subcategories, function(i, category) {
							$dropdownMenu.append([
								'<div class="category flex" style="align-items: center;" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'">',
								'\t' + cfg.icons.folder +' <a href="#" data-link="'+ cfg.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>',
								'\t<button name="add" class="btn btn-default btn-sm" type="button">'+ cfg.translations.add +'</button>',
								'</div>',
							].join('\n'));
						});
				});
			});

			$self.on('click', '.dropdown-content button[name="add"]', function(e) {
				e.preventDefault();

				let category = $(this).closest('.category').data(),
					abort = false;

				$self.find('input[name="'+ cfg.inputName +'"]').each(function() {
					if ($(this).val() == category.id) {
						abort = true;
						return;
					}
				});

				if (abort) return;

				let inputField = $('<input>', {
					type: 'hidden',
					name: cfg.inputName,
					value: category.id,
					"data-name": category.name
				})[0].outerHTML;

				$self.find('ul').append([
					'<li class="list-item flex">',
					'	<div style="flex-grow: 1;">',
					'		'+ inputField,
					'		'+ cfg.icons.folder +' '+ category.name,
					'	</div>',
					'	<button name="remove" class="btn btn-default btn-sm" type="button">',
					'		'+ cfg.translations.remove,
					'	</button>',
					'</li>',
				].join('\n'));

				$self.trigger('change');

				$('.dropdown.open').removeClass('open');

				return false;
			});

			$self.on('click', 'button[name="remove"]', function(e) {
				$(this).closest('li').remove();
				$self.trigger('change');
			});

			$('body').on('mousedown', function(e) {
				if ($('.dropdown.open').has(e.target).length === 0) {
					$('.dropdown.open').removeClass('open');
				}
			});

			$(this).find('input[type="search"]').trigger('input');
		});
	};

});


waitFor('jQuery', function($) {

	// jQuery plugin: $.git(config) — initializes the git admin app on #git-manager containers.
	$.git = function(config) {
		config = config || {};
		const $container = $(config.container || '#git-manager');
		$container.each(function() {
			const $root = $(this);
			if (!$root.data('git-manager')) {
				$root.data('git-manager', new GitApp($root, config));
			}
		});
		return $container;
	};

	// ===== Module-private helpers (no instance state) =====

	const STATUS_META = {
		'M': { label: 'M', cls: 's-modified',    title: 'Modified',    pri: 2 },
		'A': { label: 'A', cls: 's-added',       title: 'Added',       pri: 3 },
		'D': { label: 'D', cls: 's-deleted',     title: 'Deleted',     pri: 1 },
		'R': { label: 'R', cls: 's-renamed',     title: 'Renamed',     pri: 4 },
		'C': { label: 'C', cls: 's-renamed',     title: 'Copied',      pri: 4 },
		'U': { label: 'U', cls: 's-conflicted',  title: 'Conflicted',  pri: 0 },
		'?': { label: '?', cls: 's-untracked',   title: 'Untracked',   pri: 6 },
		'!': { label: '!', cls: 's-untracked',   title: 'Ignored',     pri: 7 },
	};

	function statusMeta(x, y) {
		if (x === '?' && y === '?') return STATUS_META['?'];
		if (x === '!' && y === '!') return STATUS_META['!'];
		if (x === 'U' || y === 'U') return STATUS_META['U'];
		return STATUS_META[x] || STATUS_META[y] || { label: x || y || '?', cls: 's-untracked', title: x || y, pri: 99 };
	}

	function escapeHtml(s) {
		return String(s)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	}

	function cssEscape(s) {
		if (window.CSS && CSS.escape) return CSS.escape(s);
		return String(s).replace(/["\\]/g, '\\$&');
	}

	function fallbackCopy(text) {
		const $ta = $('<textarea></textarea>')
			.css({ position: 'fixed', left: '-9999px' })
			.val(text)
			.appendTo('body');
		$ta[0].select();
		try { document.execCommand('copy'); } catch (e) {}
		$ta.remove();
	}

	function parseDiff(diffText) {
		const hunks = [];
		const lines = diffText.split(/\r?\n/);
		let current = null;
		let oldLine = 0;
		let newLine = 0;

		for (let i = 0; i < lines.length; i++) {
			const line = lines[i];

			if (line.startsWith('diff --git ') || line.startsWith('new file') || line.startsWith('deleted file') || line.startsWith('---') || line.startsWith('+++')) {
				continue;
			}

			if (line.startsWith('@@')) {
				if (current) hunks.push(current);
				const m = line.match(/@@ -(\d+)(?:,\d+)? \+(\d+)(?:,\d+)? @@/);
				current = {
					header: line,
					oldStart: m ? parseInt(m[1], 10) : 0,
					oldCount: m && m[2] ? parseInt(m[2], 10) : 1,
					newStart: m ? parseInt(m[3], 10) : 0,
					newCount: m && m[4] ? parseInt(m[4], 10) : 1,
					lines: [],
				};
				oldLine = m ? parseInt(m[1], 10) : 0;
				newLine = m ? parseInt(m[3], 10) : 0;
				continue;
			}

			if (line.startsWith('\\ No newline')) continue;
			if (!current) continue;

			if (line.startsWith('+')) {
				current.lines.push({ type: 'add', text: line.substring(1), oldLine: null, newLine: newLine });
				newLine++;
				continue;
			}
			if (line.startsWith('-')) {
				current.lines.push({ type: 'del', text: line.substring(1), oldLine: oldLine, newLine: null });
				oldLine++;
				continue;
			}
			if (line.startsWith(' ')) {
				current.lines.push({ type: 'ctx', text: line.substring(1), oldLine: oldLine, newLine: newLine });
				oldLine++;
				newLine++;
			}
		}
		if (current) hunks.push(current);
		return hunks;
	}

	function sortFiles(files) {
		return files.slice().sort(function(a, b) {
			const ma = statusMeta(a.x, b.y ? b.y : a.x);
			const mb = statusMeta(b.x, b.y);
			if (ma.pri !== mb.pri) return ma.pri - mb.pri;
			return a.path.localeCompare(b.path);
		});
	}

	// ===== GitApp instance =====

	function GitApp($root, config) {
		this.$root = $root;
		this.config = config;
		this.baseUrl = config.baseUrl || '';
		this.i18n = config.i18n || {};

		// Selection set used for both diff viewing and bulk actions.
		this.selectedFiles = new Map();
		// Per-file map of selected +/- lines for partial-line staging.
		this.lineSelections = new Map();
		// Last plain-clicked file row — anchor for shift-range file-list selection.
		this.anchor = null;
		// Last plain-clicked line in any diff table — anchor for shift-range.
		this.lineAnchor = null;
		this.stagedSet = new Set();
		this.unstagedSet = new Set();
		// Cache of untracked-flag so placeholder can show right action buttons before diff loads.
		this.fileStateCache = new Map();
		this.$contextMenu = null;
		// Per-list filter strings (case-insensitive substring match on path).
		this.unstagedFilter = '';
		this.stagedFilter = '';

		this.wireDocument();
		this.wireButtons();
		this.loadStatus();
	}

	GitApp.prototype.t = function(key, fallback) {
		return this.i18n[key] || fallback;
	};

	// ===== Toast =====
	// Wider default; hover pauses the auto-dismiss; click copies to clipboard with confirmation.
	GitApp.prototype.toast = function(message, type) {
		const variant = type === 'error' ? 'is-error' : type === 'success' ? 'is-success' : 'is-default';
		const icon = type === 'error' ? 'bi-x-circle'
			: type === 'success' ? 'bi-check-circle'
			: 'bi-info-circle';

		const $el = $('<div></div>')
			.addClass('git-toast ' + variant)
			.attr('role', 'status')
			.attr('aria-live', 'polite');

		const $icon = $('<i class="bi ' + icon + ' icon"></i>');
		const $body = $('<span class="git-toast-body"></span>').text(message);

		const self = this;
		const TOAST_LIFETIME_MS = 8000;

		$el.append($icon, $body).attr('title', self.t('tooltip_toast_click_to_copy', 'Click to copy'));
		$el.appendTo('#toast-host');

		let dismissTimer = setTimeout(dismiss, TOAST_LIFETIME_MS);
		let copied = false;

		$el.on('mouseenter', function() {
			if (dismissTimer) {
				clearTimeout(dismissTimer);
				dismissTimer = null;
				$el.css('opacity', '1');
			}
		});
		$el.on('mouseleave', function() {
			if (!dismissTimer && !copied) {
				dismissTimer = setTimeout(dismiss, TOAST_LIFETIME_MS);
			}
		});

		$el.on('click', function() {
			copied = true;
			if (dismissTimer) {
				clearTimeout(dismissTimer);
				dismissTimer = null;
			}
			const text = $body.text();
			const afterCopy = function() {
				$el.addClass('copied');
				$body.text(self.t('toast_copied_to_clipboard', 'Copied to clipboard'));
				setTimeout(dismiss, 1500);
			};
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(afterCopy).catch(function() {
					fallbackCopy(text);
					afterCopy();
				});
			} else {
				fallbackCopy(text);
				afterCopy();
			}
		});

		function dismiss() {
			$el.css({ opacity: '0', transform: 'translateY(-4px)' });
			setTimeout(function() { $el.remove(); }, 300);
		}
	};

	GitApp.prototype.fileArea = function(path) {
		if (this.stagedSet.has(path)) return 'staged';
		if (this.unstagedSet.has(path)) return 'unstaged';
		return null;
	};

	GitApp.prototype.fileMetaFor = function(file) {
		return this.fileStateCache.get(file);
	};

	// ===== File list rendering =====
	GitApp.prototype.renderList = function($el, files, area) {
		const self = this;
		$el.empty();
		sortFiles(files).forEach(function(file) {
			const meta = statusMeta(file.x, file.y);
			const $tag = $('<span></span>')
				.addClass('status-tag ' + meta.cls)
				.attr('title', self.t('title_status', 'Status') + ': ' + meta.title)
				.text(meta.label);

			const $name = $('<span class="filename"></span>').text(file.path);

			const actionLabel = area === 'staged' ? '−' : '+';
			const actionTitle = area === 'staged'
				? self.t('title_quick_unstage', 'Unstage')
				: self.t('title_quick_stage', 'Stage');
			const $action = $('<button type="button" class="quick-action"></button>')
				.attr('title', actionTitle)
				.text(actionLabel)
				.on('click', function(e) {
					e.stopPropagation();
					self.stageOrUnstage(file.path);
				});

			const $li = $('<li></li>')
				.attr('data-file', file.path)
				.attr('data-area', area);
			$li.append($tag, $name, $action);

			if (self.selectedFiles.has(file.path)) {
				$li.addClass('selected');
			}

			$li.on('click', function(e) { self.handleRowClick(file.path, e); });
			$li.on('contextmenu', function(e) {
				e.preventDefault();
				// Replace selection with just this file unless it was already multi-selected.
				if (!self.selectedFiles.has(file.path)) {
					self.selectedFiles.clear();
					self.selectedFiles.set(file.path, area);
					self.applySelectionStyles();
				}
				self.openContextMenu(e.clientX, e.clientY, file.path, area, file);
			});

			$el.append($li);
		});

		self.applyFilter(area);
	};

	GitApp.prototype.renderLists = function(data) {
		this.stagedSet = new Set(data.staged.map(function(f) { return f.path; }));
		this.unstagedSet = new Set();
		const self = this;
		data.unstaged.forEach(function(f) {
			if (!self.stagedSet.has(f.path)) self.unstagedSet.add(f.path);
		});

		this.renderList($('#unstaged-list'), data.unstaged, 'unstaged');
		this.renderList($('#staged-list'), data.staged, 'staged');

		$('#branch-name').text(data.branch || '(detached)');

		this.pruneSelection();
		this.syncDiffPane();
		this.updateButtons();
	};

	// ===== File-list selection =====
	GitApp.prototype.handleRowClick = function(file, event) {
		const self = this;
		const $visibleRows = $('li[data-file]').filter(function() { return this.offsetParent !== null; });
		const targetIndex = $visibleRows.toArray().findIndex(function(li) { return li.dataset.file === file; });

		if (event.shiftKey && self.anchor && targetIndex > -1) {
			const anchorIndex = $visibleRows.toArray().findIndex(function(li) { return li.dataset.file === self.anchor; });
			const start = Math.min(anchorIndex, targetIndex);
			const end = Math.max(anchorIndex, targetIndex);
			for (let i = start; i <= end; i++) {
				const li = $visibleRows.get(i);
				self.selectedFiles.set(li.dataset.file, li.dataset.area || 'unstaged');
			}
		} else if (event.ctrlKey || event.metaKey) {
			if (self.selectedFiles.has(file)) {
				self.selectedFiles.delete(file);
			} else {
				self.selectedFiles.set(file, self.fileArea(file) || 'unstaged');
				self.anchor = file;
			}
		} else {
			self.selectedFiles.clear();
			self.selectedFiles.set(file, self.fileArea(file) || 'unstaged');
			self.anchor = file;
		}

		self.applySelectionStyles();
		self.syncDiffPane();
		self.updateButtons();
	};

	GitApp.prototype.applySelectionStyles = function() {
		const self = this;
		$('li[data-file]').each(function() {
			$(this).toggleClass('selected', self.selectedFiles.has(this.dataset.file));
		});
	};

	GitApp.prototype.pruneSelection = function() {
		const self = this;
		const available = new Set();
		$('li[data-file]').each(function() { available.add(this.dataset.file); });
		self.selectedFiles.forEach(function(_, key) {
			if (!available.has(key)) self.selectedFiles.delete(key);
		});
		if (self.anchor && !available.has(self.anchor)) self.anchor = null;
		self.lineSelections.forEach(function(_, file) {
			if (!available.has(file)) self.lineSelections.delete(file);
		});
		if (self.lineAnchor && !available.has(self.lineAnchor.file)) self.lineAnchor = null;
	};

	GitApp.prototype.updateButtons = function() {
		let nUnstagedSelected = 0;
		let nStagedSelected = 0;
		const self = this;
		self.selectedFiles.forEach(function(area) {
			if (area === 'staged') nStagedSelected++; else nUnstagedSelected++;
		});

		$('#stage-selected').prop('disabled', nUnstagedSelected === 0);
		$('#unstage-selected').prop('disabled', nStagedSelected === 0);
		$('#discard-selected').prop('disabled', nUnstagedSelected === 0);
		$('#commit-btn').prop('disabled', self.stagedSet.size === 0);
		self.updateSelectionStatus();
	};

	GitApp.prototype.updateSelectionStatus = function() {
		const $el = $('#diff-status');
		if (!$el.length) return;
		const n = this.selectedFiles.size;
		if (n === 0) $el.text('');
		else if (n === 1) $el.text('1 selected');
		else $el.text(n + ' selected');
	};

	// ===== Filter =====
	// Hide list items whose path doesn't match the per-list filter string.
	// Count displays "visible / total" while a filter is active.
	GitApp.prototype.applyFilter = function(area) {
		const filter = (area === 'unstaged' ? this.unstagedFilter : this.stagedFilter).toLowerCase();
		const $list = $('#' + area + '-list');
		const $items = $list.children('li');
		let visible = 0;
		$items.each(function() {
			const path = this.dataset.file || '';
			const match = !filter || path.toLowerCase().indexOf(filter) >= 0;
			$(this).toggleClass('filtered-out', !match);
			if (match) visible++;
		});
		const total = $items.length;
		const $count = $('#' + area + '-count');
		$count.text(filter && visible !== total ? (visible + ' / ' + total) : String(total));
	};

	GitApp.prototype.setFilter = function(area, value) {
		if (area === 'unstaged') this.unstagedFilter = value || '';
		else if (area === 'staged') this.stagedFilter = value || '';
		this.applyFilter(area);
	};

	// ===== Diff pane =====
	GitApp.prototype.syncDiffPane = function() {
		const self = this;
		const $pane = $('#diff-pane');

		if (self.selectedFiles.size === 0) {
			$pane.html('<div class="diff-empty">' + self.t('text_select_files_to_view_diff', 'Select files to view diff') + '</div>');
			return;
		}

		const selected = new Set(self.selectedFiles.keys());
		$pane.find('.diff-file').each(function() {
			if (!selected.has(this.dataset.file)) $(this).remove();
		});

		self.selectedFiles.forEach(function(area, file) {
			if ($pane.find('.diff-file[data-file="' + cssEscape(file) + '"]').length) return;
			self.loadDiffFor(file, area);
		});
	};

	GitApp.prototype.loadDiffFor = function(file, area) {
		const self = this;
		area = area || self.fileArea(file) || 'unstaged';
		const $pane = $('#diff-pane');

		const stagedTag = area === 'staged'
			? ' <span class="text-muted">(' + self.t('text_staged', 'staged') + ')</span>'
			: '';

		// Placeholder shows disabled action buttons so header layout doesn't jump while loading.
		const $actions = $('<div class="diff-file-actions"></div>')
			.append($('<button type="button" class="btn btn-default btn-sm" disabled></button>').text(self.t('text_loading', 'Loading…')));
		const $placeholder = $(
			'<div class="diff-file" data-file="' + cssEscape(file) + '">' +
				'<div class="diff-file-header">' +
					'<span class="diff-file-meta"><i class="bi bi-file-earmark-code"></i> ' + escapeHtml(file) + stagedTag + '</span>' +
					'<span class="text-muted diff-status">' + self.t('text_loading', 'Loading…') + '</span>' +
					$actions[0].outerHTML +
				'</div>' +
				'<div class="diff-file-body"><div class="diff-empty">' + self.t('text_loading_diff', 'Loading diff…') + '</div></div>' +
			'</div>'
		);
		$placeholder.appendTo($pane);

		$.get(self.baseUrl + '/diff.json', { file: file, staged: area === 'staged' ? 1 : 0 })
			.done(function(res) {
				if (res.error) {
					$placeholder.find('.diff-status').text(res.error);
					$placeholder.find('.diff-file-body').html('<div class="diff-empty">' + escapeHtml(res.error) + '</div>');
					return;
				}
				$placeholder.replaceWith(self.renderDiffBox(file, area, res.diff || ''));
			})
			.fail(function(xhr) {
				let msg = self.t('error_failed_to_load_diff', 'Failed to load diff');
				try { msg = (JSON.parse(xhr.responseText).error || msg); } catch(e) {}
				$placeholder.find('.diff-status').text(msg);
				$placeholder.find('.diff-file-body').html('<div class="diff-empty">' + escapeHtml(msg) + '</div>');
			});
	};

	GitApp.prototype.renderDiffBox = function(file, area, diffText) {
		const self = this;
		const isUntracked = (self.fileMetaFor(file) || {}).isUntracked;
		const stagedTag = area === 'staged'
			? ' <span class="text-muted">(' + self.t('text_staged', 'staged') + ')</span>'
			: '';

		const $wrapper = $('<div></div>')
			.addClass('diff-file')
			.attr('data-file', file);

		const $close = $('<button type="button" class="btn btn-default btn-sm" title="' + self.t('title_close', 'Close') + '"></button>')
			.html('<i class="bi bi-x"></i>')
			.on('click', function() {
				self.selectedFiles.delete(file);
				self.lineSelections.delete(file);
				self.applySelectionStyles();
				self.syncDiffPane();
				self.updateButtons();
			});

		// Header action buttons sit next to close (X). Untracked → Stage File only;
		// tracked + staged view → Unstage File + Unstage Hunks; tracked + unstaged view
		// → Stage File + Stage Hunks + Discard Hunks.
		const staged = area === 'staged';
		const $actions = $('<div class="diff-file-actions"></div>');
		if (isUntracked) {
			$actions.append(
				$('<button type="button" class="btn btn-primary btn-sm"></button>')
					.html('<i class="bi bi-arrow-right"></i> ' + self.t('title_stage_file', 'Stage File'))
					.on('click', function(e) {
						e.stopPropagation();
						self.stageFiles([file]);
					})
			);
		} else if (staged) {
			$actions.append(
				$('<button type="button" class="btn btn-primary btn-sm"></button>')
					.html('<i class="bi bi-arrow-left"></i> ' + self.t('title_unstage_file', 'Unstage File'))
					.on('click', function(e) {
						e.stopPropagation();
						self.unstageFiles([file]);
					}),
				$('<button type="button" class="btn btn-default btn-sm"></button>')
					.html('<i class="bi bi-arrow-left"></i> ' + self.t('title_unstage_hunks', 'Unstage Hunks'))
					.on('click', function(e) {
						e.stopPropagation();
						self.stageAllHunks(file, area);
					})
			);
		} else {
			$actions.append(
				$('<button type="button" class="btn btn-primary btn-sm"></button>')
					.html('<i class="bi bi-arrow-right"></i> ' + self.t('title_stage_file', 'Stage File'))
					.on('click', function(e) {
						e.stopPropagation();
						self.stageFiles([file]);
					}),
				$('<button type="button" class="btn btn-default btn-sm"></button>')
					.html('<i class="bi bi-arrow-right"></i> ' + self.t('title_stage_hunks', 'Stage Hunks'))
					.on('click', function(e) {
						e.stopPropagation();
						self.stageAllHunks(file, area);
					}),
				$('<button type="button" class="btn btn-default btn-sm"></button>')
					.html('<i class="bi bi-arrow-counterclockwise"></i> ' + self.t('title_discard_hunks', 'Discard Hunks'))
					.on('click', function(e) {
						e.stopPropagation();
						if (!confirm(self.t('text_confirm_discard_hunks', 'Discard all unstaged changes in this file? This cannot be undone.'))) return;
						self.discardFiles([file]);
					})
			);
		}
		$actions.append($close);

		const $header = $('<div class="diff-file-header"></div>')
			.css('cursor', 'pointer')
			.on('click', function(e) {
				if ($(e.target).closest('button').length) return;
				self.stageOrUnstage(file);
			})
			.append(
				$('<span class="diff-file-meta"></span>').html('<i class="bi bi-file-earmark-code"></i> ' + escapeHtml(file) + stagedTag),
				$('<span class="text-muted diff-status"></span>').text(''),
				$actions
			);

		const $body = $('<div class="diff-file-body"></div>');

		const hunks = parseDiff(diffText || '');
		if (!hunks.length) {
			$body.append($('<div class="diff-empty"></div>').text(self.t('text_no_diff', 'No diff content')));
		} else {
			const $table = $('<table style="width: 100%;"></table>');

			hunks.forEach(function(hunk, hunkIdx) {

				// Hunk header row showing the @@ summary.
				const $hunkTr = $('<tr class="hunk-header"></tr>')
					.append($('<td colspan="4"></td>').text(hunk.header));
				$table.append($hunkTr);

				hunk.lines.forEach(function(line, lineIdx) {
					const selectable = line.type === 'add' || line.type === 'del';

					// Two-column line numbers GitHub-style: ctx→both, add→right, del→left.
					const showOld = (typeof line.oldLine === 'number' && isFinite(line.oldLine)) ? line.oldLine : '';
					const showNew = (typeof line.newLine === 'number' && isFinite(line.newLine)) ? line.newLine : '';

					const prefix = line.type === 'add' ? '+ ' : line.type === 'del' ? '- ' : '  ';
					const ln_text = prefix + line.text;

					const $tr = $('<tr></tr>')
						.addClass(line.type)
						.attr('data-hunk', hunkIdx)
						.attr('data-line', lineIdx);

					const $checkTd = $('<td class="check"></td>');
					if (selectable) {
						const sel = self.lineSelections.get(file);
						const initiallySelected = !!(sel && sel.has(hunkIdx + ':' + lineIdx));
						const $cb = $('<input type="checkbox" />')
							.attr('data-hunk', hunkIdx)
							.attr('data-line', lineIdx)
							.attr('data-file', file)
							.prop('checked', initiallySelected);
						if (initiallySelected) $tr.addClass('selected');
						$checkTd.append($cb);
					}
					$tr.append(
						$checkTd,
						$('<td class="gut gut-old"></td>').text(showOld),
						$('<td class="gut gut-new"></td>').text(showNew),
						$('<td class="line"></td>').text(ln_text)
					);

					if (selectable) {
						$tr.css('cursor', 'pointer').on('click', function(e) {
							if ($(e.target).is('input')) return;
							self.handleLineClick(file, hunkIdx, lineIdx, e);
						});
					}

					$table.append($tr);
				});
			});

			$body.append($table);
		}

		$wrapper.append($header, $body);

		// Footer for "Stage Selected Lines" (only when there are hunks).
		if (hunks.length) {
			const $info = $('<span class="selection-info"></span>').attr('data-file', file);
			self.updateSelectionInfo(file, $info);

			const selectedLinesLabel = area === 'staged'
				? self.t('title_unstage_selected_lines', 'Unstage Selected Lines')
				: self.t('title_stage_selected_lines', 'Stage Selected Lines');
			const $stageSelectedBtn = $('<button type="button" class="btn btn-primary btn-sm"></button>')
				.attr('data-file', file)
				.prop('disabled', !self.hasLineSelection(file))
				.html('<i class="bi bi-check2-square"></i> ' + selectedLinesLabel)
				.on('click', function() { self.stageSelectedLines(file, area); });

			$wrapper.append(
				$('<div class="diff-file-footer"></div>').append($info, $stageSelectedBtn)
			);
		}

		if (self.lineAnchor && self.lineAnchor.file === file) self.lineAnchor = null;

		self.wireCheckboxHandlers($wrapper, file);

		return $wrapper;
	};

	GitApp.prototype.wireCheckboxHandlers = function($wrapper, file) {
		const self = this;
		$wrapper.find('input[type="checkbox"][data-file][data-hunk]').each(function() {
			const $cb = $(this);
			const hunkIdx = parseInt($cb.attr('data-hunk'), 10);
			const lineIdx = parseInt($cb.attr('data-line'), 10);
			$cb.on('change', function() {
				self.toggleLineSelection(file, hunkIdx, lineIdx, $cb.prop('checked'), $cb.closest('tr'));
			});
		});
	};

	// ===== Line selection =====
	GitApp.prototype.handleLineClick = function(file, hunkIdx, lineIdx, event) {
		const self = this;
		const set = self.getLineSet(file);

		if (event.shiftKey && self.lineAnchor && self.lineAnchor.file === file) {
			self.selectLineRange(file, self.lineAnchor, { hunkIdx: hunkIdx, lineIdx: lineIdx }, set);
		} else if (event.ctrlKey || event.metaKey) {
			const key = hunkIdx + ':' + lineIdx;
			if (set.has(key)) {
				set.delete(key);
			} else {
				set.set(key, true);
				self.lineAnchor = { file: file, hunkIdx: hunkIdx, lineIdx: lineIdx };
			}
		} else {
			set.clear();
			set.set(hunkIdx + ':' + lineIdx, true);
			self.lineAnchor = { file: file, hunkIdx: hunkIdx, lineIdx: lineIdx };
		}

		self.applyLineSelectionStyles(file);
		self.updateFooterFor(file);
	};

	GitApp.prototype.selectLineRange = function(file, from, to, set) {
		const $wrapper = $('.diff-file[data-file="' + cssEscape(file) + '"]');
		if (!$wrapper.length) return;

		const rows = $wrapper.find('tr[data-hunk][data-line]').filter(function() {
			return $(this).hasClass('add') || $(this).hasClass('del');
		}).toArray();

		const indexOf = function(hunkIdx, lineIdx) {
			return rows.findIndex(function(tr) {
				return parseInt(tr.dataset.hunk, 10) === hunkIdx
					&& parseInt(tr.dataset.line, 10) === lineIdx;
			});
		};

		const a = indexOf(from.hunkIdx, from.lineIdx);
		const b = indexOf(to.hunkIdx, to.lineIdx);
		if (a < 0 || b < 0) return;
		const start = Math.min(a, b);
		const end = Math.max(a, b);

		set.clear();
		for (let i = start; i <= end; i++) {
			const tr = rows[i];
			set.set(tr.dataset.hunk + ':' + tr.dataset.line, true);
		}
	};

	GitApp.prototype.applyLineSelectionStyles = function(file) {
		const $wrapper = $('.diff-file[data-file="' + cssEscape(file) + '"]');
		if (!$wrapper.length) return;
		const set = this.lineSelections.get(file) || new Map();
		$wrapper.find('tr[data-hunk][data-line]').each(function() {
			const $tr = $(this);
			const key = $tr.attr('data-hunk') + ':' + $tr.attr('data-line');
			const selected = set.has(key);
			$tr.toggleClass('selected', selected);
			const $cb = $tr.find('input[type="checkbox"]');
			if ($cb.length) $cb.prop('checked', selected);
		});
	};

	GitApp.prototype.updateFooterFor = function(file) {
		const $wrapper = $('.diff-file[data-file="' + cssEscape(file) + '"]');
		if (!$wrapper.length) return;
		const $info = $wrapper.find('.selection-info');
		if ($info.length) this.updateSelectionInfo(file, $info);
		const $btn = $wrapper.find('.diff-file-footer button');
		if ($btn.length) $btn.prop('disabled', !this.hasLineSelection(file));
	};

	GitApp.prototype.getLineSet = function(file) {
		if (!this.lineSelections.has(file)) this.lineSelections.set(file, new Map());
		return this.lineSelections.get(file);
	};

	GitApp.prototype.toggleLineSelection = function(file, hunkIdx, lineIdx, checked, $row) {
		const self = this;
		const set = self.getLineSet(file);
		const key = hunkIdx + ':' + lineIdx;
		if (checked) {
			set.set(key, true);
			$row.addClass('selected');
		} else {
			set.delete(key);
			$row.removeClass('selected');
		}
		const $wrapper = $('.diff-file[data-file="' + cssEscape(file) + '"]');
		if ($wrapper.length) {
			const $info = $wrapper.find('.selection-info');
			if ($info.length) self.updateSelectionInfo(file, $info);
			const $btn = $wrapper.find('.diff-file-footer button');
			if ($btn.length) $btn.prop('disabled', !self.hasLineSelection(file));
		}
	};

	GitApp.prototype.updateSelectionInfo = function(file, $infoEl) {
		const set = this.lineSelections.get(file);
		const n = set ? set.size : 0;
		if (n === 0) $infoEl.text(this.t('text_no_lines_selected', 'No lines selected'));
		else if (n === 1) $infoEl.text(this.t('text_one_line_selected', '1 line selected'));
		else $infoEl.text(this.t('text_n_lines_selected', '{n} lines selected').replace('{n}', n));
	};

	GitApp.prototype.hasLineSelection = function(file) {
		const set = this.lineSelections.get(file);
		return !!(set && set.size);
	};

	GitApp.prototype.buildSelectionPayload = function(file) {
		const set = this.lineSelections.get(file);
		if (!set) return [];
		const grouped = {};
		set.forEach(function(_, key) {
			const [hunkIdx, lineIdx] = key.split(':');
			if (!grouped[hunkIdx]) grouped[hunkIdx] = [];
			grouped[hunkIdx].push(parseInt(lineIdx, 10));
		});
		const out = [];
		Object.keys(grouped).forEach(function(hi) {
			out.push({ hunkIndex: parseInt(hi, 10), lines: grouped[hi].sort(function(a, b) { return a - b; }) });
		});
		return out;
	};

	// "Stage all hunks" pre-selects every +/- line and reuses the per-line staging pipeline.
	GitApp.prototype.stageAllHunks = function(file, area) {
		const self = this;
		const $box = $('.diff-file[data-file="' + cssEscape(file) + '"]');
		if (!$box.length) return;
		const set = self.getLineSet(file);
		set.clear();
		$box.find('input[type="checkbox"][data-file]').each(function() {
			const $cb = $(this);
			set.set($cb.attr('data-hunk') + ':' + $cb.attr('data-line'), true);
			$cb.prop('checked', true);
			$cb.closest('tr').addClass('selected');
		});
		self.stageSelectedLines(file, area);
	};

	GitApp.prototype.stageSelectedLines = function(file, area) {
		const self = this;
		const staged = area === 'staged';
		const hunks = self.buildSelectionPayload(file);
		if (!hunks.length) {
			self.toast(self.t('error_no_lines_selected', 'Select lines to stage first'), 'error');
			return;
		}
		$.ajax({
			url: self.baseUrl + '/stage_selections.json',
			type: 'POST',
			data: { file: file, staged: staged ? 1 : 0, hunks: hunks },
			dataType: 'json',
		})
			.done(function(res) {
				if (res.error) { self.toast(res.error, 'error'); return; }
				self.lineSelections.delete(file);
				const key = staged ? 'success_selections_unstaged' : 'success_selections_staged';
				self.toast(self.t(key, staged ? 'Selected lines unstaged' : 'Selected lines staged'), 'success');
				self.loadStatus();
			})
			.fail(function(xhr) {
				let msg = self.t(staged ? 'error_failed_to_unstage' : 'error_failed_to_stage', staged ? 'Failed to unstage files' : 'Failed to stage files');
				try { msg = (JSON.parse(xhr.responseText).error || msg); } catch(e) {}
				self.toast(msg, 'error');
			});
	};

	// ===== AJAX =====
	GitApp.prototype.loadStatus = function() {
		const self = this;
		$.get(self.baseUrl + '/status.json')
			.done(function(data) {
				if (data.error) {
					self.toast(data.error, 'error');
					return;
				}

				// Refresh the untracked-flag cache so renderDiffBox can choose the right header buttons.
				self.fileStateCache.clear();
				(data.unstaged || []).forEach(function(f) {
					if (f.x === '?' && f.y === '?') {
						self.fileStateCache.set(f.path, { isUntracked: true });
					}
				});

				self.renderLists(data);
			})
			.fail(function(xhr) {
				let msg = self.t('error_failed_to_load_status', 'Failed to load git status');
				try { msg = (JSON.parse(xhr.responseText).error || msg); } catch(e) {}
				self.toast(msg, 'error');
			});
	};

	GitApp.prototype.postJson = function(url, data) {
		return $.ajax({
			url: url,
			type: 'POST',
			data: data,
			dataType: 'json',
		});
	};

	GitApp.prototype.stageFiles = function(files) {
		const self = this;
		self.postJson(self.baseUrl + '/stage.json', { files: files })
			.done(function(res) {
				if (res.error) { self.toast(res.error, 'error'); return; }
				self.loadStatus();
			})
			.fail(function(xhr) {
				let msg = self.t('error_failed_to_stage', 'Failed to stage files');
				try { msg = (JSON.parse(xhr.responseText).error || msg); } catch(e) {}
				self.toast(msg, 'error');
			});
	};

	GitApp.prototype.unstageFiles = function(files) {
		const self = this;
		self.postJson(self.baseUrl + '/unstage.json', { files: files })
			.done(function(res) {
				if (res.error) { self.toast(res.error, 'error'); return; }
				self.loadStatus();
			})
			.fail(function(xhr) {
				let msg = self.t('error_failed_to_unstage', 'Failed to unstage files');
				try { msg = (JSON.parse(xhr.responseText).error || msg); } catch(e) {}
				self.toast(msg, 'error');
			});
	};

	GitApp.prototype.discardFiles = function(files) {
		const self = this;
		self.postJson(self.baseUrl + '/discard.json', { files: files })
			.done(function(res) {
				if (res.error) { self.toast(res.error, 'error'); return; }
				self.toast(self.t('success_changes_discarded', 'Changes discarded'), 'success');
				self.loadStatus();
			})
			.fail(function(xhr) {
				let msg = self.t('error_failed_to_discard', 'Failed to discard changes');
				try { msg = (JSON.parse(xhr.responseText).error || msg); } catch(e) {}
				self.toast(msg, 'error');
			});
	};

	GitApp.prototype.stageOrUnstage = function(file) {
		const area = this.fileArea(file);
		if (area === 'staged') this.unstageFiles([file]);
		else this.stageFiles([file]);
	};

	GitApp.prototype.commitChanges = function(message, amend) {
		const self = this;
		const payload = { message: message };
		if (amend) payload.amend = 1;
		self.postJson(self.baseUrl + '/commit.json', payload)
			.done(function(res) {
				if (res.error) { self.toast(res.error, 'error'); return; }
				$('textarea[name="commit_message"]').val('');
				self.selectedFiles.clear();
				self.toast(self.t('success_changes_committed', 'Changes committed'), 'success');
				self.loadStatus();
			})
			.fail(function(xhr) {
				let msg = self.t('error_failed_to_commit', 'Failed to commit');
				try { msg = (JSON.parse(xhr.responseText).error || msg); } catch(e) {}
				self.toast(msg, 'error');
			});
	};

	GitApp.prototype.selectedFilesInArea = function(area) {
		const out = [];
		const self = this;
		self.selectedFiles.forEach(function(a, file) {
			if (a === area) out.push(file);
		});
		return out;
	};

	// ===== Right-click context menu =====
	GitApp.prototype.closeContextMenu = function() {
		if (this.$contextMenu) {
			this.$contextMenu.remove();
			this.$contextMenu = null;
		}
	};

	GitApp.prototype.openContextMenu = function(x, y, file, area, fileMeta) {
		const self = this;

		self.closeContextMenu();

		// When the right-clicked file is part of a multi-selection, target
		// every selected file in the same area; otherwise target just this
		// file. The click handler has already swapped selection to just-
		// this-file when it wasn't previously selected.
		const targetPaths = [];
		if (self.selectedFiles.has(file)) {
			self.selectedFiles.forEach(function(a, p) {
				if (a === area) targetPaths.push(p);
			});
		}
		if (!targetPaths.length) targetPaths.push(file);
		const multi = targetPaths.length > 1;
		const n = targetPaths.length;

		const $menu = $('<div class="git-context-menu"></div>');

		const isUntracked = fileMeta.x === '?' && fileMeta.y === '?';
		const isTracked = !isUntracked;
		const staged = area === 'staged';

		const addItem = function(label, opts) {
			const $item = $('<div></div>')
				.addClass('item' + (opts.cls ? ' ' + opts.cls : ''))
				.text(label);
			if (opts.disabled) {
				$item.addClass('disabled');
			} else {
				$item.on('click', function() {
					self.closeContextMenu();
					opts.onClick();
				});
			}
			$menu.append($item);
			return $item;
		};

		const addSeparator = function() { $menu.append($('<div class="separator"></div>')); };

		if (staged) {
			const label = multi
				? self.t('title_unstage_files', 'Unstage {n} files').replace('{n}', n)
				: self.t('title_unstage_file', 'Unstage File');
			addItem(label, { onClick: function() { self.unstageFiles(targetPaths); } });
		} else {
			const label = multi
				? self.t('title_stage_files', 'Stage {n} files').replace('{n}', n)
				: self.t('title_stage_file', 'Stage File');
			addItem(label, { onClick: function() { self.stageFiles(targetPaths); } });
		}

		addSeparator();

		const discardConfirm = multi
			? self.t('text_confirm_discard', 'Discard changes to {n} files? This cannot be undone.').replace('{n}', n)
			: self.t('text_confirm_discard_one', 'Discard local changes to "{file}"? This cannot be undone.').replace('{file}', file);
		addItem(self.t('title_discard_changes', 'Discard Changes'), {
			disabled: isUntracked || staged,
			cls: 'danger',
			onClick: function() {
				if (!confirm(discardConfirm)) return;
				self.discardFiles(targetPaths);
			},
		});

		const untrackConfirm = multi
			? self.t('text_confirm_untrack_many', 'Stop tracking {n} files? They will be removed from the index but kept on disk.').replace('{n}', n)
			: self.t('text_confirm_untrack', 'Stop tracking "{file}"? The file will be removed from the index but kept on disk.').replace('{file}', file);
		addItem(self.t('title_stop_tracking', 'Stop Tracking'), {
			disabled: !isTracked,
			onClick: function() {
				if (!confirm(untrackConfirm)) return;
				self.postJson(self.baseUrl + '/untrack.json', { files: targetPaths })
					.done(function(r) {
						if (r.error) { self.toast(r.error, 'error'); return; }
						self.toast(self.t('success_stopped_tracking', 'Stopped tracking file'), 'success');
						self.loadStatus();
					})
					.fail(function(xhr) {
						let msg = self.t('error_failed_to_untrack', 'Failed to stop tracking');
						try { msg = (JSON.parse(xhr.responseText).error || msg); } catch(e) {}
						self.toast(msg, 'error');
					});
			},
		});

		addSeparator();

		const deleteConfirm = multi
			? self.t('text_confirm_delete_many', 'Delete {n} files from the working tree? This cannot be undone.').replace('{n}', n)
			: self.t('text_confirm_delete', 'Delete "{file}" from the working tree? This cannot be undone.').replace('{file}', file);
		addItem(self.t('title_delete_file', 'Delete File'), {
			cls: 'danger',
			onClick: function() {
				if (!confirm(deleteConfirm)) return;
				self.postJson(self.baseUrl + '/delete.json', { files: targetPaths })
					.done(function(r) {
						if (r.error) { self.toast(r.error, 'error'); return; }
						self.toast(self.t('success_file_deleted', 'File deleted'), 'success');
						targetPaths.forEach(function(p) {
							self.selectedFiles.delete(p);
							self.lineSelections.delete(p);
						});
						self.loadStatus();
					})
					.fail(function(xhr) {
						let msg = self.t('error_failed_to_delete', 'Failed to delete file');
						try { msg = (JSON.parse(xhr.responseText).error || msg); } catch(e) {}
						self.toast(msg, 'error');
					});
			},
		});

		$menu.appendTo('body').css({ left: '0px', top: '0px' });
		const rect = $menu[0].getBoundingClientRect();
		const maxX = window.innerWidth - rect.width - 4;
		const maxY = window.innerHeight - rect.height - 4;
		$menu.css({
			left: Math.min(x, maxX) + 'px',
			top: Math.min(y, maxY) + 'px',
		});
		self.$contextMenu = $menu;
	};

	// Document-level click/escape handlers; bound once per instance with namespaced .gitApp events.
	GitApp.prototype.wireDocument = function() {
		const self = this;
		$(document).on('click.gitApp', function() { self.closeContextMenu(); });
		$(document).on('keydown.gitApp', function(e) {
			if (e.key === 'Escape') self.closeContextMenu();
		});
		$(window).on('scroll.gitApp', function() { self.closeContextMenu(); });
	};

	// Page-level button wiring (footer buttons in the left column).
	GitApp.prototype.wireButtons = function() {
		const self = this;

		$('#refresh-btn').on('click', function() { self.loadStatus(); });

		$('#stage-selected').on('click', function() {
			const files = self.selectedFilesInArea('unstaged');
			if (files.length) self.stageFiles(files);
		});

		$('#stage-all').on('click', function() {
			const files = $('#unstaged-list li').map(function() { return $(this).attr('data-file'); }).get();
			if (files.length) self.stageFiles(files);
		});

		$('#unstage-selected').on('click', function() {
			const files = self.selectedFilesInArea('staged');
			if (files.length) self.unstageFiles(files);
		});

		$('#discard-selected').on('click', function() {
			const files = self.selectedFilesInArea('unstaged');
			if (!files.length) return;
			const msg = self.t('text_confirm_discard', 'Discard changes to {n} files? This cannot be undone.').replace('{n}', files.length);
			if (!confirm(msg)) return;
			self.discardFiles(files);
		});

		$('#commit-btn').on('click', function() {
			const $ta = $('textarea[name="commit_message"]');
			const $amend = $('input[name="amend"]');
			const msg = $ta.length ? $ta.val() : '';
			const amend = $amend.length ? $amend.prop('checked') : false;
			if (!msg.trim() && !amend) {
				self.toast(self.t('error_commit_message_required', 'A commit message is required'), 'error');
				return;
			}
			self.commitChanges(msg, amend);
		});

		$('textarea[name="commit_message"]').on('keydown', function(e) {
			if (e.ctrlKey && e.key === 'Enter') {
				$('#commit-btn').click();
			}
		});

		$('#unstaged-filter').on('input', function() {
			self.setFilter('unstaged', $(this).val());
		});

		$('#staged-filter').on('input', function() {
			self.setFilter('staged', $(this).val());
		});
	};

});


waitFor('jQuery', ($) => {
	'use strict';

	$.fn.inputCSV = function(config){

		const cfg = $.extend({
			i18n: {
				table: 'Table',
				raw: 'Raw',
				add_row: 'Add Row',
				add_column: 'Add Column',
				column_title: 'Column Title',
				remove: '<i class="icon-times" style="color: #d33;"></i>',
			},
			delimiter: 'auto',
			default_view: 'table',
		}, config);

		return this.each(function(){

			const $textarea = $(this);

			let $wrapper = $textarea.closest('.form-input-csv');

			if (!$wrapper.length) {
				$wrapper = $('<div class="form-input-csv"></div>');
				$textarea.wrap($wrapper);
			}

			const $tabs = $wrapper.find('.csv-view-tabs').length
				? $wrapper.find('.csv-view-tabs')
				: $('<div class="csv-view-tabs btn-group btn-group-sm" role="tablist"></div>').insertBefore($textarea);

			if (!$tabs.find('[data-view="table"]').length) {
				$('<button type="button" class="csv-view-tab btn btn-default btn-sm active" data-view="table">'+ cfg.i18n.table +'</button>').appendTo($tabs);
			}
			if (!$tabs.find('[data-view="raw"]').length) {
				$('<button type="button" class="csv-view-tab btn btn-default btn-sm" data-view="raw">'+ cfg.i18n.raw +'</button>').appendTo($tabs);
			}

			let $table = $wrapper.find('table.csv-table');
			if (!$table.length) {
				$table = $([
					'<table class="table csv-table" style="display: none;">',
					'	<thead><tr></tr></thead>',
					'	<tbody></tbody>',
					'	<tfoot>',
					'		<tr><td colspan="0">',
					'			<button type="button" class="add-row btn btn-default btn-sm"></button>',
					'		</td></tr>',
					'	</tfoot>',
					'</table>'
				].join('\n')).insertAfter($textarea);
				$table.find('.add-row').text(cfg.i18n.add_row);
			}

			function detectDelimiter(string){
				const lines = (string || '').split(/\r?\n/).filter(line => line.trim().length);
				if (!lines.length) return ',';

				const candidates = ['\t', '|', ';', ','];
				for (const delimiter of candidates) {
					if (lines[0].includes(delimiter)) return delimiter;
				}

				return ',';
			}

			function getDelimiter(string){
				return cfg.delimiter === 'auto' ? detectDelimiter(string) : cfg.delimiter;
			}

			// Parse textarea CSV → { columns, rows }
			function parse(string){
				const rows = [];
				const delimiter = getDelimiter(string);
				const lines = (string || '').split(/\r?\n/).filter(l => l.length);
				if (!lines.length) return { columns: [], rows: [] };

				const parseLine = (line) => {
					const out = []; let cur = ''; let inQ = false;
					for (let i = 0; i < line.length; i++){
						const c = line[i];
						if (inQ){
							if (c === '"' && line[i+1] === '"'){ cur += '"'; i++; }
							else if (c === '"'){ inQ = false; }
							else { cur += c; }
						} else {
							if (c === '"') inQ = true;
							else if (c === delimiter){ out.push(cur); cur = ''; }
							else cur += c;
						}
					}
					out.push(cur);
					return out;
				};

				const cells = lines.map(parseLine);
				const columns = cells.shift() || [];
				for (const row of cells){
					const obj = {};
					columns.forEach((col, i) => obj[col] = row[i] ?? '');
					rows.push(obj);
				}
				return { columns, rows };
			}

			// Serialize table → CSV string
			function serialize(){
				const delimiter = getDelimiter($textarea.val());
				const lines = [];
				const columnCount = $table.find('thead tr th').not('.csv-header-actions').length || 0;
				$table.find('thead tr, tbody tr').each(function(){
					const cells = $(this).find('th:not(.csv-header-actions),td:not(:last-child)').map(function(){
						let string = $(this).text();
						if (i18n.indexOf('"') !== -1 || i18n.indexOf(delimiter) !== -1 || i18n.indexOf('\n') !== -1){
							string = '"' + i18n.replace(/"/g, '""') + '"';
						}
						return string;
					}).get();
					if (cells.length || $(this).is('tbody tr')) {
						lines.push(cells.join(delimiter));
					}
				});
				if (!lines.length && columnCount) {
					lines.push(Array(columnCount).fill('').join(delimiter));
				}
				return lines.join('\n');
			}

			// Render table from CSV string
			function render(){
				const { columns, rows } = parse($textarea.val());
				const hasHeaders = columns.length > 0;
				$table.data('has-headers', hasHeaders);

				const $thead = $table.find('thead').empty().append('<tr></tr>');
				columns.forEach(col => {
					if (hasHeaders) {
						$thead.find('tr').append('<th>'+ col +'</th>');
					} else {
						$thead.find('tr').append('<th contenteditable>'+ col +'<button type="button" name="remove_column" class="btn btn-default btn-sm">'+ cfg.remove_icon +'</button></th>');
					}
				});
				if (hasHeaders) {
					$thead.find('tr').append('<th class="csv-header-actions" aria-hidden="true"></th>');
				} else {
					$thead.find('tr').append([
						'<th>',
						'	<button type="button" class="add-column btn btn-default btn-sm"></button>',
						'</th>'
					].join('\n'));
					$table.find('.add-column').text(cfg.i18n.add_column);
				}

				const $tbody = $table.find('tbody').empty();
				if (rows.length){
					rows.forEach(row => {
						const $tr = $('<tr></tr>');
						columns.forEach(col => {
							$tr.append('<td contenteditable>'+ (row[col] ?? '') +'</td>');
						});
						$tr.append([
							'<td>',
							'	<button type="button" name="remove_row" class="btn btn-default btn-sm">',
								cfg.i18n.remove,
							'	</button>',
							'</td>'
						].join('\n'));
						$tbody.append($tr);
					});
				}

				$table.find('tfoot tr td').attr('colspan', columns.length + (hasHeaders ? 1 : 1));
			}

			function setView(view){
				const nextView = view === 'raw' ? 'raw' : 'table';
				if (nextView === 'table') {
					render();
					$textarea.hide();
					$table.show();
				} else {
					$textarea.val(serialize()).trigger('change');
					$table.hide();
					$textarea.show();
				}

				$tabs.find('.csv-view-tab').removeClass('active');
				$tabs.find('[data-view="'+ nextView +'"]').addClass('active');
			}

			$tabs.on('click', '.csv-view-tab', function(e){
				e.preventDefault();
				setView($(this).data('view'));
			});

			setView(cfg.default_view || 'table');

			$table.on('click', '.add-row', function(e){
				e.preventDefault();
				const n = $table.find('thead th:not(:last-child)').length;
				if (!n){
					$table.find('thead tr').append([
						'<th contenteditable>',
						'	<button type="button" name="remove_column" class="btn btn-default btn-sm">',
							cfg.i18n.remove,
						'	</button>',
						'</th>'
					].join('\n'));
				}
				const $tr = $('<tr></tr>');
				$table.find('thead th:not(:last-child)').each(function(){
					$tr.append('<td contenteditable></td>');
				});
				$tr.append([
					'<td>',
					'	<button type="button" name="remove_row" class="btn btn-default btn-sm">',
						cfg.i18n.remove,
					'	</button>',
					'</td>'
				].join('\n'));
				$table.find('tbody').append($tr);
				$textarea.val(serialize()).trigger('change');
			});

			$table.on('click', '.add-column', function(e){
				e.preventDefault();
				if ($table.data('has-headers')) return;
				const title = prompt(cfg.i18n.column_title);

				if (!title) return;

				$table.find('thead tr th:last-child:last-child').before([
					'<th contenteditable>',
						title,
					'	<button type="button" name="remove_column" class="btn btn-default btn-sm">'+ cfg.i18n.remove +'</button>',
					'</th>'
				].join('\n'));

				$table.find('tbody tr').each(function(){
					$(this).find('td:last-child:last-child').before('<td contenteditable></td>');
				});

				const colspan = (parseInt($table.find('tfoot tr td').attr('colspan'), 10) || 0) + 1;
				$table.find('tfoot tr td').attr('colspan', colspan);
				$textarea.val(serialize()).trigger('change');
			});

			$table.on('click', 'button[name="remove_row"]', function(e){
				e.preventDefault();
				$(this).closest('tr').remove();
				$textarea.val(serialize()).trigger('change');
			});

			$table.on('click', 'button[name="remove_column"]', function(e){
				e.preventDefault();

				if ($table.data('has-headers')) return;

				const index = $(this).closest('th').index();

				$table.find('thead tr th').eq(index).remove();
				$table.find('tbody tr').each(function(){
					$(this).find('td').eq(index).remove();
				});

				const colspan = (parseInt($table.find('tfoot tr td').attr('colspan'), 10) || 0) - 1;

				$table.find('tfoot tr td').attr('colspan', colspan);
				$textarea.val(serialize()).trigger('change');
			});

			$table.on('input blur', '[contenteditable]', function(){
				$textarea.val(serialize()).trigger('change');
			});
		});
	};

});

waitFor('jQuery', $ => {

	// Keep-alive
	if (typeof _env !== 'undefined' && _env?.platform?.path) {
		setInterval(function() {
			$.get({
				url: _env.platform.path + 'ajax/keep_alive',
				cache: false
			});
		}, 60e3);
	}

});


waitFor('jQuery', ($) => {

	// AJAX Search
	let timer_ajax_search = null;
	let xhr_search = null;

	$('#search input[name="query"]').on({

		'focus': function(){
			if ($(this).val()) {
				$('#search.dropdown').addClass('open');
			}
		},

		'blur': function(){
			if (!$('#search').filter(':hover').length) {
				$('#search.dropdown').removeClass('open');
			} else {
				$('#search.dropdown').on('blur', function() {
					$('#search.dropdown').removeClass('open');
				});
			}
		},

		'input': function(){

			if (xhr_search) {
				xhr_search.abort();
			}

			let $searchField = $(this);

			if ($searchField.val()) {

				$('#search .results').html([
					'<div class="loader-wrapper text-center">',
					'  <div class="loader" style="width: 48px; height: 48px;"></div>',
					'</div>'
				].join('\n'));

				$('#search.dropdown').addClass('open');

			} else {
				$('#search .results').html('');
				$('#search.dropdown').removeClass('open');
				return;
			}

			clearTimeout(timer_ajax_search);

			timer_ajax_search = setTimeout(function() {
				xhr_search = $.ajax({
					type: 'get',
					async: true,
					cache: false,
					url: _env.backend.url + 'search_results.json?query=' + $searchField.val(),
					dataType: 'json',

					beforeSend: function(jqXHR) {
						jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
					},

					error: function(jqXHR, textStatus, errorThrown) {
						$('#search .results').text(textStatus + ': ' + errorThrown);
					},

					success: function(json) {

						$('#search .results').html('');

						if (!$('#search input[name="query"]').val()) {
							$('#search .results').html('Search');
							return;
						}

						// Defense-in-depth: only allow http(s), mailto, root-relative or scheme-less
						// URLs as result links. Today all providers build links via document::ilink()
						// (relative), so this guards against future providers leaking javascript:,
						// data: or protocol-relative (//) URLs.
						function safe_link(url) {
							if (typeof url !== 'string') return '#';
							if (url.startsWith('//')) return '#';                          // Protocol-relative — inherits page scheme
							if (/^(https?:\/\/|mailto:)/i.test(url)) return url;           // Allowed absolute schemes
							if (url.startsWith('/')) return url;                           // Root-relative
							if (!/^[a-z][a-z0-9+.\-]*:/i.test(url)) return url;            // Scheme-less = relative
							return '#';                                                    // Everything else (javascript:, data:, vbscript:, ...)
						}

						$.each(json, function(i, group) {

							if (!group.results.length) return;

							// Build group header and list via DOM APIs so untrusted fields
							// (group.name, result.title, result.description, result.link)
							// can't break out of text/attribute context.
							var $heading = $('<h3>').text(group.name);
							var $ul = $('<ul>').addClass('flex flex-rows flex-nogap').attr('data-group', group.name);

							$('#search .results').append($heading).append($ul);

							$.each(group.results, function(i, result) {

								var $a = $('<a>')
									.addClass('list-group-item')
									.attr('href', safe_link(result.link))
									.css({
										'border-inline-start': '3px solid ' + group.theme.color,
										'background': group.theme.color + '11'
									});

								$('<small>').addClass('id float-end').text('#' + result.id).appendTo($a);
								$('<div>').addClass('title').text(result.title).appendTo($a);
								$('<div>').addClass('description').append($('<small>').text(result.description)).appendTo($a);

								$('<li>').addClass('result').append($a).appendTo($ul);
							});
						});

						if ($('#search .results').html() == '') {
							$('#search .results').html('<p class="text-center no-results"><em>:(</em></p>');
						}
					},
				});
			}, 500);
		}
	});

});


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

	// Toggle docs subset when an app with a `.docs` child is clicked.
	// Without a docs subset the app link navigates to the default doc as before.
	// In compact mode icons-only the docs are hidden by CSS so we keep navigation.
	$('#sidebar-menu').on('click', '.app > a', function(e) {
		if ($('#sidebar-compact-toggle').is(':checked')) return;
		const $app = $(this).closest('.app');
		const $docs = $app.children('.docs');
		if (!$docs.length) return;
		e.preventDefault();
		$app.toggleClass('expanded');
	});

	// On page load, scroll the sidebar content so the active app is in view (vertically centered).
	$(function() {
		const $content = $('#sidebar .sidebar-content');
		if (!$content.length) return;
		const $active = $content.find('.app.active').first();
		if (!$active.length) return;
		const container = $content[0];
		const el = $active[0];
		const elTop = el.getBoundingClientRect().top - container.getBoundingClientRect().top + container.scrollTop;
		const target = elTop - container.clientHeight / 2 + el.offsetHeight / 2;
		container.scrollTop = Math.max(0, target);
	});
});

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


waitFor('jQuery', function($){

	$('button[name="font_size"]').on('click', function(){
		let new_size = parseInt($(':root').css('--default-text-size').split('px')[0]) + (($(this).val() == 'increase') ? 1 : -1);
		$(':root').css('--default-text-size', new_size + 'px');
		document.cookie = `font_size=${new_size}; Path=${_env.platform.path}; Max-Age=2592000;`;
	});

	$('input[name="theme"]').on('click', function(){
		if ($(this).val() == 'dark') {
			document.cookie = `theme=dark; Path=${_env.platform.path}; Max-Age=2592000;`;
			$('html').addClass('dark-mode');
		} else {
			document.cookie = `theme=light; Path=${_env.platform.path}; Max-Age=2592000;`;
			$('html').removeClass('dark-mode');
		}
	});

});
