<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	document::$title[] = language::translate('title_currencies', 'Currencies');

	breadcrumbs::add(language::translate('title_currencies', 'Currencies'), document::ilink());

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['currencies'])) {
				throw new Exception(language::translate('error_must_select_currencies', 'You must select currencies'));
			}

			foreach (array_keys($_POST['currencies']) as $currency_code) {

				if (!empty($_POST['disable']) && $currency_code == settings::get('default_currency_code')) {
					throw new Exception(language::translate('error_cannot_disable_default_currency', 'You cannot disable the default currency'));
				}

				if (!empty($_POST['disable']) && $currency_code == settings::get('store_currency_code')) {
					throw new Exception(language::translate('error_cannot_disable_store_currency', 'You cannot disable the store currency'));
				}

				$currency = new ent_currency($_POST['currencies'][$currency_code]);
				$currency->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$currency->save();
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Table Rows
	$currencies = database::query(
		"select * from ". DB_TABLE_PREFIX ."currencies
		order by field(status, 1, -1, 0), priority, name;"
	)->fetch_all();

	// Number of Rows
	$num_rows = count($currencies);

?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_currencies', 'Currencies'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_currency'), language::translate('title_create_new_currency', 'Create New Currency'), '', 'add'); ?>
	</div>

	<?php echo functions::form_begin('currencies_form', 'post'); ?>

		<table class="table table-striped table-hover data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th><?php echo language::translate('title_id', 'ID'); ?></th>
					<th><?php echo language::translate('title_code', 'Code'); ?></th>
					<th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
					<th><?php echo language::translate('title_value', 'Value'); ?></th>
					<th><?php echo language::translate('title_format_example', 'Format Example'); ?></th>
					<th><?php echo language::translate('title_default_currency', 'Default Currency'); ?></th>
					<th><?php echo language::translate('title_store_currency', 'Store Currency'); ?></th>
					<th><?php echo language::translate('title_priority', 'Priority'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($currencies as $currency) { ?>
				<tr class="<?php echo empty($currency['status']) ? 'semi-transparent' : ''; ?>">
					<td><?php echo functions::form_checkbox('currencies[]', $currency['code']); ?></td>
					<td><?php echo functions::draw_fonticon(($currency['status'] == 1) ? 'on' : (($currency['status'] == -1) ? 'semi-off' : 'off')); ?></td>
					<td><?php echo $currency['id']; ?></td>
					<td><?php echo $currency['code']; ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_currency', ['currency_code' => $currency['code']]); ?>"><?php echo $currency['name']; ?></a></td>
					<td class="text-end"><?php echo language::number_format($currency['value'], 4); ?></td>
					<td class="text-center"><?php echo currency::format_html(1234.56, false, $currency['code'], 1); ?></td>
					<td class="text-center"><?php echo ($currency['code'] == settings::get('default_currency_code')) ? functions::draw_fonticon('icon-check') : ''; ?></td>
					<td class="text-center"><?php echo ($currency['code'] == settings::get('store_currency_code')) ? functions::draw_fonticon('icon-check') : ''; ?></td>
					<td class="text-center"><?php echo $currency['priority']; ?></td>
					<td class="text-end">
						<a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_currency', ['currency_code' => $currency['code']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>">
							<?php echo functions::draw_fonticon('edit'); ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="11"><?php echo language::translate('title_currencies', 'Currencies'); ?>: <?php echo language::number_format($num_rows); ?></td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">
				<legend><?php echo language::translate('text_with_selected', 'With selected'); ?>:</legend>

				<div class="btn-group">
					<?php echo functions::form_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
					<?php echo functions::form_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
				</div>
			</fieldset>
		</div>

	<?php echo functions::form_end(); ?>
</div>

<script>
	$('.data-table input[name^="currencies["]').on('change', function() {
		if ($('.data-table input[name^="currencies["]:checked').length > 0) {
			$('fieldset').prop('disabled', false)
		} else {
			$('fieldset').prop('disabled', true)
		}
	}).trigger('change')
</script>