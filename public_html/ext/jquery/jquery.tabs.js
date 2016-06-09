$(document).ready(function(){
  $(".tabs [id^=tab-]").hide();
  $(".tabs [id^=tab-]:first").show();
  $(".tabs .index li:first").addClass('active');
  $(".tabs .index li").click(function() {
    $(".tabs .index li").removeClass('active');
    $(this).addClass("active");
    $("[id^=tab-]").hide();
    var selected_tab = $(this).find("a").attr("href");
    $(selected_tab).fadeIn('fast');
    return false;
  });
  if (window.location.hash != '') $('a[href="' + window.location.hash + '"]').click();
});
