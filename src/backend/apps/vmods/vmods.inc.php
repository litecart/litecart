<?php

	administrator::require_login();

	document::$title[] = t('title_vmods', 'vMods');

	breadcrumbs::add(t('title_vmods', 'vMods'), document::ilink());

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['vmods'])) {
				throw new Exception(t('error_must_select_vmods', 'You must select vMods'));
			}

			foreach ($_POST['vmods'] as $vmod) {

				$filename = pathinfo($vmod, PATHINFO_FILENAME); // Remove extension

				if (!empty($_POST['enable'])) {
					if (!is_file('storage://vmods/' . $filename .'.disabled')) continue;
					rename('storage://vmods/' . $filename .'.disabled', 'storage://vmods/' . $filename .'.xml');
				} else {
					if (!is_file(FS_DIR_STORAGE . 'vmods/' . $filename .'.xml')) continue;
					rename('storage://vmods/' . $filename .'.xml', 'storage://vmods/' . $filename .'.disabled');
				}
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($_POST['vmods'])) {
				throw new Exception(t('error_must_select_vmods', 'You must select vMods'));
			}

			foreach ($_POST['vmods'] as $vmod) {
				$vmod = new ent_vmod(pathinfo($vmod, PATHINFO_BASENAME));
				$vmod->delete();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['upload'])) {

		try {

			if (!isset($_FILES['vmod']['tmp_name']) || !is_uploaded_file($_FILES['vmod']['tmp_name'])) {
				throw new Exception(t('error_must_select_file_to_upload', 'You must select a file to upload'));
			}

			$dom = new DOMDocument('1.0', 'UTF-8');

			$xml = file_get_contents($_FILES['vmod']['tmp_name']); // DOMDocument::load() does not support Windows paths so we use DOMDocument::loadXML()

			if (!@$dom->loadXML($xml)) {
				throw new Exception(t('error_invalid_xml_file', 'Invalid XML file'));
			}

			if (!$dom->getElementsByTagName('modification')) {
				throw new Exception(t('error_xml_file_is_not_valid_vmod', 'XML file is not a valid vMod file'));
			}

			$filename = 'storage://vmods/' . pathinfo($_FILES['vmod']['name'], PATHINFO_FILENAME) .'.xml';

			if (is_file($filename)) {
				unlink($filename);
			}

			move_uploaded_file($_FILES['vmod']['tmp_name'], $filename);

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Table Rows
	$vmods = [];

	foreach (f::file_search('storage://vmods/*.{xml,disabled}', GLOB_BRACE) as $file) {

		$dom = new DOMDocument('1.0', 'UTF-8');

		$xml = file_get_contents($file); // DOMDocument::load() does not support Windows paths so we use DOMDocument::loadXML()

		if (!@$dom->loadXML($xml)) {
			throw new Exception(t('error_invalid_xml_file', 'Invalid XML file'));
		}

		$vmod = vmod::parse_xml($dom, $file);

		$vmod = array_merge($vmod, [
			'id' => pathinfo($file, PATHINFO_FILENAME),
			'filename' => pathinfo($file, PATHINFO_BASENAME),
			'status' => preg_match('#\.xml$#', $file) ? true : false,
			'errors' => null,
		]);

		if (empty($vmod['version'])) {
			$vmod['version'] = date('Y-m-d', filemtime($file));
		}

		// Check for errors
		try {

			foreach (array_keys($vmod['files']) as $key) {

				foreach (glob(FS_DIR_APP . $vmod['files'][$key]['name'], GLOB_BRACE) as $file) {

					$buffer = file_get_contents($file);

					foreach ($vmod['files'][$key]['operations'] as $i => $operation) {

						$found = preg_match_all($operation['find']['pattern'], $buffer, $matches, PREG_OFFSET_CAPTURE);

						if (!$found) {
							switch ($operation['onerror']) {

								case 'ignore':
									continue 2;

								case 'abort':
								case 'warning':
								default:
									throw new Exception('Operation #'. ($i+1) .' failed in '. preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $file), E_USER_WARNING);
									continue 2;
							}
						}

						if (!empty($operation['find']['indexes'])) {
							rsort($operation['find']['indexes']);

							foreach ($operation['find']['indexes'] as $index) {
								$index = $index - 1; // [0] is the 1st in computer language

								if ($found > $index) {
									$buffer = substr_replace($buffer, preg_replace($operation['find']['pattern'], $operation['insert'], $matches[0][$index][0]), $matches[0][$index][1], strlen($matches[0][$index][0]));
								}
							}

						} else {
							$buffer = preg_replace($operation['find']['pattern'], $operation['insert'], $buffer, -1, $count);

							if (!$count && $operation['onerror'] != 'skip') {
								throw new Exception("Failed to perform insert");
								continue;
							}
						}
					}
				}
			}

		} catch (Exception $e) {
			$vmod['errors'] = $e->getMessage();
		}

		$vmods[] = $vmod;
	}

	// Number of Rows
	$num_rows = count($vmods);
?>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_vmods', 'vMods') ?>™
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink('settings/advanced', ['action' => 'edit', 'key' => 'cache_clear']), t('title_clear_cache', 'Clear Cache'), '', 'icon-square-out') ?>
		<?= f::form_button_link(document::ilink(__APP__.'/edit_vmod'), t('title_create_new_vmod', 'Create New vMod'), '', 'create') ?>
	</div>

	<?= f::form_begin('vmod_form', 'post', '', true) ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th class="text-center"><?= t('title_version', 'Version') ?></th>
					<th><?= t('title_filename', 'Filename') ?></th>
					<th><?= t('title_author', 'Author') ?></th>
					<th><?= t('title_type', 'Type') ?></th>
					<th><?= t('title_health', 'Health') ?></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($vmods as $vmod) { ?>
				<tr class="<?= $vmod['status'] ? null : 'semi-transparent' ?>">
					<td><?= f::form_checkbox('vmods[]', $vmod['filename']) ?></td>
					<td><?= f::draw_fonticon($vmod['status'] ? 'on' : 'off') ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_vmod', ['vmod' => $vmod['filename']]) ?>"><?= $vmod['name'] ?></a></td>
					<td class="text-center"><?= $vmod['version'] ?></td>
					<td><?= $vmod['filename'] ?></td>
					<td><?= $vmod['author'] ?></td>
					<td class="text-center"><?= $vmod['type'] ?></td>
					<td class="text-center">
						<a href="<?= document::href_ilink(__APP__.'/test', ['vmod' => $vmod['filename']]) ?>">
							<?php if (empty($vmod['errors'])) { ?>
							<span style="color: #8c4"><?= f::draw_fonticon('ok') ?> <?= t('title_ok', 'OK') ?></span>
							<?php } else { ?>
							<span style="color: #c00" title="<?= f::escape_html($vmod['errors']) ?>"><?= f::draw_fonticon('warning') ?> <?= t('title_failed', 'Failed') ?></span>
							<?php } ?>
						</a>
					</td>
					<td>
						<?php if (!empty($vmod['settings'])) { ?>
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/configure', ['vmod' => $vmod['filename']]) ?>" title="<?= t('title_configure', 'Configure') ?>"><?= f::draw_fonticon('icon-cog') ?></a>
						<?php } ?>
					</td>
					<td>
						<?php if ($vmod['type'] == 'vMod') { ?>
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/view', ['vmod' => $vmod['filename']]) ?>" title="<?= t('title_view', 'View') ?>"><?= f::draw_fonticon('icon-search') ?></a>
						<?php } ?>
					</td>
					<td>
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/download', ['vmod' => $vmod['id']]) ?>" title="<?= t('title_download', 'Download') ?>"><?= f::draw_fonticon('icon-download') ?></a>
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_vmod', ['vmod' => $vmod['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_vmods', 'vMods') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<div class="grid">
				<div class="col-md-6">
					<fieldset id="actions" disabled>

						<legend>
							<?= t('text_with_selected', 'With selected') ?>:
						</legend>

						<div class="flex">

							<div class="btn-group">
								<?= f::form_button_predefined('enable') ?>
								<?= f::form_button_predefined('disable') ?>
							</div>

							<?= f::form_button_predefined('delete') ?>

						</div>
					</fieldset>
			</div>

			<div class="col-md-6">
				<fieldset>
					<legend><?= t('title_upload_new_vmod', 'Upload a New vMod') ?>:</legend>

					<div class="input-group">
						<?= f::form_input_file('vmod', ['accept' => 'application/zip']) ?>
						<?= f::form_button('upload', t('title_upload', 'Upload'), 'submit') ?>
					</div>
				</fieldset>
			</div>
		</div>

	<?= f::form_end() ?>
</div>

<script>
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>