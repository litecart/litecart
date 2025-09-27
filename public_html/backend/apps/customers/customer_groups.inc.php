<?php

	document::$title[] = t('title_customer_groups', 'Customer Groups');

	breadcrumbs::add(t('title_customers', 'Customers'), document::ilink(__APP__.'/customers'));
	breadcrumbs::add(t('title_customer_groups', 'Customer Groups'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	$customer_groups = database::query(
		"select cg.*, c.num_customers
		from ". DB_TABLE_PREFIX ."customer_groups cg
		left join (
			select group_id, count(*) as num_customers
			from ". DB_TABLE_PREFIX ."customers
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
			<?php echo $app_icon; ?> <?php echo t('title_customer_groups', 'Customer Groups'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_customer_group'), t('title_create_new_customer_group', 'Create New Customer Group'), '', 'create'); ?>
	</div>

	<div class="card-filter">
		<?php echo functions::form_begin('search_form', 'get'); ?>
			<?php echo functions::form_input_search('query', true, 'placeholder="'. t('text_search_phrase_or_keyword', 'Search phrase or keyword') .'" style="width: 400px;"'); ?>
		<?php echo functions::form_end(); ?>
	</div>

	<?php echo functions::form_begin('customer_groups_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check checkbox-toggle'); ?></th>
					<th><?php echo t('title_id', 'ID'); ?></th>
					<th class="main"><?php echo t('title_name', 'Name'); ?></th>
					<th class="tect-center"><?php echo t('title_customers', 'Customers'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($customer_groups as $group)  { ?>
				<tr>
					<td><?php echo functions::form_checkbox('customer_groups[]', $group['id']); ?></td>
					<td><?php echo $group['id']; ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_customer_group', ['group_id' => $group['id']]); ?>"><?php echo $group['name']; ?></a></td>
					<td><?php echo language::number_format($group['num_customers']); ?></td>
					<td><a class="btn btn-default btn-sm" href="<?php echo document::href_link(__APP__.'/edit_customer_group', ['group_id' => $group['id']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_customer_groups', 'Customer Groups'); ?>: <?php echo language::number_format($num_rows); ?>
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
