<?php

	if (!empty($_GET['order_status_id'])) {
		$order_status = new ent_order_status($_GET['order_status_id']);
	} else {
		$order_status = new ent_order_status();
	}

	document::$title[] = !empty($order_status->data['id']) ? t('title_edit_order_status', 'Edit Order Status') : t('title_create_new_order_status', 'Create New Order Status');

	breadcrumbs::add(t('title_order_statuses', 'Order Statuses'), document::ilink(__APP__.'/order_statuses'));
	breadcrumbs::add(!empty($order_status->data['id']) ? t('title_edit_order_status', 'Edit Order Status') : t('title_create_new_order_status', 'Create New Order Status'), document::ilink());

	if (!$_POST) {
		$_POST = $order_status->data;
	}

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['name'])) {
				throw new Exception(t('error_must_provide_name', 'You must provide a name'));
			}

			if (empty($_POST['hidden'])) {
				$_POST['is_sale'] = 0;
			}

			if (empty($_POST['notify'])) {
				$_POST['notify'] = 0;
			}

			if (empty($_POST['is_sale'])) {
				$_POST['is_sale'] = 0;
			}

			if (empty($_POST['is_archived'])) {
				$_POST['is_archived'] = 0;
			}

			if (empty($_POST['is_trackable'])) {
				$_POST['is_trackable'] = 0;
			}

			foreach ([
				'hidden',
				'state',
				'icon',
				'color',
				'is_sale',
				'is_archived',
				'is_trackable',
				'notify',
				'priority',
				'name',
				'description',
				'email_subject',
				'email_message',
				'stock_action',
			] as $field) {
				if (isset($_POST[$field])) {
					$order_status->data[$field] = $_POST[$field];
				}
			}

			$order_status->save();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/order_statuses'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($order_status->data['id'])) {
				throw new Exception(t('error_must_provide_order_status', 'You must provide an order status'));
			}

			$order_status->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/order_statuses'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$states = [
		'created' => t('title_created', 'Created'),
		'on_hold' => t('title_on_hold', 'On Hold'),
		'ready' => t('title_ready', 'Ready'),
		'delayed' => t('title_delayed', 'Delayed'),
		'processing' => t('title_processing', 'Processing'),
		'completed' => t('title_completed', 'Completed'),
		'dispatched' => t('title_dispatched', 'Dispatched'),
		'in_transit' => t('title_in_transit', 'In Transit'),
		'delivered' => t('title_delivered', 'Delivered'),
		'returning' => t('title_returning', 'Returning'),
		'returned' => t('title_returned', 'Returned'),
		'cancelled' => t('title_cancelled', 'Cancelled'),
	];

	$language_codes = array_unique(array_merge([language::$selected['code']], [settings::get('store_language_code')], array_keys(language::$languages)));
?>
<style>
.form-group .checkbox {
	margin-top: .5em;
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($order_status->data['id']) ? t('title_edit_order_status', 'Edit Order Status') : t('title_create_new_order_status', 'Create New Order Status'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('order_status_form', 'post'); ?>

			<div class="grid">
				<div class="col-md-6">
					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_name', 'Name'); ?></div>
								<?php foreach ($language_codes as $language_code) echo functions::form_regional_text('name['. $language_code .']', $language_code, true); ?>
							</label>
						</div>

						<div class="col-md-6">

							<div class="grid">
								<div class="col-md-8">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_order_state', 'State'); ?></div>
										<?php echo functions::form_select('state', $states, true); ?>
									</label>
								</div>

								<div class="col-md-4">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_priority', 'Priority'); ?></div>
										<?php echo functions::form_input_number('priority', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_font_icon', 'Font Icon'); ?></div>
										<?php echo functions::form_input_text('icon', true, 'placeholder="icon-circle-thin"'); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_color', 'Color'); ?></div>
										<?php echo functions::form_input_color('color', empty($_POST['color']) ? '#cccccc' : true, 'placeholder="#cccccc"'); ?>
									</label>
								</div>
							</div>
						</div>
					</div>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_description', 'Description'); ?></div>
						<?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_regional_textarea('description['. $language_code .']', $language_code, true, 'style="height: 50px;"'); ?>
					</label>

					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_properties', 'Properties'); ?></div>

								<div>
									<strong><?php echo functions::form_checkbox('hidden', ['1', t('text_hidden', 'Hidden')], empty($_POST['hidden']) ? '0' : '1'); ?></strong>
									<?php echo t('text_hidden_from_customer', 'Hidden from the customer\'s order history'); ?>
								</div>

								<div>
									<strong><?php echo functions::form_checkbox('is_sale', ['1', t('text_is_sale', 'Is sale')], empty($_POST['is_sale']) ? '0' : '1'); ?></strong>
									<?php echo t('text_include_in_sales_reports', 'Include in sales reports'); ?>
								</div>

								<div>
									<strong><?php echo functions::form_checkbox('is_archived', ['1', t('text_is_archived', 'Is archived')], empty($_POST['is_archived']) ? '0' : '1'); ?></strong>
									<?php echo t('text_exclude_from_list_of_orders', 'Exclude from the default list of orders'); ?>
								</div>

								<div class="checkbox">
									<strong><?php echo functions::form_checkbox('is_trackable', ['1', t('text_is_trackable', 'Is trackable')], empty($_POST['is_trackable']) ? '0' : '1'); ?> </strong>
									<?php echo t('text_will_send_tracking_event_to_shipping_module', 'Will send a request to the shipping module for tracking the shipment.'); ?></label>
								</div>
							</label>
						</div>

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_stock_action', 'Stock Action'); ?></div>

								<div>
									<strong><?php echo functions::form_radio_button('stock_action', ['none', t('title_none', 'None')], empty($_POST['stock_action']) ? 'none' : true); ?></strong>
									<?php echo t('text_stock_remains_without_an_action', 'Stock remains without an action.'); ?>
								</div>

								<div>
									<strong><?php echo functions::form_radio_button('stock_action', ['reserve', t('title_reserve_stock', 'Reserve Stock')], true); ?></strong>
									<?php echo t('text_reserve_stock_for_orders_having_this_status', 'Reserve stock for orders having this status.'); ?>
								</div>

								<div class="checkbox">
									<strong><?php echo functions::form_radio_button('stock_action', ['commit', t('title_commit_changes', 'Commit Changes')], true); ?> </strong>
									<?php echo t('text_commit_changes_to_the_stock', 'Commit changes to stock withdrawing or depositing quantities determined by if an order is a sale or return.'); ?></label>
								</div>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_priority', 'Priority'); ?></div>
									<?php echo functions::form_input_number('priority', true); ?>
								</label>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<fieldset>
						<legend>
							<?php echo functions::form_checkbox('notify', ['1', t('title_email_notification', 'Email Notification')], empty($_POST['notify']) ? '0' : '1'); ?>
						</legend>

						<?php if (count(language::$languages) > 1) { ?>
						<nav class="tabs">
							<?php foreach (language::$languages as $language) { ?>
							<a class="tab-item<?php if ($language['code'] == language::$selected['code']) echo ' active'; ?>" data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a>
							<?php } ?>
						</nav>
						<?php } ?>

						<div class="tab-contents">
							<?php foreach (array_keys(language::$languages) as $language_code) { ?>
							<div id="<?php echo $language_code; ?>" class="tab-content<?php if ($language_code == language::$selected['code']) echo ' active'; ?>">
								<div class="form-group">
									<div class="form-label"><?php echo t('title_subject', 'Subject'); ?></div>
									<?php echo functions::form_regional_text('email_subject['. $language_code .']', $language_code, true); ?>
								</div>

								<div class="form-group">
									<div class="form-label"><?php echo t('title_message', 'Message'); ?></div>
									<?php echo functions::form_regional_wysiwyg('email_message['. $language_code .']', $language_code, true); ?>
								</div>
							</div>
							<?php } ?>
						</div>

						<div><?php echo t('title_aliases', 'Aliases'); ?>: <code>{order_id}, {order_status}, {firstname}, {lastname}, {billing_address}, {order_items}, {total}, {payment_transaction_id}, {shipping_address}, {shipping_tracking_id}, {shipping_tracking_url}, {shipping_current_status}, {shipping_current_location}, {order_copy_url}, {store_name}, {store_url}</code></div>
					</fieldset>
				</div>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php if (!empty($order_status->data['id'])) echo functions::form_button_predefined('delete'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>
