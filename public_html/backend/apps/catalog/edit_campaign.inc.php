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

	$currencies = array_map(function($currency){
		return ['code' => $currency['code'], 'decimals' => (int)$currency['decimals'], 'value' => $currency['value']];
	}, currency::$currencies);

	functions::draw_lightbox();

?>
<div class="card card-app">
	<div class="card-header">
	<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($campaign->data['id']) ? language::translate('title_edit_campaign', 'Edit Campaign') : language::translate('title_create_new_campaign', 'Create New Campaign'); ?>
		</div>
	</div>

	<?php echo functions::form_begin('campaigns_form', 'post'); ?>

		<div class="card-body">
			<div style="max-width: 640px;">

				<div class="row">
					<div class="form-group col-md-6">
						<label><?php echo language::translate('title_status', 'Status'); ?></label>
						<?php echo functions::form_toggle('status', 'e/d', true); ?>
					</div>
				</div>

				<div class="form-group">
					<label><?php echo language::translate('title_name', 'Name'); ?></label>
					<?php echo functions::form_input_text('name', true); ?>
				</div>

				<div class="row">
					<div class="form-group col-md-6">
						<label><?php echo language::translate('title_valid_from', 'Valid From'); ?></label>
						<?php echo functions::form_input_datetime('date_valid_from', true); ?>
					</div>

					<div class="form-group col-md-6">
						<label><?php echo language::translate('title_valid_to', 'Valid To'); ?></label>
						<?php echo functions::form_input_datetime('date_valid_to', true); ?>
					</div>
				</div>

			</div>
		</div>

		<table id="campaigns" class="table table-striped table-hover data-table">
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
					<td class="text-end"><?php echo functions::form_input_money('products['.$key.']['.settings::get('store_currency_code').']', settings::get('store_currency_code'), true, 'style="width: 200px;"'); ?></td>
					<td><?php echo functions::form_input_percent('products['.$key.'][percentage]', '', 2, 'style="width: 100px;"'); ?></td>
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
				<?php echo functions::draw_fonticon('icon-plus', 'style="margin-right: 0.5em;"'); ?> <?php echo language::translate('title_add_product', 'Add Product'); ?>
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

	$('#campaigns').on('focus', 'input[name^="campaigns"]', (e) => {
		if($(this).attr('name').match(/\[[A-Z]{3}\]$/)) {
			$(this).closest('.dropdown').addClass('open')
	 }
	})

	$('#campaigns').on('blur', '.dropdown', (e) => {
		$(this).removeClass('open')
	})

	$('#campaigns').on('input', 'input[name^="campaigns"][name$="[percentage]"]', () => {
		let parent = $(this).closest('tr'),
			value = 0

		<?php foreach (currency::$currencies as $currency) { ?>
		if ($('input[name^="prices"][name$="[<?php echo $currency['code']; ?>]"]').val() > 0) {
			value = parseFloat($('input[name="prices[<?php echo $currency['code']; ?>]"]').val() * (100 - $(this).val()) / 100).toFixed(<?php echo $currency['decimals']; ?>)
			$(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val(value)
		} else {
			$(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val("")
		}
		<?php } ?>

		<?php foreach (currency::$currencies as $currency) { ?>
		value = parseFloat($(parent).find('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val() / <?php echo $currency['value']; ?>).toFixed(<?php echo $currency['decimals']; ?>)
		$(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').attr('placeholder', value)
		<?php } ?>
	})

	$('#campaigns').on('input', 'input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]', () => {
		let parent = $(this).closest('tr')
		let percentage = ($('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() - $(this).val()) / $('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() * 100
		percentage = percentage.toFixed(2)
		$(parent).find('input[name$="[percentage]"]').val(percentage)

		<?php foreach (currency::$currencies as $currency) { ?>
		value = $(parent).find('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val() / <?php echo $currency['value']; ?>
		value = value.toFixed(<?php echo $currency['decimals']; ?>)
		$(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').attr("placeholder", value)
		if ($(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').val() == 0) {
			$(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').val('')
		}
		<?php } ?>
	})

	$('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').trigger('input')

	$('button[name="remove"]').on('click', function(e){
		e.preventDefault()
		if (confirm('<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>')) {
			$(this).closest('tr').remove()
		}
	})

	var new_product_i = 0
	while ($('input[name="products[new_product_'+new_product_i+'][product_id]"]').length) new_product_i++

	window.add_product = function(product) {
		var key = $('table tbody tr').length

		$output = $([
			'<tr>',
			'  <td>',
			'    <?php echo functions::form_input_hidden('products[new_product_i][product_id]', 'new_product_id'); ?>',
			'    <a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_product', ['product_id' => 'new_product_id']); ?>">',
			'      ' + product.name,
			'		 </a>',
			'  </td>',
			'  <td class="text-end">'+ product.price +'</td>',
			'  <td class="text-end"><?php echo functions::escape_js(functions::form_input_money('products[new_product_i][currency_code]', settings::get('store_currency_code'), '', 'style="width: 200px;"')); ?></td>',
			'  <td><?php echo functions::escape_js(functions::form_input_percent('products[new_product_i][percentage]', '', 2)); ?></td>',
			'  <td class="text-end">',
			'    <button class="btn btn-danger btn-sm" name="remove" type="button" title="<?php echo language::translate('title_edit', 'Edit'); ?>">',
			'      <?php echo functions::draw_fonticon('icon-times'); ?>',
			'    </button>',
			'  </td>',
			'</tr>',
		].join('\n')
			.replace(/new_product_i/g, 'new_product_'+new_product_i++)
			.replace(/new_product_id/g, product.id)
		)

		$('table tbody').append($output)
	}
</script>