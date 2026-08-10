<?php

	document::$title[] = t('title_licenses', 'Licenses');

	breadcrumbs::add(t('title_addons', 'Add-Ons'), document::ilink(__APP__ . '/installed'));
	breadcrumbs::add(t('title_licenses', 'Licenses'), document::ilink());

	// Installed add-ons
	$installed_marketplace_addons = [];

	foreach (f::file_search('storage://addons/*/vmod.xml') as $file) {
		$dom = new DOMDocument();
		$dom->load($file);

		if ($dom->getElementsByTagName('marketplace')->length) {
			$addon_id = $dom->getElementsByTagName('marketplace')->item(0)->getElementsByTagName('addon_id')->item(0)->textContent;
			$installed_marketplace_addons[$addon_id] = dirname($file) . '/';
		}
	}

	// Licenses
	if (!($licenses = marketplace_client::get_licenses())) {
		$licenses = [];
	}

	// Number of Rows
	$num_rows = count($licenses);

	// Number of Pages
	$num_pages = ceil($num_rows / settings::get('data_table_rows_per_page'));

?>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_licenses', 'Licenses'); ?> / <?php echo t('title_purchased_addons', 'Purchased Add-ons'); ?>
		</div>
	</div>

	<?php echo f::form_begin('vmod_form', 'post', '', true); ?>

		<table class="table table-striped table-hover data-table">
			<thead>
				<tr>
					<th><?php echo f::draw_fonticon('icon-check-square-o icon-fw', 'data-toggle="checkbox-toggle"'); ?></th>
					<th class="main"><?php echo t('title_addon', 'Add-on'); ?></th>
					<th><?php echo t('title_invoice', 'Invoice'); ?></th>
					<th><?php echo t('title_updates_expiry', 'Updates Expiry'); ?></th>
					<th><?php echo t('title_valid_until', 'Valid Until'); ?></th>
					<th class="text-center"><?php echo t('title_status', 'Status'); ?></th>
					<th><?php echo t('title_invoice_date', 'Invoice Date'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($licenses as $license) { ?>
				<tr>
					<td><?php echo f::form_checkbox('licenses[]', $license['id']); ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__ . '/addon', ['addon_id' => $license['addon']['id']]); ?>"><?php echo f::escape_html($license['addon']['name']); ?></a></td>
					<td class="text-center"><a class="btn btn-default btn-sm" href="<?php echo f::escape_html($license['invoice']['link']); ?>" target="_blank"><?php echo f::draw_fonticon('icon-file-text'); ?> <?php echo $license['invoice']['no']; ?></a></td>
					<td class="text-end"><?php echo !empty($license['period_end']) ? f::datetime_when($license['period_end']) : '-'; ?></td>
					<td class="text-end"><?php echo !empty($license['updates_expire']) ? f::datetime_format('date', $license['updates_expire']) : '-'; ?></td>
					<td class="text-center"><?php echo in_array($license['addon']['id'], array_keys($installed_marketplace_addons)) ? '<strong>' . f::draw_fonticon('ok') . ' ' . t('title_installed') . '</strong>' : t('title_not_installed', 'Not Installed'); ?></td>
					<td class="text-end"><?php echo f::datetime_format('date', $license['created_at']); ?></td>
					<td>
						<a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__ . '/addon', ['addon_id' => $license['addon']['id']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>">
							<?php echo f::draw_fonticon('edit'); ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_licenses', 'Licenses'); ?>: <?php echo f::format_number($num_rows); ?>
					</td>
				</tr>
			</tfoot>
		</table>

	<?php echo f::form_end(); ?>
</div>

<script>
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>