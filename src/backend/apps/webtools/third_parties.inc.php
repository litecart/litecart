<?php

	administrator::require_login();

	document::$title[] = t('title_third_parties', 'Third Parties');

	breadcrumbs::add(t('title_webtools', 'Webtools'));
	breadcrumbs::add(t('title_third_parties', 'Third Parties'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['third_parties'])) {
				throw new Exception(t('error_must_select_third_parties', 'You must select third_parties'));
			}

			foreach ($_POST['third_parties'] as $third_party_id) {
				$third_party = new ent_third_party($third_party_id);
				$third_party->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$third_party->save();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

// Table Rows
	$third_parties = database::prepare(
		"select * from ". DB_PREFIX ."third_parties
		order by name asc;"
	)->fetch_page(null, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);

	$privacy_classes = [
		'necessary' => t('title_necessary', 'Necessary'),
		'functionality' => t('title_functionality', 'Functionality'),
		'personalization' => t('title_personalization', 'Personalization'),
		'security' => t('title_security', 'Security'),
		'measurement' => t('title_measurement', 'Measurement'),
		'marketing' => t('title_marketing', 'Marketing'),
	];
?>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_third_parties', 'Third Parties') ?>
		</div>
	</div>

	<div class="card-action">
		<ul class="list-inline">
			<li><?= f::form_button_link(document::ilink(__APP__.'/edit_third_party'), t('title_create_new_third_party', 'Create New Third Party'), '', 'create') ?></li>
		</ul>
	</div>

	<?= f::form_begin('third_parties_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check checkbox-toggle', 'data-toggle="checkbox-toggle"') ?></th>
					<th></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th><?= t('title_privacy_classes', 'Privacy Classes') ?></th>
					<th><?= t('title_country', 'Country') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($third_parties as $third_party) { ?>
				<tr>
					<td><?= f::form_checkbox('third_parties[]', $third_party['id']) ?></td>
					<td><?= f::draw_fonticon(!empty($third_party['status']) ? 'on' : 'off') ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_third_party', ['third_party_id' => $third_party['id']]) ?>"><?= $third_party['name'] ?></a></td>
					<td><?= implode(', ', array_map(function($class) use ($privacy_classes){ return $privacy_classes[$class]; }, preg_split('#\s*,\s*#', $third_party['privacy_classes'], -1, PREG_SPLIT_NO_EMPTY))) ?></td>
					<td class="text-center"><?= $third_party['country_code'] ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_third_party', ['third_party_id' => $third_party['id']], true) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php }?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_third_parties', 'Third Parties') ?>: <?= $num_rows ?>
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
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>