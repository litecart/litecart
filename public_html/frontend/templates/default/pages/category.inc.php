<style>
#filter-tokens {
	display: block;
	margin-bottom: 1em;
}
#filter-tokens:empty {
	display: none;
}
#filter-tokens .token {
	padding: .5em 1em;
	border-radius: 4px;
	cursor: default;
	margin-inline-end: .5em;
	white-space: nowrap;
}
#filter-tokens .token .remove {
	padding-inline-start: .5em;
	color: inherit;
	font-weight: 600;
}

#filter-tokens .token[data-group="name"] {
	background: #cbe2b6;
}
#filter-tokens .token[data-group="brand"] {
	background: #b6c2e2;
}
#filter-tokens .token[data-group^="attribute"] {
	background: #e2c6b6;
}
#filter-tokens .token[data-group="price-range"] {
	background:rgb(182, 216, 226);
}
</style>

<main id="main" class="container">
	{{breadcrumbs}}
	{{notices}}

	<div class="grid">
		<div class="col-md-3">
			<div id="sidebar" style="margin-bottom: 2em;">

				<nav class="pills" style="margin-bottom: 2em;">
					<a class="pill-item" href="<?php echo document::href_ilink(''); ?>">
						<?php echo functions::draw_fonticon('icon-chevron-left'); ?> <?php echo t('title_back', 'Back'); ?>
					</a>
				</nav>

				<div class="card">
					<div class="card-header">
						<h1 class="card-title"><?php echo $main_category['name']; ?></h1>
					</div>

					<div class="card-body">

						<?php include 'app://frontend/partials/box_category_tree.inc.php'; ?>

						<section id="box-category-filter">
							<?php echo functions::form_begin('filter_form', 'get'); ?>

									<label class="form-group">
										<div class="form-label"><?php echo t('title_product_name', 'Product Name'); ?></div>
										<?php echo functions::form_input_search('product_name', true, 'autocomplete="off" data-token-group="name" data-token-title="'. t('title_name', 'Name') .'" placeholder="'. functions::escape_attr(t('text_filter_by_product_name', 'Filter by product name')) .'"'); ?>
									</label>

									<?php if ($brands) { ?>
									<div class="form-group">
										<div class="form-label"><?php echo t('title_brands', 'Brands'); ?></div>
										<div class="dropdown">
											<div class="form-select" data-toggle="dropdown">
												<?php echo t('title_brands', 'Brands'); ?>
											</div>
											<ul class="dropdown-content">
												<?php foreach ($brands as $brand) { ?>
												<li><?php echo functions::form_checkbox('brands[]', [$brand['id'], $brand['name']], true, 'data-token-group="brand" data-token-title="'. t('title_brand', 'Brand') .'" data-token-value="'. $brand['name'] .'"'); ?></li>
												<?php } ?>
											</ul>
										</div>
									</div>
									<?php } ?>

									<?php foreach ($attributes as $attribute) { ?>
									<div class="form-group">
										<div class="form-label"><?php echo t('title_product_attributes', 'Product Attributes'); ?></div>
										<div class="dropdown">
											<div class="form-select" data-toggle="dropdown">
												<?php echo $attribute['name']; ?>
											</div>
											<ul class="dropdown-content">
												<?php foreach ($attribute['values'] as $value) { ?>
												<li><?php echo !empty($attribute['select_multiple']) ? functions::form_checkbox('attributes['. $attribute['id'] .'][]', [$value['id'], $value['value']], true, 'data-token-group="attribute-'. $attribute['id'] .'" data-token-title="'. functions::escape_attr($attribute['name']) .'" data-token-value="'. functions::escape_attr($value['value']) .'"') : functions::form_radio_button('attributes['. $group['id'] .'][]', [$value['id'], $value['value']], true, 'data-token-group="attribute-'. $attribute['id'] .'" data-token-title="'. functions::escape_attr($attribute['name']) .'" data-token-value="'. functions::escape_attr($value['value']) .'"'); ?></li>
												<?php } ?>
											</ul>
										</div>
									</div>
									<?php } ?>

									<div class="form-group">
										<div class="form-label"><?php echo t('title_price_range', 'Price Range'); ?></div>
										<div class="input-group">
											<?php echo functions::form_input_number('price_range[min]', true, 'placeholder="'. t('title_min', 'Min') .'"'); ?>
											<span class="input-group-text"> &ndash; </span>
											<?php echo functions::form_input_number('price_range[max]', true, 'placeholder="'. t('title_max', 'Max') .'"'); ?>
										</div>
									</div>

							<?php echo functions::form_end(); ?>
						</section>

					</div>
				</div>

				<?php include 'app://frontend/partials/box_recently_viewed_products.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<div id="content">

				<?php if ($description) { ?>
				<article id="box-category-description" class="card">
					<div class="card-header">
						<h1 class="card-title"><?php echo $h1_title; ?></h1>
					</div>

					<div class="card-body">
						<div class="flex">

							<div class="description flex-grow" style="flex: 1 1 auto;">

								<?php if ($short_description) { ?>
								<p class="short-description text-medium" style="margin-top: 0;">
									<?php echo $short_description; ?>
								</p>
								<?php } ?>

								<div class="description">
									{{description}}
								</div>
							</div>

							<?php if ($image) { ?>
							<div style="flex: 0 0 320px;">
								<?php echo functions::draw_thumbnail($image, 480, 0, 'category'); ?>
							</div>
							<?php } ?>

						</div>
					</div>
				</article>
				<?php } ?>

				<article id="box-category" class="card">
					<div class="card-header hidden-xs">
						<div class="grid">

							<div class="col-6">
								<h2 class="card-title"><?php echo $h1_title; ?></h2>
							</div>

							<div class="col-6 text-end" style="display: flex; gap: 1em; justify-content: end;">

								<div class="dropdown" style="display: inline-block; margin-inline-start: 1em;">
									<div class="form-select" data-toggle="dropdown">
										<?php echo t('title_sort_by', 'Sort By'); ?>
									</div>

									<ul class="dropdown-content">
										<?php foreach ($sort_alternatives as $key => $title) { ?>
										<li><?php echo functions::form_radio_button('sort', [$key, $title], true); ?></li>
										<?php } ?>
									</ul>
								</div>

								<div style="display: inline-block;">
									<?php echo functions::form_toggle('list_style', ['columns' => functions::draw_fonticon('icon-th-large'), 'rows' => functions::draw_fonticon('icon-bars')], true, 'data-token-group="list_style" data-token-title="'. t('title_list_style', 'List Style') .'"'); ?>
								</div>
							</div>
						</div>
					</div>

					<div class="card-body">

						<?php if ($products) { ?>
						<div id="filter-tokens"></div>

<?php /*
						<nav class="pills hidden-xs" style="margin-bottom: 1em;">
							<a class="pill-item" href="<?php echo !empty($parent_id) ? document::href_ilink('category', ['category_id' => $parent_id]) : document::href_ilink(''); ?>"><?php echo functions::draw_fonticon('icon-chevron-left'); ?> <?php echo t('title_back', 'Back'); ?></a>
							<?php foreach ($subcategories as $subcategory) { ?><a class="nav-item" href="<?php echo document::href_ilink('category', ['category_id' => $subcategory['id']]); ?>"><?php echo $subcategory['name']; ?></a><?php } ?>
						</nav>
*/ ?>

						<section class="listing products <?php echo (isset($_GET['list_style']) && $_GET['list_style'] == 'rows') ? 'rows' : 'columns'; ?>">
							<?php foreach ($products as $product) echo functions::draw_listing_product($product, ['category_id']); ?>
						</section>

						<?php } ?>
					</div>

					<?php if ($pagination) { ?>
					<div class="card-footer">
						{{pagination}}
					</div>
					<?php } ?>
				</article>
			</div>
		</div>
	</div>
</main>

<script>
	$('form[name="filter_form"] :input').on('input', function() {
		$('#filter-tokens').html('');

		$.each($('#box-category-filter input[data-token-title][type="search"]'), function(i,el) {
			if (!$(this).val()) return;
			$('#filter-tokens').append('<span class="token" data-group="'+ $(el).data('token-group') +'" data-name="'+ $(el).attr('name') +'" data-value="'+ $(el).val() +'">'+ $(el).data('token-title') +': '+ $(el).val() +'<a href="#" class="remove">×</a></span>');
		});

		$.each($('#box-category-filter input[data-token-title]:checkbox:checked, #box-category-filter input[data-token-title][type="radio"]:checked'), function(i,el) {
			if (!$(this).val()) return;
			$('#filter-tokens').append('<span class="token" data-group="'+ $(el).data('token-group') +'" data-name="'+ $(el).attr('name') +'" data-value="'+ $(el).val() +'">'+ $(el).data('token-title') +': '+ $(el).data('token-value') +'<a href="#" class="remove">×</a></span>');
		});

		if ($('input[name="price_range[min]"]').val() || $('input[name="price_range[max]"]').val()) {
			$('#filter-tokens').append([
				'<span class="token" data-group="price-range" data-name="price_range[min]" data-value="'+ $('input[name="price_range[min]"]').val() +'">',
				'<?php echo functions::escape_js(t('title_price_range', 'Price Range')) ; ?>: '+ $('input[name="price_range[min]"]').val() +' &ndash; '+ $('input[name="price_range[max]"]').val(),
				'<a href="#" class="remove">×</a>',
				'</span>'
			].join('\n'));
		}

	}).first().trigger('change');

	let xhr_filter = null;
	$('form[name="filter_form"]').on('input', function() {

		if (xhr_filter) {
			xhr_filter.abort();
		}

		let url = new URL(location.protocol + '//' + location.host + location.pathname + '?' + $('form[name="filter_form"]').serialize());

		history.replaceState(null, null, url);

		$('section.listing.products').hide();

		xhr_filter = $.ajax({
			type: 'get',
			url: url.href,
			dataType: 'html',
			success: function(response) {
				let html = $('section.listing.products', response)[0].outerHTML;
				$('section.listing.products').replaceWith(html).fadeIn('fast');
			}
		});
	});

	$('#box-category').on('click', '#filter-tokens .remove', function(e) {
		e.preventDefault();

		let $token = $(this).closest('.token');

		switch ($(':input[name="'+ $token.data('name') +'"]').attr('type')) {

			case 'radio':
			case 'checkbox':
				$(':input[name="'+ $token.data('name') +'"][value="'+ $token.data('value') +'"]').prop('checked', false).trigger('input');
				break;

			case 'text':
			case 'search':
				$(':input[name="'+ $token.data('name') +'"]').val('').trigger('input');
				break;
		}

		if ($token.data('group') == 'price-range') {
			$(':input[name="price_range[min]"]').val('').trigger('input');
			$(':input[name="price_range[max]"]').val('').trigger('input');
		}
	});
</script>