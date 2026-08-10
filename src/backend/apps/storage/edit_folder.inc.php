<?php
	if (empty($_GET['path'])) die('No file');

	$_GET['path'] = '/' . f::file_resolve_path($_GET['path']);

	if ((!$folder = f::file_realpath(FS_DIR_STORAGE . $_GET['path'])) || !is_dir($folder) || !preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $folder)) {
		die('Invalid file');
	}

	if (!empty($_GET['path'])) {
		$prefix = '';
		foreach (array_slice(explode('/', ltrim($_GET['path'], '/')), 0, -1) as $part) {
			breadcrumbs::add($part, document::ilink(__APP__.'/files', ['file' => $prefix .'/'. $part . '/']));
			$prefix .= '/' . $part;
		}
		breadcrumbs::add(basename($_GET['path']));
	}

	if (!$_POST) {
		$_POST['filename'] = $_GET['path'];
		$_POST['mode'] = substr(sprintf('%o', fileperms($folder)), -4);
	}

	if (!empty($_POST['save'])) {

		try {

			if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', FS_DIR_STORAGE . ltrim(f::file_resolve_path($_POST['filename']), '/'))) {
				throw new Exception(t('error_access_forbidden', 'Access forbidden'));
			}

			chmod($folder, $_POST['mode']);

			if ($folder != f::realpath(FS_DIR_STORAGE . ltrim($_POST['filename'], '/'))) {
				if (!is_dir(dirname(FS_DIR_STORAGE . ltrim($_POST['filename'], '/')))) {
					mkdir(dirname(FS_DIR_STORAGE . ltrim($_POST['filename'], '/')));
				}
				rename($folder, FS_DIR_STORAGE . ltrim($_POST['filename'], '/'));
			}

			redirect(document::ilink(__APP__.'/files', ['path' => dirname($_POST['filename'])]));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

?>
<style>
textarea[name="content"] {
	background: #2f3244;
	color: #fff;
	height: 640px;
	font-family: "Lucida Console", monospace;
}
</style>

<div class="card" style="min-width: 800px;">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_edit_folder', 'Edit Folder'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo f::form_begin('file_form', 'post'); ?>

			<div class="grid" style="max-width: 640px;">
				<div class="col-md-8">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_filename', 'Filename'); ?></div>
						<?php echo f::form_input_text('filename', true); ?>
					</label>
				</div>

				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_mode', 'Mode'); ?></div>
						<?php echo f::form_input_text('mode', true); ?>
					</label>
				</div>
			</div>

			<div class="card-action">
				<?php echo f::form_button('save', t('title_save', 'Save'), 'submit', ['class' => 'btn btn-success'], 'save'); ?>
				<?php echo !empty($folder) ? f::form_button('delete', t('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. t('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
				<?php echo f::form_button('cancel', t('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
			</div>

		<?php echo f::form_end(); ?>
	</div>
</div>
