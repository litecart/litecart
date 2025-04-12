<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['redirects'])) {
				throw new Exception(language::translate('error_must_select_redirects', 'You must select redirects'));
			}

			foreach ($_POST['redirects'] as $redirect_id) {

				$redirect = new ent_redirect($redirect_id);
				$redirect->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$redirect->save();
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($currency->data['id'])) {
				throw new Exception(language::translate('error_must_provide_currency', 'You must provide a currency'));
			}

			if (empty($_POST['redirects'])) {
				throw new Exception(language::translate('error_must_select_redirects', 'You must select redirects'));
			}

			foreach ($_POST['redirects'] as $redirect_id) {
				$redirect = new ent_redirect($redirect_id);
				$redirect->delete();
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/redirects'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$redirects = database::query(
		"select * from ". DB_TABLE_PREFIX ."redirects
		order by status desc, pattern asc, destination asc;"
	)->fetch_page(null, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_redirects', 'Redirects'); ?>
		</div>
	</div>

	<div class="card-action">
		<ul class="list-inline pull-right">
			<li><?php echo functions::form_button_link(document::ilink(__APP__.'/edit_redirect'), language::translate('title_create_new_redirect', 'Create New Redirect'), '', 'add'); ?></li>
		</ul>
	</div>

	<?php echo functions::form_begin('redirects_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th><?php echo language::translate('title_pattern', 'Pattern'); ?> (Regex)</th>
					<th class="main"><?php echo language::translate('title_destination', 'Destination'); ?></th>
					<th><?php echo language::translate('title_redirects', 'Redirects'); ?></th>
					<th><?php echo language::translate('title_last_redirected', 'Last Redirected'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($redirects as $redirect) { ?>
				<tr class="<?php echo empty($redirect['status']) ? 'semi-transparent' : null; ?>">
					<td><?php echo functions::form_checkbox('redirects[]', $redirect['id']); ?></td>
					<td><?php echo functions::draw_fonticon(!empty($redirect['status']) ? 'on' : 'off'); ?></td>
					<td><a href="<?php echo document::href_ilink(__APP__.'/edit_redirect', ['redirect_id' => $redirect['id']]); ?>"><?php echo $redirect['pattern']; ?></a></td>
					<td><?php echo $redirect['destination']; ?></td>
					<td class="text-end"><?php echo $redirect['redirects']; ?></td>
					<td class="text-end"><?php echo $redirect['date_redirected'] ? functions::datetime_when($redirect['date_redirected']) : '-'; ?></td>
					<td class="text-end"><a href="<?php echo document::href_ilink(__APP__.'/edit_redirect', ['redirect_id' => $redirect['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="7"><?php echo language::translate('title_redirects', 'Redirects'); ?>: <?php echo $num_rows; ?></td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">
				<legend><?php echo language::translate('text_with_selected', 'With selected'); ?></legend>

				<div class="btn-group">
					<?php echo functions::form_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
					<?php echo functions::form_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
				</div>

				<?php echo functions::form_button_predefined('delete'); ?>
			</fieldset>
		</div>

	<?php echo functions::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
		<div class="card-footer">
			<?php echo functions::draw_pagination($num_pages); ?>
		</div>
	<?php } ?>
</div>
