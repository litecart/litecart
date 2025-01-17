<style>
#box-category-filter .token {
	padding: .5em 1em;
	border-radius: 4px;
	cursor: default;
	margin-inline-end: .5em;
}
#box-category-filter .token .remove {
	padding-inline-start: .5em;
	color: inherit;
	font-weight: 600;
}

#box-category-filter .token[data-group="name"] {
	background: #cbe2b6;
}
#box-category-filter .token[data-group="brand"] {
	background: #b6c2e2;
}
#box-category-filter .token[data-group^="attribute"] {
	background: #e2c6b6;
}
</style>

<section id="box-category-filter">
	<?php echo functions::form_begin('filter_form', 'get'); ?>

			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_product_name', 'Product Name'); ?></div>
				<?php echo functions::form_input_search('product_name', true, 'autocomplete="off" data-token-group="name" data-token-title="'. language::translate('title_name', 'Name') .'" placeholder="'. functions::escape_attr(language::translate('text_filter_by_product_name', 'Filter by product name')) .'"'); ?>
			</label>

			<?php if ($brands) { ?>
			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_brands', 'Brands'); ?></div>
				<div class="dropdown">
					<div class="form-select" data-toggle="dropdown">
						<?php echo language::translate('title_brands', 'Brands'); ?>
					</div>
					<ul class="dropdown-content">
						<?php foreach ($brands as $brand) { ?>
						<li><?php echo functions::form_checkbox('brands[]', [$brand['id'], $brand['name']], true, 'data-token-group="brand" data-token-title="'. language::translate('title_brand', 'Brand') .'" data-token-value="'. $brand['name'] .'"'); ?></li>
						<?php } ?>
					</ul>
				</div>
			</label>
			<?php } ?>

			<?php foreach ($attributes as $attribute) { ?>
			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_product_attributes', 'Product Attributes'); ?></div>
				<div class="dropdown">
					<div class="form-select" data-toggle="dropdown">
						<?php echo $attribute['name']; ?>
					</div>
					<ul class="dropdown-content">
						<?php foreach ($attribute['values'] as $value) { ?>
						<li><?php echo !empty($attribute['select_multiple']) ? functions::form_checkbox('attributes['. $attribute['id'] .'][]', [$value['id'], $value['value']], true, 'data-token-group="attribute-'. $attribute['id'] .'" data-token-title="'. functions::escape_attr($group['name']) .'" data-token-value="'. functions::escape_attr($value['value']) .'"') : functions::form_radio_button('attributes['. $group['id'] .'][]', [$value['id'], $value['value']], true, 'data-token-group="attribute-'. $attribute['id'] .'" data-token-title="'. functions::escape_attr($attribute['name']) .'" data-token-value="'. functions::escape_attr($value['value']) .'"'); ?></li>
						<?php } ?>
					</ul>
				</div>
			</label>
			<?php } ?>

			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_price_range', 'Price Range'); ?></div>
				<div class="input-group">
					<?php echo functions::form_input_number('price[min]', true, 'placeholder="'. language::translate('title_min', 'Min') .'"'); ?>
					<span class="input-group-text"> &ndash; </span>
					<?php echo functions::form_input_number('price[max]', true, 'placeholder="'. language::translate('title_max', 'Max') .'"'); ?>
				</div>
			</label>



		<div class="tokens"></div>

	<?php echo functions::form_end(); ?>
</section>

<script>
	$('#box-category-filter form[name="filter_form"] :input').on('input', function() {
		$('#box-category-filter .tokens').html('')

		$.each($('#box-category-filter input[data-token-title][type="search"]'), function(i,el) {
			if (!$(this).val()) return
			$('#box-category-filter .tokens').append('<span class="token" data-group="'+ $(el).data('token-group') +'" data-name="'+ $(el).attr('name') +'" data-value="'+ $(el).val() +'">'+ $(el).data('token-title') +': '+ $(el).val() +'<a href="#" class="remove">×</a></span>')
		})

		$.each($('#box-category-filter input[data-token-title]:checkbox:checked, #box-category-filter input[data-token-title][type="radio"]:checked'), function(i,el) {
			if (!$(this).val()) return
			$('#box-category-filter .tokens').append('<span class="token" data-group="'+ $(el).data('token-group') +'" data-name="'+ $(el).attr('name') +'" data-value="'+ $(el).val() +'">'+ $(el).data('token-title') +': '+ $(el).data('token-value') +'<a href="#" class="remove">×</a></span>')
		})

	}).first().trigger('change')

	let xhr_filter = null
	$('#box-category-filter form[name="filter_form"]').on('input', function() {
		if (xhr_filter) xhr_filter.abort()
		let url = new URL(location.protocol + '//' + location.host + location.pathname + '?' + $('form[name="filter_form"]').serialize())
		history.replaceState(null, null, url)
		$('section.listing.products').hide()
		xhr_filter = $.ajax({
			type: 'get',
			url: url.href,
			dataType: 'html',
			success: function(response) {
				let html = $('section.listing.products', response)[0].outerHTML
				$('section.listing.products').replaceWith(html).fadeIn('fast')
			}
		})
	})

	$('#box-category-filter form[name="filter_form"] .tokens').on('click', '.remove', function() {
		let token = $(this).closest('.token')
		switch ($(':input[name="'+ $(token).data('name') +'"]').attr('type')) {
			case 'radio':
			case 'checkbox':
				$(':input[name="'+ $(token).data('name') +'"][value="'+ $(token).data('value') +'"]').prop('checked', false).trigger('input')
				break
			case 'text':
			case 'search':
				$(':input[name="'+ $(token).data('name') +'"]').val('').trigger('input')
				break
		}
	})
</script>