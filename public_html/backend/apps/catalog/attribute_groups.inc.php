<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	document::$title[] = language::translate('title_attribute_groups', 'Attribute Groups');

	breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
	breadcrumbs::add(language::translate('title_attribute_groups', 'Attribute Groups'), document::ilink(__APP__.'/attribute_groups'));

	// Table Rows, Total Number of Rows, Total Number of Pages
	$attribute_groups = database::query(
		"select ag.id, ag.code, json_value(ag.name, '$.".database::input(language::$selected['code'])."') as name, av.num_values
		from ". DB_TABLE_PREFIX ."attribute_groups ag
		left join (
			select group_id, count(id) as num_values
			from ". DB_TABLE_PREFIX ."attribute_values
			group by group_id
		) av on av.group_id = ag.id
		order by name asc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_attribute_groups', 'Attribute Groups'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_attribute_group'), language::translate('title_create_new_group', 'Create New Group'), '', 'create'); ?>
	</div>

	<?php echo functions::form_begin('attributes_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th class="text-center"><?php echo language::translate('title_id', 'ID'); ?></th>
					<th class="text-center"><?php echo language::translate('title_code', 'Code'); ?></th>
					<th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
					<th><?php echo language::translate('title_values', 'Values'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($attribute_groups as $attribute_group) { ?>
				<tr>
					<td><?php echo functions::form_checkbox('attributes[]', $attribute_group['id']); ?></td>
					<td class="text-center"><?php echo $attribute_group['id']; ?></td>
					<td><?php echo $attribute_group['code']; ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_attribute_group', ['group_id' => $attribute_group['id']]); ?>"><?php echo $attribute_group['name']; ?></a></td>
					<td class="text-center"><?php echo $attribute_group['num_values']; ?></td>
					<td><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_attribute_group', ['group_id' => $attribute_group['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="6"><?php echo language::translate('title_attributes', 'Attributes'); ?>: <?php echo language::number_format($num_rows); ?></td>
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
