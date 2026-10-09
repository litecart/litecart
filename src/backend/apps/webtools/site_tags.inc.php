<?php

	administrator::require_login();

	document::$title[] = t('title_site_tags', 'Site Tags');

	breadcrumbs::add(t('title_webtools', 'Webtools'));
	breadcrumbs::add(t('title_site_tags', 'Site Tags'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['site_tags'])) {
				throw new Exception(t('error_must_select_site_tags', 'You must select site_tags'));
			}

			foreach ($_POST['site_tags'] as $site_tag_id) {
				$site_tag = new ent_site_tag($site_tag_id);
				$site_tag->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$site_tag->save();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

// Table Rows
	$site_tags = database::prepare(
		"select * from ". DB_PREFIX ."site_tags
		order by status desc, position asc, priority asc, name asc;"
	)->fetch_page(null, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);
?>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_site_tags', 'Site Tags') ?>
		</div>
	</div>

	<div class="card-action">
		<ul class="list-inline">
			<li><?= f::form_button_link(document::ilink(__APP__.'/edit_site_tag'), t('title_create_new_site_tag', 'Create New Site Tag'), '', 'create') ?></li>
		</ul>
	</div>

	<?= f::form_begin('site_tags_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check checkbox-toggle', 'data-toggle="checkbox-toggle"') ?></th>
					<th></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th><?= t('title_require_consent', 'Require Consent') ?></th>
					<th><?= t('title_position', 'Position') ?></th>
					<th><?= t('title_priority', 'Priority') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($site_tags as $site_tag) { ?>
				<tr class="<?= empty($site_tag['status']) ? 'semi-transparent' : null ?>">
					<td><?= f::form_checkbox('site_tags[]', $site_tag['id']) ?></td>
					<td><?= f::draw_fonticon(!empty($site_tag['status']) ? 'on' : 'off') ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_site_tag', ['site_tag_id' => $site_tag['id']]) ?>"><?= $site_tag['name'] ?></a></td>
					<td class="text-center"><?= $site_tag['require_consent'] ? f::draw_fonticon('icon-check') : '' ?></td>
					<td class="text-center"><?= $site_tag['position'] ?></td>
					<td class="text-center"><?= (int)$site_tag['priority'] ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_site_tag', ['site_tag_id' => $site_tag['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php }?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_site_tags', 'Site Tags') ?>: <?= $num_rows ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions" disabled>

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
	$('.data-table input[name^="site_tags["]').on('change', function() {
		if ($('.data-table input[name^="site_tags["]:checked').length > 0) {
			$('fieldset').prop('disabled', false);
		} else {
			$('fieldset').prop('disabled', true);
		}
	}).trigger('change');
</script>
