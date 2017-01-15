<?php
  switch ($_GET['doc']) {
    case 'customer':
      $title = language::translate('title_customer_modules', 'Customer Modules');
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'customer/*.inc.php');
      $modules = new mod_customer();
      $type = 'customer';
      $edit_doc = 'edit_customer';
      break;

    case 'jobs':
      $title = language::translate('title_job_modules', 'Job Modules');
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'jobs/*.inc.php');
      $modules = new mod_jobs();
      $type = 'job';
      $edit_doc = 'edit_job';
      break;

    case 'order':
      $title = language::translate('title_order_modules', 'Order Modules');
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order/*.inc.php');
      $modules = new mod_order();
      $type = 'order';
      $edit_doc = 'edit_order';
      break;

    case 'order_total':
      $title = language::translate('title_order_total_modules', 'Order Total Modules');
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_total/*.inc.php');
      $modules = new mod_order_total();
      $type = 'order_total';
      $edit_doc = 'edit_order_total';
      break;

    case 'payment':
      $title = language::translate('title_payment_modules', 'Payment Modules');
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'payment/*.inc.php');
      $modules = new mod_payment();
      $type = 'payment';
      $edit_doc = 'edit_payment';
      break;

    case 'shipping':
      $title = language::translate('title_shipping_modules', 'Shipping Modules');
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'shipping/*.inc.php');
      $modules = new mod_shipping();
      $type = 'shipping';
      $edit_doc = 'edit_shipping';
      break;

    default:
      trigger_error('Unknown module type', E_USER_ERROR);
  }

  $installed_modules_query = database::query(
    "select * from ". DB_TABLE_MODULES ."
    where type = '". database::input($type) ."';"
  );

  $installed_modules = array();
  while($module = database::fetch($installed_modules_query)){
    $installed_modules[] = $module['module_id'];
  }
?>
<h1><?php echo $app_icon; ?> <?php echo $title; ?></h1>
<?php echo functions::form_draw_form_begin('modules_form', 'post'); ?>
  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th></th>
        <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th>&nbsp;</th>
        <th><?php echo language::translate('title_version', 'Version'); ?></th>
        <th><?php echo language::translate('title_developer', 'Developer'); ?></th>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th class="text-center"><?php echo language::translate('title_priority', 'Priority'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  $total_rows = 0;
  if (is_array($modules->modules) && count($modules->modules)) {
    foreach ($modules->modules as $module) {
?>
      <tr class="<?php echo empty($module->status) ? 'semi-transparent' : null; ?>">
        <td><?php echo functions::form_draw_checkbox('modules['. $module->id .']', $module->id); ?></td>
        <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($module->status) ? '#99cc66' : '#ff6666') .';"'); ?></td>
        <td><a href="<?php echo document::href_link('', array('doc' => 'edit_'.$type, 'module_id' => $module->id), true); ?>"><?php echo $module->name; ?></a></td>
        <?php if ($_GET['doc'] == 'jobs' && !empty($module->status)) { ?>
        <td class="text-center"><a href="<?php echo document::href_link('', array('doc' => 'run_job', 'module_id' => $module->id), array('app')); ?>" target="_blank"><strong><?php echo language::translate('title_run_now', 'Run Now'); ?></strong></a></td>
        <?php } else { ?>
        <td class="text-center"></td>
        <?php } ?>
        <td class="text-right"><?php echo $module->version; ?></td>
        <td><?php echo (!empty($module->website)) ? '<a href="'. document::link($module->website) .'" target="_blank">'. $module->author .'</a>' : $module->author; ?></td>
        <td><?php echo $module->id; ?></td>
        <td class="text-center"><?php echo $module->priority; ?></td>
        <td class="text-right"><a href="<?php echo document::href_link('', array('doc' => $edit_doc, 'module_id' => $module->id), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
      </tr>
<?php
      $total_rows++;
    }
  }

  if (!empty($files)) foreach ($files as $file) {
    $module_id = substr(basename($file), 0, -8);
    if (!in_array($module_id, $installed_modules)) {
      $module = new $module_id;
?>
      <tr class="semi-transparent">
        <td></td>
        <td></td>
        <td><?php echo $module->name; ?></td>
        <td style="text-align: center;"></td>
        <td style="text-align: right;"><?php echo $module->version; ?></td>
        <td><?php echo (!empty($module->website)) ? '<a href="'. document::link($module->website) .'" target="_blank">'. $module->author .'</a>' : $module->author; ?></td>
        <td><?php echo $module->id; ?></td>
        <td style="text-align: center;">-</td>
        <td style="text-align: right;"><a href="<?php echo document::href_link('', array('doc' => 'edit_'.$type, 'module_id' => $module->id), true); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_install', 'Install'); ?></a></td>
      </tr>
<?php
      $total_rows++;
    }
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="9"><?php echo language::translate('title_modules', 'Modules'); ?>: <?php echo $total_rows; ?></td>
      </tr>
    </tfoot>
  </table>

  <p><?php echo language::translate('title_external_link', 'External Link'); ?>: <strong><a href="http://www.litecart.net/addons" target="_blank">LiteCart Add-ons</a></strong></p>

<?php echo functions::form_draw_form_end(); ?>