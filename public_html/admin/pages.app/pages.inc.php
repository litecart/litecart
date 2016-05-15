<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_page'), true), language::translate('title_create_new_page', 'Create New Page'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_pages', 'Pages'); ?></h1>

<?php echo functions::form_draw_form_begin('pages_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle'); ?></th>
    <th></th>
    <th><?php echo language::translate('title_id', 'ID'); ?></th>
    <th width="100%"><?php echo language::translate('title_title', 'Title'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php
  $pages_query = database::query(
    "select p.*, pi.title from ". DB_TABLE_PAGES ." p
    left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
    order by p.priority, pi.title;"
  );

  if (database::num_rows($pages_query) > 0) {

    if ($_GET['page'] > 1) database::seek($pages_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($page = database::fetch($pages_query)) {
?>
  <tr class="row<?php echo !$page['status'] ? ' semi-transparent' : null; ?>">
    <td><?php echo functions::form_draw_checkbox('delivery_statuses['. $page['id'] .']', $page['id']); ?></td>
    <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($page['status']) ? '#99cc66' : '#ff6666') .';"'); ?></td>
    <td><?php echo $page['id']; ?></td>
    <td><a href="<?php echo document::href_link('', array('doc' => 'edit_page', 'pages_id' => $page['id']), true); ?>"><?php echo $page['title']; ?></a></td>
    <td style="text-align: right;"><a href="<?php echo document::href_link('', array('doc' => 'edit_page', 'pages_id' => $page['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
  </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="6"><?php echo language::translate('title_pages', 'Pages'); ?>: <?php echo database::num_rows($pages_query); ?></td>
  </tr>
</table>

<script>
  $(".dataTable .checkbox-toggle").click(function() {
    $(this).closest("form").find(":checkbox").each(function() {
      $(this).attr('checked', !$(this).attr('checked'));
    });
    $(".dataTable .checkbox-toggle").attr("checked", true);
  });

  $('.dataTable tr').click(function(event) {
    if ($(event.target).is('input:checkbox')) return;
    if ($(event.target).is('a, a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });
</script>

<?php
  echo functions::form_draw_form_end();

  echo functions::draw_pagination(ceil(database::num_rows($pages_query)/settings::get('data_table_rows_per_page')));
?>