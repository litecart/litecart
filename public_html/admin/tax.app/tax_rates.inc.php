<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_tax_rate'), true), language::translate('title_add_new_tax_rate', 'Add New Tax Rate'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_tax_rates', 'Tax Rates'); ?></h1>

<?php echo functions::form_draw_form_begin('tax_rates_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle'); ?></th>
    <th><?php echo language::translate('title_id', 'ID'); ?></th>
    <th><?php echo language::translate('title_tax_class', 'Tax Class'); ?></th>
    <th><?php echo language::translate('title_geo_zone', 'Geo Zone'); ?></th>
    <th><?php echo language::translate('title_name', 'Name'); ?></th>
    <th style="width: 100%;"><?php echo language::translate('title_description', 'Description'); ?></th>
    <th><?php echo language::translate('title_rate', 'Rate'); ?></th>
    <th><?php echo language::translate('title_type', 'Type'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php

  $tax_rates_query = database::query(
    "select tr.*, gz.name as geo_zone, tc.name as tax_class from ". DB_TABLE_TAX_RATES ." tr
    left join ". DB_TABLE_GEO_ZONES ." gz on (gz.id = tr.geo_zone_id)
    left join ". DB_TABLE_TAX_CLASSES ." tc on (tc.id = tr.tax_class_id)
    order by tc.name, gz.name, tr.name;"
  );

  if (database::num_rows($tax_rates_query) > 0) {

    if ($_GET['page'] > 1) database::seek($tax_rates_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($tax_rate = database::fetch($tax_rates_query)) {
?>
  <tr class="row">
    <td><?php echo functions::form_draw_checkbox('tax_rates['. $tax_rate['id'] .']', $tax_rate['id']); ?></td>
    <td><?php echo $tax_rate['id']; ?></td>
    <td><?php echo $tax_rate['tax_class']; ?></td>
    <td><?php echo $tax_rate['geo_zone']; ?></td>
    <td><a href="<?php echo document::href_link('', array('doc' => 'edit_tax_rate', 'tax_rate_id' => $tax_rate['id']), true); ?>"><?php echo $tax_rate['name']; ?></a></td>
    <td><?php echo $tax_rate['description']; ?></td>
    <td><?php echo language::number_format($tax_rate['rate'], 4); ?></td>
    <td><?php echo $tax_rate['type']; ?></td>
    <td style="text-align: right;"><a href="<?php echo document::href_link('', array('doc' => 'edit_tax_rate', 'tax_rate_id' => $tax_rate['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
  </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="9"><?php echo language::translate('title_tax_rates', 'Tax Rates'); ?>: <?php echo database::num_rows($tax_rates_query); ?></td>
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
  echo functions::draw_pagination(ceil(database::num_rows($tax_rates_query)/settings::get('data_table_rows_per_page')));
?>