<?php

  if (!empty($_POST['save'])) {

    if (!preg_match('#(\.catalog)$#', $_POST['template_catalog'])) notices::add('errors', language::translate('error_invalid_catalog_template', 'Not a valid catalog template'));
    if (!preg_match('#(\.admin)$#', $_POST['template_admin'])) notices::add('errors', language::translate('error_invalid_admin_template', 'Not a valid admin template'));

    if (empty(notices::$data['errors'])) {

      if ($_POST['template_catalog'] != settings::get('store_template_catalog')) {
        database::query(
          "update ". DB_TABLE_SETTINGS ."
          set
            `value` = '". database::input($_POST['template_catalog']) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
          where `key` = '". database::input('store_template_catalog') ."'
          limit 1;"
        );


      // Get template settings structure
        include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . basename($_POST['template_catalog']) .'/config.inc.php');

      // Set template default settings
        $settings = array();
        foreach (array_keys($template_config) as $key) {
          $settings[$key] = $template_config[$key]['default_value'];
        }

        database::query(
          "update ". DB_TABLE_SETTINGS ."
          set
            `value` = '". database::input(serialize($settings)) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
          where `key` = '". database::input('store_template_catalog_settings') ."'
          limit 1;"
        );

        $redirect_to_settings = true;
      }

      if ($_POST['template_admin'] != settings::get('store_template_admin')) {
        database::query(
          "update ". DB_TABLE_SETTINGS ."
          set
            `value` = '". database::input($_POST['template_admin']) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
          where `key` = '". database::input('store_template_admin') ."'
          limit 1;"
        );
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes were successfully saved.'));

      if (!empty($redirect_to_settings)) {
        header('Location: '. document::link('', array('doc' => 'template_settings'), array('app')));
      } else {
        header('Location: '. document::link());
      }
      exit;
    }
  }

?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_template', 'Template'); ?></h1>

<?php echo functions::form_draw_form_begin('template_form', 'post'); ?>

  <table>
    <tr>
      <td><?php echo language::translate('title_catalog_template', 'Catalog Template'); ?><br />
        <?php echo functions::form_draw_templates_list('catalog', 'template_catalog', empty($_POST['template_catalog']) ? settings::get('store_template_catalog') : true); ?> <a href="<?php echo document::href_link(WS_DIR_ADMIN, array('doc' => 'template_settings'), array('app')); ?>" alt="<?php language::translate('title_settings', 'Settings'); ?>"><?php echo functions::draw_fonticon('fa-wrench fa-lg'); ?></a></td>
    </tr>
    <tr>
      <td><?php echo language::translate('title_admin_template', 'Admin Template'); ?><br />
        <?php echo functions::form_draw_templates_list('admin', 'template_admin', empty($_POST['template_admin']) ? settings::get('store_template_admin') : true); ?></td>
    </tr>
  </table>

  <p class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?></p>

<?php echo functions::form_draw_form_end(); ?>