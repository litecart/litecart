<div style="float: right;"><a class="button" href="<?php echo $system->document->href_link('', array('doc' => 'edit_user.php'), true); ?>"><?php echo $system->language->translate('title_create_new_user', 'Create New User'); ?></a></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle;" style="margin-right: 10px;" /><?php echo $system->language->translate('title_users', 'Users'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('users_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_user', 'User'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php
  
  
  $users = array();
  foreach(file(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd') as $row) {
    list($user, $password) = explode(':', trim($row));
    $users[] = $user;
  }
  sort($users);

  if (count($users) > 0) {
    
    foreach ($users as $user) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td align="left"><?php echo $system->functions->form_draw_checkbox('users['. $user .']', $user); ?></td>
    <td align="left"><?php echo $user; ?></td>
    <td align="right"><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_user.php', 'user' => $user), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" alt="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" title="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" /></a></td>
  </tr>
<?php
    }
  }
?>
  <tr class="footer">
    <td colspan="3" align="left"><?php echo $system->language->translate('title_users', 'Users'); ?>: <?php echo count($users); ?></td>
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

<?php
  echo $system->functions->form_draw_form_end();
?>