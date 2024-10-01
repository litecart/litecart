<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (isset($_POST['add'])) {

		try {

			if (empty($_POST['recipients'])) {
				throw new Exception(language::translate('error_must_provide_recipients', 'You must provide recipients'));
			}

			$added = 0;
			foreach (preg_split('#\R+#', $_POST['recipients']) as $recipient) {
				if (!functions::validate_email($recipient)) continue;

				database::query(
					"insert ignore into ". DB_TABLE_PREFIX ."newsletter_recipients
					(email, date_created)
					values ('". database::input($recipient) ."', '". date('Y-m-d H:i:s') ."');"
				);

				if (database::affected_rows()) $added++;
			}

			notices::add('success', strtr(language::translate('success_added_n_new_recipients', 'Added %n new recipients'), ['%n' => $added]));
			header('Location: '. document::ilink());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($_POST['recipients'])) {
				throw new Exception(language::translate('error_must_select_recipients', 'You must select recipients'));
			}

			database::query(
				"delete from ". DB_TABLE_PREFIX ."newsletter_recipients
				where id in ('". implode("', '", database::input($_POST['recipients'])) ."');"
			);

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (!empty($_GET['action']) && $_GET['action'] == 'export') {

		ob_clean();

		header('Content-Type: text/plain; charset='. mb_http_output());

		database::query(
			"select email from ". DB_TABLE_PREFIX ."newsletter_recipients
			where id
			". (!empty($_GET['query']) ? "and c.email like '%". database::input($_GET['query']) ."%'" : "") ."
			order by date_created desc;"
		)->each(function($recipient) {
			echo $recipient['email'] . PHP_EOL;
		});

		exit;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$recipients = database::query(
		"select * from ". DB_TABLE_PREFIX ."newsletter_recipients
		where id
		". (!empty($_GET['query']) ? "and email like '%". database::input($_GET['query']) ."%'" : "") ."
		order by date_created desc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

	functions::draw_lightbox();
?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_newletter_recipients', 'Newsletter Recipients'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(null, ['action' => 'export']), language::translate('title_export', 'Export'), 'target="_blank"'); ?>
	</div>

	<?php echo functions::form_begin('search_form', 'get'); ?>
		<div class="card-filter">
			<div class="expandable"><?php echo functions::form_input_search('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"'); ?></div>
			<div><?php echo functions::form_button('filter', language::translate('title_search', 'Search'), 'submit'); ?></div>
		</div>
	<?php echo functions::form_end(); ?>

	<?php echo functions::form_begin('recipients_form', 'post'); ?>

		<table class="table table-striped table-hover data-table">
			<thead>
				<tr>
					<th style="width: 50px;"><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
					<th style="width: 50px;"><?php echo language::translate('title_id', 'ID'); ?></th>
					<th style="width: 480px;"><?php echo language::translate('title_email', 'Email'); ?></th>
					<th><?php echo language::translate('title_name', 'Name'); ?></th>
					<th style="width: 200px;"><?php echo language::translate('title_hostname', 'Hostname'); ?></th>
					<th class="text-end" style="width: 200px;"><?php echo language::translate('title_date_registered', 'Date Registered'); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($recipients as $recipient) { ?>
				<tr>
					<td><?php echo functions::form_checkbox('recipients[]', $recipient['id']); ?></td>
					<td><?php echo $recipient['id']; ?></td>
					<td><?php echo $recipient['email']; ?></td>
					<td><?php echo $recipient['name']; ?></td>
					<td><?php echo $recipient['hostname']; ?></td>
					<td class="text-end"><?php echo language::strftime('datetime', $recipient['date_created']); ?></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="6"><?php echo language::translate('title_recipients', 'Recipients'); ?>: <?php echo language::number_format($num_rows); ?></td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions" disabled>
				<legend><?php echo language::translate('text_with_selected', 'With selected'); ?>:</legend>

			<div class="btn-group">
				<?php echo functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'class="btn btn-danger"', 'delete'); ?>
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

<div id="modal-add-recipients" class="modal fade" style="width: 640px; display: none;">
	<?php echo functions::form_begin('recipients_form', 'post'); ?>

		<div class="form-group">
			<label><?php echo language::translate('title_recipients', 'Recipients'); ?></label>
			<?php echo functions::form_input_textarea('recipients', '', 'style="height: 480px;"'); ?>
		</div>

		<?php echo functions::form_button('add', language::translate('title_add', 'Add'), 'submit', 'class="btn btn-default btn-block"'); ?>

	<?php echo functions::form_end(); ?>
</div>

<script>
	$('button[name="add_recipients"]').on('click', function(){
		$.featherlight('#modal-add-recipients');
		$('textarea[name="recipients"]').attr('placeholder', 'user@email.com\nanother@email.com');
	})

	$('.data-table :checkbox').change(function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>
