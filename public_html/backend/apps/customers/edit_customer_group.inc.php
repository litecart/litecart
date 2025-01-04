<?php

	if (!empty($_GET['group_id'])) {
		$customer_group = new ent_customer_group($_GET['group_id']);
	} else {
		$customer_group = new ent_customer_group();
	}

	if (!$_POST) {
		$_POST = $customer_group->data;
	}

	breadcrumbs::add(!empty($customer_group->data['id']) ? language::translate('title_edit_customer_group', 'Edit Customer Group') : language::translate('title_add_new_customer_group', 'Add New Customer Group'));

	if (isset($_POST['save'])) {

		try {

			foreach ([
				'type',
				'name',
				'description',
			] as $field) {
				if (isset($_POST[$field])) {
					$customer_group->data[$field] = $_POST[$field];
				}
			}

			$customer_group->save();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::link('', ['app' => $_GET['app'], 'doc' => 'customer_groups']));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {
		try {

			$customer_group->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::link('', ['app' => $_GET['app'], 'doc' => 'customer_groups']));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$type_options = [
		['retail', language::translate('title_retail', 'Retail')],
		['wholesale', language::translate('title_wholesale', 'Wholesale')],
	];

?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($customer_group->data['id']) ? language::translate('title_edit_customer_group', 'Edit Customer Group') : language::translate('title_create_new_customer_group', 'Create New Customer Group'); ?>
		</div>
	</div>

	<div class="card-body">

		<?php echo functions::form_begin('customer_group_form', 'post', null, false, 'style="max-width: 640px;"'); ?>

			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label><?php echo language::translate('title_type', 'Type'); ?></label>
						<?php echo functions::form_select('type', $type_options, true); ?>
					</div>
				</div>

				<div class="col-md-8">
					<div class="form-group">
						<label><?php echo language::translate('title_name', 'Name'); ?></label>
						<?php echo functions::form_input_text('name', true); ?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label><?php echo language::translate('title_description', 'Description'); ?></label>
				<?php echo functions::form_textarea('description', true); ?>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php echo !empty($customer_group->data['id']) ? functions::form_button_predefined('delete') : ''; ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>

	</div>
</div>
