#!/usr/bin/env bash

set -euo pipefail

errors=0

# Prefer the locally installed stylelint so that the matching
# stylelint-config-standard-scss / postcss-scss versions from
# package.json are picked up. node_modules absent? — skip
# gracefully so the job stays green; CI just needs `bun install`
# (or `npm install`) before this step to enable the check.
STYLELINT_BIN="./node_modules/.bin/stylelint"

if [ ! -x "$STYLELINT_BIN" ]; then
  echo "::warning::Skipping SCSS syntax check — stylelint not installed. Run 'bun install' (or 'npm install') before this step to enable the check."
  exit 0
fi

if ! "$STYLELINT_BIN" "src/**/*.scss" --custom-syntax postcss-scss --max-warnings=0; then
  echo "::error::SCSS lint/syntax error (stylelint)"
  errors=$((errors + 1))
fi

if [ $errors -gt 0 ]; then
  echo "::error::$errors SCSS file(s) with issues"
  exit 1
fi

echo "All SCSS files passed syntax check"
