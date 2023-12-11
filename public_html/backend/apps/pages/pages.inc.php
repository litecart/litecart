<?php

  if (empty($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  if (empty($_GET['parent_id']) || !is_numeric($_GET['parent_id'])) {
    $_GET['parent_id'] = 0;
  }

  if (empty($_GET['expanded'])) {
    $_GET['expanded'] = [];
  }

  document::$snippets['title'][] = language::translate('title_pages', 'Pages');

  breadcrumbs::add(language::translate('title_pages', 'Pages'));

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {

      if (empty($_POST['pages'])) {
        throw new Exception(language::translate('error_must_select_pages', 'You must select pages'));
      }

      foreach ($_POST['pages'] as $page_id) {
        $page = new ent_page($page_id);
        $page->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $page->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['move'])) {

    try {

      if (empty($_POST['pages']) && empty($_POST['pages'])) {
        throw new Exception(language::translate('error_must_select_pages', 'You must select pages'));
      }

      if (isset($_POST['page_id']) && $_POST['page_id'] == '') {
        throw new Exception(language::translate('error_must_select_destination', 'You must select a destination'));
      }

      if (isset($_POST['page_id']) && isset($_POST['pages']) && in_array($_POST['page_id'], $_POST['pages'])) {
        throw new Exception(language::translate('error_cant_move_page_to_itself', 'You can\'t move a page to itself'));
      }

      if (!empty($_POST['pages'])) {
        foreach ($_POST['pages'] as $page_id) {
          $page = new ent_page($page_id);
          $page->data['parent_id'] = $_POST['page_id'];
          $page->save();
        }
        notices::add('success', sprintf(language::translate('success_moved_d_pages', 'Moved %d pages'), count($_POST['pages'])));
      }

      header('Location: '. document::ilink(null, ['page_id' => $_POST['page_id']]));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {

      if (empty($_POST['pages'])) {
        throw new Exception(language::translate('error_must_select_pages', 'You must select pages'));
      }

      foreach ($_POST['pages'] as $page_id) {
        $page = new ent_page($page_id);
        $page->delete();
      }

      notices::add('success', sprintf(language::translate('success_deleted_d_pages', 'Deleted %d pages'), count($_POST['pages'])));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $dock_options = [
    '' => '-- '. language::translate('title_all', 'All') .' --',
    'menu' => language::translate('title_store_menu', 'Store Menu'),
    'information' => language::translate('title_information', 'Information'),
  ];
?>
<style>
table tbody .toggle {
  width: 30px;
  display: inline-block;
}
</style>


<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_pages', 'Pages'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_button_link(document::ilink(__APP__.'/edit_page'), language::translate('title_create_new_page', 'Create New Page'), '', 'add'); ?>
  </div>

  <?php echo functions::form_begin('search_form', 'get'); ?>
    <div class="card-filter">
      <div class="expandable"><?php echo functions::form_input_search('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword').'"'); ?></div>
      <div class="max-width: max-content;"><?php echo functions::form_select('dock', $dock_options, true); ?></div>
      <?php echo functions::form_button('filter', language::translate('title_search', 'Search'), 'submit'); ?>
    </div>
  <?php echo functions::form_end(); ?>

  <?php echo functions::form_begin('pages_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th class="main" style="padding-inline-start: 30px;"><?php echo language::translate('title_title', 'Title'); ?></th>
          <th><?php echo language::translate('title_store_menu', 'Store Menu'); ?></th>
          <th><?php echo language::translate('title_information', 'Information'); ?></th>
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

    $pages = database::query(
      "select p.*, pi.title, p2.num_subpages
      from ". DB_TABLE_PREFIX ."pages p
      left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      left join (
        select parent_id as id, count(id) as num_subpages
        from ". DB_TABLE_PREFIX ."pages
      ) p2 on (p2.id = p.id)
      where p.id
      ". (empty($_GET['query']) ? "and parent_id = 0" : "") ."
      ". (!empty($sql_where_query) ? "and (". implode(" or ", $sql_where_query) .")" : "") ."
      ". (!empty($_GET['dock']) ? "and find_in_set('". database::input($_GET['dock']) ."', p.dock)" : "") ."
      order by p.priority, pi.title;"
    )->fetch_page($_GET['page'], null, $num_rows, $num_pages);

    foreach ($pages as $page) {
      $page['dock'] = explode(',', $page['dock']);

?>
        <tr class="<?php if (empty($page['status'])) echo 'semi-transparent'; ?>">
          <td><?php echo functions::form_input_checkbox('pages[]', $page['id']); ?></td>
          <td><?php echo functions::draw_fonticon($page['status'] ? 'on' : 'off'); ?></td>
          <td><?php echo $page['id']; ?></td>
          <td><?php echo functions::draw_fonticon('fa-file-o fa-fw'); ?> <a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_page', ['page_id' => $page['id']]); ?>"><?php echo $page['title']; ?></a></td>
          <td class="text-center"><?php if (in_array('menu', $page['dock'])) echo functions::draw_fonticon('fa-check'); ?></td>
          <td class="text-center"><?php if (in_array('information', $page['dock'])) echo functions::draw_fonticon('fa-check'); ?></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_page', ['page_id' => $page['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
<?php
    }

  } else {

    $iterator = function($parent_id, $depth=0) use (&$iterator) {

      $pages_query = database::query(
        "select p.*, pi.title, p2.num_subpages
        from ". DB_TABLE_PREFIX ."pages p
        left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
        left join (
          select parent_id as id, count(id) as num_subpages
          from ". DB_TABLE_PREFIX ."pages
        ) p2 on (p2.id = p.id)
        where p.parent_id = ". (int)$parent_id ."
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

        if ($page['num_subpages']) {
          if (!in_array($page['id'], $_GET['expanded'])) {
            $expanded = array_merge($_GET['expanded'], [$page['id']]);
            $icon = '<a class="toggle" href="'. document::href_ilink(null, ['expanded' => $expanded], true) .'">'. functions::draw_fonticon('fa-plus-square-o fa-fw') . '</a>';

          } else {
            $expanded = array_diff($_GET['expanded'], [$page['id']]);
            $icon = '<a class="toggle" href="'. document::href_ilink(null, ['expanded' => $expanded], true) .'">'. functions::draw_fonticon('fa-minus-square-o fa-fw') .'</a>';
          }

        } else {
          $icon = '<span class="toggle"></span>';
        }
?>
        <tr class="<?php if (empty($page['status'])) echo 'semi-transparent'; ?>">
          <td><?php echo functions::form_input_checkbox('pages[]', $page['id']); ?></td>
          <td><?php echo functions::draw_fonticon($page['status'] ? 'on' : 'off'); ?></td>
          <td><?php echo $page['id']; ?></td>
          <td style="padding-inline-start: <?php echo $depth * 30; ?>px">
            <?php echo $icon; ?>
            <a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_page', ['page_id' => $page['id']]); ?>"><?php echo $page['title']; ?></a>
          </td>
          <td class="text-center"><?php if (in_array('menu', $page['dock'])) echo functions::draw_fonticon('fa-check'); ?></td>
          <td class="text-center"><?php if (in_array('information', $page['dock'])) echo functions::draw_fonticon('fa-check'); ?></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_page', ['page_id' => $page['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
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

    $num_rows = database::query("select id from ". DB_TABLE_PREFIX ."pages;")->num_rows;
    $num_root_rows = database::query("select id from ". DB_TABLE_PREFIX ."pages where parent_id = 0;")->num_rows;
    $num_pages = $num_root_rows / settings::get('data_table_rows_per_page');
    $iterator(0, 0);
  }
?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="7"><?php echo language::translate('title_pages', 'Pages'); ?>: <?php echo language::number_format($num_rows); ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="card-body">
      <fieldset id="actions">
        <legend><?php echo language::translate('text_with_selected', 'With selected'); ?>:</legend>

        <ul class="list-inline">
          <li>
            <div class="btn-group">
              <?php echo functions::form_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
              <?php echo functions::form_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
            </div>
          </li>
          <li>
            <?php echo functions::form_select_page('page_id', true); ?>
          </li>
          <li>
            <?php echo functions::form_button('move', language::translate('title_move', 'Move'), 'submit', 'onclick="if (!confirm(\''. str_replace("'", "\\\'", language::translate('text_are_you_sure', 'Are you sure?')) .'\')) return false;"'); ?>
          </li>
          <li>
            <?php echo functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(\''. str_replace("'", "\\\'", language::translate('text_are_you_sure', 'Are you sure?')) .'\')) return false;"'); ?>
          </li>
        </ul>
      </fieldset>
    </div>

  <?php echo functions::form_end(); ?>


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
