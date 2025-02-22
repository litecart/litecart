/*
 * jQuery Momentum Scroll
 * by LiteCart
 */

+function($) {

	$.fn.momentumScroll = function(){
		this.each(function() {

			this.$element = $(this);
			this.clicked = null;
			this.dragging = false;
			this.clickX = 0;
			this.scrollX = 0;
			this.velX = 0;
			this.momentumID = null;

			this.$element.on({

				'click': function(e) {
					if (this.dragging) {
						e.preventDefault();
					}
					this.dragging = false;
				},

				'mousemove': function(e) {
					if (!this.clicked) return;
					this.dragging = true;
					let prevScrollLeft = this.$element.scrollLeft(); // Store the previous scroll position
					this.$element.scrollLeft(this.scrollX + (this.clickX - e.pageX));
					this.velX = this.$element.scrollLeft() - prevScrollLeft; // Compare change in position to work out drag speed
				},

				'mousedown': function(e) {
					e.preventDefault();
					this.clicked = true;
					this.scrollX = this.$element.scrollLeft();
					this.clickX = e.pageX;
					this.$element.css('cursor', 'grabbing');
				},

				'mouseup': function(e) {
					e.preventDefault();
					self = this;
					this.clicked = false;
					let momentumLoop = function() {
						self.$element.scrollLeft( self.$element.scrollLeft() + self.velX ); // Apply the velocity to the scroll position
						self.velX *= 0.90; // Slow the velocity slightly
						if (Math.abs(self.velX) > 0.5){ // Still moving?
							self.momentumID = requestAnimationFrame(momentumLoop); // Keep looping
						}
					}
					cancelAnimationFrame(self.momentumID);
					self.momentumID = requestAnimationFrame(momentumLoop);
					self.$element.css('cursor', 'grab');
				},

				'mouseleave': function(e) {
					this.clicked = false;
					this.$element.css('cursor', 'grab');
				}

			});
		});
	}

	$('[data-toggle*="momentumScroll"]').momentumScroll();

}(jQuery);

/*
 * jQuery Auto Scroll
 * by LiteCart
 */

+function($) {

	$.fn.autoScroll = function(){
		this.each(function() {

			this.$element = $(this);
			this.scrollInterval = null;
			this.scrollDirection = 'right';

			this.$element.on({

				'mouseenter': function(e) {
					this.$element.trigger('scrollStop');
				},

				'mouseleave': function(e) {
					if (!this.scrollInterval) {
						this.$element.trigger('scrollStart');
					}
				},

				'scrollStart': function(){
					let self = this;
					self.scrollInterval = setInterval(function() {
						if (self.scrollDirection != 'left') {
							self.$element.scrollLeft(self.$element.scrollLeft() + 1);
							if ((self.$element.scrollLeft() + self.$element.width()) >= self.$element[0].scrollWidth) {
								self.scrollDirection = 'left';
							}
						} else {
							self.$element.scrollLeft(self.$element.scrollLeft() - 1);
							if (self.$element.scrollLeft() == 0) {
								self.scrollDirection = 'right';
							}
						}
					}, 100);
				},

				'scrollStop': function() {
					clearInterval(this.scrollInterval);
					this.scrollInterval = null;
				}

			}).trigger('scrollStart');

		});
	}

	$('[data-toggle*="autoScroll"]').autoScroll();

}(jQuery);