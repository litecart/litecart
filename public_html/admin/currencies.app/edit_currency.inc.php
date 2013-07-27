<?php
  
  if (isset($_GET['currency_code'])) {
    $currency = new ctrl_currency($_GET['currency_code']);
    if (!$_POST) {
      foreach ($currency->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
  } else {
    $currency = new ctrl_currency();
  }

  if (!empty($_POST['save'])) {
    
    if (empty($_POST['code'])) $system->notices->add('errors', $system->language->translate('error_must_enter_code', 'You must enter a code'));
    if (empty($_POST['name'])) $system->notices->add('errors', $system->language->translate('error_must_enter_name', 'You must enter a name'));
    if (empty($_POST['value'])) $system->notices->add('errors', $system->language->translate('error_must_enter_value', 'You must enter a value'));
    
    if (empty($_POST['status']) && isset($currency->data['code']) && $language->data['code'] == $system->settings->get('default_currency_code')) {
      $system->notices->add('errors', $system->language->translate('error_cannot_disable_default_currency', 'You must change the default currency before disabling it.'));
    }
    
    if (empty($_POST['status']) && isset($currency->data['code']) && $currency->data['code'] == $system->settings->get('store_currency_code')) {
      $system->notices->add('errors', $system->language->translate('error_cannot_disable_store_currency', 'You must change the store currency before disabling it.'));
    }
    
    if (empty($_POST['set_default']) && isset($currency->data['code']) && $currency->data['code'] == $system->settings->get('default_currency_code') && $currency->data['code'] != $_POST['code']) {
      $system->notices->add('errors', $system->language->translate('error_cannot_rename_default_currency', 'You must change the default currency before renaming it.'));
    }
    
    if (empty($_POST['set_store']) && isset($currency->data['code']) && $currency->data['code'] == $system->settings->get('store_currency_code') && $currency->data['code'] != $_POST['code']) {
      $system->notices->add('errors', $system->language->translate('error_cannot_rename_store_currency', 'You must change the store currency before renaming it.'));
    }
    
    if (!empty($_POST['set_default']) && empty($_POST['status']) && isset($currency->data['code']) && $currency->data['code'] == $system->settings->get('default_currency_code')) {
      $system->notices->add('errors', $system->language->translate('error_cannot_set_disabled_default_currency', 'You cannot set a disabled currency as default currency.'));
    }
    
    if (!empty($_POST['set_store']) && empty($_POST['status']) && isset($currency->data['code']) && $currency->data['code'] == $system->settings->get('store_currency_code')) {
      $system->notices->add('errors', $system->language->translate('error_cannot_set_disabled_store_currency', 'You cannot set a disabled currency as store currency.'));
    }
    
    if (empty($system->notices->data['errors'])) {
      
      $fields = array(
        'status',
        'code',
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
        $system->database->query("update ". DB_TABLE_SETTINGS ." set `value` = '". $system->database->input($_POST['code']) ."' where `key` = 'default_currency_code' limit 1;");
      }
      
      if (!empty($_POST['set_store'])) {
        $system->database->query("update ". DB_TABLE_SETTINGS ." set `value` = '". $system->database->input($_POST['code']) ."' where `key` = 'store_currency_code' limit 1;");
      }
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes were successfully saved.'));
      header('Location: '. $system->document->link('', array('doc' => 'currencies'), true, array('action', 'currency_code')));
      exit;
    }
  }
  
  if (!empty($_POST['delete'])) {
  
    if ($currency->data['code'] == 'en') {
      $system->notices->add('errors', $system->language->translate('error_cannot_delete_framework_currency', 'You cannot delete the PHP framework currency. But you can disable it.'));
    }

    if ($currency->data['code'] == $system->settings->get('default_currency_code')) {
      $system->notices->add('errors', $system->language->translate('error_cannot_delete_default_currency', 'You must change the default currency before it can be deleted.'));
    }
    
    if ($currency->data['code'] == $system->settings->get('store_currency_code')) {
      $system->notices->add('errors', $system->language->translate('error_cannot_delete_store_currency', 'You must change the store currency before it can be deleted.'));
    }
    
    $currency->delete();
    
    $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes were successfully saved.'));
    header('Location: '. $system->document->link('', array('doc' => 'currencies'), true, array('action', 'currency_code')));
    exit;
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo (isset($currency->data['id'])) ? $system->language->translate('title_edit_currency', 'Edit Currency') : $system->language->translate('title_add_new_currency', 'Add New Currency'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('', 'post'); ?>

  <table>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_status', 'Status'); ?></strong><br />
        <?php echo $system->functions->form_draw_radio_button('status', '1', isset($_POST['status']) ? $_POST['status'] : '1'); ?> <?php echo $system->language->translate('title_enabled', 'Enabled'); ?>
        <?php echo $system->functions->form_draw_radio_button('status', '0', isset($_POST['status']) ? $_POST['status'] : '1'); ?> <?php echo $system->language->translate('title_disabled', 'Disabled'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_code', 'Code'); ?></strong><br />
        <?php echo $system->functions->form_draw_text_field('code', true, 'data-size="tiny"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
        <?php echo $system->functions->form_draw_text_field('name', true); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_value', 'Value'); ?></strong><br />
        <?php echo $system->functions->form_draw_decimal_field('value', true); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_prefix', 'Prefix'); ?></strong><br />
        <?php echo $system->functions->form_draw_text_field('prefix', true, 'data-size="tiny"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_suffix', 'Suffix'); ?></strong><br />
        <?php echo $system->functions->form_draw_text_field('suffix', true, 'data-size="tiny"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_decimals', 'Decimals'); ?></strong><br />
        <?php echo $system->functions->form_draw_number_field('decimals', true); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_priority', 'Priority'); ?></strong><br />
        <?php echo $system->functions->form_draw_number_field('priority', true); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap">
        <?php echo $system->functions->form_draw_checkbox('set_default', '1', (isset($currency->data['code']) && $currency->data['code'] && $currency->data['code'] == $system->settings->get('default_currency_code')) ? '1' : true); ?> <?php echo $system->language->translate('description_set_as_default_currency', 'Set as default currency'); ?><br />
        <?php echo $system->functions->form_draw_checkbox('set_store', '1', (isset($currency->data['code']) && $currency->data['code'] && $currency->data['code'] == $system->settings->get('store_currency_code')) ? '1' : true); ?> <?php echo $system->language->translate('description_set_as_store_currency', 'Set as store currency'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($currency->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></td>
    </tr>
  </table>
  
<?php echo $system->functions->form_draw_form_end(); ?>