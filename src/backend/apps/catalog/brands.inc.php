<?php

	administrator::require_login();

	document::$title[] = t('title_brands', 'Brands');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_brands', 'Brands'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['brands'])) {
				throw new Exception(t('error_must_select_brands', 'You must select brands'));
			}

			foreach ($_POST['brands'] as $brand_id) {
				$brand = new ent_brand($brand_id);
				$brand->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$brand->save();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Search Filter
	if (!empty($_GET['query'])) {
		$sql_find = [
			"b.id = '". database::input($_GET['query']) ."'",
			"b.name like '%". database::input($_GET['query']) ."%'",
		];
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$brands = database::query(
		"select b.*, p.num_products
		from ". DB_PREFIX ."brands b
		left join (
			select brand_id, count(id) as num_products
			from ". DB_PREFIX ."products
			group by brand_id
		) p on (p.brand_id = b.id)
		where b.id
		". (!empty($sql_find) ? "and (". implode(" or ", $sql_find) .")" : "") ."
		order by name asc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_brands', 'Brands') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/edit_brand'), t('title_create_new_brand', 'Create New Brand'), '', 'create') ?>
	</div>

	<?= f::form_begin('search_form', 'get') ?>

		<div class="card-filter">
			<div class="expandable"><?= f::form_input_search('query', true, ['placeholder' => t('text_search_phrase_or_keyword', 'Search phrase or keyword')]) ?></div>
			<?= f::form_button('filter', t('title_search', 'Search'), 'submit') ?>
		</div>

	<?= f::form_end() ?>

	<?= f::form_begin('brands_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th></th>
					<th></th>
					<th></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th><?= t('title_products', 'Products') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($brands as $brand) { ?>
				<tr class="<?php if (empty($brand['status'])) echo 'semi-transparent'; ?>">
					<td><?= f::form_checkbox('brands[]', $brand['id']) ?></td>
					<td><?= f::draw_fonticon($brand['status'] ? 'on' : 'off') ?></td>
					<td><?php if ($brand['featured']) echo f::draw_fonticon('icon-star', 'style="color: #ffd700;"'); ?></td>
					<td><?= f::draw_thumbnail('storage://images/' . ($brand['image'] ?: 'no_image.svg'), 16, 16, 'fit', 'style="vertical-align: bottom;"') ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_brand', ['brand_id' => $brand['id']]) ?>"><?= $brand['name'] ?></a></td>
					<td class="text-center"><?= (int)$brand['num_products'] ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_brand', ['brand_id' => $brand['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_brands', 'Brands') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">
				<legend>
					<?= t('text_with_selected', 'With selected') ?>:
				</legend>

				<div class="btn-group">
					<?= f::form_button_predefined('enable') ?>
					<?= f::form_button_predefined('disable') ?>
				</div>

			</fieldset>
		</div>

	<?= f::form_end() ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?= f::draw_pagination($num_pages) ?>
	</div>
	<?php } ?>
</div>

<script>
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>