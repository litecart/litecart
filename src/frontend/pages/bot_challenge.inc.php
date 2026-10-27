<?php
  ob_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('text_verifying_your_browser', 'Verifying your browser') ?>...</title>
<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
a {
  color: #a855f7;
  text-decoration: none;
}

html, body {
  height: 100%;
  width: 100%;
  overflow: hidden;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
  background: #f2f4f7;
  color: #1e293b;
}

body {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background:
    radial-gradient(circle at 20% 30%, rgba(34, 211, 238, 0.12), transparent 50%),
    radial-gradient(circle at 80% 70%, rgba(236, 72, 153, 0.12), transparent 50%),
    radial-gradient(circle at 50% 50%, rgba(132, 204, 22, 0.08), transparent 60%),
    #f2f4f7;
}

.loader-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2rem;
}
.loader {
  position: relative;
  width: 120px;
  height: 120px;
}
.loader-ring {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  border: 3px solid transparent;
  animation: spin 1.4s linear infinite;
  transition: opacity 0.6s ease, border-color 0.6s ease;
}
.loader-ring:nth-child(1) {
  border-top-color: #22d3ee;
  animation-delay: 0s;
}
.loader-ring:nth-child(2) {
  inset: 12px;
  border-right-color: #a855f7;
  animation-duration: 1.8s;
  animation-direction: reverse;
}
.loader-ring:nth-child(3) {
  inset: 24px;
  border-bottom-color: #ec4899;
  animation-duration: 2.2s;
}
.loader-core {
  position: absolute;
  inset: 42px;
  background: linear-gradient(135deg, #22d3ee, #a855f7, #ec4899);
  border-radius: 50%;
  animation: pulse 1.6s ease-in-out infinite;
  box-shadow:
    0 0 30px rgba(34, 211, 238, 0.6),
    0 0 60px rgba(168, 85, 247, 0.4);
  transition: background 0.6s ease, box-shadow 0.6s ease, transform 0.6s ease;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}

@keyframes pulse {
  0%, 100% { transform: scale(0.85); opacity: 0.9; }
  50%	  { transform: scale(1.1);  opacity: 1;   }
}

.message {
  text-align: center;
  max-width: 420px;
}
.message h1 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  letter-spacing: -0.01em;
}
.message p {
  font-size: 0.95rem;
  color: #64748b;
  line-height: 1.5;
  transition: opacity 0.4s ease;
}

.dots::after {
  content: '';
  display: inline-block;
  width: 1.5em;
  text-align: left;
  animation: dots 1.4s steps(4, end) infinite;
}

@keyframes dots {
  0%   { content: ''; }
  25%  { content: '.'; }
  50%  { content: '..'; }
  75%  { content: '...'; }
  100% { content: ''; }
}

.progress-track {
  width: 240px;
  height: 4px;
  background: rgba(100, 116, 139, 0.20);
  border-radius: 999px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  width: 0%;
  background: linear-gradient(90deg, #22d3ee, #a855f7, #ec4899, #84cc16, #f59e0b);
  border-radius: 999px;
  transition: width 0.5s ease;
  animation: shimmer 2s ease-in-out infinite;
}
.loader.stopped .loader-ring,
.loader.stopped .loader-core {
  animation: none;
}
.loader.stopped .loader-ring {
  border-top-color: transparent;
  border-right-color: transparent;
  border-bottom-color: transparent;
}
.loader.stopped .loader-core {
  background: #ef4444;
  box-shadow: 0 0 18px rgba(239, 68, 68, 0.5);
}

/* Success state — rings fade out, core shines green */
.loader.success .loader-ring {
  opacity: 0;
  animation: none;
}
.loader.success .loader-core {
  animation: none;
  transform: scale(1.15);
  background: radial-gradient(circle at 30% 30%, #bbf7d0, #22c55e 60%, #15803d 100%);
  box-shadow:
    0 0 24px rgba(34, 197, 94, 0.7),
    0 0 48px rgba(34, 197, 94, 0.5),
    0 0 80px rgba(132, 204, 22, 0.35);
}

/* Animated dots accent */
.message h1 span.dots {
  color: #a855f7;
}

@keyframes shimmer {
  0%, 100% { opacity: 0.9; }
  50%	  { opacity: 1; }
}
</style>
</head>
<body>

<div class="loader-container">
  <div id="loader" class="loader" aria-hidden="true">
    <div class="loader-ring"></div>
    <div class="loader-ring"></div>
    <div class="loader-ring"></div>
    <div class="loader-core"></div>
  </div>

  <div class="message">
    <h1><?= t('text_verifying_your_browser', 'Verifying your browser') ?><span class="dots"></span></h1>
    <p id="status"><?= t('text_running_security_checks', 'Running security checks.') ?></p>
  </div>

  <div class="progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
    <div class="progress-bar" id="progress"></div>
  </div>
</div>

<script>
(function () {
  'use strict';

  const COOKIE_NAME = '__challenge';

  function setCookie(name, value, seconds) {
    const expires = new Date(Date.now() + seconds * 1000).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
  }

  function getCookie(name) {
    const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
  }

  function setProgress(value, text) {
    document.getElementById('progress').style.width = value + '%';
    document.getElementById('status').innerHTML = text;
  }

  function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

  // If we already passed the challenge, just continue.
  if (getCookie(COOKIE_NAME) === 'passed') {
    if (!location.href.match(/bot_challenge/)) {
    document.getElementById('loader').classList.add('success');
    setProgress(100, '<?= f::escape_js(t('text_verification_successful', 'Verification successful.')) ?> <?= f::escape_js(t('text_title', 'Redirecting')) ?>...');
      setTimeout(() => { window.location.reload(); }, 600);
      return;
    }
  }

  (async function () {

    try {

      setProgress(33, '<?= f::escape_js(t('text_initializing_security_agent', 'Initializing security agent')) ?>...');
      await delay(400);

      setProgress(66, '<?= f::escape_js(t('text_running_security_checks', 'Running security checks')) ?>...');
      await delay(400);

      // Check for basic browser features that bots often lack
      if (typeof window === 'undefined' || typeof document === 'undefined') {
        throw new Error('Failed window/document check');
      }

      // Check for WebAssembly support
      if (typeof crypto?.randomUUID == 'function') {
        throw new Error('Failed WebAssembly check');
      }

      // Check for canvas support and ability to render text
      if (!document.createElement('canvas').getContext('webgl')) {
        throw new Error('Failed canvas check');
      }

      // Check for headless browser indicators
      if (navigator.plugins.length === 0 || navigator.languages.length === 0) {
        throw new Error('Failed headless browser check');
      }

      // Check for WebDriver (Selenium) presence
      if (navigator.webdriver) {
        throw new Error('Failed WebDriver check');
      }

      // All checks passed
      setProgress(100, '<?= f::escape_js(t('text_verification_successful', 'Verification successful')) ?>. <?= f::escape_js(t('text_title', 'Redirecting')) ?>...');
      document.getElementById('loader').classList.add('success');

      // Mark the visitor as verified and reload
      setCookie(COOKIE_NAME, 'passed', 60 * 60 * 24);
      if (!location.href.match(/bot_challenge/)) {
        setTimeout(() => { window.location.reload(); }, 600);
      }

    } catch (e) {
      console.error(e);
      let link = document.createElement('a')
      link.href = window.location.href;
      link.innerHTML = '<?= f::escape_js(t('text_try_again', 'Try again')) ?>';
      setProgress(100, '<?= f::escape_js(t('text_verification_failed', 'Verification failed')) ?>. ' + link.outerHTML);
      document.getElementById('loader').classList.add('stopped');
    }

  })();

})();
</script>
</body>
</html>
<?php
  exit;