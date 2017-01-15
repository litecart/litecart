<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;

  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {

    if (!empty($_POST['pages'])) {
      foreach ($_POST['pages'] as $key => $value) {
        $currency = new ctrl_page($_POST['pages'][$key]);
        $currency->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $currency->save();
      }
    }

    header('Location: '. document::link());
    exit;
  }
?>
<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_page'), true), language::translate('title_create_new_page', 'Create New Page'), '', 'add'); ?></li>
</ul>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_pages', 'Pages'); ?></h1>

<?php echo functions::form_draw_form_begin('pages_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th></th>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th class="main"><?php echo language::translate('title_title', 'Title'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
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
      <tr class="<?php echo empty($page['status']) ? 'semi-transparent' : null; ?>">
        <td><?php echo functions::form_draw_checkbox('pages['. $page['id'] .']', $page['id']); ?></td>
        <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($page['status']) ? '#99cc66' : '#ff6666') .';"'); ?></td>
        <td><?php echo $page['id']; ?></td>
        <td><a href="<?php echo document::href_link('', array('doc' => 'edit_page', 'pages_id' => $page['id']), true); ?>"><?php echo $page['title']; ?></a></td>
        <td class="text-right"><a href="<?php echo document::href_link('', array('doc' => 'edit_page', 'pages_id' => $page['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
      </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="7"><?php echo language::translate('title_pages', 'Pages'); ?>: <?php echo database::num_rows($pages_query); ?></td>
      </tr>
    </tfoot>
  </table>

  <p class="btn-group">
    <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
    <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>

<?php echo functions::draw_pagination(ceil(database::num_rows($pages_query)/settings::get('data_table_rows_per_page'))); ?>