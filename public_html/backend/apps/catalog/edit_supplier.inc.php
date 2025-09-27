<?php

	if (!empty($_GET['supplier_id'])) {
		$supplier = new ent_supplier($_GET['supplier_id']);
	} else {
		$supplier = new ent_supplier();
	}

	document::$title[] = !empty($supplier->data['id']) ? t('title_edit_supplier', 'Edit Supplier') : t('title_create_new_supplier', 'Create New Supplier');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_suppliers', 'Suppliers'), document::ilink(__APP__.'/suppliers'));
	breadcrumbs::add(!empty($supplier->data['id']) ? t('title_edit_supplier', 'Edit Supplier') : t('title_create_new_supplier', 'Create New Supplier'), document::ilink());

	if (!$_POST) {
		$_POST = $supplier->data;
	}

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['name'])) {
				throw new Exception(t('error_must_provide_name', 'You must provide a name'));
			}

			if (!isset($_POST['status'])) $_POST['status'] = '0';

			foreach ([
				'code',
				'name',
				'description',
				'email',
				'phone',
				'link',
			] as $field) {
				if (isset($_POST[$field])) {
					$supplier->data[$field] = $_POST[$field];
				}
			}

			$supplier->save();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/suppliers'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($supplier->data['id'])) {
				throw new Exception(t('error_must_provide_supplier', 'You must provide a supplier'));
			}

			$supplier->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/suppliers'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}
?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($supplier->data['id']) ? t('title_edit_supplier', 'Edit Supplier') : t('title_create_new_supplier', 'Create New Supplier'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('supplier_form', 'post', false, false, 'style="max-width: 720px;"'); ?>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_code', 'Code'); ?></div>
						<?php echo functions::form_input_text('code', true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_name', 'Name'); ?></div>
						<?php echo functions::form_input_text('name', true); ?>
					</label>
				</div>
			</div>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_description', 'Description'); ?></div>
				<?php echo functions::form_textarea('description', true); ?>
			</label>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_email_address', 'Email Address'); ?></div>
						<?php echo functions::form_input_email('email', true, 'email', ''); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_phone_number', 'Phone Number'); ?></div>
						<?php echo functions::form_input_text('phone', true); ?>
					</label>
				</div>
			</div>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_link', 'Link'); ?></div>
				<?php echo functions::form_input_text('link', true); ?>
			</label>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php if (!empty($supplier->data['id'])) echo functions::form_button_predefined('delete'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>

