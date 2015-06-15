<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  
  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {
  
    if (!empty($_POST['languages'])) {
      foreach ($_POST['languages'] as $key => $value) {
        $language = new ctrl_language($_POST['languages'][$key]);
        $language->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $language->save();
      }
    }
    
    header('Location: '. document::link());
    exit;
  }
?>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_language'), true), language::translate('title_add_new_language', 'Add New Language'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_languages', 'Languages'); ?></h1>

<?php echo functions::form_draw_form_begin('languages_form', 'post'); ?>

  <table width="100%" align="center" class="dataTable">
    <tr class="header">
      <th><?php echo functions::form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_id', 'ID'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_code', 'Code'); ?></th>
      <th width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_default_language', 'Default Language'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_store_language', 'Store Language'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_priority', 'Priority'); ?></th>
      <th>&nbsp;</th>
    </tr>
<?php
  $languages_query = database::query(
    "select * from ". DB_TABLE_LANGUAGES ."
    order by status desc, priority, name;"
  );

  if (database::num_rows($languages_query) > 0) {
    
    if ($_GET['page'] > 1) database::seek($languages_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($language = database::fetch($languages_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
    <tr class="<?php echo $rowclass . ($language['status'] ? false : ' semi-transparent'); ?>">
      <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($language['status']) ? '#99cc66' : '#ff6666') .';"'); ?> <?php echo functions::form_draw_checkbox('languages['. $language['code'] .']', $language['code']); ?></td>
      <td><?php echo $language['id']; ?></td>
      <td style="text-align: center;"><?php echo $language['code']; ?></td>
      <td><a href="<?php echo document::href_link('', array('doc' => 'edit_language', 'language_code' => $language['code'], 'page' => $_GET['page']), true); ?>"><?php echo $language['name']; ?></a></td>
      <td style="text-align: center;"><?php echo ($language['code'] == settings::get('default_language_code')) ? 'x' : ''; ?></td>
      <td style="text-align: center;"><?php echo ($language['code'] == settings::get('store_language_code')) ? 'x' : ''; ?></td>
      <td style="text-align: right;"><?php echo $language['priority']; ?></td>
      <td style="text-align: right;"><a href="<?php echo document::href_link('', array('doc' => 'edit_language', 'language_code' => $language['code'], 'page' => $_GET['page']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
    </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
    <tr class="footer">
      <td colspan="8"><?php echo language::translate('title_languages', 'Languages'); ?>: <?php echo database::num_rows($languages_query); ?></td>
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
  
  echo functions::draw_pagination(ceil(database::num_rows($languages_query)/settings::get('data_table_rows_per_page')));
?>