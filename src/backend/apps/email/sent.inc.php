<?php

	administrator::require_login();

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	document::$title[] = t('title_emails', 'Emails');

	breadcrumbs::add(t('title_email', 'Email'));
	breadcrumbs::add(t('title_sent_emails', 'Sent Emails'));

	if (!empty($_POST['delete'])) {
		if (!empty($_POST['emails'])) {
			database::query(
				"delete from " . DB_PREFIX . "emails
				where id in ('". implode("', '", database::input($_POST['emails'])) ."');",
			);
		}

		notices::add('success', t('success_changes_saved', 'Changes saved'));
		header('Location: ' . document::link());
		exit;
	}

	$emails = database::prepare(
		"select * from " . DB_PREFIX . "emails
		where status = 'sent'
		". (!empty($_GET['query'])? 'and ('. implode(PHP_EOL . 'or ', [
			"recipients like '%" . database::input_like($_GET['query']) . "%'",
			"subject like '%" . database::input_like($_GET['query']) . "%'",
			"multiparts like '%" . database::input_like($_GET['query']) . "%'"
		]) .')' : '') ."
		order by sent_at desc;",
	)->fetch_page(function(&$email) {

		$email['sender'] = json_decode($email['sender'], true);

		$email['recipients'] = $email['recipients']
			? array_map(function ($contact) {
				return $contact['name'] . ' <' . $contact['email'] . '>';
			}, json_decode($email['recipients'], true))
			: [];

		$email['ccs'] = $email['ccs']
			? array_map(function ($contact) {
				return $contact['name'] . ' <' . $contact['email'] . '>';
			}, json_decode($email['ccs'], true))
			: [];

		$email['bccs'] = $email['bccs']
			? array_map(function ($contact) {
				return $contact['name'] . ' <' . $contact['email'] . '>';
			}, json_decode($email['bccs'], true))
			: [];

		$email['multiparts'] = json_decode($email['multiparts'], true);

	}, null, $_GET['page'], null, $num_rows, $num_pages);

	$statuses = [
		'sent' => t('title_sent', 'Sent'),
		'scheduled' => t('title_scheduled', 'Scheduled'),
		'draft' => t('title_draft', 'Draft'),
		'cancelled' => t('title_cancelled', 'Cancelled'),
		'error' => t('title_error', 'Error'),
	];

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_sent_emails', 'Sent Emails') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__ . '/edit_email'), t('title_create_new_email', 'Create New Email'), '', 'create') ?>
	</div>

	<div class="card-filter">

		<?= f::form_begin('emails_form', 'get') ?>

			<ul class="list-inline">
				<li class="expandable">
					<?= f::form_input_search('query', true, ['placeholder' => t('text_search_phrase_or_keyword', 'Search phrase or keyword') , 'style' => 'width: 400px;']) ?>
				</li>
				<li>
					<?= f::form_button('search', t('title_search', 'Search'), 'submit') ?>
				</li>
			</ul>

		<?= f::form_end() ?>

	</div>

	<?= f::form_begin('emails_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th></th>
					<th><?= t('title_recipients', 'Recipients') ?></th>
					<th class="main"><?= t('title_subject', 'Subject') ?></th>
					<th><?= t('title_sent', 'Sent') ?></th>
					<th><?= t('title_created', 'Created') ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($emails as $email) { ?>
				<tr class="<?= empty($email['status']) ? 'semi-transparent' : '' ?>">
					<td><?= f::form_checkbox('emails[]', $email['id']) ?></td>
					<td><?= strtr($email['status'], $statuses) ?></td>
					<td><?= f::escape_html(implode(', ', array_column($email['recipients'], 'name'))) ?></td>
					<td>
						<a class="link" href="<?= document::href_ilink(__APP__ . '/view', ['email_id' => $email['id']]) ?>">
							<?= f::escape_html($email['subject']) ?>
						</a>
					</td>
					<td><?= f::datetime_format('datetime', $email['sent_at']) ?></td>
					<td><?= f::datetime_format('datetime', $email['created_at']) ?></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99"><?= t('title_emails', 'Emails') ?>: <?= f::format_number($num_rows) ?></td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">
				<legend><?= t('text_with_selected', 'With selected') ?>:</legend>

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
