<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_settings', 'Settings');

  breadcrumbs::add(language::translate('title_settings', 'Settings'));

  if (isset($_POST['save'])) {

    try {

      foreach (array_keys($_POST['settings']) as $key) {

        $setting = database::query(
          "select * from ". DB_TABLE_PREFIX ."settings
          where `key` = '". database::input($key) ."'
          limit 1;"
        )->fetch();

        if (!$setting) {
          throw new Exception(language::translate('error_setting_key_does_not_exist', 'The settings key does not exist'));
        }

        if (!empty($setting['required']) && empty($_POST['settings'][$key])) {
          throw new Exception(language::translate('error_cannot_set_empty_value_for_setting', 'You cannot set an empty value for this setting'));
        }

        if (substr($setting['function'], 0, 8) == 'regional') {
          $value = json_encode($_POST['settings'][$key]);
        } else {
          $value = $_POST['settings'][$key];
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."settings
          set `value` = '". database::input($value) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
          where `key` = '". database::input($key) ."'
          limit 1;"
        );
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(null, [], true, ['action']));
      exit;

    } catch (Exception $e) {
      notices::add('success', $e->getMessage());
    }
  }

  $settings_group = database::query(
    "select * from ". DB_TABLE_PREFIX ."settings_groups
    where `key` = '". database::input(__DOC__) ."'
    order by priority, `key`
    limit 1;"
  )->fetch();

  if (!$settings_group) {
    notices::add('errors', 'Invalid settings group ('. __DOC__ .')');
    return;
  }

// Table Rows, Total Number of Rows, Total Number of Pages
  $settings = database::query(
    "select * from ". DB_TABLE_PREFIX ."settings
    where `group_key` = '". database::input($settings_group['key']) ."'
    order by priority, `key` asc;"
  )->fetch_page($_GET['page'], null, $num_rows, $num_pages);

  foreach ($settings as $i => $setting) {
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && $_GET['key'] == $setting['key']) {

      switch (true) {
        case (substr($setting['function'], 0, 14) == 'regional_input'):
          if (!isset($_POST['settings'][$setting['key']])) {
            $_POST['settings'][$setting['key']] = !empty($setting['value']) ? json_decode($setting['value'], true) : null;
          }
          break;

        default:
          $_POST['settings'][$setting['key']] = $setting['value'];
          break;
      }

    } else {

      switch (true) {
        case (substr($setting['function'], 0, 8) == 'password'):
          $setting['value'] = '****************';
          break;

        case (substr($setting['function'], 0, 12) == 'order_status'):
          $setting['value'] = $setting['value'] ? reference::order_status($setting['value'])->name : '';
          break;

        case (substr($setting['function'], 0, 4) == 'page'):
          $setting['value'] = $setting['value'] ? reference::page($setting['value'])->title : '';
          break;

        case (substr($setting['function'], 0, 14) == 'regional_input'):
          $setting['value'] = !empty($setting['value']) ? json_decode($setting['value'], true) : null;
          $setting['value'] = isset($setting['value'][language::$selected['code']]) ? $setting['value'][language::$selected['code']] : null;
          break;

        case (substr($setting['function'], 0, 6) == 'toggle'):
          if (in_array(($setting['value']), ['1', 'active', 'enabled', 'on', 'true', 'yes'])) {
           $setting['value'] = language::translate('title_true', 'True');
          } else if (in_array(($setting['value']), ['', '0', 'inactive', 'disabled', 'off', 'false', 'no'])) {
           $setting['value'] = language::translate('title_false', 'False');
          }
          break;
      }
    }

    $settings[$i] = $setting;
  }

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_settings', 'Settings').' &ndash; '.$settings_group['name']; ?>
    </div>
  </div>

  <?php echo functions::form_begin('settings_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th style="width: 35%;"><?php echo language::translate('title_key', 'Key'); ?></th>
          <th><?php echo language::translate('title_value', 'Value'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($settings as $setting) { ?>
        <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && $_GET['key'] == $setting['key']) { ?>
        <tr>
          <td>
            <strong><?php echo language::translate('settings_key:title_'.$setting['key'], $setting['title']); ?></strong><br />
            <?php echo language::translate('settings_key:description_'.$setting['key'], $setting['description']); ?>
          </td>
          <td><?php echo functions::form_function('settings['.$setting['key'].']', $setting['function'], true); ?></td>
          <td class="text-end">
            <?php echo functions::form_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
            <?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
          </td>
        </tr>
        <?php } else { ?>
        <tr>
          <td class="text-start"><a class="link" href="<?php echo document::href_ilink(null, ['action' => 'edit', 'key' => $setting['key']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo language::translate('settings_key:title_'.$setting['key'], $setting['title']); ?></a></td>
          <td style="white-space: normal;">
            <div style="max-height: 200px; overflow-y: auto;" title="<?php echo functions::escape_html(language::translate('settings_key:description_'.$setting['key'], $setting['description'])); ?>">
              <?php echo $setting['value']; ?>
            </div>
          </td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(null, ['action' => 'edit', 'key' => $setting['key']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
        <?php } ?>
      </tbody>
    </table>

  <?php echo functions::form_end(); ?>

  <?php if ($num_pages > 1) { ?>
  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>
</div>
