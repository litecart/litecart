<?php
  switch ($_GET['doc']) {
    case 'customer':
      $title = language::translate('title_customer_modules', 'Customer Modules');
      $installed_modules = explode(';', settings::get('customer_modules'));
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'customer/*.inc.php');
      $modules = new mod_customer();
      $edit_doc = 'edit_customer';
      break;
    case 'jobs':
      $title = language::translate('title_job_modules', 'Job Modules');
      $installed_modules = explode(';', settings::get('jobs_modules'));
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'jobs/*.inc.php');
      $modules = new mod_jobs();
      $edit_doc = 'edit_job';
      break;
    case 'order_action':
      $title = language::translate('title_order_action_modules', 'Order Action Modules');
      $installed_modules = explode(';', settings::get('order_action_modules'));
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_action/*.inc.php');
      $modules = new mod_order_action();
      $edit_doc = 'edit_order_action';
      break;
    case 'order_total':
      $title = language::translate('title_order_total_modules', 'Order Total Modules');
      $installed_modules = explode(';', settings::get('order_total_modules'));
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_total/*.inc.php');
      $modules = new mod_order_total();
      $edit_doc = 'edit_order_total';
      break;
    case 'payment':
      $title = language::translate('title_payment_modules', 'Payment Modules');
      $installed_modules = explode(';', settings::get('payment_modules'));
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'payment/*.inc.php');
      $modules = new mod_payment();
      $edit_doc = 'edit_payment';
      break;
    case 'order_success':
      $title = language::translate('title_order_success_modules', 'Order Success Modules');
      $installed_modules = explode(';', settings::get('order_success_modules'));
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_success/*.inc.php');
      $modules = new mod_order_success();
      $edit_doc = 'edit_order_success';
      break;
    case 'shipping':
      $title = language::translate('title_shipping_modules', 'Shipping Modules');
      $installed_modules = explode(';', settings::get('shipping_modules'));
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'shipping/*.inc.php');
      $modules = new mod_shipping();
      $edit_doc = 'edit_shipping';
      break;
    default:
      die('Unknown module type');
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $title; ?></h1>
<?php echo functions::form_draw_form_begin('modules_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo functions::form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo language::translate('title_version', 'Version'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo language::translate('title_developed_by', 'Developed By'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo language::translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo language::translate('title_priority', 'Priority'); ?></th>
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
  <tr class="<?php echo $rowclass . (!empty($module->status) ? false : ' semi-transparent'); ?>">
    <td nowrap="nowrap"><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/'. (!empty($module->status) ? 'on.png' : 'off.png') ?>" width="16" height="16" align="absbottom" /> <?php echo functions::form_draw_checkbox('modules['. $module->id .']', $module->id); ?></td>
    <td align="left"><a href="<?php echo document::href_link('', array('doc' => $edit_doc, 'module_id' => $module->id), true); ?>"><?php echo $module->name; ?></a></td>
    <td align="right" nowrap="nowrap"><?php echo $module->version; ?></td>
    <td align="left" nowrap="nowrap"><?php echo (!empty($module->website)) ? '<a href="'. document::link($module->website) .'" target="_blank">'. $module->author .'</a>' : $module->author; ?></td>
    <td align="left" nowrap="nowrap"><?php echo $module->id; ?></td>
    <td align="center" nowrap="nowrap"><?php echo $module->priority; ?></td>
    <td align="right" nowrap="nowrap"><a href="<?php echo document::href_link('', array('doc' => $edit_doc, 'module_id' => $module->id), true); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/edit.png" width="16" height="16" alt="<?php echo language::translate('title_edit', 'Edit'); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>" /></a></td>
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
    <td align="left"><?php echo $module->name; ?></td>
    <td align="right" nowrap="nowrap"><?php echo $module->version; ?></td>
    <td align="left" nowrap="nowrap"><?php echo (!empty($module->website)) ? '<a href="'. document::link($module->website) .'" target="_blank">'. $module->author .'</a>' : $module->author; ?></td>
    <td align="left" nowrap="nowrap"><?php echo $module->id; ?></td>
    <td align="center" nowrap="nowrap">-</td>
    <td align="right" nowrap="nowrap"><a href="<?php echo document::href_link('', array('doc' => $edit_doc, 'module_id' => $module->id), true); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" alt="<?php echo language::translate('title_install', 'Install'); ?>" title="<?php echo language::translate('title_install', 'Install'); ?>" /> <?php echo language::translate('title_install', 'Install'); ?></a></td>
  </tr>
<?php
    }
  }
?>
  <tr class="footer">
    <td colspan="7" align="left"><?php echo language::translate('title_modules', 'Modules'); ?>: <?php echo $num_module_rows; ?></td>
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
    if ($(event.target).is('a, a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });
</script>

<?php echo functions::form_draw_form_end(); ?>
