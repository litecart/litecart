<style>
form[name="buy_now_form"] .dropdown-menu {
	left: 0;
	right: 0;
}

form[name="buy_now_form"] .dropdown-menu .image {
	border-radius: var(--border-radius);
	border: 1px solid var(--default-border-color);
	vertical-align: middle;
}

#quantity-prices {
	font-size: 15px;
}

#quantity-prices td, #quantity-prices th {
	cursor: pointer;
}

#quantity-prices tr.selected {
	background: #ffeee0;
	color: #c91c17;
}
</style>

<main id="main" class="container">
	{{breadcrumbs}}
	{{notices}}

	<div id="content">

		<article id="box-product" data-id="<?php echo $product_id; ?>" data-name="<?php echo f::escape_attr($name); ?>" data-price="<?php echo currency::format_raw($final_price); ?>" aria-label="<?php echo f::escape_attr($name); ?>">

			<div class="grid" style="margin-bottom: 0;">
				<div class="col-md-6">
					<div class="images">

						<a class="main-image" href="<?php echo document::href_rlink($image); ?>" data-toggle="lightbox" data-gallery="product" aria-label="<?php echo f::escape_attr(t('title_view_image', 'View image') .': '. $name); ?>">
							<?php echo f::draw_thumbnail($image, 720, 0, 'product', 'alt="'. f::escape_attr($name) .'"'); ?>
							<?php echo ($sticker); ?>
						</a>

						<?php if ($extra_images || $video_url) { ?>
						<div class="grid">
							<?php foreach ($extra_images as $extra_image) { ?>
							<div class="col-4">
								<a class="extra-image" href="<?php echo document::href_rlink($extra_image); ?>" data-toggle="lightbox" data-gallery="product" aria-label="<?php echo f::escape_attr(t('title_view_image', 'View image') .': '. $name); ?>">
									<?php echo f::draw_thumbnail($extra_image, 320, 0, 'product', 'alt="'. f::escape_attr($name) .'"'); ?>
								</a>
							</div>
							<?php } ?>

							<?php if ($video_url) { ?>
							<div class="col-4">
								<a class="video" href="<?php echo document::href_rlink($video_url); ?>" data-toggle="lightbox" data-gallery="product" aria-label="<?php echo f::escape_attr(t('title_play_video', 'Play video') .': '. $name); ?>">
									<?php echo f::draw_thumbnail($image, 320, 0, 'product', 'alt="'. f::escape_attr($name) .'"'); ?>
									<span class="video-icon" aria-hidden="true"><?php echo f::draw_fonticon('icon-play'); ?></span>
								</a>
							</div>
							<?php } ?>
						</div>
						<?php } ?>

					</div>
				</div>

				<div class="col-md-6">
					<h1 class="title"><?php echo f::escape_html($name); ?></h1>

					<?php //if ($average_rating) { ?>
					<div class="average-rating">
						<a href="#reviews" style="text-decoration: none;" aria-label="<?php echo f::escape_attr(t('title_view_reviews', 'View reviews')); ?>">
							<?php echo f::draw_rating($average_rating); ?>
						</a>
					</div>
					<?php //} ?>

					<?php if ($short_description) { ?>
					<p class="short-description">
						<?php echo f::escape_html($short_description); ?>
					</p>
					<?php } ?>

					<?php if (!empty($brand)) { ?>
					<div class="brand">
						<a href="<?php echo f::escape_html($brand['link']); ?>" aria-label="<?php echo f::escape_attr($brand['name']); ?>">
							<?php if ($brand['image']) { ?>
							<?php echo f::draw_thumbnail($brand['image'], 0, 40, '', 'alt="'. f::escape_attr($brand['name']) .'" style="margin: 0; max-height: 80px; margin-inline-start: 0;"'); ?>
							<?php } else { ?>
							<h3><?php echo f::escape_html($brand['name']); ?></h3>
							<?php } ?>
						</a>
					</div>
					<?php } ?>

					<?php if ($recommended_price) { ?>
					<div class="recommended-price" style="margin: 1em 0;">
						<?php echo t('title_recommended_price', 'Recommended Price'); ?>:
						<span class="value"><?php echo currency::format($recommended_price); ?></span>
					</div>
					<?php } ?>

					<?php if ($cheapest_shipping_fee !== null) { ?>
					<div class="cheapest-shipping" style="margin: 1em 0;">
						<?php echo f::draw_fonticon('icon-truck', 'aria-hidden="true"'); ?> <?php echo strtr(t('text_cheapest_shipping_from_price', 'Cheapest shipping from <strong class="value">{price}</strong>'), [
							'{price}' => currency::format($cheapest_shipping_fee)
						]); ?>
					</div>
					<?php } ?>

					<?php if ($sku || $mpn || $gtin) { ?>
					<div class="codes" style="margin: 1em 0;">
						<?php if ($sku) { ?>
						<div class="sku">
							<?php echo t('title_sku', 'SKU'); ?>:
							<span class="value"><?php echo f::escape_html($sku); ?></span>
						</div>
						<?php } ?>

						<?php if ($mpn) { ?>
						<div class="mpn">
							<?php echo t('title_mpn', 'MPN'); ?>:
							<span class="value"><?php echo f::escape_html($mpn); ?></span>
						</div>
						<?php } ?>

						<?php if ($gtin) { ?>
						<div class="gtin">
							<?php echo t('title_gtin', 'GTIN'); ?>:
							<span class="value"><?php echo f::escape_html($gtin); ?></span>
						</div>
						<?php } ?>
					</div>
					<?php } ?>

					<?php if ($quantity_available) { ?>
					<div class="stock-status" style="margin: 1em 0;" aria-live="polite">
						<?php if ($quantity_available > 0) { ?>
						<div class="stock-available">
							<?php echo t('title_stock_status', 'Stock Status'); ?>:
							<span class="value"><?php echo f::escape_html($stock_status); ?></span>
						</div>

						<?php if ($delivery_status) { ?>
						<div class="stock-delivery">
							<?php echo t('title_delivery_status', 'Delivery Status'); ?>:
							<span class="value"><?php echo f::escape_html($delivery_status['name']); ?></span>
						</div>
						<?php } ?>

						<?php } else { ?>
						<?php if ($sold_out_status) { ?>
							<div class="<?php echo $orderable ? 'stock-partly-available' : 'stock-unavailable'; ?>">
								<?php echo t('title_stock_status', 'Stock Status'); ?>:
								<span class="value"><?php echo f::escape_html($sold_out_status['name']); ?></span>
							</div>

					<?php } else { ?>
					<div class="stock-unavailable">
						<?php echo t('title_stock_status', 'Stock Status'); ?>:
						<span class="value"><?php echo t('title_sold_out', 'Sold Out'); ?></span>
					</div>

					<?php if (!$orderable) { ?>
					<div class="notify-me" style="margin-top: 1em;">
						<?php echo f::form_begin('notification_form', 'post', false, false, ['aria-label' => f::escape_attr(t('title_notify_when_in_stock', 'Notify me when this product is back in stock'))]); ?>
							<div class="form-group">
								<label for="notification_email"><?php echo t('description_get_notified_when_in_stock', 'Would you like to receive a notification when this item is back in stock?'); ?></label>
								<?php echo f::form_input_email('email', customer::$data['email'] ?? '', ['id' => 'notification_email', 'autocomplete' => 'email', 'placeholder' => t('title_email', 'Email')]); ?>
							</div>
							<div>
								<?php echo f::form_button('notify_me', t('title_notify_me', 'Notify Me'), 'submit', ['class' => 'btn btn-success']); ?>
							</div>
						<?php echo f::form_end(); ?>
					</div>
					<?php } ?>

					<?php } ?>
					<?php } ?>
				</div>
				<?php } ?>

				<?php if ($final_price) { ?>
					<?php echo f::form_begin('buy_now_form', 'post', false, false, ['aria-label' => f::escape_attr(t('title_purchase_now', 'Purchase Now') .': '. $name)]); ?>

						<fieldset style="margin: 2em 0;">

							<legend><?php echo t('title_purchase_now', 'Purchase Now'); ?></legend>

							<?php echo f::form_input_hidden('product_id', $product_id); ?>

							<?php if ($stock_options) { ?>
							<div class="form-group">
								<div class="form-label" id="stock-option-label"><?php echo t('text_select_desired_option', 'Select desired option'); ?></div>
								<?php echo form_select_product_stock_option('stock_option_id', $product_id, true); ?>
							</div>
							<?php } ?>

							<div class="grid" style="margin-bottom: 0;">

								<div class="col-xl-8">
									<?php if (!settings::get('catalog_only_mode')) { ?>
									<label class="form-group">
										<div class="form-label"><?php echo t('title_quantity', 'Quantity'); ?></div>
										<div style="display: flex">
											<div class="input-group" style="flex: 0 1 150px;">
												<?php echo !empty($quantity_unit['decimals']) ? f::form_input_decimal('quantity', isset($_POST['quantity']) ? true : 1, $quantity_unit['decimals'], 'autocomplete="off" min="'. ($quantity_min ?: '1') .'" max="'. ($quantity_max ?: '') .'" step="'. ($quantity_step ?: '') .'"') : f::form_input_number('quantity', isset($_POST['quantity']) ? true : 1, 'autocomplete="off" min="'. ($quantity_min ?: '1') .'" max="'. ($quantity_max ?: '') .'" step="'. ($quantity_step ?: '') .'"'); ?>
												<?php if (!empty($quantity_unit['name'])) echo '<div class="input-group-text">'. f::escape_html($quantity_unit['name']) .'</div>'; ?>
											</div>

											<div style="flex: 1 0 auto; padding-inline-start: 1em;">
												<?php echo '<button class="btn btn-success" name="add_cart_product" value="true" type="submit" aria-label="'. f::escape_attr(t('title_add_to_cart', 'Add To Cart') .': '. $name) .'"'. (($quantity_available <= 0 && !$orderable) ? ' disabled aria-disabled="true"' : '') .'>'. f::escape_html(t('title_add_to_cart', 'Add To Cart')) .'</button>'; ?>
											</div>
										</div>
									</label>
									<?php } ?>
								</div>

								<div id="price" class="col-xl-4" aria-label="<?php echo f::escape_attr(t('title_price', 'Price')); ?>">
									<br>
									<?php echo f::draw_price_tag($regular_price, $final_price, currency::$selected['code']); ?>

									<?php if ($tax_class_id) { ?>
									<?php if ($tax) { ?>
									<div class="tax" style="margin: 0 0 1em;">
									<?php if ($tax_rates) { ?>
										<?php echo $including_tax ? t('text_tax_included', 'Tax included') : t('title_excluding_tax', 'Excluding Tax'); ?>: <span class="tax"><?php echo currency::format($tax); ?></span>
									<?php } else { ?>
										<?php echo t('title_no_tax_included', 'No tax included'); ?>
									<?php } ?>
									</div>
									<?php } ?>
									<?php } ?>
								</div>
							</div>

							<?php if (!empty($quantity_prices)) { ?>
							<div id="quantity-prices">
								<table class="table table-striped data-table">
									<caption class="hidden"><?php echo t('title_quantity_pricing', 'Quantity pricing'); ?></caption>
									<thead>
										<tr>
											<th scope="col"><?php echo t('title_min_qty', 'Min. Qty'); ?></th>
											<th scope="col"><?php echo t('title_unit_price', 'Unit Price'); ?></th>
										<tr>
									</thead>

									<tbody>
										<tr data-min-quantity="1" data-regular-price="<?php echo (float)$final_price; ?>" data-regular-price="<?php echo currency::format_raw($regular_price); ?>" data-final-price="<?php echo currency::format_raw($final_price); ?>" data-tax="<?php echo currency::format_raw($tax); ?>">
											<td>1+</td>
											<td><?php echo f::draw_price_tag($regular_price, $final_price); ?></td>
										</tr>

										<?php foreach ($quantity_prices as $quantity_price) { ?>
										<tr data-min-quantity="<?php echo (float)$quantity_price['min_quantity']; ?>" data-regular-price="<?php echo currency::format_raw($quantity_price['regular_price']); ?>" data-final-price="<?php echo currency::format_raw($quantity_price['final_price']); ?>" data-tax="<?php echo currency::format_raw($quantity_price['final_price']); ?>">
											<td><?php echo (float)$quantity_price['min_quantity']; ?>+</td>
											<td><?php echo f::draw_price_tag($quantity_price['regular_price'], $quantity_price['final_price']); ?></td>
										</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
							<?php } ?>

						</fieldset>

					<?php echo f::form_end(); ?>
					<?php } ?>

					<div class="social-bookmarks" aria-label="<?php echo f::escape_attr(t('title_share', 'Share')); ?>">

						<a class="link btn btn-default" href="#" aria-label="<?php echo f::escape_attr(t('text_share_url', 'Share URL') .': '. $name); ?>">
							<?php echo f::draw_fonticon('icon-link', 'style="background: #333; color: #fff;" aria-hidden="true"'); ?>
							<div class="description"><?php echo f::escape_html(t('text_share_url', 'Share URL')); ?></div>
						</a>

						<a class="btn btn-default" href="<?php echo 'mailto:user@email.com?', http_build_query(['subject' => t('text_is_this_a_product_for_you', 'Is this a product for you?'), 'body' => document::ilink()]); ?>" aria-label="<?php echo f::escape_attr(t('text_share_via_email', 'Share via Email')); ?>">
							<?php echo f::draw_fonticon('icon-envelope', 'style="background: #333; color: #fff;" aria-hidden="true"'); ?>
							<div class="description"><?php echo f::escape_html(t('text_share_via_email', 'Share via Email')); ?></div>
						</a>

						<?php /* Requires appId
						<a class="x btn btn-default" href="<?php echo document::href_link('fb-messenger://share/', ['link' => $link]); ?>" target="_blank">
							<?php echo f::draw_fonticon('icon-brand-messenger', 'style="background: #000; color: #fff;"'); ?>
							<div class="description"><?php echo f::escape_html(t('text_share_via_s', 'Share via {s}'), ['{s}' => 'Messenger']); ?></div>
						</a>
							*/ ?>

						<a class="x btn btn-default" href="<?php echo document::href_link('https://wa.me/', ['text' => $name .' - '. $link]); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo f::escape_attr(strtr(t('text_share_on_s', 'Share on {s}'), ['{s}' => 'WhatsApp'])); ?>">
							<?php echo f::draw_fonticon('icon-brand-whatsapp', 'style="background: #25D366; color: #fff;" aria-hidden="true"'); ?>
							<div class="description"><?php echo f::escape_html(strtr(t('text_share_on_s', 'Share on {s}'), ['{s}' => 'WhatsApp'])); ?></div>
						</a>

						<a class="facebook btn btn-default" href="<?php echo document::href_link('https://www.facebook.com/sharer.php', ['u' => $link]); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo f::escape_attr(strtr(t('text_share_on_s', 'Share on {s}'), ['{s}' => 'Facebook'])); ?>">
							<?php echo f::draw_fonticon('icon-brand-facebook', 'style="background: #3b5998; color: #fff;" aria-hidden="true"'); ?>
							<div class="description"><?php echo f::escape_html(strtr(t('text_share_on_s', 'Share on {s}'), ['{s}' => 'Facebook'])); ?></div>
						</a>

						<a class="x btn btn-default" href="<?php echo document::href_link('https://x.com/intent/tweet/', ['text' => $name .' - '. $link]); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo f::escape_attr(strtr(t('text_share_on_s', 'Share on {s}'), ['{s}' => 'X'])); ?>">
							<?php echo f::draw_fonticon('icon-brand-x', 'style="background: #000; color: #fff;" aria-hidden="true"'); ?>
							<div class="description"><?php echo f::escape_html(strtr(t('text_share_on_s', 'Share on {s}'), ['{s}' => 'X'])); ?></div>
						</a>

						<a class="pinterest btn btn-default" href="<?php echo document::href_link('https://pinterest.com/pin/create/button/', ['url' => $link]); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo f::escape_attr(strtr(t('text_share_on_s', 'Share on {s}'), ['{s}' => 'Pinterest'])); ?>">
							<?php echo f::draw_fonticon('icon-brand-pinterest', 'style="background: #bd081c; color: #fff;" aria-hidden="true"'); ?>
							<div class="description"><?php echo f::escape_html(strtr(t('text_share_on_s', 'Share on {s}'), ['{s}' => 'Pinterest'])); ?></div>
						</a>
					</div>

				</div>
			</div>

			<?php if (!is_ajax_request()) { ?>
			<?php if ($description || $technical_data) { ?>
			<section class="card" style="margin: var(--gutter-y) 0;">
				<div class="card-body">
					<div class="grid" style="margin-bottom: 0;">

						<?php if ($description) { ?>
						<div class="col-md-<?php echo ($technical_data) ? 6 : 12; ?>">
							<h2 style="margin-top: 0;"><?php echo t('title_description', 'Description'); ?></h2>

							<div class="description">
								<?php echo $description; ?>
							</div>
						</div>
						<?php } ?>

						<?php if ($technical_data) { ?>
						<div class="col-md-<?php echo ($description) ? 6 : 12; ?>">
							<h2 style="margin-top: 0;"><?php echo t('title_technical_data', 'Technical Data'); ?></h2>

							<div class="technical-data" <?php if (!$description) echo 'style="columns: 2 auto;"'; ?>>
								<table class="table">
<?php
	foreach ($technical_data as $line) {

		if (preg_match('#[:\t]#', $line)) {

			@list($key, $value) = preg_split('#([:\t]+)#', $line, -1, PREG_SPLIT_NO_EMPTY);

			echo implode(PHP_EOL, [
				'  <tr>',
				'    <td>'. trim($key) .'</td>',
				'    <td>'. trim($value) .'</td>',
				'  </tr>',
			]);

		} else if (trim($line) != '') {
			echo implode(PHP_EOL, [
				'  <thead>',
				'    <tr>',
				'      <th colspan="2">'. $line .'</th>',
				'    </tr>',
				'  </thead>',
				'  <tbody>',
			]);

		} else {
			echo implode(PHP_EOL, [
				' </tbody>',
				'</table>',
				'<table class="table">',
			]);
		}
	}
?>
									</table>
								</div>
							</div>
						<?php } ?>
					</div>

				</div>
			</section>
			<?php } ?>
			<?php } ?>

			<?php //if (!is_ajax_request() && $reviews) { ?>
			<section id="reviews" class="card" aria-label="<?php echo f::escape_attr(t('title_customer_reviews', 'Customer Reviews')); ?>">
				<a name="reviews"></a>

			<div class="card-header">
				<div class="card-title">
					<?php echo t('title_customer_reviews', 'Customer Reviews'); ?>
				</div>
			</div>

				<div class="card-body">
					<div class="row">
						<div class="col-md-6">

							<?php if (!empty($reviews)) { ?>
							<div class="reviews" role="list" aria-label="<?php echo f::escape_attr(t('title_customer_reviews', 'Customer Reviews')); ?>">
								<?php foreach ($reviews as $review) { ?>
								<article class="review" data-review-id="<?php echo $review['id']; ?>" role="listitem" aria-label="<?php echo f::escape_attr(t('title_review_by', 'Review by {author}', ['{author}' => $review['customer_name']])); ?>">

									<div class="vote">
										<a class="upvote" href="#" aria-label="<?php echo f::escape_attr(t('title_upvote', 'Upvote')); ?>">
											<?php echo f::draw_fonticon('icon-thumbs-up', 'aria-hidden="true"'); ?> <span class="num-votes" aria-label="<?php echo (int)$review['upvotes']; ?>"><?php echo $review['upvotes']; ?></span>
										</a>
										<a class="downvote" href="#" aria-label="<?php echo f::escape_attr(t('title_downvote', 'Downvote')); ?>">
											<?php echo f::draw_fonticon('icon-thumbs-down', 'aria-hidden="true"'); ?> <span class="num-votes" aria-label="<?php echo (int)$review['downvotes']; ?>"><?php echo $review['downvotes']; ?></span>
										</a>
									</div>

									<div class="name">
										<?php echo f::escape_html($review['customer_name']); ?>
									</div>

									<div class="rating" aria-label="<?php echo f::escape_attr(t('title_rating', 'Rating') .': '. round($review['rating'] * 2) .'/10'); ?>">
										<?php echo f::draw_rating($review['rating']); ?>
									</div>

									<div class="title">
										<?php echo f::escape_html($review['title']); ?>
									</div>

									<div class="description">
										<?php echo nl2br(f::escape_html($review['description'])); ?>
									</div>

<?php
	if (!empty($review['attachments'])) {
		echo '<div class="attachments" style="margin-top: 1em;" aria-label="'. f::escape_attr(t('title_attachments', 'Attachments')) .'">';
		foreach ($review['attachments'] as $attachment) {
			echo match(true) {

				(preg_match('#\.(bmp|gif|jpe?g|png)$#', $attachment['filename']) === 1)
					=> '<div class="attachment"><a href="'. f::escape_html($attachment['link']) .'" class="thumbnail" data-toggle="lightbox" data-type="image" aria-label="'. f::escape_attr($attachment['filename']) .'"><img src="'. WS_DIR_APP . f::image_thumbnail($attachment['attachment'], 96, 96, 'FIT_USE_WHITESPACING') .'" alt="'. f::escape_attr($attachment['filename']) .'"></a></div>',

				(preg_match('#\.(avi|mp4|mov)$#', $attachment['filename']) === 1)
					=> '<div class="attachment"><a href="'. f::escape_html($attachment['link']) .'" class="thumbnail text-center" aria-label="'. f::escape_attr($attachment['filename']) .'">'. WS_DIR_APP . f::draw_fonticon('icon-film', 'style="font-size: 3rem; padding-top: 2rem;" aria-hidden="true"') .'</a></div>',

				default
					=> '<div class="attachment"><a href="'. f::escape_html($attachment['link']) .'" class="thumbnail text-center" aria-label="'. f::escape_attr($attachment['filename']) .'">'. f::draw_fonticon('icon-paperclip', 'style="font-size: 3rem; padding-top: 2rem;" aria-hidden="true"') .'</a></div>'
			};
		}
		echo '</div>';
	}
?>

								</article>
								<?php } ?>
							</div>

							<?php } else { ?>
							<p><em><?php echo t('title_be_first_to_post_review', 'Be the first to post a review for this product'); ?></em></p>
							<?php } ?>

						</div>

						<div class="col-md-6">

							<?php echo f::form_begin('rating_form', 'post', '', true, ['aria-label' => f::escape_attr(t('title_customer_reviews', 'Customer Reviews'))]); ?>

								<fieldset<?php echo empty(customer::$data['id']) ? ' disabled aria-disabled="true"' : ''; ?>>
									<h3><?php echo t('title_rate_this_product', 'Rate This Product'); ?></h3>

									<?php if (empty(customer::$data['id'])) { ?>

									<div style="color: #999;"><?php echo t('text_must_be_signed_in_to_rate_product', 'You must be signed in to rate this product'); ?></div>

									<div><a class="btn btn-default" href="<?php echo document::ilink('account/sign_in', ['redirect_url' => document::link()]) ?>#box-login" data-toggle="lightbox" data-seamless="true" data-width="480px" data-require-window-width="768"><?php echo t('title_sign_in', 'Sign In'); ?></a></div>

									<?php } else { ?>

									<div class="form-group">
										<div class="rate-now" role="radiogroup" aria-label="<?php echo f::escape_attr(t('title_rate_this_product', 'Rate This Product')); ?>">
											<?php foreach (range(5, 1) as $i) { ?>
											<input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>"<?php echo (isset($customer_review['rating']) && $customer_review['rating'] == $i) ? ' checked' : ''; ?>>
												<label for="star<?php echo $i; ?>" aria-label="<?php echo f::escape_attr($i .'/5 '. t('title_stars', 'stars')); ?>"><?php echo f::draw_fonticon('icon-star', 'aria-hidden="true"'); ?></label>
											<?php } ?>
										</div>
									</div>

									<div class="form-group">
										<label for="review_title" class="hidden"><?php echo t('title_title', 'Title'); ?></label>
										<?php echo f::form_input_text('title', !empty($customer_review['title']) ? $customer_review['title'] : true, ['id' => 'review_title', 'autocomplete' => 'off', 'placeholder' => f::escape_html(t('title_title', 'Title'))]); ?>
									</div>

											<div class="form-group">
										<label for="review_description" class="hidden"><?php echo t('title_review', 'Review'); ?></label>
										<?php echo f::form_textarea('description', !empty($customer_review['description']) ? $customer_review['description'] : true, ['id' => 'review_description', 'autocomplete' => 'off', 'placeholder' => f::escape_html(t('title_review', 'Review'))]); ?>
									</div>

									<div class="attachments" aria-label="<?php echo f::escape_attr(t('title_attachments', 'Attachments')); ?>">

										<div class="current-attachments">
											<?php if (!empty($_POST['attachments'])) foreach (array_keys($_POST['attachments']) as $key) { ?>
											<div class="attachment form-group">
												<label><?php echo t('title_attachment', 'Attachment'); ?></label>
												<?php echo f::form_input_hidden('attachments['.$key.'][id]', true); ?>
												<div class="input-group">
													<div class="form-control"><?php echo f::escape_html($_POST['attachments'][$key]['filename']); ?></div>
													<div class="input-group-text">
														<?php echo f::form_button_predefined('move-up-sm'); ?>
														<?php echo f::form_button_predefined('move-down-sm'); ?>
														<?php echo f::form_button_predefined('remove-sm'); ?>
													</div>
												</div>
											</div>
											<?php } ?>
										</div>

										<div class="new-attachments">
										</div>

										<div class="form-group">
											<p><?php echo t('text_add_review_attachment', 'Add a photo to your review.'); ?></p>
											<a href="#" class="add" title="<?php echo f::escape_attr(t('text_add', 'Add')); ?>" aria-label="<?php echo f::escape_attr(t('title_add_attachment', 'Add Attachment')); ?>"><?php echo f::draw_fonticon('add', 'aria-hidden="true"'); ?> <?php echo t('title_add_attachment', 'Add Attachment'); ?></a>
										</div>
									</div>

									<?php if (settings::get('captcha_enabled')) { ?>
									<div class="form-group">
										<label><?php echo t('title_captcha', 'CAPTCHA'); ?></label>
										<div style="max-width: 250px;"><?php echo f::form_captcha('review_product', ['required' => '']); ?></div>
									</div>
									<?php } ?>

									<?php echo f::form_button('submit_review', t('title_submit', 'Submit'), 'submit'); ?>

									<?php } ?>
								</fieldset>

							<?php echo f::form_end(); ?>

						</div>
					</div>
				</div>
			</section>
			<?php //} ?>

		</article>

		<?php include 'app://frontend/partials/box_similar_products.inc.php'; ?>

		<?php include 'app://frontend/partials/box_also_purchased_products.inc.php'; ?>

	</div>

</main>

<script>

	$('#quantity-prices tbody tr').on('click', function(){
		let min_quantity = $(this).data('min-quantity');
		$('#box-product input[name="quantity"]').val(min_quantity).trigger('input');
	});

	$('#box-product input[name="quantity"]').on('input', function(){
		let current_quantity = $(this).val();
		$('#quantity-prices tbody tr').each(function(i, row){
			let $row = $(row);
			if ($row.data('min-quantity') <= current_quantity) {
				$row.addClass('selected');
				$row.siblings('.selected').removeClass('selected');
			}
		});
	}).trigger('input');

	$('#box-product[data-id="<?php echo $product_id; ?>"] form[name="buy_now_form"]').on('input', function(e) {

		var $form = $(this),
			regular_price = <?php echo (float)$regular_price; ?>,
			final_price = <?php echo (float)$final_price; ?>,
			tax = <?php echo (float)$tax; ?>;

		if (regular_price == 0) {
			return;
		}

		if ($('#quantity-prices tbody tr.selected').length) {

			let $row = $('#quantity-prices tbody tr.selected'),
				quantity_price = Number($row.data('final-price')),
				quantity_tax = Number($row.data('tax'));

			if (quantity_price < final_price) {
				final_price = quantity_price;
				tax = quantity_tax;
			}
		}

		$form.find('input[type="radio"]:checked, input[type="checkbox"]:checked').each(function() {
			if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
			if ($(this).data('price-adjust')) final_price += $(this).data('price-adjust');
			if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
		});

		$form.find('select option:checked').each(function() {
			if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
			if ($(this).data('price-adjust')) final_price += $(this).data('price-adjust');
			if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
		});

		$form.find('input[type!="radio"][type!="checkbox"]').each(function() {
			if ($(this).val() != '') {
				if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
				if ($(this).data('price-adjust')) final_price += $(this).data('price-adjust');
				if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
			}
		});

		$form.find('#price .regular-price').text(regular_price.toMoney());
		$form.find('#price .final-price').text(final_price.toMoney());
		if (!$form.find('#price .final-price').length) { // If there is no separate regular and final price (e.g. when there is only one price), then update the displayed price with the adjusted final price.
			$form.find('#price .regular-price').text(final_price.toMoney());
		}
		$form.find('#price .tax').text(tax.toMoney());
	});

	$('#box-product form[name="buy_now_form"] .options :input').on('change', function() {

		$.ajax({
			type: 'post',
			url: '<?php echo document::ilink('ajax/product_options_stock.json'); ?>',
			data: $(this).closest('form').serialize(),
			dataType: 'json',
			cache: false,
			success: function(data) {
				if (data.status == 'ok') {
					$('.stock-notice').text(data.notice).removeClass('warning');
				} else if (data.status == 'warning') {
					$('.stock-notice').text(data.notice).addClass('warning');
				} else {
					$('.stock-notice').html('');
				}
			}
		});
	});

	$('#box-product[data-id="<?php echo $product_id; ?>"] .social-bookmarks .link').off().on('click', function(e) {
		e.preventDefault();
		prompt("<?php echo t('text_link_to_this_product', 'Link to this product'); ?>", "<?php echo f::escape_attr($link); ?>");
	});

	// Reviews

	$('.average-rating a[href="#reviews"]').on('click', function(){
		$('html, body').animate({
				scrollTop: $('#reviews').offset().top - 20
		}, 500);
	});

	$('.review .upvote').click(function(e){
		var $button = $(this);
		var $review = $(this).closest('.review');
		e.preventDefault();
		$.ajax({
			type: 'post',
			url: '<?php echo document::ilink('ajax/review'); ?>?review_id='+ $review.data('review-id'),
			data: 'upvote=true',
			success: function(result){
				$button.find('.num-votes').text(result.upvotes);
			}
		});
	});

	$('.review .downvote').click(function(e){
		var $review = $(this).closest('.review');
		var $button = $(this);
		e.preventDefault();
		$.ajax({
			type: 'post',
			url: '<?php echo document::ilink('ajax/review'); ?>?review_id='+ $review.data('review-id'),
			data: 'review_id='+ $review.data('review-id') +'&downvote=true',
			success: function(result){
				$button.find('.num-votes').text(result.downvotes);
			}
		});
	});

	$('.attachments').on('click', 'button[name="remove"]', function(e) {
		e.preventDefault();
		$(this).closest('.form-group').remove();
		refreshMainImage();
	});

	$('.attachments .add').click(function(e) {
		e.preventDefault();
		var output = [
			'<div class="attachment form-group">',
			'  <div class="input-group">',
			'    <?php echo f::escape_js(f::form_input_file('new_attachments[]', ['accept' => '.gif,.jpg,.png'])); ?>',
			'    <?php echo f::escape_js(f::form_button_predefined('remove-sm')); ?>',
			'  </div>',
			'</div>'
		].join('\n');
		$('.attachments .new-attachments').append(output);
	});
</script>