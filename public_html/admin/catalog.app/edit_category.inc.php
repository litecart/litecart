<?php
  
  if (isset($_GET['category_id'])) {
    $category = new ctrl_category($_GET['category_id']);
  } else {
    $category = new ctrl_category();
  }
  
  if (!$_POST && isset($category)) {
    foreach ($category->data as $key => $value) {
      $_POST[$key] = $value;
    }
    if (!empty($_GET['parent_id'])) $_POST['parent_id'] = $_GET['parent_id'];
  }
  
  // Save data to database
  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) $system->notices->add('errors', $system->language->translate('error_must_enter_name', 'You must enter a name'));
    
    if (!empty($_POST['code']) && $system->database->num_rows($system->database->query("select id from ". DB_TABLE_CATEGORIES ." where id != '". (isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0) ."' and code = '". $system->database->input($_POST['code']) ."' limit 1;"))) $system->notices->add('errors', $system->language->translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
    
    if (!$system->notices->get('errors')) {
    
      if (!isset($_POST['status'])) $_POST['status'] = '0';
    
      $fields = array(
        'status',
        'parent_id',
        'code',
        'image',
        'name',
        'short_description',
        'description',
        'keywords',
        'head_title',
        'h1_title',
        'meta_description',
        'meta_keywords',
        'priority',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $category->data[$field] = $_POST[$field];
      }
      
      $category->save();
      
      if (is_uploaded_file($_FILES['image']['tmp_name'])) {
        $category->save_image($_FILES['image']['tmp_name']);
      }
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('doc' => 'catalog', 'category_id' => $_POST['parent_id']), array('app')));
      exit;
    }
  }

  // Delete from database
  if (isset($_POST['delete']) && $category) {
    
    $category->delete();
    
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('doc' => 'catalog', 'category_id' => $_POST['parent_id']), array('app')));
    exit();
  }
  
  $system->document->snippets['head_tags']['jquery-tabs'] = '<script src="'. WS_DIR_EXT .'jquery/jquery.tabs.js"></script>';

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo (empty($category->data['id'])) ? $system->language->translate('title_add_new_category', 'Add New Category') : $system->language->translate('title_edit_category', 'Edit Category') .': '. $category->data['name'][$system->language->selected['code']]; ?></h1>

<?php
  if (!empty($category->data['image'])) {
    echo '<p><img src="'. $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $category->data['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 150, 150) .'" /></p>';
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
              <label><?php echo $system->functions->form_draw_checkbox('status', '1', (isset($_POST['status'])) ? $_POST['status'] : '1'); ?> <?php echo $system->language->translate('title_published', 'Published'); ?></label></td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, '');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_code', 'Code'); ?></strong><br />
              <?php echo $system->functions->form_draw_input('code', true, 'text', ''); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_parent_category', 'Parent Category'); ?></strong><br />
              <?php echo $system->functions->form_draw_categories_list('parent_id', true); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_keywords', 'Keywords'); ?></strong><br />
              <?php echo $system->functions->form_draw_input('keywords', true, 'text', 'style="width: 360px;"'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo ((isset($category->data['image']) && $category->data['image'] != '') ? $system->language->translate('title_new_image', 'New Image') : $system->language->translate('title_image', 'Image')); ?></strong><br />
            <?php echo $system->functions->form_draw_file_field('image', ''); ?><?php if (isset($category->data['image']) && $category->data['image'] != '') echo '<br />' . PHP_EOL . $category->data['image']; ?></td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_priority', 'Priority'); ?></strong><br />
              <?php echo $system->functions->form_draw_input('priority', true, 'text', 'style="width: 50px;"'); ?>
            </td>
          </tr>
          <?php if (isset($category->data['id'])) { ?>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_date_updated', 'Date Updated'); ?></strong><br />
              <?php echo strftime('%e %b %Y %H:%M', strtotime($category->data['date_updated'])); ?></td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_date_created', 'Date Created'); ?></strong><br />
              <?php echo strftime('%e %b %Y %H:%M', strtotime($category->data['date_created'])); ?></td>
          </tr>
          <?php } ?>
        </table>
      </div>
    
      <div id="tab-information">
        <table>
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
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_short_description', 'Short Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'short_description['. $language_code .']', true, 'style="width: 360px;"');  $use_br = true;
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
  echo $system->functions->form_draw_regional_wysiwyg_field($language_code, 'description['. $language_code .']', true, 'style="width: 360px; height: 240px;"');  $use_br = true;
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
  echo $system->functions->form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', true, '');
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
        </table>
      </div>
    </div>
  </div>
  
  <?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($category->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  
<?php echo $system->functions->form_draw_form_end(); ?>