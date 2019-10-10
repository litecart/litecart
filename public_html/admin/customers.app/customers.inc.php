<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['customers'])) throw new Exception(language::translate('error_must_select_customers', 'You must select customers'));

      foreach ($_POST['customers'] as $customer_id) {
        $customer = new ent_customer($customer_id);
        $customer->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $customer->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $customers = array();

  if (!empty($_GET['query'])) {
    $sql_find = array(
      "c.id = '". database::input($_GET['query']) ."'",
      "c.email like '%". database::input($_GET['query']) ."%'",
      "c.tax_id like '%". database::input($_GET['query']) ."%'",
      "c.company like '%". database::input($_GET['query']) ."%'",
      "concat(c.firstname, ' ', c.lastname) like '%". database::input($_GET['query']) ."%'",
    );
  }

  $customers_query = database::query(
    "select c.* from ". DB_TABLE_CUSTOMERS ." c
    where c.id
    ". (!empty($sql_find) ? "and (". implode(" or ", $sql_find) .")" : "") ."
    order by c.firstname, c.lastname;"
  );

  if ($_GET['page'] > 1) database::seek($customers_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

  $page_items = 0;
  while ($customer = database::fetch($customers_query)) {
    $customers[] = $customer;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($customers_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_customers', 'Customers'); ?>
  </div>

  <div class="panel-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, array('doc' => 'edit_customer'), true), language::translate('title_add_new_customer', 'Add New Customer'), '', 'add'); ?></li>
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
    <?php echo functions::form_draw_form_begin('customers_form', 'post'); ?>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
            <th></th>
            <th><?php echo language::translate('title_id', 'ID'); ?></th>
            <th><?php echo language::translate('title_email', 'Email'); ?></th>
            <th><?php echo language::translate('title_name', 'Name'); ?></th>
            <th class="main"><?php echo language::translate('title_company', 'Company'); ?></th>
            <th class="text-center"><?php echo language::translate('title_date_registered', 'Date Registered'); ?></th>
            <th>&nbsp;</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($customers as $customer) { ?>
          <tr class="<?php echo empty($customer['status']) ? 'semi-transparent' : null; ?>">
            <td><?php echo functions::form_draw_checkbox('customers['.$customer['id'].']', $customer['id']); ?></td>
            <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($customer['status']) ? '#88cc44' : '#ff6644') .';"'); ?></td>
            <td><?php echo $customer['id']; ?></td>
            <td><a href="<?php echo document::href_link('', array('doc' => 'edit_customer', 'customer_id' => $customer['id']), true); ?>"><?php echo $customer['email']; ?></a></td>
            <td><?php echo $customer['firstname'] .' '. $customer['lastname']; ?></td>
            <td><?php echo $customer['company']; ?></td>
            <td class="text-right"><?php echo language::strftime(language::$selected['format_datetime'], strtotime($customer['date_created'])); ?></td>
            <td class="text-right"><a href="<?php echo document::href_link('', array('doc' => 'edit_customer', 'customer_id' => $customer['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
          </tr>
          <?php } ?>
        </tbody>

        <tfoot>
          <tr>
            <td colspan="8"><?php echo language::translate('title_customers', 'Customers'); ?>: <?php echo $num_rows; ?></td>
          </tr>
        </tfoot>
      </table>

      <div class="btn-group">
        <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
        <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <div class="panel-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
</div>
