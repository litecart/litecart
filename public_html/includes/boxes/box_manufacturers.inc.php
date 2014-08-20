<?php
  if (!in_array(link::relpath($_SERVER['SCRIPT_NAME']), array('index.php', 'categories.php', 'manufacturers.php', 'product.php', 'search.php'))) return;
  
  $box_manufacturers_list_cache_id = cache::cache_id('box_manufacturers_list', array('language'));
  if (cache::capture($box_manufacturers_list_cache_id, 'file')) {

    $manufacturers_query = database::query(
      "select id, name from ". DB_TABLE_MANUFACTURERS ." m
      where status
      order by name asc;"
    );
    
    if (database::num_rows($manufacturers_query)) {
?>
<div id="box-manufacturers-list" class="box">
  <div class="heading"><h3><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h3></div>
  <div class="content">
  <?php
  echo functions::form_draw_form_begin('manufacturers_form', 'get', document::ilink('manufacturer'));
  
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
<?php
    }
    
    cache::end_capture($box_manufacturers_list_cache_id);
  }
?>
