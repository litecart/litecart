<?php

  document::$title[] = language::translate('title_logotype', 'Logotype');

  breadcrumbs::add(language::translate('title_appearance', 'Appearance'));
  breadcrumbs::add(language::translate('title_logotype', 'Logotype'));

  if (isset($_POST['upload'])) {

    try {

      if (empty($_FILES['image'])) {
        throw new Exception(language::translate('error_missing_image', 'You must select an image'));
      }

      $image = new ent_image($_FILES['image']['tmp_name']);

      if (!$image->width()) {
        throw new Exception(language::translate('error_invalid_image', 'The image is invalid'));
      }

      $filename = 'logotype.png';

      if (is_file('storage://images/' . $filename)) {
        unlink('storage://images/' . $filename);
      }

      functions::image_delete_cache('storage://images/' . $filename);

      if (settings::get('image_downsample_size')) {
        list($width, $height) = explode(',', settings::get('image_downsample_size'));
        $image->resample($width, $height, 'FIT_ONLY_BIGGER');
      }

      if (!$image->write('storage://images/' . $filename)) {
        throw new Exception(language::translate('error_failed_uploading_image', 'The uploaded image failed saving to disk. Make sure permissions are set.'));
      }

      notices::add('success', language::translate('success_changes_saved_refresh_cache', 'Changes saved successfully. If you don\'t see any changes, try <a href="https://www.google.com/search?q=how+to+hard+refresh+a+web+page" target="_blank">hard refreshing</a> the page or clear browser cache.'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_logotype', 'Logotype'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('logotype_form', 'post', false, true); ?>

      <div style="max-width: 480px;">
        <img class="thumbnail fit" src="<?php echo document::href_rlink('storage://images/logotype.png'); ?>" alt="" style="margin: 0 0 2em 0;">
      </div>

      <div class="form-group" style="max-width: 480px;">
        <label><?php echo language::translate('title_new_image', 'New Image'); ?></label>
        <div class="input-group">
          <?php echo functions::form_input_file('image', 'accept="image/*"'); ?>
          <?php echo functions::form_button('upload', language::translate('title_upload', 'Upload'), 'submit'); ?>
        </div>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>