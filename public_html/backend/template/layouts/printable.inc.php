<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}">
<?php echo functions::draw_style('app://backend/template/css/variables.css'); ?>
<?php echo functions::draw_style('app://backend/template/css/framework.min.css'); ?>
<?php echo functions::draw_style('app://backend/template/css/printable.min.css'); ?>
{{head_tags}}
{{style}}
</head>
<body>

{{content}}

{{foot_tags}}
{{javascript}}

</body>
</html>
