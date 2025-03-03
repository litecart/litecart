<?php

	if (!empty($_GET['sold_out_status_id'])) {
		$sold_out_status = new ent_sold_out_status($_GET['sold_out_status_id']);
	} else {
		$sold_out_status = new ent_sold_out_status();
	}

	if (!$_POST) {
		$_POST = $sold_out_status->data;
	}

	document::$title[] = !empty($sold_out_status->data['id']) ? language::translate('title_edit_sold_out_status', 'Edit Sold Out Status') : language::translate('title_create_new_sold_out_status', 'Create New Sold Out Status');

	breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
	breadcrumbs::add(language::translate('title_sold_out_statuses', 'Sold-Out Statuses'), document::ilink(__APP__.'/sold_out_statuses'));
	breadcrumbs::add(!empty($sold_out_status->data['id']) ? language::translate('title_edit_sold_out_status', 'Edit Sold Out Status') : language::translate('title_create_new_sold_out_status', 'Create New Sold Out Status'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['name'])) {
				throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
			}

			if (empty($_POST['hidden'])) $_POST['hidden'] = 0;
			if (empty($_POST['orderable'])) $_POST['orderable'] = 0;

			foreach ([
				'name',
				'description',
				'hidden',
				'orderable',
			] as $field) {
				if (isset($_POST[$field])) {
					$sold_out_status->data[$field] = $_POST[$field];
				}
			}

			$sold_out_status->save();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/sold_out_statuses'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($sold_out_status->data['id'])) {
				throw new Exception(language::translate('error_must_provide_sold_out_status', 'You must provide a sold out status'));
			}

			$sold_out_status->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/sold_out_statuses'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}
?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($sold_out_status->data['id']) ? language::translate('title_edit_sold_out_status', 'Edit Sold Out Status') : language::translate('title_create_new_sold_out_status', 'Create New Sold Out Status'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('sold_out_status_form', 'post', false, false, 'style="max-width: 720px;"'); ?>

			<div class="grid">
				<div class="col-md-8">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
						<?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_regional_text('name['. $language_code .']', $language_code, true, ''); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-8">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_description', 'Description'); ?></div>
						<?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_regional_textarea('description['. $language_code .']', $language_code, true, 'style="height: 60px;"'); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-6">
					<div class="form-group">
						<?php echo functions::form_checkbox('hidden', ['1', language::translate('text_hide_from_listing', 'Hide from listing')], true); ?>
						<?php echo functions::form_checkbox('orderable', ['1', language::translate('text_product_is_orderable', 'Product is orderable')], true); ?>
					</div>
				</div>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php if (!empty($sold_out_status->data['id'])) echo functions::form_button_predefined('delete'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>
