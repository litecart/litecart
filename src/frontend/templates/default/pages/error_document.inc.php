<style>
main {
	padding: 2em 0;
}

#box-error-document {
	padding: 4em 0;
	background-image: url('<?= document::rlink('storage://images/illustration/crash.svg') ?>');
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

	<article id="box-error-document" class="text-center" aria-label="<?= f::escape_attr(strtr(t('title_error_document', 'Error: {code}'), ['{code}' => '{{code}}'])) ?>">

		<div class="code" role="status">{{code}}</div>
		<span class="title">{{title}}</span>

		<p class="description">{{description}}</p>

		<div>
			<a class="btn btn-default" href="<?= document::href_ilink('') ?>">
				<?= f::draw_fonticon('icon-home', 'aria-hidden="true"') ?> <?= t('title_home', 'Home') ?>
			</a>
		</div>
	</article>
</main>
