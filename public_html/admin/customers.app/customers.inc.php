<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;

  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {

    if (!empty($_POST['customers'])) {
      foreach ($_POST['customers'] as $key => $value) {
        $customer = new ctrl_customer($_POST['customers'][$key]);
        $customer->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $customer->save();
      }
    }

    header('Location: '. document::link());
    exit;
  }
?>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_customer'), true), language::translate('title_add_new_customer', 'Add New Customer'), '', 'add'); ?></div>
<div style="float: right; padding-right: 10px;"><?php echo functions::form_draw_form_begin('search_form', 'get', '', false, 'onsubmit="return false;"') . functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"  onkeydown=" if (event.keyCode == 13) location=(\''. document::link('', array(), true, array('page', 'query')) .'&query=\' + this.value)"') . functions::form_draw_form_end(); ?></div>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_customers', 'Customers'); ?></h1>

<?php echo functions::form_draw_form_begin('customers_form', 'post'); ?>
  <table width="100%" class="dataTable">
    <tr class="header">
      <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle'); ?></th>
      <th></th>
      <th><?php echo language::translate('title_id', 'ID'); ?></th>
      <th><?php echo language::translate('title_code', 'Code'); ?></th>
      <th><?php echo language::translate('title_name', 'Name'); ?></th>
      <th width="100%"><?php echo language::translate('title_company', 'Company'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_date_registered', 'Date Registered'); ?></th>
      <th>&nbsp;</th>
    </tr>
<?php
  if (!empty($_GET['query'])) {
    $sql_find = array(
      "id = '". database::input($_GET['query']) ."'",
      "email like '%". database::input($_GET['query']) ."%'",
      "tax_id like '%". database::input($_GET['query']) ."%'",
      "company like '%". database::input($_GET['query']) ."%'",
      "concat(firstname, ' ', lastname) like '%". database::input($_GET['query']) ."%'",
    );
  }

  $customers_query = database::query(
    "select * from ". DB_TABLE_CUSTOMERS ."
    ". ((!empty($sql_find)) ? "where (". implode(" or ", $sql_find) .")" : "") ."
    order by firstname, lastname;"
  );

  if (database::num_rows($customers_query) > 0) {


    if ($_GET['page'] > 1) database::seek($customers_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($customer = database::fetch($customers_query)) {
?>
    <tr class="row<?php echo !$customer['status'] ? ' semi-transparent' : null; ?>">
      <td><?php echo functions::form_draw_checkbox('customers['.$customer['id'].']', $customer['id']); ?></td>
      <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($customer['status']) ? '#99cc66' : '#ff6666') .';"'); ?></td>
      <td><?php echo $customer['id']; ?></td>
      <td><?php echo $customer['code']; ?></td>
      <td><a href="<?php echo document::href_link('', array('doc' => 'edit_customer', 'customer_id' => $customer['id']), true); ?>"><?php echo $customer['firstname'] .' '. $customer['lastname']; ?></a></td>
      <td><?php echo $customer['company']; ?></td>
      <td style="text-align: right;"><?php echo language::strftime(language::$selected['format_datetime'], strtotime($customer['date_created'])); ?></td>
      <td><a href="<?php echo document::href_link('', array('doc' => 'edit_customer', 'customer_id' => $customer['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
    </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
    <tr class="footer">
      <td colspan="8"><?php echo language::translate('title_customers', 'Customers'); ?>: <?php echo database::num_rows($customers_query); ?></td>
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

  <p><span class="button-set"><?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?> <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?></span></p>

<?php echo functions::form_draw_form_end(); ?>

<?php echo functions::draw_pagination(ceil(database::num_rows($customers_query)/settings::get('data_table_rows_per_page'))); ?>