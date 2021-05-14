<?php

  switch ($_GET['doc']) {
    case 'customer':
      $title = language::translate('title_customer_modules', 'Customer Modules');
      $files = glob(FS_DIR_APP . 'includes/modules/customer/*.inc.php');
      $mod_class = new mod_customer();
      $type = 'customer';
      $edit_doc = 'edit_customer';
      break;

    case 'jobs':
      $title = language::translate('title_job_modules', 'Job Modules');
      $files = glob(FS_DIR_APP . 'includes/modules/jobs/*.inc.php');
      $mod_class = new mod_jobs();
      $type = 'job';
      $edit_doc = 'edit_job';
      break;

    case 'order':
      $title = language::translate('title_order_modules', 'Order Modules');
      $files = glob(FS_DIR_APP . 'includes/modules/order/*.inc.php');
      $mod_class = new mod_order();
      $type = 'order';
      $edit_doc = 'edit_order';
      break;

    case 'order_total':
      $title = language::translate('title_order_total_modules', 'Order Total Modules');
      $files = glob(FS_DIR_APP . 'includes/modules/order_total/*.inc.php');
      $mod_class = new mod_order_total();
      $type = 'order_total';
      $edit_doc = 'edit_order_total';
      break;

    case 'payment':
      $title = language::translate('title_payment_modules', 'Payment Modules');
      $files = glob(FS_DIR_APP . 'includes/modules/payment/*.inc.php');
      $mod_class = new mod_payment();
      $type = 'payment';
      $edit_doc = 'edit_payment';
      break;

    case 'shipping':
      $title = language::translate('title_shipping_modules', 'Shipping Modules');
      $files = glob(FS_DIR_APP . 'includes/modules/shipping/*.inc.php');
      $mod_class = new mod_shipping();
      $type = 'shipping';
      $edit_doc = 'edit_shipping';
      break;

    default:
      trigger_error('Unknown module type', E_USER_ERROR);
  }

  document::$snippets['title'][] = $title;

  breadcrumbs::add(language::translate('title_modules', 'Modules'));
  breadcrumbs::add($title);

// Installed Modules
  $installed_modules_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."modules
    where type = '". database::input($type) ."';"
  );

  $installed_modules = [];
  while ($module = database::fetch($installed_modules_query)) {
    $installed_modules[] = $module['module_id'];
  }

// Table Rows
  $modules = [];

  if (is_array($mod_class->modules) && count($mod_class->modules)) {
    foreach ($mod_class->modules as $module) {
      $modules[] = [
        'id' => $module->id,
        'status' => $module->status,
        'name' => $module->name,
        'version' => $module->version,
        'priority' => $module->priority,
        'author' => $module->author,
        'website' => $module->website,
        'installed' => true,
      ];
    }
  }

  foreach ($files as $file) {
    $module_id = substr(basename($file), 0, -8);
    if (in_array($module_id, $installed_modules)) continue;

    $module = new $module_id;

    $modules[] = [
      'id' => $module_id,
      'status' => null,
      'name' => $module->name,
      'version' => $module->version,
      'priority' => null,
      'author' => $module->author,
      'website' => $module->website,
      'installed' => false,
    ];
  }

// Number of Rows
  $num_rows = count($modules);
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo $title; ?>
  </div>

  <div class="panel-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button('https://www.litecart.net/addons/', 'LiteCart Add-Ons', 'target="_blank"', 'add'); ?></li>
    </ul>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('modules_form', 'post'); ?>
      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
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
          <?php foreach ($modules as $module) { ?>
          <?php if (!empty($module['installed'])) { ?>
          <tr class="<?php echo empty($module['status']) ? 'semi-transparent' : ''; ?>">
            <td><?php echo functions::form_draw_checkbox('modules['. $module['id'] .']', $module['id']); ?></td>
            <td><?php echo functions::draw_fonticon($module['status'] ? 'on' : 'off'); ?></td>
            <td><a href="<?php echo document::href_link('', ['doc' => 'edit_'.$type, 'module_id' => $module['id']], true); ?>"><?php echo $module['name']; ?></a></td>
            <?php if ($_GET['doc'] == 'jobs' && !empty($module['status'])) { ?>
            <td class="text-center"><a href="<?php echo document::href_link('', ['doc' => 'run_job', 'module_id' => $module['id']], ['app']); ?>" target="_blank"><strong><?php echo language::translate('title_run_now', 'Run Now'); ?></strong></a></td>
            <?php } else { ?>
            <td class="text-center"></td>
            <?php } ?>
            <td class="text-right"><?php echo $module['version']; ?></td>
            <td><?php echo (!empty($module['website'])) ? '<a href="'. document::link($module['website']) .'" target="_blank">'. $module['author'] .'</a>' : $module['author']; ?></td>
            <td><?php echo $module['id']; ?></td>
            <td class="text-center"><?php echo $module['priority']; ?></td>
            <td class="text-right"><a href="<?php echo document::href_link('', ['doc' => $edit_doc, 'module_id' => $module['id']], true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
          </tr>
          <?php } else { ?>
          <tr class="semi-transparent">
            <td></td>
            <td></td>
            <td><?php echo $module['name']; ?></td>
            <td class="text-center"></td>
            <td class="text-right"><?php echo $module['version']; ?></td>
            <td><?php echo (!empty($module['website'])) ? '<a href="'. document::link($module['website']) .'" target="_blank">'. $module['author'] .'</a>' : $module['author']; ?></td>
            <td><?php echo $module['id']; ?></td>
            <td class="text-center">-</td>
            <td class="text-right"><a href="<?php echo document::href_link('', ['doc' => 'edit_'.$type, 'module_id' => $module['id']], true); ?>"><?php echo functions::draw_fonticon('add'); ?> <?php echo language::translate('title_install', 'Install'); ?></a></td>
          </tr>
          <?php } ?>
          <?php } ?>
        </tbody>

        <tfoot>
          <tr>
            <td colspan="9"><?php echo language::translate('title_modules', 'Modules'); ?>: <?php echo $num_rows; ?></td>
          </tr>
        </tfoot>
      </table>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
