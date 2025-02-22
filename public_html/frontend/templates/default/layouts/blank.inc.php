<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}">
<meta name="description" content="{{description}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php echo functions::draw_style('app://frontend/templates/'.settings::get('template').'/css/variables.css'); ?>
<?php echo functions::draw_style('app://assets/litecore/css/framework.min.css'); ?>
<?php echo functions::draw_style('app://frontend/templates/'.settings::get('template').'/css/app.min.css'); ?>
{{head_tags}}
</head>
<body>

{{content}}

{{foot_tags}}
<?php echo functions::draw_script('app://frontend/templates/'.settings::get('template').'/js/app.min.js'); ?>
</body>
</html>