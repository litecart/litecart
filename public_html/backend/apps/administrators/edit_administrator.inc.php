<?php

	if (!empty($_GET['administrator_id'])) {
		$administrator = new ent_administrator($_GET['administrator_id']);
	} else {
		$administrator = new ent_administrator();
	}

	if (!$_POST) {
		$_POST = $administrator->data;
	}

	document::$title[] = !empty($administrator->data['username']) ? language::translate('title_edit_administrator', 'Edit Administrator') : language::translate('title_create_new_administrator', 'Create New Administrator');

	breadcrumbs::add(language::translate('title_administrators', 'Administrators'), document::href_ilink(__APP__.'/administrators'));
	breadcrumbs::add(!empty($administrator->data['username']) ? language::translate('title_edit_administrator', 'Edit Administrator') : language::translate('title_create_new_administrator', 'Create New Administrator'));

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['username'])) {
				throw new Exception(language::translate('error_must_enter_username', 'You must enter a username'));
			}

			if (empty($administrator->data['id']) && empty($_POST['password'])) {
				throw new Exception(language::translate('error_must_enter_password', 'You must enter a password'));
			}

			if (!empty($_POST['two_factor_auth']) && empty($_POST['email'])) {
				throw new Exception(language::translate('error_email_required_for_two_factor_authenticatoin', 'An email address is required for two-factor authentication'));
			}

			if (empty($_POST['apps'])) {
				$_POST['apps'] = [];
			}

			if (empty($_POST['widgets'])) {
				$_POST['widgets'] = [];
			}

			foreach ([
				'status',
				'username',
				'email',
				'password',
				'apps',
				'widgets',
				'two_factor_auth',
				'date_valid_from',
				'date_valid_to',
			] as $field) {
				if (isset($_POST[$field])) {
					$administrator->data[$field] = $_POST[$field];
				}
			}

			if (!empty($_POST['password'])) {
				$administrator->set_password($_POST['password']);
			}

			$administrator->data['administrator_security_timestamp'] = date('Y-m-d H:i:s');

			$administrator->save();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/administrators'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($administrator->data['id'])) {
				throw new Exception(language::translate('error_must_provide_administrator', 'You must provide a administrator'));
			}

			$administrator->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/administrators'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}
?>
<style>
#app-permissions li,
#widget-permissions li {
	padding: .25em 0;
}
</style>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($administrator->data['username']) ? language::translate('title_edit_administrator', 'Edit Administrator') : language::translate('title_create_new_administrator', 'Create New Administrator'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('administrator_form', 'post', false, false, 'autocomplete="off" style="max-width: 1200px;"'); ?>

			<div class="row">

				<div class="col-md-8">
					<div class="row">
						<div class="form-group col-md-6">
							<label><?php echo language::translate('title_status', 'Status'); ?></label>
							<?php echo functions::form_toggle('status', 'e/d', (isset($_POST['status'])) ? $_POST['status'] : '1'); ?>
						</div>

						<div class="form-group col-sm-6">
							<label><?php echo language::translate('title_username', 'Username'); ?></label>
							<?php echo functions::form_input_text('username', true, 'autocomplete="off" required'); ?>
						</div>
					</div>


					<div class="row">
						<div class="form-group col-sm-6">
							<label><?php echo language::translate('title_email', 'Email'); ?></label>
							<?php echo functions::form_input_email('email', true, 'autocomplete="off"'); ?>
						</div>

						<div class="form-group col-md-6">
							<label><?php echo language::translate('title_new_password', 'New Password'); ?></label>
							<?php echo functions::form_input_password_unmaskable('password', '', 'autocomplete="new-password"'); ?>
						</div>
					</div>

					<div class="row">
						<div class="form-group col-md-6">
							<label><?php echo language::translate('title_two_factor_authentication', 'Two-Factor Authentication'); ?></label>
							<?php echo functions::form_toggle('two_factor_auth', 'e/d', true); ?>
						</div>
					</div>

					<div class="row">
						<div class="form-group col-md-6">
							<label><?php echo language::translate('title_valid_from', 'Valid From'); ?></label>
							<?php echo functions::form_input_datetime('date_valid_from', true); ?>
						</div>

						<div class="form-group col-md-6">
							<label><?php echo language::translate('title_valid_to', 'Valid To'); ?></label>
							<?php echo functions::form_input_datetime('date_valid_to', true); ?>
						</div>
					</div>

					<?php if (!empty($administrator->data['id'])) { ?>
					<div class="row">
						<div class="form-group col-md-6">
							<label><?php echo language::translate('title_last_ip_address', 'Last IP Address'); ?></label>
							<?php echo functions::form_input_text('last_ip_address', true, 'readonly'); ?>
						</div>

						<div class="form-group col-md-6">
							<label><?php echo language::translate('title_last_hostname', 'Last Hostname'); ?></label>
							<?php echo functions::form_input_text('last_hostname', true, 'readonly'); ?>
						</div>
					</div>

					<div class="row">
						<div class="form-group col-md-6">
							<label><?php echo language::translate('title_last_login', 'Last Login'); ?></label>
							<?php echo functions::form_input_text('date_login', true, 'readonly'); ?>
						</div>

						<div class="form-group col-md-6">
							<label><?php echo language::translate('title_last_active', 'Last Active'); ?></label>
							<?php echo functions::form_input_text('date_active', true, 'readonly'); ?>
					</div>
					</div>
					<?php } ?>
				</div>

				<div class="col-md-4">
					<div id="app-permissions" class="form-group">
						<?php echo functions::form_checkbox('apps_toggle', ['1', language::translate('title_apps', 'Apps')]); ?>
						<div class="form-input" style="height: 400px; overflow-y: scroll;">
							<ul class="list-unstyled">
<?php
	foreach (functions::admin_get_apps() as $app) {
		echo implode(PHP_EOL, [
			'<li data-app="'. functions::escape_attr($app['id']) .'">',
			'  '. functions::form_checkbox('apps['.$app['id'].'][status]', ['1', $app['name']], true),
			'  <ul class="list-unstyled">',
			implode(PHP_EOL, array_map(function($doc) use ($app) {
				return '    <li data-doc="'. functions::escape_attr($doc) .'">'. functions::form_checkbox('apps['.$app['id'].'][docs][]', [$doc], true) .'</li>';
			}, array_keys($app['docs']))),
			'  </ul>',
			'</li>',
		]);
	}
?>
							</ul>
						</div>
					</div>

					<div id="widget-permissions" class="form-group">
						<?php echo functions::form_checkbox('widgets_toggle', ['1', language::translate('title_widgets', 'Widgets')]); ?>
						<div class="form-input" style="height: 150px; overflow-y: scroll;">
							<ul class="list-unstyled">
<?php
	foreach (functions::admin_get_widgets() as $widget) {
		echo implode(PHP_EOL, [
			'<li>',
			'  '. functions::form_checkbox('widgets['.$widget['id'].']', ['1', $widget['name']], true),
			'</li>',
		]);
	}
?>
							</ul>
						</div>
					</div>
				</div>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php if (!empty($administrator->data['id'])) echo functions::form_button_predefined('delete'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>

<script>
	$('input[name="apps_toggle"]').change(function(){
		$('input[name^="apps"][name$="[status]"]').prop('disabled', !$(this).is(':checked'));
		$('input[name^="apps"][name$="[docs][]"]').prop('disabled', !$(this).is(':checked'));
	}).trigger('change');

	$('input[name^="apps"][name$="[status]"]').change(function(){
		if ($(this).prop('checked')) {
			if (!$(this).closest('[data-app]').find('ul :input:checked').length) {
				$(this).closest('[data-app]').find('ul :input').prop('checked', true);
			}
		} else {
			$(this).closest('[data-app]').find('ul :input').prop('checked', false);
		}
	});

	$('input[name^="apps"][name$="[docs][]"]').change(function() {
		if ($(this).is(':checked')) {
			$(this).closest('ul').closest('[data-app]').children().not('ul').find(':input').prop('checked', true);
		}
	});

	$('input[name="widgets_toggle"]').change(function(){
		$('input[name^="widgets["]').prop('disabled', !$(this).is(':checked'));
	}).trigger('change');
</script>