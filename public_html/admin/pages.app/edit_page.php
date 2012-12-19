<?php

  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'page.inc.php');
  
  if (isset($_GET['pages_id'])) {
    $pages = new ctrl_pages($_GET['pages_id']);
    
    if (!$_POST) {
      foreach ($pages->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
  } else {
    $pages = new ctrl_pages();
  }
  
  if (isset($_POST['save'])) {

    if (empty($_POST['title'])) $system->notices->add('errors', $system->language->translate('error_missing_title', 'You must enter a title.'));
    
    if (empty($_POST['dock_menu'])) $_POST['dock_menu'] = 0;
    if (empty($_POST['dock_support'])) $_POST['dock_support'] = 0;
	if (empty($_POST['status'])) $_POST['status'] = 0;
    
    if (!$system->notices->get('errors')) {
    
      $fields = array(
        'status',
        'title',
        'content',
        'dock_menu',
        'dock_support',
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
  
  $system->document->snippets['head_tags']['ckeditor'] = '<script type="text/javascript" src="'. WS_DIR_EXT .'ckeditor/ckeditor.js"></script>' . PHP_EOL
                                                       . ' <script type="text/javascript" src="'. WS_DIR_EXT .'ckeditor/adapters/jquery.js"></script>' . PHP_EOL
                                                       . ' <script type="text/javascript">' . PHP_EOL
                                                       . '   $(document).ready(function() {' . PHP_EOL
                                                       . '     $("textarea[name^=content]").ckeditor({' . PHP_EOL
                                                       . '       toolbar: [' . PHP_EOL
                                                       . '         ["Source", "-", "DocProps", "Preview", "Print", "-", "Templates", "Maximize", "ShowBlocks"],' . PHP_EOL
                                                       . '         ["NumberedList", "BulletedList", "-", "Outdent", "Indent", "-", "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"],' . PHP_EOL
                                                       . '         ["Link", "Unlink", "Anchor"],' . PHP_EOL
                                                       . '         ["Image", "Table", "HorizontalRule", "Smiley", "SpecialChar", "PageBreak"],' . PHP_EOL
                                                       . '         ["Format", "Font", "FontSize"],' . PHP_EOL
                                                       . '         ["TextColor", "BGColor"],' . PHP_EOL
                                                       . '         ["Bold", "Italic", "Underline", "Strike", "Subscript", "Superscript", "-", "RemoveFormat"],' . PHP_EOL
                                                       . ' 	    ],' . PHP_EOL
                                                       . '       entities: false,' . PHP_EOL
                                                       . '       enterMode: CKEDITOR.ENTER_P,' . PHP_EOL
                                                       . '       shiftEnterMode: CKEDITOR.ENTER_BR' . PHP_EOL
                                                       . '     });' . PHP_EOL
                                                       . '   });' . PHP_EOL
                                                       . '</script>' . PHP_EOL;

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle;" style="margin-right: 10px;" /><?php echo !empty($pages->data['id']) ? $system->language->translate('title_edit_page', 'Edit Page') : $system->language->translate('title_create_new_pages', 'Create New Page'); ?></h1>
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
  echo $system->functions->form_draw_regional_input_field($language_code, 'title['. $language_code .']', (isset($_POST['title'][$language_code]) ? $_POST['title'][$language_code] : ''), 'text', 'style="width: 360px"');
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
  echo $system->functions->form_draw_regional_textarea($language_code, 'content['. $language_code .']', (isset($_POST['content'][$language_code]) ? $_POST['content'][$language_code] : ''), 'style="width: 720px; height: 400px;"');
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
  echo $system->functions->form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', (isset($_POST['head_title'][$language_code]) ? $_POST['head_title'][$language_code] : ''), 'text', 'style="width: 360px;"');
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
  echo $system->functions->form_draw_regional_input_field($language_code, 'meta_description['. $language_code .']', (isset($_POST['meta_description'][$language_code]) ? $_POST['meta_description'][$language_code] : ''), 'text', 'style="width: 360px;"');
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
  echo $system->functions->form_draw_regional_input_field($language_code, 'meta_keywords['. $language_code .']', (isset($_POST['meta_keywords'][$language_code]) ? $_POST['meta_keywords'][$language_code] : ''), 'text', 'style="width: 360px;"');
  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_dock', 'Dock'); ?></strong><br />
        <?php echo $system->functions->form_draw_checkbox('dock_menu', '1', isset($_POST['dock_menu']) ? $_POST['dock_menu'] : '0'); ?> <?php echo $system->language->translate('text_dock_in_dock_menu', 'Dock in site menu'); ?><br />
        <?php echo $system->functions->form_draw_checkbox('dock_support', '1', isset($_POST['dock_support']) ? $_POST['dock_support'] : '0'); ?> <?php echo $system->language->translate('text_dock_in_support_page', 'Dock in support page'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_priority', 'Priority'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('priority', (isset($_POST['priority']) ? $_POST['priority'] : '0'), 'text', 'style="width: 50px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"'); ?> <?php echo (isset($pages->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"') : false; ?></td>
    </tr>
  </table>
<?php echo $system->functions->form_draw_form_end(); ?>