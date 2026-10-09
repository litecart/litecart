<?php

	administrator::require_login();

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	document::$title[] = t('title_banners', 'Banners');

	breadcrumbs::add(t('title_banners', 'Banners'), document::ilink());

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['banners'])) {
				throw new Exception(t('error_must_select_banners', 'You must select banners'));
			}

			foreach ($_POST['banners'] as $banner_id) {

				$banner = new ent_banner($banner_id);
				$banner->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$banner->save();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$banners = database::query(
		"select * from ". DB_PREFIX ."banners
		where true
		". (!empty($_GET['keyword']) ? "and find_in_set('". database::input($_GET['keywords']) ."', keywords)" : '') ."
		". (!empty($_GET['query']) ? "and name like '%". database::input($_GET['query']) ."%'" : '') ."
		order by status desc, name asc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_banners', 'Banners') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_begin('filter_form', 'get') ?>
			<ul class="list-inline">
				<li><?= f::form_input_search('query', true, ['placeholder' => t('text_search_phrase_or_keyword', 'Search phrase or keyword') , 'style' => 'width: 250px;']) ?></li>
				<li><?= f::form_button_link(document::ilink(__APP__.'/edit_banner'), t('title_create_new_banner', 'Create New Banner'), '', 'create') ?></li>
			</ul>
		<?= f::form_end() ?>
	</div>

	<?= f::form_begin('banners_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th><?= t('title_keywords', 'Keywords') ?></th>
					<th class="text-center"><?= t('title_clicks', 'Clicks') ?></th>
					<th class="text-center"><?= t('title_views', 'Views') ?></th>
					<th class="text-center"><?= t('title_ratio', 'Ratio') ?></th>
					<th class="text-center"><?= t('title_valid_from', 'Valid From') ?></th>
					<th class="text-center"><?= t('title_valid_to', 'Valid To') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($banners as $banner) { ?>
				<tr class="<?= $banner['status'] ? false : ' semi-transparent' ?>">
					<td><?= f::form_checkbox('banners[]', $banner['id']) ?></td>
					<td><?= f::draw_fonticon(!empty($banner['status']) ? 'on' : 'off') ?></td>
					<td><?= $banner['id'] ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_banner', ['banner_id' => $banner['id']]) ?>"><?= $banner['name'] ?></a></td>
					<td><?= $banner['keywords'] ?></td>
					<td class="text-end"><?= $banner['total_clicks'] ?></td>
					<td class="text-end"><?= $banner['total_views'] ?></td>
					<td class="text-end"><?= !empty($banner['total_clicks']) ? '1:'.round($banner['total_views']/$banner['total_clicks']) : '-' ?></td>
					<td class="text-center"><?= $banner['valid_from'] ? f::datetime_when($banner['valid_from']) : '-' ?></td>
					<td class="text-center"><?= $banner['valid_to'] ? f::datetime_when($banner['valid_to']) : '-' ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_banner', ['banner_id' => $banner['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_banners', 'Banners') ?>: <?= $num_rows ?>
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