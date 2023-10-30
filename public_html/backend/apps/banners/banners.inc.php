<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_banners', 'Banners');

  breadcrumbs::add(language::translate('title_banners', 'Banners'));

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {

      if (empty($_POST['banners'])) {
        throw new Exception(language::translate('error_must_select_banners', 'You must select banners'));
      }

      foreach (array_keys($_POST['banners']) as $banner_id) {

        $banner = new ent_banner($banner_id);
        $banner->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $banner->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows, Total Number of Rows, Total Number of Pages
  $banners = database::query(
    "select * from ". DB_TABLE_PREFIX ."banners
    where id
    ". (!empty($_GET['keyword']) ? "and find_in_set('". database::input($_GET['keywords']) ."', keywords)" : '') ."
    ". (!empty($_GET['query']) ? "and name like '%". database::input($_GET['query']) ."%'" : '') ."
    order by status desc, name asc;"
  )->fetch_page($_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_banners', 'Banners'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_begin('filter_form', 'get'); ?>
      <ul class="list-inline">
        <li><?php echo functions::form_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'" style="width: 250px;"'); ?></li>
        <li><?php echo functions::form_link_button(document::ilink(__APP__.'/edit_banner'), language::translate('title_create_new_banner', 'Create New Banner'), '', 'add'); ?></li>
      </ul>
    <?php echo functions::form_end(); ?>
  </div>

  <?php echo functions::form_begin('banners_form', 'post'); ?>

    <table class="table table-striped data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_triggers', 'Triggers'); ?></th>
          <th class="text-center"><?php echo language::translate('title_clicks', 'Clicks'); ?></th>
          <th class="text-center"><?php echo language::translate('title_views', 'Views'); ?></th>
          <th class="text-center"><?php echo language::translate('title_ratio', 'Ratio'); ?></th>
          <th class="text-center"><?php echo language::translate('title_valid_from', 'Valid From'); ?></th>
          <th class="text-center"><?php echo language::translate('title_valid_to', 'Valid To'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($banners as $banner) { ?>
        <tr class="<?php echo $banner['status'] ? false : ' semi-transparent'; ?>">
          <td><?php echo functions::form_checkbox('banners[]', $banner['id']); ?></td>
          <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($banner['status']) ? '#99cc66' : '#ff6666') .';"'); ?></td>
          <td><?php echo $banner['id']; ?></td>
          <td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_banner', ['banner_id' => $banner['id']]); ?>"><?php echo $banner['name']; ?></a></td>
          <td><?php echo $banner['keywords']; ?></td>
          <td class="text-end"><?php echo $banner['total_clicks']; ?></td>
          <td class="text-end"><?php echo $banner['total_views']; ?></td>
          <td class="text-end"><?php echo !empty($banner['total_clicks']) ? '1:'.round($banner['total_views']/$banner['total_clicks']) : '-'; ?></td>
          <td class="text-center"><?php echo $banner['date_valid_from'] ? language::strftime(language::$selected['format_datetime'], strtotime($banner['date_valid_from'])) : '-'; ?></td>
          <td class="text-center"><?php echo $banner['date_valid_to'] ? language::strftime(language::$selected['format_datetime'], strtotime($banner['date_valid_to'])) : '-'; ?></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_banner', ['banner_id' => $banner['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="11"><?php echo language::translate('title_banners', 'Banners'); ?>: <?php echo $num_rows; ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="card-body">
      <fieldset id="actions">
        <legend><?php echo language::translate('text_with_selected', 'With selected'); ?></legend>

        <div class="btn-group">
          <?php echo functions::form_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
          <?php echo functions::form_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
        </div>
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
  $('.data-table :checkbox').change(function() {
    $('#actions').prop('disabled', !$('.data-table :checked').length);
  }).first().trigger('change');
</script>