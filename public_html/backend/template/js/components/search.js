// AJAX Search
  let timer_ajax_search = null;
  let xhr_search = null;
  $('#search input[name="query"]').on('input', function(){

    let search_field = this;

    if (xhr_search) xhr_search.abort();

    if ($(this).val() == '') {
      $('#search .results').hide().html('');
      $('#box-apps-menu').fadeIn('fast');
      return;
    }

    if (!$('#search .loader-wrapper').length) {
      $('#box-apps-menu').fadeOut('fast');
      $('#search .results').show().html('<div class="loader-wrapper text-center"><div class="loader" style="width: 48px; height: 48px;"></div></div>');
    }

    clearTimeout(timer_ajax_search);
    timer_ajax_search = setTimeout(function() {
      xhr_search = $.ajax({
        type: 'get',
        async: true,
        cache: false,
        url: window._env.backend.url + 'search_results.json?query=' + $(search_field).val(),
        dataType: 'json',

        beforeSend: function(jqXHR) {
          jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
        },

        error: function(jqXHR, textStatus, errorThrown) {
          $('#search .results').text(textStatus + ': ' + errorThrown);
        },

        success: function(json) {

          $('#search .results').html('');

          if (!$('#search input[name="query"]').val()) return;

          $.each(json, function(i, group){

            if (group.results.length) {
              $('#search .results').append(
                '<h4>'+ group.name +'</h4>' +
                '<ul class="list-group" data-group="'+ group.name +'"></ul>'
              );

              $.each(group.results, function(i, result){
                $('#search .results ul[data-group="'+ group.name +'"]').append(
                  '<li class="result">' +
                  '  <a class="list-group-item" href="'+ result.link +'" style="border-inline-start: 3px solid '+ group.theme.color +';">' +
                  '    <small class="id float-end">#'+ result.id +'</small>' +
                  '    <div class="title">'+ result.title +'</div>' +
                  '    <div class="description"><small>'+ result.description +'</small></div>' +
                  '  </a>' +
                  '</li>'
                );
              });
            }
          });

          if ($('#search .results').html() == '') {
            $('#search .results').html('<p class="text-center no-results"><em>:(</em></p>');
          }
        },
      });
    }, 500);
  });
