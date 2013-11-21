<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  
  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {
  
    if (!empty($_POST['countries'])) {
      foreach ($_POST['countries'] as $key => $value) $_POST['countries'][$key] = database::input($value);
      database::query(
        "update ". DB_TABLE_COUNTRIES ."
        set status = '". ((!empty($_POST['enable'])) ? 1 : 0) ."'
        where id in ('". implode("', '", $_POST['countries']) ."');"
      );
    }
    
    header('Location: '. document::link());
    exit;
  }
?>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_country'), true), language::translate('title_add_new_country', 'Add New Country'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo language::translate('title_countries', 'Countries'); ?></h1>

<?php echo functions::form_draw_form_begin('countries_form', 'post'); ?>

  <table width="100%" align="center" class="dataTable">
    <tr class="header">
      <th><?php echo functions::form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
      <th nowrap="nowrap" align="left"><?php echo language::translate('title_id', 'ID'); ?></th>
      <th nowrap="nowrap" align="left"><?php echo language::translate('title_code', 'Code'); ?></th>
      <th nowrap="nowrap" align="left" width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
      <th nowrap="nowrap" align="left"><?php echo language::translate('title_zones', 'Zones'); ?></th>
      <th>&nbsp;</th>
    </tr>
<?php

  $countries_query = database::query(
    "select * from ". DB_TABLE_COUNTRIES ."
    order by status desc, name asc;"
  );

  if (database::num_rows($countries_query) > 0) {
    
  // Jump to data for current page
    if ($_GET['page'] > 1) database::seek($countries_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($country = database::fetch($countries_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
    <tr class="<?php echo $rowclass; ?>"<?php echo $country['status'] ? false : ' style="color: #999;"'; ?>>
      <td nowrap="nowrap"><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/'. (!empty($country['status']) ? 'on.png' : 'off.png') ?>" width="16" height="16" align="absbottom" /> <?php echo functions::form_draw_checkbox('countries['. $country['id'] .']', $country['id']); ?></td>
      <td align="left"><?php echo $country['id']; ?></td>
      <td align="left" nowrap="nowrap"><?php echo $country['iso_code_2']; ?></td>
      <td align="left"><a href="<?php echo document::href_link('', array('doc' => 'edit_country', 'country_code' => $country['iso_code_2']), true); ?>"><?php echo $country['name']; ?></a></td>
      <td align="left"><?php echo database::num_rows(database::query("select id from ". DB_TABLE_ZONES ." where country_code = '". database::input($country['iso_code_2']) ."'")); ?></td>
      <td align="right"><a href="<?php echo document::href_link('', array('doc' => 'edit_country', 'country_code' => $country['iso_code_2']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" alt="<?php echo language::translate('title_edit', 'Edit'); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>" /></a></td>
    </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
    <tr class="footer">
      <td colspan="6" align="left"><?php echo language::translate('title_countries', 'Countries'); ?>: <?php echo database::num_rows($countries_query); ?></td>
    </tr>
  </table>

  <script>
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

  <p><span class="button-set"><?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?> <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?></span></p>

<?php
  echo functions::form_draw_form_end();
  
// Display page links
  echo functions::draw_pagination(ceil(database::num_rows($countries_query)/settings::get('data_table_rows_per_page')));
  
?>