<?php

  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {
  
    if (!empty($_POST['designers'])) {
      foreach ($_POST['designers'] as $key => $value) $_POST['designers'][$key] = $system->database->input($value);
      $system->database->query(
        "update ". DB_TABLE_DESIGNERS ."
        set status = '". ((!empty($_POST['enable'])) ? 1 : 0) ."'
        where id in ('". implode("', '", $_POST['designers']) ."');"
      );
    }
    
    header('Location: '. $system->document->link());
    exit;
  }
  
?>

<div style="float: right;"><a class="button" href="<?php echo $system->document->href_link('', array('app' => $_GET['app'], 'doc' => 'edit_designer.php')); ?>"><?php echo $system->language->translate('title_add_new_designer', 'Add New Designer'); ?></a></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle;" style="margin-right: 10px;" /><?php echo $system->language->translate('title_designers', 'Designers'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('designers_form', 'post'); ?>
<table class="dataTable" width="100%">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th width="100%" align="left"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php
    $designers_query = $system->database->query(
      "select * from ". DB_TABLE_DESIGNERS ."
      order by name asc;"
    );
    
    if ($system->database->num_rows($designers_query) > 0) {
      while ($designer = $system->database->fetch($designers_query)) {
        if (!isset($rowclass) || $rowclass == 'even') {
          $rowclass = 'odd';
        } else {
          $rowclass = 'even';
        }
        echo '<tr class="'. $rowclass . ($designer['status'] ? false : ' semi-transparent') .'">' . PHP_EOL
           . '  <td nowrap="nowrap"><img src="'. WS_DIR_IMAGES .'icons/16x16/'. (!empty($designer['status']) ? 'on' : 'off') .'.png" width="16" height="16" align="absbottom" /> '. $system->functions->form_draw_checkbox('designers['. $designer['id'] .']', $designer['id']) .'</td>' . PHP_EOL
           . '  <td nowrap="nowrap"><img src="'. (($designer['image']) ?  $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $designer['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 16, 16, 'FIT_USE_WHITESPACING') : WS_DIR_IMAGES .'no_image.png') .'" width="16" height="16" align="absbottom" /> '. $designer['name'] .'</td>' . PHP_EOL
           . '  <td nowrap="nowrap"> <a href="'. $system->document->href_link('', array('app' => $_GET['app'], 'doc' => 'edit_designer.php', 'designer_id' => $designer['id'])) .'"><img src="'. WS_DIR_IMAGES .'icons/16x16/edit.png" width="16" height="16" align="absbottom" /></a></td>' . PHP_EOL
           . '</tr>' . PHP_EOL;
      }
    }
?>
  <tr class="footer">
    <td colspan="3" align="left"><?php echo $system->language->translate('title_designers', 'Designers'); ?>: <?php echo $system->database->num_rows($designers_query); ?></td>
  </tr>
</table>

<script type="text/javascript">
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

<p><?php echo $system->functions->form_draw_button('enable', $system->language->translate('title_enable', 'Enable'), 'submit'); ?> <?php echo $system->functions->form_draw_button('disable', $system->language->translate('title_disable', 'Disable'), 'submit'); ?></p>

<?php echo $system->functions->form_draw_form_end(); ?>