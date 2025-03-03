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

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($quantity_unit->data['id']) ? language::translate('title_edit_quantity_unit', 'Edit Quantity Unit') : language::translate('title_create_new_quantity_unit', 'Create New Quantity Unit'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('quantity_unit_form', 'post', false, false, 'style="max-width: 720px;"'); ?>

			<div class="grid">
				<div class="col-md-8">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
						<?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_regional_text('name['. $language_code .']', $language_code, true); ?>
					 </label>
				</div>

				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_priority', 'Priority'); ?></div>
						<?php echo functions::form_input_number('priority', true); ?>
					</label>
				</div>
			</div>

			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_description', 'Description'); ?></div>
				<?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_regional_text('description['. $language_code .']', $language_code, true); ?>
			 </label>

			<div class="grid">
				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_decimals', 'Decimals'); ?></div>
						<?php echo functions::form_input_number('decimals', true); ?>
					</label>
				</div>

				<div class="col-md-8">
					<div class="form-group">
						<div class="form-label">&nbsp;</div>
						<?php echo functions::form_checkbox('separate', ['1', language::translate('text_separate_added_cart_items', 'Separate added cart items')], true); ?>
					</div>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_priority', 'Priority'); ?></div>
						<?php echo functions::form_input_number('priority', true); ?>
					</label>
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
