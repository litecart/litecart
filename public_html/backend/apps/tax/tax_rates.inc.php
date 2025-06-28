<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	document::$title[] = t('title_tax_rates', 'Tax Rates');

	breadcrumbs::add(t('title_tax_rates', 'Tax Rates'), document::ilink());

	// Table Rows, Total Number of Rows, Total Number of Pages
	$tax_rates = database::query(
		"select tr.*, gz.name as geo_zone, tc.name as tax_class from ". DB_TABLE_PREFIX ."tax_rates tr
		left join ". DB_TABLE_PREFIX ."geo_zones gz on (gz.id = tr.geo_zone_id)
		left join ". DB_TABLE_PREFIX ."tax_classes tc on (tc.id = tr.tax_class_id)
		order by tc.name, gz.name, tr.name;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_tax_rates', 'Tax Rates'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_tax_rate'), t('title_create_new_tax_rate', 'Create New Tax Rate'), '', 'create'); ?>
	</div>

	<?php echo functions::form_begin('tax_rates_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th><?php echo t('title_id', 'ID'); ?></th>
					<th><?php echo t('title_tax_class', 'Tax Class'); ?></th>
					<th><?php echo t('title_geo_zone', 'Geo Zone'); ?></th>
					<th><?php echo t('title_name', 'Name'); ?></th>
					<th class="main"><?php echo t('title_description', 'Description'); ?></th>
					<th><?php echo t('title_rate', 'Rate'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($tax_rates as $tax_rate) { ?>
				<tr>
					<td><?php echo functions::form_checkbox('tax_rates[]', $tax_rate['id']); ?></td>
					<td><?php echo $tax_rate['id']; ?></td>
					<td><?php echo $tax_rate['tax_class']; ?></td>
					<td><?php echo $tax_rate['geo_zone']; ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_tax_rate', ['tax_rate_id' => $tax_rate['id']], true); ?>"><?php echo $tax_rate['name']; ?></a></td>
					<td><?php echo $tax_rate['description']; ?></td>
					<td><?php echo language::number_format($tax_rate['rate'], 4); ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_tax_rate', ['tax_rate_id' => $tax_rate['id']], true); ?>" title="<?php echo t('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_tax_rates', 'Tax Rates'); ?>: <?php echo language::number_format($num_rows); ?>
					</td>
				</tr>
			</tfoot>
		</table>

	<?php echo functions::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?php echo functions::draw_pagination($num_pages); ?>
	</div>
	<?php } ?>
</div>
