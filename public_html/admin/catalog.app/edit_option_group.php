<?php
  
  $option_group = new ctrl_option_group();
  
  if (!empty($_GET['option_group_id'])) {
  
    $option_group->load($_GET['option_group_id']);
    
    if (empty($option_group)) die('Invalid option group id');
    
    if (empty($_POST)) {
      foreach ($option_group->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
  }
  
  if (!empty($_POST['save'])) {
    
    if (empty($_POST['required'])) $_POST['required'] = 0;
    
    if (empty($errors)) {
      $fields = array(
        'name',
        'description',
        'function',
        'required',
        'sort',
        'values',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $option_group->data[$field] = $_POST[$field];
      }
      
      $option_group->save();
 
      header('Location: '. $system->document->link('', array('doc' => 'option_groups.php'), array('app')));
      exit;
    }
  }
  
  if (!empty($_POST['delete'])) {
    
    if (empty($errors)) {
      $option_group->delete();
 
      header('Location: '. $system->document->link('', array('doc' => 'option_groups.php'), array('app')));
      exit;
    }
  }
  
?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo !empty($option_group->data['id']) ? $system->language->translate('title_edit_option_group', 'Edit Option Group') : $system->language->translate('title_create_new_option_group', 'Create New Option Group'); ?></h1>
<?php echo $system->functions->form_draw_form_begin('form_option_group', 'post'); ?>

<table>
  <tr>
    <td><strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
<?php
  $use_br = false;
  foreach (array_keys($system->language->languages) as $language_code) {
    if ($use_br) echo '<br />';
    echo $system->functions->form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, '');
    $use_br = true;
  }
?>
    </td>
  </tr>
  <tr>
    <td><strong><?php echo $system->language->translate('title_description', 'Description'); ?></strong><br />
<?php
  $use_br = false;
  foreach (array_keys($system->language->languages) as $language_code) {
    if ($use_br) echo '<br />';
    echo $system->functions->form_draw_regional_input_field($language_code, 'description['. $language_code .']', true, 'style="width: 360px;"');
    $use_br = true;
  }
?>
    </td>
  </tr>
  <tr>
    <td><strong><?php echo $system->language->translate('title_required', 'Required'); ?></strong><br />
      <?php echo $system->functions->form_draw_checkbox('required', '1', true); ?> <?php echo $system->language->translate('title_required', 'Required'); ?>
    </td>
  </tr>
  <tr>
    <td><strong><?php echo $system->language->translate('title_sort', 'Sort'); ?></strong><br />
      <?php echo $system->functions->form_draw_radio_button('sort', 'alphabetical', true); ?> <?php echo $system->language->translate('title_alphabetical', 'Alphabetical'); ?><br />
      <?php echo $system->functions->form_draw_radio_button('sort', 'priority', true); ?> <?php echo $system->language->translate('title_priority', 'Priority'); ?><br />
      <?php echo $system->functions->form_draw_radio_button('sort', 'product', true); ?> <?php echo $system->language->translate('text_set_by_product', 'Set by product'); ?>
    </td>
  </tr>
  <tr>
    <td><strong><?php echo $system->language->translate('title_function', 'Function'); ?></strong><br />
      <?php echo $system->functions->form_draw_select_field('function', array(array('input'), array('checkbox'), array('radio'), array('select'), array('textarea')), true); ?>
    </td>
  </tr>
</table>
<script>
  $("select[name='function']").change(function() {
    $("div[id^='option-values']").hide();
    $("div[id^='option-values'] input").attr("disabled", "disabled");
    switch ($(this).find("option:selected").val()) {
      case "select":
      case "checkbox":
      case "radio":
        $("#option-values-multiset").show();
        $("#option-values-multiset input").removeAttr("disabled");
        break;
      case "range":
        $("#option-values-range").show();
        $("#option-values-range input").removeAttr("disabled");
        break;
      case "input":
        $("#option-values-input").show();
        $("#option-values-input input").removeAttr("disabled");
        break;
      case "textarea":
        $("#option-values-textarea").show();
        $("#option-values-textarea input").removeAttr("disabled");
        break;
    }
  });
</script>

<div id="option-values-multiset">
  <h2><?php echo $system->language->translate('title_values', 'Values'); ?></h2>
  <table width="100%" class="dataTable">
    <tr class="header">
      <th align="left" style="vertical-align: text-top" nowrap="nowrap"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
      <th align="left" style="vertical-align: text-top" nowrap="nowrap" width="100%"><?php echo $system->language->translate('title_values', 'Values'); ?></th>
      <th align="center" style="vertical-align: text-top" nowrap="nowrap">&nbsp;</th>
    </tr>
<?php
    if (!empty($_POST['values'])) foreach (array_keys($_POST['values']) as $key) {
?>
    <tr>
      <td align="left"><?php echo $_POST['values'][$key]['id']; ?><?php echo $system->functions->form_draw_hidden_field('values['. $key .'][id]', true); ?><?php echo $system->functions->form_draw_hidden_field('values['. $key .'][value]', ''); ?></td>
      <td align="left">
<?php
      $use_br = false;
      foreach (array_keys($system->language->languages) as $language_code) {
        if ($use_br) echo '<br />';
        echo $system->functions->form_draw_regional_input_field($language_code, 'values['. $key .'][name]['. $language_code .']', true, '');
        $use_br = true;
      }
?>
      </td>
      <td align="right" nowrap="nowrap"><a id="move-value-up" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/up.png" width="16" height="16" border="0" alt="<?php echo $system->language->translate('text_move_up', 'Move up'); ?>" /></a> <a id="move-value-down" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/down.png" width="16" height="16" border="0" alt="<?php echo $system->language->translate('text_move_down', 'Move down'); ?>" /></a> <a href="#"<?php echo empty($num_products) ? ' id="remove-value"' : ''; ?>><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" /></a></td>
    </tr>
<?php
    }
?>
    <tr>
      <td colspan="5"><a id="add-value" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" /> <?php echo $system->language->translate('title_add_value', 'Add Value'); ?></a></td>
    </tr>  
  </table>
  <script>
    var new_value_index = 1;
    $('body').on('click', '#add-value', function(event) {
      event.preventDefault();
      while ($("input[name^='values[new_"+ new_value_index +"][id]']").length) new_value_index++;
<?php
    $name_fields = '';
    $use_br = false;
    foreach (array_keys($system->language->languages) as $language_code) {
      if ($use_br) $name_fields .=  '<br />';
      $name_fields .= $system->functions->form_draw_regional_input_field($language_code, 'values[new_value_index][name]['. $language_code .']', '', '');
      $use_br = true;
    }
?>
      var output = '<tr>'
                 + '  <td align="left" nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_hidden_field('values[new_value_index][id]', '') . $system->functions->form_draw_hidden_field('values[new_value_index][value]', '')); ?></td>'
                 + '  <td align="left" nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $name_fields); ?></td>'
                 + '  <td align="left" nowrap="nowrap"><a id="move-value-up" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/up.png" width="16" height="16" border="0" alt="<?php echo $system->language->translate('text_move_up', 'Move up'); ?>" /></a> <a id="move-value-down" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/down.png" width="16" height="16" border="0" alt="<?php echo $system->language->translate('text_move_down', 'Move down'); ?>" /></a> <a id="remove-value" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>'
                 + '</tr>';
      output = output.replace(/new_value_index/g, 'new_' + new_value_index);
      $(this).closest('tr').before(output);
    });
    
    $("body").on("click", "#move-value-up, #move-value-down", function(event) {
      event.preventDefault();
      var row = $(this).parents("tr:first");
      var firstrow = $('table tr:first');

      if ($(this).is("#move-value-up") && row.prevAll().length > 1) {
          row.insertBefore(row.prev());
      } else if ($(this).is("#move-value-down") && row.nextAll().length-1 > 0) {
          row.insertAfter(row.next());
      } else {
        return false;
      }
    });
    
    $("body").on("click", "#remove-value", function(event) {
      event.preventDefault();
      $(this).closest('tr').remove();
    });
  </script>
</div>

<div id="option-values-range">
  <h2><?php echo $system->language->translate('title_values', 'Values'); ?></h2>
  <table width="100%" class="dataTable">
    <tr class="header">
      <th align="left" style="vertical-align: text-top" nowrap="nowrap"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
      <th align="left" style="vertical-align: text-top" nowrap="nowrap" width="100%"><?php echo $system->language->translate('title_value', 'Value'); ?></th>
    </tr>
<?php
    if (!empty($_POST['values'])) {
      $key = array_shift(array_keys($_POST['values']));
    } else {
      $key = 0;
    }
?>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo isset($_POST['values'][$key]['id']) ? $_POST['values'][$key]['id'] : ''; ?><?php echo $system->functions->form_draw_hidden_field('values['. $key .'][id]', true); ?></td>
      <td align="left" nowrap="nowrap"><?php echo $system->language->translate('title_range', 'Range'); ?>: <?php echo $system->functions->form_draw_input('values['. $key .'][value]', true, 'text', 'style="width: 50px;"'); ?> (<?php echo $system->language->translate('title_example', 'Example'); ?>: 100-400)
      </td>
    </tr>
  </table>
</div>
<div id="option-values-input">
  <h2><?php echo $system->language->translate('title_values', 'Values'); ?></h2>
  <table width="100%" class="dataTable">
    <tr class="header">
      <th align="left" style="vertical-align: text-top" nowrap="nowrap"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
      <th align="left" style="vertical-align: text-top" nowrap="nowrap" width="100%"><?php echo $system->language->translate('title_value', 'Value'); ?></th>
    </tr>
<?php
    if (!empty($_POST['values'])) {
      $key = array_shift(array_keys($_POST['values']));
    } else {
      $key = 0;
    }
?>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo isset($_POST['values'][$key]['id']) ? $_POST['values'][$key]['id'] : ''; ?><?php echo $system->functions->form_draw_hidden_field('values['. $key .'][id]', true); ?></td>
      <td align="left" nowrap="nowrap"><?php echo $system->language->translate('title_default', 'Default'); ?>: <?php echo $system->functions->form_draw_input('values['. $key .'][value]', true, 'text'); ?>
      </td>
    </tr>
  </table>
</div>
<div id="option-values-textarea">
  <h2><?php echo $system->language->translate('title_values', 'Values'); ?></h2>
  <table width="100%" class="dataTable">
    <tr class="header">
      <th align="left" style="vertical-align: text-top" nowrap="nowrap"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
      <th align="left" style="vertical-align: text-top" nowrap="nowrap" width="100%"><?php echo $system->language->translate('title_value', 'Value'); ?></th>
    </tr>
<?php
    if (!empty($_POST['values'])) {
      $key = array_shift(array_keys($_POST['values']));
    } else {
      $key = 0;
    }
?>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo isset($_POST['values'][$key]['id']) ? $_POST['values'][$key]['id'] : ''; ?><?php echo $system->functions->form_draw_hidden_field('values['. $key .'][id]', true); ?></td>
      <td align="left" nowrap="nowrap"><?php echo $system->language->translate('title_default', 'Default'); ?>: <?php echo $system->functions->form_draw_textarea('values['. $key .'][value]', true $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (!empty($option_group->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></p>
<?php echo $system->functions->form_draw_form_end(); ?>