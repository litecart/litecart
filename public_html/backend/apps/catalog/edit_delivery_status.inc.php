<?php

	if (!empty($_GET['delivery_status_id'])) {
		$delivery_status = new ent_delivery_status($_GET['delivery_status_id']);
	} else {
		$delivery_status = new ent_delivery_status();
	}

	if (!$_POST) {
		$_POST = $delivery_status->data;
	}

	document::$title[] = !empty($delivery_status->data['id']) ? language::translate('title_edit_delivery_status', 'Edit Delivery Status') : language::translate('title_create_new_delivery_status', 'Create New Delivery Status');

	breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
	breadcrumbs::add(language::translate('title_delivery_statuses', 'Delivery Statuses'), document::ilink(__APP__.'/delivery_statuses'));
	breadcrumbs::add(!empty($delivery_status->data['id']) ? language::translate('title_edit_delivery_status', 'Edit Delivery Status') : language::translate('title_create_new_delivery_status', 'Create New Delivery Status'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['name'])) {
				throw new Exception(language::translate('error_must_provide_name', 'You must provide a name'));
			}

			foreach ([
				'name',
				'description',
			] as $field) {
				if (isset($_POST[$field])) {
					$delivery_status->data[$field] = $_POST[$field];
				}
			}

			$delivery_status->save();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/delivery_statuses'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($delivery_status->data['id'])) {
				throw new Exception(language::translate('error_must_provide_delivery_status', 'You must provide a delivery status'));
			}

			$delivery_status->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/delivery_statuses'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}
?>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($delivery_status->data['id']) ? language::translate('title_edit_delivery_status', 'Edit Delivery Status') : language::translate('title_create_new_delivery_status', 'Create New Delivery Status'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('delivery_status_form', 'post', false, false, 'style="max-width: 720px;"'); ?>

			<div class="grid">
				<div class="col-md-8">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
						<?php foreach (array_keys(language::$languages) as $language_code)  echo functions::form_regional_text('name['. $language_code .']', $language_code, true, ''); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-8">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_description', 'Description'); ?></div>
						<?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_regional_textarea('description['. $language_code .']', $language_code, true, 'style="height: 50px;"'); ?>
					</label>
				</div>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php if (!empty($delivery_status->data['id'])) echo functions::form_button_predefined('delete'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>
