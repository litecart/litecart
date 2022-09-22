<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['parent_id']) || !is_numeric($_GET['parent_id'])) $_GET['parent_id'] = 0;
  if (empty($_GET['expanded'])) $_GET['expanded'] = [];

  document::$snippets['title'][] = language::translate('title_pages', 'Pages');

  breadcrumbs::add(language::translate('title_pages', 'Pages'));

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['pages'])) throw new Exception(language::translate('error_must_select_pages', 'You must select pages'));

      foreach ($_POST['pages'] as $page_id) {
        $page = new ent_page($page_id);
        $page->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $page->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['move'])) {

    try {
      if (empty($_POST['pages']) && empty($_POST['pages'])) throw new Exception(language::translate('error_must_select_pages', 'You must select pages'));
      if (isset($_POST['page_id']) && $_POST['page_id'] == '') throw new Exception(language::translate('error_must_select_destination', 'You must select a destination'));
      if (isset($_POST['page_id']) && isset($_POST['pages']) && in_array($_POST['page_id'], $_POST['pages'])) throw new Exception(language::translate('error_cant_move_page_to_itself', 'You can\'t move a page to itself'));

      if (!empty($_POST['pages'])) {
        foreach ($_POST['pages'] as $page_id) {
          $page = new ent_page($page_id);
          $page->data['parent_id'] = $_POST['page_id'];
          $page->save();
        }
        notices::add('success', sprintf(language::translate('success_moved_d_pages', 'Moved %d pages'), count($_POST['pages'])));
      }

      header('Location: '. document::link(WS_DIR_ADMIN, ['page_id' => $_POST['page_id']], true));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($_POST['pages'])) throw new Exception(language::translate('error_must_select_pages', 'You must select pages'));

      foreach ($_POST['pages'] as $page_id) {
        $page = new ent_page($page_id);
        $page->delete();
      }

      notices::add('success', sprintf(language::translate('success_deleted_d_pages', 'Deleted %d pages'), count($_POST['pages'])));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $dock_options = [
    ['-- '. language::translate('title_all', 'All') .' --', ''],
    [language::translate('title_site_menu', 'Site Menu'), 'menu'],
    [language::translate('title_customer_service', 'Customer Service'), 'customer_service'],
    [language::translate('title_information', 'Information'), 'information'],
  ];
?>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_pages', 'Pages'); ?>
    </div>
  </div>

  <div class="card-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, ['doc' => 'edit_page'], true), language::translate('title_create_new_page', 'Create New Page'), '', 'add'); ?></li>
    </ul>
  </div>

  <?php echo functions::form_draw_form_begin('search_form', 'get'); ?>
    <?php echo functions::form_draw_hidden_field('app', true); ?>
    <?php echo functions::form_draw_hidden_field('doc', true); ?>
    <div class="card-filter">
      <div class="expandable"><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword').'"'); ?></div>
      <div><?php echo functions::form_draw_select_field('dock', $dock_options, true); ?></div>
      <div><?php echo functions::form_draw_button('filter', language::translate('title_search', 'Search'), 'submit'); ?></div>
    </div>
  <?php echo functions::form_draw_form_end(); ?>

  <?php echo functions::form_draw_form_begin('pages_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th class="main"><?php echo language::translate('title_title', 'Title'); ?></th>
          <th><?php echo language::translate('title_site_menu', 'Site Menu'); ?></th>
          <th><?php echo language::translate('title_information', 'Information'); ?></th>
          <th><?php echo language::translate('title_customer_service', 'Customer Service'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
<?php
  if (!empty($_GET['query'])) {
    $sql_where_query = [
      "p.id = '". database::input($_GET['query']) ."'",
      "pi.title like '%". database::input($_GET['query']) ."%'",
      "pi.content like '%". database::input($_GET['query']) ."%'",
    ];

    $pages_query = database::query(
      "select p.*, pi.title from ". DB_TABLE_PREFIX ."pages p
      left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      where p.id
      ". (empty($_GET['query']) ? "and parent_id = 0" : "") ."
      ". (!empty($sql_where_query) ? "and (". implode(" or ", $sql_where_query) .")" : "") ."
      ". (!empty($_GET['dock']) ? "and find_in_set('". database::input($_GET['dock']) ."', p.dock)" : "") ."
      order by p.priority, pi.title;"
    );

    if (database::num_rows($pages_query) > 0) {

      if ($_GET['page'] > 1) database::seek($pages_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

      $page_items = 0;
      while ($page = database::fetch($pages_query)) {
        $page['dock'] = explode(',', $page['dock']);

        $num_subpages = database::num_rows(
          database::query(
            "select * from ". DB_TABLE_PREFIX ."pages p
            left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
            where parent_id = ". (int)$page['id'] .";"
          )
        );

?>
        <tr class="<?php echo empty($page['status']) ? 'semi-transparent' : null; ?>">
          <td><?php echo functions::form_draw_checkbox('pages[]', $page['id']); ?></td>
          <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($page['status']) ? '#88cc44' : '#ff6644') .';"'); ?></td>
          <td><?php echo $page['id']; ?></td>
          <td><a class="link" href="<?php echo document::href_link('', ['doc' => 'edit_page', 'page_id' => $page['id']], true); ?>"><?php echo $page['title']; ?></a></td>
          <td class="text-center"><?php echo in_array('menu', $page['dock']) ? functions::draw_fonticon('fa-check') : ''; ?></td>
          <td class="text-center"><?php echo in_array('information', $page['dock']) ? functions::draw_fonticon('fa-check') : ''; ?></td>
          <td class="text-center"><?php echo in_array('customer_service', $page['dock']) ? functions::draw_fonticon('fa-check') : ''; ?></td>
          <td><a class="btn btn-default btn-sm" href="<?php echo document::href_link('', ['doc' => 'edit_page', 'page_id' => $page['id']], true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
        </tr>
<?php
        if (++$page_items == settings::get('data_table_rows_per_page')) break;
      }
    }

    $num_rows = database::num_rows($pages_query);
    $num_pages = $num_rows / settings::get('data_table_rows_per_page');

  } else {

    $iterator = function($parent_id, $depth=0) use (&$iterator) {

      $pages_query = database::query(
        "select p.*, pi.title from ". DB_TABLE_PREFIX ."pages p
        left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
        where parent_id = ". (int)$parent_id ."
        ". ((!empty($_GET['dock']) && empty($depth)) ? "and find_in_set('". database::input($_GET['dock']) ."', p.dock)" : "") ."
        order by p.priority, pi.title;"
      );

      if (empty($parent_id)) {
        if ($_GET['page'] > 1) database::seek($pages_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));
        $num_rows = database::num_rows($pages_query);
      }

      $page_items = 0;
      while ($page = database::fetch($pages_query)) {
        $page['dock'] = explode(',', $page['dock']);

        $subpages_query = database::query(
          "select p.*, pi.title from ". DB_TABLE_PREFIX ."pages p
          left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
          where parent_id = ". (int)$page['id'] ."
          order by p.priority, pi.title;"
        );

        if (database::num_rows($subpages_query)) {
          if (!in_array($page['id'], $_GET['expanded'])) {
            $expanded = array_merge($_GET['expanded'], [$page['id']]);
            $icon = '<a href="'. document::href_link(WS_DIR_ADMIN, ['expanded' => $expanded], ['app', 'doc', 'page']) .'">'. functions::draw_fonticon('fa-plus-square-o fa-fw') . '</a>';

          } else {
            $expanded = array_diff($_GET['expanded'], [$page['id']]);
            $icon = '<a href="'. document::href_link(WS_DIR_ADMIN, ['expanded' => $expanded], ['app', 'doc', 'page']) .'">'. functions::draw_fonticon('fa-minus-square-o fa-fw') .'</a>';
          }
        } else {
          $icon = '';
        }
?>
        <tr class="<?php echo empty($page['status']) ? 'semi-transparent' : null; ?>">
          <td><?php echo functions::form_draw_checkbox('pages[]', $page['id']); ?></td>
          <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($page['status']) ? '#88cc44' : '#ff6644') .';"'); ?></td>
          <td><?php echo $page['id']; ?></td>
          <td style="padding-inline-start: <?php echo $depth * 30; ?>px"><a class="link" href="<?php echo document::href_link('', ['doc' => 'edit_page', 'page_id' => $page['id']], true); ?>"><?php echo $page['title']; ?></a> <?php echo $icon; ?></td>
          <td class="text-center"><?php echo in_array('menu', $page['dock']) ? functions::draw_fonticon('fa-check') : ''; ?></td>
          <td class="text-center"><?php echo in_array('information', $page['dock']) ? functions::draw_fonticon('fa-check') : ''; ?></td>
          <td class="text-center"><?php echo in_array('customer_service', $page['dock']) ? functions::draw_fonticon('fa-check') : ''; ?></td>
          <td class="text-end"><a href="<?php echo document::href_link('', ['doc' => 'edit_page', 'page_id' => $page['id']], true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
        </tr>
<?php
        if (in_array($page['id'], $_GET['expanded'])) {
          $iterator($page['id'], $depth + 1);
        }

        if (empty($parent_id)) {
          if (++$page_items == settings::get('data_table_rows_per_page')) break;
        }
      }
    };

    $num_rows = database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."pages"));
    $num_pages = ceil(database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."pages where parent_id = 0;")) / settings::get('data_table_rows_per_page'));
    $iterator(0, 0);
  }
?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="8"><?php echo language::translate('title_pages', 'Pages'); ?>: <?php echo $num_rows; ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="card-body">
      <fieldset id="actions" disabled>
        <legend><?php echo language::translate('text_with_selected', 'With selected'); ?>:</legend>

        <ul class="list-inline">
          <li><?php echo language::translate('text_with_selected', 'With selected'); ?>:</li>
          <li>
            <div class="btn-group">
              <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
              <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
            </div>
          </li>
          <li>
            <div>
              <?php echo functions::form_draw_pages_list('page_id', isset($_POST['page_id']) ? $_POST['page_id'] : ''); ?>
            </div>
          </li>
          <li>
            <div class="btn-group">
              <?php echo functions::form_draw_button('move', language::translate('title_move', 'Move'), 'submit', 'onclick="if (!confirm(\''. str_replace("'", "\\\'", language::translate('text_are_you_sure', 'Are you sure?')) .'\')) return false;"'); ?>
            </div>
          </li>
          <li>
            <div class="btn-group">
              <?php echo functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(\''. str_replace("'", "\\\'", language::translate('text_are_you_sure', 'Are you sure?')) .'\')) return false;"'); ?>
            </div>
          </li>
        </ul>
      </fieldset>
    </div>

  <?php echo functions::form_draw_form_end(); ?>

  <?php if ($num_pages > 1) { ?>
  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>
</div>

<script>
  $('input[name="query"]').keypress(function(e) {
    if (e.which == 13) {
      e.preventDefault();
      $(this).closest('form').submit();
    }
  });

  $('form[name="search_form"] select').change(function(){
    $(this).closest('form').submit();
  });

  $('.data-table :checkbox').change(function() {
    $('#actions').prop('disabled', !$('.data-table :checked').length);
  }).first().trigger('change');
</script>
