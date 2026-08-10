if (!localStorage.getItem('bot_challenge_passed')) {

	(async () => {

		const report = {
			started: performance.now(),
			events: 0,
			mouseMoves: 0,
			keyEvents: 0,
			touchEvents: 0,
			scrollEvents: 0,
			visibilityChanges: 0,
			focusChanges: 0,
			timings: [],
			entropy: 0,
			webdriver: false,
			plugins: 0,
			languages: [],
			hardwareConcurrency: 0,
			deviceMemory: null,
			canvas: null,
			webgl: null,
			suspicious: [],
		};

		// -------------------------------------------------------------------------
		// Basic automation detection
		// -------------------------------------------------------------------------

		report.webdriver = !!navigator.webdriver;

		if (report.webdriver) {
			report.suspicious.push('webdriver');
		}

		if (!window.chrome && navigator.userAgent.includes('Chrome')) {
			report.suspicious.push('fake_chrome');
		}

		report.plugins = navigator.plugins.length;
		report.languages = navigator.languages || [];
		report.hardwareConcurrency = navigator.hardwareConcurrency || 0;
		report.deviceMemory = navigator.deviceMemory || null;

		// -------------------------------------------------------------------------
		// Human interaction tracking
		// -------------------------------------------------------------------------

		let lastMove = null;

		document.addEventListener('mousemove', e => {

			report.events++;
			report.mouseMoves++;

			const now = performance.now();

			if (lastMove) {
				const dx = e.clientX - lastMove.x;
				const dy = e.clientY - lastMove.y;
				const dt = now - lastMove.t;

				if (dt > 0) {
					report.timings.push(
						Math.sqrt(dx * dx + dy * dy) / dt
					);
				}
			}

			lastMove = {
				x: e.clientX,
				y: e.clientY,
				t: now,
			};

		}, { passive: true });

		document.addEventListener('keydown', () => {
			report.events++;
			report.keyEvents++;
		});

		document.addEventListener('touchstart', () => {
			report.events++;
			report.touchEvents++;
		}, { passive: true });

		document.addEventListener('scroll', () => {
			report.events++;
			report.scrollEvents++;
		}, { passive: true });

		// -------------------------------------------------------------------------
		// Focus / visibility
		// -------------------------------------------------------------------------

		document.addEventListener('visibilitychange', () => {
			report.visibilityChanges++;
		});

		window.addEventListener('focus', () => {
			report.focusChanges++;
		});

		window.addEventListener('blur', () => {
			report.focusChanges++;
		});

		// -------------------------------------------------------------------------
		// Canvas fingerprint
		// -------------------------------------------------------------------------

		try {

			const canvas = document.createElement('canvas');

			canvas.width = 200;
			canvas.height = 50;

			const ctx = canvas.getContext('2d');

			ctx.textBaseline = 'top';
			ctx.font = '14px Arial';
			ctx.fillStyle = '#f60';
			ctx.fillText('background bot challenge', 2, 2);

			report.canvas = canvas.toDataURL();

		} catch {}

		// -------------------------------------------------------------------------
		// WebGL fingerprint
		// -------------------------------------------------------------------------

		try {

			const canvas = document.createElement('canvas');

			const gl = canvas.getContext('webgl')
				|| canvas.getContext('experimental-webgl');

			if (gl) {

				const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');

				if (debugInfo) {

					report.webgl = {
						vendor: gl.getParameter(
							debugInfo.UNMASKED_VENDOR_WEBGL
						),
						renderer: gl.getParameter(
							debugInfo.UNMASKED_RENDERER_WEBGL
						),
					};
				}
			}

		} catch {}

		// -------------------------------------------------------------------------
		// Entropy scoring
		// -------------------------------------------------------------------------

		function stddev(values) {

			if (!values.length) {
				return 0;
			}

			const avg =
				values.reduce((a, b) => a + b, 0) /
				values.length;

			const variance =
				values.reduce(
					(a, b) => a + ((b - avg) ** 2),
					0
				) / values.length;

			return Math.sqrt(variance);
		}

		report.entropy = stddev(report.timings);

		// -------------------------------------------------------------------------
		// Wait in background
		// -------------------------------------------------------------------------

		await new Promise(resolve => setTimeout(resolve, 8000));

		// -------------------------------------------------------------------------
		// Simple scoring
		// -------------------------------------------------------------------------

		let score = 0;

		if (report.webdriver) {
			score += 100;
		}

		if (report.mouseMoves === 0) {
			score += 25;
		}

		if (report.entropy < 0.15) {
			score += 25;
		}

		if (report.events < 3) {
			score += 15;
		}

		if (report.plugins === 0) {
			score += 10;
		}

		if (!report.webgl) {
			score += 10;
		}

		report.botScore = score;

		// -------------------------------------------------------------------------
		// Send to backend
		// -------------------------------------------------------------------------

		navigator.sendBeacon(
			'/bot-check',
			JSON.stringify(report)
		);

		localStorage.setItem('bot_challenge_passed', '1');

	})();
}