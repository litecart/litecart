<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  
  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {
  
    if (!empty($_POST['slides'])) {
      foreach ($_POST['slides'] as $key => $value) $_POST['slides'][$key] = $system->database->input($value);
      $system->database->query(
        "update ". DB_TABLE_SLIDES ."
        set status = '". ((!empty($_POST['enable'])) ? 1 : 0) ."'
        where id in ('". implode("', '", $_POST['slides']) ."');"
      );
    }
    
    header('Location: '. $system->document->link());
    exit;
  }
?>
<div style="float: right;"><?php echo $system->functions->form_draw_link_button($system->document->link('', array('doc' => 'edit_slide'), true), $system->language->translate('title_add_new_slide', 'Add New Slide'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_slides', 'Slides'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('slides_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_language', 'Language'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_valid_from', 'Valid From'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_valid_to', 'Valid To'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_priority', 'Priority'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php

  $slides_query = $system->database->query(
    "select * from ". DB_TABLE_SLIDES ."
    order by language_code asc, priority asc;"
  );

  if ($system->database->num_rows($slides_query) > 0) {
    
    if ($_GET['page'] > 1) $system->database->seek($slides_query, ($system->settings->get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($slide = $system->database->fetch($slides_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
    
?>
  <tr class="<?php echo $rowclass . ($slide['status'] ? false : ' semi-transparent'); ?>">
    <td nowrap="nowrap"><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/'. (!empty($slide['status']) ? 'on.png' : 'off.png') ?>" width="16" height="16" align="absbottom" /> <?php echo $system->functions->form_draw_checkbox('slides['. $slide['id'] .']', $slide['id']); ?></td>
    <td align="left"><?php echo $slide['id']; ?></td>
    <td align="left"><?php echo $slide['name']; ?></td>
    <td align="left"><?php echo $slide['language_code']; ?></td>
    <td align="center" nowrap="nowrap"><?php echo (date('Y', strtotime($slide['date_valid_from'])) > '1970') ? strftime($system->language->selected['format_datetime'], strtotime($slide['date_valid_from'])) : '-'; ?></td>
    <td align="center" nowrap="nowrap"><?php echo (date('Y', strtotime($slide['date_valid_to'])) > '1970') ? strftime($system->language->selected['format_datetime'], strtotime($slide['date_valid_to'])) : '-'; ?></td>
    <td align="center"><?php echo $slide['priority']; ?></td>
    <td align="right"><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_slide', 'slide_id' => $slide['id']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" alt="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" title="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" /></a></td>
  </tr>
<?php
      if (++$page_items == $system->settings->get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="8" align="left"><?php echo $system->language->translate('title_slides', 'Slides'); ?>: <?php echo $system->database->num_rows($slides_query); ?></td>
  </tr>
</table>

<script type="text/javascript">
  $(".dataTable input[name='checkbox_toggle']").click(function() {
    $(this).closest("form").find(":checkbox").each(function() {
      $(this).attr('checked', !$(this).attr('checked'));
    });
    $(".dataTable input[name='checkbox_toggle']").attr("checked", true);
  });

  $('.dataTable tr').click(function(event) {
    if ($(event.target).is('input:checkbox')) return;
    if ($(event.target).is('a, a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });
</script>

<p><?php echo $system->functions->form_draw_button('enable', $system->language->translate('title_enable', 'Enable'), 'submit'); ?> <?php echo $system->functions->form_draw_button('disable', $system->language->translate('title_disable', 'Disable'), 'submit'); ?></p>

<?php
  echo $system->functions->form_draw_form_end();
  
// Display page links
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($slides_query)/$system->settings->get('data_table_rows_per_page')));
  
?>