<?php

	administrator::require_login();

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	$new_key = null;

	if (isset($_POST['create'])) {

		try {

			$label = trim((string)($_POST['label'] ?? ''));

			if ($label === '') {
				throw new Exception(t('error_label_required', 'You must provide a label'));
			}

			// The plaintext key is only returned here and never stored
			$new_key = ent_access_key::generate($label);

			notices::add('success', t('success_access_key_created', 'Access key created'));

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['disable']) || isset($_POST['enable'])) {

		try {

			if (empty($_POST['access_keys'])) {
				throw new Exception(t('error_must_select_access_keys', 'You must select access keys'));
			}

			foreach ($_POST['access_keys'] as $access_key_id) {
				$access_key = new ent_access_key($access_key_id);
				$access_key->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$access_key->save();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__ .'/access_keys'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($_POST['access_keys'])) {
				throw new Exception(t('error_must_select_access_keys', 'You must select access keys'));
			}

			foreach ($_POST['access_keys'] as $access_key_id) {
				$access_key = new ent_access_key($access_key_id);
				$access_key->delete();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__ .'/access_keys'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$access_keys = database::prepare(
	"select * from ". DB_PREFIX ."access_keys
	order by status desc, created_at desc;"
	)->fetch_page(function($access_key) {

		$permissions = $access_key['permissions'] ? json_decode($access_key['permissions'], true) : [];
		$mcp_permissions = is_array($permissions) ? ($permissions['mcp'] ?? []) : [];
		$access_key['mcp_restricted'] = !empty($mcp_permissions);
		$access_key['mcp_tool_count'] = $access_key['mcp_restricted'] ? count(array_merge(...array_values($mcp_permissions))) : 0;

		return $access_key;

	}, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);

?>
<div class="card">

	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_access_keys', 'Access Keys') ?>
		</div>
	</div>

	<?php if ($new_key !== null) { ?>
	<div class="card-body">
		<div class="notice notice-success">
			<strong><?= t('title_your_new_access_key', 'Your new access key') ?></strong>
			<p class="text-muted"><?= t('text_access_key_shown_once', 'Copy this key now. It is hashed on the server and cannot be shown again.') ?></p>
			<pre id="new_access_key" class="no-padding"><code><?= $new_key ?></code></pre>
			<button type="button" class="btn btn-default btn-sm" data-copy-target="new_access_key">
				<?= f::draw_fonticon('icon-copy') ?> <?= t('title_copy_key', 'Copy key') ?>
			</button>
		</div>
	</div>
	<?php } ?>

	<div class="card-body">

		<p class="text-muted">
			<?= t('text_access_keys_intro', 'Access keys authenticate MCP clients and REST API clients through the Authorization header. The same key works for both. Only the hash is stored, so a lost key must be replaced.') ?>
			<a href="<?= document::href_ilink(__APP__ .'/instructions') ?>"><?= t('title_view_install_instructions', 'View install instructions') ?></a>
		</p>

		<?= f::form_begin('create_access_key_form', 'post') ?>

			<div class="row">
				<div class="col-md-8">
					<div class="form-group">
						<label><?= t('title_label', 'Label') ?></label>
						<input class="form-input" name="label" placeholder="<?= t('text_label_placeholder', 'Claude Code') ?>" required>
					</div>
				</div>
				<div class="col-md-4">
					<label>&nbsp;</label>
					<?= f::form_button('create', t('title_create_access_key', 'Create Key'), 'submit', ['class' => 'btn btn-primary'], 'add') ?>
				</div>
			</div>

		<?= f::form_end() ?>

	</div>

	<?= f::form_begin('access_keys_form', 'post') ?>

		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check checkbox-toggle', 'data-toggle="checkbox-toggle"') ?></th>
					<th><?= t('title_label', 'Label') ?></th>
					<th><?= t('title_key', 'Key') ?></th>
					<th><?= t('title_mcp_tools', 'MCP Tools') ?></th>
					<th><?= t('title_last_used', 'Last Used') ?></th>
					<th><?= t('title_created', 'Created') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($access_keys as $access_key) { ?>
				<tr class="<?= empty($access_key['status']) ? 'semi-transparent' : null ?>">
					<td><?= f::form_checkbox('access_keys[]', $access_key['id']) ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__ .'/edit_access_key', ['access_key_id' => $access_key['id']]) ?>"><?= f::escape_html($access_key['label']) ?></a></td>
					<td><code><?= f::escape_html($access_key['key_prefix']) ?>…</code></td>
					<td>
						<?php if (!empty($access_key['mcp_restricted'])) { ?>
							<span class="badge badge-warning"><?= t('text_mcp_restricted_n', '{n} tools', ['{n}' => number_format($access_key['mcp_tool_count'])]) ?></span>
						<?php } else { ?>
							<span class="text-muted"><?= t('text_mcp_unrestricted', 'All tools') ?></span>
						<?php } ?>
					</td>
					<td><?= !empty($access_key['last_used_at']) ? f::datetime_format('datetime', strtotime($access_key['last_used_at'])) : '-' ?></td>
					<td><?= f::datetime_format('datetime', strtotime($access_key['created_at'])) ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__ .'/edit_access_key', ['access_key_id' => $access_key['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_access_keys', 'Access Keys') ?>: <?= number_format($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset>
				<legend><?= t('text_with_selected', 'With selected') ?>:</legend>
				<?= f::form_button_predefined('enable') ?>
				<?= f::form_button_predefined('disable') ?>
				<?= f::form_button_predefined('delete') ?>
			</fieldset>
		</div>

	<?= f::form_end() ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?= f::draw_pagination($num_pages) ?>
	</div>
	<?php } ?>
</div>

<script>
	(function() {
		const buttons = document.querySelectorAll('[data-copy-target]');
		buttons.forEach((button) => {
			button.addEventListener('click', () => {
				const target = document.getElementById(button.dataset.copyTarget);
				if (!target) return;
				const text = target.innerText || target.textContent;
				if (!navigator.clipboard) return;
				navigator.clipboard.writeText(text).then(() => {
					const original = button.innerHTML;
					button.innerHTML = '<i class="icon icon-check"></i> <?= f::escape_js(t('title_copied', 'Copied')) ?>';
					setTimeout(() => { button.innerHTML = original; }, 1500);
				});
			});
		});
	})();
</script>