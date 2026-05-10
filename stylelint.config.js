export default {
	ignoreFiles: ['public_html/**/*.css'],
	extends: ['stylelint-config-standard-scss'],
	overrides: [
		{
			files: ['**/*.scss'],
			customSyntax: 'postcss-scss'
		},
		{
			files: ['**/*.{html}'],
			customSyntax: 'postcss-html'
		}
	],
	rules: {
		'at-rule-no-unknown': [
			true,
			{
				ignoreAtRules: [
					'extends',
					'tailwind',
					'apply',
					'mixin',
					'include',
					'function',
					'use',
					'forward',
					'if',
					'else',
					'for',
					'each',
					'while',
					'return',
					'warn',
					'error'
				]
			}
		],
		'custom-property-pattern': null,
		'property-no-unknown': null,
		'block-no-empty': null,
		'selector-pseudo-class-no-unknown': null,
		// Allow unknown type selectors (SCSS variables used as selectors like $page)
		'selector-type-no-unknown': null,
		'scss/operator-no-unspaced': null,
		'scss/no-global-function-names': null,
		// Allow legacy or non-standard media features and deprecated properties used in frameworks
		'media-feature-name-no-unknown': null,
		'declaration-property-value-keyword-no-deprecated': null,
		'property-no-deprecated': null,
		'declaration-block-single-line-max-declarations': null,
		'declaration-block-no-duplicate-custom-properties': null,
		'font-family-no-missing-generic-family-keyword': null,
		'selector-class-pattern': null
	}
};