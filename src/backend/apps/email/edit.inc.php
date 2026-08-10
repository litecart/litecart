<?php

	breadcrumbs::add(t('title_email', 'Email'));

	try {

		if (!empty($_GET['email_id'])) {
			$email = new ent_email($_GET['email_id']);
			document::$title[] = t('title_edit_email', 'Edit Email');
			breadcrumbs::add(t('title_edit_email', 'Edit Email'));
		} else {
			$email = new ent_email();
			document::$title[] = t('title_create_new_email', 'Create New Email');
			breadcrumbs::add(t('title_create_new_email', 'Create New Email'));
		}

		if (!$_POST) {
			$_POST = $email->data;

			$_POST['recipients'] = $email->data['recipients']
				? array_map(function ($contact) {
					return $contact['name'] . ' <' . $contact['email'] . '>';
				}, json_decode($email->data['recipients'], true))
				: [];

			$_POST['ccs'] = $email->data['ccs']
				? array_map(function ($contact) {
					return $contact['name'] . ' <' . $contact['email'] . '>';
				}, json_decode($email->data['ccs'], true))
				: [];

			$_POST['bccs'] = $email->data['bccs']
				? array_map(function ($contact) {
					return $contact['name'] . ' <' . $contact['email'] . '>';
				}, json_decode($email->data['bccs'], true))
				: [];

			$_POST['sender'] = $email->data['sender']['name'] . ' <' . $email->data['sender']['email'] . '>';
		}

	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
		return;
	}

	if (!empty($_POST['save']) && !empty($_POST['send'])) {
		try {

			foreach ([
				'from',
				'recipients',
				'ccs',
				'bccs',
				'subject',
				'multiparts',
				'scheduled_at',
			] as $field) {
				if (isset($_POST[$field])) {
					$email->data[$field] = $_POST[$field];
				}
			}

			$email->save();

			if (!empty($_POST['send'])) {
				if (empty($_POST['scheduled_at']) || strtotime($_POST['scheduled_at']) <= time()) {
					$email->queue($_POST['scheduled_at']);
				} else {
					$email->save();
				}
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			header('Location: ' . document::link());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (!empty($_POST['delete'])) {
		try {

			if (empty($email->data['id'])) {
				throw new Exception(t('error_must_provide_email', 'You must provide an email'));
			}

			$email->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			header('Location: ' . document::link());
			exit;
		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$senders = [
		f::escape_html(
			$email->format_contact([
				'name' => settings::get('store_name'),
				'email' => settings::get('store_email'),
			]),
		),
	];

	$_layout = new ent_view('app://frontend/templates/'. settings::get('template') .'/layouts/email.inc.php');
	$_layout->snippets = [
		'content' => '{{content}}',
		'language_code' => language::$selected['code'],
		'text_direction' => language::$languages[language::$selected['code']]['direction'] ?? 'ltr',
	];

	$html = $_layout->render();

?>
<style>
.head > * {
	margin: 1em 0;
}

#preview {
	border: 1px solid var(--default-border-color);
	width: 100%;
	min-height: 300px;
	height: 500px;
	background: #fff;
	border-radius: var(--border-radius);
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_email', 'Email'); ?>
		</div>
	</div>

	<div class="card-body">

		<?php echo f::form_begin('email_form', 'post'); ?>

			<div class="row">
				<div class="col-md-6">

					<label class="form-group">
						<div class="form-label"><?php echo t('title_scheduled_at', 'Scheduled At'); ?></div>
						<?php echo f::form_input_datetime('scheduled_at', true); ?>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_from', 'From'); ?></div>
						<?php echo f::form_select('sender', $senders, true); ?>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_to', 'To'); ?></div>
						<?php echo f::form_input_tags('recipients[]', true); ?>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_cc', 'CC'); ?></div>
						<?php echo f::form_input_tags('ccs[]', true); ?>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_bcc', 'BCC'); ?></div>
						<?php echo f::form_input_tags('bccs[]', true); ?>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_headers', 'Headers'); ?></div>
						<?php echo f::form_textarea('multiparts[0][headers]', true); ?>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_subject', 'Subject'); ?></div>
						<?php echo f::form_input_text('subject', true); ?>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_body', 'Body'); ?></div>
						<?php echo f::form_input_wysiwyg('multiparts[0][body]', true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_preview', 'Preview'); ?></div>
						<iframe id="preview"></iframe>
					</label>
				</div>
			</div>

			<div class="card-action">
				<?php echo f::form_button_predefined('save'); ?>
				<?php echo f::form_button_predefined('send'); ?>
				<?php echo $email->data['id'] ? f::form_button_predefined('delete') : ''; ?>
				<?php echo f::form_button_predefined('cancel'); ?>
			</div>

		<?php echo f::form_end(); ?>
	</div>
</div>

<script>
	var template = `<?php echo f::escape_js($html); ?>`;

	$('textarea[name="multiparts[0][body]"]').on('input', function(){
		let content = template.replace(/\{\{content\}\}/g, $(this).val());
		let iframeDoc = $('#preview')[0].contentDocument || $('#preview')[0].contentWindow.document;
		iframeDoc.body.innerHTML = content;
	});
</script>