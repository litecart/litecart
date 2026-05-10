#!/usr/bin/env bash

set -euo pipefail

errors=0

# Initialize runner/tool vars to avoid unbound errors under `set -u`
RUNNER_CMD=""

# Detect available tool: prefer Bun, then node
if command -v bun >/dev/null 2>&1; then
  RUNNER_CMD="bun"
elif command -v node >/dev/null 2>&1; then
  RUNNER_CMD="node"
else
  echo "::warning::Skipping SCSS syntax check — no 'bun' or 'node' available"
  exit 0
fi

if ! "${RUNNER[@]} run" stylelint "public_html/**/*.scss" --custom-syntax postcss-scss --max-warnings=0; then
  echo "::error file=$file::SCSS lint/syntax error (stylelint)"
  errors=$((errors + 1))
fi

if [ $errors -gt 0 ]; then
  echo "::error::$errors SCSS file(s) with issues"
  exit 1
fi

echo "All SCSS files passed syntax check"
