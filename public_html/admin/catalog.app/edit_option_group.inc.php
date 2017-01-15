<?php

  if (!empty($_GET['option_group_id'])) {
    $option_group = new ctrl_option_group($_GET['option_group_id']);
  } else {
    $option_group = new ctrl_option_group();
  }

  if (empty($_POST)) {
    foreach ($option_group->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($option_group->data['id']) ? language::translate('title_edit_option_group', 'Edit Option Group') : language::translate('title_create_new_option_group', 'Create New Option Group'));

  if (!empty($_POST['save'])) {

    if (empty($_POST['required'])) $_POST['required'] = 0;
    if (empty($_POST['values'])) $_POST['values'] = array();

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

      header('Location: '. document::link('', array('doc' => 'option_groups'), array('app')));
      exit;
    }
  }

  if (!empty($_POST['delete'])) {

    if (empty($errors)) {
      $option_group->delete();

      header('Location: '. document::link('', array('doc' => 'option_groups'), array('app')));
      exit;
    }
  }

?>
<h1><?php echo $app_icon; ?> <?php echo !empty($option_group->data['id']) ? language::translate('title_edit_option_group', 'Edit Option Group') : language::translate('title_create_new_option_group', 'Create New Option Group'); ?></h1>

<?php echo functions::form_draw_form_begin('option_group_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_name', 'Name'); ?></label>
      <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, ''); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md">
      <label><?php echo language::translate('title_description', 'Description'); ?></label>
      <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'description['. $language_code .']', true); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_required', 'Required'); ?></label>
      <div class="checkbox">
        <label><?php echo functions::form_draw_checkbox('required', '1', true); ?> <?php echo language::translate('title_required', 'Required'); ?></label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_sort', 'Sort'); ?></label>
      <div class="radio">
        <label><?php echo functions::form_draw_radio_button('sort', 'alphabetical', true); ?> <?php echo language::translate('title_alphabetical', 'Alphabetical'); ?></label>
      </div>
      <div class="radio">
        <label><?php echo functions::form_draw_radio_button('sort', 'priority', true); ?> <?php echo language::translate('title_priority', 'Priority'); ?></label>
      </div>
      <div class="radio">
        <label><?php echo functions::form_draw_radio_button('sort', 'product', true); ?> <?php echo language::translate('text_set_by_product', 'Set by product'); ?></label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_function', 'Function'); ?></label>
      <?php echo functions::form_draw_select_field('function', array(array('input'), array('checkbox'), array('radio'), array('select'), array('textarea')), true); ?>
    </div>
  </div>

  <div id="option-values-multiset">
    <h2><?php echo language::translate('title_values', 'Values'); ?></h2>

    <table class="table table-striped data-table">
      <thead>
        <tr>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th class="main"><?php echo language::translate('title_values', 'Values'); ?></th>
          <th>&nbsp;</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($_POST['values'])) foreach (array_keys($_POST['values']) as $key) { ?>
        <tr>
          <td><?php echo isset($_POST['values'][$key]['id']) ? $_POST['values'][$key]['id'] : ''; ?><?php echo functions::form_draw_hidden_field('values['. $key .'][id]', true); ?><?php echo functions::form_draw_hidden_field('values['. $key .'][value]', ''); ?></td>
          <td><?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'values['. $key .'][name]['. $language_code .']', true, ''); ?></td>
          <td class="text-right"><a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a> <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a> <a href="#"<?php echo empty($num_products) ? ' class="remove"' : ''; ?> title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3"><a class="add" href="#"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_add_value', 'Add Value'); ?></a></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div id="option-values-range">
    <h2><?php echo language::translate('title_values', 'Values'); ?></h2>
    <table class="table table-striped data-table">
      <thead>
    <tr>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th width="100%"><?php echo language::translate('title_value', 'Value'); ?></th>
        <th></th>
      </tr>
  </thead>
  <tbody>
<?php
    if (!empty($_POST['values'])) {
      $keys = array_keys($_POST['values']);
      $key = array_shift($keys);
    } else {
      $key = 0;
    }
?>
      <tr>
        <td><?php echo isset($_POST['values'][$key]['id']) ? $_POST['values'][$key]['id'] : ''; ?><?php echo functions::form_draw_hidden_field('values['. $key .'][id]', true); ?></td>
        <td><?php echo language::translate('title_range', 'Range'); ?>: <?php echo functions::form_draw_text_field('values['. $key .'][value]', true); ?> (<?php echo language::translate('title_example', 'Example'); ?>: 100-400)</td>
        <td></td>
      </tr>
    </table>
  </div>

  <div id="option-values-input">
    <h2><?php echo language::translate('title_values', 'Values'); ?></h2>
    <table class="table table-striped data-table">
      <thead>
    <tr>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th width="100%"><?php echo language::translate('title_value', 'Value'); ?></th>
        <th></th>
      </tr>
  </thead>
  <tbody>
<?php
    if (!empty($_POST['values'])) {
      $keys = array_keys($_POST['values']);
      $key = array_shift($keys);
    } else {
      $key = 0;
    }
?>
      <tr>
        <td><?php echo isset($_POST['values'][$key]['id']) ? $_POST['values'][$key]['id'] : ''; ?><?php echo functions::form_draw_hidden_field('values['. $key .'][id]', true); ?></td>
        <td><?php echo language::translate('title_default', 'Default'); ?>: <?php echo functions::form_draw_text_field('values['. $key .'][value]', true); ?></td>
        <td></td>
      </tr>
    </table>
  </div>

  <div id="option-values-textarea">
    <h2><?php echo language::translate('title_values', 'Values'); ?></h2>
    <table class="table table-striped data-table">
      <thead>
    <tr>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th width="100%"><?php echo language::translate('title_value', 'Value'); ?></th>
        <th></th>
      </tr>
  </thead>
  <tbody>
<?php
    if (!empty($_POST['values'])) {
      $keys = array_keys($_POST['values']);
      $key = array_shift($keys);
    } else {
      $key = 0;
    }
?>
      <tr>
        <td><?php echo isset($_POST['values'][$key]['id']) ? $_POST['values'][$key]['id'] : ''; ?><?php echo functions::form_draw_hidden_field('values['. $key .'][id]', true); ?></td>
        <td><?php echo language::translate('title_default', 'Default'); ?>: <?php echo functions::form_draw_textarea('values['. $key .'][value]', true); ?></td>
        <td></td>
      </tr>
    </table>
  </div>

  <p class="btn-group">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
    <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
    <?php echo (!empty($option_group->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>

<script>
  $('select[name="function"]').change(function() {
    $('div[id^="option-values"]').hide();
    $('div[id^="option-values"] input, div[id^="option-values"] textarea').attr('disabled', 'disabled');
    switch ($(this).find('option:selected').val()) {
      case 'select':
      case 'checkbox':
      case 'radio':
        $('#option-values-multiset').show();
        $('#option-values-multiset input').removeAttr('disabled');
        break;
      case 'range':
        $('#option-values-range').show();
        $('#option-values-range input').removeAttr('disabled');
        break;
      case 'input':
        $('#option-values-input').show();
        $('#option-values-input input').removeAttr('disabled');
        break;
      case 'textarea':
        $('#option-values-textarea').show();
        $('#option-values-textarea input').removeAttr('disabled');
        $('#option-values-textarea textarea').removeAttr('disabled');
        break;
    }
  });

  var new_value_index = 1;
  $('#option-values-multiset').on('click', '.add', function(event) {
    event.preventDefault();
    while ($('input[name^="values[new_'+ new_value_index +'][id]"]').length) new_value_index++;
<?php
  $name_fields = '';
  foreach (array_keys(language::$languages) as $language_code) {
    $name_fields .= functions::form_draw_regional_input_field($language_code, 'values[new_value_index][name]['. $language_code .']', '', '');
  }
?>
    var output = '<tr>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('values[new_value_index][id]', '') . functions::form_draw_hidden_field('values[new_value_index][value]', '')); ?></td>'
               + '  <td><?php echo functions::general_escape_js($name_fields); ?></td>'
               + '  <td class="text-right"><a class="move-up" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_up', 'Move up'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"')); ?></a> <a class="move-down" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_down', 'Move down'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"')); ?></a> <a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
               + '</tr>';
    output = output.replace(/new_value_index/g, 'new_' + new_value_index);
    $(this).closest('table').find('tbody').append(output);
  });

  $('#option-values-multiset').on('click', '.move-up, .move-down', function(event) {
    event.preventDefault();
    var row = $(this).closest('tr');

    if ($(this).is('.move-up') && $(row).prevAll().length) {
      $(row).insertBefore($(row).prev());
    } else if ($(this).is('.move-down') && $(row).nextAll().length) {
      $(row).insertAfter($(row).next());
    }
  });

  $('#option-values-multiset').on('click', '.remove', function(event) {
    event.preventDefault();
    $(this).closest("tr").remove();
  });
  $('select[name=function]').trigger('change');
</script>