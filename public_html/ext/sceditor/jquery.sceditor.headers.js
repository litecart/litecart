$.sceditor.command.set("headers", {
  exec: function(caller) {
    var editor   = this,
      $content = $("<div />");
    
    $('<a class="sceditor-header-option" href="#">'
      + '<p>Paragraph</p>'
      + '</a>'
      )
      .click(function (e) {
        editor.execCommand("formatblock", "<p>");
        editor.closeDropDown(true);
        e.preventDefault();
      })
      .appendTo($content);
    
    for (var i=1; i<= 4; i++) {
      $('<a class="sceditor-header-option" href="#">'
      + '<h' + i + '>Heading ' + i + '</h' + i + '>'
      + '</a>'
      )
      .data('headersize', i)
      .click(function (e) {
        editor.execCommand("formatblock", "<h" + $(this).data('headersize') + ">");
        editor.closeDropDown(true);
        e.preventDefault();
      })
      .appendTo($content);
    }

    editor.createDropDown(caller, "header-picker", $content);
  },
  tooltip: "Format Headers"
});