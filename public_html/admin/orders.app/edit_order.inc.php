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
    if (empty($_POST['customer']['country_code'])) $_POST['customer']['country_code'] = settings::get('default_country_code');
  }
  
// Save data to database
  if (isset($_POST['save'])) {
    
    if (empty($_POST['items'])) $_POST['items'] = array();
    if (empty($_POST['order_total'])) $_POST['order_total'] = array();
    if (empty($_POST['comments'])) $_POST['comments'] = array();
    
    if (empty(notices::$data['errors'])) {
      
      if (!empty($_POST['items'])) {
        foreach (array_keys($_POST['items']) as $key) {
          $_POST['items'][$key]['price'] = $_POST['items'][$key]['price'] / $_POST['currency_value'];
          $_POST['items'][$key]['tax'] = $_POST['items'][$key]['tax'] / $_POST['currency_value'];
        }
        
        foreach (array_keys($_POST['order_total']) as $key) {
          if (empty($_POST['order_total'][$key]['calculate'])) $_POST['order_total'][$key]['calculate'] = false;
          $_POST['order_total'][$key]['value'] = $_POST['order_total'][$key]['value'] / $_POST['currency_value'];
          $_POST['order_total'][$key]['tax'] = $_POST['order_total'][$key]['tax'] / $_POST['currency_value'];
        }
      }
      
      $fields = array(
        'language_code',
        'currency_code',
        'currency_value',
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
      
    // Send e-mails
      if (!empty($_POST['email_order_copy'])) {
        $order->email_order_copy($order->data['customer']['email']);
        foreach (explode(';', settings::get('email_order_copy')) as $email) {
          $order->email_order_copy($email);
        }
      }
      
      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. (!empty($_GET['redirect']) ? $_GET['redirect'] : document::link('', array('app' => $_GET['app'], 'doc' => 'orders'))));
      exit;
    }
  }
  
  // Delete from database
  if (isset($_POST['delete']) && $order) {
    $order->delete();
    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. (!empty($_GET['redirect']) ? $_GET['redirect'] : document::link('', array('app' => $_GET['app'], 'doc' => 'orders'))));
    exit;
  }
  
  functions::draw_fancybox('a.fancybox-iframe', array(
    'type' => 'iframe',
    'width' => 480,
    'height' => 100,
    'transitionIn' => 'fade',
    'transitionOut' => 'fade',
    'onStart' => 'function(links, index){' . PHP_EOL
               . '  if ($(links[index]).hasClass("add-product")) {' . PHP_EOL
               . '    $(links[index]).attr("href", $(links[index]).data("href") + "&language_code=" + $("select[name=\'language_code\']").val() + "&currency_code=" + $("select[name=\'currency_code\']").val() + "&currency_value=" + $("input[name=\'currency_value\']").val() + "&customer%5Bcountry_code%5D=" + $("select[name=\'customer[country_code]\']").val() + "&customer%5Bzone_code%5D=" + $("select[name=\'customer[zone_code]\']").val() + "&customer%5Bcompany%5D=" + $("input[name=\'customer[company]\']").val());' . PHP_EOL
               . '  }' . PHP_EOL
               . '  if ($(links[index]).hasClass("add-custom-item")) {' . PHP_EOL
               . '    $(links[index]).attr("href", $(links[index]).data("href") + "&language_code=" + $("select[name=\'language_code\']").val() + "&currency_code=" + $("select[name=\'currency_code\']").val() + "&currency_value=" + $("input[name=\'currency_value\']").val() + "&customer%5Bcountry_code%5D=" + $("select[name=\'customer[country_code]\']").val() + "&customer%5Bzone_code%5D=" + $("select[name=\'customer[zone_code]\']").val() + "&customer%5Bcompany%5D=" + $("input[name=\'customer[company]\']").val());' . PHP_EOL
               . '  }' . PHP_EOL
               . '}',
  ));
?>

<?php if (!empty($order->data['id'])) { ?>
<?php
  $row = database::fetch(database::query("select min(id),  max(id) from ". DB_TABLE_ORDERS .";"));
  $min_order_id = !empty($row['min(id)']) ? $row['min(id)'] : null;
  $max_order_id = !empty($row['max(id)']) ? $row['max(id)'] : null;
?>
<div style="display: inline; float: right;">
  <?php echo functions::form_draw_form_begin('', 'get'); ?>
  <?php echo functions::form_draw_hidden_field('app', $_GET['app']); ?>
  <?php echo functions::form_draw_hidden_field('doc', $_GET['doc']); ?>
  <?php echo language::translate('title_order_id', 'Order ID') .': '. functions::form_draw_number_field('order_id', $_GET['order_id'], 1, $max_order_id, 'onclick="$(this).select();"'); ?>
  <?php echo functions::form_draw_button('', language::translate('title_go', 'Go'), 'submit'); ?>
  <?php echo functions::form_draw_form_end(); ?>
  <script>
    $("input[name='order_id']").select();
  </script>
</div>
<?php } ?>

<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($order->data['id']) ? language::translate('title_edit_order', 'Edit Order') .' #'. $order->data['id'] : language::translate('title_create_new_order', 'Create New Order'); ?></h1>

<?php echo functions::form_draw_form_begin('form_order', 'post'); ?>

  <h2><?php echo language::translate('title_order_information', 'Order Information'); ?></h2>

  <table class="dataTable">
    <tr>
      <td><?php echo language::translate('title_language', 'Language'); ?><br />
        <?php echo functions::form_draw_languages_list('language_code', true); ?>
      </td>
      <td><?php echo language::translate('title_currency', 'Currency'); ?><br />
        <?php echo functions::form_draw_currencies_list('currency_code', true); ?>
        <script>
          $('select[name="currency_code"]').change(function(e){
            $("input[name='currency_value']").val($(this).find('option:selected').data('value'));
          });
        </script>
      </td>
      <td><?php echo language::translate('title_currency_value', 'Currency Value'); ?><br />
        <span id="currency-value"><?php echo functions::form_draw_decimal_field('currency_value', true, 3); ?></span>
      </td>
    </tr>
  </table>

  <table id="customer" class="dataTable">
    <tr class="header">
      <th colspan="2"><?php echo language::translate('title_customer_information', 'Customer Information'); ?></th>
    </tr>
    <tr>
      <td style="vertical-align: top;">
        <table>
          <tr>
            <td colspan="2"><?php echo language::translate('title_account', 'Account'); ?><br />
              <?php echo functions::form_draw_customers_list('customer[id]', true); ?> <?php echo functions::form_draw_button('get_address', language::translate('title_get_address', 'Get Address'), 'button'); ?></td>
          </tr>
          <tr>
            <td><?php echo language::translate('title_company', 'Company'); ?><br />
              <?php echo functions::form_draw_text_field('customer[company]', true); ?></td>
            <td><?php echo language::translate('title_tax_id', 'Tax ID'); ?><br />
              <?php echo functions::form_draw_text_field('customer[tax_id]', true); ?></td>
          </tr>
          <tr>
            <td><?php echo language::translate('title_firstname', 'First Name'); ?><br />
              <?php echo functions::form_draw_text_field('customer[firstname]', true); ?></td>
            <td><?php echo language::translate('title_lastname', 'Last Name'); ?><br />
              <?php echo functions::form_draw_text_field('customer[lastname]', true); ?></td>
          </tr>
          <tr>
            <td><?php echo language::translate('title_address1', 'Address 1'); ?><br />
              <?php echo functions::form_draw_text_field('customer[address1]', true); ?></td>
            <td><?php echo language::translate('title_address2', 'Address 2'); ?><br />
            <?php echo functions::form_draw_text_field('customer[address2]', true); ?></td>
          </tr>
          <tr>
            <td><?php echo language::translate('title_city', 'City'); ?><br />
              <?php echo functions::form_draw_text_field('customer[city]', true); ?></td>
            <td><?php echo language::translate('title_postcode', 'Postcode'); ?><br />
              <?php echo functions::form_draw_text_field('customer[postcode]', true); ?></td>
          </tr>
          <tr>
            <td><?php echo language::translate('title_country', 'Country'); ?><br />
              <?php echo functions::form_draw_countries_list('customer[country_code]', true); ?></td>
            <td><?php echo language::translate('title_zone', 'Zone'); ?><br />
              <?php echo functions::form_draw_zones_list(isset($_POST['customer']['country_code']) ? $_POST['customer']['country_code'] : '', 'customer[zone_code]', true); ?></td>
          </tr>
          <tr>
            <td width="50%"><?php echo language::translate('title_email', 'Email'); ?><br />
              <?php echo functions::form_draw_email_field('customer[email]', true); ?></td>
            <td><?php echo language::translate('title_phone', 'Phone'); ?><br />
            <?php echo functions::form_draw_text_field('customer[phone]', true); ?></td>
          </tr>
        </table>
        
        <script>
          $("button[name='get_address']").click(function() {
            $.ajax({
              url: '<?php echo document::link('', array('doc' => 'get_address.json'), array('app')); ?>',
              type: 'post',
              data: "customer_id=" + $("*[name='customer[id]']").val() + "&token=<?php echo form::session_post_token(); ?>",
              cache: false,
              async: true,
              dataType: 'json',
              error: function(jqXHR, textStatus, errorThrown) {
                if (console) console.warn(errorThrown.message);
              },
              success: function(data) {
                $.each(data, function(key, value) {
                  if (console) console.log(key +": "+ value);
                  if ($("*[name='customer["+key+"]']").length) $("*[name='customer["+key+"]']").val(data[key]).trigger('change');
                });
              },
            });
          });
          
          $("select[name='customer[country_code]']").change(function() {
            $('body').css('cursor', 'wait');
            $.ajax({
              url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
              type: 'get',
              cache: true,
              async: false,
              dataType: 'json',
              error: function(jqXHR, textStatus, errorThrown) {
                //alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
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
        <h3><?php echo language::translate('title_shipping_address', 'Shipping Address'); ?></h3>
        <table>
          <tr>
            <td><?php echo functions::form_draw_button('copy_billing_address', language::translate('title_copy_billing_address', 'Copy Billing Address'), 'button'); ?></td>
            <td></td>
          </tr>
          <tr>
            <td><?php echo language::translate('title_company', 'Company'); ?><br />
              <?php echo functions::form_draw_text_field('customer[shipping_address][company]', true); ?></td>
            <td></td>
          </tr>
          <tr>
            <td><?php echo language::translate('title_firstname', 'First Name'); ?><br />
              <?php echo functions::form_draw_text_field('customer[shipping_address][firstname]', true); ?></td>
            <td><?php echo language::translate('title_lastname', 'Last Name'); ?><br />
              <?php echo functions::form_draw_text_field('customer[shipping_address][lastname]', true); ?></td>
          </tr>
          <tr>
            <td><?php echo language::translate('title_address1', 'Address 1'); ?><br />
              <?php echo functions::form_draw_text_field('customer[shipping_address][address1]', true); ?></td>
            <td><?php echo language::translate('title_address2', 'Address 2'); ?><br />
            <?php echo functions::form_draw_text_field('customer[shipping_address][address2]', true); ?></td>
          </tr>
          <tr>
            <td><?php echo language::translate('title_city', 'City'); ?><br />
              <?php echo functions::form_draw_text_field('customer[shipping_address][city]', true); ?></td>
            <td><?php echo language::translate('title_postcode', 'Postcode'); ?><br />
              <?php echo functions::form_draw_text_field('customer[shipping_address][postcode]', true); ?></td>
          </tr>
          <tr>
            <td><?php echo language::translate('title_country', 'Country'); ?><br />
              <?php echo functions::form_draw_countries_list('customer[shipping_address][country_code]', true); ?></td>
            <td><?php echo language::translate('title_zone', 'Zone'); ?><br />
              <?php echo functions::form_draw_zones_list(isset($_POST['customer']['shipping_address']['country_code']) ? $_POST['customer']['shipping_address']['country_code'] : '', 'customer[shipping_address][zone_code]', true); ?></td>
          </tr>
        </table>

        <script>
          $("button[name='copy_billing_address']").click(function(){
            fields = ['company', 'firstname', 'lastname', 'address1', 'address2', 'postcode', 'city', 'country_code', 'zone_code'];
            $.each(fields, function(key, field){
              $("*[name='customer[shipping_address]["+ field +"]']").val($("*[name='customer["+ field +"]']").val());
            });
          });
          
          $("select[name='customer[shipping_address][country_code]']").change(function(){
            $('body').css('cursor', 'wait');
            $.ajax({
              url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
              type: 'get',
              cache: true,
              async: true,
              dataType: 'json',
              error: function(jqXHR, textStatus, errorThrown) {
                //alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
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
  
  <table id="order-info" class="dataTable">
    <tr>
      <td style="vertical-align: top;">
        <h3><?php echo language::translate('title_payment_information', 'Payment Information'); ?></h3>
        <table>
          <tr>
          <td><?php echo language::translate('title_option_id', 'Option ID'); ?><br />
            <?php echo functions::form_draw_text_field('payment_option[id]', true); ?></td>
          <td><?php echo language::translate('title_name', 'Name'); ?><br />
            <?php echo functions::form_draw_text_field('payment_option[name]', true); ?></td>
          </tr>
          <tr>
          <td><?php echo language::translate('title_transaction_id', 'Transaction ID'); ?><br />
            <?php echo functions::form_draw_text_field('payment_transaction_id', true); ?></td>
            <td></td>
          </tr>
        </table>
      </td>
      <td class="border-left" style="vertical-align: top;">
        <h3><?php echo language::translate('title_shipping_information', 'Shipping Information'); ?></h3>
        <table>
          <tr>
          <td><?php echo language::translate('title_option_id', 'Option ID'); ?><br />
            <?php echo functions::form_draw_text_field('shipping_option[id]', true); ?></td>
          <td><?php echo language::translate('title_name', 'Name'); ?><br />
            <?php echo functions::form_draw_text_field('shipping_option[name]', true); ?></td>
          </tr>
          <tr>
            <td><?php echo language::translate('title_weight', 'Weight'); ?><br />
              <?php echo weight::format($order->data['weight_total'], $order->data['weight_class']) ?></td>
            <td><?php echo language::translate('title_tracking_id', 'Tracking ID'); ?><br />
              <?php echo functions::form_draw_text_field('shipping_tracking_id', true); ?></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <h2><?php echo language::translate('title_order_items', 'Order Items'); ?></h2>
  <table id="order-items" class="dataTable" style="width: 100%;">
    <tr class="header">
      <th style="width: 100%;"><?php echo language::translate('title_item', 'Item'); ?></th>
      <th style="text-align: center; min-width: 50px;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
      <th style="text-align: center; min-width: 50px;"><?php echo language::translate('title_weight', 'Weight'); ?></th>
      <th style="text-align: center; min-width: 50px;"><?php echo language::translate('title_qty', 'Qty'); ?></th>
      <th style="text-align: center; min-width: 50px;"><?php echo language::translate('title_unit_price', 'Unit Price'); ?></th>
      <th style="text-align: center; min-width: 50px;"><?php echo language::translate('title_tax', 'Tax'); ?></th>
      <th>&nbsp;</th>
    </tr>
<?php
  if (!empty($_POST['items'])) {
    foreach (array_keys($_POST['items']) as $key) {
?>
    <tr class="item">
      <td>
        <?php echo !empty($_POST['items'][$key]['product_id']) ? '<a href="'. document::href_link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $_POST['items'][$key]['product_id'])) .'" target="_blank">'. $_POST['items'][$key]['name'] .'</a>' : $_POST['items'][$key]['name']; ?></div>
        <?php echo functions::form_draw_hidden_field('items['.$key.'][id]', true); ?>
        <?php echo functions::form_draw_hidden_field('items['.$key.'][name]', true); ?>
        <?php echo functions::form_draw_hidden_field('items['.$key.'][product_id]', true); ?>
        <?php echo functions::form_draw_hidden_field('items['.$key.'][option_stock_combination]', true); ?>
<?php
      if (!empty($_POST['items'][$key]['options'])) {
        echo '      <br />' . PHP_EOL
           . '      <table>' . PHP_EOL;
        foreach (array_keys($_POST['items'][$key]['options']) as $field) {
          echo '        <tr>' . PHP_EOL
             . '          <td style="padding-left: 10px;">'. $field .'</td>' . PHP_EOL
             . '          <td>';
          if (is_array($_POST['items'][$key]['options'][$field])) {
            foreach (array_keys($_POST['items'][$key]['options'][$field]) as $k) {
              echo functions::form_draw_text_field('items['.$key.'][options]['.$field.']['.$k.']', true, !empty($_POST['items'][$key]['option_stock_combination']) ? 'readonly="readonly"' : '');
            }
          } else {
            echo functions::form_draw_text_field('items['.$key.'][options]['.$field.']', true, !empty($_POST['items'][$key]['option_stock_combination']) ? 'readonly="readonly"' : '');
          }
          echo '          </td>' . PHP_EOL
             . '        </tr>' . PHP_EOL;
        }
        echo '      </table>' . PHP_EOL;
      } else {
        echo functions::form_draw_hidden_field('items['.$key.'][options]', '');
      }
?>
      </td>
      <td><?php echo functions::form_draw_hidden_field('items['. $key .'][sku]', true); ?><?php echo $_POST['items'][$key]['sku']; ?></td>
      <td style="text-align: center;"><?php echo functions::form_draw_decimal_field('items['. $key .'][weight]', true); ?> <?php echo functions::form_draw_weight_classes_list('items['. $key .'][weight_class]', true); ?></td>
      <td style="text-align: center;"><?php echo functions::form_draw_decimal_field('items['. $key .'][quantity]', true, 2); ?></td>
      <td style="text-align: right;"><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'items['. $key .'][price]', true); ?></td>
      <td style="text-align: right;"><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'items['. $key .'][tax]', true); ?></td>
      <td><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
    </tr>
<?php
    }
  }
?>
    <tr class="footer">
      <td colspan="7">
        <a class="button add-product fancybox-iframe" href="<?php echo document::link('', array('doc' => 'add_product', 'return_method' => 'addItem'), array('app')); ?>" data-href="<?php echo document::link('', array('doc' => 'add_product', 'return_method' => 'addItem'), array('app')); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_add_product', 'Add Product'); ?></a>
        <a class="button add-custom-item fancybox-iframe" href="<?php echo document::link('', array('doc' => 'add_custom_item', 'return_method' => 'addItem'), array('app')); ?>" data-href="<?php echo document::link('', array('doc' => 'add_custom_item', 'return_method' => 'addItem'), array('app')); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_add_custom_item', 'Add Custom Item'); ?></a>
      </td>
    </tr>
  </table>
  
  <script>
    var new_item_index = 0;
    function addItem(item) {
      new_item_index++;
      
      var output = '  <tr class="item">'
                 + '    <td>' + item.name
                 + '      <?php echo functions::general_escape_js(functions::form_draw_hidden_field('items[new_item_index][id]', '')); ?>'
                 + '      <?php echo functions::general_escape_js(functions::form_draw_hidden_field('items[new_item_index][product_id]', '')); ?>'
                 + '      <?php echo functions::general_escape_js(functions::form_draw_hidden_field('items[new_item_index][option_stock_combination]', '')); ?>'
                 + '      <?php echo functions::general_escape_js(functions::form_draw_hidden_field('items[new_item_index][options]', '')); ?>'
                 + '      <?php echo functions::general_escape_js(functions::form_draw_hidden_field('items[new_item_index][name]', '')); ?>'
                 + '    </td>'
                 + '    <td style="text-align: center;"><?php echo functions::general_escape_js(functions::form_draw_hidden_field('items[new_item_index][sku]', '')); ?>'+ item.sku +'</td>'
                 + '    <td style="text-align: center;"><?php echo functions::general_escape_js(functions::form_draw_decimal_field('items[new_item_index][weight]', '')); ?> <?php echo str_replace(PHP_EOL, '', functions::form_draw_weight_classes_list('items[new_item_index][weight_class]', '')); ?></td>'
                 + '    <td style="text-align: center;"><?php echo functions::general_escape_js(functions::form_draw_decimal_field('items[new_item_index][quantity]', '', 2)); ?></td>'
                 + '    <td style="text-align: right;"><?php echo functions::general_escape_js(functions::form_draw_currency_field($_POST['currency_code'], 'items[new_item_index][price]', '')); ?></td>'
                 + '    <td style="text-align: right;"><?php echo functions::general_escape_js(functions::form_draw_currency_field($_POST['currency_code'], 'items[new_item_index][tax]', '')); ?></td>'
                 + '    <td><a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
                 + '  </tr>';
      output = output.replace(/new_item_index/g, 'new_' + new_item_index);
      $("#order-items .footer").before(output);
      
    // Insert values
      var row = $("#order-items tr.item").last();
      $(row).find("*[name$='[product_id]']").val(item.product_id);
      $(row).find("*[name$='[sku]']").val(item.sku);
      $(row).find("*[name$='[option_stock_combination]']").val(item.option_stock_combination);
      $(row).find("*[name$='[name]']").val(item.name);
      $(row).find("*[name$='[sku]']").val(item.sku);
      $(row).find("*[name$='[weight]']").val(item.weight);
      $(row).find("*[name$='[weight_class]']").val(item.weight_class);
      $(row).find("*[name$='[quantity]']").val(item.quantity);
      $(row).find("*[name$='[price]']").val(item.price);
      $(row).find("*[name$='[tax]']").val(item.tax);
      
      if (item.options) {
        var product_options = '<br />'
                            + '<table>';
        $.each(item.options, function(group, value) {
          product_options += '  <tr>'
                           + '    <td style="padding-left: 10px;">'+ group +'</td>'
                           + '    <td>';
          if ($.isArray(value)) {
            $.each(value, function(i, array_value) {
              product_options += '<input type="text" name="items[new_'+ new_item_index +'][options]['+ group +'][]" value="'+ array_value +'" />';
            });
          } else {
            product_options += '<input type="text" name="items[new_'+ new_item_index +'][options]['+ group +']" value="'+ value +'" />';
          }
          product_options += '</tr>';
        });
        product_options += '</table>';
        $(row).find("input[type='hidden'][name$='[options]']").replaceWith(product_options);
      }
      
      calculate_total();
    }
    
    $("body").on("click", "#order-items .remove", function(event) {
      event.preventDefault();
      $(this).closest("tr").remove();
    });
  </script>

  <h2><?php echo language::translate('title_order_total', 'Order Total'); ?></h2>
  <table id="order-total" width="100%" class="dataTable">
    <tr class="header">
      <th>&nbsp;</th>
      <th><?php echo language::translate('title_module_id', 'Module ID'); ?></th>
      <th style="text-align: right;" width="100%"><?php echo language::translate('title_title', 'Title'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_value', 'Value'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_tax', 'Tax'); ?></th>
      <th>&nbsp;</th>
    </tr>
<?php
  if (empty($_POST['order_total'])) {
    $_POST['order_total'][] = array(
      'id' => '',
      'module_id' => 'ot_subtotal',
      'title' => language::translate('title_subtotal', 'Subtotal'),
      'value' => '0',
      'tax' => '0',
      'calculate' => '0',
    );
  }
  foreach (array_keys($_POST['order_total']) as $key) {
    switch($_POST['order_total'][$key]['module_id']) {
      case 'ot_subtotal':
?>
    <tr>
      <td style="text-align: right;">&nbsp;</td>
      <td style="text-align: right;"><?php echo functions::form_draw_hidden_field('order_total['. $key .'][id]', true) . functions::form_draw_text_field('order_total['. $key .'][module_id]', true, 'data-size="small" readonly="readonly"'); ?></td>
      <td style="text-align: right;"><?php echo functions::form_draw_text_field('order_total['. $key .'][title]', true, 'style="text-align: right;"'); ?> :</td>
      <td style="text-align: right;"><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'order_total['. $key .'][value]', true, 'style="text-align: right;"'); ?><?php echo functions::form_draw_checkbox('order_total['. $key .'][calculate]', '1', true, 'disabled="disabled"', language::translate('title_calculate', 'Calculate')); ?></td>
      <td style="text-align: right;"><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'order_total['. $key .'][tax]', true, 'style="text-align: right;"'); ?></td>
      <td>&nbsp;</td>
    </tr>
<?php
        break;
      default:
?>
    <tr>
      <td style="text-align: right;"><a href="#" class="add_ot_row" title="<?php echo language::translate('text_insert_before', 'Insert before'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a></td>
      <td style="text-align: right;"><?php echo functions::form_draw_hidden_field('order_total['. $key .'][id]', true) . functions::form_draw_text_field('order_total['. $key .'][module_id]', true, 'data-size="small"'); ?></td>
      <td style="text-align: right;"><?php echo functions::form_draw_text_field('order_total['. $key .'][title]', true, 'style="text-align: right;"'); ?> :</td>
      <td style="text-align: right;"><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'order_total['. $key .'][value]', true); ?><?php echo functions::form_draw_checkbox('order_total['. $key .'][calculate]', '1', true, '', language::translate('title_calculate', 'Calculate')); ?></td>
      <td style="text-align: right;"><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'order_total['. $key .'][tax]', true); ?></td>
      <td><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
    </tr>
<?php
        break;
    }
  }
?>
    <tr>
      <td colspan="6"><a class="add" href="#" title="<?php echo language::translate('title_insert_', 'Insert'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a></td>
    </tr>
    <tr class="footer" style="font-size: 1.5em;">
      <td colspan="6" style="text-align: right;"><?php echo language::translate('title_payment_due', 'Payment Due'); ?>: <strong class="total"><?php echo currency::format($order->data['payment_due'], false, false, $_POST['currency_code'], $_POST['currency_value']); ?></strong></td>
    </tr>
  </table>
  <script>
    var new_ot_row_index = 0;
    $("body").on("click", "#order-total .add", function(event) {
      while ($("input[name='order_total["+new_ot_row_index+"][id]']").length) new_ot_row_index++;
      event.preventDefault();
      var output = '  <tr>'
                 + '    <td style="text-align: right;"><a href="#" class="add" title="<?php echo functions::general_escape_js(language::translate('text_insert_before', 'Insert before'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"')); ?></a></td>'
                 + '    <td style="text-align: right;"><?php echo functions::general_escape_js(functions::form_draw_hidden_field('order_total[new_ot_row_index][id]', '')); ?><?php echo functions::general_escape_js(functions::form_draw_text_field('order_total[new_ot_row_index][module_id]', '', 'data-size="small"')); ?></td>'
                 + '    <td style="text-align: right;"><?php echo functions::general_escape_js(functions::form_draw_text_field('order_total[new_ot_row_index][title]', '', 'style="text-align: right;"')); ?> :</td>'
                 + '    <td style="text-align: right;"><?php echo functions::general_escape_js(functions::form_draw_currency_field($_POST['currency_code'], 'order_total[new_ot_row_index][value]', currency::format(0, false, true))); ?><?php echo functions::general_escape_js(functions::form_draw_checkbox('order_total[new_ot_row_index][calculate]', '1', '1', '', language::translate('title_calculate', 'Calculate'))); ?></td>'
                 + '    <td style="text-align: right;"><?php echo functions::general_escape_js(functions::form_draw_currency_field($_POST['currency_code'], 'order_total[new_ot_row_index][tax]', currency::format(0, false, true))); ?></td>'
                 + '    <td><a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
                 + '  </tr>';
    output = output.replace(/new_ot_row_index/g, 'new_' + new_ot_row_index);
    $(this).closest("tr").before(output);
    new_ot_row_index++;
    });
    
    $("body").on("click", "#order-total .remove", function(event) {
      event.preventDefault();
    $(this).closest("tr").remove();
    });
    
    function calculate_total() {
      var subtotal = 0;
      $("input[name^='items['][name$='[price]']").each(function() {
        subtotal += Number($(this).val()) * Number($(this).closest('tr').find("input[name^='items['][name$='[quantity]']").val());
      });
      subtotal = Math.round(subtotal * 1* 1<?php echo str_repeat('0', currency::$currencies[$_POST['currency_code']]['decimals']); ?>) / 1<?php echo str_repeat('0', currency::$currencies[$_POST['currency_code']]['decimals']); ?>;
      $("input[name^='order_total['][value='ot_subtotal']").closest('tr').find("input[name^='order_total['][name$='[value]']").val(subtotal);
      
      var subtotal_tax = 0;
      $("input[name^='items['][name$='[tax]']").each(function() {
        subtotal_tax += Number($(this).val()) * Number($(this).closest('tr').find("input[name^='items['][name$='[quantity]']").val());
      });
      subtotal_tax = Math.round(subtotal_tax * 1* 1<?php echo str_repeat('0', currency::$currencies[$_POST['currency_code']]['decimals']); ?>) / 1<?php echo str_repeat('0', currency::$currencies[$_POST['currency_code']]['decimals']); ?>;
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
      order_total = Math.round(order_total * 1<?php echo str_repeat('0', currency::$currencies[$_POST['currency_code']]['decimals']); ?>) / 1<?php echo str_repeat('0', currency::$currencies[$_POST['currency_code']]['decimals']); ?>;
      $("#order-total .total").text("<?php echo currency::$currencies[$_POST['currency_code']]['prefix']; ?>" + order_total + "<?php echo currency::$currencies[$_POST['currency_code']]['suffix']; ?>");
    }
    
    $("body").on("click keyup", "input[name^='items'][name$='[price]'], input[name^='items'][name$='[tax]'], input[name^='items'][name$='[quantity]'], input[name^='order_total'][name$='[value]'], input[name^='order_total'][name$='[tax]'], input[name^='order_total'][name$='[calculate]'], #order-items a.remove, #order-total a.remove", function() {
      calculate_total();
    });
  </script>
  
  <h2><?php echo language::translate('title_comments', 'Comments'); ?></h2>
  <table id="comments" class="dataTable" style="width: 100%;">
    <tr class="header">
      <th style="text-align: center;"><?php echo language::translate('title_date', 'Date'); ?></th>
      <th style="width: 100%;"><?php echo language::translate('title_comment', 'Comment'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_hidden', 'Hidden'); ?></th>
      <th>&nbsp;</th>
    </tr>
<?php
  if (!empty($_POST['comments'])) {
    foreach (array_keys($_POST['comments']) as $key) {
?>
    <tr>
      <td><?php foreach (array_keys($_POST['comments'][$key]) as $field) echo functions::form_draw_hidden_field('comments['. $key .']['. $field .']', true); ?><?php echo strftime(language::$selected['format_datetime'], strtotime($_POST['comments'][$key]['date_created'])); ?></td>
      <td style="white-space: normal;"><?php echo nl2br($_POST['comments'][$key]['text']); ?></td>
      <td><?php echo !empty($_POST['comments'][$key]['hidden']) ? 'x' : '-'; ?></td>
      <td><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
    </tr>
<?php
    }
  }
?>
    <tr>
      <td colspan="4"><a class="add" href="#" title="<?php echo language::translate('title_insert_', 'Insert'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a></td>
    </tr>
  </table>
  <script>
    var new_comment_index = 0;
    $("#comments .add").click(function(event) {
      while ($("input[name='comments["+new_comment_index+"][id]']").length) new_comment_index++;
      event.preventDefault();
      var output = '  <tr>'
                 + '    <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('comments[new_comment_index][id]', '') . functions::form_draw_hidden_field('comments[new_comment_index][date_created]', strftime(language::$selected['format_datetime'])) . strftime(language::$selected['format_datetime'])); ?></td>'
                 + '    <td style="white-space: normal;"><?php echo functions::general_escape_js(functions::form_draw_textarea('comments[new_comment_index][text]', '', 'style="width: 100%; height: 45px;"')); ?></td>'
                 + '    <td><?php echo functions::general_escape_js(functions::form_draw_checkbox('comments[new_comment_index][hidden]', '1', '', '', language::translate('title_hidden', 'Hidden'))); ?></td>'
                 + '    <td><a class="remove_comment" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
                 + '  </tr>';
      output = output.replace(/new_comment_index/g, 'new_' + new_comment_index);
      $(this).closest("tr").before(output);
      new_comment_index++;
    });
    
    $("body").on("click", "#comments .remove", function(event) {
      event.preventDefault();
      $(this).closest("tr").remove();
    });
  </script>

  <p style="text-align: right;""><strong><?php echo language::translate('title_order_status', 'Order Status'); ?>:</strong> <?php echo functions::form_draw_order_status_list('order_status_id', true); ?></p>
  
  <?php if (empty($order->data['id'])) { ?>
  <p style="text-align: right;"><label><?php echo functions::form_draw_checkbox('email_order_copy', true); ?> <?php echo language::translate('title_send_email_order_copy', 'Send email order copy'); ?></label></p>
  <?php } ?>

  <p style="text-align: right;"><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($order->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>
  
<?php echo functions::form_draw_form_end(); ?>