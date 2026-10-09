<?php

	administrator::require_login();

	document::$title[] = t('title_not_found', 'Not Found');

	breadcrumbs::add(t('title_webtools', 'Webtools'));
	breadcrumbs::add(t('title_not_found', 'Not Found'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($_POST['not_found'])) {
				throw new Exception(t('error_must_select_not_found', 'You must select not found rows'));
			}

			database::query(
				"delete from ". DB_PREFIX ."not_found
				where id in (". implode(', ', array_map('intval', $_POST['not_found'])) .");"
			);

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$not_found_rows = database::query(
		"select * from ". DB_PREFIX ."not_found
		order by hits desc, last_requested desc, url asc;"
	)->fetch_page(function(&$row) {

		$row['redirect_link'] = document::ilink(__APP__.'/edit_redirect', [
			'status' => '1',
			'use_helper' => '1',
			'regex_helper' => [
				'domain' => [
					'operator' => '=',
					'criteria' => parse_url($row['url'], PHP_URL_HOST),
				],
				'path' => [
					'operator' => '=',
					'criteria' => parse_url($row['url'], PHP_URL_PATH),
				],
			],
			'destination' => document::ilink('f:'),
			'http_response_code' => 301,
		]);

	}, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_not_found', 'Not Found') ?>
		</div>
	</div>

	<?= f::form_begin('not_found_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check checkbox-toggle', 'data-toggle="checkbox-toggle"') ?></th>
					<th class="main"><?= t('title_url', 'URL') ?></th>
					<th><?= t('title_hits', 'Hits') ?></th>
					<th><?= t('title_last_requested', 'Last Requested') ?></th>
					<th><?= t('title_last_referrer', 'Last Referrer') ?></th>
					<th><?= t('title_last_ip_address', 'Last IP Address') ?></th>
					<th><?= t('title_last_hostname', 'Last Hostname') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($not_found_rows as $row) { ?>
				<tr>
					<td><?= f::form_checkbox('not_found[]', $row['id']) ?></td>
					<td><?= f::escape_html($row['url']) ?></td>
					<td class="text-end"><?= f::format_number($row['hits']) ?></td>
					<td class="text-end"><?= f::datetime_when($row['last_requested']) ?></td>
					<td><?= f::escape_html($row['last_referrer']) ?></td>
					<td class="text-center"><?= f::escape_html($row['last_ip_address']) ?></td>
					<td><?= f::escape_html($row['last_hostname']) ?></td>
					<td class="text-end">
						<a class="btn btn-default btn-sm" href="<?= f::escape_attr($row['redirect_link']) ?>">
							<?= t('title_create_redirect', 'Create Redirect') ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_not_found', 'Not Found') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions" disabled>

				<legend>
					<?= t('text_with_selected', 'With selected') ?>:
				</legend>

				<?= f::form_button_predefined('delete') ?>

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
