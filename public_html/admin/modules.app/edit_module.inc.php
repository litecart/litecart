<?php
  if (empty($_GET['module_id'])) die('Unknown module id');

  $module_id = basename($_GET['module_id']);

  switch ($_GET['doc']) {
    case 'edit_customer':
      $type = 'customer';
      $return_doc = 'customer';
      break;
    case 'edit_job':
      $type = 'job';
      $return_doc = 'jobs';
      break;
    case 'edit_order':
      $type = 'order';
      $return_doc = 'order';
      break;
    case 'edit_order_total':
      $type = 'order_total';
      $return_doc = 'order_total';
      break;
    case 'edit_payment':
      $type = 'payment';
      $return_doc = 'payment';
      break;
    case 'edit_shipping':
      $type = 'shipping';
      $return_doc = 'shipping';
      break;
    default:
      trigger_error('Unknown module type', E_USER_ERROR);
  }

  $module = new ent_module($module_id);
  $object = new $module_id();

  document::$snippets['title'][] = !empty($module->data['id']) ? language::translate('title_edit_module', 'Edit Module') : language::translate('title_install_module', 'Install Module');

  breadcrumbs::add(language::translate('title_modules', 'Modules'));
  breadcrumbs::add(!empty($module->data['id']) ? language::translate('title_edit_module', 'Edit Module') : language::translate('title_install_module', 'Install Module'));

  if (!$_POST) {
    $_POST['settings'] = $module->data['settings'];
  }

  if (isset($_POST['save'])) {

    try {
      foreach (array_keys($module->data['settings']) as $key) {
        if (in_array($key, array('id', 'date_updated', 'date_created'))) continue;
        $module->data['settings'][$key] = isset($_POST['settings'][$key]) ? $_POST['settings'][$key] : '';
      }

      $module->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => $return_doc], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['uninstall'])) {

    try {
      $module->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => $return_doc], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (empty($_POST) && !empty($module->data['id'])) {
    notices::add('notices', language::translate('text_make_changes_necessary_to_install', 'Make any changes necessary to continue installation'));
  }

?>
<style>
#box-last-log pre {
  max-height: 800px;
  overflow-y: auto;
}
</style>

<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo !empty($module->data['id']) ? language::translate('title_edit_module', 'Edit Module') : language::translate('title_install_module', 'Install Module'); ?>
  </div>

  <div class="panel-body">
    <h2><?php echo $object->name; ?></h2>

    <?php echo !empty($object->author) ? '<p style="font-style: italic;"><strong>'. language::translate('title_developed_by', 'Developed by') .'</strong> <a href="'. $object->website .'" target="_blank">'. $object->author .'</a></p>' : false; ?>

    <?php echo !empty($object->description) ? '<p style="max-width: 960px;">'. $object->description .'</p>' : ''; ?>

    <?php echo functions::form_draw_form_begin('module_form', 'post', false, false, 'autocomplete="off" style="max-width: 960px;"'); ?>

      <table class="table table-striped">
        <tbody>
          <?php foreach ($object->settings() as $setting) { ?>
          <tr>
            <td style="width: 50%">
              <strong><?php echo $setting['title']; ?></strong>
              <?php echo !empty($setting['description']) ? '<div>'. $setting['description'] .'</div>' : ''; ?>
            </td>
            <td style="width: 50%">
              <?php if (!empty($setting['multiple'])) { ?>
              <?php echo functions::form_draw_function($setting['function'], 'settings['.$setting['key'].'][]', true, !empty($setting['placeholder']) ? ' placeholder="'. functions::escape_html($setting['placeholder']) .'"' : ''); ?>
              <?php } else { ?>
              <?php echo functions::form_draw_function($setting['function'], 'settings['.$setting['key'].']', true, !empty($setting['placeholder']) ? ' placeholder="'. functions::escape_html($setting['placeholder']) .'"' : ''); ?>
              <?php } ?>
            </td>
          </tr>
          <?php } ?>
          <tr>
            <td>
              <label><?php echo language::translate('title_translations', 'Translations'); ?></label>
            </td>
            <td>
              <a href="<?php echo document::href_link('', ['app' => 'translations', 'doc' => 'search', 'query' => $module_id . ':', 'modules' => 'true']); ?>"><?php echo language::translate('title_edit_translations', 'Edit Translations'); ?></a>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="panel-action">
        <div class="btn-group">
          <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
          <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1)"', 'cancel'); ?>
          <?php echo functions::form_draw_button('uninstall', language::translate('title_uninstall', 'Uninstall'), 'submit', 'onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete'); ?>
        </div>
      </div>

    <?php echo functions::form_draw_form_end(); ?>

    <?php if (!empty($module->data['last_log'])) { ?>
    <div id="box-last-log">
      <h2><?php echo language::translate('title_last_log', 'Last Log'); ?></h2>
      <pre class="form-control"><?php echo $module->data['last_log']; ?></pre>
    </div>
    <?php } ?>
  </div>
</div>
