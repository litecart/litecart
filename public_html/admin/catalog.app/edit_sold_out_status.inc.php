<?php

  if (!empty($_GET['sold_out_status_id'])) {
    $sold_out_status = new ent_sold_out_status($_GET['sold_out_status_id']);
  } else {
    $sold_out_status = new ent_sold_out_status();
  }

  if (empty($_POST)) {
    $_POST = $sold_out_status->data;
  }

  document::$snippets['title'][] = !empty($sold_out_status->data['id']) ? language::translate('title_edit_sold_out_status', 'Edit Sold Out Status') : language::translate('title_create_new_sold_out_status', 'Create New Sold Out Status');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_sold_out_statuses', 'Sold-Out Statuses'), document::link(WS_DIR_ADMIN, ['doc' => 'sold_out_statuses'], ['app']));
  breadcrumbs::add(!empty($sold_out_status->data['id']) ? language::translate('title_edit_sold_out_status', 'Edit Sold Out Status') : language::translate('title_create_new_sold_out_status', 'Create New Sold Out Status'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));

      if (empty($_POST['hidden'])) $_POST['hidden'] = 0;
      if (empty($_POST['orderable'])) $_POST['orderable'] = 0;

      $fields = [
        'name',
        'description',
        'hidden',
        'orderable',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $sold_out_status->data[$field] = $_POST[$field];
      }

      $sold_out_status->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'sold_out_statuses'], true, ['sold_out_status_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($sold_out_status->data['id'])) throw new Exception(language::translate('error_must_provide_sold_out_status', 'You must provide a sold out status'));

      $sold_out_status->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'sold_out_statuses'], true, ['sold_out_status_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($sold_out_status->data['id']) ? language::translate('title_edit_sold_out_status', 'Edit Sold Out Status') : language::translate('title_create_new_sold_out_status', 'Create New Sold Out Status'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('sold_out_status_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, ''); ?>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_description', 'Description'); ?></label>
        <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_textarea($language_code, 'description['. $language_code .']', true, 'style="height: 60px;"'); ?>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <div class="checkbox">
            <label><?php echo functions::form_draw_checkbox('hidden', '1', empty($_POST['hidden']) ? '' : '1'); ?> <?php echo language::translate('text_hide_from_listing', 'Hide from listing'); ?></label>
          </div>
          <div class="checkbox">
            <label><?php echo functions::form_draw_checkbox('orderable', '1', empty($_POST['orderable']) ? '' : '1'); ?> <?php echo language::translate('text_product_is_orderable', 'Product is orderable'); ?></label>
          </div>
        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($sold_out_status->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
