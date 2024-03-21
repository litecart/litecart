<?php
  document::$layout = 'ajax';
?>
<style>
#modal-product-picker tbody tr {
  cursor: pointer;
}
</style>

<div id="modal-product-picker" class="modal fade" style="max-width: 980px; display: none;">

  <h2><?php echo language::translate('title_products', 'Products'); ?></h2>

  <div class="modal-body">
    <div class="form-group">
      <?php echo functions::form_input_text('query', true, 'placeholder="'. functions::escape_html(language::translate('title_search', 'Search')) .'" autocomplete="off"'); ?>
    </div>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_stock Options', 'Stock Options'); ?></th>
          <th><?php echo language::translate('title_price', 'Price'); ?></th>
          <th><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
          <th><?php echo language::translate('title_reserved', 'Reserved'); ?></th>
          <th><?php echo language::translate('title_date_created', 'Date Created'); ?></th>
        </tr>
      </thead>
      <tbody />
    </table>
  </div>

</div>

<script>
  var xhr_product_picker = null;
  $('#modal-product-picker input[name="query"]').on('input', function(){

    let $modal = $('#modal-product-picker');

/*
    if ($(this).val() == '') {
      $modal.find('tbody').html('');
      xhr_product_picker = null;
      return;
    }
*/

    xhr_product_picker = $.ajax({
      type: 'get',
      async: true,
      cache: false,
      url: '<?php echo document::ilink('catalog/products.json'); ?>?query=' + $(this).val(),
      dataType: 'json',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error(textStatus + ': ' + errorThrown);
      },
      success: function(json) {

        $('tbody', $modal).html('');

        if (!json) {
          $('tbody', $modal).html(
            '<tr>' +
            '  <td colspan="6"><em><?php echo functions::escape_js(language::translate('text_no_results', 'No results')); ?></em></td>' +
            '</tr>'
          );
        }

        $.each(json, function(i, row){

          let $row = $([
            '<tr>',
            '  <td>' + row.id + '</td>',
            '  <td>' + row.name + '</td>',
            '  <td class="text-center">' + (row.num_stock_options ? row.num_stock_options : '-') + '</td>',
            '  <td class="text-end">' + row.price.formatted + '</td>',
            '  <td class="text-end">' + row.quantity + '</td>',
            '  <td class="text-end">' + row.reserved + '</td>',
            '  <td>' + row.date_created + '</td>',
            '</tr>'
          ].join('\n'));

          $row.data(row);

          console.log($modal.find('tbody').length);
          $modal.find('tbody').append($row);
        });

        if (!$modal.find('tbody tr').length) {
          $modal.find('tbody').html('<tr><td colspan="6"><em><?php echo functions::escape_js(language::translate('text_no_results', 'No results')); ?></em></td></tr>');
        }
      },
    });
  }).trigger('input').focus();

  $('#modal-product-picker tbody').on('click', 'td', function() {

    var $tr = $(this).closest('tr'),
      callback = $.featherlight.current().$currentTarget.data('callback'),
      expand = <?php echo (isset($_GET['collect']) && array_intersect(['price', 'stock_option'], $_GET['collect'])) ? 'true' : 'false'; ?>,
      product = $tr.data();

    if (expand || $tr.data('stock_option')) {
      callback = function(product){
        $.featherlight('<?php echo document::ilink(__APP__.'/product_picker_configure', ['callback' => @$_GET['callback']]);?>&product_id='+ product.id);
      }
    }

    if (callback) {
      if (typeof callback == 'function') {
        callback(product);
      } else {
        window[callback](product);
      }
    } else if ($.featherlight.current().$currentTarget[0].closest('.input-group')) {
      let field = $.featherlight.current().$currentTarget[0].closest('.input-group');
      $(field).find(':input').val(product.id).trigger('change');
      $(field).find('.id').text(product.id);
      $(field).find('.name').text(product.name);
      $.featherlight.close();
    }
  });
</script>