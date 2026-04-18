<style>
main {
	padding: 2em 0;
}

#box-error-document {
	padding: 4em 0;
	background-image: url('<?php echo document::rlink('storage://images/illustration/crash.svg'); ?>');
	background-repeat: no-repeat;
	background-position: top left;
	background-size: auto 400px;
	height: 400px;
}

#box-error-document .code {
	font-size: 64px;
	font-weight: bold;
}

#box-error-document .title {
	font-size: 24px;
}

#box-error-document .description {
	font-size: 18px;
	opacity: .65;
}
</style>

<main id="main">
	{{notices}}

	<article id="box-error-document" class="text-center">

		<div class="code">{{code}}</div>
		<span class="title">{{title}}</span>

		<p class="description">{{description}}</p>

		<div>
			<a class="btn btn-default" href="<?php echo document::href_ilink(''); ?>">
				<?php echo f::draw_fonticon('icon-home'); ?> <?php echo t('title_home', 'Home'); ?>
			</a>
		</div>
	</article>
</main>
