<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  if (isset($_POST['save'])) {

    try {

      foreach (array_keys($_POST['settings']) as $key) {
        $settings_query = database::query(
          "select * from ". DB_TABLE_SETTINGS ."
          where `key` = '". database::input($key) ."';"
        );

        if (!$setting = database::fetch($settings_query)) {
          throw new Exception(language::translate('error_setting_key_does_not_exist', 'The settings key does not exist'));
        }

        $values_required_for_keys = array(
          'store_language_code',
          'store_currency_code',
          'store_weight_class',
          'store_length_class',
          'default_language_code',
          'default_currency_code',
        );

        if (in_array($key, $values_required_for_keys) && empty($_POST['settings'][$key])) {
          throw new Exception(language::translate('error_cannot_set_empty_value_for_setting', 'You cannot set an empty value for this setting'));
        }

        if (substr($setting['function'], 0, 8) == 'regional') {
          $value = json_encode($_POST['settings'][$key]);
        } else {
          $value = $_POST['settings'][$key];
        }

        database::query(
          "update ". DB_TABLE_SETTINGS ."
          set
            `value` = '". database::input($value) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
          where `key` = '". database::input($key) ."'
          limit 1;"
        );
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, array(), true, array('action')));
      exit;

    } catch (Exception $e) {
      notices::add('success', $e->getMessage());
    }
  }

  $settings_groups_query = database::query(
    "select * from ". DB_TABLE_SETTINGS_GROUPS ."
    ". (!empty($_GET['doc']) ? "where `key` = '". database::input($_GET['doc']) ."'" : "") ."
    order by priority, `key`;"
  );

  if (!$settings_group = database::fetch($settings_groups_query)) {
    die('Invalid setting group ('. $_GET['doc'] .')');
  }

// Table Rows
  $settings = array();

  $settings_query = database::query(
    "select * from ". DB_TABLE_SETTINGS ."
    where `setting_group_key` = '". database::input($settings_group['key']) ."'
    order by priority, `key` asc;"
  );

  if ($_GET['page'] > 1) database::seek($settings_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($setting = database::fetch($settings_query)) {

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

        case (substr($setting['function'], 0, 14) == 'regional_input'):
          $setting['value'] = !empty($setting['value']) ? json_decode($setting['value'], true) : null;
          $setting['value'] = isset($setting['value'][language::$selected['code']]) ? $setting['value'][language::$selected['code']] : null;
          break;

        case (substr($setting['function'], 0, 6) == 'toggle'):
          if (in_array(($setting['value']), array('1', 'active', 'enabled', 'on', 'true', 'yes'))) {
           $setting['value'] = language::translate('title_true', 'True');
          } else if (in_array(($setting['value']), array('', '0', 'inactive', 'disabled', 'off', 'false', 'no'))) {
           $setting['value'] = language::translate('title_false', 'False');
          }
          break;
      }
    }

    $settings[] = $setting;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($settings_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_settings', 'Settings'); ?>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('settings_form', 'post'); ?>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo language::translate('title_key', 'Key'); ?></th>
            <th><?php echo language::translate('title_value', 'Value'); ?></th>
            <th>&nbsp;</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($settings as $setting) { ?>
          <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && $_GET['key'] == $setting['key']) { ?>
          <tr>
            <td>
              <u><?php echo language::translate('settings_key:title_'.$setting['key'], $setting['title']); ?></u><br />
              <?php echo language::translate('settings_key:description_'.$setting['key'], $setting['description']); ?>
            </td>
            <td><?php echo functions::form_draw_function($setting['function'], 'settings['.$setting['key'].']', true); ?></td>
            <td class="text-right">
              <div class="btn-group">
                <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
                <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
              </div>
            </td>
          </tr>
          <?php } else { ?>
          <tr>
            <td><?php echo language::translate('settings_key:title_'.$setting['key'], $setting['title']); ?></td>
            <td style="white-space: normal;">
              <div style="max-height: 200px; overflow-y: auto;" title="<?php echo htmlspecialchars(language::translate('settings_key:description_'.$setting['key'], $setting['description'])); ?>">
                <?php echo $setting['value']; ?>
              </div>
            </td>
            <td class="text-right"><a href="<?php echo document::href_link('', array('action' => 'edit', 'key' => $setting['key']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
          </tr>
          <?php } ?>
          <?php } ?>
        </tbody>
      </table>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <div class="panel-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
</div>
