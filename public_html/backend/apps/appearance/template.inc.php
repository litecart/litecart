<?php

  document::$snippets['title'][] = language::translate('title_template', 'Template');

  breadcrumbs::add(language::translate('title_template', 'Template'));

  if (isset($_POST['save'])) {

    try {
      if (!is_dir('app://frontend/templates/' . basename($_POST['template']))) {
        throw new Exception(language::translate('error_invalid_template', 'Not a valid template'));
      }

      if ($_POST['template'] != settings::get('template')) {
        database::query(
          "update ". DB_TABLE_PREFIX ."settings
          set `value` = '". database::input($_POST['template']) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
          where `key` = '". database::input('template') ."'
          limit 1;"
        );

      // Load template settings structure
        $template_config = include 'app://frontend/templates/' . basename($_POST['template']) .'/config.inc.php';

      // Set template default settings
        $settings = [];
        foreach (array_keys($template_config) as $i) {
          $settings[$template_config[$i]['key']] = $template_config[$i]['default_value'];
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."settings
          set `value` = '". database::input(json_encode($settings, JSON_UNESCAPED_SLASHES)) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
          where `key` = '". database::input('template_settings') ."'
          limit 1;"
        );

        if (!empty($settings)) {
          $redirect_to_settings = true;
        }
      }

      self::clear_cache();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));

      if (!empty($redirect_to_settings)) {
        $redirect_url = document::ilink('appearance/template_settings');
      } else {
        $redirect_url = document::link();
      }

      header('Location: '. $redirect_url);
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_template', 'Template'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('template_form', 'post', null, false, 'style="max-width: 320px;"'); ?>

      <div class="form-group">
        <label><?php echo language::translate('title_template', 'Template'); ?></label>
          <div class="input-group">
            <?php echo functions::form_draw_templates_list('template', empty($_POST['template']) ? settings::get('template') : true); ?>
            <a class="btn btn-default" href="<?php echo document::href_ilink('appearance/template_settings'); ?>" title="<?php echo language::translate('title_settings', 'Settings'); ?>"><?php echo functions::draw_fonticon('fa-wrench fa-lg'); ?></a>
          </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>