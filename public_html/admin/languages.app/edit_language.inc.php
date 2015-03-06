<?php
  
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
    
    if (empty($_POST['code'])) notices::add('errors', language::translate('error_must_enter_code', 'You must enter a code'));
    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));
    
    if (empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == settings::get('default_language_code')) {
      notices::add('errors', language::translate('error_cannot_disable_default_language', 'You must change the default language before disabling it.'));
    }
    
    if (empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == settings::get('store_language_code')) {
      notices::add('errors', language::translate('error_cannot_disable_store_language', 'You must change the store language before disabling it.'));
    }
    
    if (empty($_POST['set_default']) && isset($language->data['code']) && $language->data['code'] == settings::get('default_language_code') && $language->data['code'] != $_POST['code']) {
      notices::add('errors', language::translate('error_cannot_rename_default_language', 'You must change the default language before renaming it.'));
    }
    
    if (empty($_POST['set_store']) && isset($language->data['code']) && $language->data['code'] == settings::get('store_language_code') && $language->data['code'] != $_POST['code']) {
      notices::add('errors', language::translate('error_cannot_rename_store_language', 'You must change the store language before renaming it.'));
    }
    
    if (!empty($_POST['set_default']) && empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == settings::get('default_language_code')) {
      notices::add('errors', language::translate('error_cannot_set_disabled_default_language', 'You cannot set a disabled language as default language.'));
    }
    
    if (!empty($_POST['set_store']) && empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == settings::get('store_language_code')) {
      notices::add('errors', language::translate('error_cannot_set_disabled_store_language', 'You cannot set a disabled language as store language.'));
    }
    
    if (empty(notices::$data['errors'])) {
      
      $_POST['code'] = strtolower($_POST['code']);
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
      
      if (!empty($_POST['set_default'])) {
        database::query("update ". DB_TABLE_SETTINGS ." set `value` = '". database::input($_POST['code']) ."' where `key` = 'default_language_code' limit 1;");
      }
      
      if (!empty($_POST['set_store'])) {
        database::query("update ". DB_TABLE_SETTINGS ." set `value` = '". database::input($_POST['code']) ."' where `key` = 'store_language_code' limit 1;");
      }
      
      notices::add('success', language::translate('success_changes_saved', 'Changes were successfully saved.'));
      header('Location: '. document::link('', array('doc' => 'languages'), true, array('action', 'language_code')));
      exit;
    }
  }
  
  if (!empty($_POST['delete'])) {
    
    if ($language->data['code'] == 'en') {
      notices::add('errors', language::translate('error_cannot_delete_framework_language', 'You cannot delete the PHP framework language. But you can disable it.'));
    }

    if ($language->data['code'] == settings::get('default_language_code')) {
      notices::add('errors', language::translate('error_cannot_delete_default_language', 'You must change the default language before it can be deleted.'));
    }
    
    if ($language->data['code'] == settings::get('store_language_code')) {
      notices::add('errors', language::translate('error_cannot_delete_store_language', 'You must change the store language before it can be deleted.'));
    }
    
    if (empty(notices::$data['errors'])) {
      
      $language->delete();
    
      notices::add('success', language::translate('success_changes_saved', 'Changes were successfully saved.'));
      header('Location: '. document::link('', array('doc' => 'languages'), true, array('action', 'language_code')));
      exit;
    }
  }

?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?><?php echo (isset($language->data['id'])) ? language::translate('title_edit_language', 'Edit Language') : language::translate('title_add_new_language', 'Add New Language'); ?></h1>

<?php echo functions::form_draw_form_begin('', 'post'); ?>

  <table>
    <tr>
      <td><strong><?php echo language::translate('title_status', 'Status'); ?></strong><br />
        <label><?php echo functions::form_draw_radio_button('status', '1', isset($_POST['status']) ? $_POST['status'] : '1'); ?> <?php echo language::translate('title_enabled', 'Enabled'); ?></label>
        <label><?php echo functions::form_draw_radio_button('status', '0', isset($_POST['status']) ? $_POST['status'] : '1'); ?> <?php echo language::translate('title_disabled', 'Disabled'); ?></label>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_code', 'Code'); ?> (ISO 639-1)</strong><br />
        <?php echo functions::form_draw_text_field('code', true, 'data-size="tiny" required="required" pattern="[a-z]{2}"'); ?> <a href="http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank"><?php echo language::translate('title_reference', 'Reference'); ?></a>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_name', 'Name'); ?></strong><br />
        <?php echo functions::form_draw_text_field('name', true); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_charset', 'Charset'); ?></strong><br />
        <?php echo functions::form_draw_text_field('charset', !isset($_POST['charset']) ? 'UTF-8' : true, 'placeholder="UTF-8" data-size="small"'); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_system_locale', 'System Locale'); ?></strong><br />
        <?php echo functions::form_draw_text_field('locale', true, 'placeholder="xx_XX.utf8" data-size="small"'); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_date_format', 'Date Format'); ?></strong> (<a href="http://php.net/manual/en/function.strftime.php" target="_blank">?</a>)<br />
<?php
  $options = array(
    array(strftime('%e %b %Y'), '%e %b %Y'),
    array(strftime('%b %e %Y'), '%b %e %Y'),
  );
  echo functions::form_draw_select_field('format_date', $options, true, false, 'data-size="auto"');
?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_time_format', 'Time Format'); ?></strong> (<a href="http://php.net/manual/en/function.strftime.php" target="_blank">?</a>)<br />
<?php
  $options = array(
    array('12-hour format', 'null', 'style="font-weight: bold;" disabled="disabled"'),
    array(strftime('%I:%M %p'), '%I:%M %P'),
    array('24-hour format', 'null', 'style="font-weight: bold;" disabled="disabled"'),
    array(strftime('%H:%M'), '%H:%M'),
  );
  echo functions::form_draw_select_field('format_time', $options, true, false, 'data-size="auto"');
?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_raw_date_format', 'Raw Date Format'); ?></strong> (<a href="http://php.net/manual/en/function.date.php" target="_blank">?</a>)<br />
<?php
  $options = array(
    array('Big-endian (YMD)', 'null', 'style="font-weight: bold;" disabled="disabled"'),
    array(date('Y-m-d'), 'Y-m-d'),
    array(date('Y.m.d'), 'Y.m.d'),
    array(date('Y/m/d'), 'Y/m/d'),
    array('Little-endian (DMY)', 'null', 'style="font-weight: bold;" disabled="disabled"'),
    array(date('d-m-Y'), 'd-m-Y'),
    array(date('d.m.Y'), 'd.m.Y'),
    array(date('d/m/Y'), 'd/m/Y'),
    array('Middle-endian (MDY)', 'null', 'style="font-weight: bold;" disabled="disabled"'),
    array(date('m/d/y'), 'm/d/y'),
  );
  echo functions::form_draw_select_field('raw_date', $options, true, false, 'data-size="auto"');
?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_raw_time_format', 'Raw Time Format'); ?></strong> (<a href="http://php.net/manual/en/function.date.php" target="_blank">?</a>)<br />
<?php
  $options = array(
    array('12-hour format', 'null', 'style="font-weight: bold;" disabled="disabled"'),
    array(date('h:i A'), 'h:i A'),
    array('24-hour format', 'null', 'style="font-weight: bold;" disabled="disabled"'),
    array(date('H:i'), 'H:i'),
  );
  echo functions::form_draw_select_field('raw_time', $options, true, false, 'data-size="auto"');
?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_decimal_point', 'Decimal Point'); ?></strong><br />
<?php
  $options = array(
    array(language::translate('char_dot', 'Dot'), '.'),
    array(language::translate('char_comma', 'Comma'), ','),
  );
  echo functions::form_draw_select_field('decimal_point', $options, true, false, 'data-size="auto"');
?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_thousands_sep', 'Thousands Separator'); ?></strong><br />
<?php
  $options = array(
    array(language::translate('char_comma', 'Comma'), ','),
    array(language::translate('char_dot', 'Dot'), '.'),
    array(language::translate('char_space', 'Space'), ' '),
  );
  echo functions::form_draw_select_field('thousands_sep', $options, true, false, 'data-size="auto"');
?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_force_currency_code', 'Force Currency Code'); ?></strong><br />
        <?php echo functions::form_draw_text_field('currency_code', true, 'data-size="tiny"'); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_priority', 'Priority'); ?></strong><br />
        <?php echo functions::form_draw_number_field('priority', true); ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo functions::form_draw_checkbox('set_default', '1', (isset($language->data['code']) && $language->data['code'] && $language->data['code'] == settings::get('default_language_code')) ? '1' : true); ?> <?php echo language::translate('description_set_as_default_language', 'Set as default language'); ?><br />
        <?php echo functions::form_draw_checkbox('set_store', '1', (isset($language->data['code']) && $language->data['code'] && $language->data['code'] == settings::get('store_language_code')) ? '1' : true); ?> <?php echo language::translate('description_set_as_store_language', 'Set as store language'); ?>
      </td>
    </tr>
  </table>
  
  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($language->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>
  
<?php echo functions::form_draw_form_end(); ?>