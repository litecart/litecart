#!/usr/bin/env bash

set -euo pipefail

errors=0

# Initialize runner/tool vars to avoid unbound errors under `set -u`
PKG_EXEC=""

# Detect available package executor: prefer bunx, then npx
if command -v bunx >/dev/null 2>&1; then
  PKG_EXEC="bunx"
elif command -v npx >/dev/null 2>&1; then
  PKG_EXEC="npx"
else
  echo "::warning::Skipping SCSS syntax check — no 'bunx' or 'npx' available"
  exit 0
fi

if ! "$PKG_EXEC" stylelint "public_html/**/*.scss" --custom-syntax postcss-scss --max-warnings=0; then
  echo "::error::SCSS lint/syntax error (stylelint)"
  errors=$((errors + 1))
fi

if [ $errors -gt 0 ]; then
  echo "::error::$errors SCSS file(s) with issues"
  exit 1
fi

echo "All SCSS files passed syntax check"
