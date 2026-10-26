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

	// Installed MCP toolsets and their tools. Same enumeration as the AI page so
	// the available list stays in one place.
	$toolsets = [];

	foreach (f::file_search('app://shared/mcp/mcp_*.inc.php') as $mcp_file) {

		$toolset = (function() use ($mcp_file) {
			return include $mcp_file;
		})();

		if (!is_array($toolset) || empty($toolset['tools'])) continue;

		$group = $toolset['name'] ?? basename($mcp_file, '.inc.php');

		if (!isset($toolsets[$group])) {
			$toolsets[$group] = [
				'name' => $group,
				'description' => $toolset['description'] ?? '',
				'tools' => [],
			];
		}

		foreach ($toolset['tools'] as $tool) {

			if (empty($tool['name'])) continue;

			$toolsets[$group]['tools'][] = [
				'name' => $tool['name'],
				'description' => $tool['description'] ?? '',
			];
		}
	}

	document::$title[] = !empty($access_key->data['id']) ? t('title_edit_access_key', 'Edit Access Key') : t('title_create_new_access_key', 'Create New Access Key');

	breadcrumbs::add(t('title_access_keys', 'Access Keys'), document::href_ilink(__APP__ .'/access_keys'));
	breadcrumbs::add(!empty($access_key->data['id']) ? t('title_edit_access_key', 'Edit Access Key') : t('title_create_new_access_key', 'Create New Access Key'));

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['label'])) {
				throw new Exception(t('error_label_required', 'You must provide a label'));
			}

			$label = trim((string)$_POST['label']);

			if (empty($_POST['permissions'])) {
				$_POST['permissions'] = [];
			}

			$is_new = empty($access_key->data['id']);

			if ($is_new) {

				// Enroll a new access key: generate the plaintext, store only its hash.
				// The plaintext is captured for the one-time success notice.
				$plaintext_key = ent_access_key::generate($label);
				$plaintext_hash = hash('sha256', $plaintext_key);

				$access_key_id = database::query(
					"select id from ". DB_PREFIX ."access_keys
					where key_hash = '". database::input($plaintext_hash) ."'
					limit 1;"
				)->fetch('id');

				if (!$access_key_id) {
					throw new Exception(t('error_access_key_enroll_failed', 'Could not enroll the new access key'));
				}

				$access_key = new ent_access_key((int)$access_key_id);

			} else {

				foreach ([
					'status',
					'label',
				] as $field) {
					if (isset($_POST[$field])) {
						$access_key->data[$field] = $_POST[$field];
					}
				}
			}

			$access_key->data['permissions'] = $_POST['permissions'];

			$access_key->save();

			if ($is_new) {
				session::$data['flash_access_key'] = $plaintext_key;
			} else {
				notices::add('success', t('success_changes_saved', 'Changes saved'));
			}

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

	if (isset($_POST['regenerate'])) {

		try {

			if (empty($access_key->data['id'])) {
				throw new Exception(t('error_must_select_access_keys', 'You must select an access key to regenerate'));
			}

			// Rotates the key in place, label / permissions / status are preserved
			$plaintext_key = $access_key->regenerate_key();

			session::$data['flash_access_key'] = $plaintext_key;

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

			<div class="grid" style="align-items: start;">

				<div class="col-md-6">

					<div class="form-group">
						<div class="form-label"><?= t('title_status', 'Status') ?></div>
						<?= f::form_toggle('status', 'e/d', $_POST['status'] ?? '1') ?>
					</div>

					<div class="form-group">
						<div class="form-label"><?= t('title_label', 'Label') ?></div>
						<?= f::form_input_text('label', true, ['required' => true]) ?>
					</div>

					<?php if (!empty($access_key->data['id'])) { ?>
					<div class="form-group">
						<div class="form-label"><?= t('title_key', 'Key') ?></div>
						<div class="flex" style="align-items: center; gap: .5em;">
							<code><?= f::escape_html($access_key->data['key_prefix']) ?>…</code>
							<?= f::form_button('regenerate', t('title_regenerate_key', 'Regenerate Key'), 'submit', [
								'formnovalidate' => true,
								'class' => 'btn btn-warning btn-sm',
								'onclick' => 'if (!confirm("' . t('text_regenerate_key_confirm', 'Regenerating will invalidate the existing key. Any client using it will lose access until updated. Continue?') .'")) return false;',
							], 'rotate') ?>
						</div>
					</div>

					<div class="form-group">
						<div class="form-label"><?= t('title_last_used', 'Last Used') ?></div>
						<?php if (!empty($access_key->data['last_used_at'])) { ?>
							<?= f::datetime_format('datetime', strtotime($access_key->data['last_used_at'])) ?>
						<?php } else { ?>
							-
						<?php } ?>
					</div>
					<?php } ?>
				</div>

				<div class="col-md-6">

					<?php if (!empty($toolsets)) { ?>
					<div class="form-group">
						<div class="form-label"><?= t('title_mcp_tools', 'MCP Tools') ?></div>

						<label class="checkbox">
							<input type="checkbox" name="mcp_toggle" value="1"<?= !empty($_POST['mcp_toggle']) ? ' checked' : '' ?>>
							<?= t('text_restrict_mcp_tools', 'Restrict to selected MCP tools. Unchecked = all installed tools are available.') ?>
						</label>

						<div style="margin: .5em 0 0 1.5em;">
							<?php foreach ($toolsets as $toolset) { ?>
							<div data-mcp-toolset-id="<?= f::escape_attr($toolset['name']) ?>" style="margin-bottom: .5em;">
								<?php
									$mcp_tools = $_POST['permissions']['mcp'][$toolset['name']] ?? [];
									$mcp_tools = is_array($mcp_tools) ? $mcp_tools : [];
									$toolset_checked = !empty($mcp_tools);
								?>
								<label class="checkbox">
									<input type="checkbox" name="permissions_structure[mcp][<?= f::escape_attr($toolset['name']) ?>]" value="1"<?= $toolset_checked ? ' checked' : '' ?>>
									<strong><?= f::escape_html($toolset['name']) ?></strong>
									<?php if (!empty($toolset['description'])) { ?>
									<span class="text-muted" style="font-weight: normal; font-size: .9em;">— <?= f::escape_html($toolset['description']) ?></span>
									<?php } ?>
								</label>
								<ul style="list-style: none; padding-left: 1.5em; margin: .25em 0;">
									<?php foreach ($toolset['tools'] as $tool) { ?>
									<li>
										<label class="checkbox">
											<input type="checkbox" name="permissions[mcp][<?= f::escape_attr($toolset['name']) ?>][]" value="<?= f::escape_attr($tool['name']) ?>"<?= in_array($tool['name'], $mcp_tools) ? ' checked' : '' ?>>
											<code><?= f::escape_html($tool['name']) ?></code>
											<?php if (!empty($tool['description'])) { ?>
											<span class="text-muted" style="font-size: .9em;">— <?= f::escape_html($tool['description']) ?></span>
											<?php } ?>
										</label>
									</li>
									<?php } ?>
								</ul>
							</div>
							<?php } ?>
						</div>
					</div>
					<?php } else { ?>
					<div class="form-group">
						<div class="form-label"><?= t('title_permissions', 'Permissions') ?></div>
						<div class="form-input text-muted" style="padding-top: .6em;">
							<?= t('text_no_mcp_tools_installed', 'No MCP tools are installed.') ?>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>

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