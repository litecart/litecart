<?php

  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {
  
    if (!empty($_POST['manufacturers'])) {
      foreach ($_POST['manufacturers'] as $key => $value) $_POST['manufacturers'][$key] = $system->database->input($value);
      $system->database->query(
        "update ". DB_TABLE_MANUFACTURERS ."
        set status = '". ((!empty($_POST['enable'])) ? 1 : 0) ."'
        where id in ('". implode("', '", $_POST['manufacturers']) ."');"
      );
    }
    
    header('Location: '. $system->document->link());
    exit;
  }
  
?>

<div style="float: right;"><a class="button" href="<?php echo $system->document->href_link('', array('app' => $_GET['app'], 'doc' => 'edit_manufacturer.php')); ?>"><?php echo $system->language->translate('title_add_new_manufacturer', 'Add New Manufacturer'); ?></a></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo $system->language->translate('title_manufacturers', 'Manufacturers'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('manufacturers_form', 'post'); ?>
<table cellpadding="5" cellspacing="0" border="0" class="dataTable" width="100%">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th width="100%" align="left"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th align="center"><?php echo $system->language->translate('title_products', 'Products'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php
    $manufacturers_query = $system->database->query(
      "select * from ". DB_TABLE_MANUFACTURERS ."
      order by name asc;"
    );
    
    if ($system->database->num_rows($manufacturers_query) > 0) {
      while ($manufacturer = $system->database->fetch($manufacturers_query)) {
        if (!isset($rowclass) || $rowclass == 'even') {
          $rowclass = 'odd';
        } else {
          $rowclass = 'even';
        }
        
        $num_products = $system->database->num_rows($system->database->query("select id from ". DB_TABLE_PRODUCTS ." where manufacturer_id = ". (int)$manufacturer['id'] .";"));
        
        echo '<tr class="'. $rowclass . ($manufacturer['status'] ? false : ' semi-transparent') .'">' . PHP_EOL
           . '  <td nowrap="nowrap"><img src="'. WS_DIR_IMAGES .'icons/16x16/'. (!empty($manufacturer['status']) ? 'on' : 'off') .'.png" width="16" height="16" border="0" align="absbottom" /> '. $system->functions->form_draw_checkbox('manufacturers['. $manufacturer['id'] .']', $manufacturer['id']) .'</td>' . PHP_EOL
           . '  <td nowrap="nowrap"><img src="'. (($manufacturer['image']) ?  $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 16, 16, 'FIT_USE_WHITESPACING') : WS_DIR_IMAGES .'no_image.png') .'" width="16" height="16" align="absbottom" /> '. $manufacturer['name'] .'</td>' . PHP_EOL
           . '  <td nowrap="nowrap" align="right">'. (int)$num_products .'</td>' . PHP_EOL
           . '  <td nowrap="nowrap"><a href="'. $system->document->href_link('', array('app' => $_GET['app'], 'doc' => 'edit_manufacturer.php', 'manufacturer_id' => $manufacturer['id'])) .'"><img src="'. WS_DIR_IMAGES .'icons/16x16/edit.png" width="16" height="16" border="0" align="absbottom" /></a></td>' . PHP_EOL
           . '</tr>' . PHP_EOL;
      }
    }
?>
  <tr class="footer">
    <td colspan="4" align="left"><?php echo $system->language->translate('title_manufacturers', 'Manufacturers'); ?>: <?php echo $system->database->num_rows($manufacturers_query); ?></td>
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

<p><?php echo $system->functions->form_draw_button('enable', $system->language->translate('title_enable', 'Enable'), 'submit'); ?> <?php echo $system->functions->form_draw_button('disable', $system->language->translate('title_disable', 'Disable'), 'submit'); ?></p>

<?php echo $system->functions->form_draw_form_end(); ?>