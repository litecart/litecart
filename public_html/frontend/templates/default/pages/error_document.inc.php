<style>
#box-error-document {
	margin: 4em 0;
	background-image: url('<?php echo document::rlink('storage://images/illustration/crash.svg'); ?>');
	background-repeat: no-repeat;
	background-position: top left;
	background-size: auto 400px;
	height: 400px;
}

#box-error-document .code {
	font-size: 24px;
	font-weight: bold;
}
#box-error-document .title {
	font-size: 48px;
}
#box-error-document .description {
	font-size: 24px;
}
</style>

<main id="main">
	{{notices}}

	<article id="box-error-document" class="text-center">

		<div class="title">{{title}}</div>

		<div class="code">HTTP {{code}}</div>

		<p class="description">{{description}}</p>

		<div>
			<a class="btn btn-default" href="<?php echo document::href_ilink(''); ?>">
				<?php echo functions::draw_fonticon('icon-home'); ?> <?php echo t('title_home', 'Home'); ?>
			</a>
		</div>
	</article>
</main>
