<?php

	if (!empty($_GET['tax_class_id'])) {
		$tax_class = new ent_tax_class($_GET['tax_class_id']);
	} else {
		$tax_class = new ent_tax_class();
	}

	if (!$_POST) {
		$_POST = $tax_class->data;
	}

	document::$title[] = !empty($tax_class->data['id']) ? language::translate('title_edit_tax_class', 'Edit Tax Class') : language::translate('title_create_new_tax_class', 'Create New Tax Class');

	breadcrumbs::add(language::translate('title_tax_classes', 'Tax Classes'), document::ilink(__APP__.'/tax_classes'));
	breadcrumbs::add(!empty($tax_class->data['id']) ? language::translate('title_edit_tax_class', 'Edit Tax Class') : language::translate('title_create_new_tax_class', 'Create New Tax Class'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['name'])) {
				throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
			}

			foreach ([
				'code',
				'name',
				'description',
			] as $field) {
				if (isset($_POST[$field])) {
					$tax_class->data[$field] = $_POST[$field];
				}
			}

			$tax_class->save();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/tax_classes'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($tax_class->data['id'])) {
				throw new Exception(language::translate('error_must_provide_tax_class', 'You must provide a tax class'));
			}

			$tax_class->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/tax_classes'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}
?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($tax_class->data['id']) ? language::translate('title_edit_tax_class', 'Edit Tax Class') : language::translate('title_create_new_tax_class', 'Create New Tax Class'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('tax_class_form', 'post', false, false, 'style="max-width: 720px;"'); ?>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_code', 'Code'); ?></div>
						<?php echo functions::form_input_text('code', true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
						<?php echo functions::form_input_text('name', true); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-12">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_description', 'Description'); ?></div>
						<?php echo functions::form_input_text('description', true); ?>
					</label>
				</div>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php if ($tax_class->data['id']) echo functions::form_button_predefined('delete'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>

	</div>
</div>
