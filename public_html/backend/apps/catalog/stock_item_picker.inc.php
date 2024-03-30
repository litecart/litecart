<?php
  document::$layout = 'ajax';
?>
<style>
#modal-stock-item-picker tbody tr {
  cursor: pointer;
}
</style>

<div id="modal-stock-item-picker" class="modal fade" style="max-width: 980px;">

  <h2><?php echo language::translate('title_stock_items', 'Stock Items'); ?></h2>

  <div class="modal-body">
    <div class="form-group">
      <?php echo functions::form_input_text('query', true, 'placeholder="'. functions::escape_html(language::translate('title_search', 'Search')) .'" autocomplete="off"'); ?>
    </div>

    <div class="form-group results table-responsive">
      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo language::translate('title_id', 'ID'); ?></th>
            <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
            <th><?php echo language::translate('title_sku', 'SKU'); ?></th>
            <th><?php echo language::translate('title_gtin', 'GTIN'); ?></th>
            <th><?php echo language::translate('title_mpn', 'MPN'); ?></th>
            <th><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
            <th><?php echo language::translate('title_date_created', 'Date Created'); ?></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
  var xhr_stock_item_picker = null;
  $('#modal-stock-item-picker input[name="query"]').on('input', function(){

    /*
    if ($(this).val() == '') {
      $('#modal-stock-item-picker tbody').html('');
      xhr_stock_item_picker = null;
      return;
    }
    */

    xhr_stock_item_picker = $.ajax({
      type: 'get',
      async: true,
      cache: false,
      url: '<?php echo document::ilink(__APP__.'/stock_items.json'); ?>?query=' + $(this).val(),
      dataType: 'json',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error(textStatus + ': ' + errorThrown);
      },
      success: function(result) {
        $('#modal-stock-item-picker tbody').html('');
        $.each(result, function(i, item) {

          $row = $('<tr>' +
            '  <td class="id">' + item.id + '</td>' +
            '  <td class="name">' + item.name + '</td>' +
            '  <td class="sku">' + item.sku + '</td>' +
            '  <td class="gtin">' + item.gtin + '</td>' +
            '  <td class="mpn">' + item.mpn + '</td>' +
            '  <td class="quantity text-end">' + item.quantity + '</td>' +
            '  <td class="date-created">' + item.date_created + '</td>' +
            '</tr>'
          );

          $.each(Object.keys(item), function(j, key) {  // Iterate Object.keys() because jQuery.each() doesn't support a property named length
            $row.data(key, item[key]);
          });

          $row.appendTo('#modal-stock-item-picker tbody');
        });

        if ($('#modal-stock-item-picker tbody').html() == '') {
          $('#modal-stock-item-picker tbody').html('<tr><td colspan="6"><em><?php echo functions::escape_js(language::translate('text_no_results', 'No results')); ?></em></td></tr>');
        }
      }
    });
  }).trigger('input').focus();

  $('#modal-stock-item-picker tbody').on('click', 'td', function() {
    <?php if (!empty($_GET['js_callback'])) { ?>
    var row = $(this).closest('tr');

    var id = $(row).find('.id').text();
    if (!id) return;

    var data = $(row).data();

    window['<?php echo addcslashes($_GET['js_callback'], '\''); ?>'](data);
    <?php } ?>

    if ($.featherlight.opened) {
      $.featherlight.close();
    }
  });
</script>