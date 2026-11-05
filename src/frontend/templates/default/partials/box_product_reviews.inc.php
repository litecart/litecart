<section id="reviews" class="card" aria-label="<?= f::escape_attr(t('title_reviews', 'Reviews')) ?>">
  <a name="reviews"></a>

  <div class="card-header">
    <div class="card-title">
      <?= t('title_reviews', 'Reviews') ?>
    </div>

    <?php if ($reviews) { ?>
    <div class="card-subtitle">
      <span class="rating" aria-label="<?= f::escape_attr(t('title_average_rating', 'Average rating').': '. round($average_rating * 2) .'/10') ?>">
        <?= f::draw_rating($average_rating) ?>
      </span>
      <span class="review-count"><?= f::escape_html(strtr(t('text_review_count', '{count} reviews'), ['{count}' => (int)$num_reviews])) ?></span>
    </div>
    <?php } ?>
  </div>

  <div class="card-body">
    <div class="notices" role="status" aria-live="polite"></div>

    <div class="row">
      <div class="col-md-7">

        <?php if ($reviews) { ?>
        <div class="reviews" role="list" aria-label="<?= f::escape_attr(t('title_reviews', 'Reviews')) ?>">
          <?php foreach ($reviews as $review) { ?>
          <?php $initials = mb_strtoupper(mb_substr(trim($review['customer_name']), 0, 1)); ?>
          <article class="review" data-review-id="<?= (int)$review['id'] ?>" role="listitem" aria-label="<?= f::escape_attr(strtr(t('title_review_by', 'Review by {author}'), ['{author}' => $review['customer_name']])) ?>">

            <div class="avatar" aria-hidden="true"><?= f::escape_html($initials) ?></div>

            <div class="review-body">

              <div class="review-header">
                <div class="name">
                  <?= f::escape_html($review['customer_name']) ?>
                </div>

                <div class="rating" aria-label="<?= f::escape_attr(t('title_rating', 'Rating').': '. round($review['rating'] * 2) .'/10') ?>">
                  <?= f::draw_rating($review['rating']) ?>
                </div>

                <div class="date">
                  <?= f::escape_html(f::datetime_format('date', $review['created_at'])) ?>
                </div>
              </div>

              <div class="title">
                <?= f::escape_html((string)$review['title']) ?>
              </div>

              <div class="description">
                <?= nl2br(f::escape_html((string)$review['description'])) ?>
              </div>

              <div class="vote">
                <a class="upvote<?= in_array((int)$review['id'], $voted_reviews) ? ' voted' : '' ?>" href="#" aria-label="<?= f::escape_attr(t('title_upvote', 'Upvote')) ?>">
                  <?= f::draw_fonticon('icon-thumbs-up-1', 'aria-hidden="true"') ?> <span class="num-votes" aria-label="<?= (int)$review['upvotes'] ?>"><?= (int)$review['upvotes'] ?></span>
                </a>
                <a class="downvote<?= in_array((int)$review['id'], $voted_reviews) ? ' voted' : '' ?>" href="#" aria-label="<?= f::escape_attr(t('title_downvote', 'Downvote')) ?>">
                  <?= f::draw_fonticon('icon-thumbs-down', 'aria-hidden="true"') ?> <span class="num-votes" aria-label="<?= (int)$review['downvotes'] ?>"><?= (int)$review['downvotes'] ?></span>
                </a>
              </div>

              <?php if ($review['attachments']) { ?>
              <div class="attachments" aria-label="<?= f::escape_attr(t('title_attachments', 'Attachments')) ?>">

                <?php foreach ($review['attachments'] as $attachment) { ?>
                <div class="attachment">
                  <?= match(true) {

                    (preg_match('#\.(bmp|gif|jpe?g|png)$#', $attachment['filename']) === 1)
                      => '<a href="'. f::escape_html($attachment['link']) .'" class="thumbnail" data-toggle="lightbox" data-type="image" aria-label="'. f::escape_attr($attachment['filename']) .'"><img src="'. f::escape_attr(document::href_rlink(f::image_thumbnail($attachment['attachment'], 96, 96))) .'" alt="'. f::escape_attr($attachment['filename']) .'"></a>',

                    (preg_match('#\.(avi|mp4|mov)$#', $attachment['filename']) === 1)
                      => '<a href="'. f::escape_html($attachment['link']) .'" class="thumbnail text-center" aria-label="'. f::escape_attr($attachment['filename']) .'">'. f::draw_fonticon('icon-film', 'aria-hidden="true"') .'</a>',

                    default
                      => '<a href="'. f::escape_html($attachment['link']) .'" class="thumbnail text-center" aria-label="'. f::escape_attr($attachment['filename']) .'">'. f::draw_fonticon('icon-paperclip', 'aria-hidden="true"') .'</a>'

                  }; ?>
                </div>
                <?php } ?>

              </div>
              <?php } ?>

            </div>
          </article>
          <?php } ?>
        </div>

        <?php if (!empty($reviews) && !empty($pagination)) { ?>
        <div class="reviews-pagination">
          {{pagination}}
        </div>
        <?php } ?>

        <?php } else { ?>
        <div class="reviews-empty">
          <?= f::draw_fonticon('icon-comments', 'aria-hidden="true"') ?>
          <p><?= t('text_be_first_to_post_review_for_product', 'Be the first to post a review for this product') ?></p>
        </div>
        <?php } ?>

      </div>

      <div class="col-md-5">

        <?= f::form_begin('rating_form', 'post', '', true, ['aria-label' => f::escape_attr(t('title_reviews', 'Reviews'))]) ?>

          <fieldset<?= empty(customer::$data['id']) ? ' disabled aria-disabled="true"' : '' ?>>
            <h3><?= t('title_rate_this_product', 'Rate This Product') ?></h3>

            <?php if (empty(customer::$data['id'])) { ?>

            <div class="text-muted">
              <?= t('text_must_be_signed_in_to_rate_product', 'You must be signed in to rate this product') ?>
            </div>

            <div>
              <a class="btn btn-default" href="<?= document::ilink('account/sign_in', ['redirect_url' => document::link()]) ?>#box-login" data-toggle="lightbox" data-seamless="true" data-width="480px" data-require-window-width="768">
                <?= t('title_sign_in', 'Sign In') ?>
              </a>
            </div>

            <?php } else { ?>

            <div class="form-group">
              <div class="rate-now" role="radiogroup" aria-label="<?= f::escape_attr(t('title_rate_this_product', 'Rate This Product')) ?>">
                <?php foreach (range(5, 1) as $i) { ?>
                <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>"<?= ($review_form['rating'] == $i) ? ' checked' : '' ?>>
                  <label for="star<?= $i ?>" aria-label="<?= f::escape_attr($i .'/5 '. t('title_stars', 'stars')) ?>"><?= f::draw_fonticon('icon-star', 'aria-hidden="true"') ?></label>
                <?php } ?>
              </div>
            </div>

            <div class="form-group">
              <label for="review_title" class="hidden"><?= t('title_title', 'Title') ?></label>
              <?= f::form_input_text('title', $review_form['title'], ['id' => 'review_title', 'autocomplete' => 'off', 'placeholder' => f::escape_html(t('title_title', 'Title'))]) ?>
            </div>

            <div class="form-group">
              <label for="review_description" class="hidden"><?= t('title_review', 'Review') ?></label>
              <?= f::form_textarea('description', $review_form['description'], ['id' => 'review_description', 'autocomplete' => 'off', 'placeholder' => f::escape_html(t('title_review', 'Review'))]) ?>
            </div>

            <div class="attachments" aria-label="<?= f::escape_attr(t('title_attachments', 'Attachments')) ?>">

              <div class="current-attachments">
                <?php foreach ($review_form['attachments'] as $key => $attachment) { ?>
                <div class="attachment form-group">
                  <label><?= t('title_attachment', 'Attachment') ?></label>
                  <?= f::form_input_hidden('attachments['.$key.'][id]', $attachment['id']) ?>
                  <?= f::form_input_hidden('attachments['.$key.'][filename]', $attachment['filename']) ?>
                  <div class="input-group">
                    <div class="form-control"><?= f::escape_html($attachment['filename']) ?></div>
                    <div class="input-group-text">
                      <?= f::form_button_predefined('move-up-sm') ?>
                      <?= f::form_button_predefined('move-down-sm') ?>
                      <?= f::form_button_predefined('remove-sm') ?>
                    </div>
                  </div>
                </div>
                <?php } ?>
              </div>

              <div class="new-attachments">
              </div>

              <div class="form-group">
                <p><?= t('text_add_review_attachment', 'Add a photo to your review.') ?></p>
                <a href="#" class="add" title="<?= f::escape_attr(t('text_add', 'Add')) ?>" aria-label="<?= f::escape_attr(t('title_add_attachment', 'Add Attachment')) ?>"><?= f::draw_fonticon('add', 'aria-hidden="true"') ?> <?= t('title_add_attachment', 'Add Attachment') ?></a>
              </div>
            </div>

            <?php if (settings::get('captcha')) { ?>
            <div class="form-group">
              <label><?= t('title_captcha', 'CAPTCHA') ?></label>
              <div style="max-width: 250px;"><?= f::form_captcha('review_product', ['required' => true]) ?></div>
            </div>
            <?php } ?>

            <?= f::form_button('submit_review', t('title_submit', 'Submit'), 'submit') ?>

            <?php } ?>
          </fieldset>

        <?= f::form_end() ?>

      </div>
    </div>
  </div>
</section>
