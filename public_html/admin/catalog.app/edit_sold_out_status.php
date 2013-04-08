<?php

  if (isset($_GET['sold_out_status_id'])) {
    $sold_out_status = new ctrl_sold_out_status($_GET['sold_out_status_id']);
    
    if (!$_POST) {
      foreach ($sold_out_status->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
  } else {
    $sold_out_status = new ctrl_sold_out_status();
  }
  
  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) $system->notices->add('errors', $system->language->translate('error_must_enter_name', 'You must enter a name'));
    
    if (!$system->notices->get('errors')) {
    
      $fields = array(
        'name',
        'description',
        'orderable',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $sold_out_status->data[$field] = $_POST[$field];
      }
      
      $sold_out_status->save();
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('doc' => 'sold_out_statuses.php'), true, array('sold_out_status_id')));
      exit;
    }
  }
  
  if (isset($_POST['delete'])) {

    $sold_out_status->delete();
    
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('doc' => 'sold_out_statuses.php'), true, array('sold_out_status_id')));
    exit();
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo (!empty($sold_out_status->data['id'])) ? $system->language->translate('title_edit_sold_out_status', 'Edit Sold Out Status') : $system->language->translate('title_create_new_sold_out_status', 'Create New Sold Out Status'); ?></h1>
  <?php echo $system->functions->form_draw_form_begin('sold_out_status_form', 'post'); ?>
  <table>
    <tr>
      <td align="left" nowrap="nowrap">
        <strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'name['. $language_code .']', (isset($_POST['name'][$language_code]) ? $_POST['name'][$language_code] : ''), 'text', 'style="width: 360px"');
  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_description', 'Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_textarea($language_code, 'description['. $language_code .']', (isset($_POST['description'][$language_code]) ? $_POST['description'][$language_code] : ''), 'style="width: 360px; height: 50px;"');  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_checkbox('orderable', '1', empty($_POST['orderable']) ? '' : '1'); ?> <?php echo $system->language->translate('text_product_is_orderable', 'Product is orderable'); ?></td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($sold_out_status->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></td>
    </tr>
  </table>
<?php echo $system->functions->form_draw_form_end(); ?>