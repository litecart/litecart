<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	document::$title[] = language::translate('title_administrators', 'Administrators');

	breadcrumbs::add(language::translate('title_administrators', 'Administrators'));

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['administrators'])) {
				throw new Exception(language::translate('error_must_select_administrators', 'You must select administrators'));
			}

			foreach ($_POST['administrators'] as $administrator_id) {

				$administrator = new ent_administrator($administrator_id);
				$administrator->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$administrator->save();
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$administrators = database::query(
		"select * from ". DB_TABLE_PREFIX ."administrators
		order by username;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);
	foreach ($administrators as $key => $administrator) {
		try {

			if ($administrator['date_valid_from'] && $administrator['date_valid_from'] > date('Y-m-d H:i:s')) {
				throw new Exception(strtr(language::translate('text_acount_cannot_be_used_until_x', 'The account cannot be used until %datetime'), ['%datetime' => language::strftime(language::$selected['format_datetime'], strtotime($administrator['date_valid_from']))]));
			}

			if ($administrator['date_valid_to'] && $administrator['date_valid_to'] > 1970 && $administrator['date_valid_to'] < date('Y-m-d H:i:s')) {
				throw new Exception(strtr(language::translate('text_account_expired_at_x', 'The account expired at %datetime and can no longer be used'), ['%datetime' => language::strftime(language::$selected['format_datetime'], strtotime($administrator['date_valid_to']))]));
			}

			$administrators[$key]['warning'] = null;

		} catch (Exception $e) {
			$administrators[$key]['warning'] = $e->getMessage();
		}
	}

?>
<style>
.warning {
	color: #f00;
}
</style>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_administrators', 'Administrators'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_administrator'), language::translate('title_create_new_administrator', 'Create New Administrator'), '', 'add'); ?>
	</div>

	<?php echo functions::form_begin('administrators_form', 'post'); ?>

		<table class="table table-striped table-hover data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th></th>
					<th><?php echo language::translate('title_username', 'Username'); ?></th>
					<th class="main"><?php echo language::translate('title_email', 'Email'); ?></th>
					<th><?php echo language::translate('title_restrictions', 'Restrictions'); ?></th>
					<th class="text-end" style="min-width: 200px;"><?php echo language::translate('title_valid_from', 'Valid From'); ?></th>
					<th class="text-end" style="min-width: 200px;"><?php echo language::translate('title_valid_to', 'Valid To'); ?></th>
					<th class="text-end"><?php echo language::translate('title_last_login', 'Last Login'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($administrators as $administrator) { ?>
				<tr class="<?php echo empty($administrator['status']) ? 'semi-transparent' : ''; ?>">
					<td><?php echo functions::form_checkbox('administrators[]', $administrator['id']); ?></td>
					<td><?php echo functions::draw_fonticon($administrator['status'] ? 'on' : 'off'); ?></td>
					<td class="warning"><?php echo !empty($administrator['warning']) ? functions::draw_fonticon('fa-exclamation-triangle', 'title="'. functions::escape_html($administrator['warning']) .'"') : ''; ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_administrator', ['administrator_id' => $administrator['id']]); ?>"><?php echo $administrator['username']; ?></a></td>
					<td><?php echo $administrator['email']; ?></td>
					<td><?php echo (json_decode($administrator['apps'], true)) ? language::translate('title_restricted', 'Restricted') : '-'; ?></td>
					<td class="text-end"><?php echo ($administrator['date_valid_from'] > 1970) ? language::strftime(language::$selected['format_datetime'], strtotime($administrator['date_valid_from'])) : '-'; ?></td>
					<td class="text-end"><?php echo ($administrator['date_valid_to'] > 1970) ? language::strftime(language::$selected['format_datetime'], strtotime($administrator['date_valid_to'])) : '-'; ?></td>
					<td class="text-end"><?php echo ($administrator['date_login'] > 1970) ? language::strftime(language::$selected['format_datetime'], strtotime($administrator['date_login'])) : '-'; ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_administrator', ['administrator_id' => $administrator['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="10"><?php echo language::translate('title_administrators', 'Administrators'); ?>: <?php echo language::number_format($num_rows); ?></td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">
				<legend><?php echo language::translate('text_with_selected', 'With selected'); ?>:</legend>

				<div class="btn-group">
					<?php echo functions::form_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
					<?php echo functions::form_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
				</div>
			</fieldset>
		</div>

	<?php echo functions::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?php echo functions::draw_pagination($num_pages); ?>
	</div>
	<?php } ?>
</div>

<script>
	$('.data-table :checkbox').change(function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>