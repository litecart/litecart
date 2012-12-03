<?php
  if (!isset($_GET['type'])) $_GET['type'] = 'shipping';

  switch ($_GET['type']) {
    case 'shipping':
      $title = $system->language->translate('title_shipping_modules', 'Shipping Modules');
      $installed_modules = explode(';', $system->settings->get('shipping_modules'));
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'shipping/*.inc.php');
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'shipping.inc.php');
      $modules = new shipping;
      break;
    case 'payment':
      $title = $system->language->translate('title_payment_modules', 'Payment Modules');
      $installed_modules = explode(';', $system->settings->get('payment_modules'));
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'payment/*.inc.php');
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'payment.inc.php');
      $modules = new payment;
      break;
    case 'order_total':
      $title = $system->language->translate('title_order_total_modules', 'Order Total Modules');
      $installed_modules = explode(';', $system->settings->get('order_total_modules'));
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_total/*.inc.php');
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'order_total.inc.php');
      $modules = new order_total;
      break;
    case 'order_success':
      $title = $system->language->translate('title_order_success_modules', 'Order Success Modules');
      $installed_modules = explode(';', $system->settings->get('order_success_modules'));
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_success/*.inc.php');
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'order_success.inc.php');
      $modules = new order_success;
      break;
    case 'jobs':
      $title = $system->language->translate('title_job_modules', 'Job Modules');
      $installed_modules = explode(';', $system->settings->get('jobs_modules'));
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'jobs/*.inc.php');
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'jobs.inc.php');
      $modules = new jobs;
      break;
    default:
      die('Unknown module type');
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo $title; ?></h1>
<?php echo $system->functions->form_draw_form_begin('modules_form', 'post'); ?>
<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0" class="dataTable">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_priority', 'Priority'); ?></th>
    <th align="left">&nbsp;</th>
  </tr>
<?php
  $num_module_rows = 0;
  if (is_array($modules->modules) && count($modules->modules)) {
    foreach ($modules->modules as $module) {
      $num_module_rows++;
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass . (isset($module->settings['status']) && $module->settings['status'] == 'Enabled' ? false : ' semi-transparent'); ?>">
    <td nowrap="nowrap"><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/'. (isset($module->settings['status']) && $module->settings['status'] == 'Enabled' ? 'on.png' : 'off.png') ?>" width="16" height="16" border="0" align="absbottom" /> <?php echo $system->functions->form_draw_checkbox('modules['. $module->id .']', $module->id); ?></td>
    <td align="left" valign="top"><?php echo $module->name; ?></td>
    <td align="left" valign="top" nowrap="nowrap"><?php echo $module->id; ?></td>
    <td align="left" valign="top" nowrap="nowrap"><?php echo $module->settings['priority']; ?></td>
    <td align="right" valign="top" nowrap="nowrap"><a href="<?php echo $system->document->link('', array('doc' => 'edit_module.php', 'module_id' => $module->id), true); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/edit.png" width="16" height="16" border="0" align="absbottom" /></a></td>
  </tr>
<?php
    }
  }
    
  if (!empty($files)) foreach ($files as $file) {
    $module_id = substr(basename($file), 0, -8);
    if (!in_array($module_id, $installed_modules)) {
      $num_module_rows++;
      require_once($file);
      $module = new $module_id;
      
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass; ?> semi-transparent">
    <td></td>
    <td align="left" valign="top" nowrap="nowrap"><?php echo $module->name; ?></td>
    <td align="left" valign="top" nowrap="nowrap"><?php echo $module->id; ?></td>
    <td align="left" valign="top" nowrap="nowrap"></td>
    <td align="right" valign="top" nowrap="nowrap"><a href="<?php echo $system->document->link('', array('doc' => 'edit_module.php', 'module_id' => $module->id), true); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" border="0" align="absbottom" /> Install</a></td>
  </tr>
<?php
    }
  }
?>
  <tr class="footer">
    <td colspan="6" align="left"><?php echo $system->language->translate('title_modules', 'Modules'); ?>: <?php echo $num_module_rows; ?></td>
  </tr>
</table>

<script>
  $(".dataTable input[name='checkbox_toggle']").click(function() {
    $(this).closest("form").find(":checkbox").each(function() {
      $(this).attr('checked', !$(this).attr('checked'));
    });
    $(".dataTable input[name='checkbox_toggle']").attr("checked", true);
  });

  $('.dataTable tr').click(function(event) {
    if ($(event.target).is('input:checkbox')) return;
    if ($(event.target).is('a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });
</script>

<?php echo $system->functions->form_draw_form_end(); ?>