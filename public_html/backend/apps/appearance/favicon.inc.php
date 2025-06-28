<?php

	document::$title[] = t('title_favicon', 'Favicon');

	breadcrumbs::add(t('title_appearance', 'Appearance'));
	breadcrumbs::add(t('title_favicon', 'Favicon'), document::ilink());

	$icon_sizes = [96, 64, 48, 32];
	$thumbnail_sizes = [256, 192, 128];

	if (isset($_POST['upload'])) {

		try {

			if (empty($_FILES['image'])) {
				throw new Exception(t('error_must_select_image', 'You must select an image'));
			}

			if (!extension_loaded('imagick')) {
				throw new Exception('Install Imagick for PHP to continue this operation');
			}

			if (empty(Imagick::queryFormats('ICO'))) {
				throw new Exception('Install icon support for Imagick to continue this operation');
			}

			if (preg_match('#\.svg$#i', $_FILES['image']['name']) && empty(Imagick::queryFormats('SVG'))) {
				throw new Exception('Install SVG support for Imagick to continue this operation');
			}

			$image = new Imagick();
			$image->setBackgroundColor(new ImagickPixel('transparent'));
			$image->readImage($_FILES['image']['tmp_name']);

			$geometry = $image->getImageGeometry();

			if (256 / $geometry['width'] * $geometry['height'] > 256) {
				$image->scaleImage(256, 0);
			} else {
				$image->scaleImage(0, 256);
			}

			$image->cropImage(256, 256, 0, 0);

			foreach ($thumbnail_sizes as $size) {
				$clone = clone $image;
				$clone->setFormat('png32');
				$clone->scaleImage($size, 0);
				$clone->writeImage(FS_DIR_STORAGE . 'images/favicons/favicon-'. $size .'x'. $size .'.png');
			}

			$icon = new Imagick();
			$icon->setFormat('ico');
			foreach ($icon_sizes as $size) {
				$clone = clone $image;
				$clone->scaleImage($size, 0);
				$icon->addImage($clone);
			}

			$icon->writeImages(FS_DIR_STORAGE . 'images/favicons/favicon.ico', true);

			$image->destroy();
			$icon->destroy();
			$clone->destroy();

			header('Cache-Control: only-if-cached; must-revalidate');
			header('Pragma: no-cache');

			notices::add('success', t('success_changes_saved_refresh_cache', 'Changes saved successfully. If you don\'t see any changes, try <a href="https://www.google.com/search?q=how+to+hard+refresh+a+web+page" target="_blank">hard refreshing</a> the page or clear browser cache.'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

?>
<style>
.icons {
	margin-bottom: 2em;
}
.icons .favicon {
	display: inline-block;
	text-align: center;
}
.icons .favicon:not(:first-child) {
	margin-left: .5em;
}
.icons .thumbnail {
	width: auto;
	margin: 0;
	margin-bottom: 1em;
	text-align: center;
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_favicon', 'Favicon'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('favicon_form', 'post', false, true); ?>

			<div class="icons">

				<?php foreach ($thumbnail_sizes as $size) { ?>
				<?php if (is_file($icon = FS_DIR_STORAGE . 'images/favicons/favicon-'.$size.'x'.$size.'.png')) { ?>
				<div class="favicon">
					<img class="thumbnail" src="<?php echo document::href_rlink($icon); ?>" alt="">
					<div><?php echo basename($icon); ?></div>
				</div>
				<?php } ?>
				<?php } ?>

				<?php if (is_file($icon = 'storage://images/favicons/favicon.ico')) { ?>
				<div class="favicon">
					<img class="thumbnail" src="data:image/x-icon;base64,<?php echo base64_encode(file_get_contents($icon)); ?>" width="48" height="48" alt="">
					<div><?php echo basename($icon); ?></div>
				</div>
				<?php } ?>

			</div>

			<div class="form-group" style="max-width: 480px;">
				<label><?php echo t('title_new_icon', 'New Icon'); ?></label>
				<div class="input-group">
					<?php echo functions::form_input_file('image', 'accept=".ico,.png,.svg"'); ?>
					<?php echo functions::form_button('upload', t('title_upload', 'Upload'), 'submit'); ?>
				</div>
			</div>

			<p><?php echo strtr(t('note_favicon_best_result_achieved', 'Note: Best results are achieved by uploading a %size pixels PNG image with alpha transparency.'), ['%size' => '256x256']); ?></p>

		<?php echo functions::form_end(); ?>
	</div>
</div>