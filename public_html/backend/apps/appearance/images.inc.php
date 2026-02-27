<?php

	document::$title[] = t('title_logotype', 'Logotype');

	breadcrumbs::add(t('title_appearance', 'Appearance'));
	breadcrumbs::add(t('title_logotype', 'Logotype'), document::ilink());

	$images = [
		[
			'id' => 'logotype',
			'name' => t('title_logotype', 'Logotype'),
			'file' => 'storage://images/logotype.png',
			'extension' => 'png',
			'mime' => 'image/png',
			'max' => ['width' => 600, 'height' => 200],
		],
		[
			'id' => 'facility',
			'name' => t('title_facility', 'Facility'),
			'file' => 'storage://images/illustration/facility.jpg',
			'extension' => 'jpg',
			'mime' => 'image/jpeg',
			'max' => ['width' => 800, 'height' => 600],
		],
		[
			'id' => 'newsletter',
			'name' => t('title_newsletter', 'Newsletter'),
			'file' => 'storage://images/illustration/letter.svg',
			'extension' => 'svg',
			'mime' => 'image/svg+xml',
			'max' => ['width' => 800, 'height' => 600],
		],
		[
			'id' => 'backend',
			'name' => t('title_backend', 'Backend'),
			'file' => 'app://backend/template/images/background.svg',
			'extension' => 'svg',
			'mime' => 'image/svg+xml',
			'max' => ['width' => 2048, 'height' => 2048],
		],
		// Add more images as needed
	];

	if (isset($_POST['save'])) {

		try {

			foreach ($images as $image) {

				if (!empty($_FILES[$image['id']])) {

					$img = new ent_image($_FILES[$image['id']]['tmp_name']);

					if (!$img->width) {
						throw new Exception(t('error_invalid_image', 'The image is invalid'));
					}

					$img->resample($image['max']['width'], $image['max']['height'], 'FIT_ONLY_BIGGER');

					if (is_file($image['file'])) {
						unlink($image['file']);
					}

					f::image_delete_cache($image['file']);

					if (!$img->save($image['file'])) {
						throw new Exception(t('error_failed_uploading_image', 'The uploaded image failed saving to disk. Make sure permissions are set.'));
					}
				}
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

?>
<style>
[class*="col-"] {
	align-self: center;
}
.form-label {
	font-weight: bold;
	margin-bottom: 0.5em;
}
.image-container {
	position: relative;
	border: 1px solid var(--default-border-color);
	aspect-ratio: 1 / 1;
	align-content: center;
	margin-bottom: 1em;
	border-radius: var(--border-radius);
	overflow: hidden;
	cursor: pointer;
}
.image-container img {
	border-radius: 0;
}
.image-container .format {
	font-weight: 500;
	position: absolute;
	top: 0;
	inset-inline-end: 0;
	background-color: rgba(0, 0, 0, 0.5);
	color: #fff;
	padding: 0.5em 1em;
	border-end-start-radius: var(--border-radius);
	font-size: 0.75em;
}

</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_images', 'Images'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo f::form_begin('images_form', 'post', false, true); ?>

			<div class="grid">
				<?php foreach ($images as $image) { ?>
				<div class="col-md-3">
					<label class="form-group">
						<div class="form-label"><?php echo $image['name']; ?></div>
						<div class="image-container">
							<div class="format"><?php echo strtoupper(pathinfo($image['file'], PATHINFO_EXTENSION)); ?></div>
							<img class="thumbnail fit" src="<?php echo document::href_rlink($image['file']); ?>" data-original="<?php echo document::href_rlink($image['file']); ?>" alt="<?php echo f::escape_attr($image['name']); ?>">
						</div>
						<?php echo f::form_input_file($image['id'], 'accept="'. f::escape_attr($image['mime']) .'"'); ?>
					</label>
				</div>
				<?php } ?>
			</div>

			<div class="card-action">
				<?php echo f::form_button_predefined('cancel'); ?>
				<?php echo f::form_button_predefined('save'); ?>
			</div>

		<?php echo f::form_end(); ?>
	</div>
</div>

<script>
	$('input[type="file"]').on('change', function() {
		$form_group = this.closest('.form-group');
		if (this.files && this.files[0]) {
			var reader = new FileReader();
			reader.onload = function(e) {
				$('img', $form_group).attr('src', e.target.result);
			}
			reader.readAsDataURL(this.files[0]);
		} else {
			$('img', $form_group).attr('src', $('img', $form_group).data('original') );
		}
	});
</script>