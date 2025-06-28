<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	document::$title[] = t('title_administrators', 'Administrators');

	breadcrumbs::add(t('title_administrators', 'Administrators'), document::ilink());

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['administrators'])) {
				throw new Exception(t('error_must_select_administrators', 'You must select administrators'));
			}

			foreach ($_POST['administrators'] as $administrator_id) {

				$administrator = new ent_administrator($administrator_id);
				$administrator->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$administrator->save();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$administrators = database::query(
		"select *, concat(firstname, ' ', lastname) as name
		from ". DB_TABLE_PREFIX ."administrators
		order by username;"
	)->fetch_page(function($administrator){

		try {

			if ($administrator['valid_from'] && $administrator['valid_from'] > date('Y-m-d H:i:s')) {
				throw new Exception(strtr(t('text_acount_cannot_be_used_until_x', 'The account cannot be used until %datetime'), ['%datetime' => functions::datetime_format('datetime', $administrator['valid_from'])]));
			}

			if ($administrator['valid_to'] && $administrator['valid_to'] < date('Y-m-d H:i:s')) {
				throw new Exception(strtr(t('text_account_expired_at_x', 'The account expired at %datetime and can no longer be used'), ['%datetime' => functions::datetime_format('datetime', $administrator['valid_to'])]));
			}

			$administrator['warning'] = null;

		} catch (Exception $e) {
			$administrator['warning'] = $e->getMessage();
		}

		return $administrator;

	}, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<style>
.warning {
	color: #f00;
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_administrators', 'Administrators'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_administrator'), t('title_create_new_administrator', 'Create New Administrator'), '', 'create'); ?>
	</div>

	<?php echo functions::form_begin('administrators_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th></th>
					<th><?php echo t('title_username', 'Username'); ?></th>
					<th><?php echo t('title_name', 'Name'); ?></th>
					<th class="main"><?php echo t('title_email', 'Email'); ?></th>
					<th><?php echo t('title_restrictions', 'Restrictions'); ?></th>
					<th class="text-end" style="min-width: 200px;"><?php echo t('title_valid_from', 'Valid From'); ?></th>
					<th class="text-end" style="min-width: 200px;"><?php echo t('title_valid_to', 'Valid To'); ?></th>
					<th class="text-end"><?php echo t('title_last_login', 'Last Login'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($administrators as $administrator) { ?>
				<tr class="<?php echo empty($administrator['status']) ? 'semi-transparent' : ''; ?>">
					<td><?php echo functions::form_checkbox('administrators[]', $administrator['id']); ?></td>
					<td><?php echo functions::draw_fonticon($administrator['status'] ? 'on' : 'off'); ?></td>
					<td class="warning"><?php echo !empty($administrator['warning']) ? functions::draw_fonticon('icon-exclamation-triangle', 'title="'. functions::escape_html($administrator['warning']) .'"') : ''; ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_administrator', ['administrator_id' => $administrator['id']]); ?>"><?php echo $administrator['username']; ?></a></td>
					<td><?php echo $administrator['name']; ?></td>
					<td><?php echo $administrator['email']; ?></td>
					<td><?php echo (json_decode($administrator['apps'], true)) ? t('title_restricted', 'Restricted') : '-'; ?></td>
					<td class="text-end"><?php echo $administrator['valid_from'] ? functions::datetime_when($administrator['valid_from']) : '-'; ?></td>
					<td class="text-end"><?php echo $administrator['valid_to'] ? functions::datetime_when($administrator['valid_to']) : '-'; ?></td>
					<td class="text-end"><?php echo $administrator['last_login'] ? functions::datetime_when($administrator['last_login']) : '-'; ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_administrator', ['administrator_id' => $administrator['id']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_administrators', 'Administrators'); ?>: <?php echo language::number_format($num_rows); ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">

				<legend>
					<?php echo t('text_with_selected', 'With selected'); ?>:
				</legend>

				<div class="btn-group">
					<?php echo functions::form_button('enable', t('title_enable', 'Enable'), 'submit', '', 'on'); ?>
					<?php echo functions::form_button('disable', t('title_disable', 'Disable'), 'submit', '', 'off'); ?>
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
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>