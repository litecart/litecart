<?php
  if (empty($_GET['module_id'])) die('Unknown module id');

  $module_id = basename($_GET['module_id']);

  switch ($_GET['doc']) {
    case 'edit_customer':
      $type = 'customer';
      break;
    case 'edit_job':
      $type = 'jobs';
      break;
    case 'edit_order_action':
      $type = 'order_action';
      break;
    case 'edit_order_success':
      $type = 'order_success';
      break;
    case 'edit_order_total':
      $type = 'order_total';
      break;
    case 'edit_payment':
      $type = 'payment';
      break;
    case 'edit_shipping':
      $type = 'shipping';
      break;
    default:
      die('Unknown module type');
  }

  $installed = in_array($module_id, explode(';', settings::get($type.'_modules'))) ? true : false;

  $module = new ctrl_module(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . $type . '/' . $module_id .'.inc.php');

  if (isset($_POST['save'])) {
    $module->save($_POST);
    header('Location: '. document::link('', array('doc' => $type), array('app')));
    exit;
  }

  if (isset($_POST['uninstall'])) {
    $module->uninstall();
    header('Location: '. document::link('', array('doc' => $type), array('app')));
    exit;
  }

  breadcrumbs::add($installed ? language::translate('title_edit_module', 'Edit Module') : language::translate('title_install_module', 'Install Module'));

  if (empty($_POST)) {
    if (!$installed) notices::$data['notices'][] = language::translate('text_make_changes_necessary_to_install', 'Make any changes necessary to continue installation');
  }
?>
<h1 style="margin-top: 0;"><?php echo $app_icon; ?> <?php echo $installed ? language::translate('title_edit_module', 'Edit Module') : language::translate('title_install_module', 'Install Module'); ?></h1>

<h2 style="margin-top: 0;"><?php echo $module->name; ?></h2>

<?php echo !empty($module->author) ? '<p style="font-style: italic;"><strong>'. language::translate('title_developed_by', 'Developed by') .'</strong> <a href="'. $module->website .'" target="_blank">'. $module->author .'</a></p>' : false; ?>

<?php echo !empty($module->description) ? '<p style="max-width: 400px;">'. $module->description .'</p>' : false; ?>

<?php echo functions::form_draw_form_begin('module_form', 'post'); ?>

  <table>
<?php
  foreach ($module->settings as $setting) {
?>
    <tr>
      <td><strong><?php echo $setting['title']; ?></strong><?php echo !empty($setting['description']) ? '<br />' . $setting['description'] : false; ?><br />
      <?php echo functions::form_draw_hidden_field('key', $setting['key']) . functions::form_draw_function($setting['function'], $setting['key'], $setting['value']); ?></td>
    </tr>
<?php
  }
?>
  </table>

  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1)"', 'cancel'); ?> <?php echo functions::form_draw_button('uninstall', language::translate('title_uninstall', 'Uninstall'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete'); ?></span></p>

<?php echo functions::form_draw_form_end(); ?>

<p><a href="<?php echo document::href_link('', array('app' => 'translations', 'doc' => 'search', 'query' => $module_id . ':', 'modules' => 'true')); ?>"><?php echo language::translate('title_edit_translations', 'Edit Translations'); ?></a></p>