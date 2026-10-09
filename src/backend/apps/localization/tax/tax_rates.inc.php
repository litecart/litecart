<?php

	administrator::require_login();

	document::$title[] = t('title_tax_rates', 'Tax Rates');

	breadcrumbs::add(t('title_localization', 'Localization'));
	breadcrumbs::add(t('title_tax_rates', 'Tax Rates'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$tax_rates = database::prepare(
		"select tr.*, gz.name as geo_zone, tc.name as tax_class
		from ". DB_PREFIX ."tax_rates tr
		left join ". DB_PREFIX ."geo_zones gz on (gz.id = tr.geo_zone_id)
		left join ". DB_PREFIX ."tax_classes tc on (tc.id = tr.tax_class_id)
		order by tc.name, gz.name, tr.name;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_tax_rates', 'Tax Rates') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/tax/edit_tax_rate'), t('title_create_new_tax_rate', 'Create New Tax Rate'), '', 'create') ?>
	</div>

	<?= f::form_begin('tax_rates_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th><?= t('title_tax_class', 'Tax Class') ?></th>
					<th><?= t('title_geo_zone', 'Geo Zone') ?></th>
					<th><?= t('title_name', 'Name') ?></th>
					<th class="main"><?= t('title_description', 'Description') ?></th>
					<th><?= t('title_rate', 'Rate') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($tax_rates as $tax_rate) { ?>
				<tr>
					<td><?= f::form_checkbox('tax_rates[]', $tax_rate['id']) ?></td>
					<td><?= $tax_rate['id'] ?></td>
					<td><?= $tax_rate['tax_class'] ?></td>
					<td><?= $tax_rate['geo_zone'] ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/tax/edit_tax_rate', ['tax_rate_id' => $tax_rate['id']], true) ?>"><?= $tax_rate['name'] ?></a></td>
					<td><?= $tax_rate['description'] ?></td>
					<td><?= f::format_number($tax_rate['rate'], 4) ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/tax/edit_tax_rate', ['tax_rate_id' => $tax_rate['id']], true) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_tax_rates', 'Tax Rates') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

	<?= f::form_end() ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?= f::draw_pagination($num_pages) ?>
	</div>
	<?php } ?>
</div>
