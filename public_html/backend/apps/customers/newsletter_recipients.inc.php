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
			$updated = 0;

			foreach (preg_split('#\R+#', $_POST['recipients']) as $recipient) {
				if (!functions::validate_email($recipient)) continue;

				if (database::query(
					"select * from ". DB_TABLE_PREFIX ."newsletter_recipients
					where email = '". database::input(strtolower($recipient_id)) ."'
					limit 1;"
				)->num_rows) {
					$newsletter_recipient = new ent_newsletter_recipient($recipient);
					$updated++;
				} else {
					$newsletter_recipient = new ent_newsletter_recipient();
					$added++;
				}

				foreach ([
					'subscribed',
					'email',
				] as $field) {
					if (isset($_POST[$field])) {
						$newsletter_recipient->data[$field] = $_POST[$field];
					}
				}

				$newsletter_recipient->data['client_id'] = $_SERVER['REMOTE_ADDR'];
				$newsletter_recipient->data['hostname'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
				$newsletter_recipient->data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

				$newsletter_recipient->save();
			}

			notices::add('success', strtr(language::translate('success_added_n_new_recipients', 'Added %n new recipients'), ['%n' => $added]));
			header('Location: '. document::ilink());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['subscribe']) || isset($_POST['unsubscribe'])) {

		try {

			if (empty($_POST['recipients'])) {
				throw new Exception(language::translate('error_must_select_recipients', 'You must select recipients'));
			}

			$newsletter_recipient = new ent_newsletter_recipient($recipient);
			$newsletter_recipient->data['subscribed'] = isset($_POST['subscribe']) ? 1 : 0;
			$newsletter_recipient->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
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

			$newsletter_recipient = new ent_newsletter_recipient($recipient);
			$newsletter_recipient->delete();

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
			where true
			". ((isset($_GET['subscribed']) && $_GET['subscribed'] != '') ? "and subscribed = ". (int)$_GET['subscribed'] ."" : "") ."
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
		where true
		". (!empty($_GET['query']) ? "and email like '%". database::input($_GET['query']) ."%'" : "") ."
		". ((isset($_GET['subscribed']) && $_GET['subscribed'] != '') ? "and subscribed = ". (int)$_GET['subscribed'] ."" : "") ."
		order by date_created desc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);
	$filter_options = [
		['', '-- '. language::translate('title_all_recipients', 'All Recipients')],
		['1', language::translate('title_subscribed', 'Subscribed')],
		['0', language::translate('title_unsubscribed', 'Unsubscribed')],
	];

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
					<th style="width: 50px;"><?php echo functions::draw_fonticon('icon-check-square-o', 'data-toggle="checkbox-toggle"'); ?></th>
					<th><?php echo language::translate('title_subscribed', 'Subscribed'); ?></th>
					<th style="width: 480px;"><?php echo language::translate('title_email', 'Email'); ?></th>
					<th><?php echo language::translate('title_name', 'Name'); ?></th>
					<th><?php echo language::translate('title_ip_address', 'IP Address'); ?></th>
					<th style="width: 200px;"><?php echo language::translate('title_hostname', 'Hostname'); ?></th>
					<th class="text-end" style="width: 200px;"><?php echo language::translate('title_date_registered', 'Date Registered'); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($recipients as $recipient) { ?>
				<tr>
					<td><?php echo functions::form_checkbox('recipients[]', $recipient['id']); ?></td>
					<td class="text-center"><?php echo !empty($recipient['subscribed']) ? functions::draw_fonticon('fa-check', 'style="color: #88cc44;"') : functions::draw_fonticon('fa-times', 'style="color: #ff6644;"'); ?></td>
					<td><?php echo $recipient['email']; ?></td>
					<td><?php echo functions::escape_html($recipient['name']); ?></td>
					<td><?php echo $recipient['ip_address']; ?></td>
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

				<div class="flex flex-inline">
					<div class="btn-group">
							<?php echo functions::form_button('subscribe', language::translate('title_set_as_subscribed', 'Set As Subscribed'), 'submit', 'class="btn btn-default"', 'fa-check'); ?>
							<?php echo functions::form_button('unsubscribe', language::translate('title_set_as_unsubscribed', 'Set As Unsubscribed'), 'submit', 'class="btn btn-default"', 'fa-times'); ?>
					</div>

					<?php echo functions::form_button_predefined('delete'); ?>
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
			<?php echo functions::form_textarea('recipients', '', 'style="height: 480px;"'); ?>
		</div>

		<div class="form-group">
			<?php echo functions::form_toggle('subscribe', [1 => language::translate('title_subscribe', 'Subscribed'), 0 => language::translate('title_unsubscribe', 'Unsubscribed')]); ?>
			<div class="btn-group btn-block btn-group-inline" data-toggle="buttons">
				<label class="btn btn-default<?php echo (file_get_contents('php://input') == '' || !empty($_POST['subscribe'])) ? ' active' : ''; ?>">
					<?php echo functions::form_radio_button('subscribe', '1', true); ?> <?php echo language::translate('title_subscribe', 'Subscribed'); ?>
				</label>
				<label class="btn btn-default<?php echo (file_get_contents('php://input') != '' && empty($_POST['subscribe'])) ? ' active' : ''; ?>">
					<?php echo functions::form_radio_button('subscribe', '0', true); ?><?php echo language::translate('title_unsubscribe', 'Unsubscribed'); ?>
				</label>
			</div>
		</div>

		<?php echo functions::form_button('add', language::translate('title_add', 'Add'), 'submit', 'class="btn btn-default btn-block"'); ?>

	<?php echo functions::form_end(); ?>
</div>

<script>
	$('button[name="add_recipients"]').on('click', function(){
		$.featherlight('#modal-add-recipients');
		$('textarea[name="recipients"]').attr('placeholder', 'user@email.com\nanother@email.com');
	})

	$('select[name="subscribed"]').change(function(){
		$(this).closes('form').submit();
	});

	$('.data-table :checkbox').change(function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>
