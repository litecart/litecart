<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php echo functions::draw_style('app://backend/template/css/variables.css'); ?>
<?php echo functions::draw_style('app://assets/litecore/css/framework.min.css'); ?>
<?php echo functions::draw_style('app://backend/template/css/app.min.css'); ?>
{{head_tags}}
{{style}}
</head>
<body>

{{content}}

{{foot_tags}}
<?php echo functions::draw_script('app://assets/litecore/js/framework.min.js'); ?>
<?php echo functions::draw_script('app://backend/template/js/app.min.js'); ?>
{{javascript}}
</body>
</html>