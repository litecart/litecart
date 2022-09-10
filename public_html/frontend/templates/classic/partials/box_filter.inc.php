<style>
#box-filter .filter {
  display: grid;
  grid-auto-flow: row;
  grid-gap: 1em;
  grid-template-columns: 1fr;
  margin-bottom: 1em;
}
@media (min-width: 480px) {
  #box-filter .filter {
    grid-auto-flow: column;
  }
}
@media (min-width: 768px) {
  #box-filter .filter {
    grid-auto-flow: column;
  }
}
#box-filter .token {
  padding: .5em 1em;
  border-radius: 4px;
  cursor: default;
  margin-inline-end: .5em;
}
#box-filter .token .remove {
  padding-inline-start: .5em;
  color: inherit;
  font-weight: 600;
}

#box-filter .token[data-group="name"] {
  background: #cbe2b6;
}
#box-filter .token[data-group="brand"] {
  background: #b6c2e2;
}
#box-filter .token[data-group^="attribute"] {
  background: #e2c6b6;
}
</style>

<section id="box-filter" class="box">
  <?php echo functions::form_begin('filter_form', 'get'); ?>

    <div class="filter">

      <div>
        <?php echo functions::form_search_field('product_name', true, 'autocomplete="off" data-token-group="name" data-token-title="'. language::translate('title_name', 'Name') .'" placeholder="'. functions::escape_html(language::translate('text_filter_by_product_name', 'Filter by product name')) .'"'); ?>
      </div>

      <?php if ($brands) { ?>
      <div>
        <div class="dropdown">
          <div class="form-select" data-toggle="dropdown">
            <?php echo language::translate('title_brands', 'Brands'); ?>
          </div>
          <ul class="dropdown-menu">
            <?php foreach ($brands as $brand) { ?>
            <li>
              <label class="option"><?php echo functions::form_checkbox('brands[]', $brand['id'], true, 'data-token-group="brand" data-token-title="'. language::translate('title_brand', 'Brand') .'" data-token-value="'. $brand['name'] .'"'); ?>
                <span class="title"><?php echo $brand['name']; ?></span>
              </label>
            </li>
            <?php } ?>
          </ul>
        </div>
      </div>
      <?php } ?>

      <?php if ($attributes) foreach ($attributes as $group) { ?>
      <div>
        <div class="dropdown">
          <div class="form-select" data-toggle="dropdown">
            <?php echo $group['name']; ?>
          </div>
          <ul class="dropdown-menu">
            <?php foreach ($group['values'] as $value) { ?>
            <li>
              <label class="option"><?php echo !empty($group['select_multiple']) ? functions::form_checkbox('attributes['. $group['id'] .'][]', $value['id'], true, 'data-token-group="attribute-'. $group['id'] .'" data-token-title="'. functions::escape_html($group['name']) .'" data-token-value="'. functions::escape_html($value['value']) .'"') : functions::form_radio_button('attributes['. $group['id'] .'][]', $value['id'], true, 'data-token-group="attribute-'. $group['id'] .'" data-token-title="'. functions::escape_html($group['name']) .'" data-token-value="'. functions::escape_html($value['value']) .'"'); ?>
                <span class="title"><?php echo $value['value']; ?></span>
              </label>
            </li>
            <?php } ?>
          </ul>
        </div>
      </div>
      <?php } ?>

      <div>
        <div class="dropdown">
          <div class="form-control" data-toggle="dropdown">
            <?php echo language::translate('title_sort_by', 'Sort By'); ?>
          </div>
          <ul class="dropdown-menu">
            <?php foreach ($sort_alternatives as $key => $title) { ?>
            <li>
              <label class="option">
                <?php echo functions::form_radio_button('sort', $key, true); ?>
                <span class="title"><?php echo $title; ?></span>
              </label>
            </li>
            <?php } ?>
          </ul>
        </div>
      </div>

      <div>
        <div class="btn-group btn-group-inline float-end" data-toggle="buttons">
          <label class="btn btn-default<?php echo ($_GET['list_style'] == 'columns') ? ' active' : ''; ?>"><input type="radio" name="list_style" value="columns"<?php echo (!isset($_GET['list_style']) || $_GET['list_style'] == 'columns') ? ' checked' : ''; ?> /><?php echo functions::draw_fonticon('fa-th'); ?></label>
          <label class="btn btn-default<?php echo ($_GET['list_style'] == 'rows') ? ' active' : ''; ?>"><input type="radio" name="list_style" value="rows"<?php echo (isset($_GET['list_style']) && $_GET['list_style'] == 'rows') ? ' checked' : ''; ?> /><?php echo functions::draw_fonticon('fa-th-list'); ?></label>
        </div>
      </div>

    </div>

    <div class="tokens"></div>

  <?php echo functions::form_end(); ?>
</section>

<script>
  $('body').on('input', '#box-filter form[name="filter_form"] :input', function(){
    $('#box-filter .tokens').html('');

    $.each($('#box-filter input[data-token-title][type="search"]'), function(i,el) {
      if (!$(this).val()) return;
      $('#box-filter .tokens').append('<span class="token" data-group="'+ $(el).data('token-group') +'" data-name="'+ $(el).attr('name') +'" data-value="'+ $(el).val() +'">'+ $(el).data('token-title') +': '+ $(el).val() +'<a href="#" class="remove">×</a></span>');
    });

    $.each($('#box-filter input[data-token-title]:checkbox:checked'), function(i,el) {
      $('#box-filter .tokens').append('<span class="token" data-group="'+ $(el).data('token-group') +'" data-name="'+ $(el).attr('name') +'" data-value="'+ $(el).val() +'">'+ $(el).data('token-title') +': '+ $(el).data('token-value') +'<a href="#" class="remove">&times;</a></span>');
    });

  }).first().trigger('change');

  let xhr_filter = null;
  $('body').on('input', '#box-filter form[name="filter_form"]', function(){
    if (xhr_filter) xhr_filter.abort();
    let url = new URL(location.protocol + '//' + location.host + location.pathname + '?' + $('form[name="filter_form"]').serialize());
    $('section.listing.products').hide();
    xhr_filter = $.ajax({
      type: 'get',
      url: url.href,
      dataType: 'html',
      success: function(response){
        let content = $('section.listing.products', response)[0].outerHTML;
        let pagination = $('.pagination', response).length ? $('.pagination', response)[0].outerHTML : '';
        $('.pagination').remove();
        $('section.listing.products').replaceWith(content).after(pagination);
        $('section.listing.products').after(pagination);
      }
    });
  });

  $('body').on('click', '#box-filter form[name="filter_form"] .tokens .remove', function(e){
    e.preventDefault();
    let token = $(this).closest('.token');
    switch ($(':input[name="'+ $(token).data('name') +'"]').attr('type')) {
      case 'radio':
      case 'checkbox':
        $(':input[name="'+ $(token).data('name') +'"][value="'+ $(token).data('value') +'"]').prop('checked', false).trigger('input');
        break;
      case 'text':
      case 'search':
        $(':input[name="'+ $(token).data('name') +'"]').val('').trigger('input');
        break;
    }
  });
</script>