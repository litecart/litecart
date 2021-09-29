<?php

  $css_file = 'includes/templates/'. settings::get('store_template_catalog') .'/css/variables.css';

  if (!file_exists(FS_DIR_APP . $css_file)) {
    notices::add('errors', language::translate('error_template_missing_variables', 'This template does not have a variables.css file to edit.'));
  }

  if (!$_POST) {
    $_POST['content'] = file_get_contents(FS_DIR_APP . $css_file);
  }

  if (!empty($_POST['save'])) {

    try {

      file_put_contents(FS_DIR_APP . $css_file, $_POST['content']);

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
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
  font-family: Lucida Console, Monospace;
}
</style>

<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_edit_styling', 'Edit Styling'); ?>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('file_form', 'post'); ?>

      <div class="row" style="max-width: 640px;">
        <div class="form-group col-md-8">
          <label><?php echo language::translate('title_file', 'File'); ?></label>
          <div class="form-control" readonly><?php echo $css_file; ?></div>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_content', 'Content'); ?></label>
        <?php echo functions::form_draw_textarea('content', true); ?>
      </div>

      <div class="panel-action">
        <div class="btn-group">
          <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
          <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        </div>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>