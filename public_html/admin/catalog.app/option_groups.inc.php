<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc'=> 'edit_option_group'), array('app')), language::translate('title_create_new_group', 'Create New Group'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_option_groups', 'Option Groups'); ?></h1>

<?php echo functions::form_draw_form_begin('option_groups_form', 'post'); ?>
<table width="100%" class="dataTable">
  <tr class="header">
    <th><?php echo functions::form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_id', 'ID'); ?></th>
    <th width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
    <th><?php echo language::translate('title_function', 'Function'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php
  $option_groups_query = database::query(
    "select pcg.id, pcg.function, pcgi.name from ". DB_TABLE_OPTION_GROUPS ." pcg
    left join ". DB_TABLE_OPTION_GROUPS_INFO ." pcgi on (pcgi.group_id = pcg.id and pcgi.language_code = '". database::input(language::$selected['code']) ."')
    order by pcgi.name asc;"
  );
  while ($option_group = database::fetch($option_groups_query)) {
    if (!isset($rowclass) || $rowclass == 'even') {
      $rowclass = 'odd';
    } else {
      $rowclass = 'even';
    }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td><?php echo functions::form_draw_checkbox('configuration_groups['. $option_group['id'] .']', $option_group['id']); ?></td>
    <td style="text-align: center;"><?php echo $option_group['id']; ?></td>
    <td><a href="<?php echo document::href_link('', array('doc' => 'edit_option_group', 'option_group_id' => $option_group['id']), array('app')); ?>"><?php echo $option_group['name']; ?></a></td>
    <td><?php echo $option_group['function']; ?></td>
    <td><a href="<?php echo document::href_link('', array('doc' => 'edit_option_group', 'option_group_id' => $option_group['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
  </tr>
<?php
  }
?>
  <tr class="footer">
    <td colspan="5"><?php echo language::translate('title_option_groups', 'Option Groups'); ?>: <?php echo database::num_rows($option_groups_query); ?></td>
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