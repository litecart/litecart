<?php

  if (isset($_GET['tax_class_id'])) {
    $tax_class = new ctrl_tax_class($_GET['tax_class_id']);
  } else {
    $tax_class = new ctrl_tax_class();
  }
  
  if (!$_POST) {
    foreach ($tax_class->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  
  // Save data to database
  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) $system->notices->add('errors', $system->language->translate('error_must_enter_name', 'You must enter a name'));
    
    if (!$system->notices->get('errors')) {
    
      $fields = array(
        'name',
        'description',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $tax_class->data[$field] = $_POST[$field];
      }
      
      $tax_class->save();
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('doc' => 'tax_classes.php'), true, array('tax_class_id')));
      exit;
    }
  }
  
  if (isset($_POST['delete'])) {

    $tax_class->delete();
    
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('doc' => 'tax_classes.php'), true, array('tax_class_id')));
    exit();
  }

?>
  <table width="100%">
    <tr>
      <td><h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo (!empty($tax_class->data['id'])) ? $system->language->translate('title_edit_tax_class', 'Edit Tax Class') : $system->language->translate('title_add_new_tax_class', 'Add New Tax Class'); ?></h1>
        <?php echo $system->functions->form_draw_form_begin(false, 'post', false, true); ?>
        <table>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
              <?php echo $system->functions->form_draw_input_field('name', isset($_POST['name']) ? $_POST['name'] : '', 'text', 'style="width: 175px;"'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_description', 'Description'); ?></strong><br />
              <?php echo $system->functions->form_draw_input_field('description', isset($_POST['description']) ? $_POST['description'] : '', 'text', 'style="width: 360px;"'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($tax_class->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></td>
          </tr>
        </table>
      <?php echo $system->functions->form_draw_form_end(); ?></td>
    </tr>
  </table>