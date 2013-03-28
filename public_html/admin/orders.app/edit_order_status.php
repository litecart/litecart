<?php
  
  if (isset($_GET['order_status_id'])) {
    $order_status = new ctrl_order_status($_GET['order_status_id']);
    
    if (!$_POST) {
      foreach ($order_status->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
  } else {
    $order_status = new ctrl_order_status();
  }
  
  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) $system->notices->add('errors', $system->language->translate('error_must_enter_name', 'You must enter a name'));
    
    if (empty($_POST['notify'])) $_POST['notify'] = 0;
    if (empty($_POST['is_sale'])) $_POST['is_sale'] = 0;
    
    if (!$system->notices->get('errors')) {
    
      $fields = array(
        'is_sale',
        'notify',
        'name',
        'description',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $order_status->data[$field] = $_POST[$field];
      }
      
      $order_status->save();
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('doc' => 'order_statuses.php'), true, array('order_status_id')));
      exit;
    }
  }
  
  if (isset($_POST['delete'])) {

    $order_status->delete();
    
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('doc' => 'order_statuses.php'), true, array('order_status_id')));
    exit();
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo !empty($order_status->data['id']) ? $system->language->translate('title_edit_order_status', 'Edit Order Status') : $system->language->translate('title_create_new_order_status', 'Create New Order Status'); ?></h1>
<?php echo $system->functions->form_draw_form_begin('order_status_form', 'post'); ?>
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
      <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_checkbox('is_sale', '1', empty($_POST['is_sale']) ? '0' : '1'); ?> <?php echo $system->language->translate('text_is_sale', 'Is sale');?></td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_checkbox('notify', '1', empty($_POST['notify']) ? '0' : '1'); ?> <?php echo $system->language->translate('text_notify_customer', 'Notify customer');?></td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"'); ?> <?php echo (isset($order_status->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"') : false; ?></td>
    </tr>
  </table>
<?php echo $system->functions->form_draw_form_end(); ?>