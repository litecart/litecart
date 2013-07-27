<?php
  
  if (isset($_GET['manufacturer_id'])) {
    $manufacturer = new ctrl_manufacturer($_GET['manufacturer_id']);
  } else {
    $manufacturer = new ctrl_manufacturer();
  }
  
  if (!$_POST && isset($manufacturer)) {
    foreach ($manufacturer->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  // Save data to database
  if (isset($_POST['save'])) {
    
    if ($_POST['name'] == '') $system->notices->add('errors', $system->language->translate('error_name_missing', 'You must enter a name.'));
    
    if (!empty($_POST['code']) && $system->database->num_rows($system->database->query("select id from ". DB_TABLE_MANUFACTURERS ." where id != '". (isset($_GET['manufacturer_id']) ? (int)$_GET['manufacturer_id'] : 0) ."' and code = '". $system->database->input($_POST['code']) ."' limit 1;"))) $system->notices->add('errors', $system->language->translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
    
    if (!$system->notices->get('errors')) {
    
      if (!isset($_POST['status'])) $_POST['status'] = '0';
      
      if (!empty($_POST['remove_image']) && !empty($manufacturer->data['image'])) {
        $system->functions->image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer->data['image']);
        if (file_exists(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer->data['image'])) unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer->data['image']);
        $manufacturer->data['image'] = '';
      }
    
      $fields = array(
        'status',
        'code',
        'name',
        'short_description',
        'description',
        'keywords',
        'head_title',
        'h1_title',
        'meta_description',
        'meta_keywords',
        'link',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $manufacturer->data[$field] = $_POST[$field];
      }
      
      $manufacturer->save();
      
      if (is_uploaded_file($_FILES['image']['tmp_name'])) {
        $manufacturer->save_image($_FILES['image']['tmp_name']);
      }
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('doc' => 'manufacturers'), array('app')));
      exit;
    }
  }

  // Delete from database
  if (isset($_POST['delete']) && $manufacturer) {
    
    $manufacturer->delete();
    
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('doc' => 'manufacturers'), array('app')));
    exit();
  }
  
  $system->document->snippets['head_tags']['jquery-tabs'] = '<script src="'. WS_DIR_EXT .'jquery/jquery.tabs.js"></script>';

?>

<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo (empty($manufacturer->data['id'])) ? $system->language->translate('title_add_new_manufacturer', 'Add New Manufacturer') : $system->language->translate('title_edit_manufacturer', 'Edit Manufacturer'); ?></h1>

<?php
  if (!empty($manufacturer->data['image'])) {
    echo '<p><img src="'. $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer->data['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 400, 100, 'FIT') .'" /></p>';
  }
?>

<?php echo $system->functions->form_draw_form_begin(false, 'post', false, true); ?>

  <div class="tabs">
  
    <ul class="index">
      <li><a href="#tab-general"><?php echo $system->language->translate('title_general', 'General'); ?></a></li>
      <li><a href="#tab-information"><?php echo $system->language->translate('title_information', 'Information'); ?></a></li>
    </ul>
    
    <div class="content">
      <div id="tab-general">
        <table>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_status', 'Status'); ?></strong><br />
              <label><?php echo $system->functions->form_draw_checkbox('status', '1', true); ?> <?php echo $system->language->translate('title_published', 'Published'); ?></label></td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_code', 'Code'); ?></strong><br />
              <?php echo $system->functions->form_draw_text_field('code', true); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo ((isset($manufacturer->data['image']) && $manufacturer->data['image'] != '') ? $system->language->translate('title_new_image', 'New Image') : $system->language->translate('title_image', 'Image')); ?></strong><br />
            <?php echo $system->functions->form_draw_file_field('image', ''); ?></td>
          </tr>
          <?php if (isset($manufacturer->data['image']) && $manufacturer->data['image'] != '') { ?>
          <tr>
            <td align="left" nowrap="nowrap"><?php echo $manufacturer->data['image']; ?><br />
            <?php echo $system->functions->form_draw_checkbox('remove_image', '1', true); ?> <?php echo $system->language->translate('text_remove_image', 'Remove image'); ?></td>
          </tr>
          <?php } ?>
          <tr>
            <td align="left" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
              <?php echo $system->functions->form_draw_text_field('name', true); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_keywords', 'Keywords'); ?></strong><br />
              <?php echo $system->functions->form_draw_text_field('keywords', true, 'data-size="large"'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_short_description', 'Short Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'short_description['. $language_code .']', true, 'data-size="large"');  $use_br = true;
}
?>
            </td>
          </tr>
        </table>
      </div>
    
      <div id="tab-information">
        <table>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_description', 'Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_wysiwyg_field($language_code, 'description['. $language_code .']', true, 'data-size="large" style="height: 240px;"');  $use_br = true;
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
  echo $system->functions->form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', true, '');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_h1_title', 'H1 Title'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'h1_title['. $language_code .']', true, '');
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
  echo $system->functions->form_draw_regional_input_field($language_code, 'meta_description['. $language_code .']', true, 'data-size="large"');
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
  echo $system->functions->form_draw_regional_input_field($language_code, 'meta_keywords['. $language_code .']', true, 'data-size="large"');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_link', 'Link'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'link['. $language_code .']', true, 'data-size="large"');  $use_br = true;
}
?>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
  
  <?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (!empty($manufacturer->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  
<?php echo $system->functions->form_draw_form_end(); ?>