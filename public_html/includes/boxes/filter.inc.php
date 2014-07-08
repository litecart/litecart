<?php
  if (!in_array(link::relpath($_SERVER['SCRIPT_NAME']), array('category.php', 'manufacturer.php'))) return;
?>

<div id="filter">
  <?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>
  
<?php
  if (empty($_GET['manufacturer_id'])) {
    $manufacturers_query = database::query(
      "select distinct m.id, m.name from ". DB_TABLE_PRODUCTS ." p
      left join ". DB_TABLE_MANUFACTURERS ." m on m.id = p.manufacturer_id
      where p.status
      ". (!empty($_GET['category_id']) ? "and find_in_set('". (int)$_GET['category_id'] ."', categories)" : "") ."
      ;"
    );
    if (database::num_rows($manufacturers_query) > 1) {
?>
    <div id="filter-manufacturers">
      <h3 style="margin: 0px;"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h3>
      <div class="input-wrapper" style="display: block; color: inherit;">
        <ul class="list-vertical">
<?php
      while($manufacturer = database::fetch($manufacturers_query)) {
        echo '<li><label>'. functions::form_draw_checkbox('manufacturers[]', $manufacturer['id'], true) .' '. $manufacturer['name'] .'</label> <a href="'. document::link(WS_DIR_HTTP_HOME . 'manufacturer.php', array('manufacturer_id' => $manufacturer['id'])) .'">&raquo;</a></li>' . PHP_EOL;
      }
?>
        </ul>
      </div>
      <script>
        $("form[name='filter_form'] input[name='manufacturers[]']").click(function(){
          $(this).closest("form").submit();
        });
      </script>
    </div>
<?php
    }
  }
?>

<?php
  $product_groups_query = database::query(
    "select distinct product_groups from ". DB_TABLE_PRODUCTS ."
    where status
    and product_groups != ''
    ". (!empty($_GET['manufacturer_id']) ? "and manufacturer_id = '". (int)$_GET['manufacturer_id'] ."'" : "") ."
    ". (!empty($_GET['manufacturers']) ? "and (find_in_set('". implode("', manufacturer_id) or find_in_set('", database::input($_GET['manufacturers'])) ."', manufacturer_id))" : "") ."
    ". (!empty($_GET['category_id']) ? "and find_in_set('". (int)$_GET['category_id'] ."', categories)" : "") ."
    ;"
  );
  
  $product_groups = array();
  while ($product = database::fetch($product_groups_query)) {
    $sets = explode(',', $product['product_groups']);
    foreach ($sets as $set) {
      list($group_id, $value_id) = explode('-', $set);
      $product_groups[(int)$group_id][(int)$value_id] = (int)$value_id;
    }
  }
  
  $has_multiple_product_groups = false;
  if (!empty($product_groups)) {
    foreach ($product_groups as $group) {
      if (count($group) > 1) {
        $has_multiple_product_groups = true;
        break;
      }
    }
  }
  
  if ($has_multiple_product_groups) {
?>
    <div id="filter-product-groups">
<?php
    $product_groups_query = database::query(
      "select product_group_id as id, name from ". DB_TABLE_PRODUCT_GROUPS_INFO ."
      where product_group_id in ('". implode("', '", array_keys($product_groups)) ."')
      and language_code = '". database::input(language::$selected['code']) ."'
      order by name;"
    );
    
    while ($group = database::fetch($product_groups_query)) {
?>
      <div id="filter-product-group-<?php echo $group['id']; ?>">
        <h3 style="margin: 0px;"><?php echo $group['name']; ?></h3>
        <div class="input-wrapper" style="display: block; color: inherit;">
          <ul class="list-vertical">
<?php
      $product_group_values_query = database::query(
        "select product_group_value_id as id, name from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
        where product_group_value_id in ('". implode("', '", $product_groups[$group['id']]) ."')
        and language_code = '". database::input(language::$selected['code']) ."'
        order by name;"
      );
?>
<?php
      
      while ($value = database::fetch($product_group_values_query)) {
        echo '<li><label>' . functions::form_draw_checkbox('product_groups[]', $group['id'].'-'.$value['id']) .' '. $value['name'].'</label></li>' . PHP_EOL;
      }
?>
          </ul>
          <script>
            $("form[name='filter_form'] input[name='product_groups[]']").click(function(){
              $(this).closest("form").submit();
            });
          </script>
        </div>
      </div>
<?php
    }
?>
    </div>
<?php
  }
?>

  <?php echo functions::form_draw_form_end(); ?>
</div>