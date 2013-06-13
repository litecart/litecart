<?php
  if (!isset($_GET['module_id']) || $_GET['module_id'] == '') die('Unknown module id');
  
  $module_id = basename($_GET['module_id']);
  
  switch ($_GET['doc']) {
    case 'edit_get_address':
      $type = 'get_address';
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
  
  $module = new ctrl_module(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . $type . '/' . $module_id .'.inc.php');
  
  if (isset($_POST['save'])) {
    $module->save();
    header('Location: '. $system->document->link('', array('doc' => $type), array('app')));
    exit;
  }
  
  if (isset($_POST['uninstall'])) {
    $module->uninstall();
    header('Location: '. $system->document->link('', array('doc' => $type), array('app')));
    exit;
  }
?>

<h1 style="margin-top: 0;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_edit_module', 'Edit Module'); ?></h1>
<h2 style="margin-top: 0;"><?php echo $module->name; ?></h2>

<table width="100%" align="center">
  <tr>
    <td><?php echo $system->language->translate('title_author', 'Author'); ?></td>
    <td><?php echo $module->author; ?></td>
  </tr>
</table>

<?php echo isset($module->description) ? '<p>'. $module->description .'</p>': false; ?>
<?php echo $system->functions->form_draw_form_begin('module_form', 'post'); ?>
<table width="100%" align="center">
<?php
  foreach ($module->settings as $setting) {
?>
  <tr>
    <td align="left"><strong><?php echo $setting['title']; ?></strong><?php echo !empty($setting['description']) ? '<br />' . $setting['description'] : false; ?><br />
    <?php echo $system->functions->form_draw_hidden_field('key', $setting['key']) . $system->functions->form_draw_function($setting['function'], $setting['key'], $setting['value']); ?></td>
  </tr>
<?php 
  }
?>
</table>
<?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="location=\''. $system->document->link('', array('doc' => 'modules'), true, array('module_id')) .'\'"', 'cancel'); ?> <?php echo $system->functions->form_draw_button('uninstall', $system->language->translate('title_uninstall', 'Uninstall'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete'); ?>
<?php echo $system->functions->form_draw_form_end(); ?>
<p><a href="<?php echo $system->document->href_link('', array('app' => 'translations', 'doc' => 'search', 'query' => $module_id)); ?>"><?php echo $system->language->translate('title_edit_translations', 'Edit Translations'); ?></a></p>