/*
	* jQuery Category Picker
	* by LiteCart
	*/
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
