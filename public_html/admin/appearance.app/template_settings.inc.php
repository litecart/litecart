<?php

// Load template settings structure
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . settings::get('store_template_catalog') .'/config.inc.php');

// Get settings from database
  $settings = json_decode(settings::get('store_template_catalog_settings'), true);

// Complete missing settings
  foreach (array_keys($template_config) as $i) {

    if (substr($template_config[$i]['function'], 0, 8) == 'regional') {

      foreach (array_keys(language::$languages) as $language_code) {
        if (!isset($settings[$template_config[$i]['key']][$language_code])) $settings[$template_config[$i]['key']][$language_code] = $template_config[$i]['default_value'];
      }

    } else {
      if (!isset($settings[$template_config[$i]['key']])) $settings[$template_config[$i]['key']] = $template_config[$i]['default_value'];
    }
  }

  if (isset($_POST['save'])) {

    try {
      foreach (array_keys($_POST['settings']) as $key) {
        if (isset($settings[$key])) $settings[$key] = $_POST['settings'][$key];
      }

      database::query(
        "update ". DB_TABLE_SETTINGS ."
        set
          `value` = '". database::input(json_encode($settings)) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where `key` = '". database::input('store_template_catalog_settings') ."'
        limit 1;"
      );

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));

      header('Location: '. document::link('', array(), true, array('action')));
      exit;
    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

?>
<h1><?php echo $app_icon; ?> <?php echo language::translate('title_template_settings', 'Template Settings'); ?></h1>

<?php echo functions::form_draw_form_begin('template_settings_form', 'post', null, false, 'style="max-width: 960px;"'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th style="width: 250px;"><?php echo language::translate('title_key', 'Key'); ?></th>
        <th><?php echo language::translate('title_value', 'Value'); ?></th>
        <th style="width: 50px;">&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  if (!empty($template_config)) {

    foreach ($template_config as $config) {

      if (isset($_GET['action']) && $_GET['action'] == 'edit' && $_GET['key'] == $config['key']) {

        $_POST['settings'][$config['key']] = $settings[$config['key']];
?>
      <tr>
        <td style="white-space: normal;">
          <u><?php echo language::translate(settings::get('store_template_catalog').':title_'.$config['key'], $config['title']); ?></u><br />
          <?php echo language::translate(settings::get('store_template_catalog').':description_'.$config['key'], $config['description']); ?>
        </td>
        <td><?php echo functions::form_draw_function($config['function'], 'settings['.$config['key'].']', true); ?></td>
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

        case (substr($config['function'], 0, 14) == 'regional_input'):

          if (isset($settings[$config['key']][language::$selected['code']])) {
            $value = $settings[$config['key']][language::$selected['code']];

          } else if (isset($settings[$config['key']]['en'])) {
            $value = $settings[$config['key']]['en'];

          } else {
            $value = '';
          }

          break;

        case (substr($config['function'], 0, 6) == 'toggle'):

          if (in_array(($settings[$config['key']]), array('1', 'active', 'enabled', 'on', 'true', 'yes'))) {
           $value = language::translate('title_true', 'True');

          } else if (in_array(($settings[$config['key']]), array('', '0', 'inactive', 'disabled', 'off', 'false', 'no'))) {
           $value = language::translate('title_false', 'False');
          }

          break;
      }
?>
      <tr>
        <td><?php echo language::translate(settings::get('store_template_catalog').':title_'.$config['key'], $config['title']); ?></td>
        <td>
          <div style="max-height: 200px; overflow-y: auto;">
            <?php echo nl2br($value); ?>
          </div>
        </td>
        <td class="text-right"><a href="<?php echo document::href_link('', array('action' => 'edit', 'key' => $config['key']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
      </tr>
<?php
      }
    }
  } else {
?>
      <tr>
        <td colspan="3"><?php echo language::translate('text_no_template_settings', 'There are no settings available for this template.'); ?></td>
      </tr>
<?php
}
?>
    </tbody>
  </table>

<?php echo functions::form_draw_form_end(); ?>