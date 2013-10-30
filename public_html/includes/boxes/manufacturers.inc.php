<?php
  $manufacturers_query = database::query(
    "select id, name from ". DB_TABLE_MANUFACTURERS ." m
    where status
    order by name asc;"
  );
  
  if (database::num_rows($manufacturers_query) == 0) return;
?>
<div class="box">
  <div class="heading"><h3><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h3></div>
  <div class="content">
  <?php
  echo functions::form_draw_form_begin('manufacturers_form', 'get', document::link(WS_DIR_HTTP_HOME . 'manufacturer.php'));
  
  $options = array(
    array(language::translate('option_select', '-- Select --'), ''),
  );
  
  while($manufacturer = database::fetch($manufacturers_query)) {
    $options[] = array($manufacturer['name'], $manufacturer['id']);
  }
  
  echo functions::form_draw_select_field('manufacturer_id', $options, true, false, 'style="width: 100%;" onchange="this.form.submit()"');
  echo functions::form_draw_form_end();
?>
  </div>
</div>