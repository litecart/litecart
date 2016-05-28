<?php
  if (isset($_POST['save'])) {

    if (empty($_FILES['image'])) {
      notices::add('errors', language::translate('error_missing_image', 'You must select an image'));
    } else {
      $image = new ctrl_image($_FILES['image']['tmp_name']);
      if (!$image->width()) notices::add('errors', language::translate('error_invalid_image', 'The image is invalid'));
    }

    if (empty(notices::$data['errors'])) {

      $filename = 'logotype.png';

      if (is_file(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename)) unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename);
      functions::image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename);

      if (settings::get('image_downsample_size')) {
        list($width, $height) = explode(',', settings::get('image_downsample_size'));
        $image->resample($width, $height, 'FIT_ONLY_BIGGER');
      }

      if ($image->write(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename, 'png')) {
        notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
        header('Location: '. document::link());
        exit;
      } else {
        notices::add('errors', language::translate('error_failed_uploading_image', 'The uploaded image failed saving to disk. Make sure permissions are set.'));
      }
    }
  }
?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_logotype', 'Logotype'); ?></h1>

<?php echo functions::form_draw_form_begin('logotype_form', 'post', false, true); ?>

  <table>
    <tr>
      <td><img src="<?php echo functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'logotype.png', 500, 250, 'FIT_ONLY_BIGGER'); ?>" alt="" /></td>
    </tr>
    <tr>
      <td><?php echo language::translate('title_new_image', 'New Image'); ?><br />
      <?php echo functions::form_draw_file_field('image', ''); ?></td>
    </tr>
  </table>

  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($pages->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>

<?php echo functions::form_draw_form_end(); ?>