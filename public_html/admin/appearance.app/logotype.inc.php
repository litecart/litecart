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

      if (is_file(FS_DIR_APP . 'images/' . $filename)) unlink(FS_DIR_APP . 'images/' . $filename);
      functions::image_delete_cache(FS_DIR_APP . 'images/' . $filename);

      if (settings::get('image_downsample_size')) {
        list($width, $height) = explode(',', settings::get('image_downsample_size'));
        $image->resample($width, $height, 'FIT_ONLY_BIGGER');
      }

      if (!$image->write(FS_DIR_APP . 'images/' . $filename)) {
        throw new Exception(language::translate('error_failed_uploading_image', 'The uploaded image failed saving to disk. Make sure permissions are set.'));
      }

      notices::add('success', language::translate('success_logotype_saved', 'Changes saved successfully. Your browser may still show the old logotype due to cache.'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <div class="panel-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_logotype', 'Logotype'); ?>
    </div>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('logotype_form', 'post', false, true, 'style="max-width: 320px;"'); ?>

      <div class="thumbnail" style="padding: 1em; display: inline-block;  margin-top: 1em;">
        <img src="<?php echo document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/logotype.png', 500, 500, 'FIT_ONLY_BIGGER')); ?>" alt="" />
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_new_image', 'New Image'); ?></label>
        <?php echo functions::form_draw_file_field('image', ''); ?>
      </div>

      <div class="btn-group">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>