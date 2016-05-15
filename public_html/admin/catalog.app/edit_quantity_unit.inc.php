<?php

  if (!empty($_GET['quantity_unit_id'])) {
    $quantity_unit = new ctrl_quantity_unit($_GET['quantity_unit_id']);
  } else {
    $quantity_unit = new ctrl_quantity_unit();
  }

  if (empty($_POST)) {
    foreach ($quantity_unit->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($quantity_unit->data['id']) ? language::translate('title_edit_quantity_unit', 'Edit Quantity Unit') : language::translate('title_add_new_quantity_unit', 'Add New Quantity Unit'));

  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));

    if (empty(notices::$data['errors'])) {

      if (empty($_POST['separate'])) $_POST['separate'] = 0;

      $fields = array(
        'decimals',
        'separate',
        'priority',
        'name',
        'description',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $quantity_unit->data[$field] = $_POST[$field];
      }

      $quantity_unit->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('doc' => 'quantity_units'), true, array('quantity_unit_id')));
      exit;
    }
  }

  if (isset($_POST['delete'])) {

    $quantity_unit->delete();

    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('doc' => 'quantity_units'), true, array('quantity_unit_id')));
    exit;
  }

?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($quantity_unit->data['id']) ? language::translate('title_edit_quantity_unit', 'Edit Quantity Unit') : language::translate('title_add_new_quantity_unit', 'Add New Quantity Unit'); ?></h1>

<?php echo functions::form_draw_form_begin('quantity_unit_form', 'post'); ?>

  <table>
    <tr>
      <td>
        <strong><?php echo language::translate('title_name', 'Name'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, 'data-size="tiny"');
  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_description', 'Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'description['. $language_code .']', true, 'data-size="large"');  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_decimals', 'Decimals'); ?></strong><br />
        <?php echo functions::form_draw_number_field('decimals', true, 'data-size="tiny"'); ?>
      </td>
    </tr>
    <tr>
      <td><label><?php echo functions::form_draw_checkbox('separate', '1', true); ?> <?php echo language::translate('text_separate_added_cart_items', 'Separate added cart items'); ?></label></td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_priority', 'Priority'); ?></strong><br />
        <?php echo functions::form_draw_number_field('priority', true, 'data-size="tiny"'); ?>
      </td>
    </tr>
  </table>

  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($quantity_unit->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>

<?php echo functions::form_draw_form_end(); ?></td>