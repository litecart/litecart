<?php
  $system->document->layout = 'printable';

  $order = new ctrl_order($_GET['order_id']);
?>
<script>
  $(document).ready(function() {
    parent.$('#fancybox-content').height($('body').height() + parseInt(parent.$('#fancybox-content').css('border-top-width')) + parseInt(parent.$('#fancybox-content').css('border-bottom-width')));
    parent.$.fancybox.center();
  });
</script>

<?php
  if (!empty($_POST['add'])) {
    
    if (!empty($system->notices->data['errors'])) {
      die(array_shift($system->notices->data['errors']));
    }
?>
<script>
  var new_row = '  <tr class="item">'
              + '    <td nowrap="nowrap" align="left">'
              + '      <?php echo $system->functions->form_draw_hidden_field('items[new_item_index][id]', ''); ?>'
              + '      <?php echo $system->functions->form_draw_hidden_field('items[new_item_index][product_id]', '0'); ?>'
              + '      <?php echo $system->functions->form_draw_hidden_field('items[new_item_index][option_stock_combination]', ''); ?>'
              + '      <?php echo $system->functions->form_draw_hidden_field('items[new_item_index][options]', ''); ?>'
              + '      <?php echo $system->functions->form_draw_hidden_field('items[new_item_index][name]', $_POST['name']); ?>'
              + '      <?php echo $_POST['name']; ?>'
              + '    </td>'
              + '    <td nowrap="nowrap" align="center"><?php echo $system->functions->form_draw_hidden_field('items[new_item_index][sku]', $_POST['sku']); ?><?php echo $_POST['sku']; ?></td>'
              + '    <td nowrap="nowrap" align="center"><?php echo $system->functions->form_draw_decimal_field('weight', $_POST['weight']); ?> <?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_weight_classes_list('weight_class', $_POST['weight_class'])); ?></td>'
              + '    <td nowrap="nowrap" align="center"><?php echo $system->functions->form_draw_number_field('items[new_item_index][quantity]', $_POST['quantity']); ?></td>'
              + '    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'items[new_item_index][price]', $_POST['price']); ?></td>'
              + '    <td nowrap="nowrap" align="right"><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'items[new_item_index][tax]', $_POST['tax']); ?></td>'
              + '    <td nowrap="nowrap"><a class="remove_item" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>'
              + '  </tr>';
  
  var new_item_index = 0
  while ($("input[name='items["+new_item_index+"][id]']", window.parent.document).length) new_item_index++;
  new_row = new_row.replace(/new_item_index/g, "new_" + new_item_index);
  
  $("#order-items .footer", window.parent.document).before(new_row);
  parent.calculate_total();
  parent.$.fancybox.close();
</script>

<?php
  } else {
?>

<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_add_custom_item', 'Add Custom Item'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('form_add_custom_item', 'post'); ?>
  
  <table>
    <tr>
      <td><strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong></td>
      <td><?php echo $system->functions->form_draw_text_field('name', true); ?></td>
    </tr>
    <tr>
      <td><strong><?php echo $system->language->translate('title_sku', 'SKU'); ?></strong></td>
      <td><?php echo $system->functions->form_draw_text_field('sku', true, 'data-size="small"'); ?></td>
    </tr>
    <tr>
      <td><strong><?php echo $system->language->translate('title_price', 'Price'); ?></strong></td>
      <td><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'price', true); ?></td>
    </tr>
    <tr>
      <td><strong><?php echo $system->language->translate('title_tax', 'Tax'); ?></strong></td>
      <td><?php echo $system->functions->form_draw_currency_field($order->data['currency_code'], 'tax', true); ?></td>
    </tr>
    <tr>
      <td><strong><?php echo $system->language->translate('title_weight', 'weight'); ?></strong></td>
      <td><?php echo $system->functions->form_draw_decimal_field('weight', true); ?> <?php echo $system->functions->form_draw_weight_classes_list('weight_class', true); ?></td>
    </tr>
    <tr>
      <td><strong><?php echo $system->language->translate('title_quantity', 'Quantity'); ?></strong></td>
      <td><?php echo $system->functions->form_draw_number_field('quantity', !empty($_POST['quantity']) ? true : '1'); ?></td>
    </tr>
  </table>

  <p><?php echo $system->functions->form_draw_button('add', $system->language->translate('title_add', 'Add'), 'submit', '', 'add'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="parent.$.fancybox.close();"', 'cancel'); ?></p>

<?php
    echo $system->functions->form_draw_form_end();
  }
?>