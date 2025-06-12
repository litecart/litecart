<?php

	document::$title[] = language::translate('title_logotype', 'Logotype');

	breadcrumbs::add(language::translate('title_appearance', 'Appearance'));
	breadcrumbs::add(language::translate('title_logotype', 'Logotype'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			if (!empty($_FILES['logotype'])) {

				$image = new ent_image($_FILES['logotype']['tmp_name']);

				if (!$image->width) {
					throw new Exception(language::translate('error_invalid_image', 'The image is invalid'));
				}

				$file = 'storage://images/logotype.png';

				if (is_file($file)) {
					unlink($file);
				}

				functions::image_delete_cache($file);

				$image->resample(512, 512, 'FIT_ONLY_BIGGER');

				if (!$image->save($file)) {
					throw new Exception(language::translate('error_failed_uploading_image', 'The uploaded image failed saving to disk. Make sure permissions are set.'));
				}
			}

			if (!empty($_FILES['facility'])) {

				$image = new ent_image($_FILES['facility']['tmp_name']);

				if (!$image->width) {
					throw new Exception(language::translate('error_invalid_image', 'The image is invalid'));
				}

				$file = 'storage://images/illustration/facility.jpg';

				if (is_file($file)) {
					unlink($file);
				}

				functions::image_delete_cache($file);

				$image->resample(512, 512, 'FIT_ONLY_BIGGER');

				if (!$image->save($file)) {
					throw new Exception(language::translate('error_failed_uploading_image', 'The uploaded image failed saving to disk. Make sure permissions are set.'));
				}
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$images = [
		[
			'id' => 'logotype',
			'name' => language::translate('title_logotype', 'Logotype'),
			'file' => 'storage://images/logotype.png',
		],
		[
			'id' => 'facility',
			'name' => language::translate('title_facility', 'Facility'),
			'file' => 'storage://images/illustration/facility.jpg',
		]
	];

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
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_images', 'Images'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('images_form', 'post', false, true); ?>

			<div class="grid">
				<?php foreach ($images as $image) { ?>
				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?php echo $image['name']; ?></div>
						<div class="image-container">
							<img class="thumbnail fit" src="<?php echo document::href_rlink($image['file']); ?>" data-original="<?php echo document::href_rlink($image['file']); ?>" alt="<?php echo functions::escape_attr($image['name']); ?>">
						</div>
						<?php echo functions::form_input_file($image['id'], 'accept="image/*"'); ?>
					</label>
				</div>
				<?php } ?>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('cancel'); ?>
				<?php echo functions::form_button_predefined('save'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>

<script>
	$('input[type="file"]').on('change', function() {
		$form_group = $(this).closest('.form-group');
		if (this.files && this.files[0]) {
			var reader = new FileReader();
			reader.onload = function(e) {
				$form_group.find('img').attr('src', e.target.result);
			}
			reader.readAsDataURL(this.files[0]);
		} else {
			$form_group.find('img').attr('src', $form_group.find('img').data('original') );
		}
	});
</script>