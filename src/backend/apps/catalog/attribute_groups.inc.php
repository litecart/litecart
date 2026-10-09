<?php

	administrator::require_login();

	document::$title[] = t('title_attribute_groups', 'Attribute Groups');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_attribute_groups', 'Attribute Groups'), document::ilink(__APP__.'/attribute_groups'));

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$attribute_groups = database::query(
		"select ag.id, ag.code, json_value(ag.name, '$.". database::input(language::$selected['code']) ."') as name, av.num_values
		from ". DB_PREFIX ."attribute_groups ag
		left join (
			select group_id, count(id) as num_values
			from ". DB_PREFIX ."attribute_values
			group by group_id
		) av on av.group_id = ag.id
		order by name asc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_attributes', 'Attributes') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/edit_attribute_group'), t('title_create_new_group', 'Create New Group'), '', 'create') ?>
	</div>

	<?= f::form_begin('attributes_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th class="text-center"><?= t('title_id', 'ID') ?></th>
					<th class="text-center"><?= t('title_code', 'Code') ?></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th><?= t('title_values', 'Values') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($attribute_groups as $attribute_group) { ?>
				<tr>
					<td><?= f::form_checkbox('attributes[]', $attribute_group['id']) ?></td>
					<td class="text-center"><?= $attribute_group['id'] ?></td>
					<td><?= $attribute_group['code'] ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_attribute_group', ['group_id' => $attribute_group['id']]) ?>"><?= $attribute_group['name'] ?></a></td>
					<td class="text-center"><?= $attribute_group['num_values'] ?></td>
					<td><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_attribute_group', ['group_id' => $attribute_group['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_attributes', 'Attributes') ?>: <?= f::format_number($num_rows) ?>
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
