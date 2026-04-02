<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}" class="<?php echo !empty($_COOKIE['dark_mode']) ? 'dark-mode' : ''; ?>">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}">
<?php echo f::draw_style('app://backend/template/css/variables.css'); ?>
<?php echo f::draw_style('app://assets/litecore/css/framework.min.css'); ?>
<?php echo f::draw_style('app://assets/litecore/css/printable.min.css'); ?>
{{head_tags}}
{{style}}
</head>
<body>

{{content}}

{{foot_tags}}
{{javascript}}

</body>
</html>