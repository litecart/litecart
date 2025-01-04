<?php

	if (!empty($_GET['quantity_unit_id'])) {
		$quantity_unit = new ent_quantity_unit($_GET['quantity_unit_id']);
	} else {
		$quantity_unit = new ent_quantity_unit();
	}

	if (!$_POST) {
		$_POST = $quantity_unit->data;
	}

	document::$title[] = !empty($quantity_unit->data['id']) ? language::translate('title_edit_quantity_unit', 'Edit Quantity Unit') : language::translate('title_create_new_quantity_unit', 'Create New Quantity Unit');

	breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
	breadcrumbs::add(language::translate('title_quantity_units', 'Quantity Units'), document::ilink(__APP__.'/quantity_units'));
	breadcrumbs::add(!empty($quantity_unit->data['id']) ? language::translate('title_edit_quantity_unit', 'Edit Quantity Unit') : language::translate('title_create_new_quantity_unit', 'Create New Quantity Unit'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['name'])) {
				throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
			}

			if (empty($_POST['separate'])) $_POST['separate'] = 0;

			foreach ([
				'decimals',
				'separate',
				'priority',
				'name',
				'description',
			] as $field) {
				if (isset($_POST[$field])) {
					$quantity_unit->data[$field] = $_POST[$field];
				}
			}

			$quantity_unit->save();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/quantity_units'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($quantity_unit->data['id'])) {
				throw new Exception(language::translate('error_must_provide_quantity_unit', 'You must provide a quantity unit'));
			}

			$quantity_unit->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/quantity_units'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}
?>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($quantity_unit->data['id']) ? language::translate('title_edit_quantity_unit', 'Edit Quantity Unit') : language::translate('title_create_new_quantity_unit', 'Create New Quantity Unit'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('quantity_unit_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

			<div class="row">
				<div class="col-md-8">
					<div class="form-group">
						<label><?php echo language::translate('title_name', 'Name'); ?></label>
						<?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_regional_text('name['. $language_code .']', $language_code, true); ?>
					</div>
				</div>

				<div class="col-md-4">
					<div class="form-group">
						<label><?php echo language::translate('title_priority', 'Priority'); ?></label>
						<?php echo functions::form_input_number('priority', true); ?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label><?php echo language::translate('title_description', 'Description'); ?></label>
				<?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_regional_text('description['. $language_code .']', $language_code, true); ?>
			</div>

			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label><?php echo language::translate('title_decimals', 'Decimals'); ?></label>
						<?php echo functions::form_input_number('decimals', true); ?>
					</div>
				</div>

				<div class="col-md-8">
					<div class="form-group">
						<br>
						<div class="checkbox">
							<label><?php echo functions::form_checkbox('separate', '1', true); ?> <?php echo language::translate('text_separate_added_cart_items', 'Separate added cart items'); ?></label>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label><?php echo language::translate('title_priority', 'Priority'); ?></label>
						<?php echo functions::form_input_number('priority', true); ?>
					</div>
				</div>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php if (!empty($quantity_unit->data['id'])) echo functions::form_button_predefined('delete'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>
