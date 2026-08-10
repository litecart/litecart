<?php

	if (!empty($_GET['addon_id'])) {
		$addon = new ent_addon($_GET['addon_id']);
	} else {
		$addon = new ent_addon();
	}

	if (!$_POST) {
		$_POST = $addon->data;
	}

	breadcrumbs::add(!empty($addon->data['id']) ? t('title_edit_addon', 'Edit Add-on') : t('title_create_new_addon', 'Create New Add-on'));

	breadcrumbs::add(t('title_addons', 'Add-ons'), document::ilink(__APP__.'/addons'));
	breadcrumbs::add(!empty($addon->data['id']) ? t('title_edit_addon', 'Edit Add-on') : t('title_create_new_addon', 'Create New Add-on'), document::ilink());

	if (isset($_POST['save']) || isset($_POST['quicksave'])) {

		try {

			if (empty($_POST['id'])) {
				throw new Exception(t('error_must_provide_id', 'You must provide an ID'));
			}

			if (empty($_POST['name'])) {
				throw new Exception(t('error_must_provide_name', 'You must provide a name'));
			}

			foreach ([
				'install',
				'uninstall',
				'upgrades',
				'settings',
				'aliases',
				'files',
			] as $field) {
				if (empty($_POST[$field])) {
					$_POST[$field] = [];
				}
			}

			foreach ([
				'id',
				'status',
				'name',
				'description',
				'author',
				'version',
				'aliases',
				'settings',
				'install',
				'uninstall',
				'upgrades',
				'files',
			] as $field) {
				if (isset($_POST[$field])) {
					$addon->data[$field] = $_POST[$field];
				}
			}

			$addon->save();


			if (isset($_POST['quicksave'])) {
				redirect(document::ilink(__APP__.'/edit_addon', ['addon_id' => $addon->data['id']]), 303);
			} else {
				redirect(document::ilink(__APP__.'/addons'), 303);
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect($redirect_url, 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($addon->data['id'])) {
				throw new Exception(t('error_must_provide_addon', 'You must provide an add-on'));
			}

			$addon->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/addons'), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['upload'])) {

		try {

			if (empty($addon->data['id'])) {
				throw new Exception(t('text_save_addon_to_establish_file_storage', 'Save the add-on to establish a file storage'));
			}

			if (empty($_FILES['files'])) {
				throw new Exception('No files uploaded');
			}

			if (empty($_POST['paths'])) {
				throw new Exception('No paths defined for uploaded files');
			}

			foreach (array_keys($_FILES['files']['tmp_name']) as $key) {
				$new_file = $addon->data['location'] . f::file_strip_path($_POST['paths'][$key]);
				mkdir(dirname($new_file), 0777, true);
				move_uploaded_file($_FILES['files']['tmp_name'][$key], $new_file);
			}

			reload(303);
			exit;

		} catch (Exception $e) {
			http_response_code(400);
			notices::add('errors', $e->getMessage());
		}
	}

	if (!empty($_POST['storage_action'])) {

		try {

			if (empty($addon->data['id'])) {
				throw new Exception(t('text_save_addon_to_establish_file_storage', 'Save the add-on to establish a file storage'));
			}

			if (empty($_POST['file'])) {
				throw new Exception(t('error_must_provide_file', 'You must provide a file'));
			}

			$file = $addon->data['location'] . f::file_strip_path($_POST['file']);

			if (!file_exists($file)) {
				throw new Exception(t('error_file_does_not_exist', 'File does not exist'));
			}

			switch ($_POST['storage_action']) {

				case 'delete':

					f::file_delete($file, true);
					break;

				case 'rename':

					if (empty($_POST['new_name'])) {
						throw new Exception(t('error_must_provide_new_name', 'You must provide a new name'));
					}

					f::file_move($file, $addon->data['location'] . f::file_strip_path($_POST['new_name']));

					break;

				default:
					throw new Exception(t('error_unknown_action', 'Unknown action'));
			}

			reload(302);
			exit;

		} catch (Exception $e) {
			die($e->getMessage());
			http_response_code(400);
			notices::add('errors', $e->getMessage());
		}
	}

	$on_error_options = [
		'warning' => t('title_warning', 'Warning'),
		'ignore' => t('title_ignore', 'Ignore'),
		'cancel' => t('title_cancel', 'Cancel'),
	];

	$method_options = [
		'replace' => t('title_replace', 'Replace'),
		'before' => t('title_before', 'Before'),
		'after' => t('title_after', 'After'),
		'top' => t('title_top', 'Top'),
		'bottom' => t('title_bottom', 'Bottom'),
		'all' => t('title_all', 'All'),
	];

	$type_options = [
		'inline' => t('title_inline', 'Inline'),
		'multiline' => t('title_multiline', 'Multiline'),
		'regex' => t('title_regex', 'RegEx'),
	];

	// List of files
	$files_datalist = [];

	$skip_list = [
		'#.*(?<!\.inc\.php)$#',
		'#^assets/#',
		'#^index.php$#',
		'#^shared/app_header.inc.php$#',
		'#^shared/nodes/nod_vmod.inc.php$#',
		'#^shared/wrappers/wrap_app.inc.php$#',
		'#^shared/wrappers/wrap_storage.inc.php$#',
		'#^install/#',
		'#^storage/#',
	];

	$scripts = f::file_search(FS_DIR_APP . '**.php', GLOB_BRACE);

	foreach ($scripts as $script) {

		$relative_path = f::file_relative_path($script);

		foreach ($skip_list as $pattern) {
			if (preg_match($pattern, $relative_path)) {
				continue 2;
			}
		}

		$files_datalist[] = $relative_path;
	}

	// Files tree
	$draw_folder_contents = function($directory) use ($addon, &$draw_folder_contents) {

		$output = [];

		foreach (scandir($directory) as $file) {

			if (in_array($file, ['.', '..'])) {
				continue;
			}

			if ($directory == 'storage://addons/' . $addon->data['id'] . '/' && $file == 'vmod.xml') {
				continue;
			}

			$relative_path = preg_replace('#^'. preg_quote('storage://addons/'.$addon->data['id'].'/', '#') .'#', '', $directory . $file);

			if (is_dir($directory.$file)) {
				$output[] = '<li>'. f::draw_fonticon('icon-folder icon-lg', 'style="color: #7ccdff;"') .' <span class="item" data-path="'. $relative_path .'">'. $file .'/</span>'. $draw_folder_contents($directory.$file.'/') .'</li>';
			} else {
				$output[] = '<li>'. f::draw_fonticon('icon-file-o') .' <span class="item" data-path="'. $relative_path .'">'. $file .'</span><li>';
			}
		}

		if (!$output) {
			return;
		}

		return implode(PHP_EOL, [
			'<ul class="list-unstyled">',
			implode(PHP_EOL, $output),
			'</ul>',
		]);
	};

	f::draw_lightbox();

?>

<style>
.file-browser {
	background: var(--default-background-color);
	line-height: 2;
}

.file-browser .list {
	height: 415px;
	overflow-y: auto;
}

.file-browser .item {
	cursor: default;
}

.file-browser .item:hover {
	background: rgba(255, 255, 255, 0.5);
}

.file-browser .upload-bar {
	display: flex;
	flex-direction: row;
}

.file-browser .upload-bar .btn {
	line-height: 1;
}

.context-menu {
	position: absolute;
	z-index: 10000;
	background: #fff;
	border-radius: var(--border-radius);
	overflow: hidden;
}

.context-menu .item {
	padding: .5em 1em;
	cursor: pointer;
	border-radius: inherit;
}

.context-menu .item:hover {
	background: #ccc;
}

.dropzone.in {
	position: relative;
}

.dropzone .drag-notice {
	display: none;
}

.dropzone.in .drag-notice {
	content: ' ';
	position: absolute;
	top: 0;
	left: 0;
	display: flex;
	width: 100%;
	height: 100%;
	justify-content: center;
	text-align: center;
	flex-direction: column;
	background: rgba(0, 0, 0, 0.25);
	font-size: 2.5em;
	color: #fff;
}

.operation {
	background: #f8f8f8;
	padding: 1em;
	border-radius: 4px;
	margin-bottom: 2em;
}

html.dark-mode .operation {
	background: #232a3e;
}

.tabs .remove {
	color: #c00;
}

.tabs .icon-plus {
	color: #0c0;
}

.script {
	position: relative;
}

.script .filename {
	position: absolute;
	display: inline-block;
	top: 1px;
	inset-inline-end: 2em;
	padding: .5em 1em;
	border-radius: 0 0 4px 4px;
	background: #fff3;
	backdrop-filter: blur(2px);
	font-size: .8em;
	color: #fffc;
}

#settings .setting:not(:first-child) {
	border-top: 1px solid var(--default-border-color);
	padding-top: 2em;
	margin-top: 2em;
}

.sources .form-code {
	height: max-content;
	max-height: 100vh;
}

fieldset {
	border: none;
	padding: 0;
}

input[name*="[find]"][name$="[content]"],
input[name*="[insert]"][name$="[content]"] {
	height: initial;
}

textarea[name*="[find]"][name$="[content]"],
textarea[name*="[insert]"][name$="[content]"] {
	height: auto;
	transition: all 100ms linear;
}

.tabs a.warning {
	color: red;
}

input.warning,
textarea.warning {
	box-shadow: 0 0 5px 3px rgba(255 0,0, 0.7);
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($addon->data['id']) ? t('title_edit_addon', 'Edit Add-on') : t('title_create_new_addon', 'Create New Add-on'); ?>
		</div>
	</div>

	<?php echo f::form_begin('addon_form', 'post', false, true); ?>

		<nav class="tabs">
			<a class="tab-item active" href="#tab-general" data-toggle="tab"><?php echo t('title_general', 'General'); ?></a>
			<a class="tab-item" href="#tab-modifications" data-toggle="tab"><?php echo t('title_modifications', 'Modifications'); ?></a>
			<a class="tab-item" href="#tab-aliases" data-toggle="tab"><?php echo t('title_aliases', 'Aliases'); ?></a>
			<a class="tab-item" href="#tab-settings" data-toggle="tab"><?php echo t('title_settings', 'Settings'); ?></a>
			<a class="tab-item" href="#tab-install" data-toggle="tab"><?php echo t('title_install_uninstall', 'Install/Uninstall'); ?></a>
		</nav>

		<div class="card-body">
			<div class="tab-contents">
				<div id="tab-general" class="tab-content active">

					<div class="grid">
						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_status', 'Status'); ?></div>
								<?php echo f::form_toggle('status', 'e/d', true); ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_id', 'ID'); ?></div>
								<?php echo f::form_input_text('id', true, ['required' => '', 'placeholder' => 'my_awesome_addon', 'pattern' => '^[0-9a-zA-Z_\-]+$']); ?>
							</label>

							<div class="grid">
								<div class="col-md-8">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_name', 'Name'); ?></div>
										<?php echo f::form_input_text('name', true, ['required' => '', 'placeholder' => 'My Awesome Add-on']); ?>
									</label>
								</div>

								<div class="col-md-4">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_version', 'Version'); ?></div>
										<?php echo f::form_input_text('version', true, ['placeholder' => date('Y-m-d')]); ?>
									</label>
								</div>
							</div>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_description', 'Description'); ?></div>
								<?php echo f::form_input_text('description', true); ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_author', 'Author'); ?></div>
								<?php echo f::form_input_text('author', true); ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_storage_location', 'Storage Location'); ?></div>
								<div class="form-input" readyonly><?php echo (($addon->data['location'] ?? '') ?: '<em>' . t('text_save_addon_to_establish_file_storage', 'Save the add-on to establish a file storage') . '</em>'); ?></div>
							</label>
							<?php if (!empty($addon->data['id'])) { ?>
							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_created_at', 'Created At'); ?></div>
										<div><?php echo f::datetime_when($addon->data['created_at']); ?></div>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_updated_at', 'Updated At'); ?></div>
										<div><?php echo f::datetime_when($addon->data['updated_at']); ?></div>
									</label>
								</div>
							</div>
							<?php } ?>
						</div>

						<div class="col-md-8">
							<div class="form-group">
								<label class="form-label"><?php echo t('title_file_storage', 'File Storage'); ?></label>
								<div class="file-browser form-input">
									<div class="dropzone">

										<?php if (!empty($addon->data['id'])) { ?>
										<ul class="list list-unstyled">
											<li><strong><?php echo f::draw_fonticon('icon-folder icon-lg', 'style="color: #7ccdff;"'); ?> [<?php echo t('title_root', 'Root'); ?>]</strong>
												<?php echo $draw_folder_contents($addon->data['location']); ?>
											</li>
										</ul>

										<div class="drag-notice">
											<?php echo t('text_drag_and_drop_files_and_folders_here', 'Drag and drop files and folders here'); ?>
										</div>
										<?php } else { ?>
										<div>
											<em><?php echo t('text_save_addon_to_establish_file_storage', 'Save the add-on to establish a file storage'); ?></em>
										</div>
										<?php } ?>
									</div>

									<?php if (!empty($addon->data['id'])) { ?>
									<div class="upload-bar">
										<?php echo f::form_input_file('files[]', ['multiple' => '']); ?>
										<?php echo f::form_button('upload', ['true', t('title_upload', 'Upload')]); ?>
									</div>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>

				</div>

				<div id="tab-modifications" class="tab-contents">

					<h2><?php echo t('title_modifications', 'Modifications'); ?></h2>

					<nav class="tabs">
						<?php foreach (array_keys($addon->data['files']) as $f) { ?>
						<a class="tab-item" data-toggle="tab" href="#tab-<?php echo $f; ?>">
							<span class="file"><?php echo f::escape_html($_POST['files'][$f]['name']); ?></span> <span class="btn btn-default btn-sm remove" title="<?php t('title_remove', 'Remove'); ?>"><?php echo f::draw_fonticon('remove'); ?></span>
						</a>
						<?php } ?>
						<a class="tab-item add" href="#"><?php echo f::draw_fonticon('add'); ?></a>
					</nav>

					<div id="files" class="tab-contents">

						<?php if (!empty($_POST['files'])) foreach (array_keys($_POST['files']) as $f) { ?>
						<div id="tab-<?php echo $f; ?>" class="tab-contents" data-tab-index="<?php echo $f; ?>">

							<div class="grid">
								<div class="col-md-6">

									<h3><?php echo t('title_file_to_modify', 'File To Modify'); ?></h3>

									<label class="form-group">
										<div class="form-label"><?php echo t('title_file_pattern', 'File Pattern'); ?></div>
										<?php echo f::form_input_text('files['.$f.'][name]', true, ['placeholder' => 'path/to/file.php', 'list' => 'scripts']); ?>
									</label>

									<div class="sources"></div>
								</div>

								<div class="col-md-6">

									<h3><?php echo t('title_operations', 'Operations'); ?></h3>

									<div class="operations">
										<?php $i=1; foreach (array_keys($_POST['files'][$f]['operations']) as $o) { ?>
										<fieldset class="operation">

											<div class="float-end">
												<a class="btn btn-default btn-sm move-up" href="#"><?php echo f::draw_fonticon('move-up'); ?></a>
												<a class="btn btn-default btn-sm move-down" href="#"><?php echo f::draw_fonticon('move-down'); ?></a>
												<a class="btn btn-default btn-sm remove" href="#"><?php echo f::draw_fonticon('remove'); ?></a>
											</div>

											<h3><?php echo t('title_operation', 'Operation'); ?> #<span class="number"><?php echo $i++; ?></span></h3>

											<div class="grid">
												<div class="col-md-3">
													<label class="form-group">
														<div class="form-label"><?php echo t('title_method', 'Method'); ?></div>
														<?php echo f::form_select('files['.$f.'][operations]['.$o.'][method]', $method_options, true); ?>
													</label>
												</div>

												<div class="col-md-6">
													<label class="form-group">
														<div class="form-label"><?php echo t('title_match_type', 'Match Type'); ?></div>
														<?php echo f::form_toggle('files['.$f.'][operations]['.$o.'][type]', $type_options, (!isset($_POST['files'][$f]['operations'][$o]['type']) || $_POST['files'][$f]['operations'][$o]['type'] == '') ? 'multiline' : true); ?>
													</label>
												</div>

												<div class="col-md-3">
													<label class="form-group">
														<div class="form-label"><?php echo t('title_on_error', 'On Error'); ?></div>
														<?php echo f::form_select('files['.$f.'][operations]['.$o.'][onerror]', $on_error_options, true); ?>
													</label>
												</div>
											</div>

											<label class="form-group">
												<h4><?php echo t('title_find', 'Find'); ?></h4>
												<?php if (isset($_POST['files'][$f]['operations'][$o]['type']) && in_array($_POST['files'][$f]['operations'][$o]['type'], ['inline', 'regex'])) { ?>
												<?php echo f::form_input_text('files['.$f.'][operations]['.$o.'][find][content]', true, 'class="form-code" required'); ?>
												<?php } else { ?>
												<?php echo f::form_input_code('files['.$f.'][operations]['.$o.'][find][content]', true, 'required'); ?>
												<?php } ?>
											</label>

											<div class="grid" style="font-size: .8em;">
												<div class="col-md-2">
													<label class="form-group">
														<div class="form-label"><?php echo t('title_index', 'Index'); ?></div>
														<?php echo f::form_input_text('files['.$f.'][operations]['.$o.'][find][index]', true, 'placeholder="1,3,.."'); ?>
													</label>
												</div>

												<div class="col-md-2">
													<label class="form-group">
														<div class="form-label"><?php echo t('title_offset_before', 'Offset Before'); ?></div>
														<?php echo f::form_input_text('files['.$f.'][operations]['.$o.'][find][offset-before]', true, 'placeholder="0"'); ?>
													</label>
												</div>

												<div class="col-md-2">
													<label class="form-group">
														<div class="form-label"><?php echo t('title_offset_after', 'Offset After'); ?></div>
														<?php echo f::form_input_text('files['.$f.'][operations]['.$o.'][find][offset-after]', true, 'placeholder="0"'); ?>
													</label>
												</div>
											</div>

											<label class="form-group">
												<h4><?php echo t('title_insert', 'Insert'); ?></h4>
												<?php if (isset($_POST['files'][$f]['operations'][$o]['type']) && in_array($_POST['files'][$f]['operations'][$o]['type'], ['inline', 'regex'])) { ?>
												<?php echo f::form_input_text('files['.$f.'][operations]['.$o.'][insert][content]', true, 'class="form-code"'); ?>
												<?php } else { ?>
												<?php echo f::form_input_code('files['.$f.'][operations]['.$o.'][insert][content]', true); ?>
												<?php } ?>
											</label>

										</fieldset>
										<?php } ?>

									</div>

									<div class="text-end">
										<a class="btn btn-default add" href="#">
											<?php echo f::draw_fonticon('add'); ?> <?php echo t('title_add_operation', 'Add Operation'); ?>
										</a>
									</div>

								</div>
							</div>

						</div>
						<?php } ?>
					</div>

				</div>

				<div id="tab-aliases" class="tab-contents">

					<h2><?php echo t('title_aliases', 'Aliases'); ?></h2>

					<div class="aliases">

						<?php if (!empty($_POST['aliases'])) foreach (array_keys($_POST['aliases']) as $key) { ?>
						<fieldset class="alias">

							<div class="grid">
								<div class="col-md-4">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_key', 'Key'); ?></div>
										<div class="input-group">
											<span class="input-group-text" style="font-family: monospace;">{alias:</span>
											<?php echo f::form_input_text('aliases['.$key.'][key]', true, ['required' => '']); ?>
											<span class="input-group-text" style="font-family: monospace;">}</span>
										</div>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_value', 'Value'); ?></div>
										<?php echo f::form_input_text('aliases['.$key.'][value]'); ?>
									</label>
								</div>

								<div class="col-md-2" style="align-self: center;">
									<?php echo f::form_button('aliases[new_alias_index][move_up]', f::draw_fonticon('move-up'), 'button', ['class' => 'btn btn-default btn-sm', 'title' => f::escape_attr(t('title_move_up', 'Move Up'))]); ?>
									<?php echo f::form_button('aliases[new_alias_index][move_down]', f::draw_fonticon('move-down'), 'button', ['class' => 'btn btn-default btn-sm', 'title' => f::escape_attr(t('title_move_down', 'Move Down'))]); ?>
									<?php echo f::form_button('aliases[new_alias_index][remove]', f::draw_fonticon('remove'), 'button', ['class' => 'btn btn-default btn-sm', 'title' => f::escape_attr(t('title_remove', 'Remove'))]); ?>
								</div>
							</div>
						</fieldset>
						<?php } ?>

					</div>

					<div class="form-group" style="margin-top: 2em;">
						<?php echo f::form_button('add_alias', t('title_add_alias', 'Add Alias'), 'button', ['class' => 'btn btn-default'], 'add'); ?>
					</div>

				</div>

				<div id="tab-settings" class="tab-contents">

					<h2><?php echo t('title_settings', 'Settings'); ?></h2>

					<div id="settings" style="max-width: 1200px;">
						<?php if (!empty($_POST['settings'])) foreach (array_keys($_POST['settings']) as $key) { ?>
						<fieldset class="setting">

							<div class="grid">
								<div class="col-md-4">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_key', 'Key'); ?></div>
										<div class="input-group">
											<span class="input-group-text" style="font-family: monospace;">{setting:</span>
											<?php echo f::form_input_text('settings['.$key.'][key]', true, 'required'); ?>
											<span class="input-group-text" style="font-family: monospace;">}</span>
										</div>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_title', 'Title'); ?></div>
										<?php echo f::form_input_text('settings['.$key.'][title]', true, ['required' => '']); ?>
									</label>
								</div>

								<div class="col-md-2 text-center" style="align-self: center;">
									<?php echo f::form_button('settings['.$key.'][move_up]', f::draw_fonticon('move-up'), 'button', ['class' => 'btn btn-default btn-sm', 'title' => f::escape_attr(t('title_move_up', 'Move Up'))]); ?>
									<?php echo f::form_button('settings['.$key.'][move_down]', f::draw_fonticon('move-down'), 'button', ['class' => 'btn btn-default btn-sm', 'title' => f::escape_attr(t('title_move_down', 'Move Down'))]); ?>
									<?php echo f::form_button('settings['.$key.'][remove]', f::draw_fonticon('remove'), 'button', ['class' => 'btn btn-default btn-sm', 'title' => f::escape_attr(t('title_remove', 'Remove'))]); ?>
								</div>
							</div>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_description', 'Description'); ?></div>
								<?php echo f::form_input_text('settings['.$key.'][description]', true, ['required' => '']); ?>
							</label>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_function', 'Function'); ?></div>
										<?php echo f::form_input_text('settings['.$key.'][function]', true, ['required' => '', 'placeholder' => 'text()']); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_default_value', 'Default Value'); ?></div>
										<?php echo f::form_input_text('settings['.$key.'][default_value]'); ?>
									</label>
								</div>
							</div>
						</fieldset>
						<?php } ?>
					</div>

					<div class="form-group" style="margin-top: 2em;">
						<?php echo f::form_button('add_setting', t('title_add_setting', 'Add Setting'), 'button', ['class' => 'btn btn-default'], 'add'); ?>
					</div>

				</div>

				<div id="tab-install" class="tab-contents">

					<div class="grid">
						<div class="col-md-6">
							<h2><?php echo t('title_install', 'Install'); ?></h2>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_script', 'Script'); ?></div>
								<?php echo f::form_input_code('install', true, ['style' => 'height: 200px;']); ?>
							</label>
						</div>

						<div class="col-md-6">
							<h2><?php echo t('title_uninstall', 'Uninstall'); ?></h2>
							<label class="form-group">
								<div class="form-label"><?php echo t('title_script', 'Script'); ?></div>
								<?php echo f::form_input_code('uninstall', true, ['style' => 'height: 200px;']); ?>
							</label>
						</div>
					</div>

					<h2><?php echo t('title_upgrade_patches', 'Upgrade Patches'); ?></h2>

					<div class="upgrades">
						<?php if (!empty($_POST['upgrades'])) foreach (array_keys($_POST['upgrades']) as $key) { ?>
						<fieldset class="upgrade">
							<label class="form-group" style="max-width: 250px;">
								<div class="form-label"><?php echo t('title_version', 'Version'); ?></div>
								<?php echo f::form_input_text('upgrades['.$key.'][version]', true); ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_script', 'Script'); ?></div>
								<?php echo f::form_input_code('upgrades['.$key.'][script]', true, ['style' => 'height: 200px;']); ?>
							</label>
						</fieldset>
						<?php } ?>
					</div>

					<div class="form-group" style="margin-top: 2em;">
						<?php echo f::form_button('add_patch', t('title_add_patch', 'Add Patch'), 'button', ['class' => 'btn btn-default'], 'add'); ?>
					</div>

				</div>
			</div>

			<div class="card-action">
				<?php echo f::form_button_predefined('quicksave'); ?>
				<?php if (!empty($addon->data['id'])) echo f::form_button_predefined('delete'); ?>
				<?php echo f::form_button_predefined('cancel'); ?>
			</div>
		</div>
	<?php echo f::form_end(); ?>
</div>

<div id="modal-uninstall" style="display: none;">
	<?php echo f::form_begin('uninstall_form', 'post'); ?>

		<h2><?php echo t('title_uninstall_addon', 'Uninstall Add-on'); ?></h2>

		<p>
			<?php echo f::form_checkbox('cleanup', ['1', t('text_remove_all_traces_of_the_addon', 'Remove all traces of the add-on such as database tables, settings, etc.')], ''); ?>
		</p>

		<div>
			<?php echo f::form_button('delete', t('title_uninstall', 'Uninstall'), 'submit', ['class' => 'btn btn-danger']); ?>
			<?php echo f::form_button('cancel', t('title_cancel', 'Cancel'), 'button'); ?>
		</div>

	<?php echo f::form_end(); ?>
</div>

<div id="new-tab-content-template" style="display: none;">
	<div id="tab-new_tab_index" class="tab-contents">

		<div class="grid">
			<div class="col-md-6">

				<label class="form-group">
					<div class="form-label"><?php echo t('title_file_pattern', 'File Pattern'); ?></div>
					<?php echo f::form_input_text('files[new_tab_index][name]', true, ['placeholder' => 'path/to/file.php', 'list' => 'scripts']); ?>
				</label>

				<div class="sources"></div>
			</div>

			<div class="col-md-6">
				<div class="operations"></div>
				<div>
					<a class="btn btn-default add" href="#">
						<?php echo f::draw_fonticon('add'); ?> <?php echo t('title_add_operation', 'Add Operation'); ?>
					</a>
				</div>
			</div>
		</div>

	</div>
</div>

<div id="new-operation-template" style="display: none;">
	<fieldset class="operation">

		<div class="float-end">
			<a class="btn btn-default btn-sm move-up" href="#"><?php echo f::draw_fonticon('move-up'); ?></a>
			<a class="btn btn-default btn-sm move-down" href="#"><?php echo f::draw_fonticon('move-down'); ?></a>
			<a class="btn btn-default btn-sm remove" href="#"><?php echo f::draw_fonticon('remove'); ?></a>
		</div>

		<h3><?php echo t('title_operation', 'Operation'); ?> #<span class="number"></span></h3>

		<div class="grid">
			<div class="col-md-3">
				<label class="form-group">
					<div class="form-label"><?php echo t('title_method', 'Method'); ?></div>
					<?php echo f::form_select('files[current_tab_index][operations][new_operation_index][method]', $method_options, 'after'); ?>
				</label>
			</div>

			<div class="col-md-6">
				<label class="form-group">
					<div class="form-label"><?php echo t('title_match_type', 'Match Type'); ?></div>
					<?php echo f::form_toggle('files[current_tab_index][operations][new_operation_index][type]', $type_options, 'multiline'); ?>
				</label>
			</div>

			<div class="col-md-3">
				<label class="form-group">
					<div class="form-label"><?php echo t('title_on_error', 'On Error'); ?></div>
					<?php echo f::form_select('files[current_tab_index][operations][new_operation_index][onerror]', $on_error_options, ''); ?>
				</label>
			</div>
		</div>

		<label class="form-group">
			<h4><?php echo t('title_find', 'Find'); ?></h4>
			<?php echo f::form_input_code('files[current_tab_index][operations][new_operation_index][find][content]', '', ['class' => 'form-code', 'required' => '']); ?>
		</label>

		<div class="grid" style="font-size: .8em;">
			<div class="col-md-2">
				<label class="form-group">
					<div class="form-label"><?php echo t('title_index', 'Index'); ?></div>
					<?php echo f::form_input_text('files[current_tab_index][operations][new_operation_index][find][index]', '', ['placeholder' => '1,3,..']); ?>
				</label>
			</div>

			<div class="col-md-2">
				<label class="form-group">
					<div class="form-label"><?php echo t('title_offset_before', 'Offset Before'); ?></div>
					<?php echo f::form_input_text('files[current_tab_index][operations][new_operation_index][find][offset-before]', '', ['placeholder' => '0']); ?>
				</label>
			</div>

			<div class="col-md-2">
				<label class="form-group">
					<div class="form-label"><?php echo t('title_offset_after', 'Offset After'); ?></div>
					<?php echo f::form_input_text('files[current_tab_index][operations][new_operation_index][find][offset-after]', '', ['placeholder' => '0']); ?>
				</label>
			</div>
		</div>

		<label class="form-group">
			<h4><?php echo t('title_insert', 'Insert'); ?></h4>
			<?php echo f::form_input_code('files[current_tab_index][operations][new_operation_index][insert][content]', '', ['class' => 'form-code']); ?>
		</label>

	</fieldset>
</div>

<datalist id="scripts">
	<?php foreach ($files_datalist as $option) { ?>
	<option><?php echo $option; ?></option>
	<?php } ?>
</datalist>

<script>

	$('input[name="name"]').on('change', function() {
		if ($(this).val() != '' && $('input[name="id"]').val() == '') {
			$('input[name="id"]').val($(this).val().toLowerCase().replace(/[^a-z0-9_\- ]/g, '').replace(/[^a-z0-9_]+/g, '_'));
		}
	});

	// Tabs


	$('.tabs').on('click', '[data-toggle="tab"]', function(e) {
		let $target = $(this).attr('href');
		$target.find(':input[name$="[content]"]').trigger('input');
	});

	$('.tabs .add').on('click', function(e) {
		e.preventDefault();

		let __index__ = 1;
		while ($('.tab-content[id="tab-new_'+__index__+'"]').length) __index__++;

		let $tab = $([
			'<a class="nav-link" data-toggle="tab" href="#tab-__index__">',
			'	<span class="file">__index__</span> <span class="btn btn-default btn-sm remove" title="<?php t('title_remove', 'Remove'); ?>">',
			'		<?php echo f::draw_fonticon('remove'); ?></span></a>',
		].join('\n')
			.replace(/__index__/g, 'new_' + __index__)
		);

		$tab_pane = $(
			$('#new-tab-content-template').html()
			.replace(/__index__/g, __index__++)
		).hide();

		$(this).before($tab);
		$('#files').append($tab_pane);

		$(this).prev().trigger('click');
	});

	$('.tabs').on('click', '.remove', function(e) {
		e.preventDefault();

		if (!confirm("<?php echo t('text_are_you_sure', 'Are you sure?'); ?>")) return false;

		let $tab = $(this).closest('.nav-link'),
			tab_pane = $($tab.attr('href'));

		if ($tab.prev('[data-toggle="tab"]').length) {
			$tab.prev('[data-toggle="tab"]').trigger('click');

		} else if ($tab.next('[data-toggle="tab"]').length) {
			$tab.next('[data-toggle="tab"]').trigger('click');
		}

		$tab_pane.remove();
		$tab.remove();
	});

	// Storage

	$('input[type="file"]').on({
		//change: function(){
		//	$(this).closest('form').submit();
		//},
		mouseenter: function(){
			$('.dropzone').addClass('in');
		},
		mouseleave: function(){
			$('.dropzone').removeClass('in');
		}
	});

	$('.dropzone').on({

		dragover: function(e){
			e.preventDefault();
			e.stopPropagation();
			$(this).addClass('in');
		},

		dragenter: function(e){
			$(this).addClass('in');
		},

		dragleave: function(e){
			let dropzone = this.getBoundingClientRect();
			if (e.originalEvent.x < dropzone.left || e.originalEvent.x > dropzone.left + dropzone.width
			|| e.originalEvent.y < dropzone.top || e.originalEvent.y > dropzone.top + dropzone.height) {
				$(this).removeClass('in');
			}
		},

		drop: function(e) {
			e.stopPropagation();
			e.preventDefault();

			let items = e.originalEvent.dataTransfer.items;

			getFilesDataTransferItems(items).then(files => {

				let form_data = new FormData();

				$.each(files, function(i, file) {
					form_data.append('files[]', file);
					form_data.append('paths[]', file.relpath);
				});

				form_data.append('upload', 'true');

				$.ajax({
					type: 'post',
					data: form_data,
					processData: false,
					contentType: false,
					dataType: 'html',
					success: function(response){
						$('.file-browser').html(
							$('.file-browser', response).html()
						);
					}
				});
			});

			$(this).removeClass('in');
		}
	});

	function getFilesDataTransferItems(dataTransferItems) {
		function traverseFileTreePromise(item, path = '', files) {
			return new Promise(resolve => {
				if (!item) return;
				if (item.isFile) {
					item.file(file => {
						file.relpath = (path || '') + file.name;
						files.push(file);
						resolve(file);
					});
				} else if (item.isDirectory) {
					let dirReader = item.createReader();
					dirReader.readEntries(entries => {
						let entriesPromises = [];
						for (let entr of entries)
							entriesPromises.push(
								traverseFileTreePromise(entr, (path || '') + item.name + '/', files)
							);
						resolve(Promise.all(entriesPromises));
					});
				}
			});
		}

		let files = [];
		return new Promise((resolve, reject) => {
			let entriesPromises = [];
			for (let it of dataTransferItems)
				entriesPromises.push(
					traverseFileTreePromise(it.webkitGetAsEntry(), null, files)
				);
			Promise.all(entriesPromises).then(entries => {
				resolve(files);
			});
		});
	}

	$('.file-browser').on('contextmenu', '.item', function(e) {
		e.preventDefault();

		$item = $(this);

		let $contextmenu = $([
			'<nav class="context-menu">',
			'	<ul class="list-unstyled">',
			'		<li class="item rename"><?php echo f::draw_fonticon('edit'); ?> <?php echo t('title_rename', 'Rename'); ?></a>',
			'		<li class="item delete"><?php echo f::draw_fonticon('delete'); ?> <?php echo t('title_delete', 'Delete'); ?></a>',
			'	</ul>',
			'</nav>',
		].join('\n'));


		$contextmenu.find('.rename').on('click', function(){

			let form_data = new FormData();
			form_data.append('storage_action', 'rename');
			form_data.append('file', $item.data('path'));

			let new_name = prompt('<?php echo t('title_new_name', 'New Name'); ?>', $item.data('path'));

			if (!new_name) {
				$('.context-menu').remove();
				$('body').off('click');
				return;
			}

			form_data.append('new_name', new_name.trim());

			$.ajax({
				type: 'post',
				data: form_data,
				processData: false,
				contentType: false,
				dataType: 'html',
				success: function(response){
					$('.file-browser').html(
						$('.file-browser', response).html()
					);
					$('.context-menu').remove();
					$('body').off('click');
				}
			});
		});

		$contextmenu.find('.delete').on('click', function(){

			if (!confirm("<?php echo t('text_are_you_sure', 'Are you sure?'); ?>")) {
				$('.context-menu').remove();
				$('body').off('click');
				return;
			}

			let form_data = new FormData();
			form_data.append('storage_action', 'delete');
			form_data.append('file', $item.data('path'));

			$.ajax({
				type: 'post',
				data: form_data,
				processData: false,
				contentType: false,
				dataType: 'html',
				success: function(){
					$item.closest('li').remove();
					$('.context-menu').remove();
					$('body').off('click');
				}
			});
		});

		$('body').on('click', function(e) {
			$conextmenu = $(e.target).closest('.context-menu');
			if (!$conextmenu.length) {
				$contextmenu.remove();
				$('body').off('click');
			}
		});

		$contextmenu.css({
			left: e.pageX,
			top: e.pageY,
		}).appendTo('body');
	});

	// Operations

	let reindex_operations = function($operations) {
		let index = 1;
		$operations.find('.operation').each(function(i, $operation){
			$operation.find('.number').text(index++);
		});
	};

	$('#files').on('change', ':input[name$="[type]"]', function(e) {
		e.preventDefault();
		let match_type = $(this).val();

		$(this).closest('.operation').find(':input[name$="[content]"]').each(function(i, $field){
			switch (match_type) {

				case 'inline':
				case 'regex':
					var $newfield = $('<input class="form-code" name="'+ $field.attr('name') +'" type="text">').val($field.val());
					$field.replaceWith($newfield);
					break;

				default:
					var $newfield = $('<textarea class="form-code" name="'+ $field.attr('name') +'"></textarea>').val($field.val());
					$field.replaceWith($newfield);
					break;
			}
		});

		$(this).closest('.operation').find(':input[name$="[find][content]"]').trigger('input');
	});

	$('#files').on('change', ':input[name$="[method]"]', function(e) {
		e.preventDefault();

		let method = $(this).val();

		if ($.inArray(method, ['top', 'bottom', 'all']) != -1) {
			$(this).closest('.operation').find(':input[name*="[find]"]').prop('disabled', true);
		} else {
			$(this).closest('.operation').find(':input[name*="[find]"]').prop('disabled', false);
		}
	});

	$('#files :input[name$="[method]"]').trigger('change');

	// Auto expand textareas
	$('body').on('input', 'textarea.form-code', function() {
		$(this).css('height', '');
		$(this).css('height', Math.min(this.scrollHeight + 10, 250) + 'px');
	});

	$('textarea.form-code').trigger('input');

	$('.tab-content').on('input', ':input[name^="files"][name$="[name]"]', function() {
		let $tab_pane = $(this).closest('.tab-content'),
			tab_index = $tab_pane.attr('id').replace(/^tab-/, ''),
			tab_name = $tab_pane.find('input[name$="[name]"]').val();

		$('a[href="#tab-'+ tab_index +'"] .file').text(tab_name);

		let file_pattern = $(this).closest('.grid').find(':input[name^="files"][name$="[name]"]').val(),
			url = '<?php echo document::ilink(__APP__.'/sources', [
				'pattern' => 'thepattern',
			]); ?>'.replace(/thepattern/, file_pattern);

		$.get(url, function(result) {
			$tab_pane.find('.sources').html('');

			$.each(result, function(file, source_code) {

				var $script = $(
					'<div class="script">' +
					'	<div class="form-code"></div>' +
					'	<div class="filename"></div>' +
					'</div>'
				);

				$script.find('.form-code').text(source_code);
				$script.find('.filename').text(file);
				$tab_pane.find('.sources').append($script);
			});

			$tab_pane.find(':input[name$="[find][content]"]').trigger('input');
		});
	});

	$(':input[name^="files"][name$="[name]"]').trigger('input');


	$('#files').on('click', '.add', function(e) {
		e.preventDefault();

		let $operations = $(this).closest('.tab-content').find('.operations'),
			tab_index = $(this).closest('.tab-content').data('tab-index');

		let __index__ = $(':input[name$="[find][content]"]').length || 0;
		let output = $('#new-operation-template').html()
			.replace('current_tab_index', tab_index)
			.replace(/__index__/g, 'new_'+__index__);

		$operations.append(output);
		reindex_operations($operations);
	});

	$('#files').on('click', '.move-up, .move-down', function(e) {
		e.preventDefault();

		let $row = $(this).closest('.operation'),
			$operations = $(this).closest('.operations');

		if ($(this).is('.move-up') && $row.prevAll().length > 0) {
			$row.insertBefore($row.prev());
		} else if ($(this).is('.move-down') && $row.nextAll().length > 0) {
			$row.insertAfter($row.next());
		}

		reindex_operations($operations);
	});

	$('#files').on('click', '.remove', function(e) {
		e.preventDefault();

		let $operations = $(this).closest('.operations');

		if (!confirm("<?php echo t('text_are_you_sure', 'Are you sure?'); ?>")) return;

		$(this).closest('.operation').remove();
		reindex_operations($operations);

		$operations.find(':input[name$="[find][content]"]').trigger('input');
	});

	// Validate operation
	$('#files').on('input', ':input[name*="[find]"]', function() {

		let $tab = $(this).closest('.tab-content'),
			$operation = $(this).closest('.operation'),
			method = $operation.find(':input[name$="[method]"]').val(),
			find = $operation.find(':input[name$="[find][content]"]').val(),
			type = $operation.find(':input[name$="[type]"]:checked').val(),
			indexes = $operation.find(':input[name$="[index]"]').val().split(/\s*,\s*/).filter(Boolean),
			offset_before = $operation.find(':input[name$="[offset-before]"]').val(),
			offset_after = $operation.find(':input[name$="[offset-after]"]').val(),
			onerror = $operation.find(':input[name$="[onerror]"]').val(),
			regex_flags = 's';

		try {

			switch (method) {

				case 'top':
					find = '^';
					break;

				case 'bottom':
					find = '$';
					break;

				case 'all':
					find = '^.*$';
					break;

				case 'before':
				case 'after':
				case 'replace':

					// Trim
					find = find.trim();

					// Cook the regex pattern
					if (type == 'regex') {

						find_operators = 'g' + find.substr(find.lastIndexOf(find.substr(0, 1)) + 1);
						find = find.substr(1, find.lastIndexOf(find.substr(0, 1)) - 1);

					} else if (type == 'inline') {

						find = find.replace(/[\-\[\]{}()*+?.,\\\^$|#]/g, "\\$&");

					} else {

						// Whitespace
						find = find.split(/\r\n?|\n/);

						for (let i = 0; i < find.length; i++) {
							if (find[i] = find[i].trim()) {
								find[i] = '[ \t]*' + find[i].replace(/[\-\[\]{}()*+?.,\\\^$|#]/g, "\\$&") + '[ \t]*(?:\r\n?|\n|$)';
							} else if (i != (find.length - 1)) {
								find[i] = '[ \t]*(?:\r\n?|\n)';
							}
						}
						find = find.join('');

						// Offset
						if (offset_before != '') {
							find = '(?:.*?(?:\r\n?|\n)){'+ offset_before +'}'+ find;
						}

						if (offset_after != '') {
							find = find + '(?:.*?(?:\r\n?|\n|$)){0,'+ offset_after +'}';
						}
					}

					regex_flags = 'gm';

					break;

				default:
					throw new Error('Unknown error');
			}

			$.each($tab.find('.script'), function() {

				let regex = new RegExp(find, regex_flags),
					source = $(this).find('.form-code').text(),
					matches = (source.match(regex) || []).length;

				if (!matches) {
					throw new Error('Failed matching content');
				}

				if (indexes && Math.max(indexes) > (matches + 1)) {
					throw new Error('Failed matching an index');
				}
			});

			$operation.find(':input[name$="[find][content]"]').removeAttr('title').removeClass('warning');

		} catch (err) {
			if (onerror != 'ignore') {
				$operation.find(':input[name$="[find][content]"]').attr('title', err.message).addClass('warning');
			}
		}

		if ($tab.find(':input.warning').length) {
			$('.nav-link[href="#'+ $tab.attr('id') +'"]').addClass('warning');
		} else {
			$('.nav-link[href="#'+ $tab.attr('id') +'"]').removeClass('warning');
		}
	});

	// Aliases
	$('button[name="add_alias"]').on('click', function() {

		let __index__ = 0;
		while ($(':input[name^="aliases[new_'+__index__+']"]').length) __index__++;

		let $output = $([
			'<fieldset class="alias">',
			'	<div class="grid">',
			'		<div class="col-md-4">',
			'			<label class="form-group">',
			'				<div class="form-label"><?php echo t('title_key', 'Key'); ?></div>',
			'				<div class="input-group">',
			'					<span class="input-group-text" style="font-family: monospace;">{alias:</span>',
			'					<?php echo f::form_input_text('aliases[__index__][key]', '', ['required' => '']); ?>',
			'					<span class="input-group-text" style="font-family: monospace;">}</span>',
			'				</div>',
			'			</label>',
			'		</div>',
			'',
			'		<div class="col-md-6">',
			'			<label class="form-group">',
			'				<div class="form-label"><?php echo f::escape_js(t('title_value', 'Value')); ?></div>',
			'				<?php echo f::escape_js(f::form_input_text('aliases[__index__][value]', '', ['required' => ''])); ?>',
			'			</label>',
			'		</div>',
			'',
			'		<div class="col-md-2" style="align-self: center;">',
			'		 <?php echo f::form_button('aliases[__index__][move_up]', f::draw_fonticon('move-up'), 'button', ['class' => 'btn btn-default btn-sm', 'title' => f::escape_attr(t('title_move_up', 'Move Up'))]); ?>',
			'		 <?php echo f::form_button('aliases[__index__][move_down]', f::draw_fonticon('move-down'), 'button', ['class' => 'btn btn-default btn-sm', 'title' => f::escape_attr(t('title_move_down', 'Move Down'))]); ?>',
			'		 <?php echo f::form_button('aliases[__index__][remove]', f::draw_fonticon('remove'), 'button', ['class' => 'btn btn-default btn-sm', 'title' => f::escape_attr(t('title_remove', 'Remove'))]); ?>',
			'		</div>',
			'	</div>',
			'</fieldset>'
		].join('\n')
			.replace(/__index__/g, 'new_' + __index__)
		);

		$('.aliases').append($output);
	});

	$('#aliases').on('click', 'button[name$="[move_up]"], button[name$="[move_down]"]', function(e) {
		e.preventDefault();

		let $row = $(this).closest('.alias');

		if ($(this).is('button[name$="[move_up]"]') && $row.prevAll().length > 0) {
			$row.insertBefore($row.prev());
		} else if ($(this).is('button[name$="[move_down]"]') && $row.nextAll().length > 0) {
			$row.insertAfter($row.next());
		}
	});

	$('#aliases').on('click', 'button[name$="[remove]"]', function(e) {
		e.preventDefault();

		if (!confirm("<?php echo t('text_are_you_sure', 'Are you sure?'); ?>")) return;
		$(this).closest('.alias').remove();
	});

	// Settings
	$('button[name="add_setting"]').on('click', function() {

			let __index__ = 0;
			while ($(':input[name^="settings[new_'+__index__+']"]').length) __index__++;

		let $output = $([
			'<fieldset class="setting">',
			'',
			'	<div class="grid">',
			'		<div class="col-md-4">',
			'			<label class="form-group">',
			'				<div class="form-label"><?php echo t('title_key', 'Key'); ?></div>',
			'				<div class="input-group">',
			'					<span class="input-group-text" style="font-family: monospace;">{setting:</span>',
			'					<?php echo f::form_input_text('settings[__index__][key]', '', ['required' => '']); ?>',
			'					<span class="input-group-text" style="font-family: monospace;">}</span>',
			'				</div>',
			'			</label>',
			'		</div>',
			'',
			'		<div class="col-md-6">',
			'			<label class="form-group">',
			'				<div class="form-label"><?php echo f::escape_js(t('title_title', 'Title')); ?></div>',
			'				<?php echo f::escape_js(f::form_input_text('settings[new_setting_index][title]', '', ['required' => ''])); ?>',
			'			</label>',
			'		</div>',
			'',
			'		<div class="col-md-2 text-center" style="align-self: center;">',
			'			<?php echo f::form_button('settings[__index__][move_up]', f::draw_fonticon('move-up'), 'button', ['class' => 'btn btn-default btn-sm', 'title' => f::escape_attr(t('title_move_up', 'Move Up'))]); ?>',
			'			<?php echo f::form_button('settings[__index__][move_down]', f::draw_fonticon('move-down'), 'button', ['class' => 'btn btn-default btn-sm', 'title' => f::escape_attr(t('title_move_down', 'Move Down'))]); ?>',
			'			<?php echo f::form_button('settings[__index__][remove]', f::draw_fonticon('remove'), 'button', ['class' => 'btn btn-default btn-sm', 'title' => f::escape_attr(t('title_remove', 'Remove'))]); ?>',
			'		</div>',
			'	</div>',
			'',
			'	<label class="form-group">',
			'		<div class="form-label"><?php echo f::escape_js(t('title_description', 'Description')); ?></div>',
			'		<?php echo f::escape_js(f::form_input_text('settings[__index__][description]', '', ['required' => ''])); ?>',
			'	</label>',
			'',
			'	<div class="grid">',
			'		<div class="col-md-6">',
			'			<label class="form-group">',
			'				<div class="form-label"><?php echo f::escape_js(t('title_function', 'Function')); ?></div>',
			'				<?php echo f::escape_js(f::form_input_text('settings[__index__][function]', '', ['required' => ''])); ?>',
			'			</label>',
			'		</div>',
			'',
			'		<div class="col-md-6">',
			'			<label class="form-group">',
			'				<div class="form-label"><?php echo f::escape_js(t('title_default_value', 'Default Value')); ?></div>',
			'				<?php echo f::escape_js(f::form_input_text('settings[__index__][default_value]', '')); ?>',
			'			</label>',
			'		</div>',
			'	</div>',
			'',
			'</fieldset>'
		].join('\n')
			.replace(/__index__/g, 'new_' + __index__)
		);

		$('#settings').append($output);
	});

	$('#settings').on('click', 'button[name$="[move_up]"], button[name$="[move_down]"]', function(e) {
		e.preventDefault();

		let $row = $(this).closest('.setting');

		if ($(this).is('button[name$="[move_up]"]') && $row.prevAll().length > 0) {
			$row.insertBefore($row.prev());
		} else if ($(this).is('button[name$="[move_down]"]') && $row.nextAll().length > 0) {
			$row.insertAfter($row.next());
		}
	});

	$('#settings').on('click', 'button[name$="[remove]"]', function(e) {
		e.preventDefault();

		if (!confirm("<?php echo t('text_are_you_sure', 'Are you sure?'); ?>")) return;
		$(this).closest('.setting').remove();
	});

	// Upgrade Patches
	$('button[name="add_patch"]').on('click', function() {

			let __index__ = 0;
			while ($(':input[name^="upgrades[new_'+__index__+']"]').length) __index__++;

		let $output = $([
			'<fieldset class="upgrade">',
			'	<label class="form-group" style="max-width: 250px;">',
			'		<div class="form-label"><?php echo f::escape_js(t('title_version', 'Version')); ?></div>',
			'		<?php echo f::escape_js(f::form_input_text('upgrades[__index__][version]', '')); ?>',
			'	</label>',
			'',
			'	<label class="form-group">',
			'		<div class="form-label"><?php echo f::escape_js(t('title_script', 'Script')); ?></div>',
			'		<?php echo f::escape_js(f::form_input_code('upgrades[__index__][script]', '', ['style' => 'height: 200px;'])); ?>',
			'	</label>',
			'</fieldset>'
		].join('\n')
			.replace(/__index__/g, 'new_' + __index__)
		);

		$('.upgrades').append($output);
	});

	$('.card-action button[name="delete"]').on('click', function(e) {
		e.preventDefault();
		$.litebox('#modal-uninstall');
	});

	$('body').on('click', '.litebox button[name="cancel"]', function(e) {
		$.litebox.close();
	});
</script>