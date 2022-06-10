<?php

  document::$snippets['title'][] = language::translate('title_template_settings', 'Template Settings');

  breadcrumbs::add(language::translate('title_appearance', 'Appearance'));
  breadcrumbs::add(language::translate('title_template', 'Template'), document::link(WS_DIR_ADMIN, ['doc' => 'template'], ['app']));
  breadcrumbs::add(language::translate('title_template_settings', 'Template Settings'));

// Get template settings structure
  $settings = include vmod::check(FS_DIR_APP . 'includes/templates/' . settings::get('store_template_catalog') .'/config.inc.php');
  if (!is_array($settings)) include vmod::check(FS_DIR_APP . 'includes/templates/' . settings::get('store_template_catalog') .'/config.inc.php'); // Backwards compatibility

  if (empty($settings)) $settings = [];

// Insert template settings
  $saved_settings = json_decode(settings::get('store_template_catalog_settings'), true);

  foreach ($settings as $key => $setting) {

    switch (true) {

      case (substr($setting['function'], 0, 8) == 'regional'):

        foreach (array_keys(language::$languages) as $language_code) {
          if (isset($saved_settings[$setting['key']][$language_code])) {
            $settings[$key]['value'][$language_code] = $saved_settings[$setting['key']][$language_code];
          } else {
            $settings[$key]['value'][$language_code] = !empty($saved_settings[$setting['key']]['en']) ? $saved_settings[$setting['key']]['en'] : $setting['default_value'];
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
        if (isset($new_settings[$key])) $new_settings[$key] = $_POST['settings'][$key];
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."settings
        set
          `value` = '". database::input(json_encode($new_settings, JSON_UNESCAPED_SLASHES)) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where `key` = '". database::input('store_template_catalog_settings') ."'
        limit 1;"
      );

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));

      header('Location: '. document::link(WS_DIR_ADMIN, [], true, ['action']));
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

        if (preg_match('#^(1|active|enabled|on|true|yes)$#i', $setting['value'])) {
         $settings[$key]['value'] = language::translate('title_true', 'True');
        } else {
         $settings[$key]['value'] = language::translate('title_false', 'False');
        }

        break;

      default:
        $settings[$key]['value'] = $setting['value'];
        break;
    }
  }
?>

<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_template_settings', 'Template Settings'); ?>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('template_settings_form', 'post', null, false, 'style="max-width: 960px;"'); ?>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th style="width: 250px;"><?php echo language::translate('title_key', 'Key'); ?></th>
            <th><?php echo language::translate('title_value', 'Value'); ?></th>
            <th style="width: 50px;">&nbsp;</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($settings as $setting) { ?>
          <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && $_GET['key'] == $setting['key']) { ?>
          <tr>
            <td style="white-space: normal;">
              <u><?php echo language::translate(settings::get('store_template_catalog').':title_'.$setting['key'], $setting['title']); ?></u><br />
              <?php echo language::translate(settings::get('store_template_catalog').':description_'.$setting['key'], $setting['description']); ?>
            </td>
            <td><?php echo functions::form_draw_function($setting['function'], 'settings['.$setting['key'].']', true); ?></td>
            <td class="text-end">
              <div class="btn-group">
                <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
                <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
              </div>
            </td>
          </tr>
          <?php } else { ?>
          <tr>
            <td><?php echo language::translate(settings::get('store_template_catalog').':title_'.$setting['key'], $setting['title']); ?></td>
            <td>
              <div style="max-height: 200px; overflow-y: auto;">
                <?php echo nl2br($setting['value']); ?>
              </div>
            </td>
            <td class="text-end"><a href="<?php echo document::href_link('', ['action' => 'edit', 'key' => $setting['key']], true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
          </tr>
          <?php } ?>
          <?php } ?>

          <?php if (!$settings) { ?>
          <tr>
            <td colspan="3"><?php echo language::translate('text_no_template_settings', 'There are no settings available for this template.'); ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
