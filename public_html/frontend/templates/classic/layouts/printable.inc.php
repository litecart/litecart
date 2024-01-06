<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}" />
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/variables.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/framework.min.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/printable.min.css'); ?>" />
{{head_tags}}
{{style}}
<style>
@media print {
  button[name="print"] {
    display: none;
  }
}
@media screen {
  button[name="print"] {
    display: none;
  }

  html:hover button[name="print"] {
    position: fixed;
    top: 1cm;
    right: 1cm;
    display: block;
    z-index: 999;
  }
}
</style>
</head>
<body>

{{content}}

{{foot_tags}}
{{javascript}}

<button name="print" class="btn btn-default btn-lg">
  <?php echo functions::draw_fonticon('fa-print'); ?> <?php echo language::translate('title_print', 'Print'); ?>
</button>

<script>
  $('button[name="print"]').click(function(){
    window.print();
  });
</script>
</body>
</html>