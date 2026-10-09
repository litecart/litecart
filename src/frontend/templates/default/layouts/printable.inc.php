<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}">
<?= f::draw_style('app://frontend/templates/'.settings::get('template').'/css/variables.css') ?>
<?= f::draw_style('app://assets/litecore/css/framework.min.css') ?>
<?= f::draw_style('app://assets/litecore/css/printable.min.css') ?>
{{head_tags}}
</head>
<body>

{{content}}

{{foot_tags}}
</body>
</html>