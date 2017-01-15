<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_geo_zone'), true, array('geo_zone_id')), language::translate('title_add_new_geo_zone', 'Add New Geo Zone'), '', 'add'); ?></li>
</ul>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_geo_zones', 'Geo Zones'); ?></h1>

<?php echo functions::form_draw_form_begin('geo_zones_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th><?php echo language::translate('title_zones', 'Zones'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
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
    <tr>
      <td><?php echo functions::form_draw_checkbox('geo_zones['. $geo_zone['id'] .']', $geo_zone['id']); ?></td>
      <td><?php echo $geo_zone['id']; ?></td>
      <td><a href="<?php echo document::href_link('', array('doc' => 'edit_geo_zone', 'geo_zone_id' => $geo_zone['id']), true); ?>"><?php echo $geo_zone['name']; ?></a></td>
      <td class="text-center"><?php echo database::num_rows(database::query("select id from ". DB_TABLE_ZONES_TO_GEO_ZONES ." where geo_zone_id = '". (int)$geo_zone['id'] ."'")); ?></td>
      <td class="text-right"><a href="<?php echo document::href_link('', array('doc' => 'edit_geo_zone', 'geo_zone_id' => $geo_zone['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
    </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5"><?php echo language::translate('title_geo_zones', 'Geo Zones'); ?>: <?php echo database::num_rows($geo_zones_query); ?></td>
      </tr>
    </tfoot>
  </table>

<?php echo functions::form_draw_form_end(); ?>

<?php echo functions::draw_pagination(ceil(database::num_rows($geo_zones_query)/settings::get('data_table_rows_per_page'))); ?>