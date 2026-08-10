(async () => {

	const report = {
		userAgent: navigator.userAgent,
		language: navigator.language,
		hardwareConcurrency: navigator.hardwareConcurrency || 0,
		deviceMemory: navigator.deviceMemory || 0,
		timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
		plugins: navigator.plugins?.length || 0,
		webdriver: !!navigator.webdriver,
	};

	let mouseMovements = 0,
		keyPresses = 0,
		clicks = 0,
		scrollEvents = 0,
			timings = [],
		lastTimestamp = performance.now();

	function recordTiming() {
		const now = performance.now();
		timings.push(now - lastTimestamp);
		lastTimestamp = now;
	}

	onmousemove = () => {
		mouseMovements++;
		recordTiming();
	};

	onkeydown = () => keyPresses++;
	onclick = () => clicks++;
	onscroll = () => scrollEvents++;

	document.addEventListener('visibilitychange', recordTiming);
	window.addEventListener('focus', recordTiming);
	window.addEventListener('blur', recordTiming);

	await new Promise(r => setTimeout(r, 5000));

	// ----------------------------
	// ENTROPY
	// ----------------------------

	const avg =
		timings.reduce((a, b) => a + b, 0) / (timings.length || 1);

	const timingEntropy = Math.sqrt(
		timings.reduce((a, b) => a + (b - avg) ** 2, 0) /
		(timings.length || 1)
	);

	// ----------------------------
	// CANVAS
	// ----------------------------

	let canvasFingerprint = '';
	try {
		const canvas = document.createElement('canvas');
		const ctx = canvas.getContext('2d');

		ctx.textBaseline = 'top';
		ctx.font = '14px Arial';
		ctx.fillText('fp', 2, 2);

		canvasFingerprint = canvas.toDataURL();
	} catch {}

	// ----------------------------
	// WEBGL
	// ----------------------------

	let webglRenderer = '';
	try {
		const canvas = document.createElement('canvas');
		const gl = canvas.getContext('webgl');

		if (gl) {
			webglRenderer = gl.getParameter(gl.RENDERER) || '';
		}
	} catch {}

	// ----------------------------
	// BOT SCORE (risk heuristic)
	// ----------------------------

	let botScore = 0;

	// automation signal
	if (report.webdriver) botScore += 100;

	// no interaction
	if (mouseMovements === 0) botScore += 25;
	if (keyPresses === 0) botScore += 10;
	if (clicks === 0) botScore += 10;

	// suspicious timing (too smooth)
	if (timingEntropy < 20) botScore += 25;

	// weak environment signals
	if (!report.plugins) botScore += 5;

	// ----------------------------
	// CHECKSUM FINGERPRINT
	// ----------------------------

	const raw = JSON.stringify({
		report,
		behavior: {
			mouseMovements,
			keyPresses,
			clicks,
			scrollEvents
		},
		timingEntropy,
		canvasFingerprint,
		webglRenderer
	});

	let fingerprint = '';

	try {

		const encoder = new TextEncoder();
		const data = encoder.encode(raw);
		const hashBuffer = await crypto.subtle.digest('SHA-256', data);

		fingerprint = Array.from(new Uint8Array(hashBuffer))
			.map(b => b.toString(16).padStart(2, '0'))
			.join('');

	} catch {

		// Fallback: simple hash if crypto.subtle unavailable
		let hash = 0;

		for (let i = 0; i < raw.length; i++) {
			const char = raw.charCodeAt(i);
			hash = ((hash << 5) - hash) + char;
			hash = hash & hash;
		}

		fingerprint = Math.abs(hash).toString(16);
	}

	// ----------------------------
	// FINAL PAYLOAD
	// ----------------------------

	let report_url = window._env?.platform?.path + 'ajax/event';

	navigator.sendBeacon(report_url, JSON.stringify({
		type: 'challenge',
		data: {
			fingerprint,
			botScore,
			report
		}
	}));

})();