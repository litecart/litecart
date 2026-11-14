/*
	Dependency-free SVG charts

	A small, self-contained chart renderer. No charting library, no jQuery —
	just SVG and the browser's own APIs.

	Usage:
		new Chart('#chart-sales', {
			type: 'bar',
			labels: ['Jan', 'Feb', 'Mar'],
			series: [
				{name: '2025', data: [10, 20, 15]},
				{name: '2026', data: [12, 18, 25]}
			]
		});

	Types:
		'bar'  Grouped bars, optionally stacked
		'line' Smooth lines, optionally with an area fill and points

	Colours are given as CSS custom property names ('--chart-series-1') so
	they follow the theme, including dark mode. Plain colours ('#ff0000')
	are accepted too.
*/

(function() {
	'use strict';

	const SVG_NS = 'http://www.w3.org/2000/svg';

	const DEFAULTS = {
		type: 'bar',
		labels: [],
		series: [],
		height: 0,
		padding: {top: 16, right: 16, bottom: 26, left: 54},
		axisX: {show: true, format: null},
		axisY: {show: true, format: null, ticks: 5},
		legend: false,
		tooltip: true,
		colors: ['--chart-series-1', '--chart-series-2', '--chart-series-3', '--chart-series-4', '--chart-series-5'],
		barGap: 4,
		barRadius: 3,
		lineWidth: 2,
		pointRadius: 3,
		smooth: true,
		area: false,
		dashed: false,
		animate: true
	};

	let uid = 0;

	// Create an SVG element
	function svgNode(tag, attributes, parent) {
		const node = document.createElementNS(SVG_NS, tag);
		for (const name in attributes) node.setAttribute(name, attributes[name]);
		if (parent) parent.appendChild(node);
		return node;
	}

	// Create an HTML element
	function htmlNode(tag, className, parent) {
		const node = document.createElement(tag);
		if (className) node.className = className;
		if (parent) parent.appendChild(node);
		return node;
	}

	// Round a number to a readable step (1, 2, 5 or 10 times a power of ten)
	function niceNumber(value) {
		if (value <= 0) return 1;
		const exponent = Math.floor(Math.log10(value));
		const fraction = value / Math.pow(10, exponent);
		let nice;
		if (fraction < 1.5) nice = 1;
		else if (fraction < 3) nice = 2;
		else if (fraction < 7) nice = 5;
		else nice = 10;
		return nice * Math.pow(10, exponent);
	}

	// Build an axis scale with rounded tick values
	function niceScale(min, max, count) {
		if (min === max) max = min + 1;
		const step = niceNumber((max - min) / Math.max(1, count - 1));
		const from = Math.floor(min / step) * step;
		const to = Math.ceil(max / step) * step;
		const ticks = [];
		for (let value = from; value <= to + step / 2; value += step) ticks.push(value);
		return {min: from, max: to, ticks: ticks};
	}

	// Smooth curve through every point, without overshooting the data
	function smoothPath(points) {
		const count = points.length;
		if (count < 2) return '';
		if (count === 2) return 'M ' + points[0].x + ' ' + points[0].y + ' L ' + points[1].x + ' ' + points[1].y;

		// Secant slopes between the points
		const dx = [];
		const slope = [];
		for (let i = 0; i < count - 1; i++) {
			dx[i] = points[i + 1].x - points[i].x;
			slope[i] = dx[i] ? (points[i + 1].y - points[i].y) / dx[i] : 0;
		}

		// Tangents, flattened where the data turns
		const tangent = new Array(count);
		tangent[0] = slope[0];
		tangent[count - 1] = slope[count - 2];
		for (let i = 1; i < count - 1; i++) {
			tangent[i] = (slope[i - 1] * slope[i] <= 0) ? 0 : (slope[i - 1] + slope[i]) / 2;
		}

		// Limit the tangents to keep the curve monotonic
		for (let i = 0; i < count - 1; i++) {
			if (slope[i] === 0) {
				tangent[i] = 0;
				tangent[i + 1] = 0;
				continue;
			}
			const alpha = tangent[i] / slope[i];
			const beta = tangent[i + 1] / slope[i];
			const norm = alpha * alpha + beta * beta;
			if (norm > 9) {
				const tau = 3 / Math.sqrt(norm);
				tangent[i] = tau * alpha * slope[i];
				tangent[i + 1] = tau * beta * slope[i];
			}
		}

		let path = 'M ' + points[0].x + ' ' + points[0].y;
		for (let i = 0; i < count - 1; i++) {
			const third = dx[i] / 3;
			path += ' C ' + (points[i].x + third) + ' ' + (points[i].y + tangent[i] * third)
				+ ', ' + (points[i + 1].x - third) + ' ' + (points[i + 1].y - tangent[i + 1] * third)
				+ ', ' + points[i + 1].x + ' ' + points[i + 1].y;
		}
		return path;
	}

	class Chart {

		constructor(target, options = {}) {

			this.el = typeof target === 'string' ? document.querySelector(target) : target;
			if (!this.el) throw new Error('Chart: no target element');

			this.options = Object.assign({}, DEFAULTS, options);
			this.options.padding = Object.assign({}, DEFAULTS.padding, options.padding);
			this.options.axisX = Object.assign({}, DEFAULTS.axisX, options.axisX);
			this.options.axisY = Object.assign({}, DEFAULTS.axisY, options.axisY);
			this.series = this.normalizeSeries(this.options.series);

			this.el.classList.add('chart');
			if (this.options.animate) this.el.classList.add('chart-animated');

			this.legendHeight = 0;
			this.render();
			this.observe();
		}

		// Accept plain arrays as well as objects with a name
		normalizeSeries(series) {
			return series.map((item) => {
				if (Array.isArray(item)) return {name: '', data: item};
				return {name: item.name || '', data: item.data || [], color: item.color || null, gradient: item.gradient || null};
			});
		}

		// A colour, either a plain value or a custom property name
		cssColor(color) {
			return color.startsWith('--') ? 'var(' + color + ')' : color;
		}

		// The colour of a series, as a CSS value
		colorValue(index) {
			const color = this.series[index].color || this.options.colors[index % this.options.colors.length];
			return this.cssColor(color);
		}

		// The value of a data point, formatted for display
		formatValue(value) {
			if (this.options.axisY.format) return this.options.axisY.format(value);
			return new Intl.NumberFormat().format(value);
		}

		render() {

			const {padding, labels} = this.options;

			this.el.innerHTML = '';

			// Legend sits above the canvas and takes up its own space
			if (this.options.legend) this.buildLegend();

			this.svg = svgNode('svg', {'class': 'chart-canvas'}, this.el);

			const width = this.el.clientWidth || 300;
			const height = Math.max(60, (this.el.clientHeight || this.options.height || 200) - this.legendHeight);

			this.width = width;
			this.height = height;

			this.svg.setAttribute('width', width);
			this.svg.setAttribute('height', height);
			this.svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);

			this.plot = {
				x: padding.left,
				y: padding.top,
				width: Math.max(10, width - padding.left - padding.right),
				height: Math.max(10, height - padding.top - padding.bottom)
			};

			this.scale = this.buildScale();

			this.defs = svgNode('defs', {}, this.svg);
			this.grid = svgNode('g', {'class': 'chart-grid'}, this.svg);
			this.axis = svgNode('g', {'class': 'chart-axis'}, this.svg);
			this.data = svgNode('g', {'class': 'chart-data'}, this.svg);
			this.overlay = svgNode('g', {'class': 'chart-overlay'}, this.svg);

			this.guide = null;
			this.tooltip = null;

			this.drawGrid();
			this.drawAxes();

			if (this.options.type === 'line') {
				this.drawLines();
			} else {
				this.drawBars();
			}

			if (this.options.tooltip) this.setupTooltip();
		}

		// The value range of the data, rounded to readable tick values
		buildScale() {
			let min = 0;
			let max = 0;
			this.series.forEach((series) => {
				series.data.forEach((value) => {
					const number = Number(value) || 0;
					if (number < min) min = number;
					if (number > max) max = number;
				});
			});
			const scale = niceScale(min, max, this.options.axisY.ticks);
			if (scale.max < max) scale.max = max;
			return scale;
		}

		// Horizontal position of a data point
		x(index) {
			const count = Math.max(1, this.options.labels.length);
			return this.plot.x + (index + 0.5) * (this.plot.width / count);
		}

		// Vertical position of a value
		y(value) {
			const range = this.scale.max - this.scale.min || 1;
			const ratio = (value - this.scale.min) / range;
			return this.plot.y + this.plot.height - ratio * this.plot.height;
		}

		// Show every n:th label when they do not fit
		labelStep() {
			const count = Math.max(1, this.options.labels.length);
			const band = this.plot.width / count;
			return Math.max(1, Math.ceil(38 / band));
		}

		drawGrid() {
			this.scale.ticks.forEach((value) => {
				const y = this.y(value);
				svgNode('line', {
					'class': 'chart-grid-line',
					'shape-rendering': 'crispEdges',
					x1: this.plot.x,
					y1: y,
					x2: this.plot.x + this.plot.width,
					y2: y
				}, this.grid);
			});
		}

		drawAxes() {
			const {axisX, axisY, labels} = this.options;

			if (axisY.show) {
				this.scale.ticks.forEach((value) => {
					const text = svgNode('text', {
						'class': 'chart-axis-label chart-axis-label-y',
						x: this.plot.x - 8,
						y: this.y(value) + 4,
						'text-anchor': 'end'
					}, this.axis);
					text.textContent = this.formatValue(value);
				});
			}

			if (!axisX.show || !labels.length) return;

			const step = this.labelStep();
			labels.forEach((label, index) => {
				if (index % step) return;
				const text = svgNode('text', {
					'class': 'chart-axis-label chart-axis-label-x',
					x: this.x(index),
					y: this.plot.y + this.plot.height + 18,
					'text-anchor': 'middle'
				}, this.axis);
				text.textContent = axisX.format ? axisX.format(label) : label;
			});
		}

		// A vertical gradient fading the colour out towards the bottom
		gradient(index, opacity) {
			const id = 'chart-gradient-' + (++uid);
			const gradient = svgNode('linearGradient', {id: id, x1: 0, y1: 0, x2: 0, y2: 1}, this.defs);
			const custom = this.series[index].gradient;
			const stops = custom || [this.colorValue(index), this.colorValue(index)];
			svgNode('stop', {
				offset: 0,
				style: 'stop-color: ' + this.cssColor(stops[0]) + '; stop-opacity: ' + (custom ? 1 : opacity)
			}, gradient);
			svgNode('stop', {
				offset: 1,
				style: 'stop-color: ' + this.cssColor(stops[1]) + '; stop-opacity: ' + (custom ? 0.55 : opacity * 0.45)
			}, gradient);
			return 'url(#' + id + ')';
		}

		drawBars() {
			const {labels, barGap, barRadius} = this.options;
			const count = labels.length;
			if (!count) return;

			const band = this.plot.width / count;
			const groupWidth = Math.min(band * 0.76, band - 4);
			const barWidth = Math.max(2, (groupWidth - barGap * (this.series.length - 1)) / this.series.length);
			const base = this.y(Math.max(0, this.scale.min));

			this.series.forEach((series, seriesIndex) => {
				const group = svgNode('g', {'class': 'chart-series'}, this.data);
				const fill = this.gradient(seriesIndex, 1);

				series.data.forEach((value, index) => {
					const number = Number(value) || 0;
					const y = this.y(number);
					const bar = svgNode('rect', {
						'class': 'chart-bar',
						x: this.x(index) - groupWidth / 2 + seriesIndex * (barWidth + barGap),
						y: y,
						width: barWidth,
						height: Math.max(1, base - y),
						rx: Math.min(barRadius, barWidth / 2),
						style: 'fill: ' + fill + '; animation-delay: ' + (index * 18) + 'ms'
					}, group);
					bar.dataset.index = index;
					bar.dataset.series = seriesIndex;
				});
			});
		}

		drawLines() {
			const {labels, lineWidth, pointRadius} = this.options;
			const count = labels.length;
			if (!count) return;

			const base = this.y(Math.max(0, this.scale.min));

			this.series.forEach((series, seriesIndex) => {
				const points = series.data.map((value, index) => ({
					x: this.x(index),
					y: this.y(Number(value) || 0)
				}));

				const path = smoothPath(points);

				if (this.options.area) {
					const area = path
						+ ' L ' + points[points.length - 1].x + ' ' + base
						+ ' L ' + points[0].x + ' ' + base + ' Z';
					svgNode('path', {
						'class': 'chart-area',
						d: area,
						style: 'fill: ' + this.gradient(seriesIndex, 0.35)
					}, this.data);
				}

				const attributes = {
					'class': 'chart-line' + (this.options.dashed ? ' chart-line-dashed' : ''),
					d: path,
					style: 'stroke: ' + this.colorValue(seriesIndex) + '; stroke-width: ' + lineWidth
				};
				if (!this.options.dashed) attributes.pathLength = 1;
				svgNode('path', attributes, this.data);

				points.forEach((point, index) => {
					svgNode('circle', {
						'class': 'chart-point',
						cx: point.x,
						cy: point.y,
						r: pointRadius,
						style: 'fill: ' + this.colorValue(seriesIndex)
							+ '; stroke: var(--chart-point-border); animation-delay: ' + (400 + index * 40) + 'ms'
					}, this.data);
				});
			});
		}

		buildLegend() {
			const legend = htmlNode('div', 'chart-legend', this.el);
			this.series.forEach((series, index) => {
				const item = htmlNode('span', 'chart-legend-item', legend);
				const swatch = htmlNode('span', 'chart-legend-swatch', item);
				swatch.style.background = this.colorValue(index);
				const label = htmlNode('span', 'chart-legend-label', item);
				label.textContent = series.name;
			});
			this.legendHeight = legend.offsetHeight;
			return legend;
		}

		setupTooltip() {

			this.tooltip = htmlNode('div', 'chart-tooltip', this.el);

			this.svg.addEventListener('mousemove', (event) => this.showTooltip(event));
			this.svg.addEventListener('mouseleave', () => this.hideTooltip());
		}

		showTooltip(event) {
			const count = this.options.labels.length;
			if (!count) return;

			const box = this.svg.getBoundingClientRect();
			const band = this.plot.width / count;
			let index = Math.floor((event.clientX - box.left - this.plot.x) / band);
			index = Math.max(0, Math.min(count - 1, index));

			// Vertical guide marking the hovered position
			if (!this.guide) this.guide = svgNode('line', {'class': 'chart-guide'}, this.overlay);
			this.guide.setAttribute('x1', this.x(index));
			this.guide.setAttribute('x2', this.x(index));
			this.guide.setAttribute('y1', this.plot.y);
			this.guide.setAttribute('y2', this.plot.y + this.plot.height);
			this.guide.setAttribute('stroke-opacity', 1);

			this.tooltip.innerHTML = '';
			const title = htmlNode('div', 'chart-tooltip-title', this.tooltip);
			title.textContent = this.options.labels[index] || '';

			this.series.forEach((series, seriesIndex) => {
				const row = htmlNode('div', 'chart-tooltip-row', this.tooltip);
				const swatch = htmlNode('span', 'chart-tooltip-swatch', row);
				swatch.style.background = this.colorValue(seriesIndex);
				const label = htmlNode('span', 'chart-tooltip-label', row);
				label.textContent = series.name;
				const value = htmlNode('span', 'chart-tooltip-value', row);
				value.textContent = this.formatValue(Number(series.data[index]) || 0);
			});

			this.tooltip.classList.add('chart-tooltip-visible');

			// Keep the tooltip inside the chart
			const left = this.x(index);
			this.tooltip.classList.toggle('chart-tooltip-left', left + 150 > this.width);
			this.tooltip.style.left = left + 'px';
			this.tooltip.style.top = (this.legendHeight + this.plot.y) + 'px';
		}

		hideTooltip() {
			if (this.tooltip) this.tooltip.classList.remove('chart-tooltip-visible');
			if (this.guide) this.guide.setAttribute('stroke-opacity', 0);
		}

		// Redraw when the container is resized
		observe() {
			if (!window.ResizeObserver) return;
			let frame = 0;
			this.observer = new ResizeObserver(() => {
				if (frame) return;
				frame = requestAnimationFrame(() => {
					frame = 0;
					if (this.el.clientWidth === this.width) return;
					this.render();
				});
			});
			this.observer.observe(this.el);
		}

	}

	window.Chart = Chart;

})();
