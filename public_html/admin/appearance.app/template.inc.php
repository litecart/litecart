<?php

  document::$snippets['title'][] = language::translate('title_template', 'Template');

  breadcrumbs::add(language::translate('title_appearance', 'Appearance'));
  breadcrumbs::add(language::translate('title_template', 'Template'));

  if (isset($_POST['save'])) {

    try {
      if (!preg_match('#(\.catalog)$#', $_POST['template_catalog'])) throw new Exception(language::translate('error_invalid_catalog_template', 'Not a valid catalog template'));
      if (!preg_match('#(\.admin)$#', $_POST['template_admin'])) throw new Exception(language::translate('error_invalid_admin_template', 'Not a valid admin template'));

      if ($_POST['template_catalog'] != settings::get('store_template_catalog')) {
        database::query(
          "update ". DB_TABLE_PREFIX ."settings
          set
            `value` = '". database::input($_POST['template_catalog']) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
          where `key` = '". database::input('store_template_catalog') ."'
          limit 1;"
        );

      // Load template settings structure
        $template_config = include vmod::check(FS_DIR_APP . 'includes/templates/' . basename($_POST['template_catalog']) .'/config.inc.php');
        if (!is_array($template_config)) include vmod::check(FS_DIR_APP . 'includes/templates/' . basename($_POST['template_catalog']) .'/config.inc.php'); // Backwards compatibility

      // Set template default settings
        $settings = [];
        foreach (array_keys($template_config) as $i) {
          $settings[$template_config[$i]['key']] = $template_config[$i]['default_value'];
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."settings
          set
            `value` = '". database::input(json_encode($settings, JSON_UNESCAPED_SLASHES)) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
          where `key` = '". database::input('store_template_catalog_settings') ."'
          limit 1;"
        );

        if (!empty($settings)) {
          $redirect_to_settings = true;
        }
      }

      if ($_POST['template_admin'] != settings::get('store_template_admin')) {
        database::query(
          "update ". DB_TABLE_PREFIX ."settings
          set
            `value` = '". database::input($_POST['template_admin']) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
          where `key` = '". database::input('store_template_admin') ."'
          limit 1;"
        );
      }

      cache::clear_cache();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));

      if (!empty($redirect_to_settings)) {
        $redirect_url = document::link(WS_DIR_ADMIN, ['doc' => 'template_settings'], ['app']);
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
<div class="panel panel-app">
  <div class="panel-heading">
    <div class="panel-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_template', 'Template'); ?>
    </div>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('template_form', 'post', null, false, 'style="max-width: 320px;"'); ?>

      <div class="form-group">
        <label><?php echo language::translate('title_catalog_template', 'Catalog Template'); ?></label>
          <div class="input-group">
            <?php echo functions::form_draw_templates_list('catalog', 'template_catalog', empty($_POST['template_catalog']) ? settings::get('store_template_catalog') : true); ?>
            <a class="btn btn-default" href="<?php echo document::href_link(WS_DIR_ADMIN, ['doc' => 'template_settings'], ['app']); ?>" title="<?php echo language::translate('title_settings', 'Settings'); ?>"><?php echo functions::draw_fonticon('fa-wrench fa-lg'); ?></a>
          </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_admin_template', 'Admin Template'); ?></label>
          <?php echo functions::form_draw_templates_list('admin', 'template_admin', empty($_POST['template_admin']) ? settings::get('store_template_admin') : true); ?>
      </div>

      <div>
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>