<?php

  if (!empty($_GET['currency_code'])) {
    $currency = new ctrl_currency($_GET['currency_code']);
  } else {
    $currency = new ctrl_currency();
  }

  if (empty($_POST)) {
    foreach ($currency->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($currency->data['id']) ? language::translate('title_edit_currency', 'Edit Currency') : language::translate('title_add_new_currency', 'Add New Currency'));

  if (!empty($_POST['save'])) {

    if (empty($_POST['code'])) notices::add('errors', language::translate('error_must_enter_code', 'You must enter a code'));

    if (!empty($_POST['code']) && empty($currency->data['id'])) {
        $currencys_query = database::query(
          "select id from ". DB_TABLE_CURRENCIES ."
          where code = '". database::input($_POST['code']) ."'
          limit 1;"
        );

        if (database::num_rows($currencys_query)) {
          notices::add('errors', currency::translate('error_currency_already_exists', 'The currency already exists in the database'));
        }
    }

    if (!empty($_POST['code']) && !empty($currency->data['id']) && $currency->data['code'] != $_POST['code']) {
      $currencys_query = database::query(
        "select id from ". DB_TABLE_CURRENCIES ."
        where code = '". database::input($_POST['code']) ."'
        limit 1;"
      );

      if (database::num_rows($currencys_query)) {
        notices::add('errors', currency::translate('error_currency_already_exists', 'The currency already exists in the database'));
      }
    }

    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));
    if (empty($_POST['value'])) notices::add('errors', language::translate('error_must_enter_value', 'You must enter a value'));

    if ((!empty($_POST['set_store']) || $_POST['code'] == settings::get('store_currency_code')) && (float)$_POST['value'] != 1) {
      notices::add('errors', language::translate('error_store_currency_must_have_value_1', 'The store currency must always have the currency value 1.0.'));
    }

    if (empty($_POST['status']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('default_currency_code')) {
      notices::add('errors', language::translate('error_cannot_disable_default_currency', 'You must change the default currency before disabling it.'));
    }

    if (empty($_POST['status']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('store_currency_code')) {
      notices::add('errors', language::translate('error_cannot_disable_store_currency', 'You must change the store currency before disabling it.'));
    }

    if (empty($_POST['set_default']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('default_currency_code') && $currency->data['code'] != $_POST['code']) {
      notices::add('errors', language::translate('error_cannot_rename_default_currency', 'You must change the default currency before renaming it.'));
    }

    if (empty($_POST['set_store']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('store_currency_code') && $currency->data['code'] != $_POST['code']) {
      notices::add('errors', language::translate('error_cannot_rename_store_currency', 'You must change the store currency before renaming it.'));
    }

    if (!empty($_POST['set_default']) && empty($_POST['status']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('default_currency_code')) {
      notices::add('errors', language::translate('error_cannot_set_disabled_default_currency', 'You cannot set a disabled currency as default currency.'));
    }

    if (!empty($_POST['set_store']) && empty($_POST['status']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('store_currency_code')) {
      notices::add('errors', language::translate('error_cannot_set_disabled_store_currency', 'You cannot set a disabled currency as store currency.'));
    }

    if (empty(notices::$data['errors'])) {

     $_POST['code'] = strtoupper($_POST['code']);

      $fields = array(
        'status',
        'code',
        'number',
        'name',
        'value',
        'prefix',
        'suffix',
        'decimals',
        'priority',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $currency->data[$field] = $_POST[$field];
      }

      $currency->save();

      if (!empty($_POST['set_default'])) {
        database::query("update ". DB_TABLE_SETTINGS ." set `value` = '". database::input($_POST['code']) ."' where `key` = 'default_currency_code' limit 1;");
      }

      if (!empty($_POST['set_store'])) {
        database::query("update ". DB_TABLE_SETTINGS ." set `value` = '". database::input($_POST['code']) ."' where `key` = 'store_currency_code' limit 1;");
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes were successfully saved.'));
      header('Location: '. document::link('', array('doc' => 'currencies'), true, array('action', 'currency_code')));
      exit;
    }
  }

  if (!empty($_POST['delete'])) {

    if ($currency->data['code'] == settings::get('default_currency_code')) {
      notices::add('errors', language::translate('error_cannot_delete_default_currency', 'You must change the default currency before it can be deleted.'));
    }

    if ($currency->data['code'] == settings::get('store_currency_code')) {
      notices::add('errors', language::translate('error_cannot_delete_store_currency', 'You must change the store currency before it can be deleted.'));
    }

    $currency->delete();

    notices::add('success', language::translate('success_changes_saved', 'Changes were successfully saved.'));
    header('Location: '. document::link('', array('doc' => 'currencies'), true, array('action', 'currency_code')));
    exit;
  }

?>
<h1><?php echo $app_icon; ?> <?php echo !empty($currency->data['id']) ? language::translate('title_edit_currency', 'Edit Currency') : language::translate('title_add_new_currency', 'Add New Currency'); ?></h1>

<?php echo functions::form_draw_form_begin('currency_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_status', 'Status'); ?></label>
      <?php echo functions::form_draw_toggle('status', isset($_POST['status']) ? $_POST['status'] : '1', 'e/d'); ?>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_name', 'Name'); ?></label>
      <?php echo functions::form_draw_text_field('name', true); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_code', 'Code'); ?> (ISO 4217) <a href="http://en.wikipedia.org/wiki/ISO_4217" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
      <?php echo functions::form_draw_text_field('code', true, 'required="required" pattern="[A-Z]{3}"'); ?>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_number', 'Number'); ?> (ISO 4217) <a href="http://en.wikipedia.org/wiki/ISO_4217" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
      <?php echo functions::form_draw_text_field('number', true, 'pattern="[0-9]{3}"'); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_value', 'Value'); ?></label>
      <?php echo functions::form_draw_decimal_field('value', true, 4); ?>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_decimals', 'Decimals'); ?></label>
      <?php echo functions::form_draw_number_field('decimals', true); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_prefix', 'Prefix'); ?></label>
      <?php echo functions::form_draw_text_field('prefix', true); ?>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_suffix', 'Suffix'); ?></label>
      <?php echo functions::form_draw_text_field('suffix', true); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
      <?php echo functions::form_draw_number_field('priority', true); ?>
    </div>

    <div class="form-group col-md-6">
      <div class="checkbox">
        <label><?php echo functions::form_draw_checkbox('set_default', '1', (isset($currency->data['code']) && $currency->data['code'] && $currency->data['code'] == settings::get('default_currency_code')) ? '1' : true); ?> <?php echo language::translate('description_set_as_default_currency', 'Set as default currency'); ?></label>
      </div>
      <div class="checkbox">
        <label><?php echo functions::form_draw_checkbox('set_store', '1', (isset($currency->data['code']) && $currency->data['code'] && $currency->data['code'] == settings::get('store_currency_code')) ? '1' : true); ?> <?php echo language::translate('description_set_as_store_currency', 'Set as store currency'); ?></label>
      </div>
    </div>
  </div>

  <p class="btn-group">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
    <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
    <?php echo (isset($currency->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>