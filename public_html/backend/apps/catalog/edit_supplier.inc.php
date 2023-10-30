<?php

  if (!empty($_GET['supplier_id'])) {
    $supplier = new ent_supplier($_GET['supplier_id']);
  } else {
    $supplier = new ent_supplier();
  }

  if (!$_POST) {
    $_POST = $supplier->data;
  }

  document::$snippets['title'][] = !empty($supplier->data['id']) ? language::translate('title_edit_supplier', 'Edit Supplier') : language::translate('title_create_new_supplier', 'Create New Supplier');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_suppliers', 'Suppliers'), document::ilink(__APP__.'/suppliers'));
  breadcrumbs::add(!empty($supplier->data['id']) ? language::translate('title_edit_supplier', 'Edit Supplier') : language::translate('title_create_new_supplier', 'Create New Supplier'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['name'])) {
        throw new Exception(language::translate('error_name_missing', 'You must enter a name.'));
      }

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
        if (isset($_POST[$field])) {
          $supplier->data[$field] = $_POST[$field];
        }
      }

      $supplier->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/suppliers'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {

      if (empty($supplier->data['id'])) {
        throw new Exception(language::translate('error_must_provide_supplier', 'You must provide a supplier'));
      }

      $supplier->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/suppliers'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($supplier->data['id']) ? language::translate('title_edit_supplier', 'Edit Supplier') : language::translate('title_create_new_supplier', 'Create New Supplier'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('supplier_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_code', 'Code'); ?></label>
          <?php echo functions::form_text_field('code', true); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php echo functions::form_text_field('name', true); ?>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_description', 'Description'); ?></label>
        <?php echo functions::form_textarea('description', true); ?>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
          <?php echo functions::form_email_field('email', true, 'email', ''); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_phone_number', 'Phone Number'); ?></label>
          <?php echo functions::form_text_field('phone', true); ?>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_link', 'Link'); ?></label>
        <?php echo functions::form_text_field('link', true); ?>
      </div>

      <div class="card-action">
        <?php echo functions::form_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo !empty($supplier->data['id']) ? functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(&quot;'. language::translate('text_are_you_sure', 'Are you sure?') .'&quot;)) return false;"', 'delete') : false; ?>
        <?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>
