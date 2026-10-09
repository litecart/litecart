<?php

	administrator::require_login();

	document::$title[] = t('title_campaigns', 'Campaigns');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_campaigns', 'Campaigns'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

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
		"select c.*, cp.num_products, csp.num_scope_products
		from ". DB_PREFIX ."campaigns c
		left join (
			select campaign_id, count(*) as num_products
			from ". DB_PREFIX ."products_prices
			where campaign_id is not null
			group by campaign_id
		) cp on (cp.campaign_id = c.id)
		left join (
			select cs.campaign_id, count(distinct p.id) as num_scope_products
			from ". DB_PREFIX ."campaigns_scopes cs
			left join ". DB_PREFIX ."products_to_categories ptc on (cs.scope_type = 'category' and cs.scope_id = ptc.category_id)
			left join ". DB_PREFIX ."products p on (p.id = ptc.product_id or (cs.scope_type = 'brand' and p.brand_id = cs.scope_id))
			where p.id is not null
			group by cs.campaign_id
		) csp on (csp.campaign_id = c.id)
		order by c.status desc, c.valid_from, c.valid_to;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_campaigns', 'Campaigns') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/edit_campaign'), t('title_create_new_campaign', 'Create New Campaign'), '', 'create') ?>
	</div>

	<?= f::form_begin('campaigns_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check checkbox-toggle', 'data-toggle="checkbox-toggle"') ?></th>
					<th><?= t('title_ID', 'ID') ?></th>
					<th class="main"><?= t('title_Name', 'Name') ?></th>
					<th><?= t('title_type', 'Type') ?></th>
					<th class="text-end"><?= t('title_valid_from', 'Valid From') ?></th>
					<th class="text-end"><?= t('title_valid_to', 'Valid To') ?></th>
					<th class="text-end"><?= t('title_products', 'Products') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($campaigns as $campaign) { ?>
				<tr class="<?php if (!empty($campaign['end_date']) && $campaign['end_date'] < date('Y-m-d H:i:s')) echo 'semi-transparent'; ?>">
					<td><?= f::form_checkbox('campaigns[]', $campaign['id']) ?></td>
					<td><?= $campaign['id'] ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_campaign', ['campaign_id' => $campaign['id']]) ?>"><?= $campaign['name'] ?></a></td>
					<td><?= $campaign['discount_mode'] == 'percentage' ? '-'. (float)$campaign['discount_percent'] .'%' : t('title_fixed_prices', 'Fixed Prices') ?></td>
					<td class="text-end"><?= $campaign['valid_from'] ? f::datetime_format('datetime', $campaign['valid_from']) : '' ?></td>
					<td class="text-end"><?= $campaign['valid_to'] ? f::datetime_format('datetime', $campaign['valid_to']) : '' ?></td>
					<td class="text-center"><?= f::format_number($campaign['discount_mode'] == 'percentage' ? $campaign['num_scope_products'] : $campaign['num_products']) ?></td>
					<td class="text-end">
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_campaign', ['campaign_id' => $campaign['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>">
							<?= f::draw_fonticon('edit') ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_campaigns', 'Campaigns') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

	<?= f::form_end() ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-body">
		<?= f::draw_pagination($num_pages) ?>
	</div>
	<?php } ?>

</div>