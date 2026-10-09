<?php

	administrator::require_login();

	document::$title[] = t('title_currencies', 'Currencies');

	breadcrumbs::add(t('title_localization', 'Localization'));
	breadcrumbs::add(t('title_currencies', 'Currencies'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['currencies'])) {
				throw new Exception(t('error_must_select_currencies', 'You must select currencies'));
			}

			foreach (array_keys($_POST['currencies']) as $currency_code) {

				if (!empty($_POST['disable']) && $currency_code == settings::get('default_currency_code')) {
					throw new Exception(t('error_cannot_disable_default_currency', 'You cannot disable the default currency'));
				}

				if (!empty($_POST['disable']) && $currency_code == settings::get('store_currency_code')) {
					throw new Exception(t('error_cannot_disable_store_currency', 'You cannot disable the store currency'));
				}

				$currency = new ent_currency($_POST['currencies'][$currency_code]);
				$currency->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$currency->save();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Table Rows
	$currencies = database::query(
		"select * from ". DB_PREFIX ."currencies
		order by field(status, 1, -1, 0), priority, name;"
	)->fetch_all();

	// Number of Rows
	$num_rows = count($currencies);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_currencies', 'Currencies') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/currencies/edit_currency'), t('title_create_new_currency', 'Create New Currency'), '', 'create') ?>
	</div>

	<?= f::form_begin('currencies_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th><?= t('title_code', 'Code') ?></th>
					<th><?= t('title_value', 'Value') ?></th>
					<th><?= t('title_format_example', 'Format Example') ?></th>
					<th><?= t('title_default_currency', 'Default Currency') ?></th>
					<th><?= t('title_store_currency', 'Store Currency') ?></th>
					<th><?= t('title_priority', 'Priority') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($currencies as $currency) { ?>
				<tr class="<?= empty($currency['status']) ? 'semi-transparent' : '' ?>">
					<td><?= f::form_checkbox('currencies[]', $currency['code']) ?></td>
					<td><?= f::draw_fonticon(($currency['status'] == 1) ? 'on' : (($currency['status'] == -1) ? 'semi-off' : 'off')) ?></td>
					<td><?= $currency['id'] ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/currencies/edit_currency', ['currency_code' => $currency['code']]) ?>"><?= $currency['name'] ?></a></td>
					<td><?= $currency['code'] ?></td>
					<td class="text-end"><?= f::format_number($currency['value'], 4) ?></td>
					<td class="text-center"><?= currency::format_html(1234.56, false, $currency['code'], 1) ?></td>
					<td class="text-center"><?= ($currency['code'] == settings::get('default_currency_code')) ? f::draw_fonticon('icon-check') : '' ?></td>
					<td class="text-center"><?= ($currency['code'] == settings::get('store_currency_code')) ? f::draw_fonticon('icon-check') : '' ?></td>
					<td class="text-center"><?= $currency['priority'] ?></td>
					<td class="text-end">
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/currencies/edit_currency', ['currency_code' => $currency['code']]) ?>" title="<?= t('title_edit', 'Edit') ?>">
							<?= f::draw_fonticon('edit') ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_currencies', 'Currencies') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">

				<legend>
					<?= t('text_with_selected', 'With selected') ?>:
				</legend>

				<div class="btn-group">
					<?= f::form_button_predefined('enable') ?>
					<?= f::form_button_predefined('disable') ?>
				</div>

			</fieldset>
		</div>

	<?= f::form_end() ?>
</div>

<script>
	$('.data-table input[name^="currencies["]').on('change', function() {
		if ($('.data-table input[name^="currencies["]:checked').length > 0) {
			$('fieldset').prop('disabled', false);
		} else {
			$('fieldset').prop('disabled', true);
		}
	}).trigger('change');
</script>