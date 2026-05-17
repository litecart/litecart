<?php

	try {

		if (!empty($_GET['request_id'])) {
			$request = new ent_webhook_request($_GET['request_id']);
		} else {
			$request = new ent_webhook_request();
		}

		if (!$_POST) {
			$_POST = $request->data;
		}

	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
		return;
	}

	breadcrumbs::add(!empty($request->data['id']) ? t('title_edit_request', 'Edit Request') : t('title_create_new_request', 'Create New Request'));

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['status'])) {
				$_POST['status'] = 0;
			}

			foreach ([
				'status',
				'method',
				'url',
				'headers',
				'body',
				'scheduled_at',
			] as $field) {
				if (isset($_POST[$field])) {
					$request->data[$field] = $_POST[$field];
				}
			}

			$request->save();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::link('', ['doc' => 'requests'], ['app']));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($request->data['id'])) {
				throw new Exception(t('error_must_provide_url', 'You must provide a url'));
			}

			$request->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::link('', ['doc' => 'requests'], ['app']));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$status_options = [
		'pending' => t('title_pending', 'Pending'),
		'pending_retry' => t('title_pending_retry', 'Pending Retry'),
		'delivered' => t('title_delivered', 'Delivered'),
		'failed' => t('title_failed', 'Failed'),
		'cancelled' => t('title_cancelled', 'Cancelled'),
	];

	$method_options = [
		'GET',
		'PATCH',
		'POST',
		'PUT',
		'DELETE',
	];

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($request->data['id']) ? t('title_edit_request', 'Edit Request') : t('title_create_new_request', 'Create New Request'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo f::form_begin('request_form', 'post', false, false, ['autocomplete' => 'off', 'style' => 'max-width: 720px;']); ?>

			<div class="row">
				<div class="col-md-6">
					<label class="form-group">
						<div><?php echo t('title_status', 'Status'); ?></div>
						<?php echo f::form_select('status', $status_options, true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div><?php echo t('title_scheduled_at', 'Date Scheduled'); ?></div>
						<?php echo f::form_input_datetime('scheduled_at', true); ?>
					</label>
				</div>
			</div>

			<div class="row">
				<div class="col-md-3">
					<label class="form-group">
						<div><?php echo t('title_method', 'Method'); ?></div>
						<?php echo f::form_select('method', $method_options, true); ?>
					</label>
				</div>

				<div class="col-md-9">
					<label class="form-group">
						<div><?php echo t('title_url', 'URL'); ?></div>
						<?php echo f::form_input_url('url', true, ['required' => '']); ?>
					</label>
				</div>
			</div>

			<label class="form-group">
				<div><?php echo t('title_headers', 'Headers (JSON)'); ?></div>
				<?php echo f::form_textarea('headers', true, ['rows' => '6']); ?>
			</label>

			<label class="form-group">
				<div><?php echo t('title_body', 'Body'); ?></div>
				<?php echo f::form_textarea('body', true, ['rows' => '10']); ?>
			</label>

			<?php if (!empty($request->data['last_attempt'])) { ?>
			<div class="row">
				<div class="col-md-6">
					<label class="form-group">
						<div><?php echo t('title_failed_attempts', 'Failed Attempts'); ?></div>
						<div class="form-input"><?php echo (int)$request->data['failed_attempts']; ?></div>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div><?php echo t('title_last_attempt', 'Last Attempt'); ?></div>
						<div class="form-input"><?php echo $request->data['last_attempt'] ?: '-'; ?></div>
					</label>
				</div>
			</div>
			<?php } ?>

			<?php if (!empty($request->data['raw_response'])) { ?>
			<label class="form-group">
				<div><?php echo t('title_raw_response', 'Raw Response'); ?></div>
				<div class="form-input"><?php echo f::escape_html($request->data['raw_response']); ?></div>
			</label>
			<?php } ?>

			<div class="form-group">
				<?php echo f::form_button_predefined('save'); ?>
				<?php echo (!empty($request->data['id'])) ? f::form_button_predefined('delete') : ''; ?>
				<?php echo f::form_button_predefined('cancel'); ?>
			</div>

		<?php echo f::form_end(); ?>
	</div>
</div>
