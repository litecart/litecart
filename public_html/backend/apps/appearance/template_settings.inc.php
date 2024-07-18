<?php

  document::$title[] = language::translate('title_template_settings', 'Template Settings');

  breadcrumbs::add(language::translate('title_appearance', 'Appearance'));
  breadcrumbs::add(language::translate('title_template', 'Template'), document::ilink('appearance/template'));
  breadcrumbs::add(language::translate('title_template_settings', 'Template Settings'));

// Get template settings structure
  $settings = include 'app://frontend/templates/' . settings::get('template') .'/config.inc.php';

  if (empty($settings)) $settings = [];

// Insert template settings
  $saved_settings = json_decode(settings::get('template_settings'), true);

  foreach ($settings as $key => $setting) {

    switch (true) {

      case (substr($setting['function'], 0, 8) == 'regional'):

        foreach (array_keys(language::$languages) as $language_code) {
          if (isset($saved_settings[$setting['key']][$language_code])) {
            $settings[$key]['value'][$language_code] = $saved_settings[$setting['key']][$language_code];
          } else {
            $settings[$key]['value'][$language_code] = fallback($saved_settings[$setting['key']]['en'], $setting['default_value']);
          }
        }

        break;

      default:

        if (isset($saved_settings[$setting['key']])) {
          $settings[$key]['value'] = $saved_settings[$setting['key']];
        } else {
          $settings[$key]['value'] = $setting['default_value'];
        }

        break;
    }
  }

  if (empty($_POST) && isset($_GET['action']) && $_GET['action'] == 'edit') {
    foreach ($settings as $setting) {
      $_POST['settings'][$setting['key']] = $setting['value'];
    }
  }

  if (isset($_POST['save'])) {

    try {

      $new_settings = [];
      foreach ($settings as $setting) {
        $new_settings[$setting['key']] = $setting['value'];
      }

      foreach (array_keys($_POST['settings']) as $key) {
        if (isset($new_settings[$key])) {
          $new_settings[$key] = $_POST['settings'][$key];
        }
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."settings
        set
          `value` = '". database::input(json_encode($new_settings, JSON_UNESCAPED_SLASHES)) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where `key` = '". database::input('template_settings') ."'
        limit 1;"
      );

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));

      header('Location: '. document::ilink(null, [], true, ['action']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Readability
  foreach ($settings as $key => $setting) {
    switch (true) {

      case (substr($setting['function'], 0, 8) == 'password'):
        $setting['value'] = '****************';
        break;

      case (substr($setting['function'], 0, 8) == 'regional'):

        if (isset($setting['value'][language::$selected['code']])) {
          $settings[$key]['value'] = $setting['value'][language::$selected['code']];
        } else {
          $settings[$key]['value'] = '';
        }

        break;

      case (substr($setting['function'], 0, 6) == 'toggle'):

        if (in_array(strtolower($setting['value']), ['1', 'active', 'enabled', 'on', 'true', 'yes'])) {
         $settings[$key]['value'] = language::translate('title_true', 'True');
        } else if (in_array(strtolower($setting['value']), ['', '0', 'inactive', 'disabled', 'off', 'false', 'no'])) {
         $settings[$key]['value'] = language::translate('title_false', 'False');
        }

        break;

      default:
        $settings[$key]['value'] = $setting['value'];
        break;
    }
  }
?>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_template_settings', 'Template Settings'); ?>
    </div>
  </div>

  <?php echo functions::form_begin('template_settings_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th style="width: 50%;"><?php echo language::translate('title_key', 'Key'); ?></th>
          <th><?php echo language::translate('title_value', 'Value'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($settings as $setting) { ?>
        <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && $_GET['key'] == $setting['key']) { ?>
        <tr>
          <td style="white-space: normal;">
            <u><?php echo language::translate(settings::get('template').':title_'.$setting['key'], $setting['title']); ?></u><br>
            <?php echo language::translate(settings::get('template').':description_'.$setting['key'], $setting['description']); ?>
          </td>
          <td><?php echo functions::form_function('settings['.$setting['key'].']', $setting['function'], true); ?></td>
          <td class="text-end">
            <?php echo functions::form_button_predefined('save'); ?>
            <?php echo functions::form_button_predefined('cancel'); ?>
          </td>
        </tr>
        <?php } else { ?>
        <tr>
          <td><?php echo language::translate(settings::get('template').':title_'.$setting['key'], $setting['title']); ?></td>
          <td>
            <div style="max-height: 200px; overflow-y: auto;">
              <?php echo nl2br($setting['value']); ?>
            </div>
          </td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink('appearance/template_settings', ['action' => 'edit', 'key' => $setting['key']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
        <?php } ?>

        <?php if (!$settings) { ?>
        <tr>
          <td colspan="3"><?php echo language::translate('text_no_frontend_template_settings', 'There are no settings available for the frontend template.'); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>

  <?php echo functions::form_end(); ?>
</div>
