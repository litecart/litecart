<?php

	/*
		This file contains PHP logic that is separated from the HTML view.
		Visual changes can be made to the file found in the template folder:
		- frontend/templates/default/partials/box_product_reviews.inc.php
	*/

	if (empty($_GET['product_id'])) return;

	$product = reference::product($_GET['product_id']);

	if (empty($product->id)) return;

	// Handle submit review

	if (!empty($_POST['submit_review'])) {

		try {

			if (settings::get('captcha') && !f::captcha_validate('review_product')) {
				throw new Exception(t('error_invalid_captcha', 'Invalid CAPTCHA given'));
			}

			if (empty(customer::$data['id'])) {
				throw new Exception(t('error_must_be_logged_in', 'You must be logged in'));
			}

			if (empty($_POST['rating'])) {
				throw new Exception(t('error_must_select_rating', 'You must select a rating'));
			}

			// The review text is posted as "description" (matching the entity field), older templates post it as "review"
			$review_text = $_POST['description'] ?? $_POST['review'] ?? '';
			$review_text = is_scalar($review_text) ? trim((string)$review_text) : '';

			if ($review_text === '') {
				throw new Exception(t('error_must_enter_a_review', 'You must enter a review'));
			}

			$review_title = $_POST['title'] ?? '';
			$review_title = is_scalar($review_title) ? trim((string)$review_title) : '';

			$review = database::query(
				"select * from ". DB_PREFIX ."reviews
				where product_id = ". (int)$product->id ."
				and customer_id = ". (int)customer::$data['id'] ."
				limit 1;"
			)->fetch();

			if ($review) {
				$review = new ent_review($review['id']);
			} else {
				$review = new ent_review();
				$review->data['customer_id'] = customer::$data['id'];
				$review->data['customer_name'] = customer::$data['company'] ?: trim((customer::$data['firstname'] ?? '').' '. (customer::$data['lastname'] ?? ''));
				$review->data['customer_email'] = customer::$data['email'];
				$review->data['product_id'] = $product->id;
			}

			$review->data['status'] = 0;
			$review->data['rating'] = (int)$_POST['rating'];
			$review->data['title'][language::$selected['code']] = $review_title;
			$review->data['description'][language::$selected['code']] = $review_text;

			// Keep only the attachments that were posted back (removed ones are dropped)
			$posted_attachment_ids = empty($_POST['attachments']) ? [] : array_column($_POST['attachments'], 'id');

			foreach ($review->data['attachments'] as $key => $attachment) {
				if (!in_array($attachment['id'], $posted_attachment_ids)) {
					unset($review->data['attachments'][$key]);
				}
			}

			if (!empty($_FILES['new_attachments']['tmp_name'])) {
				foreach (array_keys($_FILES['new_attachments']['tmp_name']) as $key) {
					$review->add_attachment($_FILES['new_attachments']['tmp_name'][$key], $_FILES['new_attachments']['name'][$key], $_FILES['new_attachments']['type'][$key]);
				}
			}

			$review->save();

			$message = strtr(t('email_body_product_reviewed', "{customer_email} has reviewed the product {product_name} giving it a rating of {rating} stars.\r\n\r\n{review}"), [
				'{customer_email}' => customer::$data['email'],
				'{product_name}' => $product->name,
				'{rating}' => (int)$_POST['rating'],
				'{review}' => $review_text,
			]);

			(new ent_email())
				->add_recipient(settings::get('store_email'))
				->set_subject(t('email_subject_product_reviewed', 'Product Review'))
				->add_body($message)
				->send();

			notices::add('success', t('success_thank_you_for_reviewing_product', 'Thank you for reviewing this product'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Ratings

	$ratings = ent_review::get_ratings((int)$product->id);

	// Reviews

	$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

	$reviews = ent_review::get_for_product((int)$product->id, $page, 10, $num_rows, $num_pages);

	document::$schema['product']['reviews'] = [];

	foreach ($reviews as $review) {

		document::$schema['product']['reviews'][] = [
			'@type' => 'Review',
			'author' => [
				'@type' => 'Person',
				'name' => $review['customer_name'],
			],
			'datePublished' => date('Y-m-d', strtotime($review['created_at'])),
			'name' => (string)$review['title'],
			'description' => (string)$review['description'],
			'reviewRating' => [
				'@type' => 'Rating',
				'bestRating' => 5,
				'ratingValue' => $review['rating'],
				'worstRating' => 1,
			],
		];
	}

	// Customer's own review

	$customer_review = ent_review::get_for_customer((int)$product->id, (int)customer::$data['id']);

	// Reviews this session has already voted on

	$voted_reviews = empty(session::$data['voted_reviews']) ? [] : (array)session::$data['voted_reviews'];

	// The review form keeps the posted values when a submission failed

	$posted_rating = $_POST['rating'] ?? ($customer_review['rating'] ?? 0);
	$posted_title = $_POST['title'] ?? ($customer_review['title'] ?? '');
	$posted_description = $_POST['description'] ?? ($customer_review['description'] ?? '');

	// Attachments already on the review, unless the form was posted back
	$form_attachments = [];

	if (!empty($_POST['attachments'])) {
		foreach ($_POST['attachments'] as $attachment) {
			if (empty($attachment['id'])) continue;
			$form_attachments[] = [
				'id' => $attachment['id'],
				'filename' => $attachment['filename'] ?? '',
			];
		}
	} else if (!empty($customer_review['attachments'])) {
		foreach ($customer_review['attachments'] as $attachment) {
			if (empty($attachment['id'])) continue;
			$form_attachments[] = [
				'id' => $attachment['id'],
				'filename' => $attachment['filename'] ?? '',
			];
		}
	}

	$review_form = [
		'rating' => is_array($posted_rating) ? 0 : (int)$posted_rating,
		'title' => is_array($posted_title) ? '' : (string)$posted_title,
		'description' => is_array($posted_description) ? '' : (string)$posted_description,
		'attachments' => $form_attachments,
	];

	$box_product_reviews = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_product_reviews.inc.php', [
		'product' => $product,
		'reviews' => $reviews,
		'num_reviews' => (int)$ratings['num_ratings'],
		'average_rating' => (float)$ratings['average_rating'],
		'review_form' => $review_form,
		'voted_reviews' => $voted_reviews,
		'pagination' => f::draw_pagination($num_pages),
	]);

	// Render the reviews
	echo $box_product_reviews->render();
