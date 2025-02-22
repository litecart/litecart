<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}">
<?php echo functions::draw_style('app://frontend/templates/'.settings::get('template').'/css/variables.css'); ?>
<?php echo functions::draw_style('app://assets/litecore/css/framework.min.css'); ?>
<?php echo functions::draw_style('app://assets/litecore/css/printable.min.css'); ?>
{{head_tags}}
</head>
<body>

{{content}}

{{foot_tags}}
</body>
</html>