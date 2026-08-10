<?php
	if (empty($_GET['path'])) die('No file');

	$_GET['path'] = f::file_resolve_path(ltrim($_GET['path'], '/'));

	if ((!$file = f::file_realpath('storage://' . $_GET['path'])) || !is_file($file) || !preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $file)) {
		die('Invalid file');
	}

	if (!empty($_GET['path'])) {
		$prefix = '';
		foreach (array_slice(explode('/', $_GET['path']), 0, -1) as $part) {
			breadcrumbs::add($part, document::ilink(__APP__.'/files', ['path' => $prefix .'/'. $part . '/']));
			$prefix .= '/' . $part;
		}
		breadcrumbs::add(basename($_GET['path']));
	}

	if (f::file_is_binary('storage://' . $_GET['path'])) {
		notices::add('warnings', 'File is a binary file');
		$disable_editing = true;
	}

	if (!$_POST) {
		$_POST['filename'] = $_GET['path'];
		$_POST['mode'] = substr(sprintf('%o', fileperms($file)), -4);
		$_POST['content'] = file_get_contents($file);
	}

	if (!empty($_POST['save'])) {

		try {

			if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', FS_DIR_STORAGE . ltrim(f::file_resolve_path($_POST['filename']), '/'))) {
				throw new Exception(t('error_access_forbidden', 'Access forbidden'));
			}

			chmod($file, $_POST['mode']);

			if (empty($disable_editing)) {
				file_put_contents($file, $_POST['content']);
			}

			if ($file != f::realpath('storage://' . ltrim($_POST['filename'], '/'))) {
				if (!is_dir(dirname('storage://' . ltrim($_POST['filename'], '/')))) {
					mkdir(dirname('storage://' . ltrim($_POST['filename'], '/')));
				}
				rename($file, 'storage://' . ltrim($_POST['filename'], '/'));
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
	height: 640px;
}
</style>

<div class="card" style="min-width: 800px;">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_edit_file', 'Edit File'); ?>
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

			<label class="form-group">
				<div class="form-label"><?php echo t('title_content', 'Content'); ?></div>
				<?php echo f::form_input_code('content', true, !empty($disable_editing) ? 'placeholder="Binary Data" disabled' : ''); ?>
			</label>

			<div class="card-action">
				<?php echo f::form_button('save', t('title_save', 'Save'), 'submit', ['class' => 'btn btn-success'], 'save'); ?>
				<?php echo !empty($file) ? f::form_button('delete', t('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. t('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
				<?php echo f::form_button('cancel', t('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
			</div>

		<?php echo f::form_end(); ?>
	</div>
</div>
