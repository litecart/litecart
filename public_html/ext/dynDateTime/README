***************************
* dynDateTime: a jQuery date+time picker
*
* http://plugins.jquery.com/project/dyndatetime
* http://code.google.com/p/dyndatetime
*
* Author: thetoolman@gmail.com
*
***************************

This jQuery plugin makes it easy to add date and time selection to single textfield inputs.

jQuery("input.dateField").dynDateTime();

This example will make all input elements tagged with the 'dateField' class.  There are plenty of options for configuration; see details in documentation.

***************************

	
	The options (and plenty of code) are taken from the calendar-setup.js, and most options (all, except top 3 listed below)
	are unchanged from the original Dynarch code. For full docs, see:
		
		http://www.dynarch.com/demos/jscalendar/doc/html/reference.html#node_sec_2.3


	Changed opts  | description
	-------------------------------------------------------------------------------------------------
	displayArea   | (String) relative jQuery traverse method(s); find first element and show the date in it. eg ".siblings('div.dispArea')"  
	button        | (String) relative jQuery traverse method(s); find first element and bind show/hide event for calendar. eg ".siblings('button')"
	flat          | (String) relative jQuery traverse method(s); find first element and place the flat calendar within. eg ".parent().siblings('div.flatCal')"

	The above all use jQuery traverse methods as detailed here: http://docs.jquery.com/Traversing


	option        | description
	-------------------------------------------------------------------------------------------------
	eventName     | event that will trigger the calendar, without the "on" prefix (default: "click")

	flatCallback  | function that receives a JS Date object and returns an URL to point the browser to (for flat calendar)
	dateStatusFunc| function that receives a JS Date object and should return true if that date has to be disabled in the calendar, or String for CSS classes.
	onSelect      | function that gets called when a date is selected.  You don't _have_ to supply this (the default is generally okay)
	onClose       | function that gets called when the calendar is closed.  [default]
	onUpdate      | function that gets called after the date is updated in the input field.  Receives a reference to the calendar.

	date          | the date that the calendar will be initially displayed to
	
	ifFormat      | date format that will be stored in the input field
	daFormat      | the date format that will be used to display the date in displayArea
	timeFormat    | the time format; can be "12" or "24", default is "12"

	showsTime     | default: false; if true the calendar will include a time selector
	align         | alignment (default: "Br"); if you don't know what's this see the calendar documentation
	range         | array with 2 elements.  Default: [1900, 2999] -- the range of years available
	singleClick   | (true/false) whether the calendar is in single click mode or not (default: true)
	firstDay      | numeric: 0 to 6.  "0" means display Sunday first, "1" means display Monday first, etc.
	weekNumbers   | (true/false) if it's true (default) the calendar will display week numbers
	electric      | if true (default) then given fields/date areas are updated for each move; otherwise they're updated only on close
	step          | configures the step of the years in drop-down boxes; default: 2
	position      | configures the calendar absolute position; default: null
	cache         | if "true" (but default: "false") it will reuse the same calendar object, where possible
	showOthers    | if "true" (but default: "false") it will show days from other months too

	debug         | if "true" (but default: "false") write to firebug console

***************************

It is a fork of Dynarch.com's (LGPL) DHTML Calendar v1.0, and the initial codebase is little more then a jQuery wrapper.

Author: Mihai Bazon, <mihai_bazon@yahoo.com>
    http://dynarch.com/mishoo/
	http://www.dynarch.com/projects/calendar/

The fork source is wonderful tool that ceased LGPL development in 2005.  
