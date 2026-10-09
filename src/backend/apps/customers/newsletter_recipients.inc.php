<?php

	administrator::require_login();

	document::$title[] = t('title_newsletter_recipients', 'Newsletter Recipients');

	breadcrumbs::add(t('title_customers', 'Customers'), document::ilink(__APP__.'/customers'));
	breadcrumbs::add(t('title_newsletter_recipients', 'Newsletter Recipients'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (isset($_POST['add'])) {

		try {

			if (empty($_POST['recipients'])) {
				throw new Exception(t('error_must_provide_recipients', 'You must provide recipients'));
			}

			$added = 0;
			$updated = 0;

			foreach (preg_split('#\R+#', $_POST['recipients']) as $recipient) {
				if (!f::validate_email($recipient)) continue;

				if (database::query(
					"select *, concat(firstname, ' ', lastname) as name
					from ". DB_PREFIX ."newsletter_recipients
					where email = '". database::input(strtolower($recipient)) ."'
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
				$newsletter_recipient->data['hostname'] = reverse_dns($_SERVER['REMOTE_ADDR']);
				$newsletter_recipient->data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

				$newsletter_recipient->save();
			}

			notices::add('success', strtr(t('success_added_n_new_recipients', 'Added {n} new recipients'), [
				'{n}' => $added
			]));

			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['subscribe']) || isset($_POST['unsubscribe'])) {

		try {

			if (empty($_POST['recipients'])) {
				throw new Exception(t('error_must_select_recipients', 'You must select recipients'));
			}

			$newsletter_recipient = new ent_newsletter_recipient($recipient);
			$newsletter_recipient->data['subscribed'] = isset($_POST['subscribe']) ? 1 : 0;
			$newsletter_recipient->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($_POST['recipients'])) {
				throw new Exception(t('error_must_select_recipients', 'You must select recipients'));
			}

			$newsletter_recipient = new ent_newsletter_recipient($recipient);
			$newsletter_recipient->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (!empty($_GET['action']) && $_GET['action'] == 'export') {

		ob_clean();

		header('Content-Type: text/plain; charset='. mb_http_output());

		database::query(
			"select email from ". DB_PREFIX ."newsletter_recipients
			where true
			". ((isset($_GET['subscribed']) && $_GET['subscribed'] != '') ? "and subscribed = ". (int)$_GET['subscribed'] ."" : "") ."
			". (!empty($_GET['query']) ? "and c.email like '%". database::input($_GET['query']) ."%'" : "") ."
			order by created_at desc;"
		)->each(function($recipient) {
			echo $recipient['email'] . PHP_EOL;
		});

		exit;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$recipients = database::query(
		"select * from ". DB_PREFIX ."newsletter_recipients
		where true
		". (!empty($_GET['query']) ? "and email like '%". database::input($_GET['query']) ."%'" : "") ."
		". ((isset($_GET['subscribed']) && $_GET['subscribed'] != '') ? "and subscribed = ". (int)$_GET['subscribed'] ."" : "") ."
		order by created_at desc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);
	$filter_options = [
		['', '-- '. t('title_all_recipients', 'All Recipients')],
		['1', t('title_subscribed', 'Subscribed')],
		['0', t('title_unsubscribed', 'Unsubscribed')],
	];

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_newletter_recipients', 'Newsletter Recipients') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button('add_recipients', t('title_add_recipients', 'Add Recipients'), 'button', '', 'create') ?>
		<?= f::form_button_link(document::ilink(null, ['action' => 'export']), t('title_export', 'Export'), 'target="_blank"', 'icon-output') ?>
	</div>

	<?= f::form_begin('search_form', 'get') ?>
		<div class="card-filter">
			<div class="expandable"><?= f::form_input_search('query', true, ['placeholder' => t('text_search_phrase_or_keyword', 'Search phrase or keyword')]) ?></div>
			<div><?= f::form_button('filter', t('title_search', 'Search'), 'submit') ?></div>
		</div>
	<?= f::form_end() ?>

	<?= f::form_begin('recipients_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center" style="width: 50px;"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th><?= t('title_subscribed', 'Subscribed') ?></th>
					<th style="width: 480px;"><?= t('title_email', 'Email') ?></th>
					<th class="main"><?= t('title_person_name', 'Name') ?></th>
					<th><?= t('title_ip_address', 'IP Address') ?></th>
					<th style="width: 200px;"><?= t('title_hostname', 'Hostname') ?></th>
					<th class="text-end" style="width: 200px;"><?= t('title_updated_at', 'Updated At') ?></th>
					<th class="text-end" style="width: 200px;"><?= t('title_created_at', 'Created At') ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($recipients as $recipient) { ?>
				<tr>
					<td><?= f::form_checkbox('recipients[]', $recipient['id']) ?></td>
					<td class="text-center"><?= !empty($recipient['subscribed']) ? f::draw_fonticon('true') : f::draw_fonticon('false') ?></td>
					<td><?= $recipient['email'] ?></td>
					<td><?= f::escape_html($recipient['name']) ?></td>
					<td><?= $recipient['ip_address'] ?></td>
					<td><?= $recipient['hostname'] ?></td>
					<td class="text-end"><?= f::datetime_when($recipient['updated_at']) ?></td>
					<td class="text-end"><?= f::datetime_when($recipient['created_at']) ?></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_recipients', 'Recipients') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions" disabled>

				<legend>
					<?= t('text_with_selected', 'With selected') ?>:
				</legend>

				<div class="flex">

					<div class="btn-group">
						<?= f::form_button('subscribe', t('title_set_as_subscribed', 'Set As Subscribed'), 'submit', ['class' => 'btn btn-default'], 'icon-check') ?>
						<?= f::form_button('unsubscribe', t('title_set_as_unsubscribed', 'Set As Unsubscribed'), 'submit', ['class' => 'btn btn-default'], 'remove') ?>
					</div>

					<?= f::form_button_predefined('delete') ?>

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

<div id="modal-add-recipients" class="modal fade" style="width: 640px; display: none;">
	<?= f::form_begin('recipients_form', 'post') ?>

		<label class="form-group">
			<div class="form-label"><?= t('title_recipients', 'Recipients') ?></div>
			<?= f::form_textarea('recipients', '', ['style' => 'height: 480px;']) ?>
		</label>

		<label class="form-group">
			<div class="form-label"><?= t('title_subscribed', 'Subscribed') ?></div>
			<?= f::form_toggle('subscribe', [1 => t('title_subscribe', 'Subscribed'), 0 => t('title_unsubscribe', 'Unsubscribed')], '1') ?>
		</label>

		<?= f::form_button('add', t('title_add', 'Add'), 'submit', ['class' => 'btn btn-default btn-block']) ?>

	<?= f::form_end() ?>
</div>

<script>
	$('button[name="add_recipients"]').on('click', function() {
		$.litebox('#modal-add-recipients');
		$('textarea[name="recipients"]').attr('placeholder', 'user@email.com\nanother@email.com');
	});

	$('select[name="subscribed"]').on('change', function() {
		$(this).closes('form').submit();
	});

	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>
