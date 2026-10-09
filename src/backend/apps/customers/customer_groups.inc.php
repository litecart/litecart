<?php

	administrator::require_login();

	document::$title[] = t('title_customer_groups', 'Customer Groups');

	breadcrumbs::add(t('title_customers', 'Customers'), document::ilink(__APP__.'/customers'));
	breadcrumbs::add(t('title_customer_groups', 'Customer Groups'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	$customer_groups = database::query(
		"select cg.*, c.num_customers
		from ". DB_PREFIX ."customer_groups cg
		left join (
			select group_id, count(*) as num_customers
			from ". DB_PREFIX ."customers
			group by group_id
		) c on (c.group_id = cg.id)
		where true
		". (!empty($_GET['query']) ? "and cg.name like '%". database::input($_GET['query']) ."%'" : "") ."
		order by name;"
	)->fetch_page(null, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_customer_groups', 'Customer Groups') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/edit_customer_group'), t('title_create_new_customer_group', 'Create New Customer Group'), '', 'create') ?>
	</div>

	<div class="card-filter">
		<?= f::form_begin('search_form', 'get') ?>
			<?= f::form_input_search('query', true, ['placeholder' => t('text_search_phrase_or_keyword', 'Search phrase or keyword') , 'style' => 'width: 400px;']) ?>
		<?= f::form_end() ?>
	</div>

	<?= f::form_begin('customer_groups_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check checkbox-toggle') ?></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th class="tect-center"><?= t('title_customers', 'Customers') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($customer_groups as $group)  { ?>
				<tr>
					<td><?= f::form_checkbox('customer_groups[]', $group['id']) ?></td>
					<td><?= $group['id'] ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_customer_group', ['group_id' => $group['id']]) ?>"><?= $group['name'] ?></a></td>
					<td><?= f::format_number($group['num_customers']) ?></td>
					<td><a class="btn btn-default btn-sm" href="<?= document::href_link(__APP__.'/edit_customer_group', ['group_id' => $group['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_customer_groups', 'Customer Groups') ?>: <?= f::format_number($num_rows) ?>
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
