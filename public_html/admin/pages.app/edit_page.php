<?php
  
  if (isset($_GET['pages_id'])) {
    $pages = new ctrl_page($_GET['pages_id']);
    
    if (!$_POST) {
      foreach ($pages->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
  } else {
    $pages = new ctrl_page();
  }
  
  if (isset($_POST['save'])) {
    
    if (empty($_POST['title'])) $system->notices->add('errors', $system->language->translate('error_missing_title', 'You must enter a title.'));
    if (empty($_POST['dock'])) $system->notices->add('errors', $system->language->translate('error_missing_dock', 'You must select a dock.'));
    
    if (empty($_POST['status'])) $_POST['status'] = 0;
    
    if (!$system->notices->get('errors')) {
    
      $fields = array(
        'status',
        'title',
        'content',
        'dock',
        'priority',
        'head_title',
        'meta_description',
        'meta_keywords',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $pages->data[$field] = $_POST[$field];
      }
      
      $pages->save();
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('doc' => 'pages.php'), true, array('pages_id')));
      exit;
    }
  }
  
  if (isset($_POST['delete'])) {

    $pages->delete();
    
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('doc' => 'pages.php'), true, array('page_id')));
    exit();
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo !empty($pages->data['id']) ? $system->language->translate('title_edit_page', 'Edit Page') : $system->language->translate('title_create_new_pages', 'Create New Page'); ?></h1>
  <?php echo $system->functions->form_draw_form_begin('pages_form', 'post'); ?>
  <table>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_status', 'Status'); ?></strong><br />
      <?php echo $system->functions->form_draw_checkbox('status', '1', (isset($_POST['status'])) ? $_POST['status'] : '1'); ?> <?php echo $system->language->translate('title_published', 'Published'); ?></td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap">
        <strong><?php echo $system->language->translate('title_title', 'Title'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'title['. $language_code .']', true, 'style="width: 360px"');
  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_content', 'Content'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_textarea($language_code, 'content['. $language_code .']', true, 'style="width: 720px; height: 400px;"');
  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_head_title', 'Head Title'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', true, 'style="width: 360px;"');
  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_meta_description', 'Meta Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'meta_description['. $language_code .']', true, 'style="width: 360px;"');
  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_meta_keywords', 'Meta Keywords'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'meta_keywords['. $language_code .']', true, 'style="width: 360px;"');
  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_dock', 'Dock'); ?></strong><br />
        <?php echo $system->functions->form_draw_checkbox('dock[]', 'menu', (isset($_POST['dock']) && in_array('menu', $_POST['dock'])) ? 'menu' : '0'); ?> <?php echo $system->language->translate('text_dock_in_dock_menu', 'Dock in site menu'); ?><br />
        <?php echo $system->functions->form_draw_checkbox('dock[]', 'customer_service', (isset($_POST['dock']) && in_array('customer_service', $_POST['dock'])) ? 'customer_service' : ''); ?> <?php echo $system->language->translate('text_dock_in_customer_service', 'Dock in customer service'); ?><br />
        <?php echo $system->functions->form_draw_checkbox('dock[]', 'information', (isset($_POST['dock']) && in_array('information', $_POST['dock'])) ? 'information' : '0'); ?> <?php echo $system->language->translate('text_dock_in_information', 'Dock in information'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_priority', 'Priority'); ?></strong><br />
        <?php echo $system->functions->form_draw_input('priority', true, 'text', 'style="width: 50px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($pages->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></td>
    </tr>
  </table>
<?php echo $system->functions->form_draw_form_end(); ?>