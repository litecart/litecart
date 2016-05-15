<?php

  if (!empty($_GET['sold_out_status_id'])) {
    $sold_out_status = new ctrl_sold_out_status($_GET['sold_out_status_id']);
  } else {
    $sold_out_status = new ctrl_sold_out_status();
  }

  if (empty($_POST)) {
    foreach ($sold_out_status->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($sold_out_status->data['id']) ? language::translate('title_edit_sold_out_status', 'Edit Sold Out Status') : language::translate('title_create_new_sold_out_status', 'Create New Sold Out Status'));

  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));

    if (empty(notices::$data['errors'])) {

      if (empty($_POST['orderable'])) $_POST['orderable'] = 0;

      $fields = array(
        'name',
        'description',
        'orderable',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $sold_out_status->data[$field] = $_POST[$field];
      }

      $sold_out_status->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('doc' => 'sold_out_statuses'), true, array('sold_out_status_id')));
      exit;
    }
  }

  if (isset($_POST['delete'])) {

    $sold_out_status->delete();

    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('doc' => 'sold_out_statuses'), true, array('sold_out_status_id')));
    exit;
  }

?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($sold_out_status->data['id']) ? language::translate('title_edit_sold_out_status', 'Edit Sold Out Status') : language::translate('title_create_new_sold_out_status', 'Create New Sold Out Status'); ?></h1>

<?php echo functions::form_draw_form_begin('sold_out_status_form', 'post'); ?>

  <table>
    <tr>
      <td>
        <strong><?php echo language::translate('title_name', 'Name'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, '');
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
  echo functions::form_draw_regional_textarea($language_code, 'description['. $language_code .']', true, 'data-size="large" style="height: 60px;"');  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td><label><?php echo functions::form_draw_checkbox('orderable', '1', empty($_POST['orderable']) ? '' : '1'); ?> <?php echo language::translate('text_product_is_orderable', 'Product is orderable'); ?></label></td>
    </tr>
  </table>

  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($sold_out_status->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>

<?php echo functions::form_draw_form_end(); ?>