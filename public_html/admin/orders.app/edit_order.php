<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'order.inc.php');
  
  if (empty($_GET['order_id'])) {
    $order = new ctrl_order('new');
  } else {
    $order = new ctrl_order('load', $_GET['order_id']);
    
  // Convert to order currency
    foreach (array_keys($order->data['items']) as $key) {
      $order->data['items'][$key]['price'] = $order->data['items'][$key]['price'] * $order->data['currency_value'];
      $order->data['items'][$key]['tax'] = $order->data['items'][$key]['tax'] * $order->data['currency_value'];
    }
    foreach (array_keys($order->data['order_total']) as $key) {
      $order->data['order_total'][$key]['value'] = $order->data['order_total'][$key]['value'] * $order->data['currency_value'];
      $order->data['order_total'][$key]['tax'] = $order->data['order_total'][$key]['tax'] * $order->data['currency_value'];
    }
  }
  
  if (empty($_POST)) {
    foreach ($order->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }
  
  // Save data to database
  if (isset($_POST['save'])) {
    
    if (empty($_POST['items'])) $_POST['items'] = array();
    if (empty($_POST['order_total'])) $_POST['order_total'] = array();
    if (empty($_POST['comments'])) $_POST['comments'] = array();
    
    if (!$system->notices->get('errors')) {
      
    // Add new items
      if (!empty($_POST['items'])) {
        
      // Add item
        foreach (array_keys($_POST['items']) as $key) {
          
          if (empty($_POST['items'][$key]['id'])) {
            
            require_once(FS_DIR_HTTP_ROOT . WS_DIR_REFERENCES . 'product.inc.php');
            $product = new ref_product($_POST['items'][$key]['product_id']);
            
            $name = array();
            foreach(array_keys($product->name) as $language_code) {
              $name[$language_code] = $product->name[$language_code];
            }
            
            $_POST['items'][$key] = array(
              'id' => '',
              'product_id' => $product->id,
              'option_stock_combination' => '',
              'options' => array(),
              'name' => $name[$system->language->selected['code']],
              'code' => $product->code,
              'sku' => $product->sku,
              'upc' => $product->upc,
              'taric' => $product->taric,
              'price' => $_POST['items'][$key]['price'] / $order->data['currency_value'],
              'tax' => $system->tax->get_tax($_POST['items'][$key]['price'] * $order->data['currency_value'], $product->tax_class_id, $order->data['customer']['country_code'], $order->data['customer']['zone_code']) / $order->data['currency_value'],
              'tax_class_id' => $product->tax_class_id,
              'quantity' => $_POST['items'][$key]['quantity'],
              'weight' => $product->weight,
              'weight_class' => $product->weight_class,
            );
          
        // Update item
          } else {
            $_POST['items'][$key]['price'] = $_POST['items'][$key]['price'] / $order->data['currency_value'];
            $_POST['items'][$key]['tax'] = $system->tax->get_tax($_POST['items'][$key]['price'] * $order->data['currency_value'], $_POST['items'][$key]['tax_class_id'], $order->data['customer']['country_code'], $order->data['customer']['zone_code']) / $order->data['currency_value'];
          }
        }
        
        foreach (array_keys($_POST['order_total']) as $key) {
          $_POST['order_total'][$key]['value'] = $_POST['order_total'][$key]['value'] / $order->data['currency_value'];
          $_POST['order_total'][$key]['tax'] = $system->tax->get_tax($_POST['order_total'][$key]['value'] * $order->data['currency_value'], $_POST['order_total'][$key]['tax_class_id'], $order->data['customer']['country_code'], $order->data['customer']['zone_code']) / $order->data['currency_value'];
        }
      }
      
      $fields = array(
        'items',
        'order_total',
        'order_status_id',
        'comments',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $order->data[$field] = $_POST[$field];
      }
      
      $fields = array(
        'id',
        'email',
        'tax_id',
        'company',
        'firstname',
        'lastname',
        'address1',
        'address2',
        'postcode',
        'city',
        'phone',
        'mobile',
        'country_code',
        'zone_code',
        'shipping_address',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST['customer'][$field])) $order->data['customer'][$field] = $_POST['customer'][$field];
      }
      
      $order->save();
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => 'orders.php')));
      exit;
    }
  }
  
  // Delete from database
  if (isset($_POST['delete']) && $order) {
    $order->delete();
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => 'orders.php')));
    exit();
  }
  
?>
<style>
.ListTable-Row-Odd {
  background-color: #ffffff;
}
.ListTable-Row-Even {
  background-color: #f9f9f9;
}
</style>

<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle;" style="margin-right: 10px;" /><?php echo !empty($order->data['id']) ? $system->language->translate('title_edit_order', 'Edit Order') .' #'. $order->data['id'] : $system->language->translate('title_create_new_order', 'Create New Order'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('form_order', 'post'); ?>

<div style="float: left; padding-right: 10px; width: 50%;">
  <h2 style="margin-top: 0px;"><?php echo $system->language->translate('title_customer_info', 'Customer Info'); ?></h2>
  <table>
    <tr>
      <td colspan="2"><?php echo $system->language->translate('title_account', 'Account'); ?><br />
        <?php echo $system->functions->form_draw_customers_list('customer[id]', isset($_POST['customer']['id']) ? $_POST['customer']['id'] : 0); ?></td>
    </tr>
    <tr>
      <td><?php echo $system->language->translate('title_company', 'Company'); ?><br />
        <?php echo $system->functions->form_draw_input_field('customer[company]', isset($_POST['customer']['company']) ? $_POST['customer']['company'] : ''); ?></td>
      <td nowrap="nowrap"><?php echo $system->language->translate('title_tax_id', 'Tax ID'); ?><br />
        <?php echo $system->functions->form_draw_input_field('customer[tax_id]', isset($_POST['customer']['tax_id']) ? $_POST['customer']['tax_id'] : ''); ?></td>
    </tr>
    <tr>
      <td><?php echo $system->language->translate('title_firstname', 'First Name'); ?><br />
        <?php echo $system->functions->form_draw_input_field('customer[firstname]', isset($_POST['customer']['firstname']) ? $_POST['customer']['firstname'] : ''); ?></td>
      <td><?php echo $system->language->translate('title_lastname', 'Last Name'); ?><br />
        <?php echo $system->functions->form_draw_input_field('customer[lastname]', isset($_POST['customer']['lastname']) ? $_POST['customer']['lastname'] : ''); ?></td>
    </tr>
    <tr>
      <td width="50%"><?php echo $system->language->translate('title_email', 'E-mail'); ?><br />
        <?php echo $system->functions->form_draw_input_field('customer[email]', isset($_POST['customer']['email']) ? $_POST['customer']['email'] : ''); ?></td>
      <td><?php echo $system->language->translate('title_phone', 'Phone'); ?><br />
      <?php echo $system->functions->form_draw_input_field('customer[phone]', isset($_POST['customer']['phone']) ? $_POST['customer']['phone'] : ''); ?></td>
    </tr>
    <tr>
      <td><?php echo $system->language->translate('title_address1', 'Address 1'); ?><br />
        <?php echo $system->functions->form_draw_input_field('customer[address1]', isset($_POST['customer']['address1']) ? $_POST['customer']['address1'] : ''); ?></td>
      <td><?php echo $system->language->translate('title_address2', 'Address 2'); ?><br />
      <?php echo $system->functions->form_draw_input_field('customer[address2]', isset($_POST['customer']['address2']) ? $_POST['customer']['address2'] : ''); ?></td>
    </tr>
    <tr>
      <td><?php echo $system->language->translate('title_city', 'City'); ?><br />
        <?php echo $system->functions->form_draw_input_field('customer[city]', isset($_POST['customer']['city']) ? $_POST['customer']['city'] : ''); ?></td>
      <td><?php echo $system->language->translate('title_postcode', 'Postcode'); ?><br />
        <?php echo $system->functions->form_draw_input_field('customer[postcode]', isset($_POST['customer']['postcode']) ? $_POST['customer']['postcode'] : ''); ?></td>
    </tr>
    <tr>
      <td><?php echo $system->language->translate('title_country', 'Country'); ?><br />
        <?php echo $system->functions->form_draw_countries_list('customer[country_code]', isset($_POST['customer']['country_code']) ? $_POST['customer']['country_code'] : ''); ?></td>
      <td><?php echo $system->language->translate('title_zone', 'Zone'); ?><br />
        <?php echo $system->functions->form_draw_zones_list(isset($_POST['customer']['country_code']) ? $_POST['customer']['country_code'] : '', 'customer[zone_code]', isset($_POST['customer']['zone_code']) ? $_POST['customer']['zone_code'] : ''); ?></td>
    </tr>
  </table>
  
  <script type="text/javascript">
    $("select[name='customer[country_code]']").change(function(){
      $('body').css('cursor', 'wait');
      $.ajax({
        url: '<?php echo WS_DIR_AJAX .'zones.json.php'; ?>?country_code=' + $(this).val(),
        type: 'get',
        cache: true,
        async: true,
        dataType: 'json',
        error: function(jqXHR, textStatus, errorThrown) {
          alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
        },
        success: function(data) {
          $('select[name=\'customer[zone_code]\']').html('');
          if ($('select[name=\'customer[zone_code]\']').attr('disabled')) $('select[name=\'customer[zone_code]\']').removeAttr('disabled');
          if (data) {
            $.each(data, function(i, zone) {
              $('select[name=\'customer[zone_code]\']').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
            });
          } else {
            $('select[name=\'customer[zone_code]\']').attr('disabled', 'disabled');
          }
        },
        complete: function() {
          $('body').css('cursor', 'auto');
        }
      });
    });
  </script>
</div>

<h2><?php echo $system->language->translate('title_shipping_address', 'Shipping Address'); ?></h2>
<table>
  <tr>
    <td><?php echo $system->language->translate('title_firstname', 'First Name'); ?><br />
      <?php echo $system->functions->form_draw_input_field('customer[shipping_address][firstname]', isset($_POST['customer']['shipping_address']['firstname']) ? $_POST['customer']['shipping_address']['firstname'] : ''); ?></td>
    <td><?php echo $system->language->translate('title_lastname', 'Last Name'); ?><br />
      <?php echo $system->functions->form_draw_input_field('customer[shipping_address][lastname]', isset($_POST['customer']['shipping_address']['lastname']) ? $_POST['customer']['shipping_address']['lastname'] : ''); ?></td>
  </tr>
  <tr>
    <td><?php echo $system->language->translate('title_address1', 'Address 1'); ?><br />
      <?php echo $system->functions->form_draw_input_field('customer[shipping_address][address1]', isset($_POST['customer']['shipping_address']['address1']) ? $_POST['customer']['shipping_address']['address1'] : ''); ?></td>
    <td><?php echo $system->language->translate('title_address2', 'Address 2'); ?><br />
    <?php echo $system->functions->form_draw_input_field('customer[shipping_address][address2]', isset($_POST['customer']['shipping_address']['address2']) ? $_POST['customer']['shipping_address']['address2'] : ''); ?></td>
  </tr>
  <tr>
    <td><?php echo $system->language->translate('title_city', 'City'); ?><br />
      <?php echo $system->functions->form_draw_input_field('customer[shipping_address][city]', isset($_POST['customer']['shipping_address']['city']) ? $_POST['customer']['shipping_address']['city'] : ''); ?></td>
    <td><?php echo $system->language->translate('title_postcode', 'Postcode'); ?><br />
      <?php echo $system->functions->form_draw_input_field('customer[shipping_address][postcode]', isset($_POST['customer']['shipping_address']['postcode']) ? $_POST['customer']['shipping_address']['postcode'] : ''); ?></td>
  </tr>
  <tr>
    <td><?php echo $system->language->translate('title_country', 'Country'); ?><br />
      <?php echo $system->functions->form_draw_countries_list('customer[shipping_address][country_code]', isset($_POST['customer']['shipping_address']['country_code']) ? $_POST['customer']['shipping_address']['country_code'] : ''); ?></td>
    <td><?php echo $system->language->translate('title_zone', 'Zone'); ?><br />
      <?php echo $system->functions->form_draw_zones_list(isset($_POST['customer[shipping_address][country_code']) ? $_POST['customer']['shipping_address']['country_code'] : '', 'customer[shipping_address][zone_code]', isset($_POST['customer']['shipping_address']['zone_code']) ? $_POST['customer']['shipping_address']['zone_code'] : ''); ?></td>
  </tr>
</table>

<br />

<h2><?php echo $system->language->translate('title_order_informations', 'Order Information'); ?></h2>
<table>
  <tr>
    <td><?php echo $system->language->translate('title_currency', 'Currency'); ?><br />
      <?php echo $order->data['currency_code']; ?></td>
    <td><?php echo $system->language->translate('title_currency_value', 'Currency Value'); ?><br />
      <?php echo $order->data['currency_value']; ?></td>
    <td><?php echo $system->language->translate('title_order_total', 'Order Total'); ?><br />
      <?php echo $system->currency->format($order->data['payment_due'], false, false, $order->data['currency_code'], $order->data['currency_value']); ?> (<?php echo $system->currency->format($order->data['payment_due'], false, false, $system->settings->get('store_currency_code'), 1); ?>)</td>
  </tr>
</table>

<script type="text/javascript">
  $("select[name='customer[shipping_country_code]']").change(function(){
    $('body').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo WS_DIR_AJAX .'zones.json.php'; ?>?country_code=' + $(this).val(),
      type: 'get',
      cache: true,
      async: true,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
      },
      success: function(data) {
        $('select[name=\'customer[shipping_zone_code]\']').html('');
        if ($('select[name=\'customer[shipping_zone_code]\']').attr('disabled')) $('select[name=\'customer[shipping_zone_code]\']').removeAttr('disabled');
        if (data) {
          $.each(data, function(i, zone) {
            $('select[name=\'customer[shipping_zone_code]\']').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name=\'customer[shipping_zone_code]]\']').attr('disabled', 'disabled');
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });
</script>

<div style="clear: both;">&nbsp;</div>

<h2><?php echo $system->language->translate('title_order_items', 'Order Items'); ?></h2>
<table width="100%" class="dataTable">
  <tr class="header">
    <th nowrap="nowrap" align="center">&nbsp;</th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_item', 'Item'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_qty', 'Qty'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_unit_price', 'Unit Price'); ?></th>
    <!--<th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_tax', 'Tax'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_tax_class', 'Tax Class'); ?></th>-->
    <th nowrap="nowrap">&nbsp;</th>
  </tr>
<?php
  foreach (array_keys($_POST['items']) as $key) {
?>
  <tr class="">
    <td nowrap="nowrap" align="center"><a href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" class="add_item" title="<?php echo $system->language->translate('text_insert_before', 'Insert before'); ?>" /></a></td>
    <td nowrap="nowrap" align="left">
<?php
    foreach (array_keys($_POST['items'][$key]) as $field) {
      if (is_array($_POST['items'][$key][$field])) continue;
      echo $system->functions->form_draw_hidden_field('items['.$key.']['.$field.']', $_POST['items'][$key][$field]);
    }
?>
    <a href="<?php echo $system->document->href_link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $_POST['items'][$key]['product_id'])); ?>" target="_blank"><?php echo $_POST['items'][$key]['name']; ?></a></td>
    <td nowrap="nowrap" align="center"><?php echo $system->functions->form_draw_input_field('items['. $key .'][quantity]', $_POST['items'][$key]['quantity'], 'text', 'style="width: 25px; text-align: right;"'); ?></td>
    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'items['. $key .'][price]', $_POST['items'][$key]['price']); ?></td>
    <!--<td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'items['. $key .'][tax]', $_POST['items'][$key]['tax']); ?></td>
    <td nowrap="nowrap" align="right"><?php echo $system->tax->get_class_name($_POST['items'][$key]['tax_class_id']); ?></td>-->
    <td nowrap="nowrap"><a href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" class="remove_item" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>
  </tr>
<?php
  }
?>
  <tr>
    <td nowrap="nowrap" align="left" colspan="7"><a href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" class="add_item" title="<?php echo $system->language->translate('title_insert', 'Insert'); ?>" /></a></td>
  </tr>
</table>

<script type="text/javascript">

  function calculate_total() {
    var subtotal = 0;
    $("input[type='text'][name$='[price]']").each(function() {
      subtotal += $(this).val() * $(this).closest('tr').find("input[type='text'][name$='[quantity]']").val();
    });
    $("input[type=text][name^='order_total['][value='ot_subtotal']").closest('tr').find("input[type='text'][name$='[value]']").val(subtotal);
  }
  
  $("input[type='text'][name^='items['][name$='[price]'], #remove_item").keyup(function() {
    calculate_total();
  });

  var new_item_index = 0;
  $(".add_item").live("click", function(event) {
    while ($("input[name='items["+new_item_index+"][id]']").length) new_item_index++;
    event.preventDefault();
    var new_row = '  <tr>'
                + '    <td nowrap="nowrap" align="center"><a href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" class="add_item" title="<?php echo $system->language->translate('text_insert_before', 'Insert before'); ?>" /></a></td>'
                + '    <td nowrap="nowrap" align="left"><?php echo $system->functions->form_draw_hidden_field('items[new_item_index][id]', ''); ?><?php echo str_replace(array("\r", "\n"), '', $system->functions->form_draw_products_list('items[new_item_index][product_id]', '', 'style="width: 350px; text-align: left;"')); ?></td>'
                + '    <td nowrap="nowrap" align="center"><?php echo $system->functions->form_draw_input_field('items[new_item_index][quantity]', '1', 'text', 'style="width: 25px; text-align: right;"'); ?></td>'
                + '    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'items[new_item_index][price]', 0); ?></td>'
                //+ '    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'items[new_item_index][tax]', 0); ?></td>'
                //+ '    <td nowrap="nowrap" align="right">&nbsp;</td>'
                + '    <td nowrap="nowrap"><a href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" class="remove_item" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>'
                + '  </tr>';
    new_row = new_row.replace(/new_item_index/g, 'new_' + new_item_index);
    $(this).closest("tr").before(new_row);
    new_item_index++;
  });
  
  $(".add_item").live("click", function(event) {
    $.fancybox({
      content: 'it works!'
    });
  });
  
  $(".remove_item").live("click", function(event) {
    event.preventDefault();
    $(this).closest("tr").remove();
    calculate_total();
  });
</script>

<h2><?php echo $system->language->translate('title_order_total', 'Order Total'); ?></h2>
<table width="100%" class="dataTable">
  <tr class="header">
    <th nowrap="nowrap" align="left">&nbsp;</th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_module_id', 'Module ID'); ?></th>
    <th nowrap="nowrap" align="center" width="100%"><?php echo $system->language->translate('title_title', 'Title'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_value', 'Value'); ?></th>
    <!--<th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_tax', 'Tax'); ?></th>-->
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_tax_class', 'Tax Class'); ?></th>
    <th nowrap="nowrap">&nbsp;</th>
  </tr>
<?php
  foreach (array_keys($_POST['order_total']) as $key) {
?>
  <tr>
    <td nowrap="nowrap" align="right"><a href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" class="add_ot_row" title="<?php echo $system->language->translate('text_insert_before', 'Insert before'); ?>" /></a></td>
    <td nowrap="nowrap" align="right"><?php foreach (array_keys($_POST['order_total'][$key]) as $field) echo $system->functions->form_draw_hidden_field('order_total['. $key .']['. $field .']', $_POST['order_total'][$key][$field]); ?><?php echo $system->functions->form_draw_input_field('order_total['. $key .'][module_id]', $_POST['order_total'][$key]['module_id'], 'text', 'style="width: 75px;"'); ?></td>
    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_input_field('order_total['. $key .'][title]', $_POST['order_total'][$key]['title'], 'text', 'style="width: 200px; text-align: right;"'); ?> :</td>
    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'order_total['. $key .'][value]', $_POST['order_total'][$key]['value']); ?><?php echo $system->functions->form_draw_checkbox('order_total['. $key .'][calculate]', 'true', !empty($_POST['order_total'][$key]['calculate']) ? 'true' : '', '', $system->language->translate('title_calculate', 'Calculate')); ?></td>
    <!--<td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'order_total['. $key .'][tax]', $_POST['order_total'][$key]['tax']); ?></td>-->
    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_function('tax_classes()', 'order_total['. $key .'][tax_class_id]', $_POST['order_total'][$key]['tax_class_id']); ?></td>
    <td nowrap="nowrap"><a href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" class="remove_ot_row" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>
  </tr>
<?php
  }
?>
  <tr>
    <td colspan="7" nowrap="nowrap" align="left"><a href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" class="add_ot_row" title="<?php echo $system->language->translate('title_insert_', 'Insert'); ?>" /></a></td>
  </tr>
</table>
<script type="text/javascript">
  var new_ot_row_index = 0;
  $(".add_ot_row").live("click", function(event) {
    while ($("input[name='order_total["+new_ot_row_index+"][id]']").length) new_ot_row_index++;
    event.preventDefault();
    var output = '  <tr>'
               + '    <td nowrap="nowrap" align="right"><a href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" class="add_ot_row" title="<?php echo $system->language->translate('text_insert_before', 'Insert before'); ?>" /></a></td>'
               + '    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_hidden_field('order_total[new_ot_row_index][id]', ''); ?><?php echo $system->functions->form_draw_input_field('order_total[new_ot_row_index][module_id]', '', 'text', 'style="width: 75px;"'); ?></td>'
               + '    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_input_field('order_total[new_ot_row_index][title]', '', 'text', 'style="width: 200px; text-align: right;"'); ?> :</td>'
               //+ '    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'order_total[new_ot_row_index][tax]', $system->currency->format(0, false, true), 'text', 'style="width: 75px; text-align: right;"'); ?><?php echo $system->functions->form_draw_checkbox('order_total[new_ot_row_index][calculate]', 'true', 'true', '', $system->language->translate('title_calculate', 'Calculate')); ?></td>'
               + '    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'order_total[new_ot_row_index][value]', $system->currency->format(0, false, true), 'text', 'style="width: 75px; text-align: right;"'); ?></td>'
               + '    <td nowrap="nowrap" align="right"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_function('tax_classes()', 'order_total[new_ot_row_index][tax_class_id]', '', 'text', 'style="width: 75px; text-align: right;"')); ?></td>'
               + '    <td nowrap="nowrap"><a href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" class="remove_ot_row" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>'
               + '  </tr>';
	output = output.replace(/new_ot_row_index/g, 'new_' + new_ot_row_index);
	$(this).closest("tr").before(output);
	new_ot_row_index++;
  });
  
  $(".remove_ot_row").live("click", function(event) {
    event.preventDefault();
	$(this).closest("tr").remove();
  });
</script>


<h2><?php echo $system->language->translate('title_comments', 'Comments'); ?></h2>
<table class="dataTable">
  <tr class="header">
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_date', 'Date'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_comment', 'Comment'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_hidden', 'Hidden'); ?></th>
    <th nowrap="nowrap">&nbsp;</th>
  </tr>
<?php
  foreach (array_keys($_POST['comments']) as $key) {
?>
  <tr>
    <td nowrap="nowrap" align="left"><?php foreach (array_keys($_POST['comments'][$key]) as $field) echo $system->functions->form_draw_hidden_field('comments['. $key .']['. $field .']', $_POST['comments'][$key][$field]); ?><?php echo strftime($system->language->selected['format_datetime'], strtotime($_POST['comments'][$key]['date_created'])); ?></td>
    <td nowrap="nowrap" align="left"><?php echo nl2br($_POST['comments'][$key]['text']); ?></td>
    <td nowrap="nowrap" align="left"><?php echo !empty($_POST['comments'][$key]['hidden']) ? 'x' : '-'; ?></td>
    <td nowrap="nowrap"><a href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" class="remove_comment" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>
  </tr>
<?php
  }
?>
  <tr>
    <td nowrap="nowrap" align="left" colspan="4"><a href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" class="add_comment" title="<?php echo $system->language->translate('title_insert_', 'Insert'); ?>" /></a></td>
  </tr>
</table>
<script type="text/javascript">
  var new_comment_index = 0;
  $(".add_comment").live("click", function(event) {
    while ($("input[name='comments["+new_comment_index+"][id]']").length) new_comment_index++;
    event.preventDefault();
    var output = '  <tr>'
               + '    <td nowrap="nowrap" align="left"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_hidden_field('comments[new_comment_index][id]', '') . $system->functions->form_draw_hidden_field('comments[new_comment_index][date_created]', strftime($system->language->selected['format_datetime'])) . strftime($system->language->selected['format_datetime'])); ?></td>'
               + '    <td nowrap="nowrap" align="left"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_textarea('comments[new_comment_index][text]', '')); ?></td>'
               + '    <td nowrap="nowrap" align="left"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_checkbox('comments[new_comment_index][hidden]', '1', '', $system->language->translate('title_hidden', 'Hidden'))); ?></td>'
               + '    <td nowrap="nowrap"><a href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" class="remove_comment" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>'
               + '  </tr>';
	output = output.replace(/new_comment_index/g, 'new_' + new_comment_index);
	$(this).closest("tr").before(output);
	new_comment_index++;
  });
  
  $(".remove_comment").live("click", function(event) {
    event.preventDefault();
	$(this).closest("tr").remove();
  });
</script>

<p><strong><?php echo $system->language->translate('title_order_status', 'Order Status'); ?>:</strong> <?php echo $system->functions->form_draw_order_status_list('order_status_id', isset($_POST['order_status_id']) ? $_POST['order_status_id'] : ''); ?></p>

<p align="right"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"'); ?> <?php echo (isset($order->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"') : false; ?></p>

<?php echo $system->functions->form_draw_form_end(); ?>