<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	document::$title[] = t('title_emails', 'Emails');

	breadcrumbs::add(t('title_emails', 'Emails'));

	if (!empty($_POST['delete'])) {
		if (!empty($_POST['emails'])) {
			database::query(
				"delete from " . DB_TABLE_PREFIX ."emails
				where id in ('" . implode("', '", database::input($_POST['emails'])) ."');",
			);
		}

		notices::add('success', t('success_changes_saved', 'Changes saved'));
		header('Location: ' . document::link());
		exit;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$emails = database::query(
		"select * from " . DB_TABLE_PREFIX ."emails
		where status = 'error'
		". (!empty($_GET['query']) ? 'and ('. implode(PHP_EOL . 'or ', ["recipients like '%" . database::input_like($_GET['query']) . "%'", "subject like '%" . database::input_like($_GET['query']) . "%'", "multiparts like '%" . database::input_like($_GET['query']) . "%'"]) .')' : '') ."
		order by sent_at desc;",
	)->fetch_page(function(&$email) {

		$email['sender'] = json_decode($email['sender'], true);

		$email['recipients'] = $email['recipients']
			? array_map(function($contact) {
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
			<?php echo $app_icon; ?> <?php echo t('title_failed_emails', 'Failed Emails'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo f::form_button_link(document::ilink(__APP__ . '/edit'), t('title_create_new_email', 'Create New Email'), '', 'create'); ?>
	</div>

	<div class="card-filter">

		<?php echo f::form_begin('emails_form', 'get'); ?>

			<ul class="list-inline">
				<li class="expandable">
					<?php echo f::form_input_search('query', true, ['placeholder' => t('text_search_phrase_or_keyword', 'Search phrase or keyword') , 'style' => 'width: 400px;']); ?>
				</li>
				<li>
					<?php echo f::form_button('search', t('title_search', 'Search'), 'submit'); ?>
				</li>
			</ul>

		<?php echo f::form_end(); ?>

	</div>

	<?php echo f::form_begin('emails_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th><?php echo t('title_recipients', 'Recipients'); ?></th>
					<th class="main"><?php echo t('title_subject', 'Subject'); ?></th>
					<th><?php echo t('title_sent', 'Sent'); ?></th>
					<th><?php echo t('title_created', 'Created'); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($emails as $email) { ?>
				<tr class="<?php echo empty($email['status']) ? 'semi-transparent' : ''; ?>">
					<td><?php echo f::form_checkbox('emails[]', $email['id']); ?></td>
					<td><?php echo strtr($email['status'], $statuses); ?></td>
					<td><?php echo f::escape_html(implode(', ', array_column($email['recipients'], 'name'))); ?></td>
					<td>
						<a class="link" href="<?php echo document::href_ilink(__APP__ . '/view', ['email_id' => $email['id']]); ?>">
							<?php echo f::escape_html($email['subject']); ?>
						</a>
					</td>
					<td><?php echo f::datetime_format('datetime', $email['sent_at']); ?></td>
					<td><?php echo f::datetime_format('datetime', $email['created_at']); ?></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_emails', 'Emails'); ?>: <?php echo f::format_number($num_rows); ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">
				<legend><?php echo t('text_with_selected', 'With selected'); ?>:</legend>

				<?php echo f::form_button_predefined('delete'); ?>
			</fieldset>
		</div>

	<?php echo f::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?php echo f::draw_pagination($num_pages); ?>
	</div>
	<?php } ?>
</div>

<script>
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>
