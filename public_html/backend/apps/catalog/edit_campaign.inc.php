<?php

	if (!empty($_GET['campaign_id'])) {
		$campaign = new ent_campaign($_GET['campaign_id']);
	} else {
		$campaign = new ent_campaign();
	}

	if (!$_POST) {
		$_POST = $campaign->data;
	}

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['name'])) {
				throw new Exception(language::translate('error_must_provide_name', 'You must provide a name'));
			}

			if ($_POST['date_valid_from'] > $_POST['date_valid_to']) {
				throw new Exception(language::translate('error_svalid_from_cannot_be_later_than_valid_to', 'The valid from cannot be later than valid to'));
			}

			if (empty($_POST['products'])) {
				$_POST['products'] = [];
			}

			foreach ([
				'status',
				'name',
				'date_valid_from',
				'date_valid_to',
				'products',
			] as $field) {
				if (isset($_POST[$field])) {
					$campaign->data[$field] = $_POST[$field];
				}
			}

			$campaign->save();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/campaigns'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($campaign->data['id'])) {
				throw new Exception(language::translate('error_must_provide_campaign', 'You must provide a campaign'));
			}

			$campaign->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/campaigns'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$currency_codes = array_unique(array_merge([currency::$selected['code']], [settings::get('store_currency_code')], array_keys(currency::$currencies)));

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($campaign->data['id']) ? language::translate('title_edit_campaign', 'Edit Campaign') : language::translate('title_create_new_campaign', 'Create New Campaign'); ?>
		</div>
	</div>

	<?php echo functions::form_begin('campaigns_form', 'post', '', false, 'data-track-changes'); ?>

		<div class="card-body">
			<div style="max-width: 720px;">

				<div class="grid">
					<div class="col-md-6">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_status', 'Status'); ?></div>
							<?php echo functions::form_toggle('status', 'e/d', true); ?>
						</label>
					</div>

					<div class="col-md-6">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
							<?php echo functions::form_input_text('name', true); ?>
						</label>
					</div>
				</div>

				<div class="grid">
					<div class="col-md-6">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_valid_from', 'Valid From'); ?></div>
							<?php echo functions::form_input_datetime('date_valid_from', true); ?>
						</label>
					</div>

					<div class="col-md-6">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_valid_to', 'Valid To'); ?></div>
							<?php echo functions::form_input_datetime('date_valid_to', true); ?>
						</label>
					</div>
				</div>

			</div>
		</div>

		<table id="campaigns" class="table data-table">
			<thead>
				<tr>
					<th class="main"><?php echo language::translate('title_product', 'Product'); ?></th>
					<th class="text-center"><?php echo language::translate('title_regular_price', 'Regular Price'); ?></th>
					<th class="text-center"><?php echo language::translate('title_campaign_price', 'Campaign Price'); ?></th>
					<th class="text-center"><?php echo language::translate('title_percentage', 'Percentage'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($_POST['products'] as $key => $product) { ?>
				<tr>
					<td>
						<?php echo functions::form_input_hidden('products['.$key.'][product_id]', true); ?>
						<a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_product', ['product_id' => $product['product_id']]); ?>">
							<?php echo $product['name']; ?>
						</a>
					</td>
					<td class="text-end"><?php echo currency::format($product['regular_price'], false, settings::get('store_currency_code')); ?></td>
					<td>
						<div class="dropdown dropdown-end">
							<?php echo functions::form_input_money('products['.$key.'][price]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), true, 'style="width: 125px;"'); ?>
							<ul class="dropdown-menu">
								<?php foreach (array_diff($currency_codes, [settings::get('store_currency_code')]) as $currency_code) { ?>
								<li>
									<?php echo functions::form_input_money('products['.$key.'][price]['. $currency_code .']', $currency_code, true, 'style="width: 125px;"'); ?>
								</li>
								<?php } ?>
							</ul>
						</div>
					</td>
					<td><?php echo functions::form_input_percent('products['.$key.'][percentage]', true, 2, 'style="width: 100px;"'); ?></td>
					<td class="text-end">
						<button class="btn btn-danger btn-sm" name="remove" type="button" title="<?php echo language::translate('title_edit', 'Edit'); ?>">
							<?php echo functions::draw_fonticon('icon-times'); ?>
						</button>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>

		<div class="card-body">
			<a href="<?php echo document::href_ilink(__APP__.'/product_picker'); ?>" class="btn btn-default" data-toggle="lightbox" data-max-width="800px" data-callback="add_product">
				<?php echo functions::draw_fonticon('icon-plus', 'style="margin-inline-end: .5em;"'); ?> <?php echo language::translate('title_add_product', 'Add Product'); ?>
			</a>
		</div>

		<div class="card-action">
			<?php echo functions::form_button_predefined('save'); ?>
			<?php if (!empty($campaign->data['id'])) echo functions::form_button_predefined('delete'); ?>
			<?php echo functions::form_button_predefined('cancel'); ?>
		</div>

	<?php echo functions::form_end(); ?>
</div>

<script>
	const store_currency_code = '<?php echo settings::get('store_currency_code'); ?>';
	const currencies = <?php echo json_encode(currency::$currencies, JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE); ?>;
	const currency_codes = <?php echo json_encode($currency_codes); ?>;

	$('#campaigns').on('focus', 'input[name^="campaigns"]', function(e) {
		if ($(this).attr('name').match(/\[[A-Z]{3}\]$/)) {
			$(this).closest('.dropdown').addClass('open');
		}
	});

	$('#campaigns').on('blur', '.dropdown', function(e) {
		$(this).removeClass('open');
	});

	$('#campaigns').on('input', 'input[name$="[percentage]"]', function() {
		let $row = $(this).closest('tr'),
			amount = 0;

		$.each(currencies, function(i, currency) {

			if ($('input[name$="['+currency.code+']"]').val() > 0) {
				amount = Number($('input[name$="['+store_currency_code+']"]').val() * (100 - $(this).val()) / 100).toFixed(currency.decimals);
				$row.find('input[name$="['+currency.code+']"]').val(amount);
			} else {
				$row.find('input[name$="['+currency.code+']"]').val('');
			}

			amount = Number($row.find('input[name$="['+store_currency_code+']"]').val() / currency.value).toFixed(currency.decimals);

			$row.find('input[name$="['+currency.code+']"]').attr('placeholder', amount);
		});
	});

	$('#campaigns').on('input', 'input[name$="['+store_currency_code+']"]', function() {

		let $row = $(this).closest('tr'),
			percentage = Number(($('input[name$="['+store_currency_code+']"]').val() - $(this).val()) / $('input[name$="['+store_currency_code+']"]').val() * 100).toFixed(2);

		$row.find('input[name$="[percentage]"]').val(percentage);

		$.each(currencies, function(i, currency) {

			amount = Number($row.find('input[name$="['+store_currency_code+']"]').val() / currency.value).toFixed(currency.decimals);

			$row.find('input[name$="['+currency.code+']"]').attr('placeholder', amount);

			if (!$row.find('input[name$="['+currency.code+']"]').val()) {
				$row.find('input[name$="['+currency.code+']"]').val('');
			}
		});
	});

	$('input[name$="['+store_currency_code+']"]').trigger('input');

	$('button[name="remove"]').on('click', function(e) {
		e.preventDefault();
		if (confirm('<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>')) {
			$(this).closest('tr').remove();
		}
	});

	var new_product_i = 0;
	while ($('input[name^="products[new_product_'+new_product_i+']"]').length) new_product_i++;

	window.add_product = function(product) {

		$output = $([
			'<tr>',
			'  <td>',
			'    <?php echo functions::form_input_hidden('products[new_product_i][product_id]', 'product.id'); ?>',
			'    <a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_product', ['product_id' => 'product.id']); ?>">',
			'      ' + product.name,
			'		 </a>',
			'  </td>',
			'  <td class="text-end">'+ product.price +'</td>',
			'  <td>',
			'    <div class="dropdown dropdown-end">',
			'      <?php echo functions::escape_js(functions::form_input_money('products[new_product_i][price]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), '', 'style="width: 125px;"')); ?>',
			'      <ul class="dropdown-menu">',
			'        <?php foreach (array_diff($currency_codes, [settings::get('store_currency_code')]) as $currency_code) { ?>',
			'        <li>',
			'          <?php echo functions::escape_js(functions::form_input_money('products[new_product_i][price]['. $currency_code .']', $currency_code, '', 'style="width: 125px;"')); ?>',
			'        </li>',
			'        <?php } ?>',
			'      </ul>',
			'    </div>',
			'  </td>',
			'  <td><?php echo functions::escape_js(functions::form_input_percent('products[new_product_i][percentage]', '', 2)); ?></td>',
			'  <td class="text-end">',
			'    <button class="btn btn-danger btn-sm" name="remove" type="button" title="<?php echo language::translate('title_edit', 'Edit'); ?>">',
			'      <?php echo functions::draw_fonticon('icon-times'); ?>',
			'    </button>',
			'  </td>',
			'</tr>',
		].join('\n')
			.replace(/new_product_i/g, 'new_'+new_product_i++)
			.replace(/product\.id/g, product.id)
		);

		$('table tbody').append($output);
	};
</script>