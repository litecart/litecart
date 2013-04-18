<?php

  if (isset($_GET['supplier_id'])) {
    $supplier = new ctrl_supplier($_GET['supplier_id']);
  } else {
    $supplier = new ctrl_supplier();
  }
  
  if (!$_POST && isset($supplier)) {
    foreach ($supplier->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  // Save data to database
  if (isset($_POST['save'])) {

    if ($_POST['name'] == '') $system->notices->add('errors', $system->language->translate('error_name_missing', 'You must enter a name.'));
    
    if (!$system->notices->get('errors')) {
    
      if (!isset($_POST['status'])) $_POST['status'] = '0';
    
      $fields = array(
        'name',
        'description',
        'email',
        'phone',
        'link',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $supplier->data[$field] = $_POST[$field];
      }
      
      $supplier->save();
      
      if (is_uploaded_file($_FILES['image']['tmp_name'])) {
        $supplier->save_image($_FILES['image']['tmp_name']);
      }
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('doc' => 'suppliers.php'), array('app')));
      exit;
    }
  }

  // Delete from database
  if (isset($_POST['delete']) && $supplier) {
    
    $supplier->delete();
    
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('doc' => 'suppliers.php'), array('app')));
    exit();
  }

?>
  <table width="100%">
    <tr>
      <td><h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo (empty($supplier->data['id'])) ? $system->language->translate('title_add_new_supplier', 'Add New Supplier') : $system->language->translate('title_edit_supplier', 'Edit Supplier'); ?></h1>
        <?php echo $system->functions->form_draw_form_begin(false, 'post', false, true); ?>
        <table>
          <tr>
            <td align="left" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
                <?php echo $system->functions->form_draw_input('name', true, 'text', ''); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_description', 'description'); ?></strong><br />
                <?php echo $system->functions->form_draw_textarea('description', true, 'style="width: 360px;"'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_email', 'email'); ?></strong><br />
                <?php echo $system->functions->form_draw_email_field('email', true, 'email', ''); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_phone', 'Phone'); ?></strong><br />
                <?php echo $system->functions->form_draw_input('phone', true, 'text', ''); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_link', 'Link'); ?></strong><br />
                <?php echo $system->functions->form_draw_input('link', true, 'text', ''); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($supplier->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></td>
          </tr>
        </table>
      <?php echo $system->functions->form_draw_form_end(); ?></td>
    </tr>
  </table>