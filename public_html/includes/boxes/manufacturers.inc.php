<?php
  $manufacturers_query = $system->database->query(
    "select id, name from ". DB_TABLE_MANUFACTURERS ." m
    where status
    order by name asc;"
  );
  
  if ($system->database->num_rows($manufacturers_query) == 0) return;
?>
<div class="box">
  <div class="heading"><h3><?php echo $system->language->translate('title_manufacturers', 'Manufacturers'); ?></h3></div>
  <div class="content">
  <?php
  echo $system->functions->form_draw_form_begin('manufacturers_form', 'get', $system->document->link(WS_DIR_HTTP_HOME . 'manufacturer.php'));
  
  $options = array(
    array($system->language->translate('option_select', '-- Select --'), ''),
  );
  
  while($manufacturer = $system->database->fetch($manufacturers_query)) {
    $options[] = array($manufacturer['name'], $manufacturer['id']);
  }
  
  echo $system->functions->form_draw_select_field('manufacturer_id', $options, '', false, 'style="width: 100%;" onchange="this.form.submit()"');
  echo $system->functions->form_draw_form_end();
?>
  </div>
</div>