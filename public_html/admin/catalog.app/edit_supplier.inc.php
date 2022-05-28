<?php

  if (!empty($_GET['supplier_id'])) {
    $supplier = new ent_supplier($_GET['supplier_id']);
  } else {
    $supplier = new ent_supplier();
  }

  if (empty($_POST)) {
    $_POST = $supplier->data;
  }

  document::$snippets['title'][] = !empty($supplier->data['id']) ? language::translate('title_edit_supplier', 'Edit Supplier') : language::translate('title_add_new_supplier', 'Add New Supplier');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_suppliers', 'Suppliers'), document::link(WS_DIR_ADMIN, ['doc' => 'suppliers'], ['app']));
  breadcrumbs::add(!empty($supplier->data['id']) ? language::translate('title_edit_supplier', 'Edit Supplier') : language::translate('title_add_new_supplier', 'Add New Supplier'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['name'])) throw new Exception(language::translate('error_name_missing', 'You must enter a name.'));

      if (!isset($_POST['status'])) $_POST['status'] = '0';

      $fields = [
        'code',
        'name',
        'description',
        'email',
        'phone',
        'link',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $supplier->data[$field] = $_POST[$field];
      }

      $supplier->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'suppliers'], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($supplier->data['id'])) throw new Exception(language::translate('error_must_provide_supplier', 'You must provide a supplier'));

      $supplier->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'suppliers'], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo !empty($supplier->data['id']) ? language::translate('title_edit_supplier', 'Edit Supplier') : language::translate('title_add_new_supplier', 'Add New Supplier'); ?>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('supplier_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_code', 'Code'); ?></label>
          <?php echo functions::form_draw_text_field('code', true); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php echo functions::form_draw_text_field('name', true); ?>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_description', 'Description'); ?></label>
        <?php echo functions::form_draw_textarea('description', true); ?>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
          <?php echo functions::form_draw_email_field('email', true, 'email', ''); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_phone', 'Phone'); ?></label>
          <?php echo functions::form_draw_text_field('phone', true); ?>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_link', 'Link'); ?></label>
        <?php echo functions::form_draw_text_field('link', true); ?>
      </div>

      <div class="panel-action btn-group">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($supplier->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
