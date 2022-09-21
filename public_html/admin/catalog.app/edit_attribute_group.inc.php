<?php

  if (!empty($_GET['group_id'])) {
    $attribute_group = new ent_attribute_group($_GET['group_id']);
  } else {
    $attribute_group = new ent_attribute_group();
  }

  if (empty($_POST)) {
    $_POST = $attribute_group->data;
  }

  document::$snippets['title'][] = !empty($attribute_group->data['id']) ? language::translate('title_edit_attribute_group', 'Edit Attribute Group') : language::translate('title_create_new_attribute_group', 'Create New Attribute Group');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_attribute_groups', 'Attribute Groups'), document::link(WS_DIR_ADMIN, ['doc' => 'attribute_groups'], ['app']));
  breadcrumbs::add(!empty($attribute_group->data['id']) ? language::translate('title_edit_attribute_group', 'Edit Attribute Group') : language::translate('title_create_new_attribute_group', 'Create New Attribute Group'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['values'])) $_POST['values'] = [];

      foreach ($_POST['values'] as $value) {
        foreach ($value['name'] as $name) {
          if (preg_match('#(["\',\[\]<>])#', $name, $matches)) {
            throw new Exception(strtr(language::translate('error_attribute_value_contains_forbidden_character', 'An attribute value contains a forbidden character (%char)'), ['%char' => $matches[1]]));
          }
        }
      }

      $fields = [
        'code',
        'sort',
        'name',
        'values',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $attribute_group->data[$field] = $_POST[$field];
      }

      $attribute_group->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'attribute_groups'], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($attribute_group->data['id'])) throw new Exception(language::translate('error_must_provide_attribute', 'You must provide an attribute'));

      $attribute_group->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'attribute_groups'], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $option_sort_options = [
    [language::translate('title_list_order', 'List Order'), 'priority'],
    [language::translate('title_alphabetical', 'Alphabetical'), 'alphabetical'],
  ];
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($attribute_group->data['id']) ? language::translate('title_edit_attribute_group', 'Edit Attribute Group') : language::translate('title_create_new_attribute_group', 'Create New Attribute Group'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('attribute_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_code', 'Code'); ?></label>
          <?php echo functions::form_draw_text_field('code', true); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_sort_values', 'Sort Values'); ?></label>
          <?php echo functions::form_draw_select_field('sort', $option_sort_options, true); ?>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_name', 'Name'); ?></label>
        <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, ''); ?>
      </div>

      <div id="product-values">
        <h2><?php echo language::translate('title_values', 'Values'); ?></h2>

        <table class="table table-striped table-hover table-dragable data-table">
          <thead>
            <tr>
              <th><?php echo language::translate('title_id', 'ID'); ?></th>
              <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
              <th><?php echo language::translate('title_in_use', 'In Use'); ?></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($_POST['values'])) foreach ($_POST['values'] as $key => $group_value) { ?>
            <tr>
              <td class="grabable"><?php echo $group_value['id']; ?><?php echo functions::form_draw_hidden_field('values['. $key .'][id]', $group_value['id']); ?></td>
              <td><?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'values['. $key .'][name]['. $language_code .']', true, ''); ?></td>
              <td class="text-center"><?php echo !empty($group_value['in_use']) ? language::translate('title_yes', 'Yes') : language::translate('title_no', 'No'); ?></td>
              <td class="text-end"><?php echo empty($group_value['in_use']) ? '<a href="#" class="remove" title="'. language::translate('title_remove', 'Remove') .'">'. functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"') .'</a>' : false; ?></td>
            </tr>
            <?php } ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3"><a class="add" href="#"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_add_group_value', 'Add Group Value'); ?></a></td>
            </tr>
          </tfoot>
        </table>

      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (!empty($attribute_group->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
  var new_value_index = 1;
  $('form[name="attribute_form"]').on('click', '.add', function(e) {
    e.preventDefault();
    while ($("input[name^='values[new_"+ new_value_index +"][id]']").length) new_value_index++;
<?php
    $name_fields = '';
    foreach (array_keys(language::$languages) as $language_code) $name_fields .= functions::form_draw_regional_input_field($language_code, 'values[new_value_index][name]['. $language_code .']', '', '');
?>
    var output = '<tr>'
               + '  <td><?php echo functions::escape_js(functions::form_draw_hidden_field('values[new_value_index][id]', '')); ?></td>'
               + '  <td><?php echo functions::escape_js($name_fields); ?></td>'
               + '  <td class="text-center"><?php echo language::translate('title_no', 'No'); ?></td>'
               + '  <td class="text-end"><a class="remove" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
               + '</tr>';
    output = output.replace(/new_value_index/g, 'new_' + new_value_index);
    $(this).closest('table').find('tbody').append(output);
  });

  $('form[name="attribute_form"]').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });
</script>