<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	document::$title[] = t('title_campaigns', 'Campaigns');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_campaigns', 'Campaigns'), document::ilink());

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['campaigns'])) {
				throw new Exception(t('error_must_select_campaigns', 'You must select campaigns'));
			}

			foreach ($_POST['campaigns'] as $campaign_id) {
				$campaign = new ent_campaign($campaign_id);
				$campaign->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$campaign->save();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$campaigns = database::query(
		"select c.*, cp.num_products
		from ". DB_TABLE_PREFIX ."campaigns c
		left join (
			select campaign_id, count(*) as num_products
			from ". DB_TABLE_PREFIX ."campaigns_products
			group by campaign_id
		) cp on (cp.campaign_id = c.id)
		order by c.status desc, c.valid_from, c.valid_to;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_campaigns', 'Campaigns'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_campaign'), t('title_create_new_campaign', 'Create New Campaign'), '', 'create'); ?>
	</div>

	<?php echo functions::form_begin('campaigns_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
					<th><?php echo t('title_ID', 'ID'); ?></th>
					<th class="main"><?php echo t('title_Name', 'Name'); ?></th>
					<th class="text-end"><?php echo t('title_valid_from', 'Valid From'); ?></th>
					<th class="text-end"><?php echo t('title_valid_to', 'Valid To'); ?></th>
					<th class="text-end"><?php echo t('title_products', 'Products'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($campaigns as $campaign) { ?>
				<tr class="<?php if (!empty($campaign['end_date']) && $campaign['end_date'] < date('Y-m-d H:i:s')) echo 'semi-transparent'; ?>">
					<td><?php echo functions::form_checkbox('campaigns[]', $campaign['id']); ?></td>
					<td><?php echo $campaign['id']; ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_campaign', ['campaign_id' => $campaign['id']]); ?>"><?php echo $campaign['name']; ?></a></td>
					<td class="text-end"><?php echo $campaign['valid_from'] ? functions::datetime_format('datetime', $campaign['valid_from']) : ''; ?></td>
					<td class="text-end"><?php echo $campaign['valid_to'] ? functions::datetime_format('datetime', $campaign['valid_to']) : ''; ?></td>
					<td class="text-center"><?php echo language::number_format($campaign['num_products']); ?></td>
					<td class="text-end">
						<a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_campaign', ['campaign_id' => $campaign['id']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>">
							<?php echo functions::draw_fonticon('edit'); ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_campaigns', 'Campaigns'); ?>: <?php echo language::number_format($num_rows); ?>
					</td>
				</tr>
			</tfoot>
		</table>

	<?php echo functions::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-body">
		<?php echo functions::draw_pagination($num_pages); ?>
	</div>
	<?php } ?>

</div>