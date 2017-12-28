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

  $module = new ctrl_module($module_id);
  $object = new $module_id();

  if (!$_POST) {
    $_POST['settings'] = $module->data['settings'];
  }

  if (isset($_POST['save'])) {

    try {
      foreach (array_keys($_POST['settings']) as $key) {
        if (in_array($key, array('id', 'date_updated', 'date_created'))) continue;
        if (isset($module->data['settings'][$key])) $module->data['settings'][$key] = $_POST['settings'][$key];
      }

      $module->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::link('', array('doc' => $return_doc), array('app')));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['uninstall'])) {

    try {
      $module->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::link('', array('doc' => $return_doc), array('app')));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  breadcrumbs::add(!empty($module->data['id']) ? language::translate('title_edit_module', 'Edit Module') : language::translate('title_install_module', 'Install Module'));

  if (empty($_POST) && !empty($module->data['id'])) {
    notices::add('notices', language::translate('text_make_changes_necessary_to_install', 'Make any changes necessary to continue installation'));
  }

?>
<style>
pre.last-log {
  max-height: 800px;
  overflow-y: auto;
}
</style>

<h1><?php echo $app_icon; ?> <?php echo !empty($module->data['id']) ? language::translate('title_edit_module', 'Edit Module') : language::translate('title_install_module', 'Install Module'); ?></h1>

<h2><?php echo $object->name; ?></h2>

<?php echo !empty($object->author) ? '<p style="font-style: italic;"><strong>'. language::translate('title_developed_by', 'Developed by') .'</strong> <a href="'. $object->website .'" target="_blank">'. $object->author .'</a></p>' : false; ?>

<?php echo !empty($object->description) ? '<p style="max-width: 400px;">'. $object->description .'</p>' : ''; ?>

<?php echo functions::form_draw_form_begin('module_form', 'post', false, false, 'style="max-width: 960px;"'); ?>

  <table class="table table-striped">
    <tbody>
      <?php foreach ($object->settings() as $setting) { ?>
      <tr>
        <td style="width: 50%">
          <strong><?php echo $setting['title']; ?></strong>
          <?php echo !empty($setting['description']) ? '<div>'. $setting['description'] .'</div>' : ''; ?>
        </td>
        <td style="width: 50%">
          <?php echo functions::form_draw_function($setting['function'], 'settings['.$setting['key'].']', true, !empty($setting['description']) ? ' data-toggle="tooltip" title="'.htmlspecialchars($setting['description']).'"' : ''); ?>
        </td>
      </tr>
      <?php } ?>
      <tr>
        <td>
          <label><?php echo language::translate('title_translations', 'Translations'); ?></label>
        </td>
        <td>
          <a href="<?php echo document::href_link('', array('app' => 'translations', 'doc' => 'search', 'query' => $module_id . ':', 'modules' => 'true')); ?>"><?php echo language::translate('title_edit_translations', 'Edit Translations'); ?></a>
        </td>
      </tr>
    </tbody>
  </table>

  <p class="btn-group">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
    <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1)"', 'cancel'); ?>
    <?php echo functions::form_draw_button('uninstall', language::translate('title_uninstall', 'Uninstall'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete'); ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>

<?php if (!empty($module->data['last_log'])) { ?>
<div class="form-group">
  <label><?php echo language::translate('title_last_log', 'Last Log'); ?></label>
  <pre class="last-log form-control"><?php echo $module->data['last_log']; ?></pre>
</div>
<?php } ?>