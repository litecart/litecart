<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta http-equiv="Content-Type" content="text/html; charset={snippet:charset}" />
<link href="<!--snippet:template_path-->styles/loader.css" rel="stylesheet" media="print, screen" />
<!--[if IE]><link rel="stylesheet" type="text/css" href="<!--snippet:template_path-->styles/ie.css" /><![endif]-->
<!--[if IE 9]><link rel="stylesheet" href="<!--snippet:template_path-->styles/ie9.css" /><![endif]-->
<!--[if lt IE 9]><link rel="stylesheet" href="<!--snippet:template_path-->styles/ie8.css" /><![endif]-->
<!--[if lt IE 9]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<script src="//code.jquery.com/jquery-1.9.1.min.js"></script>
<script src="//code.jquery.com/jquery-migrate-1.1.1.min.js"></script>
<script type="text/javascript">
  if (typeof jQuery == 'undefined') document.write(unescape("%3Cscript src='<?php echo WS_DIR_EXT; ?>jquery/jquery-1.9.1.min.js' type='text/javascript'%3E%3C/script%3E"));
  if (typeof jQuery.migrateTrace == 'undefined') document.write(unescape("%3Cscript src='<?php echo WS_DIR_EXT; ?>jquery/jquery-migrate-1.1.1.min.js' type='text/javascript'%3E%3C/script%3E"));
  if (/iphone|ipod|android|blackberry|opera mini|opera mobi|skyfire|maemo|windows phone|palm|iemobile|symbian|symbianos|fennec/i.test(navigator.userAgent.toLowerCase())) {
    $("meta[name='viewport']").attr("content", "width=640");
  }
</script>
<!--snippet:head_tags-->
<!--snippet:javascript-->
<style>
html, body, body > table, body > table {
  height: 100%;
}
#box-login {
  display: inline-block;
  width: 250px;
  box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
}
</style>
</head>
<body>
<table style="width: 100%;">
  <tr>
    <td style="text-align: center;">
      <div style="display: inline-block; width: 500px;">
        <!--snippet:notices-->
        <!--snippet:content-->
      </div>
    </td>
  </tr>
</table>
<!--snippet:foot_tags-->
</body>
</html>