<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta http-equiv="Content-Type" content="text/html; charset={snippet:charset}" />
<link href="<!--snippet:template_path-->styles/loader.css" rel="stylesheet" media="print, screen" />
<!--[if IE]><link rel="stylesheet" href="<!--snippet:template_path-->styles/ie.css" /><![endif]-->
<!--[if IE 9]><link rel="stylesheet" href="<!--snippet:template_path-->styles/ie9.css" /><![endif]-->
<!--[if lt IE 9]><link rel="stylesheet" href="<!--snippet:template_path-->styles/ie8.css" /><![endif]-->
<!--[if lt IE 9]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<!--snippet:head_tags-->
<!--snippet:javascript-->
<style>
html, body, body > table, body > table {
  width: 100%;
  height: 100%;
  background: #f8f8f8;
}
#content-wrapper {
  width: 300px;
  margin: auto;
  background: #fff;
  padding: 20px;
}
#box-login {
  width: 200px;
  margin: auto;
}
.shadow {
  box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.3);
}
.rounded-corners {
  border-radius: 4px 4px 4px 4px;
  -moz-border-radius: 4px 4px 4px 4px;
  -webkit-border-radius: 4px;
  -webkit-border-radius: 4px;
}
</style>
</head>
<body>

<table style="width: 100%;">
  <tr>
    <td style="text-align: center;">
    <div style="margin-bottom: 10px;">
    </div>
    <div id="content-wrapper" class="rounded-corners shadow">
      <div id="box-login-wrapper">
        <p><a href="<?php echo $system->document->href_link(WS_DIR_HTTP_HOME . 'index.php'); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>logotype.png" height="50" alt="<?php echo $system->settings->get('store_name'); ?>" /></a></p>
        <div class="content">
          <!--snippet:notices-->
          <!--snippet:content-->
        </div>
      </div>
    </div>
    </td>
  </tr>
</table>

<script>
  $("form[name='login_form']").submit(function() {
    $('#content-wrapper .content').slideUp('fast');
  });
</script>

<!--snippet:foot_tags-->
</body>
</html>