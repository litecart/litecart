<?php

	if (!empty($_GET['campaign_id'])) {
		$campaign = new ent_campaign($_GET['campaign_id']);
	} else {
		$campaign = new ent_campaign();
	}

	document::$title[] = !empty($campaign->data['id']) ? t('title_edit_campaign', 'Edit Campaign') :  t('title_create_new_campaign', 'Create New Campaign');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_campaigns', 'Campaigns'), document::ilink(__APP__.'/campaigns'));
	breadcrumbs::add(!empty($campaign->data['id']) ? t('title_edit_campaign', 'Edit Campaign') :  t('title_create_new_campaign', 'Create New Campaign'), document::ilink());

	if (!$_POST) {
		$_POST = $campaign->data;
	}

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['name'])) {
				throw new Exception(t('error_must_provide_name', 'You must provide a name'));
			}

			if ($_POST['valid_from'] > $_POST['valid_to']) {
				throw new Exception(t('error_svalid_from_cannot_be_later_than_valid_to', 'The valid from cannot be later than valid to'));
			}

			if (empty($_POST['products'])) {
				$_POST['products'] = [];
			}

			// Parse scope checkboxes (format: "category:5", "brand:3")
			$_POST['scopes'] = f::array_each(array_filter($_POST['scopes'] ?? []), function($val) {
				list($type, $id) = explode(':', $val, 2);
				return ['scope_type' => $type, 'scope_id' => (int)$id];
			});

			if ($_POST['discount_mode'] == 'percentage' && empty($_POST['scopes'])) {
				throw new Exception(t('error_must_provide_scope', 'You must select at least one category or brand'));
			}

			foreach ([
				'status',
				'name',
				'discount_mode',
				'discount_percent',
				'valid_from',
				'valid_to',
				'products',
				'scopes',
			] as $field) {
				if (isset($_POST[$field])) {
					$campaign->data[$field] = $_POST[$field];
				}
			}

			$campaign->save();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/campaigns'), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($campaign->data['id'])) {
				throw new Exception(t('error_must_provide_campaign', 'You must provide a campaign'));
			}

			$campaign->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/campaigns'), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$currency_codes = array_values(
		array_unique(
			array_merge([settings::get('store_currency_code')], [currency::$selected['code']], array_keys(currency::$currencies))
		)
	);

	$categories = database::query(
		"select c.id, json_value(c.name, '$.". database::input(language::$selected['code']) ."') as name
		from ". DB_TABLE_PREFIX ."categories c
		where c.status
		order by name;"
	)->fetch_all();

	$brands = database::query(
		"select id, name from ". DB_TABLE_PREFIX ."brands
		where status
		order by name;"
	)->fetch_all();

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($campaign->data['id']) ? t('title_edit_campaign', 'Edit Campaign') : t('title_create_new_campaign', 'Create New Campaign'); ?>
		</div>
	</div>

	<?php echo f::form_begin('campaigns_form', 'post'); ?>

		<div class="card-body">
			<div style="max-width: 720px;">

				<div class="grid">
					<div class="col-md-6">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_status', 'Status'); ?></div>
							<?php echo f::form_toggle('status', 'e/d', true); ?>
						</label>
					</div>

					<div class="col-md-6">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_name', 'Name'); ?></div>
							<?php echo f::form_input_text('name', true); ?>
						</label>
					</div>
				</div>

				<div class="grid">
					<div class="col-md-6">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_valid_from', 'Valid From'); ?></div>
							<?php echo f::form_input_datetime('valid_from', true); ?>
						</label>
					</div>

					<div class="col-md-6">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_valid_to', 'Valid To'); ?></div>
							<?php echo f::form_input_datetime('valid_to', true); ?>
						</label>
					</div>
				</div>

			<div class="grid">
					<div class="col-md-12">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_discount_mode', 'Discount Mode'); ?></div>
							<?php echo f::form_toggle('discount_mode', ['fixed' => t('title_fixed_prices', 'Fixed Prices'), 'percentage' => t('title_percentage_discount', 'Percentage Discount')], true); ?>
						</label>
					</div>
				</div>

				<div id="percentage-settings" style="<?php echo ($_POST['discount_mode'] ?? 'fixed') != 'percentage' ? 'display: none;' : ''; ?>">
					<div class="grid">
						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_discount_percent', 'Discount (%)'); ?></div>
								<?php echo f::form_input_number('discount_percent', true, ['min' => '0', 'max' => '100', 'step' => '0.01']); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-md-6">
							<fieldset style="padding: 15px; border: 1px solid var(--default-border-color); border-radius: var(--border-radius);">
								<legend style="font-weight: bold; padding: 0 5px;"><?php echo t('title_categories', 'Categories'); ?></legend>
								<?php foreach ($categories as $cat) {?>
								<label class="form-group" style="margin-bottom: 0.25em;">
									<?php echo f::form_checkbox('scopes[]', 'category:'.$cat['id'], true); ?> <?php echo f::escape_html($cat['name']); ?>
								</label>
								<?php } ?>
							</fieldset>
						</div>

						<div class="col-md-6">
							<fieldset style="padding: 15px; border: 1px solid var(--default-border-color); border-radius: var(--border-radius);">
								<legend style="font-weight: bold; padding: 0 5px;"><?php echo t('title_brands', 'Brands'); ?></legend>
								<?php foreach ($brands as $brand) { ?>
								<label class="form-group" style="margin-bottom: 0.25em;">
									<?php echo f::form_checkbox('scopes[]', 'brand:'.$brand['id'], true); ?> <?php echo f::escape_html($brand['name']); ?>
								</label>
								<?php } ?>
							</fieldset>
						</div>
					</div>
				</div>

			</div>
		</div>

		<div id="fixed-price-settings" style="<?php echo ($_POST['discount_mode'] ?? 'fixed') == 'percentage' ? 'display: none;' : ''; ?>">
			<table id="campaigns" class="table data-table">
				<thead>
					<tr>
						<th class="main"><?php echo t('title_product', 'Product'); ?></th>
						<th><?php echo t('title_customer_group', 'Customer Group'); ?></th>
						<th><?php echo t('title_geo_zone', 'Geo Zone'); ?></th>
						<th class="text-center"><?php echo t('title_regular_price', 'Regular Price'); ?></th>
						<th class="text-center"><?php echo t('title_campaign_price', 'Campaign Price'); ?></th>
						<th class="text-center"><?php echo t('title_percentage', 'Percentage'); ?></th>
						<th></th>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($_POST['products'] as $key => $product) { ?>
					<tr data-product-id="<?php echo $product['product_id']; ?>" data-regular-price="<?php echo currency::format_raw($product['regular_price'], settings::get('store_currency_code')); ?>">
						<td>
							<?php echo f::form_input_hidden('products['.$key.'][product_id]', true); ?>
							<a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_product', ['product_id' => $product['product_id']]); ?>">
								<?php echo $product['name']; ?>
							</a>
						</td>
						<td><?php echo f::form_select_customer_group('products['.$key.'][customer_group_id]', true); ?></td>
						<td><?php echo f::form_select_geo_zone('products['.$key.'][geo_zone_id]', true); ?></td>
						<td class="text-end"><?php echo currency::format($product['regular_price'], false, settings::get('store_currency_code')); ?></td>
						<td>
							<div class="dropdown dropdown-end">
								<?php echo f::form_input_money('products['.$key.'][price]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), true, ['style' => 'width: 125px;']); ?>
								<ul class="dropdown-menu">
									<?php foreach (array_diff($currency_codes, [settings::get('store_currency_code')]) as $currency_code) { ?>
									<li>
										<?php echo f::form_input_money('products['.$key.'][price]['. $currency_code .']', $currency_code, true, ['style' => 'width: 125px;']); ?>
									</li>
									<?php } ?>
								</ul>
							</div>
						</td>
						<td><?php echo f::form_input_percent('products['.$key.'][percentage]', true, 2, ['style' => 'width: 100px;']); ?></td>
						<td class="text-end">
							<button class="btn btn-danger btn-sm" name="remove" type="button" title="<?php echo t('title_edit', 'Edit'); ?>">
								<?php echo f::draw_fonticon('remove'); ?>
							</button>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>

			<div class="card-body">
				<a href="<?php echo document::href_ilink(__APP__.'/product_picker'); ?>" class="btn btn-default" data-toggle="lightbox" data-max-width="800px" data-callback="add_product">
					<?php echo f::draw_fonticon('add', 'style="margin-inline-end: .5em;"'); ?> <?php echo t('title_add_product', 'Add Product'); ?>
				</a>
			</div>
		</div>

		<div class="card-action">
			<?php echo f::form_button_predefined('save'); ?>
			<?php if (!empty($campaign->data['id'])) echo f::form_button_predefined('delete'); ?>
			<?php echo f::form_button_predefined('cancel'); ?>
		</div>

	<?php echo f::form_end(); ?>
</div>

<script>
	const store_currency_code = '<?php echo settings::get('store_currency_code'); ?>';
	const currencies = <?php echo f::format_json(currency::$currencies); ?>;
	const currency_codes = <?php echo f::format_json($currency_codes); ?>;

	$('#campaigns').on('focus', 'input[name^="campaigns"]', function(e) {
		if ($(this).attr('name').match(/\[[A-Z]{3}\]$/)) {
			$(this).closest('.dropdown').addClass('open');
		}
	});

	$('#campaigns').on('blur', '.dropdown', function(e) {
		$(this).removeClass('open');
	});

	$('#campaigns').on('input', 'input[name$="['+store_currency_code+']"]', function() {

		let $row = $(this).closest('tr'),
			regular_price = Number($row.data('regular-price')),
			campaign_price = Number($(this).val()),
			percentage = Number((regular_price - $(this).val()) / regular_price * 100).toFixed(2);

		$('input[name$="[percentage]"]', $row).val(percentage);

		$.each(currencies, function(i, currency) {
			if (currency.value !== undefined && currency.decimals !== undefined) {
				amount = Number(regular_price / currency.value).toFixed(currency.decimals);

				$('input[name$="['+currency.code+']"]', $row).attr('placeholder', amount);

				if (!$('input[name$="['+currency.code+']"]', $row).val()) {
					$('input[name$="['+currency.code+']"]', $row).val('');
				}
			}
		});
	});

	$('#campaigns').on('input', 'input[name$="[percentage]"]', function() {
		let $row = $(this).closest('tr'),
			amount = 0;

		$.each(currencies, function(i, currency) {

			if ($('input[name$="['+currency.code+']"]').val() > 0) {
				amount = Number($('input[name$="['+store_currency_code+']"]').val() * (100 - $('input[name$="['+currency.code+']"]').val()) / 100).toFixed(currency.decimals);
				$('input[name$="['+currency.code+']"]', $row).val(amount);
			} else {
				$('input[name$="['+currency.code+']"]', $row).val('');
			}

			amount = Number($('input[name$="['+store_currency_code+']"]', $row).val() / currency.value).toFixed(currency.decimals);

			$('input[name$="['+currency.code+']"]', $row).attr('placeholder', amount);
		});
	});

	$('input[name$="['+store_currency_code+']"]').trigger('input');

	$('#campaigns').on('click', 'button[name="remove"]', function(e) {
		e.preventDefault();
		if (confirm('<?php echo t('text_are_you_sure', 'Are you sure?'); ?>')) {
			$(this).closest('tr').remove();
		}
	});

	$('input[name="discount_mode"]').on('change', function() {
		if ($(this).val() == 'percentage') {
			$('#percentage-settings').show();
			$('#fixed-price-settings').hide();
		} else {
			$('#percentage-settings').hide();
			$('#fixed-price-settings').show();
		}
	});

	window.add_product = function(product) {

		var __index__ = 0;
		while ($('input[name^="products[new_product_new_'+__index__+']"]').length) __index__++;

		$output = $([
			'<tr>',
			'  <td>',
			'    <?php echo f::form_input_hidden('products[__index__][product_id]', 'product.id'); ?>',
			'    <a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_product', ['product_id' => 'product.id']); ?>">',
			'      ' + product.name,
			'		 </a>',
			'  </td>',
			'  <td><?php echo f::escape_js(f::form_select_customer_group('products[__index__][customer_group_id]', '')); ?></td>',
			'  <td><?php echo f::escape_js(f::form_select_geo_zone('products[__index__][geo_zone_id]', '')); ?></td>',
			'  <td class="text-end">'+ Number(product.price.value).toMoney() +'</td>',
			'  <td>',
			'    <div class="dropdown dropdown-end">',
			'      <?php echo f::escape_js(f::form_input_money('products[__index__][price]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), '', ['style' => 'width: 125px;'])); ?>',
			'      <ul class="dropdown-menu">',
			'        <?php foreach (array_diff($currency_codes, [settings::get('store_currency_code')]) as $currency_code) { ?>',
			'        <li>',
			'          <?php echo f::escape_js(f::form_input_money('products[__index__][price]['. $currency_code .']', $currency_code, '', ['style' => 'width: 125px;'])); ?>',
			'        </li>',
			'        <?php } ?>',
			'      </ul>',
			'    </div>',
			'  </td>',
			'  <td><?php echo f::escape_js(f::form_input_percent('products[__index__][percentage]', '0.00', 2)); ?></td>',
			'  <td class="text-end">',
			'    <button class="btn btn-danger btn-sm" name="remove" type="button" title="<?php echo t('title_edit', 'Edit'); ?>">',
			'      <?php echo f::draw_fonticon('remove'); ?>',
			'    </button>',
			'  </td>',
			'</tr>',
		].join('\n')
			.replace(/__index__/g, 'new_' + __index__)
			.replace('product.id', product.id)
		);

		$output.data({
			'product-id': product.id,
			'regular-price': product.price.value
		});

		$('#campaigns tbody').append($output);
		$.litebox.close();
	};
</script>