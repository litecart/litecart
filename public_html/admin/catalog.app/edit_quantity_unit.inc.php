<?php

  if (!empty($_GET['quantity_unit_id'])) {
    $quantity_unit = new ent_quantity_unit($_GET['quantity_unit_id']);
  } else {
    $quantity_unit = new ent_quantity_unit();
  }

  if (empty($_POST)) {
    $_POST = $quantity_unit->data;
  }

  document::$snippets['title'][] = !empty($quantity_unit->data['id']) ? language::translate('title_edit_quantity_unit', 'Edit Quantity Unit') : language::translate('title_add_new_quantity_unit', 'Add New Quantity Unit');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_quantity_units', 'Quantity Units'), document::link(WS_DIR_ADMIN, ['doc' => 'quantity_units'], ['app']));
  breadcrumbs::add(!empty($quantity_unit->data['id']) ? language::translate('title_edit_quantity_unit', 'Edit Quantity Unit') : language::translate('title_add_new_quantity_unit', 'Add New Quantity Unit'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));

      if (empty($_POST['separate'])) $_POST['separate'] = 0;

      $fields = [
        'decimals',
        'separate',
        'priority',
        'name',
        'description',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $quantity_unit->data[$field] = $_POST[$field];
      }

      $quantity_unit->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'quantity_units'], true, ['quantity_unit_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($quantity_unit->data['id'])) throw new Exception(language::translate('error_must_provide_quantity_unit', 'You must provide a quantity unit'));

      $quantity_unit->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'quantity_units'], true, ['quantity_unit_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>

<div class="panel panel-app">
  <div class="panel-heading">
    <div class="panel-title">
      <?php echo $app_icon; ?> <?php echo !empty($quantity_unit->data['id']) ? language::translate('title_edit_quantity_unit', 'Edit Quantity Unit') : language::translate('title_add_new_quantity_unit', 'Add New Quantity Unit'); ?>
    </div>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('quantity_unit_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true); ?>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_description', 'Description'); ?></label>
        <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'description['. $language_code .']', true); ?>
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

      <div class="panel-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($quantity_unit->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>