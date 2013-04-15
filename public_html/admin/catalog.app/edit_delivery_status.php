<?php
  
  if (isset($_GET['delivery_status_id'])) {
    $delivery_status = new ctrl_delivery_status($_GET['delivery_status_id']);
    
    if (!$_POST) {
      foreach ($delivery_status->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
  } else {
    $delivery_status = new ctrl_delivery_status();
  }
  
  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) $system->notices->add('errors', $system->language->translate('error_must_enter_name', 'You must enter a name'));
    
    if (!$system->notices->get('errors')) {
    
      $fields = array(
        'name',
        'description',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $delivery_status->data[$field] = $_POST[$field];
      }
      
      $delivery_status->save();
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('doc' => 'delivery_statuses.php'), true, array('delivery_status_id')));
      exit;
    }
  }
  
  if (isset($_POST['delete'])) {

    $delivery_status->delete();
    
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('doc' => 'delivery_statuses.php'), true, array('delivery_status_id')));
    exit();
  }

?>
  <table width="100%">
    <tr>
      <td><h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo !empty($delivery_status->data['id']) ? $system->language->translate('title_edit_delivery_status', 'Edit Delivery Status') : $system->language->translate('title_create_new_delivery_status', 'Create New Delivery Status'); ?></h1>
        <?php echo $system->functions->form_draw_form_begin('delivery_status_form', 'post'); ?>
        <table>
          <tr>
            <td align="left" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, 'style="width: 360px"');
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
  echo $system->functions->form_draw_regional_textarea($language_code, 'description['. $language_code .']', true, 'style="width: 360px; height: 50px;"');  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($delivery_status->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></td>
          </tr>
        </table>
      <?php echo $system->functions->form_draw_form_end(); ?></td>
    </tr>
  </table>