<?php

  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'language.inc.php');
  
  if (isset($_GET['language_code'])) {
    $language = new ctrl_language($_GET['language_code']);
    if (!$_POST) {
      foreach ($language->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
  } else {
    $language = new ctrl_language();
  }

  if (!empty($_POST['save'])) {
    
    $_POST['raw_datetime'] = $_POST['raw_date'] .' '. $_POST['raw_time'];
    $_POST['format_datetime'] = $_POST['format_date'] .' '. $_POST['format_time'];
    
    $fields = array(
      'status',
      'code',
      'name',
      'charset',
      'locale',
      'raw_date',
      'raw_time',
      'raw_datetime',
      'format_date',
      'format_time',
      'format_datetime',
      'decimal_point',
      'thousands_sep',
      'currency_code',
      'priority',
    );
    
    foreach ($fields as $field) {
      if (isset($_POST[$field])) $language->data[$field] = $_POST[$field];
    }
    
    $language->save();
    
    $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes were successfully saved.'));
    header('Location: '. $system->document->link('', array('doc' => 'languages.php'), true, array('action', 'language_code')));
    exit;
  }
  
  if (!empty($_POST['delete'])) {
    
    $language->delete();
    
    $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes were successfully saved.'));
    header('Location: '. $system->document->link('', array('doc' => 'languages.php'), true, array('action', 'language_code')));
    exit;
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle;" style="margin-right: 10px;" /><?php echo (isset($language->data['id'])) ? $system->language->translate('title_edit_language', 'Edit Language') : $system->language->translate('title_add_new_language', 'Add New Language'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('', 'post'); ?>
  <table>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_status', 'Status'); ?></strong><br />
        <?php echo $system->functions->form_draw_radio_button('status', '1', isset($_POST['status']) ? $_POST['status'] : '1'); ?> <?php echo $system->language->translate('title_enabled', 'Enabled'); ?>
        <?php echo $system->functions->form_draw_radio_button('status', '0', isset($_POST['status']) ? $_POST['status'] : '1'); ?> <?php echo $system->language->translate('title_disabled', 'Disabled'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_code', 'Code'); ?> (ISO 639-1)</strong><br />
        <?php echo $system->functions->form_draw_input_field('code', isset($_POST['code']) ? $_POST['code'] : '', 'text', 'style="width: 25px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('name', isset($_POST['name']) ? $_POST['name'] : '', 'text', 'style="width: 175px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_charset', 'Charset'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('charset', isset($_POST['charset']) ? $_POST['charset'] : '', 'text', 'style="width: 75px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_system_locale', 'System Locale'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('locale', isset($_POST['locale']) ? $_POST['locale'] : '', 'text', 'style="width: 75px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_date_format', 'Date Format'); ?></strong> (<a href="http://php.net/manual/en/function.strftime.php" target="_blank">?</a>)<br />
        <?php echo $system->functions->form_draw_input_field('format_date', isset($_POST['format_date']) ? $_POST['format_date'] : '', 'text', 'style="width: 75px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_time_format', 'Time Format'); ?></strong> (<a href="http://php.net/manual/en/function.strftime.php" target="_blank">?</a>)<br />
        <?php echo $system->functions->form_draw_input_field('format_time', isset($_POST['format_time']) ? $_POST['format_time'] : '', 'text', 'style="width: 75px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_raw_date_format', 'Raw Date Format'); ?></strong> (<a href="http://php.net/manual/en/function.date.php" target="_blank">?</a>)<br />
        <?php echo $system->functions->form_draw_input_field('raw_date', isset($_POST['raw_date']) ? $_POST['raw_date'] : '', 'text', 'style="width: 75px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_raw_time_format', 'Raw Time Format'); ?></strong> (<a href="http://php.net/manual/en/function.date.php" target="_blank">?</a>)<br />
        <?php echo $system->functions->form_draw_input_field('raw_time', isset($_POST['raw_time']) ? $_POST['raw_time'] : '', 'text', 'style="width: 75px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_decimal_point', 'Decimal Point'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('decimal_point', isset($_POST['decimal_point']) ? $_POST['decimal_point'] : '', 'text', 'style="width: 75px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_thousands_sep', 'Thousands Separator'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('thousands_sep', isset($_POST['thousands_sep']) ? $_POST['thousands_sep'] : '', 'text', 'style="width: 75px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_currency_code', 'Currency Code'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('currency_code', isset($_POST['currency_code']) ? $_POST['currency_code'] : '', 'text', 'style="width: 75px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_priority', 'Priority'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('priority', isset($_POST['priority']) ? $_POST['priority'] : '', 'text', 'style="width: 75px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"'); ?> <?php echo (isset($language->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"') : false; ?></td>
    </tr>
  </table>
  
<?php echo $system->functions->form_draw_form_end(); ?>