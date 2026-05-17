<?php

	try {
		if (empty($_GET['path'])) {
			$_GET['path'] = '';
		}

		// Sanitize path
		$_GET['path'] = trim(f::file_resolve_path($_GET['path']), '/') . '/';
		$_GET['path'] = ltrim($_GET['path'], '/');

		if (!is_dir('storage://'. $_GET['path'])) {
			throw new Exception('Invalid path (storage://'. $_GET['path'] .')');
		}

	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
	}

	breadcrumbs::add(t('title_storage', 'Storage'), document::ilink(__APP__.'/storage'));

	if (!empty($_GET['path'])) {

		$prefix = '';

		foreach (preg_split('#/#', $_GET['path'], -1, PREG_SPLIT_NO_EMPTY) as $part) {
			breadcrumbs::add($part, document::ilink(null, ['path' => $prefix .'/'. $part .'/']));
			$prefix .= '/' . $part;
		}
	}

	if (!empty($_GET['filter']['pattern'])) {
		$_GET['filter']['pattern'] = f::file_resolve_path(ltrim($_GET['filter']['pattern'], '/\\'));
	}

	if (!empty($_POST['mkdir'])) {

		try {

			if (empty($_POST['directory'])) {
				throw new Exception('No directory name provided');
			}

			if (!preg_match('#^' . preg_quote('storage://' . rtrim($_GET['path'], '/') . '/' . basename($_POST['directory']), '#'))) {
				throw new Exception(t('error_access_forbidden', 'Access forbiden'));
			}

			if (!mkdir('storage://' . rtrim($_GET['path'], '/') . '/' . basename($_POST['directory']))) {
				throw new Exception('Could not create directory');
			}

			$_GET['path'] = $_GET['path'] .'/'. basename($_POST['directory']);

			redirect(document::ilink(), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (!empty($_POST['chmod'])) {

		try {

			if (empty($_POST['file'])) {
				throw new Exception('No file to chmod');
			}

			if (empty($_POST['chmod'])) {
				throw new Exception('Missing chmod value');
			}

			if (!preg_match('#^'. preg_quote('storage://'. rtrim($_GET['path'], '/') .'/'. basename($_POST['directory']), '#'))) {
				throw new Exception(t('error_access_forbidden', 'Access forbiden'));
			}

			if (!chmod('storage://'. rtrim($_GET['path'], '/') .'/'. basename($_POST['file']), $_POST['chmod'])) {
				throw new Exception('Could not chmod file');
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (!empty($_POST['delete'])) {

		try {

			if (empty($_POST['folders']) && empty($_POST['files'])) {
				throw new Exception('No files or folders selected');
			}

			// Sanitize incoming paths: f::file_resolve_path() throws when a path
			// climbs above the storage root via .. segments.
			if (!empty($_POST['folders'])) {
				foreach ($_POST['folders'] as $folder) {
					$resolved = f::file_resolve_path(trim($folder, '/'));
					if ($resolved === '') continue;
					f::file_delete('storage://' . $resolved, true);
				}
			}

			if (!empty($_POST['files'])) {
				foreach ($_POST['files'] as $file) {
					$resolved = f::file_resolve_path(trim($file, '/'));
					if ($resolved === '') continue;
					f::file_delete('storage://' . $resolved);
				}
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (!empty($_POST['upload'])) {

		try {

			if (empty($_FILES['files'])) {
				throw new Exception('No files uploaded');
			}

			if (empty($_POST['paths'])) {
				throw new Exception('No paths defined for uploaded files');
			}

			if (count($_POST['paths']) != count($_FILES['files']['tmp_name'])) {
				throw new Exception('No paths defined for uploaded files');
			}

			foreach (array_keys($_FILES['files']['tmp_name']) as $key) {
				$new_file = 'storage://' . ltrim($_GET['path'], '/') . f::file_strip_path($_POST['paths'][$key]);
				if (!is_dir(dirname($new_file))) {
					mkdir(dirname($new_file), 0777, true);
				}
				move_uploaded_file($_FILES['files']['tmp_name'][$key], $new_file);
			}

			reload(303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}


	if (isset($_POST['download'])) {

		try {

			if (empty($_POST['folders']) && empty($_POST['files'])) {
				throw new Exception('No files or folders selected');
			}

			// Sanitize incoming paths: reject any traversal above the storage root.
			if (!empty($_POST['folders'])) {
				foreach ($_POST['folders'] as &$_folder) {
					$_folder = f::file_resolve_path(trim($_folder, '/'));
				}
				unset($_folder);
			}

			if (!empty($_POST['files'])) {
				foreach ($_POST['files'] as &$_file) {
					$_file = f::file_resolve_path(trim($_file, '/'));
				}
				unset($_file);
			}

			// Pack multiple files into a zip archive
			if (!empty($_POST['folders']) || count($_POST['files']) > 1) {

				$zip = new ZipArchive();

				$zip_file = tempnam(sys_get_temp_dir(), 'zip');

				if (version_compare(PHP_VERSION, '8.3.0', '>=')) {
					unlink($zip_file); // Fix PHP Deprecation notice: Using empty file as ZipArchive is deprecated
				}

				if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
						throw new RuntimeException('Could not create zip');
				}

				if (!empty($_POST['folders'])) {

					$add_dir_to_zip = function($dir, $zip, $base = '') use (&$add_dir_to_zip) {
						$files = scandir($dir);

						foreach ($files as $file) {
							if ($file === '.' || $file === '..') continue;

							$path = $dir . '/' . $file;
							$local = $base . $file;

							if (is_dir($path)) {
								$zip->addEmptyDir($local);
								$add_dir_to_zip($path, $zip, $local . '/');
							} else {
								$zip->addFile($path, $local);
							}
						}
					};

					foreach ($_POST['folders'] as $current_folder) {

						$realpath = f::file_realpath('storage://' . trim($current_folder, '/') .'/');

						if (!is_dir($realpath)) {
							throw new Exception('Folder does not exist: ' . $current_folder);
						}

						$add_dir_to_zip($realpath, $zip, basename($current_folder) . '/');
					}
				}

				if (!empty($_POST['files'])) {
					foreach ($_POST['files'] as $current_file) {

						$full_path = 'storage://' . ltrim($current_file, '/');

						if (!is_file($full_path)) {
							throw new Exception('File does not exist: ' . $current_file);
						}

						$abs = f::file_realpath($full_path);
						$inner = ltrim(str_replace('\\', '/', substr($abs, strlen(str_replace('\\', '/', FS_DIR_STORAGE)))), '/');
						$zip->addFile($abs, $inner);
					}
				}

				$zip->close();

				$file = $zip_file;
				$filename = 'files-'. date('Ymd-His') .'.zip';

			// Single file download
			} else {
				$file = f::file_realpath('storage://' . trim($_POST['files'][0], '/'));
				$filename = basename($file);
			}

			header('Cache-Control: must-revalidate');
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . $filename);
			header('Content-Length: ' . f::file_size($file));
			header('Expires: 0');

			ob_end_clean();
			readfile($file);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$results = [];

	if (!empty($_GET['filter']['pattern'])) {
		foreach (f::file_search('storage://'. $_GET['path'] . (($_GET['filter']['pattern'] ?? '') ?:  '*')) as $file) {
			if (in_array($file, ['.', '..'])) continue;
			if (empty($_GET['filter']['content']) || (is_file($file) && strpos(file_get_contents($file), $_GET['filter']['content']) !== false)) {
				$results[] = [
					'file' => $file,
					'location' => dirname(preg_replace('#^storage://#', '', $file)) .'/'
				];
			}
		}
	} else {
		foreach (f::file_search('storage://'. $_GET['path'] .'*') as $file) {
			if (in_array($file, ['.', '..'])) continue;
			$results[] = [
				'file' => $file,
				'location' => dirname(preg_replace('#^storage://#', '', $file)) .'/'
			];
		}
	}

	$folders = [];
	$files = [];

	foreach ($results as $entry) {
		$file = $entry['file'];
		$location = $entry['location'];

		if (is_dir($file)) {
			$folders[] = [
				'file' => $file . '/',
				'name' => basename($file) . '/',
				'path' => $_GET['path'] . basename($file) .'/',
				'location' => $location,
				'permissions' => f::file_permissions($file),
				'size' => f::file_size($file),
				'updated_at' => date('Y-m-d H:i:s', filemtime($file)),
				'created_at' => date('Y-m-d H:i:s', filectime($file)),
			];
		} else {

			$fonticon = match(true) {
				(preg_match('#\.(a?png|avif|bmp|gif|ico|jpe?g|webp|tiff?)$#i', $file) === 1) => 'icon-file-image',
				(preg_match('#\.(css|html|js|less|php|scss)$#i', $file) === 1) => 'icon-file-code',
				(preg_match('#\.(doc|pdf|txt|csv)$#i', $file) === 1) => 'icon-file-text',
				default => 'icon-file',
			};

			$files[] = [
				'file' => $file,
				'name' => basename($file),
				'path' => $location . basename($file),
				'location' => $location,
				'extension' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
				'icon' => $fonticon,
				'permissions' => f::file_permissions($file),
				'size' => f::file_size($file),
				'updated_at' => date('Y-m-d H:i:s', filemtime($file)),
				'created_at' => date('Y-m-d H:i:s', filectime($file)),
			];
		}
	}

	$folder_size = array_sum(array_column($folders, 'size')) + array_sum(array_column($files, 'size'));
	$quota = 1 * 1024 * 1024 * 1024;
	$usage = ceil(($folder_size / $quota) * 100);

?>
<style>
.icon-lg {
	font-size: 1.25em;
	margin-right: .5em;
}

.dropzone.in {
	position: relative;

	/* border: 2px dashed #999; */
}

.dropzone .drag-notice {
	display: none;
}

.dropzone.in .drag-notice {
	content: ' ';
	position: absolute;
	top: 0;
	display: flex;
	width: 100%;
	height: 100%;
	justify-content: center;
	text-align: center;
	flex-direction: column;
	background: rgb(0 0 0 / 25%);
	font-size: 2.5em;
	color: #fff;
}

table .icon-folder {
	color: #7ccdff;
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_file_storage', 'File Storage'); ?>
		</div>
	</div>

	<?php echo f::form_begin('upload_form', 'post', '', true); ?>
	<div class="card-action">
		<ul class="flex flex-columns">
			<li><?php echo f::form_input_file('new_files[]', ['multiple' => '']); ?></li>
			<li><?php echo f::form_button('upload', ['true', f::draw_fonticon('icon-upload') . ' ' . t('title_upload', 'Upload')]); ?></li>
			<li><?php echo f::form_button('create_folder', ['true', f::draw_fonticon('icon-folder') . ' ' . t('title_create_new_folder', 'Create New Folder')]); ?></li>
		</ul>
	</div>
	<?php echo f::form_end(); ?>

	<?php echo f::form_begin('search_form', 'get'); ?>
		<div class="card-filter">
			<div class="input-group" style="width: 640px;">
				<span class="input-group-text"><?php echo t('title_location', 'Location'); ?></span>
				<?php echo f::form_input_text('path', $_GET['path']); ?>
			</div>
			<div class="expandable">
				<div class="input-group" style="width: 400px;">
					<span class="input-group-text"><?php echo t('title_filter', 'Filter') ?></span>
					<?php echo f::form_input_text('filter[pattern]', true, ['placeholder' => t('title_filter_pattern', 'Filter Pattern') , 'list' => 'search-patterns']); ?>
					<?php echo f::form_input_text('filter[content]', true, ['placeholder' => t('title_file_contents', 'File Contents')]); ?>
				</div>
			</div>
			<?php echo f::form_button('search', t('title_search', 'Search'), 'submit'); ?>
		</div>
	<?php echo f::form_end(); ?>

	<?php echo f::form_begin('files_form', 'post'); ?>
		<div class="dropzone">
			<table class="table data-table">
				<thead>
					<tr>
						<th><?php echo f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
						<th class="main"><?php echo t('title_file', 'File'); ?></th>
						<?php if (!empty($_GET['filter']['pattern']) || !empty($_GET['filter']['content'])) { ?>
						<th><?php echo t('title_location', 'Location'); ?></th>
						<?php } ?>
						<th class="text-end"><?php echo t('title_size', 'Size'); ?></th>
						<th class="text-end"><?php echo t('title_permissions', 'Permissions'); ?></th>
						<th class="text-end"><?php echo t('title_modified', 'Modified'); ?></th>
						<th class="text-end"><?php echo t('title_created', 'Created'); ?></th>
						<th></th>
					</tr>
				</thead>

				<tbody>
					<?php if (!empty($_GET['path']) && $_GET['path'] != '/') { ?>
					<tr>
						<td colspan="99">
							<?php echo f::draw_fonticon('icon-arrow-left'); ?> <a href="<?php echo document::href_ilink(null, ['path' => dirname($_GET['path']) == '.' ? '' : str_replace('\\', '/', dirname($_GET['path']))]); ?>">
								<?php echo t('title_back', 'Back'); ?>
							</a>
						</td>
					</tr>
					<?php } ?>

					<?php foreach ($folders as $folder) { ?>
					<tr>
						<td><?php echo f::form_checkbox('folders[]', $folder['path']); ?></td>
						<td class="folder">
							<?php echo f::draw_fonticon('icon-folder icon-lg'); ?> <a href="<?php echo document::href_ilink(null, ['path' => $folder['path']]); ?>">
								<?php echo $folder['name']; ?>
							</a>
						</td>
						<?php if (!empty($_GET['filter']['pattern']) || !empty($_GET['filter']['content'])) { ?>
						<td><?php echo dirname($folder['location']); ?></td>
						<?php } ?>
						<td class="text-end"><?php echo f::file_format_size($folder['size']); ?></td>
						<td class="text-end"><tt><?php echo $folder['permissions']; ?></tt></td>
						<td class="text-end"><?php echo f::datetime_when($folder['updated_at']); ?></td>
						<td class="text-end"><?php echo f::datetime_when($folder['created_at']); ?></td>
						<td class="text-end"></td>
					</tr>
					<?php } ?>

					<?php foreach ($files as $file) { ?>
					<tr>
						<td><?php echo f::form_checkbox('files[]', $file['path']); ?></td>
						<td class="file">
							<?php echo f::draw_fonticon($file['icon'] . ' icon-lg'); ?> <a class="" href="<?php echo document::href_ilink(__APP__ . '/edit_file', ['path' => $file['path']]); ?>">
								<?php echo $file['name']; ?>
							</a>
						</td>
						<?php if (!empty($_GET['filter']['pattern']) || !empty($_GET['filter']['content'])) { ?>
						<td><?php echo $file['location']; ?></td>
						<?php } ?>
						<td class="text-end"><?php echo f::file_format_size($file['size']); ?></td>
						<td class="text-end"><tt><?php echo $file['permissions']; ?></tt></td>
						<td class="text-end"><?php echo f::datetime_when($file['updated_at']); ?></td>
						<td class="text-end"><?php echo f::datetime_when($file['created_at']); ?></td>
						<td class="text-end">
							<a class="btn btn-default btn-sm download" href="<?php echo document::href_ilink(__APP__ . '/download', ['path' => $file['path']]); ?>" title="<?php echo t('title_download', 'Download'); ?>">
								<?php echo f::draw_fonticon('icon-download'); ?>
							</a>
						</td>
					</tr>
					<?php } ?>
				</tbody>

				<tfoot>
					<td colspan="99">
						<?php echo t('title_folders', 'Folders'); ?>: <?php echo count($folders); ?>,
						<?php echo t('title_files', 'Files'); ?>: <?php echo count($files); ?>,
						<?php echo t('title_folder_size', 'Folder Size'); ?>: <?php echo f::file_format_size($folder_size); ?>
					</td>
				</tfoot>
			</table>

			<div class="drag-notice">
				<?php echo t('text_drag_and_drop_files_here', 'Drag and drop files here'); ?>
			</div>

		</div>

		<div class="card-body">
			<fieldset id="actions">

				<legend>
					<?php echo t('text_with_selected', 'With selected'); ?>:
				</legend>

				<div class="flex">
					<?php echo f::form_button('download', t('title_download', 'Download'), 'submit', ['class' => 'btn btn-default'], 'icon-download'); ?>
					<?php echo f::form_button_predefined('delete'); ?>
				</div>

			</fieldset>
		</div>
	<?php echo f::form_end(); ?>
</div>

<datalist id="search-patterns">
	<option value="*.php">Display all files ending with .php in the current folder</option>
	<option value="**/*.php">Display all files ending with .php in all recursive folders</option>
	<option value="**/images/">Display all directories named images/ in all recursive folders</option>
</datalist>

<script>
	$('button[name="create_folder"]').on('click', function(e){
		e.preventDefault();
		let folder_name = prompt("<?php echo t('text_new_folder_name', 'New Folder Name'); ?>");
		if (!folder_name) return false;
		let form = $('<form method="post"><input name="mkdir" type="true"><input name="directory" type="hidden"></form>');
		$('input[name="directory"]').val(folder_name).closest('form').submit();
		console.log('x');
	});

	$('.folder .download').on('click', function(e){
		if (!confirm("<?php echo t('text_are_you_sure', 'Are you sure?'); ?>")) return false;
	});

	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');

	$('input[type="file"]').on({
		change: function(){
			$(this).closest('form').submit();
		},
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
					success: function(e){
						location.reload();
					}
				});
			});

			$(this).removeClass('in');
		}
	});

	function getFilesDataTransferItems(dataTransferItems) {
		function traverseFileTreePromise(item, path = '', files) {
			return new Promise(resolve => {
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
</script>
