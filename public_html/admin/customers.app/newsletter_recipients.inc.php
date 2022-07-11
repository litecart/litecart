<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  if (isset($_POST['add'])) {

    try {
      if (empty($_POST['recipients'])) throw new Exception(language::translate('error_must_provide_recipients', 'You must provide recipients'));

      $added = 0;
      foreach (preg_split('#\R+#', $_POST['recipients']) as $recipient) {
        if (!functions::validate_email($recipient)) continue;

        database::query(
          "insert ignore into ". DB_TABLE_PREFIX ."newsletter_recipients
          (email, date_created)
          values ('". database::input($recipient) ."', '". date('Y-m-d H:i:s') ."');"
        );

        if (database::affected_rows()) $added++;
      }

      notices::add('success', strtr(language::translate('success_added_n_new_recipients', 'Added %n new recipients'), ['%n' => $added]));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($_POST['recipients'])) throw new Exception(language::translate('error_must_select_recipients', 'You must select recipients'));

      database::query(
        "delete from ". DB_TABLE_PREFIX ."newsletter_recipients
        where id in ('". implode("', '", database::input($_POST['recipients'])) ."');"
      );

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (!empty($_GET['action']) && $_GET['action'] == 'export') {

    ob_clean();

    header('Content-Type: text/plain; charset='. language::$selected['code']);

    $recipients_query = database::query(
      "select email from ". DB_TABLE_PREFIX ."newsletter_recipients
      where id
      ". (!empty($_GET['query']) ? "c.email like '%". database::input($_GET['query']) ."%'" : "") ."
      order by date_created desc;"
    );

    while ($recipient = database::fetch($recipients_query)) {
      echo $recipient['email'] . PHP_EOL;
    }

    exit;
  }

// Table Rows
  $recipients = [];

  $recipients_query = database::query(
    "select *, concat(firstname, ' ', lastname) as name from ". DB_TABLE_PREFIX ."newsletter_recipients
    ". (!empty($_GET['query']) ? "where email like '%". database::input($_GET['query']) ."%'" : "") ."
    order by date_created desc;"
  );

  if ($_GET['page'] > 1) database::seek($recipients_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

  $page_items = 0;
  while ($recipient = database::fetch($recipients_query)) {
    $recipients[] = $recipient;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($recipients_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));

  functions::draw_lightbox();
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <div class="panel-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_newletter_recipients', 'Newsletter Recipients'); ?>
    </div>
  </div>

  <div class="panel-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_button('add_recipient', language::translate('title_add_new_recipient', 'Add New Recipient'), 'button', '', 'add'); ?></li>
      <li><?php echo functions::form_draw_link_button(document::link(null, ['action' => 'export']), language::translate('title_export', 'Export'), 'target="_blank"'); ?></li>
    </ul>
  </div>

  <?php echo functions::form_draw_form_begin('search_form', 'get'); ?>
    <?php echo functions::form_draw_hidden_field('app', true); ?>
    <?php echo functions::form_draw_hidden_field('doc', true); ?>
    <div class="panel-filter">
      <div class="expandable"><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"'); ?></div>
      <div><?php echo functions::form_draw_button('filter', language::translate('title_search', 'Search'), 'submit'); ?></div>
    </div>
  <?php echo functions::form_draw_form_end(); ?>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('recipients_form', 'post'); ?>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
            <th><?php echo language::translate('title_id', 'ID'); ?></th>
            <th class="main"><?php echo language::translate('title_email', 'Email'); ?></th>
            <th><?php echo language::translate('title_name', 'Name'); ?></th>
            <th><?php echo language::translate('title_client_ip', 'Client IP'); ?></th>
            <th class="text-center"><?php echo language::translate('title_date_registered', 'Date Registered'); ?></th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($recipients as $recipient) { ?>
          <tr>
            <td><?php echo functions::form_draw_checkbox('recipients[]', $recipient['id']); ?></td>
            <td><?php echo $recipient['id']; ?></td>
            <td><?php echo $recipient['email']; ?></td>
            <td><?php echo $recipient['name']; ?></td>
            <td><?php echo $recipient['client_ip']; ?></td>
            <td class="text-end"><?php echo language::strftime(language::$selected['format_datetime'], strtotime($recipient['date_created'])); ?></td>
          </tr>
          <?php } ?>
        </tbody>

        <tfoot>
          <tr>
            <td colspan="5"><?php echo language::translate('title_recipients', 'Customers'); ?>: <?php echo $num_rows; ?></td>
          </tr>
        </tfoot>
      </table>

      <div class="panel-body">
        <div class="btn-group">
          <?php echo functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', '', 'delete'); ?>
        </div>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <div class="panel-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
</div>

<div id="modal-add-recipients" class="modal fade" style="width: 640px; display: none;">
  <?php echo functions::form_draw_form_begin('recipients_form', 'post'); ?>

    <div class="form-group">
      <label><?php echo language::translate('title_recipients', 'Recipients'); ?></label>
      <?php echo functions::form_draw_textarea('recipients', '', 'style="height: 480px;"'); ?>
    </div>

    <?php echo functions::form_draw_button('add', language::translate('title_add', 'Add'), 'submit', 'class="btn btn-default btn-block"'); ?>

  <?php echo functions::form_draw_form_end(); ?>
</div>

<script>
  $('button[name="add_recipient"]').click(function(){
    $.featherlight('#modal-add-recipients');
    $('textarea[name="recipients"]').attr('placeholder', 'user@email.com\nanother@email.com');
  })
</script>
