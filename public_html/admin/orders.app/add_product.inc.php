<?php
  document::$layout = 'printable';
  
  if (empty($_GET['currency_code'])) $_GET['currency_code'] = settings::get('store_currency_code');
  if (empty($_GET['currency_value'])) $_GET['currency_value'] = currency::$currencies[$_GET['currency_code']]['value'];
  
  if (!empty($_POST['product_id'])) {
    
    $product = catalog::product($_POST['product_id'], $_GET['currency_code']);
    
    $price = !empty($product->campaign['price']) ? $product->campaign['price'] : $product->price;
    $tax = tax::get_tax($price, $product->tax_class_id, $_GET['customer']);
    $weight = weight::convert($product->weight, $product->weight_class, settings::get('store_weight_class'));
    $weight_class = settings::get('store_weight_class');
    $sku = $product->sku;
    
    $_POST['options'] = !empty($_POST['options']) ? array_filter($_POST['options']) : array();
    $selected_options = array();
    
    if (count($product->options) > 0) {
      foreach (array_keys($product->options) as $key) {
        
        if ($product->options[$key]['required'] != 0) {
          if (empty($_POST['options'][$product->options[$key]['name'][$_GET['language_code']]])) {
            notices::add('errors', language::translate('error_set_product_options', 'Please set your product options') . ' ('. $product->options[$key]['name'][$_GET['language_code']] .')');
          }
        }
        
        if (!empty($_POST['options'][$product->options[$key]['name'][$_GET['language_code']]])) {
          switch ($product->options[$key]['function']) {
            case 'checkbox':
              $valid_values = array();
              foreach ($product->options[$key]['values'] as $value) {
                $valid_values[] = $value['name'][$_GET['language_code']];
                if (in_array($value['name'][$_GET['language_code']], $_POST['options'][$product->options[$key]['name'][$_GET['language_code']]])) {
                  $selected_options[] = $product->options[$key]['id'].'-'.$value['id'];
                  $price += $value['price_adjust'];
                  $tax += tax::get_tax($value['price_adjust'], $product->tax_class_id, $_GET['customer']);;
                }
              }
              
              foreach ($_POST['options'][$product->options[$key]['name'][$_GET['language_code']]] as $current_value) {
                if (!in_array($current_value, $valid_values)) {
                  notices::add('errors', language::translate('error_product_options_contains_errors', 'The product options contains errors'));
                }
              }
              break;
            
            case 'input':
            case 'textarea':
              $values = array_values($product->options[$key]['values']);
              $value = array_shift($values);
              $selected_options[] = $product->options[$key]['id'].'-'.$value['id'];
              $price += $value['price_adjust'];
              $tax += tax::get_tax($value['price_adjust'], $product->tax_class_id, $_GET['customer']);
              break;
            
            case 'radio':
            case 'select':
            
              $valid_values = array();
              foreach ($product->options[$key]['values'] as $value) {
                $valid_values[] = $value['name'][$_GET['language_code']];
                if ($value['name'][$_GET['language_code']] == $_POST['options'][$product->options[$key]['name'][$_GET['language_code']]]) {
                  $selected_options[] = $product->options[$key]['id'].'-'.$value['id'];
                  $price += $value['price_adjust'];
                  $tax += tax::get_tax($value['price_adjust'], $product->tax_class_id, $_GET['customer']);
                }
              }
              
              if (!in_array($_POST['options'][$product->options[$key]['name'][$_GET['language_code']]], $valid_values)) {
                notices::add('errors', language::translate('error_product_options_contains_errors', 'The product options contains errors'));
              }
              break;
          }
        }
      }
    }
    
  // Match options with options stock
    $option_stock_combination = '';
    if (count($product->options_stock) > 0) {
      foreach ($product->options_stock as $option_stock) {
      
        $option_match = true;
        foreach (explode(',', $option_stock['combination']) as $pair) {
          if (!in_array($pair, $selected_options)) {
            $option_match = false;
          }
        }
        
        if ($option_match) {
          if (($option_stock['quantity'] - $_POST['quantity']) < 0 && empty($product->sold_out_status['orderable'])) {
            notices::add('errors', language::translate('text_not_enough_products_in_stock_for_options', 'There are not enough products for the selected options.'));
            return;
          }
          
          $option_stock_combination = $option_stock['combination'];
          if (!empty($option_stock['weight'])) $weight = weight::convert($option_stock['weight'], $option_stock['weight_class'], $product->weight_class);
          if (!empty($option_stock['sku'])) $sku = $option_stock['sku'];
          break;
        }
      }
    }
    
    if (!empty(notices::$data['errors'])) {
      notices::$data['errors'] = array(array_shift(notices::$data['errors']));
    }
    
    $price = round($price * $_GET['currency_value'], currency::$currencies[$_GET['currency_code']]['decimals']);
    $tax = round($tax * $_GET['currency_value'], currency::$currencies[$_GET['currency_code']]['decimals']);
  }
?>
<script>
  $(document).ready(function() {
    parent.$('#fancybox-content').height($('body').height() + parseInt(parent.$('#fancybox-content').css('border-top-width')) + parseInt(parent.$('#fancybox-content').css('border-bottom-width')));
    parent.$.fancybox.center();
  });
</script>

<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_add_product', 'Add Product'); ?></h1>

<?php echo functions::form_draw_form_begin('form_add_product', 'post'); ?>

  <?php echo functions::form_draw_products_list('product_id', true, false, 'onchange="$(this).closest(\'form\').submit();"'); ?>

  <?php if (!empty($product)) { ?>
  
  <hr />
  
  <?php if (!empty($product->options_stock)) {?>
  <div style="float: right; display: inline-block; border: 1px dashed #ccc; padding: 10px;">
    <h3 style="margin-top: 0px;"><?php echo language::translate('title_options_stock', 'Options Stock'); ?></h3>
    <table>
      <?php foreach (array_keys($product->options_stock) as $key) { ?>
      <tr>
        <td><strong><?php echo $product->options_stock[$key]['name'][$_GET['language_code']]; ?></strong></td>
        <td><?php echo $product->options_stock[$key]['quantity']; ?></td>
      </tr>
      <?php } ?>
    </table>
  </div>
  <?php } ?>
  
  <h2><?php echo functions::form_draw_hidden_field('name', $product->name[$_GET['language_code']]); ?><?php echo $product->name[$_GET['language_code']]; ?></h2>
  
  <table>
    <tr>
      <td><strong><?php echo language::translate('title_in_stock', 'In Stock'); ?></strong></td>
      <td><?php echo $product->quantity; ?></td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_price', 'Price'); ?></strong></td>
      <td>
        <?php echo functions::form_draw_hidden_field('price', $price); ?>
        <?php echo !empty($product->campaign['price']) ? '<s>'. currency::format($product->price, true, false, $_GET['currency_code'], $_GET['currency_value']) .'</s>' : null; ?>
        <?php echo currency::format(!empty($product->campaign['price']) ? $product->campaign['price'] : $product->price, true, false, $_GET['currency_code'], $_GET['currency_value']); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_tax', 'Tax'); ?></strong></td>
      <td>
        <?php echo functions::form_draw_hidden_field('tax', $tax); ?>
        <?php echo !empty($product->campaign['price']) ? '<s>'. currency::format(tax::get_tax($product->price, $product->tax_class_id, $_GET['customer']), true, false, $_GET['currency_code'], $_GET['currency_value']) .'</s>' : null; ?>
        <?php echo currency::format(tax::get_tax(!empty($product->campaign['price']) ? $product->campaign['price'] : $product->price, $product->tax_class_id, $_GET['customer']), true, false, $_GET['currency_code'], $_GET['currency_value']); ?>
      </td>
    </tr>
<?php
    if (count($product->options) > 0) {
      
      foreach ($product->options as $group) {
      
        echo '  <tr>' . PHP_EOL
           . '    <td valign="top"><strong>'. $group['name'][$_GET['language_code']] .'</strong>'. (empty($group['required']) == false ? ' <span class="required">*</span>' : '') .'<br />'
           . (!empty($group['description'][$_GET['language_code']]) ? $group['description'][$_GET['language_code']] . '</td>' . PHP_EOL : '')
           . '    <td>';
        
        switch ($group['function']) {
        
          case 'checkbox':
            $use_br = false;
            
            foreach (array_keys($group['values']) as $value_id) {
              if ($use_br) echo '<br />';
              
              $price_adjust_text = '';
              if ($group['values'][$value_id]['price_adjust']) {
                $price_adjust_text = currency::format($group['values'][$value_id]['price_adjust']);
                if ($group['values'][$value_id]['price_adjust'] > 0) {
                  $price_adjust_text = ' +'.$price_adjust_text;
                }
              }
              
              echo '<label>' . functions::form_draw_checkbox('options['.$group['name'][$_GET['language_code']].'][]', $group['values'][$value_id]['name'][$_GET['language_code']], true, 'data-group="'. htmlspecialchars($group['name'][$_GET['language_code']]) .'"'. (!empty($group['required']) ? ' required="required"' : '')) .' '. $group['values'][$value_id]['name'][$_GET['language_code']] . $price_adjust_text . '</label>' . PHP_EOL;
              $use_br = true;
            }
            break;
            
          case 'input':
            $keys = array_keys($group['values']);
            $value_id = array_shift($keys);
          
            $price_adjust_text = '';
            if ($group['values'][$value_id]['price_adjust']) {
              $price_adjust_text = currency::format($group['values'][$value_id]['price_adjust']);
              if ($group['values'][$value_id]['price_adjust'] > 0) {
                $price_adjust_text = ' +'.$price_adjust_text;
              }
            }
            
            echo functions::form_draw_text_field('options['.$group['name'][$_GET['language_code']].']', true, 'data-group="'. htmlspecialchars($group['name'][$_GET['language_code']]) .'"'. (!empty($group['required']) ? ' required="required"' : '')) . $price_adjust_text . PHP_EOL;
            break;
            
          case 'radio':
          
            $use_br = false;
            foreach (array_keys($group['values']) as $value_id) {
              if ($use_br) echo '<br />';
              
              $price_adjust_text = '';
              if ($group['values'][$value_id]['price_adjust']) {
                $price_adjust_text = currency::format($group['values'][$value_id]['price_adjust']);
                if ($group['values'][$value_id]['price_adjust'] > 0) {
                  $price_adjust_text = ' +'.$price_adjust_text;
                }
              }
              
              echo '<label>' . functions::form_draw_radio_button('options['.$group['name'][$_GET['language_code']].']', $group['values'][$value_id]['name'][$_GET['language_code']], true, 'data-group="'. htmlspecialchars($group['name'][$_GET['language_code']]) .'"'. (!empty($group['required']) ? ' required="required"' : '')) .' '. $group['values'][$value_id]['name'][$_GET['language_code']] . $price_adjust_text . '</label>' . PHP_EOL;
              $use_br = true;
            }
            break;
            
          case 'select':
            
            $options = array(array('-- '. language::translate('title_select', 'Select') .' --', ''));
            foreach (array_keys($group['values']) as $value_id) {
            
              $price_adjust_text = '';
              if ($group['values'][$value_id]['price_adjust']) {
                $price_adjust_text = currency::format($group['values'][$value_id]['price_adjust']);
                if ($group['values'][$value_id]['price_adjust'] > 0) {
                  $price_adjust_text = ' +'.$price_adjust_text;
                }
              }

              $options[] = array($group['values'][$value_id]['name'][$_GET['language_code']] . $price_adjust_text, $group['values'][$value_id]['name'][$_GET['language_code']]);
            }
            echo functions::form_draw_select_field('options['.$group['name'][$_GET['language_code']].']', $options, true, false, 'data-group="'. htmlspecialchars($group['name'][$_GET['language_code']]) .'"'. (!empty($group['required']) ? ' required="required"' : ''));
            break;
            
          case 'textarea':
            
            $value_id = array_shift(array_keys($group['values']));
            $price_adjust_text = '';
            if (!empty($group['values'][$value_id]['price_adjust'])) {
              $price_adjust_text = '';
              if ($group['values'][$value_id]['price_adjust'] > 0) {
                $price_adjust_text = ' <br />+'. currency::format($group['values'][$value_id]['price_adjust']);
              }
            }

            echo functions::form_draw_textarea('options['.$group['name'][$_GET['language_code']].']', true, 'data-group="'. htmlspecialchars($group['name'][$_GET['language_code']]) .'"'. (!empty($group['required']) ? ' required="required"' : '')) . $price_adjust_text. PHP_EOL;
            break;
        }
      }
      
      echo '    </td>' . PHP_EOL
         . '  </tr>' . PHP_EOL;
    }
    
    echo functions::form_draw_hidden_field('option_stock_combination', $option_stock_combination);
?>
    <tr>
      <td><strong><?php echo language::translate('title_sku', 'SKU'); ?></strong></td>
      <td><?php echo functions::form_draw_hidden_field('sku', $sku); ?><?php echo $sku; ?></td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_weight', 'Weight'); ?></strong></td>
      <td><?php echo functions::form_draw_hidden_field('weight', $weight) . functions::form_draw_hidden_field('weight_class', $weight_class); ?><?php echo $weight .' '. $product->weight_class; ?></td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_quantity', 'Quantity'); ?></strong></td>
      <td><?php echo functions::form_draw_decimal_field('quantity', !empty($_POST['quantity']) ? true : '1', 2); ?></td>
    </tr>
  </table>
  
  <script>
    $("input[name^='options['], select[name^='options[']").change(function(){
      $(this).closest('form').submit();
    });
  </script>
  
  <p><?php echo functions::form_draw_button('add', language::translate('title_add', 'Add'), 'submit', '', 'add'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="parent.$.fancybox.close();"', 'cancel'); ?></p>

<?php } ?>

<?php echo functions::form_draw_form_end(); ?>

<script>
  $("button[name='add']").click(function(e){
    e.preventDefault();
    
    var item = {
      id: '',
      product_id: $("select[name='product_id']").val(),
      option_stock_combination: $("input[name='option_stock_combination']").val(),
      options: {},
      name: $("input[name='name']").val(),
      sku: $("input[name='sku']").val(),
      weight: $("input[name='weight']").val(),
      weight_class: $("input[name='weight_class']").val(),
      quantity: $("input[name='quantity']").val(),
      price: $("input[name='price']").val(),
      tax: $("input[name='tax']").val()
    };
    
    $("input[name^='options['][type='radio']").each(function(){
      if ($(this).is(':checked')) {
        var key = $(this).data('group');
        item.options[key] = $(this).val();
      }
    });
    
    $("input[name^='options['][type='text'], textarea[name^='options['], select[name^='options[']").each(function(){
      if ($(this).val()) {
        var key = $(this).data('group');
        item.options[key] = $(this).val();
      }
    });
    
    var option_i = 0;
    $("input[name^='options['][type='checkbox']").each(function(){
      if ($(this).is(':checked')) {
        var key = $(this).data('group');
        if (!item.options[key]) item.options[key] = [];
        item.options[key][option_i] = $(this).val();
        option_i++;
      }
    });
    
    parent.<?php echo preg_replace('#([^a-zA-Z_])#', '', $_GET['return_method']); ?>(item);
    parent.$.fancybox.close();
  });
</script>