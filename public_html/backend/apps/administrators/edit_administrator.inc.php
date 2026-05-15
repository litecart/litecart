<?php

	if (!empty($_GET['administrator_id'])) {
		$administrator = new ent_administrator($_GET['administrator_id']);
	} else {
		$administrator = new ent_administrator();
	}

	if (!$_POST) {
		$_POST = $administrator->data;
		$_POST['apps_toggle'] = !empty($administrator->data['permissions']['apps']) ? 1 : 0;
		$_POST['widgets_toggle'] = !empty($administrator->data['permissions']['widgets']) ? 1 : 0;
		$_POST['mcp_toggle'] = !empty($administrator->data['permissions']['mcp']) ? 1 : 0;
	}

	document::$title[] = !empty($administrator->data['username']) ? t('title_edit_administrator', 'Edit Administrator') : t('title_create_new_administrator', 'Create New Administrator');

	breadcrumbs::add(t('title_administrators', 'Administrators'), document::href_ilink(__APP__.'/administrators'));
	breadcrumbs::add(!empty($administrator->data['username']) ? t('title_edit_administrator', 'Edit Administrator') : t('title_create_new_administrator', 'Create New Administrator'));

	// TOTP enroll/confirm/disable — handled before the main save so the sub-form
	// buttons (totp_setup, totp_confirm, totp_disable) don't have to go through
	// the generic save validation.
	if (!empty($administrator->data['id']) && (!empty($_POST['totp_setup']) || !empty($_POST['totp_confirm']) || !empty($_POST['totp_disable']))) {

		try {

			if (!empty($_POST['totp_setup'])) {
				session::$data['totp_pending_secret'] = f::totp_generate_secret();
				reload();
				exit;
			}

			if (!empty($_POST['totp_confirm'])) {

				if (empty(session::$data['totp_pending_secret'])) {
					throw new Exception(t('error_totp_setup_expired', 'TOTP setup session expired. Please try again.'));
				}

				if (empty($_POST['totp_code']) || !totp_verify_code(session::$data['totp_pending_secret'], $_POST['totp_code'])) {
					throw new Exception(t('error_invalid_verification_code', 'Invalid verification code'));
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."administrators
					set totp_secret = '". database::input(session::$data['totp_pending_secret']) ."'
					where id = ". (int)$administrator->data['id'] ."
					limit 1;"
				);

				unset(session::$data['totp_pending_secret']);
				notices::add('success', t('success_totp_enabled', 'TOTP has been enabled'));
				reload();
				exit;
			}

			if (!empty($_POST['totp_disable'])) {

				if (empty($_POST['totp_disable_password']) || !password_verify($_POST['totp_disable_password'], $administrator->data['password_hash'])) {
					throw new Exception(t('error_wrong_password', 'Wrong password'));
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."administrators
					set totp_secret = null
					where id = ". (int)$administrator->data['id'] ."
					limit 1;"
				);

				unset(session::$data['totp_pending_secret']);
				notices::add('success', t('success_totp_disabled', 'TOTP has been disabled'));
				reload();
				exit;
			}

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['username'])) {
				throw new Exception(t('error_must_provide_username', 'You must provide a username'));
			}

			if (empty($administrator->data['id']) && empty($_POST['password'])) {
				throw new Exception(t('error_must_provide_password', 'You must provide a password'));
			}

			if (!empty($_POST['two_factor_auth']) && empty($_POST['email'])) {
				throw new Exception(t('error_email_required_for_two_factor_authentication', 'An email address is required for two-factor authentication'));
			}

			if (!empty($_POST['password']) && empty($_POST['confirmed_password'])) {
				throw new Exception(t('error_must_confirm_password', 'You must confirm the password'));
			}

			if (!empty($_POST['password']) && $_POST['password'] != $_POST['confirmed_password']) {
				throw new Exception(t('error_passwords_missmatch', 'The passwords did not match'));
			}

			if (empty($_POST['permissions'])) {
				$_POST['permissions'] = [];
			}

			foreach ([
				'status',
				'username',
				'firstname',
				'lastname',
				'email',
				'password',
				'permissions',
				'two_factor_auth',
				'valid_from',
				'valid_to',
			] as $field) {
				if (isset($_POST[$field])) {
					$administrator->data[$field] = $_POST[$field];
				}
			}

			if (!empty($_POST['password'])) {
				$administrator->set_password($_POST['password']);
			}

			$administrator->data['sessions_expiry'] = date('Y-m-d H:is');

			$administrator->save();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/administrators'), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($administrator->data['id'])) {
				throw new Exception(t('error_must_provide_administrator', 'You must provide an administrator'));
			}

			$administrator->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/administrators'), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($administrator->data['username']) ? t('title_edit_administrator', 'Edit Administrator') : t('title_create_new_administrator', 'Create New Administrator'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo f::form_begin('administrator_form', 'post', false, false, 'autocomplete="off"'); ?>

			<div class="grid" style="max-width: 1200px;">

				<div class="col-md-8">
					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_status', 'Status'); ?></div>
								<?php echo f::form_toggle('status', 'e/d', $_POST['status'] ?? '1'); ?>
							</label>
						</div>

						<div class="col-sm-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_username', 'Username'); ?></div>
								<?php echo f::form_input_text('username', true, 'autocomplete="off" required'); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-sm-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_firstname', 'Firstname'); ?></div>
								<?php echo f::form_input_text('firstname', true, 'required'); ?>
							</label>
						</div>
						<div class="col-sm-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_lastname', 'Lastname'); ?></div>
								<?php echo f::form_input_text('lastname', true, 'required'); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-sm-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_email', 'Email'); ?></div>
								<?php echo f::form_input_email('email', true, 'autocomplete="off"'); ?>
							</label>
						</div>

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_two_factor_authentication', 'Two-Factor Authentication'); ?></div>
								<?php echo f::form_toggle('two_factor_auth', 'e/d', true); ?>
							</label>
						</div>
					</div>

					<?php if (!empty($administrator->data['id'])) { ?>
					<div class="grid">
						<div class="col-md-12">
							<div class="form-group">
								<div class="form-label"><?php echo t('title_totp_authenticator', 'TOTP Authenticator'); ?></div>

								<?php if (!empty($administrator->data['totp_secret'])) { ?>

									<div class="alert alert-success" style="margin-bottom: 1em;">
										<?php echo t('text_totp_enabled', 'TOTP is enabled. You will be prompted for a code on each login.'); ?>
									</div>

									<div class="grid">
										<div class="col-md-6">
											<?php echo f::form_input_password('totp_disable_password', '', 'autocomplete="off" placeholder="'. t('title_password', 'Password') .'"'); ?>
										</div>
										<div class="col-md-6">
											<?php echo f::form_button('totp_disable', t('title_disable_totp', 'Disable TOTP'), 'submit', 'class="btn btn-danger"'); ?>
										</div>
									</div>

								<?php } elseif (!empty(session::$data['totp_pending_secret'])) { ?>

									<?php
										$totp_uri = f::totp_build_uri(session::$data['totp_pending_secret'], $administrator->data['username'], settings::get('store_name'));
										$totp_svg = f::totp_generate_qr_svg($totp_uri, 200);
									?>

									<div style="text-align: center; margin-bottom: 1em;">
										<?php echo $totp_svg; ?>
									</div>

									<div class="form-group">
										<div class="form-label"><?php echo t('title_manual_setup_key', 'Manual Setup Key'); ?></div>
										<code style="word-break: break-all; user-select: all;"><?php echo session::$data['totp_pending_secret']; ?></code>
									</div>

									<div class="grid">
										<div class="col-md-6">
											<?php echo f::form_input_text('totp_code', '', 'placeholder="'. t('title_verification_code', 'Verification Code') .'" autocomplete="one-time-code" inputmode="numeric" maxlength="6" pattern="\d{6}"'); ?>
										</div>
										<div class="col-md-6">
											<?php echo f::form_button('totp_confirm', t('title_confirm', 'Confirm'), 'submit', 'class="btn btn-success"'); ?>
										</div>
									</div>

								<?php } else { ?>

									<?php echo f::form_button('totp_setup', t('title_enable_totp', 'Enable TOTP'), 'submit', 'class="btn btn-default"'); ?>

								<?php } ?>
							</div>
						</div>
					</div>
					<?php } ?>

					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_new_password', 'New Password'); ?></div>
								<?php echo f::form_input_password_unmaskable('password', '', 'autocomplete="new-password"'); ?>
							</label>
						</div>
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_confirm_password', 'Confirm Password'); ?></div>
								<?php echo f::form_input_password_unmaskable('confirmed_password', '', 'autocomplete="new-password"'); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_valid_from', 'Valid From'); ?></div>
								<?php echo f::form_input_datetime('valid_from', true); ?>
							</label>
						</div>

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_valid_to', 'Valid To'); ?></div>
								<?php echo f::form_input_datetime('valid_to', true); ?>
							</label>
						</div>
					</div>

					<?php if (!empty($administrator->data['id'])) { ?>
					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_last_ip_address', 'Last IP Address'); ?></div>
								<?php echo f::form_input_text('last_ip_address', true, 'readonly'); ?>
							</label>
						</div>
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_last_hostname', 'Last Hostname'); ?></div>
								<?php echo f::form_input_text('last_hostname', true, 'readonly'); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_last_login', 'Last Login'); ?></div>
								<?php echo f::form_input_text('last_login', true, 'readonly'); ?>
							</label>
						</div>
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_last_active', 'Last Active'); ?></div>
								<?php echo f::form_input_text('last_active', true, 'readonly'); ?>
							</label>
						</div>
					</div>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_known_ip_addresses', 'Known IP Addresses'); ?></div>
						<div class="form-input" readonly style="height: 80px;">
							<?php echo implode(', ', $administrator->data['known_ips']); ?>
						</div>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_known_fingerprints', 'Known Fingerprints'); ?></div>
						<div class="form-input" readonly style="height: 80px;">
							<?php echo implode(', ', $administrator->data['known_fingerprints']); ?>
						</div>
					</label>
					<?php } ?>
				</div>

				<div class="col-md-4">
					<div id="app-permissions" class="form-group">
						<?php echo f::form_checkbox('apps_toggle', ['1', t('title_apps', 'Apps')]); ?>
						<div class="form-input" style="height: 400px; overflow-y: scroll;">
							<ul class="list-unstyled">
<?php
	foreach (f::admin_get_apps() as $app) {
		echo implode(PHP_EOL, [
			'<li data-app="'. f::escape_attr($app['id']) .'">',
			'  '. f::form_checkbox('permissions_structure[apps]['.$app['id'].']', ['1', $app['name']], true),
			'  <ul class="list-unstyled">',
			implode(PHP_EOL, array_map(function($doc) use ($app) {
				return '    <li data-doc="'. f::escape_attr($doc) .'">'. f::form_checkbox('permissions[apps]['.$app['id'].'][docs][]', [$doc], true) .'</li>';
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
						<?php echo f::form_checkbox('widgets_toggle', ['1', t('title_widgets', 'Widgets')]); ?>
						<div class="form-input" style="height: 150px; overflow-y: scroll;">
							<ul class="list-unstyled">
<?php
	foreach (f::admin_get_widgets() as $widget) {
		echo implode(PHP_EOL, [
			'<li>',
			'  '. f::form_checkbox('permissions[widgets][]', ['1', $widget['name']], true),
			'</li>',
		]);
	}
?>
							</ul>
						</div>
					</div>
					<div id="mcp-permissions" class="form-group">
						<?php echo f::form_checkbox('mcp_toggle', ['1', t('title_mcp_tools', 'MCP Tools')]); ?>
						<div class="form-input" style="height: 150px; overflow-y: scroll;">
							<ul class="list-unstyled">
<?php
	foreach (f::admin_get_mcp_tools() as $toolset) {
		echo implode(PHP_EOL, [
			'<li data-mcp-toolset-id="'. f::escape_attr($toolset['id']) .'">',
			'  '. f::form_checkbox('permissions_structure[mcp]['.$toolset['id'].']', ['1', $toolset['name']], true),
			'  <ul class="list-unstyled">',
			implode(PHP_EOL, array_map(function($tool) use ($toolset) {
				return '    <li data-tool="'. f::escape_attr($tool) .'">'. f::form_checkbox('permissions[mcp]['.$toolset['id'].'][]', [$tool], true) .'</li>';
			}, $toolset['tools'])),
			'  </ul>',
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
				<?php echo f::form_button_predefined('save'); ?>
				<?php if (!empty($administrator->data['id'])) echo f::form_button_predefined('delete'); ?>
				<?php echo f::form_button_predefined('cancel'); ?>
			</div>

		<?php echo f::form_end(); ?>
	</div>
</div>

<script>
	// App permissions
	$('input[name="apps_toggle"]').on('change', function() {
		$('input[name^="permissions_structure[apps]"]').prop('disabled', !$(this).is(':checked'));
		$('input[name^="permissions[apps]"][name$="[]"]').prop('disabled', !$(this).is(':checked'));
	}).trigger('change');

	$('input[name^="permissions_structure[apps]"]').on('change', function() {
		if ($(this).prop('checked')) {
			if (!$(this).closest('[data-app]').find('ul :input:checked').length) {
				$(this).closest('[data-app]').find('ul :input').prop('checked', true);
			}
		} else {
			$(this).closest('[data-app]').find('ul :input').prop('checked', false);
		}
	});

	$('input[name^="permissions[apps]"][name$="[]"]').on('change', function() {
		if ($(this).is(':checked')) {
			$(this).closest('ul').closest('[data-app]').children().not('ul').find(':input').prop('checked', true);
		}
	});

	// Widget permissions
	$('input[name="widgets_toggle"]').on('change', function() {
		$('input[name^="permissions[widgets]"]').prop('disabled', !$(this).is(':checked'));
	}).trigger('change');

	// MCP tool permissions
	$('input[name="mcp_toggle"]').on('change', function() {
		$('input[name^="permissions_structure[mcp]"]').prop('disabled', !$(this).is(':checked'));
		$('input[name^="permissions[mcp]"][name$="[]"]').prop('disabled', !$(this).is(':checked'));
	}).trigger('change');

	$('input[name^="permissions_structure[mcp]"]').on('change', function() {
		if ($(this).prop('checked')) {
			if (!$(this).closest('[data-mcp-toolset-id]').find('ul :input:checked').length) {
				$(this).closest('[data-mcp-toolset-id]').find('ul :input').prop('checked', true);
			}
		} else {
			$(this).closest('[data-mcp-toolset-id]').find('ul :input').prop('checked', false);
		}
	});

	$('input[name^="permissions[mcp]"][name$="[]"]').on('change', function() {
		if ($(this).is(':checked')) {
			$(this).closest('ul').closest('[data-mcp-toolset-id]').children().not('ul').find(':input').prop('checked', true);
		}
	});
</script>