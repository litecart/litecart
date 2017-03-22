<?php

  if (!empty($_GET['slide_id'])) {
    $slide = new ctrl_slide($_GET['slide_id']);
  } else {
    $slide = new ctrl_slide();
  }

  if (empty($_POST)) {
    foreach ($slide->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($slide->data['id']) ? language::translate('title_edit_slide', 'Edit Slide') : language::translate('title_add_new_slide', 'Add New Slide'));

  if (!empty($_POST['save'])) {

    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));

    if (empty(notices::$data['errors'])) {

      if (empty($_POST['status'])) $_POST['status'] = 0;
      if (empty($_POST['languages'])) $_POST['languages'] = array();

      $fields = array(
        'status',
        'languages',
        'name',
        'caption',
        'link',
        'priority',
        'date_valid_from',
        'date_valid_to',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $slide->data[$field] = $_POST[$field];
      }

      if (is_uploaded_file($_FILES['image']['tmp_name'])) $slide->save_image($_FILES['image']['tmp_name']);

      $slide->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes were successfully saved.'));
      header('Location: '. document::link('', array('doc' => 'slides'), true, array('action', 'slide_id')));
      exit;
    }
  }

  if (!empty($_POST['delete'])) {

    $slide->delete();

    notices::add('success', language::translate('success_changes_saved', 'Changes were successfully saved.'));
    header('Location: '. document::link('', array('doc' => 'slides'), true, array('action', 'slide_id')));
    exit;
  }

?>
<h1><?php echo $app_icon; ?> <?php echo !empty($slide->data['id']) ? language::translate('title_edit_slide', 'Edit Slide') : language::translate('title_add_new_slide', 'Add New Slide'); ?></h1>

<?php echo functions::form_draw_form_begin('slide_form', 'post', false, true, 'style="max-width: 640px;"'); ?>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_status', 'Status'); ?></label>
      <?php echo functions::form_draw_toggle('status', isset($_POST['status']) ? $_POST['status'] : '1', 'e/d'); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_languages', 'Languages'); ?> <em>(<?php echo language::translate('text_leave_blank_for_all', 'Leave blank for all'); ?>)</em></label>
      <div></div>
      <div class="form-control">
        <?php foreach (language::$languages as $language) { ?>
        <div><label><?php echo functions::form_draw_checkbox('languages['. $language['code'] .']', $language['code'], true); ?> <?php echo $language['name']; ?></label></div>
        <?php } ?>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_name', 'Name'); ?></label>
      <?php echo functions::form_draw_text_field('name', true); ?>
    </div>
  </div>

  <?php if (!empty($slide->data['image'])) echo '<p><img src="'. WS_DIR_IMAGES . $slide->data['image'] .'" alt="" class="img-responsive" /></p>'; ?>

  <div class="row">
    <div class="form-group col-md">
      <label><?php echo language::translate('title_image', 'Image'); ?></label>
        <?php echo functions::form_draw_file_field('image'); ?>
        <?php echo (!empty($slide->data['image'])) ? '</label>' . $slide->data['image'] : ''; ?>
    </div>
  </div>

  <ul class="nav nav-tabs">
  <?php foreach (language::$languages as $language) { ?>
    <li<?php echo ($language['code'] == language::$selected['code']) ? ' class="active"' : ''; ?>><a data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a></li>
  <?php } ?>
  </ul>

  <div class="tab-content">
    <?php foreach (array_keys(language::$languages) as $language_code) { ?>
    <div id="<?php echo $language_code; ?>" class="tab-pane fade in<?php echo ($language_code == language::$selected['code']) ? ' active' : ''; ?>">
      <div class="form-group">
        <label><?php echo language::translate('title_caption', 'Caption'); ?></label>
        <?php echo functions::form_draw_regional_wysiwyg_field($language_code, 'caption['. $language_code .']', true, 'style="height: 240px;"'); ?>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_link', 'Link'); ?></label>
        <?php echo functions::form_draw_regional_input_field($language_code, 'link['. $language_code .']', true, ''); ?>
      </div>
    </div>
    <?php } ?>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_date_valid_from', 'Date Valid From'); ?></label>
      <?php echo functions::form_draw_datetime_field('date_valid_from', true); ?>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_date_valid_to', 'Date Valid To'); ?></label>
      <?php echo functions::form_draw_datetime_field('date_valid_to', true); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-3">
      <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
      <?php echo functions::form_draw_number_field('priority', true); ?>
    </div>
  </div>

  <p class="btn-group">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
    <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
    <?php echo (isset($slide->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>