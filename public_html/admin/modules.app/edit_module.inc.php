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

  $modules_query = database::query(
    "select * from ". DB_TABLE_MODULES ."
    where type = '". database::input($type) ."'
    and module_id = '". database::input($module_id) ."'
    limit 1;"
  );
  $module = database::fetch($modules_query);

  $is_installed = $module ? true : false;

  $object = new $module_id;

  if (empty($_POST)) {
    if ($is_installed) {
      if ($settings = json_decode($module['settings'], true)) {
        foreach ($settings as $key => $value) {
          $_POST[$key] = $value;
        }
      }
    } else {
      foreach ($object->settings() as $setting) {
        $_POST[$setting['key']] = $setting['default_value'];
      }
    }
  }

  if (isset($_POST['save'])) {

    $ctrl_module = new ctrl_module($module_id);

    $fields = array_column($object->settings(), 'key');

    foreach ($fields as $field) {
      if (in_array($field, array('id', 'date_updated', 'date_created'))) continue;
      if (isset($_POST[$field])) $ctrl_module->data['settings'][$field] = $_POST[$field];
    }

    $ctrl_module->save();

    header('Location: '. document::link('', array('doc' => $return_doc), array('app')));
    exit;
  }

  if (isset($_POST['uninstall'])) {

    $ctrl_module = new ctrl_module($module_id);

    $ctrl_module->delete();

    header('Location: '. document::link('', array('doc' => $return_doc), array('app')));
    exit;
  }

  breadcrumbs::add($is_installed ? language::translate('title_edit_module', 'Edit Module') : language::translate('title_install_module', 'Install Module'));

  if (empty($_POST) && !$is_installed) {
    notices::$data['notices'][] = language::translate('text_make_changes_necessary_to_install', 'Make any changes necessary to continue installation');
  }

?>
<h1 style="margin-top: 0;"><?php echo $app_icon; ?> <?php echo $is_installed ? language::translate('title_edit_module', 'Edit Module') : language::translate('title_install_module', 'Install Module'); ?></h1>

<h2 style="margin-top: 0;"><?php echo $object->name; ?></h2>

<?php echo !empty($object->author) ? '<p style="font-style: italic;"><strong>'. language::translate('title_developed_by', 'Developed by') .'</strong> <a href="'. $object->website .'" target="_blank">'. $object->author .'</a></p>' : false; ?>

<?php echo !empty($object->description) ? '<p style="max-width: 400px;">'. $object->description .'</p>' : ''; ?>

<?php echo functions::form_draw_form_begin('module_form', 'post', false, false, 'style="max-width: 960px;"'); ?>

  <table class="table table-striped">
    <tbody>
      <?php foreach ($object->settings() as $setting) { ?>
      <tr>
        <td class="col-md-6">
          <label><?php echo $setting['title']; ?></label>
          <?php echo !empty($setting['description']) ? '<p>'. $setting['description'] .'</p>' : ''; ?>
        </td>
        <td class="col-md-6">
          <?php echo functions::form_draw_function($setting['function'], $setting['key'], true, !empty($setting['description']) ? ' data-toggle="tooltip" title="'.htmlspecialchars($setting['description']).'"' : ''); ?>
        </td>
      </tr>
      <?php } ?>
      <tr>
        <td>
          <label><?php echo language::translate('title_translations', 'Translations'); ?></label>
        </td>
        <td>
          <a href="<?php echo document::href_link('', array('app' => 'translations', 'doc' => 'search', 'query' => $object_id . ':', 'modules' => 'true')); ?>"><?php echo language::translate('title_edit_translations', 'Edit Translations'); ?></a>
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

<?php if (!empty($module['last_log'])) { ?>
<div class="form-group">
  <label><?php echo language::translate('title_last_log', 'Last Log'); ?></label>
  <pre><?php echo $module['last_log']; ?></pre>
</div>
<?php } ?>