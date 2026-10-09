<?php

	administrator::require_login();

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
			<?= $app_icon ?> <?= t('title_licenses', 'Licenses') ?> / <?= t('title_purchased_addons', 'Purchased Add-ons') ?>
		</div>
	</div>

	<?= f::form_begin('vmod_form', 'post', '', true) ?>

		<table class="table table-striped table-hover data-table">
			<thead>
				<tr>
					<th><?= f::draw_fonticon('icon-check-square-o icon-fw', 'data-toggle="checkbox-toggle"') ?></th>
					<th class="main"><?= t('title_addon', 'Add-on') ?></th>
					<th><?= t('title_invoice', 'Invoice') ?></th>
					<th><?= t('title_updates_expiry', 'Updates Expiry') ?></th>
					<th><?= t('title_valid_until', 'Valid Until') ?></th>
					<th class="text-center"><?= t('title_status', 'Status') ?></th>
					<th><?= t('title_invoice_date', 'Invoice Date') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($licenses as $license) { ?>
				<tr>
					<td><?= f::form_checkbox('licenses[]', $license['id']) ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__ . '/addon', ['addon_id' => $license['addon']['id']]) ?>"><?= f::escape_html($license['addon']['name']) ?></a></td>
					<td class="text-center"><a class="btn btn-default btn-sm" href="<?= f::escape_html($license['invoice']['link']) ?>" target="_blank"><?= f::draw_fonticon('icon-file-text') ?> <?= $license['invoice']['no'] ?></a></td>
					<td class="text-end"><?= !empty($license['period_end']) ? f::datetime_when($license['period_end']) : '-' ?></td>
					<td class="text-end"><?= !empty($license['updates_expire']) ? f::datetime_format('date', $license['updates_expire']) : '-' ?></td>
					<td class="text-center"><?= in_array($license['addon']['id'], array_keys($installed_marketplace_addons)) ? '<strong>' . f::draw_fonticon('ok') . ' ' . t('title_installed') . '</strong>' : t('title_not_installed', 'Not Installed') ?></td>
					<td class="text-end"><?= f::datetime_format('date', $license['created_at']) ?></td>
					<td>
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__ . '/addon', ['addon_id' => $license['addon']['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>">
							<?= f::draw_fonticon('edit') ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_licenses', 'Licenses') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

	<?= f::form_end() ?>
</div>

<script>
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>