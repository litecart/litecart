<div style="float: right;"><?php echo $system->functions->form_draw_link_button($system->document->link('', array('doc'=> 'edit_option_group.php'), array('app')), $system->language->translate('title_create_new_group', 'Create New Group'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_option_groups', 'Option Groups'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('option_groups_form', 'post'); ?>
<table width="100%" class="dataTable">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th align="center" nowrap="nowrap"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
    <th align="left" nowrap="nowrap" width="100%"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th align="left" nowrap="nowrap"><?php echo $system->language->translate('title_function', 'Function'); ?></th>
    <th align="left" nowrap="nowrap">&nbsp;</th>
  </tr>
<?php
  $option_groups_query = $system->database->query(
    "select pcg.id, pcg.function, pcgi.name from ". DB_TABLE_OPTION_GROUPS ." pcg
    left join ". DB_TABLE_OPTION_GROUPS_INFO ." pcgi on (pcgi.group_id = pcg.id and pcgi.language_code = '". $system->database->input($system->language->selected['code']) ."')
    order by pcgi.name asc;"
  );
  while ($option_group = $system->database->fetch($option_groups_query)) {
    if (!isset($rowclass) || $rowclass == 'even') {
      $rowclass = 'odd';
    } else {
      $rowclass = 'even';
    }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td><?php echo $system->functions->form_draw_checkbox('configuration_groups['. $option_group['id'] .']', $option_group['id']); ?></td>
    <td align="center" nowrap="nowrap"><?php echo $option_group['id']; ?></td>
    <td align="left" nowrap="nowrap"><?php echo $option_group['name']; ?></td>
    <td align="left" nowrap="nowrap"><?php echo $option_group['function']; ?></td>
    <td><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_option_group.php', 'option_group_id' => $option_group['id']), true); ?>"><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/edit.png'; ?>" width="16" height="16" align="absbottom" /></a></td>
  </tr>
<?php
  }
?>
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

<?php echo $system->functions->form_draw_form_end(); ?>