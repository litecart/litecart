<?php

  if (!empty($_GET['delivery_status_id'])) {
    $delivery_status = new ent_delivery_status($_GET['delivery_status_id']);
  } else {
    $delivery_status = new ent_delivery_status();
  }

  if (empty($_POST)) {
    foreach ($delivery_status->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  document::$snippets['title'][] = !empty($delivery_status->data['id']) ? language::translate('title_edit_delivery_status', 'Edit Delivery Status') : language::translate('title_create_new_delivery_status', 'Create New Delivery Status');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_delivery_statuses', 'Delivery Statuses'), document::link(WS_DIR_ADMIN, ['doc' => 'delivery_statuses'], ['app']));
  breadcrumbs::add(!empty($delivery_status->data['id']) ? language::translate('title_edit_delivery_status', 'Edit Delivery Status') : language::translate('title_create_new_delivery_status', 'Create New Delivery Status'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));

      $fields = [
        'name',
        'description',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $delivery_status->data[$field] = $_POST[$field];
      }

      $delivery_status->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'delivery_statuses'], true, ['delivery_status_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($delivery_status->data['id'])) throw new Exception(language::translate('error_must_provide_delivery_status', 'You must provide a delivery status'));

      $delivery_status->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'delivery_statuses'], true, ['delivery_status_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>

<div class="panel panel-app">
  <div class="panel-heading">
    <div class="panel-title">
      <?php echo $app_icon; ?> <?php echo !empty($delivery_status->data['id']) ? language::translate('title_edit_delivery_status', 'Edit Delivery Status') : language::translate('title_create_new_delivery_status', 'Create New Delivery Status'); ?>
    </div>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('delivery_status_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php foreach (array_keys(language::$languages) as $language_code)  echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, ''); ?>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_description', 'Description'); ?></label>
        <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_textarea($language_code, 'description['. $language_code .']', true, 'style="height: 50px;"'); ?>
      </div>

      <div class="panel-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($delivery_status->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
