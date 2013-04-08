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
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes were successfully saved.'));
      header('Location: '. $system->document->link('', array('doc' => 'currencies.php'), true, array('action', 'currency_code')));
      exit;
    }
  }
  
  if (!empty($_POST['delete'])) {
    
    $currency->delete();
    
    $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes were successfully saved.'));
    header('Location: '. $system->document->link('', array('doc' => 'currencies.php'), true, array('action', 'currency_code')));
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
      <?php echo $system->functions->form_draw_input_field('code', isset($_POST['code']) ? $_POST['code'] : '', 'text', 'style="width: 50px;"'); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
      <?php echo $system->functions->form_draw_input_field('name', isset($_POST['name']) ? $_POST['name'] : '', 'text', 'style="width: 175px;"'); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_value', 'Value'); ?></strong><br />
      <?php echo $system->functions->form_draw_input_field('value', isset($_POST['value']) ? $_POST['value'] : '', 'text', 'style="width: 75px;"'); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_prefix', 'Prefix'); ?></strong><br />
      <?php echo $system->functions->form_draw_input_field('prefix', isset($_POST['prefix']) ? $_POST['prefix'] : '', 'text', 'style="width: 75px;"'); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_suffix', 'Suffix'); ?></strong><br />
      <?php echo $system->functions->form_draw_input_field('suffix', isset($_POST['suffix']) ? $_POST['suffix'] : '', 'text', 'style="width: 75px;"'); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_decimals', 'decimals'); ?></strong><br />
      <?php echo $system->functions->form_draw_input_field('decimals', isset($_POST['decimals']) ? $_POST['decimals'] : '', 'text', 'style="width: 75px;"'); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_priority', 'Priority'); ?></strong><br />
      <?php echo $system->functions->form_draw_input_field('priority', isset($_POST['priority']) ? $_POST['priority'] : '', 'text', 'style="width: 75px;"'); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($currency->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></td>
  </tr>
</table>
  
<?php echo $system->functions->form_draw_form_end(); ?>