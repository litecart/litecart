
	// Execute third party code here
		// ...

	// Execute third party code here if the user prefer privacy rather than personalized content
	if (window.doNotTrack || navigator.doNotTrack) {
			// ...
	}

	// Execute third party code here that needs cookie consent
	$(document).on('cookiesAccepted', function() {
			// ...
	});
