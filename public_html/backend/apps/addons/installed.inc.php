<?php

	document::$title[] = t('title_installed_addons', 'Installed Add-ons');

	breadcrumbs::add(t('title_addons', 'Add-Ons'));
	breadcrumbs::add(t('title_installed_addons', 'Installed Add-ons'), document::ilink());

	if (isset($_POST['enable']) || isset($_POST['disable'])) {
		try {

			if (empty($_POST['addons'])) {
				throw new Exception(t('error_must_select_addons', 'You must select add-ons'));
			}

			foreach ($_POST['addons'] as $addon) {
				if (!($addon = basename($addon)) || (!is_dir('storage://addons/' . $addon . '/') && !is_dir('storage://addons/' . $addon . '.disabled/'))) {
					throw new Exception(t('error_invalid_addon_folder', 'Invalid add-on folder') . ' (' . $addon . ')');
				}

				if (!empty($_POST['enable'])) {

					if (!is_dir('storage://addons/' . $addon . '.disabled/')) {
						continue;
					}

					rename('storage://addons/' . $addon . '.disabled/', 'storage://addons/' . $addon . '/');

				} else {

					if (!is_dir('storage://addons/' . $addon . '/')) {
						continue;
					}

					rename('storage://addons/' . $addon . '/', 'storage://addons/' . $addon . '.disabled/');
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

			if (empty($_POST['addons'])) {
				throw new Exception(t('error_must_select_addons', 'You must select add-ons'));
			}

			foreach ($_POST['addons'] as $addon) {

				if ($addon != basename($_POST['addons'])) {
					throw new Exception(t('error_invalid_addon_folder', 'Invalid add-on folder') . ' (' . $addon . ')');
				}

				if (!($addon = basename($addon)) || !is_dir('storage://addons/' . basename($addon) . '/')) {
					throw new Exception(t('error_missing_addon_folder', 'Missing add-on folder') . ' (' . $addon . ')');
				}

				f::file_delete('storage://addons/' . basename($addon) . '/', true);
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
			if (!isset($_FILES['addon']['tmp_name']) || !is_uploaded_file($_FILES['addon']['tmp_name'])) {
				throw new Exception(t('error_must_select_file_upload', 'You must select a file to upload'));
			}

			if (!($id = preg_replace('#^(.*?)(-[0-9\.]+)?(\.vmod)?\.zip$#', '$1', $_FILES['vmod']['name']))) {
				throw new Exception(t('error_could_not_determine_archive_name', 'Could not determine archive name'));
			}

			$folder = 'storage://addons/' . $id . '/';

			$zip = new ZipArchive();
			if ($zip->open($_FILES['vmod']['tmp_name'], ZipArchive::RDONLY) !== true) {
				// ZipArchive::CREATE throws an error with temp files in PHP 8.
				throw new Exception('Failed opening ZIP archive');
			}

			if (!($addon = $zip->getFromName('vmod.xml'))) {
				throw new Exception('Could not find vmod.xml');
			}

			$dom = new DOMDocument('1.0', 'UTF-8');

			if (!@$dom->loadXML($addon) || !$dom->getElementsByTagName('vmod')) {
				throw new Exception(t('error_xml_file_is_not_valid_vmod', 'XML file is not a valid vMod file'));
			}

			if (is_dir($folder)) {
				f::file_delete($folder, true);
			}

			if (!$zip->extractTo(f::file_realpath($folder))) {
				throw new Exception('Failed extracting contents from ZIP archive');
			}

			$zip->close();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;
		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Licenses
	if ($licenses = marketplace_client::get_licenses()) {
		foreach (array_keys($licenses) as $key) {
			$licenses[$key] = $licenses[$key]['addon']['id'];
		}
	} else {
		$licenses = [];
	}

	// Installed add-ons
	$installed_addons = preg_split('#[\r\n]+#', file_get_contents('storage://addons/.installed'), -1, PREG_SPLIT_NO_EMPTY);

	// Table Rows
	$addons = [];

	foreach (f::file_search('storage://addons/*/') as $folder) {
		if (preg_match('#/.cache/#', $folder)) {
			continue;
		}

		$folder_name = preg_replace('#^storage://addons/#', '', $folder);
		$addon = new ent_addon($folder_name);

		$current_addon = [
			'id' => $addon->data['id'],
			'folder' => $addon->data['folder'],
			'status' => $addon->data['status'],
			'installed' => $addon->data['installed'],
			'location' => $addon->data['location'],
			'name' => $addon->data['name'],
			'version' => $addon->data['version'],
			'author' => $addon->data['author'],
			'configurable' => !empty($this->data['settings']),
			'errors' => null,
		];

		$addons[] = $current_addon;
	}

	// Number of Rows
	$num_rows = count($addons);

?>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_installed_addons', 'Installed Add-ons'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo f::form_button_link(document::ilink(__APP__ . '/edit_addon'), t('title_create_new_addon', 'Create New Add-on'), '', 'create'); ?>
	</div>

	<?php echo f::form_begin('addon_form', 'post', '', true); ?>

		<table class="table table-striped table-hover data-table">
			<thead>
				<tr>
					<th><?php echo f::draw_fonticon('icon-check-square-o icon-fw', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th class="main"><?php echo t('title_name', 'Name'); ?> / <?php echo t('title_version', 'Version'); ?></th>
					<th></th>
					<th><?php echo t('title_conflicts', 'Conflicts'); ?></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($addons as $addon) { ?>
				<tr class="<?php echo $addon['status'] ? null : 'semi-transparent'; ?>">
					<td><?php echo f::form_checkbox('addons[]', $addon['id']); ?></td>
					<td><?php echo f::draw_fonticon($addon['status'] ? 'on' : 'off'); ?></td>
					<td>
					<?php if (!empty($addon['marketplace']['addon_id'])) { ?>
						<a class="link" href="<?php echo document::href_ilink(__APP__ . '/marketplace_addon', ['addon_id' => $addon['marketplace']['addon_id']]); ?>">
							<?php echo $addon['name']; ?> / <?php echo $addon['version']; ?>
						</a>
						<?php } else { ?>
						<a class="link" href="<?php echo document::href_ilink(__APP__ . '/addon', ['addon_id' => $addon['id']]); ?>">
							<?php echo $addon['name']; ?> / <?php echo $addon['version']; ?>
						</a>
						<?php } ?>
					</td>
					<td class="text-center">
						<?php echo f::draw_fonticon('icon-star', 'style="color: gold;"'); ?> <?php echo t('text_an_update_is_available', 'An update is available'); ?>
					</td>
					<td class="text-center">
						<?php if (empty($addon['errors'])) { ?>
						<span style="color: #8c4"><?php echo f::draw_fonticon('ok'); ?> <?php echo t('title_ok', 'OK'); ?></span>
						<?php } else { ?>
						<span style="color: #c00"><?php echo f::draw_fonticon('warning'); ?> <?php echo t('title_fail', 'Fail'); ?></span>
						<?php } ?>
					</td>
					<td class="text-center">
						<?php if (!empty($addon['marketplace']['addon_id'])) { ?>
						<?php echo t('title_marketplace_addon', 'Marketplace Add-on'); ?>
						<?php } else { ?>
						<a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__ . '/download', ['addon_id' => $addon['id']]); ?>" title="<?php echo t('title_download', 'Download'); ?>">
							<?php echo f::draw_fonticon('icon-download'); ?> <?php echo t('title_download', 'Download'); ?>
						</a>
						<?php } ?>
					</td>
					<td></td>
					<td>
						<a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__ . '/edit_addon', ['addon_id' => $addon['id']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>">
							<?php echo f::draw_fonticon('edit'); ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_addons', 'Add-ons'); ?>: <?php echo f::format_number($num_rows); ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<div class="grid">
				<div class="col-md-6">
					<fieldset id="actions">

						<legend>
							<?php echo t('text_with_selected', 'With selected'); ?>:
						</legend>

						<div class="flex">

							<div class="btn-group">
								<?php echo f::form_button_predefined('enable'); ?>
								<?php echo f::form_button_predefined('disable'); ?>
							</div>

							<?php echo f::form_button_predefined('delete'); ?>

						</div>
					</fieldset>
			</div>

			<div class="col-md-6">
				<fieldset>
					<legend><?php echo t('title_upload_new_addon', 'Upload a New Add-on'); ?>:</legend>

					<div class="input-group">
						<?php echo f::form_input_file('addon', 'accept="application/zip,application/xml"'); ?>
						<?php echo f::form_button('upload', t('title_upload', 'Upload'), 'submit'); ?>
					</div>
				</fieldset>
			</div>
		</div>

	<?php echo f::form_end(); ?>
</div>

<script>
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>