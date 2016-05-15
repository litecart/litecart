<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_geo_zone'), true, array('geo_zone_id')), language::translate('title_add_new_geo_zone', 'Add New Geo Zone'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_geo_zones', 'Geo Zones'); ?></h1>

<?php echo functions::form_draw_form_begin('geo_zones_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle'); ?></th>
    <th><?php echo language::translate('title_id', 'ID'); ?></th>
    <th width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
    <th><?php echo language::translate('title_zones', 'Zones'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php

  $geo_zones_query = database::query(
    "select * from ". DB_TABLE_GEO_ZONES ."
    order by name asc;"
  );

  if (database::num_rows($geo_zones_query) > 0) {

    if ($_GET['page'] > 1) database::seek($geo_zones_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($geo_zone = database::fetch($geo_zones_query)) {
?>
  <tr class="row">
    <td><?php echo functions::form_draw_checkbox('geo_zones['. $geo_zone['id'] .']', $geo_zone['id']); ?></td>
    <td><?php echo $geo_zone['id']; ?></td>
    <td><a href="<?php echo document::href_link('', array('doc' => 'edit_geo_zone', 'geo_zone_id' => $geo_zone['id']), true); ?>"><?php echo $geo_zone['name']; ?></a></td>
    <td><?php echo database::num_rows(database::query("select id from ". DB_TABLE_ZONES_TO_GEO_ZONES ." where geo_zone_id = '". (int)$geo_zone['id'] ."'")); ?></td>
    <td style="text-align: right;"><a href="<?php echo document::href_link('', array('doc' => 'edit_geo_zone', 'geo_zone_id' => $geo_zone['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
  </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="5"><?php echo language::translate('title_geo_zones', 'Geo Zones'); ?>: <?php echo database::num_rows($geo_zones_query); ?></td>
  </tr>
</table>
<script>
  $(".dataTable .checkbox-toggle").click(function() {
    $(this).closest("form").find(":checkbox").each(function() {
      $(this).attr('checked', !$(this).attr('checked'));
    });
    $(".dataTable .checkbox-toggle").attr("checked", true);
  });

  $('.dataTable tr').click(function(event) {
    if ($(event.target).is('input:checkbox')) return;
    if ($(event.target).is('a, a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });
</script>
<?php
  echo functions::form_draw_form_end();

// Display page links
  echo functions::draw_pagination(ceil(database::num_rows($geo_zones_query)/settings::get('data_table_rows_per_page')));
?>