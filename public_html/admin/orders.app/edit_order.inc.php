<?php
  
  if (empty($_GET['order_id'])) {
    $order = new ctrl_order('new');
  } else {
    $order = new ctrl_order('load', $_GET['order_id']);
    
  // Convert to local currency
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
      
      if (!empty($_POST['items'])) {
        foreach (array_keys($_POST['items']) as $key) {
          $_POST['items'][$key]['price'] = $_POST['items'][$key]['price'] / $order->data['currency_value'];
          $_POST['items'][$key]['tax'] = $_POST['items'][$key]['tax'] / $order->data['currency_value'];
        }
        
        foreach (array_keys($_POST['order_total']) as $key) {
          if (empty($_POST['order_total'][$key]['calculate'])) $_POST['order_total'][$key]['calculate'] = false;
          $_POST['order_total'][$key]['value'] = $_POST['order_total'][$key]['value'] / $order->data['currency_value'];
          $_POST['order_total'][$key]['tax'] = $_POST['order_total'][$key]['tax'] / $order->data['currency_value'];
        }
      }
      
      $fields = array(
        'items',
        'order_total',
        'order_status_id',
        'payment_option',
        'payment_transaction_id',
        'shipping_option',
        'shipping_tracking_id',
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
      header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => 'orders')));
      exit;
    }
  }
  
  // Delete from database
  if (isset($_POST['delete']) && $order) {
    $order->delete();
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => 'orders')));
    exit();
  }
  
?>

<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo !empty($order->data['id']) ? $system->language->translate('title_edit_order', 'Edit Order') .' #'. $order->data['id'] : $system->language->translate('title_create_new_order', 'Create New Order'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('form_order', 'post'); ?>

<h2><?php echo $system->language->translate('title_order_information', 'Order Information'); ?></h2>

<table class="dataTable">
  <tr>
    <td><?php echo $system->language->translate('title_order_total', 'Order Total'); ?><br />
      <?php echo $system->currency->format($order->data['payment_due'], false, false, $order->data['currency_code'], $order->data['currency_value']); ?> (<?php echo $system->currency->format($order->data['payment_due'], false, false, $system->settings->get('store_currency_code'), 1); ?>)</td>
    <td><?php echo $system->language->translate('title_currency_value', 'Currency Value'); ?><br />
      <?php echo $order->data['currency_value']; ?></td>
  </tr>
</table>

<table class="dataTable">
  <tr>
    <td style="vertical-align: top;">
      <h3><?php echo $system->language->translate('title_payment_information', 'Payment Information'); ?></h3>
      <table>
        <tr>
          <td><?php echo $system->language->translate('title_option_id', 'Option ID'); ?><br />
            <?php echo $system->functions->form_draw_text_field('payment_option[id]', true); ?></td>
          <td><?php echo $system->language->translate('title_name', 'Name'); ?><br />
            <?php echo $system->functions->form_draw_text_field('payment_option[name]', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_transaction_id', 'Transaction ID'); ?><br />
            <?php echo $system->functions->form_draw_text_field('payment_transaction_id', true); ?></td>
          <td></td>
        </tr>
      </table>
    </td>
    <td class="border-left" style="vertical-align: top;">
      <h3><?php echo $system->language->translate('title_shipping_information', 'Shipping Information'); ?></h3>
      <table>
        <tr>
          <td><?php echo $system->language->translate('title_option_id', 'Option ID'); ?><br />
            <?php echo $system->functions->form_draw_text_field('shipping_option[id]', true); ?></td>
          <td><?php echo $system->language->translate('title_name', 'Name'); ?><br />
            <?php echo $system->functions->form_draw_text_field('shipping_option[name]', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_weight', 'Weight'); ?><br />
            <?php echo $system->weight->format($order->data['weight_total'], $order->data['weight_class']) ?></td>
          <td><?php echo $system->language->translate('title_tracking_id', 'Tracking ID'); ?><br />
            <?php echo $system->functions->form_draw_text_field('shipping_tracking_id', true); ?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<table class="dataTable">
  <tr class="header">
    <th colspan="2"><?php echo $system->language->translate('title_customer_information', 'Customer Information'); ?></th>
  </tr>
  <tr>
    <td style="vertical-align: top;">
      <table>
        <tr>
          <td colspan="2"><?php echo $system->language->translate('title_account', 'Account'); ?><br />
            <?php echo $system->functions->form_draw_customers_list('customer[id]', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_company', 'Company'); ?><br />
            <?php echo $system->functions->form_draw_text_field('customer[company]', true); ?></td>
          <td nowrap="nowrap"><?php echo $system->language->translate('title_tax_id', 'Tax ID'); ?><br />
            <?php echo $system->functions->form_draw_text_field('customer[tax_id]', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_firstname', 'First Name'); ?><br />
            <?php echo $system->functions->form_draw_text_field('customer[firstname]', true); ?></td>
          <td><?php echo $system->language->translate('title_lastname', 'Last Name'); ?><br />
            <?php echo $system->functions->form_draw_text_field('customer[lastname]', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_address1', 'Address 1'); ?><br />
            <?php echo $system->functions->form_draw_text_field('customer[address1]', true); ?></td>
          <td><?php echo $system->language->translate('title_address2', 'Address 2'); ?><br />
          <?php echo $system->functions->form_draw_text_field('customer[address2]', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_city', 'City'); ?><br />
            <?php echo $system->functions->form_draw_text_field('customer[city]', true); ?></td>
          <td><?php echo $system->language->translate('title_postcode', 'Postcode'); ?><br />
            <?php echo $system->functions->form_draw_text_field('customer[postcode]', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_country', 'Country'); ?><br />
            <?php echo $system->functions->form_draw_countries_list('customer[country_code]', true); ?></td>
          <td><?php echo $system->language->translate('title_zone', 'Zone'); ?><br />
            <?php echo $system->functions->form_draw_zones_list(isset($_POST['customer']['country_code']) ? $_POST['customer']['country_code'] : '', 'customer[zone_code]', true); ?></td>
        </tr>
        <tr>
          <td width="50%"><?php echo $system->language->translate('title_email', 'E-mail'); ?><br />
            <?php echo $system->functions->form_draw_email_field('customer[email]', true); ?></td>
          <td><?php echo $system->language->translate('title_phone', 'Phone'); ?><br />
          <?php echo $system->functions->form_draw_text_field('customer[phone]', true); ?></td>
        </tr>
      </table>
      
      <script>
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
    </td>

    <td class="border-left" style="vertical-align: top;">
      <h3><?php echo $system->language->translate('title_shipping_address', 'Shipping Address'); ?></h3>
      <table>
        <tr>
          <td><?php echo $system->language->translate('title_company', 'Company'); ?><br />
            <?php echo $system->functions->form_draw_text_field('customer[shipping_address][company]', true); ?></td>
          <td nowrap="nowrap"></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_firstname', 'First Name'); ?><br />
            <?php echo $system->functions->form_draw_text_field('customer[shipping_address][firstname]', true); ?></td>
          <td><?php echo $system->language->translate('title_lastname', 'Last Name'); ?><br />
            <?php echo $system->functions->form_draw_text_field('customer[shipping_address][lastname]', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_address1', 'Address 1'); ?><br />
            <?php echo $system->functions->form_draw_text_field('customer[shipping_address][address1]', true); ?></td>
          <td><?php echo $system->language->translate('title_address2', 'Address 2'); ?><br />
          <?php echo $system->functions->form_draw_text_field('customer[shipping_address][address2]', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_city', 'City'); ?><br />
            <?php echo $system->functions->form_draw_text_field('customer[shipping_address][city]', true); ?></td>
          <td><?php echo $system->language->translate('title_postcode', 'Postcode'); ?><br />
            <?php echo $system->functions->form_draw_text_field('customer[shipping_address][postcode]', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_country', 'Country'); ?><br />
            <?php echo $system->functions->form_draw_countries_list('customer[shipping_address][country_code]', true); ?></td>
          <td><?php echo $system->language->translate('title_zone', 'Zone'); ?><br />
            <?php echo $system->functions->form_draw_zones_list(isset($_POST['customer']['shipping_address']['country_code']) ? $_POST['customer']['shipping_address']['country_code'] : '', 'customer[shipping_address][zone_code]', true); ?></td>
        </tr>
      </table>

      <script>
        $("select[name='customer[shipping_address][country_code]']").change(function(){
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
              $('select[name=\'customer[shipping_address][zone_code]\']').html('');
              if ($('select[name=\'customer[shipping_address][zone_code]\']').attr('disabled')) $('select[name=\'customer[shipping_address][zone_code]\']').removeAttr('disabled');
              if (data) {
                $.each(data, function(i, zone) {
                  $('select[name=\'customer[shipping_address][zone_code]\']').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
                });
              } else {
                $('select[name=\'customer[shipping_address][zone_code]]\']').attr('disabled', 'disabled');
              }
            },
            complete: function() {
              $('body').css('cursor', 'auto');
            }
          });
        });
      </script>
    </td>
  </tr>
</table>

<h2><?php echo $system->language->translate('title_order_items', 'Order Items'); ?></h2>
<table id="order-items" class="dataTable" style="width: 100%;">
  <tr class="header">
    <th nowrap="nowrap" align="left" style="width: 100%;"><?php echo $system->language->translate('title_item', 'Item'); ?></th>
    <th nowrap="nowrap" align="center" style="min-width: 50px;"><?php echo $system->language->translate('title_sku', 'SKU'); ?></th>
    <th nowrap="nowrap" align="center" style="min-width: 50px;"><?php echo $system->language->translate('title_weight', 'Weight'); ?></th>
    <th nowrap="nowrap" align="center" style="min-width: 50px;"><?php echo $system->language->translate('title_qty', 'Qty'); ?></th>
    <th nowrap="nowrap" align="center" style="min-width: 50px;"><?php echo $system->language->translate('title_unit_price', 'Unit Price'); ?></th>
    <th nowrap="nowrap" align="center" style="min-width: 50px;"><?php echo $system->language->translate('title_tax', 'Tax'); ?></th>
    <th nowrap="nowrap">&nbsp;</th>
  </tr>
<?php
  foreach (array_keys($_POST['items']) as $key) {
?>
  <tr class="item">
    <td nowrap="nowrap" align="left">
      <?php echo $system->functions->form_draw_hidden_field('items['.$key.'][id]', true); ?>
      <?php echo $system->functions->form_draw_hidden_field('items['.$key.'][name]', true); ?>
      <?php echo $system->functions->form_draw_hidden_field('items['.$key.'][product_id]', true); ?>
      <?php echo $system->functions->form_draw_hidden_field('items['.$key.'][option_stock_combination]', true); ?>
      <?php echo !empty($_POST['items'][$key]['product_id']) ? '<a href="'. $system->document->href_link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $_POST['items'][$key]['product_id'])) .'" target="_blank">'. $_POST['items'][$key]['name'] .'</a>' : $_POST['items'][$key]['name']; ?></div>
<?php
    if (!empty($_POST['items'][$key]['options'])) {
      echo '      <br />' . PHP_EOL
         . '      <table>' . PHP_EOL;
      foreach (array_keys($_POST['items'][$key]['options']) as $field) {
        echo '        <tr>' . PHP_EOL
           . '          <td style="padding-left: 10px;">'. $field .'</td>' . PHP_EOL
           . '          <td>';
        if (is_array($_POST['items'][$key]['options'][$field])) {
          foreach (array_keys($_POST['items'][$key][$field]) as $k) {
            echo $system->functions->form_draw_text_field('items['.$key.'][options]['.$field.']['.$k.']', true, !empty($_POST['items'][$key]['option_stock_combination']) ? 'readonly="readonly"' : '');
          }
        } else {
          echo $system->functions->form_draw_text_field('items['.$key.'][options]['.$field.']', true, !empty($_POST['items'][$key]['option_stock_combination']) ? 'readonly="readonly"' : '');
        }
        echo '          </td>' . PHP_EOL
           . '        </tr>' . PHP_EOL;
      }
      echo '      </table>' . PHP_EOL;
    }
?>
    </td>
    <td nowrap="nowrap" align="left"><?php echo $system->functions->form_draw_hidden_field('items['. $key .'][sku]', true); ?><?php echo $_POST['items'][$key]['sku']; ?></td>
    <td nowrap="nowrap" align="center"><?php echo $system->functions->form_draw_decimal_field('items['. $key .'][weight]', true); ?> <?php echo $system->functions->form_draw_weight_classes_list('items['. $key .'][weight_class]', true); ?></td>
    <td nowrap="nowrap" align="center"><?php echo $system->functions->form_draw_number_field('items['. $key .'][quantity]', true); ?></td>
    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'items['. $key .'][price]', true); ?></td>
    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'items['. $key .'][tax]', true); ?></td>
    <td nowrap="nowrap"><a class="remove_item" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>
  </tr>
<?php
  }
?>
  <tr class="footer">
    <td nowrap="nowrap" align="left" colspan="7"><?php echo $system->functions->form_draw_link_button('#', $system->language->translate('title_add_product', 'Add Product'), 'id="add_product"', 'add'); ?> <?php echo $system->functions->form_draw_link_button('#', $system->language->translate('title_add_custom_item', 'Add Custom Item'), 'id="add_custom_item"', 'add'); ?></td>
  </tr>
</table>

<script>
<?php
  $system->functions->draw_fancybox('#add_product', array(
    'type' => 'iframe',
    'width' => 480,
    'height' => 100,
    'href' => $system->document->link('', array('doc' => 'add_product', 'order_id' => $order->data['id']), true),
    'transitionIn' => 'fade',
    'transitionOut' => 'fade',
  ));
  
  $system->functions->draw_fancybox('#add_custom_item', array(
    'type' => 'iframe',
    'width' => 480,
    'height' => 100,
    'href' => $system->document->link('', array('doc' => 'add_custom_item', 'order_id' => $order->data['id']), true),
    'transitionIn' => 'fade',
    'transitionOut' => 'fade',
  ));
?>
  
  $("body").on("click", "a.remove_item", function(event) {
    event.preventDefault();
    $(this).closest("tr").remove();
  });
</script>

<h2><?php echo $system->language->translate('title_order_total', 'Order Total'); ?></h2>
<table width="100%" class="dataTable">
  <tr class="header">
    <th nowrap="nowrap" align="left">&nbsp;</th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_module_id', 'Module ID'); ?></th>
    <th nowrap="nowrap" align="right" width="100%"><?php echo $system->language->translate('title_title', 'Title'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_value', 'Value'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_tax', 'Tax'); ?></th>
    <th nowrap="nowrap">&nbsp;</th>
  </tr>
<?php
  if (empty($_POST['order_total'])) {
    $_POST['order_total'][] = array(
      'id' => '',
      'module_id' => 'ot_subtotal',
      'title' => $system->language->translate('title_subtotal', 'Subtotal'),
      'value' => '0',
      'tax' => '0',
      'tax_class_id' => '0',
      'calculate' => '0',
    );
  }
  foreach (array_keys($_POST['order_total']) as $key) {
    switch($_POST['order_total'][$key]['module_id']) {
      case 'ot_subtotal':
?>
  <tr>
    <td nowrap="nowrap" align="right">&nbsp;</td>
    <td nowrap="nowrap" align="right">
      <?php echo $system->functions->form_draw_hidden_field('order_total['. $key .'][id]', true); ?>
      <?php echo $system->functions->form_draw_text_field('order_total['. $key .'][module_id]', true, 'data-size="small" readonly="readonly"'); ?>
    </td>
    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_text_field('order_total['. $key .'][title]', true, 'style="text-align: right;"'); ?> :</td>
    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'order_total['. $key .'][value]', true, 'style="width: 75px; text-align: right;"'); ?><?php echo $system->functions->form_draw_checkbox('order_total['. $key .'][calculate]', '1', true, 'disabled="disabled"', $system->language->translate('title_calculate', 'Calculate')); ?></td>
    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'order_total['. $key .'][tax]', true, 'style="width: 75px; text-align: right;"'); ?></td>
    <td nowrap="nowrap">&nbsp;</td>
  </tr>
<?php
        break;
      default:
?>
  <tr>
    <td nowrap="nowrap" align="right"><a href="#" class="add_ot_row"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" title="<?php echo $system->language->translate('text_insert_before', 'Insert before'); ?>" /></a></td>
    <td nowrap="nowrap" align="right">
      <?php echo $system->functions->form_draw_hidden_field('order_total['. $key .'][id]', true); ?>
      <?php echo $system->functions->form_draw_text_field('order_total['. $key .'][module_id]', true, 'data-size="small"'); ?>
    </td>
    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_text_field('order_total['. $key .'][title]', true, 'style="text-align: right;"'); ?> :</td>
    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'order_total['. $key .'][value]', true); ?><?php echo $system->functions->form_draw_checkbox('order_total['. $key .'][calculate]', '1', true, '', $system->language->translate('title_calculate', 'Calculate')); ?></td>
    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'order_total['. $key .'][tax]', true); ?></td>
    <td nowrap="nowrap"><a class="remove_ot_row" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>
  </tr>
<?php
        break;
    }
  }
?>
  <tr>
    <td colspan="6" nowrap="nowrap" align="left"><a class="add_ot_row" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" title="<?php echo $system->language->translate('title_insert_', 'Insert'); ?>" /></a></td>
  </tr>
  <tr class="footer" style="font-size: 1.5em;">
    <td colspan="6" nowrap="nowrap" align="right"><?php echo $system->language->translate('title_payment_due', 'Payment Due'); ?>: <strong id="order-total"><?php echo $system->currency->format($order->data['payment_due']); ?></strong></td>
  </tr>
</table>
<script>
  var new_ot_row_index = 0;
  $("body").on("click", "a.add_ot_row", function(event) {
    while ($("input[name='order_total["+new_ot_row_index+"][id]']").length) new_ot_row_index++;
    event.preventDefault();
    var output = '  <tr>'
               + '    <td nowrap="nowrap" align="right"><a href="#" class="add_ot_row"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" title="<?php echo $system->language->translate('text_insert_before', 'Insert before'); ?>" /></a></td>'
               + '    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_hidden_field('order_total[new_ot_row_index][id]', ''); ?><?php echo $system->functions->form_draw_text_field('order_total[new_ot_row_index][module_id]', '', 'data-size="small"'); ?></td>'
               + '    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_text_field('order_total[new_ot_row_index][title]', '', 'style="text-align: right;"'); ?> :</td>'
               + '    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'order_total[new_ot_row_index][value]', $system->currency->format(0, false, true)); ?><?php echo $system->functions->form_draw_checkbox('order_total[new_ot_row_index][calculate]', '1', '1', '', $system->language->translate('title_calculate', 'Calculate')); ?></td>'
               + '    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'order_total[new_ot_row_index][tax]', $system->currency->format(0, false, true)); ?></td>'
               + '    <td nowrap="nowrap"><a class="remove_ot_row" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>'
               + '  </tr>';
	output = output.replace(/new_ot_row_index/g, 'new_' + new_ot_row_index);
	$(this).closest("tr").before(output);
	new_ot_row_index++;
  });
  
  $("body").on("click", "a.remove_ot_row", function(event) {
    event.preventDefault();
	$(this).closest("tr").remove();
  });
  
  function calculate_total() {
    var subtotal = 0;
    $("input[name^='items['][name$='[price]']").each(function() {
      subtotal += Number($(this).val()) * Number($(this).closest('tr').find("input[name^='items['][name$='[quantity]']").val());
    });
    $("input[name^='order_total['][value='ot_subtotal']").closest('tr').find("input[name^='order_total['][name$='[value]']").val(subtotal);
    
    var subtotal_tax = 0;
    $("input[name^='items['][name$='[tax]']").each(function() {
      subtotal_tax += Number($(this).val()) * Number($(this).closest('tr').find("input[name^='items['][name$='[quantity]']").val());
    });
    $("input[name^='order_total['][value='ot_subtotal']").closest('tr').find("input[name^='order_total['][name$='[tax]']").val(subtotal_tax);
    
    var order_total = subtotal + subtotal_tax;
    $("input[name^='order_total['][name$='[value]']").each(function() {
      if ($(this).closest('tr').find("input[name^='order_total['][name$='[calculate]']").is(':checked')) {
        order_total += Number(Number($(this).val()));
      }
    });
    $("input[name^='order_total['][name$='[tax]']").each(function() {
      if ($(this).closest('tr').find("input[name^='order_total['][name$='[calculate]']").is(':checked')) {
        order_total += Number($(this).val());
      }
    });
    order_total = Math.round(order_total * 1<?php echo str_repeat('0', $system->currency->currencies[$order->data['currency_code']]['decimals']); ?>) / 1<?php echo str_repeat('0', $system->currency->currencies[$order->data['currency_code']]['decimals']); ?>;
    $("#order-total").text("<?php echo $system->currency->currencies[$order->data['currency_code']]['prefix']; ?>" + order_total + "<?php echo $system->currency->currencies[$order->data['currency_code']]['suffix']; ?>");
  }
  
  $("body").on("click keyup", "input[name^='items'][name$='[price]'], input[name^='items'][name$='[tax]'], input[name^='items'][name$='[quantity]'], input[name^='order_total'][name$='[value]'], input[name^='order_total'][name$='[tax]'], a.remove_item, a.remove_ot_row", function() {
    calculate_total();
  });
</script>

<h2><?php echo $system->language->translate('title_comments', 'Comments'); ?></h2>
<table class="dataTable" style="width: 100%;">
  <tr class="header">
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_date', 'Date'); ?></th>
    <th nowrap="nowrap" align="left" style="width: 100%;"><?php echo $system->language->translate('title_comment', 'Comment'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_hidden', 'Hidden'); ?></th>
    <th nowrap="nowrap">&nbsp;</th>
  </tr>
<?php
  foreach (array_keys($_POST['comments']) as $key) {
?>
  <tr>
    <td nowrap="nowrap" align="left"><?php foreach (array_keys($_POST['comments'][$key]) as $field) echo $system->functions->form_draw_hidden_field('comments['. $key .']['. $field .']', true); ?><?php echo strftime($system->language->selected['format_datetime'], strtotime($_POST['comments'][$key]['date_created'])); ?></td>
    <td align="left"><?php echo nl2br($_POST['comments'][$key]['text']); ?></td>
    <td nowrap="nowrap" align="left"><?php echo !empty($_POST['comments'][$key]['hidden']) ? 'x' : '-'; ?></td>
    <td nowrap="nowrap"><a class="remove_comment" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>
  </tr>
<?php
  }
?>
  <tr>
    <td nowrap="nowrap" align="left" colspan="4"><a class="add_comment" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" title="<?php echo $system->language->translate('title_insert_', 'Insert'); ?>" /></a></td>
  </tr>
</table>
<script>
  var new_comment_index = 0;
  $("a.add_comment").click(function(event) {
    while ($("input[name='comments["+new_comment_index+"][id]']").length) new_comment_index++;
    event.preventDefault();
    var output = '  <tr>'
               + '    <td nowrap="nowrap" align="left"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_hidden_field('comments[new_comment_index][id]', '') . $system->functions->form_draw_hidden_field('comments[new_comment_index][date_created]', strftime($system->language->selected['format_datetime'])) . strftime($system->language->selected['format_datetime'])); ?></td>'
               + '    <td align="left"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_textarea('comments[new_comment_index][text]', '', 'style="width: 100%; height: 45px;"')); ?></td>'
               + '    <td nowrap="nowrap" align="left"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_checkbox('comments[new_comment_index][hidden]', '1', '', '', $system->language->translate('title_hidden', 'Hidden'))); ?></td>'
               + '    <td nowrap="nowrap"><a class="remove_comment" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>'
               + '  </tr>';
    output = output.replace(/new_comment_index/g, 'new_' + new_comment_index);
    $(this).closest("tr").before(output);
    new_comment_index++;
  });
  
  $("body").on("click", "a.remove_comment", function(event) {
    event.preventDefault();
    $(this).closest("tr").remove();
  });
</script>

<p><strong><?php echo $system->language->translate('title_order_status', 'Order Status'); ?>:</strong> <?php echo $system->functions->form_draw_order_status_list('order_status_id', true); ?></p>

<p align="right"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($order->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></p>

<?php echo $system->functions->form_draw_form_end(); ?>