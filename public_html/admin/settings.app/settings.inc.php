<?php
  if (empty($_GET['setting_group_key'])) $_GET['setting_group_key'] = 'store_info';
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

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::link('', array(), true, array('action')));
      exit;

    } catch (Exception $e) {
      notices::add('success', $e->getMessage());
    }
  }

  $settings_groups_query = database::query(
    "select * from ". DB_TABLE_SETTINGS_GROUPS ."
    order by priority, `key`;"
  );

  while ($group = database::fetch($settings_groups_query)) {
    if ($_GET['doc'] == $group['key']) $setting_group = $group;
  }

  if (empty($setting_group)) die('Invalid setting group ('. $setting_group .')');
?>
<h1><?php echo $app_icon; ?> <?php echo language::translate('title_settings', 'Settings'); ?></h1>

<?php echo functions::form_draw_form_begin('settings_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo language::translate('title_key', 'Key'); ?></th>
        <th><?php echo language::translate('title_value', 'Value'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  $settings_query = database::query(
    "select * from ". DB_TABLE_SETTINGS ."
    where `setting_group_key` = '". $setting_group['key'] ."'
    order by priority, `key` asc;"
  );

  if (database::num_rows($settings_query) > 0) {

    if ($_GET['page'] > 1) database::seek($settings_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($setting = database::fetch($settings_query)) {

      if (isset($_GET['action']) && $_GET['action'] == 'edit' && $_GET['key'] == $setting['key']) {

        switch (true) {
          case (substr($setting['function'], 0, 14) == 'regional_input'):
            if (!isset($_POST['settings'][$setting['key']])) {
              $_POST['settings'][$setting['key']] = @json_decode($setting['value'], true);
            }
            break;

          default:
            $_POST['settings'][$setting['key']] = $setting['value'];
            break;
        }
?>
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
<?php
      } else {

        switch (true) {
          case (substr($setting['function'], 0, 14) == 'regional_input'):
            $setting['value'] = @json_decode($setting['value'], true);
            $setting['value'] = $setting['value'][language::$selected['code']];
            break;

          case (substr($setting['function'], 0, 6) == 'toggle'):
            if (in_array(($setting['value']), array('1', 'active', 'enabled', 'on', 'true', 'yes'))) {
             $setting['value'] = language::translate('title_true', 'True');
            } else if (in_array(($setting['value']), array('', '0', 'inactive', 'disabled', 'off', 'false', 'no'))) {
             $setting['value'] = language::translate('title_false', 'False');
            }
            break;
        }
?>
      <tr>
        <td><?php echo language::translate('settings_key:title_'.$setting['key'], $setting['title']); ?></td>
        <td style="white-space: normal;">
          <div style="max-height: 200px; overflow-y: auto;" title="<?php echo htmlspecialchars(language::translate('settings_key:description_'.$setting['key'], $setting['description'])); ?>">
            <?php echo $setting['value']; ?>
          </div>
        </td>
        <td class="text-right"><a href="<?php echo document::href_link('', array('action' => 'edit', 'key' => $setting['key']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
      </tr>
<?php
      }
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
    </tbody>
  </table>

<?php echo functions::form_draw_form_end(); ?>

<?php echo functions::draw_pagination(ceil(database::num_rows($settings_query)/settings::get('data_table_rows_per_page'))); ?>