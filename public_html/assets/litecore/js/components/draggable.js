waitFor('jQuery', ($) => {
	"use strict";

	$('<style>', {type: 'text/css'})
		.html('.grabbed { opacity: 0.5; }')
		.appendTo('head');

	$.fn.draggable = function(options) {

		// Default settings
		var settings = $.extend({
			handle: null,
			cursor: 'ns-resize',
			direction: 'vertical' // Default direction
		}, options);

		return this.each(function() {
			var $self = $(this),
					$handle = settings.handle ? $self.find(settings.handle) : $self,
					dragging = false,
					startPos = null;

			// Add basic styling
			$self.css({
				'position': 'relative',
				'user-select': 'none'
			});

			$handle.css({
				'cursor': settings.cursor
			});

			// Mouse down handler
			$handle.on('mousedown', function(e) {
				e.preventDefault();
				dragging = true;
				startPos = {
					x: e.pageX,
					y: e.pageY
				};
				$self.addClass('grabbed');
				$self.parent().addClass('dragging');

				// Store original position
				$self.data('original-index', $self.index());
			});

			// Mouse move handler
			$(document).on('mousemove', function(e) {
				if (!dragging) return;
				e.preventDefault();

				var $siblings = $self.siblings().not('.grabbed'),
						selfHeight = $self.outerHeight(),
						selfWidth = $self.outerWidth(),
						selfOffset = $self.offset(),
						selfTopY = selfOffset.top,
						selfBottomY = selfOffset.top + selfHeight,
						selfLeftX = selfOffset.left,
						selfRightX = selfOffset.left + selfWidth,
						mouseX = e.pageX,
						mouseY = e.pageY;

				// Find the sibling to swap with
				$siblings.each(function() {
					var $sibling = $(this),
							siblingOffset = $sibling.offset(),
							siblingHeight = $sibling.outerHeight(),
							siblingWidth = $sibling.outerWidth(),
							siblingTop = siblingOffset.top,
							siblingBottom = siblingOffset.top + siblingHeight,
							siblingLeft = siblingOffset.left,
							siblingRight = siblingOffset.left + siblingWidth;

					if (settings.direction === 'vertical') {
						// Moving up: use self's top Y position
						if (mouseY < selfTopY && siblingBottom > selfTopY && siblingTop < selfTopY) {
							$sibling.before($self);
						}
						// Moving down: use self's bottom Y position
						else if (mouseY > selfBottomY && siblingTop < selfBottomY && siblingBottom > selfBottomY) {
							$sibling.after($self);
						}
					} else if (settings.direction === 'horizontal') {
						// Moving left: use self's left X position
						if (mouseX < selfLeftX && siblingRight > selfLeftX && siblingLeft < selfLeftX) {
							$sibling.before($self);
						}
						// Moving right: use self's right X position
						else if (mouseX > selfRightX && siblingLeft < selfRightX && siblingRight > selfRightX) {
							$sibling.after($self);
						}
					}
				});
			});

			// Mouse up handler
			$(document).on('mouseup', function(e) {
				if (!dragging) return;
				dragging = false;
				$self.removeClass('grabbed');
				$self.parent().removeClass('dragging');
			});

			// Prevent text selection while dragging
			$self.on('dragstart selectstart', function() {
				return false;
			});
		});
	};

	// Initialize draggable elements
	$('[draggable="true"]').draggable({
		handle: '.grabbable',
		cursor: 'ns-resize',
		direction: 'vertical' // Default direction
	});

});