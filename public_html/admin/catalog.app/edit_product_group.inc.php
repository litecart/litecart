<?php

  if (!empty($_GET['product_group_id'])) {
    $product_group = new ctrl_product_group($_GET['product_group_id']);
  } else {
    $product_group = new ctrl_product_group();
  }

  if (empty($_POST)) {
    foreach ($product_group->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($product_group->data['id']) ? language::translate('title_edit_product_group', 'Edit Product Group') : language::translate('title_new_product_group', 'Create New Product Group'));

  if (!empty($_POST['save'])) {

    if (empty($_POST['values'])) $_POST['values'] = array();

    if (empty($errors)) {
      $fields = array(
        'name',
        'values',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $product_group->data[$field] = $_POST[$field];
      }

      $product_group->save();

      header('Location: '. document::link('', array('doc' => 'product_groups'), array('app')));
      exit;
    }
  }

  if (!empty($_POST['delete'])) {

    if (empty($errors)) {
      $product_group->delete();

      header('Location: '. document::link('', array('doc' => 'product_groups'), array('app')));
      exit;
    }
  }

?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($product_group->data['id']) ? language::translate('title_edit_product_group', 'Edit Product Group') : language::translate('title_new_product_group', 'Create New Product Group'); ?></h1>

<?php echo functions::form_draw_form_begin('form_product_group', 'post'); ?>

<?php
  $use_br = false;
  foreach (array_keys(language::$languages) as $language_code) {
    if ($use_br) echo '<br />';
    echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, '');
    $use_br = true;
  }
?>

  <div id="product-values">
    <h2><?php echo language::translate('title_values', 'Values'); ?></h2>
    <table width="100%" class="dataTable">
      <tr class="header">
        <th style="vertical-align: text-top;"><?php echo language::translate('title_id', 'ID'); ?></th>
        <th style="vertical-align: text-top; width: 100%;"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th style="text-align: center; vertical-align: text-top;"><?php echo empty($product_group->data['id']) ? '' : language::translate('title_products', 'Products'); ?></th>
        <th style="text-align: center; vertical-align: text-top;">&nbsp;</th>
      </tr>
<?php
    if (!empty($_POST['values'])) foreach ($_POST['values'] as $key => $group_value) {

      $products_query = database::query(
        "select id from ". DB_TABLE_PRODUCTS ."
        where product_groups like '%". (int)$product_group->data['id'] ."-". (int)$group_value['id'] ."%';"
      );
      $num_products = database::num_rows($products_query);
?>
      <tr>
        <td><?php echo $group_value['id']; ?><?php echo functions::form_draw_hidden_field('values['. $key .'][id]', $group_value['id']); ?></td>
        <td>
<?php
      $use_br = false;
      foreach (array_keys(language::$languages) as $language_code) {
        if ($use_br) echo '<br />';
        echo functions::form_draw_regional_input_field($language_code, 'values['. $key .'][name]['. $language_code .']', true, '');
        $use_br = true;
      }
?>
        </td>
        <td style="text-align: center;"><?php echo $num_products; ?></td>
        <td style="text-align: right;"><?php echo empty($num_products) ? '<a href="#" id="remove-group-value" title="'. language::translate('title_remove', 'Remove') .'">'. functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"') .'</a>' : false; ?></td>
      </tr>
  <?php
    }
  ?>
      <tr>
        <td colspan="4"><a id="add-group-value" href="#"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_add_group', 'Add Group Value'); ?></a></td>
      </tr>
    </table>
    <script>
      var new_value_index = 1;
      $("body").on("click", "#add-group-value", function(event) {
        event.preventDefault();
        while ($("input[name^='values[new_"+ new_value_index +"][id]']").length) new_value_index++;
<?php
        $name_fields = '';
        $use_br = false;
        foreach (array_keys(language::$languages) as $language_code) {
          if ($use_br) $name_fields .=  '<br />';
          $name_fields .= functions::form_draw_regional_input_field($language_code, 'values[new_value_index][name]['. $language_code .']', '', '');
          $use_br = true;
        }
?>
        var output = '<tr>'
                   + '  <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('values[new_value_index][id]', '')); ?></td>'
                   + '  <td><?php echo functions::general_escape_js($name_fields); ?></td>'
                   + '  <td>&nbsp;</td>'
                   + '  <td style="text-align: right;"><a id="remove-group-value" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
                   + '</tr>';
        output = output.replace(/new_value_index/g, 'new_' + new_value_index);
        $(this).closest('tr').before(output);
      });

      $("body").on("click", "#remove-group-value", function(event) {
        event.preventDefault();
        $(this).closest('tr').remove();
      });
    </script>
  </div>

  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (!empty($product_group->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>

<?php echo functions::form_draw_form_end(); ?>