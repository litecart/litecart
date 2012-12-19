<?php
  if (!isset($_GET['module_id']) || $_GET['module_id'] == '') die('Unknown module id');
  
  $module_id = basename($_GET['module_id']);
  
  switch ($_GET['type']) {
    case 'shipping':
      break;
    case 'payment':
      break;
    case 'order_total':
      break;
    case 'order_success':
      break;
    case 'jobs':
      break;
    default:
      die('Unknown module type');
  }
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'module.inc.php');
  $module = new ctrl_module(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . $_GET['type'] . '/' . $module_id .'.inc.php');
  
  if (isset($_POST['save'])) {
    $module->save($_POST);
    header('Location: '. $system->document->link('', array('doc' => 'modules.php'), array('app', 'type')));
    exit;
  }
  
  if (isset($_POST['uninstall'])) {
    $module->uninstall($_POST);
    header('Location: '. $system->document->link('', array('doc' => 'modules.php'), array('app', 'type')));
    exit;
  }
?>

<h1 style="margin-top: 0;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle;" style="margin-right: 10px;" /><?php echo $system->language->translate('title_edit_module', 'Edit Module'); ?></h1>
<h2 style="margin-top: 0;"><?php echo $module->name; ?></h2>
<?php echo isset($module->description) ? '<p>'. $module->description .'</p>': false; ?>
<?php echo $system->functions->form_draw_form_begin('module_form', 'post'); ?>
<table width="100%" align="center" class="ListTable">
<?php
  foreach ($module->settings as $setting) {
    if (!isset($rowclass) || $rowclass == 'ListTable-Row-Even') {
      $rowclass = 'ListTable-Row-Odd';
    } else {
      $rowclass = 'ListTable-Row-Even';
    }
?>
  <tr class="<?=$rowclass?>-Hover">
    <td align="left"><strong><?=$setting['title']?></strong><? echo !empty($setting['description']) ? '<br />' . $setting['description'] : false; ?><br />
    <?php echo $system->functions->form_draw_hidden_field('key', $setting['key']) . $system->functions->form_draw_function($setting['function'], $setting['key'], $setting['value']); ?></td>
  </tr>
<?php 
  }
?>
</table>
<?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'disk'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="location=\''. $system->document->link('', array('doc' => 'modules.php'), true, array('module_id')) .'\'"'); ?> <?php echo $system->functions->form_draw_button('uninstall', $system->language->translate('title_uninstall', 'Uninstall'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'disk'); ?>
<?php echo $system->functions->form_draw_form_end(); ?>