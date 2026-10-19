<?php

	administrator::require_login();

	if (!empty($_GET['access_key_id'])) {
		$access_key = new ent_access_key($_GET['access_key_id']);
	} else {
		$access_key = new ent_access_key();
	}

	if (!$_POST) {
		$_POST = $access_key->data;
		$_POST['mcp_toggle'] = !empty($access_key->data['permissions']['mcp']) ? 1 : 0;
	}

	document::$title[] = !empty($access_key->data['id']) ? t('title_edit_access_key', 'Edit Access Key') : t('title_create_new_access_key', 'Create New Access Key');

	breadcrumbs::add(t('title_access_keys', 'Access Keys'), document::href_ilink(__APP__ .'/access_keys'));
	breadcrumbs::add(!empty($access_key->data['id']) ? t('title_edit_access_key', 'Edit Access Key') : t('title_create_new_access_key', 'Create New Access Key'));

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['label'])) {
				throw new Exception(t('error_label_required', 'You must provide a label'));
			}

			if (empty($_POST['permissions'])) {
				$_POST['permissions'] = [];
			}

			foreach ([
				'status',
				'label',
				'permissions',
			] as $field) {
				if (isset($_POST[$field])) {
					$access_key->data[$field] = $_POST[$field];
				}
			}

			$access_key->save();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__ .'/access_keys'), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($access_key->data['id'])) {
				throw new Exception(t('error_must_select_access_keys', 'You must select access keys'));
			}

			$access_key->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__ .'/access_keys'), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= !empty($access_key->data['id']) ? t('title_edit_access_key', 'Edit Access Key') : t('title_create_new_access_key', 'Create New Access Key') ?>
		</div>
	</div>

	<div class="card-body">

		<?= f::form_begin('access_key_form', 'post', false, false, ['autocomplete' => 'off']) ?>

			<div class="grid">

				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?= t('title_status', 'Status') ?></div>
						<?= f::form_toggle('status', 'e/d', $_POST['status'] ?? '1') ?>
					</label>
				</div>

				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?= t('title_label', 'Label') ?></div>
						<?= f::form_input_text('label', true, ['required' => true]) ?>
					</label>
				</div>

				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?= t('title_label', 'Label') ?></div>
						<?= f::form_input_text('label', true, ['required' => true]) ?>
					</label>
				</div>

				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?= t('title_permissions', 'Permissions') ?></div>
						<div class="form-input text-muted" style="padding-top: .6em;">
							<?= t('text_access_key_permissions_managed_below', 'MCP permissions are managed in the section below') ?>
						</div>
					</label>
				</div>
			</div>

			<?php if (!empty($access_key->data['id'])) { ?>
			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?= t('title_key', 'Key') ?></div>
						<code><?= f::escape_html($access_key->data['key_prefix']) ?>…</code>
					</label>
				</div>
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?= t('title_last_used', 'Last Used') ?></div>
						<?php if (!empty($access_key->data['last_used_at'])) { ?>
							<?= f::datetime_format('datetime', strtotime($access_key->data['last_used_at'])) ?>
						<?php } else { ?>
							-
						<?php } ?>
					</label>
				</div>
			</div>
			<?php } ?>

			<div class="card-action">
				<?= f::form_button_predefined('save') ?>
				<?php if (!empty($access_key->data['id'])) echo f::form_button_predefined('delete'); ?>
				<?= f::form_button_predefined('cancel') ?>
			</div>

		<?= f::form_end() ?>
	</div>
</div>

<script>
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
