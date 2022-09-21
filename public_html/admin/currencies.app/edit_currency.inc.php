<?php

  if (!empty($_GET['currency_code'])) {
    $currency = new ent_currency($_GET['currency_code']);
  } else {
    $currency = new ent_currency();
  }

  if (empty($_POST)) {
    $_POST = $currency->data;
  }

  document::$snippets['title'][] = !empty($currency->data['id']) ? language::translate('title_edit_currency', 'Edit Currency') : language::translate('title_add_new_currency', 'Add New Currency');

  breadcrumbs::add(language::translate('title_currencies', 'Currencies'), document::link(WS_DIR_ADMIN, ['doc' => 'currencies'], ['app']));
  breadcrumbs::add(!empty($currency->data['id']) ? language::translate('title_edit_currency', 'Edit Currency') : language::translate('title_add_new_currency', 'Add New Currency'));

  if (isset($_POST['save'])) {

    try {
      $_POST['code'] = strtoupper($_POST['code']);

      if (empty($_POST['code'])) throw new Exception(language::translate('error_must_enter_code', 'You must enter a code'));
      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
      if (empty($_POST['value'])) throw new Exception(language::translate('error_must_enter_value', 'You must enter a value'));

      if ((!empty($_POST['set_store']) || $_POST['code'] == settings::get('store_currency_code')) && (float)$_POST['value'] != 1) {
        throw new Exception(language::translate('error_store_currency_must_have_value_1', 'The store currency must always have the currency value 1.0.'));
      }

      if (empty($_POST['set_default']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('default_currency_code') && $currency->data['code'] != $_POST['code']) {
        throw new Exception(language::translate('error_cannot_rename_default_currency', 'You must change the default currency before renaming it.'));
      }

      if (empty($_POST['set_store']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('store_currency_code') && $currency->data['code'] != $_POST['code']) {
        throw new Exception(language::translate('error_cannot_rename_store_currency', 'You must change the store currency before renaming it.'));
      }

      if (!empty($_POST['set_default']) && empty($_POST['status']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('default_currency_code')) {
        throw new Exception(language::translate('error_cannot_set_disabled_default_currency', 'You cannot set a disabled currency as default currency.'));
      }

      if (!empty($_POST['set_store']) && empty($_POST['status']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('store_currency_code')) {
        throw new Exception(language::translate('error_cannot_set_disabled_store_currency', 'You cannot set a disabled currency as store currency.'));
      }

      $fields = [
        'status',
        'code',
        'number',
        'name',
        'value',
        'prefix',
        'suffix',
        'decimals',
        'priority',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $currency->data[$field] = $_POST[$field];
      }

      $currency->save();

      if (!empty($_POST['set_default'])) {
        database::query("update ". DB_TABLE_PREFIX ."settings set `value` = '". database::input($_POST['code']) ."' where `key` = 'default_currency_code' limit 1;");
      }

      if (!empty($_POST['set_store'])) {
        database::query("update ". DB_TABLE_PREFIX ."settings set `value` = '". database::input($_POST['code']) ."' where `key` = 'store_currency_code' limit 1;");
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'currencies'], true, ['action', 'currency_code']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($currency->data['id'])) throw new Exception(language::translate('error_must_provide_currency', 'You must provide a currency'));

      $currency->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'currencies'], true, ['action', 'currency_code']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $store_currency = reference::currency(settings::get('store_currency_code'));
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <div class="panel-title">
      <?php echo $app_icon; ?> <?php echo !empty($currency->data['id']) ? language::translate('title_edit_currency', 'Edit Currency') : language::translate('title_add_new_currency', 'Add New Currency'); ?>
    </div>
  </div>

  <div class="panel-nav">
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('currency_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_status', 'Status'); ?></label>
          <div class="btn-group btn-block btn-group-inline" data-toggle="buttons">
            <label class="btn btn-default<?php echo ((int)$_POST['status'] == 1) ? ' active' : ''; ?>"><?php echo functions::form_draw_radio_button('status', '1', true); ?> <?php echo language::translate('title_enabled', 'Enabled'); ?></label>
            <label class="btn btn-default<?php echo ((int)$_POST['status'] == -1) ? ' active' : ''; ?>"><?php echo functions::form_draw_radio_button('status', '-1', true); ?><?php echo language::translate('title_hidden', 'Hidden'); ?></label>
            <label class="btn btn-default<?php echo ((int)$_POST['status'] == 0) ? ' active' : ''; ?>"><?php echo functions::form_draw_radio_button('status', '0', true); ?><?php echo language::translate('title_disabled', 'Disabled'); ?></label>
          </div>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php echo functions::form_draw_text_field('name', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_code', 'Code'); ?> (ISO 4217) <a href="http://en.wikipedia.org/wiki/ISO_4217" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
          <?php echo functions::form_draw_text_field('code', true, 'required pattern="[A-Z]{3}"'); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_number', 'Number'); ?> (ISO 4217) <a href="http://en.wikipedia.org/wiki/ISO_4217" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
          <?php echo functions::form_draw_text_field('number', true, 'required pattern="[0-9]{3}"'); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_value', 'Value'); ?></label>
          <div class="input-group">
            <?php echo functions::form_draw_decimal_field('value', true, 4); ?>
            <span class="input-group-text"><?php echo $store_currency->code; ?></span>
          </div>
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

      <div class="panel-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($currency->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
