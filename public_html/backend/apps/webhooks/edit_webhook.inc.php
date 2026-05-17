<?php

	if (!empty($_GET['webhook_id'])) {
		$webhook = new ent_webhook($_GET['webhook_id']);
	} else {
		$webhook = new ent_webhook();
	}

	if (!$_POST) {
		$_POST = $webhook->data;
	}

	breadcrumbs::add(!empty($webhook->data['id']) ? t('title_edit_webhook', 'Edit Webhook') : t('title_create_new_webhook', 'Create New Webhook'));

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['status'])) {
				$_POST['status'] = 0;
			}

			foreach ([
				'status',
				'event',
				'url',
			] as $field) {
				if (isset($_POST[$field])) {
					$webhook->data[$field] = $_POST[$field];
				}
			}

			$webhook->save();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::link('', ['doc' => 'webhooks'], ['app']));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($webhook->data['id'])) {
				throw new Exception(t('error_must_provide_url', 'You must provide a url'));
			}

			$webhook->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect('Location: '. document::ilink(__APP__ .'/webhooks'), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$event_options = [
		[
			'label' => t('title_customer', 'Customer'),
			'options' => [
				'customer:created' => t('title_customer_created', 'Customer Created'),
				'customer:updated' => t('title_customer_updated', 'Customer Updated'),
				'customer:deleted' => t('title_customer_deleted', 'Customer Deleted'),
			],
		],
		[
			'label' => t('title_newsletter', 'Newsletter'),
			'options' => [
				'newsletter:subscribed' => t('title_newsletter_subscribed', 'Newsletter Subscribed'),
				'newsletter:unsubscribed' => t('title_newsletter_unsubscribed', 'Newsletter Unsubscribed'),
			],
		],
		[
			'label' => t('title_order', 'Order'),
			'options' => [
				'order:created' => t('title_order_created', 'Order Created'),
				'order:updated' => t('title_order_updated', 'Order Updated'),
				'order:deleted' => t('title_order_deleted', 'Order Deleted'),
			],
		],
	];

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($webhook->data['id']) ? t('title_edit_webhook', 'Edit Webhook') : t('title_create_new_webhook', 'Create New Webhook'); ?>
		</div>
	</div>

	<?php echo f::form_begin('webhook_form', 'post', false, false, ['autocomplete' => 'off', 'style' => 'max-width: 640px;']); ?>

		<div class="card-body">

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo t('title_status', 'Status'); ?></label>
						<?php echo f::form_toggle('status', 'e/d', $_POST['status'] ?? '1'); ?>
					</div>
				</div>

				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo t('title_event', 'Event'); ?></label>
						<?php echo f::form_select_optgroup('event', $event_options, true); ?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label><?php echo t('title_url', 'URL'); ?></label>
				<?php echo f::form_input_url('url', true, ['required' => '']); ?>
			</div>

			<?php if (!empty($webhook->data['id'])) { ?>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo t('title_last_sent', 'Last Sent'); ?></label>
						<input class="form-input" readonly name="sent_at" value="<?php echo $webhook->data['sent_at'] ? f::format_datetime('datetime', $webhook->data['sent_at']) : '-'; ?>">
					</div>
				</div>
			</div>
			<?php } ?>
		</div>

		<div class="card-action">
			<?php echo f::form_button_predefined('save'); ?>
			<?php echo !empty($webhook->data['id']) ? f::form_button_predefined('delete') : ''; ?>
			<?php echo f::form_button_predefined('cancel'); ?>
		</div>

	<?php echo f::form_end(); ?>
</div>