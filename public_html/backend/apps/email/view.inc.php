<?php

	if (empty($_GET['email_id']) || !is_numeric($_GET['email_id'])) {
		notices::add('errors', t('error_must_provide_email', 'You must provide an email'));
	}

	$email = database::query(
		"select * from ". DB_TABLE_PREFIX ."emails
		where id = ". (int)$_GET['email_id'] ."
		limit 1;"
	)->fetch(function (&$email) {

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
	});

?>
<style>
	.head > * {
		margin: 1em 0;
	}

	.body {
		padding: 1em;
		border: 1px solid var(--default-border-color);
		border-radius: var(--border-radius);
		background: #fcfcfc;
		font-family: monospace;
		white-space: pre-line;
	}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_email', 'Email'); ?>
		</div>
	</div>

	<div class="card-body">

		<div style="max-width: 720px;">

			<div class="head">
				<div class="from">
					<strong><?php echo t('title_from', 'From'); ?>:</strong> <?php echo f::escape_html($email['sender']['name'] . ' <' . $email['sender']['email'] . '>'); ?>
				</div>

				<div class="to">
				<strong><?php echo t('title_to', 'To'); ?>:</strong> <?php echo f::escape_html(implode(', ', $email['recipients'])); ?>
				</div>

				<div class="ccs">
				<strong><?php echo t('title_cc', 'CC'); ?>:</strong> <?php echo f::escape_html(implode(', ', $email['ccs'])); ?>
				</div>

				<div class="bccs">
				<strong><?php echo t('title_bcc', 'BCC'); ?>:</strong> <?php echo f::escape_html(implode(', ', $email['bccs'])); ?>
				</div>

				<div class="subject">
				<strong><?php echo t('title_subject', 'Subject'); ?>:</strong> <?php echo f::escape_html($email['subject']); ?>
				</div>
			</div>

			<div class="body">
				<?php foreach ($email['multiparts'] as $multipart) echo nl2br(f::escape_html($multipart['body'])); ?>
			</div>

		</div>

	</div>
</div>
