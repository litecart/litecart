// Product gallery: click a thumbnail to show it as the main image
// Delegated on the document so it also covers galleries injected by AJAX
waitFor('jQuery', ($) => {

	$(document).on('click', '#box-product .gallery .extra-image', function(e) {

		e.preventDefault();

		const $thumb = $(this),
			$gallery = $thumb.closest('.gallery'),
			$main = $gallery.find('.main-image'),
			$mainImage = $main.find('img'),
			src = $thumb.attr('data-image-src'),
			srcset = $thumb.attr('data-image-srcset');

		if (!src) return;

		$mainImage.attr('src', src);

		if (srcset) $mainImage.attr('srcset', srcset);

		$main.attr('href', $thumb.attr('href'));

		// The main image shows the selected image, so keep it out of the lightbox gallery
		$gallery.find('.extra-image')
			.removeClass('active')
			.removeAttr('aria-current')
			.attr('data-gallery', 'product');

		$thumb.addClass('active').attr('aria-current', 'true').removeAttr('data-gallery');
	});

});
