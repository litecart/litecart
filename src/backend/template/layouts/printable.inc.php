<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}" class="<?= (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') ? 'dark-mode' : '' ?>">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}">
<?= f::draw_style('app://backend/template/css/variables.css') ?>
<?= f::draw_style('app://assets/litecore/css/framework.min.css') ?>
<?= f::draw_style('app://assets/litecore/css/printable.min.css') ?>
{{head_tags}}
{{style}}
</head>
<body>

{{content}}

{{foot_tags}}
{{javascript}}

</body>
</html>