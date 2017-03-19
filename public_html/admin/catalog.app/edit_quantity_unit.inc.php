<?php

  if (!empty($_GET['quantity_unit_id'])) {
    $quantity_unit = new ctrl_quantity_unit($_GET['quantity_unit_id']);
  } else {
    $quantity_unit = new ctrl_quantity_unit();
  }

  if (empty($_POST)) {
    foreach ($quantity_unit->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($quantity_unit->data['id']) ? language::translate('title_edit_quantity_unit', 'Edit Quantity Unit') : language::translate('title_add_new_quantity_unit', 'Add New Quantity Unit'));

  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));

    if (empty(notices::$data['errors'])) {

      if (empty($_POST['separate'])) $_POST['separate'] = 0;

      $fields = array(
        'decimals',
        'separate',
        'priority',
        'name',
        'description',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $quantity_unit->data[$field] = $_POST[$field];
      }

      $quantity_unit->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('doc' => 'quantity_units'), true, array('quantity_unit_id')));
      exit;
    }
  }

  if (isset($_POST['delete'])) {

    $quantity_unit->delete();

    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('doc' => 'quantity_units'), true, array('quantity_unit_id')));
    exit;
  }

?>
<h1><?php echo $app_icon; ?> <?php echo !empty($quantity_unit->data['id']) ? language::translate('title_edit_quantity_unit', 'Edit Quantity Unit') : language::translate('title_add_new_quantity_unit', 'Add New Quantity Unit'); ?></h1>

<?php echo functions::form_draw_form_begin('quantity_unit_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_name', 'Name'); ?></label>
      <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md">
      <label><?php echo language::translate('title_description', 'Description'); ?></label>
      <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'description['. $language_code .']', true); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-4">
      <label><?php echo language::translate('title_decimals', 'Decimals'); ?></label>
      <?php echo functions::form_draw_number_field('decimals', true); ?>
    </div>

    <div class="form-group col-md-8">
      <br />
      <div class="checkbox">
        <label><?php echo functions::form_draw_checkbox('separate', '1', true); ?> <?php echo language::translate('text_separate_added_cart_items', 'Separate added cart items'); ?></label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-4">
      <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
      <?php echo functions::form_draw_number_field('priority', true); ?>
    </div>
  </div>

  <p class="btn-group">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
    <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
    <?php echo (isset($quantity_unit->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>