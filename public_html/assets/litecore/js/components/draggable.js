waitFor('jQuery', ($) => {
	"use strict";

	$('<style>', {type: 'text/css'})
		.html([
			'[draggable="true"] .grabbed { opacity: 0.5; }',
			'[draggable="true"] .grabbable { cursor: ns-resize; }',
		].join('\n'))
		.appendTo('head');

	$.fn.draggable = function(options) {
		// Default settings
		var settings = $.extend({
			handle: '.grabbable',
			cursor: 'ns-resize',
			direction: 'vertical'
		}, options);

		return this.each(function() {
			var $self = $(this);

			// Add basic styling
			$self.css({
				'position': 'relative',
				'user-select': 'none'
			});

			// Store settings
			$self.data('draggable-settings', settings);

			// Prevent text selection while dragging
			$self.on('dragstart selectstart', function() {
				return false;
			});
		});
	};

	// Global dragging state
	var isDragging = false;
	var dragElement = null;

	// Use event delegation for mouse events to handle dynamic elements
	$(document).on('mousedown', '[draggable="true"] .grabbable', function(e) {
		e.preventDefault();

		var $handle = $(this);
		var $element = $handle.closest('[draggable="true"]');

		// Initialize if needed
		if (!$element.data('draggable-settings')) {
			$element.draggable();
		}

		isDragging = true;
		dragElement = $element;

		$element.addClass('grabbed');
		$element.parent().addClass('dragging');
	});

	$(document).on('mousemove', function(e) {
		if (!isDragging || !dragElement) return;
		e.preventDefault();

		var mouseY = e.pageY;
		var $siblings = dragElement.siblings(':not(.grabbed)');

		$siblings.each(function() {
			var $sibling = $(this);
			var siblingOffset = $sibling.offset();
			var siblingHeight = $sibling.outerHeight();
			var siblingTop = siblingOffset.top;
			var siblingBottom = siblingOffset.top + siblingHeight;

			// Move as soon as mouse enters sibling bounds
			if (mouseY >= siblingTop && mouseY <= siblingBottom) {
				if (dragElement.index() > $sibling.index()) {
					// Moving up - place before sibling
					$sibling.before(dragElement);
				} else if (dragElement.index() < $sibling.index()) {
					// Moving down - place after sibling
					$sibling.after(dragElement);
				}
			}
		});
	});

	$(document).on('mouseup', function(e) {
		if (!isDragging) return;

		isDragging = false;
		if (dragElement) {
			dragElement.removeClass('grabbed');
			dragElement.parent().removeClass('dragging');
			dragElement = null;
		}
	});

	// Initialize existing elements
	$('[draggable="true"]').draggable();
});