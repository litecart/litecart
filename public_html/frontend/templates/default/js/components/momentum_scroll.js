/*
 * Momentum Scroll
 * by LiteCart
 */

+function($) {

	$.fn.momentumScroll = function() {
		this.each(function() {

			let $self = $(this),
				$content = $self.find('.scroll-content')
				direction = '',
				velX = 0,
				clickX = 0,
				scrollX = 0,
				clicked = false,
				dragging = false,
				momentumID = null;

			if ($(this).width() <= 768) {
				$content.css('overflow', 'auto');
			}

			let momentumLoop = function() {

				if (direction == 'left') {
					$content.scrollLeft($content.scrollLeft() - velX); // Apply the velocity to the scroll position
				} else {
					$content.scrollLeft($content.scrollLeft() + velX);
				}

				velX *= 1 - 5 / 100; // Slow down the velocity 5%

				if (Math.abs(velX) > 0.5) { // Still moving?
					momentumID = requestAnimationFrame(momentumLoop); // Keep looping
				}
			}

			$content.on({

				'click': function(e) {
					if (dragging) {
						e.preventDefault();
					}
					dragging = false;
				},

				'mousemove': function(e) {
					if (!clicked) return;

					dragging = true;

					let prevScrollLeft = $content.scrollLeft(); // Store the previous scroll position
						currentDrag = (clickX - e.pageX);

					$content.scrollLeft(scrollX + (clickX - e.pageX));

					if (currentDrag > 0) {
						direction = 'right';
					} else {
						direction = 'left';
					}

					velX = Math.abs($content.scrollLeft() - prevScrollLeft); // Compare change in position to work out drag speed
				},

				'mousedown': function(e) {
					e.preventDefault();
					clicked = true;
					scrollX = $content.scrollLeft();
					clickX = e.pageX;
					$content.css('cursor', 'grabbing');
				},

				'mouseup': function(e) {
					e.preventDefault();
					self = this;
					clicked = false;
					cancelAnimationFrame(momentumID);
					momentumID = requestAnimationFrame(momentumLoop);
					$content.css('cursor', '');
				},

				'mouseleave': function(e) {
					clicked = false;
					$content.css('cursor', '');
				}
			});

			$(window).on('resize', function(){

				if ($content.prop('scrollWidth') > ($self.outerWidth() + 20)) {

					if (!$self.find('button[name="left"], button[name="right"]').length) {

						$self.append(
							'<button name="left" class="btn btn-default" type="button"><i class="fa fa-chevron-left"></i></button>' +
							'<button name="right" class="btn btn-default" type="button"><i class="fa fa-chevron-right"></i></button>'
						);

						$self.on('click', 'button[name="left"], button[name="right"]', function(e) {
							if (direction != $(this).attr('name')) {
								velX = 0;
							}
							cancelAnimationFrame(momentumID);
							velX += Math.round($self.outerWidth() * 0.03);
							direction = $(this).attr('name');
							momentumID = requestAnimationFrame(momentumLoop);

						});
					}

				} else {
					$self.find('button[name="left"], button[name="right"]').remove();
				}

				/*
				if ($(window).width() > ($self.outerWidth() + 45)) {
					$self.find('button[name="left"]').css('left', '');
					$self.find('button[name="right"]').css('right', '');
				} else {
					$self.find('button[name="left"]').css('left', 0);
					$self.find('button[name="right"]').css('right', 0);
				}
				*/

			}).trigger('resize');
		});
	}

	$('[data-toggle*="momentumScroll"]').momentumScroll();

}(jQuery);
