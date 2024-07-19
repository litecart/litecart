/*
 * jQuery Category Picker
 * by LiteCart
 */

+function($) {

	$.fn.categoryPicker = function(config){
		this.each(function(){

			this.xhr = null;
			this.config = config;

			self = this;

			$(this).find('.dropdown input[type="search"]').on({

				'focus': function(e){
					$(self).find('.dropdown').addClass('open');
				},

				'input': function(e){
						let dropdownMenu = $(self).find('.dropdown-menu');

						$(dropdownMenu).html('');

						if (self.xhr) self.xhr.abort();

						if ($(this).val() == '') {

							$.getJSON(self.config.link, function(result) {

								$(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>');

								$.each(result.subcategories, function(i, category) {
									$(dropdownMenu).append(
										'<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
										'  ' + self.config.icons.folder +
										'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
										'  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
										'</li>'
									);
								});
							});

							return;
						}

						self.xhr = $.ajax({
							type: 'get',
							async: true,
							cache: true,
							url: self.config.link + '&query=' + $(this).val(),
							dataType: 'json',

							beforeSend: function(jqXHR) {
								jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
							},

							error: function(jqXHR, textStatus, errorThrown) {
								if (errorThrown == 'abort') return;
								alert(errorThrown);
							},

							success: function(result) {

								if (!result.subcategories.length) {
									$(dropdownMenu).html('<li class="dropdown-item text-center no-results"><em>:(</em></li>');
									return;
								}

								$(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ self.config.translations.search_results +'</h3></li>');

								$.each(result.subcategories, function(i, category) {
									$(dropdownMenu).append(
										'<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
										'  ' + self.config.icons.folder +
										'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
										'  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
										'</li>'
									);
								});
							},
						});
					}
			});

			$(this).on('click', '.dropdown-menu .dropdown-item a', function(e){
				e.preventDefault();

				let dropdownMenu = $(this).closest('.dropdown-menu');

				$.getJSON($(this).data('link'), function(result) {

					$(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>');

					if (result.id) {
						$(dropdownMenu).append(
							'<li class="dropdown-item" data-id="'+ result.parent.id +'" data-name="'+ result.parent.name +'" style="display: flex; align-items: center;">' +
							'  ' + self.config.icons.back +
							'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ result.parent.id +'" style="flex-grow: 1;">'+ result.parent.name +'</a>' +
							'</li>'
						);
					}

					$.each(result.subcategories, function(i, category) {
						$(dropdownMenu).append(
							'<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
							'  ' + self.config.icons.folder +
							'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
							'  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
							'</li>'
						);
					});
				});
			});

			$(this).on('click', '.dropdown-menu .dropdown-item button.add', function(e){
				e.preventDefault();

				let category = $(this).closest('li'),
						abort = false;

				$(self).find('input[name="'+ self.config.inputName +'"]').each(function(){
					if ($(this).val() == category.data('id')) {
						abort = true;
						return;
					}
				});

				if (abort) return;

				$(self).find('.categories').append(
					'<li class="dropdown-item" style="display: flex; align-items: center;">' +
					'  <input type="hidden" name="'+ self.config.inputName +'" value="'+ $(category).data('id') +'" data-name="'+ $(category).data('name').replace(/"/, '&quote;') +'" />' +
					'  <div style="flex-grow: 1;">' + self.config.icons.folder +' '+ $(category).data('name') +'</div>' +
					'  <div><button class="remove btn btn-default btn-sm" type="button">'+ self.config.translations.remove +'</button></div>' +
					'</li>'
				);

				$(self).trigger('change');

				$('.dropdown.open').removeClass('open');

				return false;
			});

			$(this).find('.categories').on('click', '.remove', function(e){
				$(this).closest('li').remove();
				$(self).trigger('change');
			});

			$('body').on('mousedown', function(e){
				if ($('.dropdown.open').has(e.target).length === 0) {
					$('.dropdown.open').removeClass('open');
				}
			});

			$(this).find('input[type="search"]').trigger('input');

		});
	}

}(jQuery);
