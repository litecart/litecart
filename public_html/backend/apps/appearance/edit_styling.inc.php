<?php

  $css_file = 'app://frontend/templates/'. settings::get('template') .'/css/variables.css';

  if (!file_exists($css_file)) {
    notices::add('errors', language::translate('error_template_missing_variables', 'This template does not have a variables.css file to edit.'));
  }

  if (!$_POST) {
    $_POST['content'] = file_get_contents($css_file);
  }

  if (!empty($_POST['save'])) {

    try {

      file_put_contents($css_file, $_POST['content']);

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_edit_styling', 'Edit Styling'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('file_form', 'post'); ?>

      <div class="row" style="max-width: 640px;">
        <div class="form-group col-md-8">
          <label><?php echo language::translate('title_file', 'File'); ?></label>
          <div class="form-input" readonly><?php echo parse_url($css_file, PHP_URL_PATH); ?></div>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_content', 'Content'); ?></label>
        <?php echo functions::form_draw_code_field('content', true); ?>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>