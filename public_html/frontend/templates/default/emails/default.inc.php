<!doctype html>
<html>
<head>
<meta name="viewport" content="width=device-width">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
body {
	background-color: #f3f3f3;
	width: 100%;
	font-family: sans-serif;
	font-size: 14px;
	line-height: 1.5;
	margin: 0;
	padding: 0;
}

img {
	border: none;
	-ms-interpolation-mode: bicubic;
	max-width: 100%;
}

table {
	border-collapse: separate;
	mso-table-lspace: 0pt;
	mso-table-rspace: 0pt;
	width: 100%; }
	table td {
		font-family: sans-serif;
		font-size: 14px;
		vertical-align: top;
}

.container {
	display: block;
	margin: 0 auto !important;
	/* makes it centered */
	max-width: 640px;
	padding: 10px;
	width: 640px;
}

.content {
	background: #fff;
	border-radius: .5em;
	box-sizing: border-box;
	display: block;
	margin: 0 auto;
	max-width: 640px;
	padding: 10px;
}

.main {
	width: 100%;
}

.wrapper {
	box-sizing: border-box;
	padding: 20px;
}

.content-block {
	padding-bottom: 10px;
	padding-top: 10px;
}

.footer {
	clear: both;
	margin-top: 10px;
	text-align: center;
	width: 100%;
}
	.footer td,
	.footer p,
	.footer span,
	.footer a {
		color: #999999;
		font-size: 12px;
		text-align: center;
}

h1, h2, h3 {
	color: #000000;
	font-family: sans-serif;
	font-weight: 400;
	line-height: 1.4;
	margin: 0;
	margin-bottom: 30px;
}

h1 {
	font-size: 35px;
	font-weight: 300;
	text-align: center;
	text-transform: capitalize;
}

p, ul, ol {
	font-family: sans-serif;
	font-size: 14px;
	font-weight: normal;
	margin: 0;
	margin-bottom: 15px;
}
p li, ul li, ol li {
	list-style-position: inside;
	margin-left: 5px;
}

a {
	color: #3498db;
	text-decoration: underline;
}

.btn {
	box-sizing: border-box;
	width: 100%;
}
.btn > tbody > tr > td {
	padding-bottom: 15px;
}
.btn table {
	width: auto;
}
.btn table td {
	background-color: #fff;
	border-radius: 5px;
	text-align: center;
}
.btn a {
	background-color: #fff;
	border: solid 1px #7393cb;
	border-radius: 5px;
	box-sizing: border-box;
	color: #7393cb;
	cursor: pointer;
	display: inline-block;
	font-size: 14px;
	font-weight: bold;
	margin: 0;
	padding: .5em 1em;
	text-decoration: none;
	text-transform: capitalize;
}

.btn-default table td {
	background-color: #7393cb;
}

.btn-default a {
	background-color: #7393cb;
	border-color: #7393cb;
	color: #fff;
}

.last {
	margin-bottom: 0;
}

.first {
	margin-top: 0;
}

.text-center {
	text-align: center;
}

.text-end {
	text-align: end;
}

.text-start {
	text-align: start;
}

.clear {
	clear: both;
}

.mt0 {
	margin-top: 0;
}

.mb0 {
	margin-bottom: 0;
}

.preheader {
	color: transparent;
	display: none;
	height: 0;
	max-height: 0;
	max-width: 0;
	opacity: 0;
	overflow: hidden;
	mso-hide: all;
	visibility: hidden;
	width: 0;
}

.powered-by a {
	text-decoration: none;
}

hr {
	border: 0;
	border-bottom: 1px solid #f6f6f6;
	margin: 20px 0;
}

@media only screen and (max-width: 620px) {
	table.body h1 {
		font-size: 28px !important;
		margin-bottom: 10px !important;
	}
	table.body p,
	table.body ul,
	table.body ol,
	table.body td,
	table.body span,
	table.body a {
		font-size: 16px !important;
	}
	table.body .wrapper,
	table.body .article {
		padding: 10px !important;
	}
	table.body .content {
		padding: 0 !important;
	}
	table.body .container {
		padding: 0 !important;
		width: 100% !important;
	}
	table.body .main {
		border-inline-start-width: 0 !important;
		border-radius: 0 !important;
		border-inline-end-width: 0 !important;
	}
	table.body .btn table {
		width: 100% !important;
	}
	table.body .btn a {
		width: 100% !important;
	}
	table.body .img-responsive {
		height: auto !important;
		max-width: 100% !important;
		width: auto !important;
	}
}
</style>
</head>

<body>

	<span class="preheader">This is preheader text. Some clients will show this text as a preview.</span>

	<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
		<tr>

			<td class="container">
				<div class="content">

					<table role="presentation" class="main">

						<tr>
							<td class="wrapper">
							{{content}}
							</td>
						</tr>

					</table>

					<div class="footer">
						<table role="presentation" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td class="content-block">
								</td>
							</tr>
							<tr>
								<td class="content-block powered-by">
									<?php echo settings::get('store_name'); ?><br>
									<span class="apple-link"><?php echo preg_replace('#\r\n?|\n#', ', ', settings::get('store_postal_address')); ?></span><br>
									<a href="<?php echo document::href_ilink('', [], [], [], $language_code); ?>" target="_blank"><?php echo document::href_ilink('', [], [], [], $language_code); ?></a>
								</td>
							</tr>
						</table>
					</div>

				</div>
			</td>

		</tr>
	</table>

</body>
</html>