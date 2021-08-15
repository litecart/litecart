<?php

  document::$snippets['title'][] = language::translate('title_logotype', 'Logotype');

  breadcrumbs::add(language::translate('title_appearance', 'Appearance'));
  breadcrumbs::add(language::translate('title_logotype', 'Logotype'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_FILES['image'])) {
        throw new Exception(language::translate('error_missing_image', 'You must select an image'));
      }

      $image = new ent_image($_FILES['image']['tmp_name']);
      if (!$image->width()) throw new Exception(language::translate('error_invalid_image', 'The image is invalid'));

      $filename = 'logotype.png';

      if (is_file(FS_DIR_STORAGE . 'images/' . $filename)) unlink(FS_DIR_STORAGE . 'images/' . $filename);
      functions::image_delete_cache(FS_DIR_STORAGE . 'images/' . $filename);

      if (settings::get('image_downsample_size')) {
        list($width, $height) = explode(',', settings::get('image_downsample_size'));
        $image->resample($width, $height, 'FIT_ONLY_BIGGER');
      }

      if (!$image->write(FS_DIR_STORAGE . 'images/' . $filename)) {
        throw new Exception(language::translate('error_failed_uploading_image', 'The uploaded image failed saving to disk. Make sure permissions are set.'));
      }

      notices::add('success', language::translate('success_logotype_saved', 'Changes saved successfully. Your browser may still show the old logotype due to cache.'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="card card-app">
  <div class="card-heading">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_logotype', 'Logotype'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('logotype_form', 'post', false, true, 'style="max-width: 320px;"'); ?>

      <div class="thumbnail" style="padding: 1em; display: inline-block;  margin-top: 1em;">
        <img src="<?php echo document::href_rlink(FS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_STORAGE . 'images/logotype.png', 500, 500, 'FIT_ONLY_BIGGER')); ?>" alt="" />
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_new_image', 'New Image'); ?></label>
        <?php echo functions::form_draw_file_field('image', ''); ?>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo (isset($pages->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'class="btn btn-danger" onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>