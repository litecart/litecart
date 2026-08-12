/* Minimal waitFor() implementation
	Calls callback when objectName is defined in the global scope
	waitTime is the time between retries, in milliseconds (default 50ms)
	retries is the number of times to retry before giving up (default 100)
 */
window.waitFor = (objectName, callback, waitTime=50, retries=100) => {

	if (typeof(objectName) !== 'string') {
		throw new TypeError('First argument to waitFor() must be a string');
	}

	if (typeof(window[objectName]) !== 'undefined') {
		callback(window[objectName]);

	} else if (retries > 0) {

		setTimeout(() => {
			waitFor(objectName, callback, waitTime, --retries);
		}, waitTime);

	} else {
		console.warn(`waitFor(${objectName}) timed out`);
	}
};
