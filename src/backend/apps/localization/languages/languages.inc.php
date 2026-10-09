<?php

	administrator::require_login();

	document::$title[] = t('title_languages', 'Languages');

	breadcrumbs::add(t('title_localization', 'Localization'));
	breadcrumbs::add(t('title_languages', 'Languages'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['languages'])) {
				throw new Exception(t('error_must_select_languages', 'You must select languages'));
			}

			foreach (array_keys($_POST['languages']) as $language_code) {

				if (!empty($_POST['disable']) && $language_code == settings::get('default_language_code')) {
					throw new Exception(t('error_cannot_disable_default_language', 'You cannot disable the default language'));
				}

				if (!empty($_POST['disable']) && $language_code == settings::get('store_language_code')) {
					throw new Exception(t('error_cannot_disable_store_language', 'You cannot disable the store language'));
				}

				$language = new ent_language($_POST['languages'][$language_code]);
				$language->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$language->save();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$languages = database::prepare(
		"select * from ". DB_PREFIX ."languages
		order by field(status, 1, -1, 0), priority, name;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_languages', 'Languages') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/languages/edit_language'), t('title_create_new_language', 'Create New Language'), '', 'create') ?>
	</div>

	<?= f::form_begin('languages_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th>ISO 639-1</th>
					<th>ISO 639-2</th>
					<th><?= t('title_url_type', 'URL Type') ?></th>
					<th><?= t('title_default_language', 'Default Language') ?></th>
					<th><?= t('title_store_language', 'Store Language') ?></th>
					<th><?= t('title_priority', 'Priority') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
			<?php foreach ($languages as $language) { ?>
				<tr class="<?= empty($language['status']) ? 'semi-transparent' : '' ?>">
					<td><?= f::form_checkbox('languages[]', $language['code']) ?></td>
					<td><?= f::draw_fonticon(($language['status'] == 1) ? 'on' : (($language['status'] == -1) ? 'semi-off' : 'off')) ?></td>
					<td><?= $language['id'] ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/languages/edit_language', ['language_code' => $language['code']]) ?>"><?= $language['name'] ?></a></td>
					<td class="text-center"><?= $language['code'] ?></td>
					<td class="text-center"><?= $language['code2'] ?></td>
					<td class="text-center"><?= $language['url_type'] ?></td>
					<td class="text-center"><?= ($language['code'] == settings::get('default_language_code')) ? f::draw_fonticon('icon-check') : '' ?></td>
					<td class="text-center"><?= ($language['code'] == settings::get('store_language_code')) ? f::draw_fonticon('icon-check') : '' ?></td>
					<td class="text-center"><?= $language['priority'] ?></td>
					<td class="text-end">
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/languages/edit_language', ['language_code' => $language['code']]) ?>" title="<?= t('title_edit', 'Edit') ?>">
							<?= f::draw_fonticon('edit') ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_languages', 'Languages') ?>: <?= f::format_number($num_rows) ?>
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