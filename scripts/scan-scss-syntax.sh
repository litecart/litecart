#!/usr/bin/env bash

set -euo pipefail

errors=0

# Initialize runner/tool vars to avoid unbound errors under `set -u`
RUNNER_CMD=""
TOOL=""

# Detect available tool: prefer Bun (`bunx`), then dart-sass, then npx
if command -v bunx >/dev/null 2>&1; then
  RUNNER_CMD="bunx"
elif command -v bun >/dev/null 2>&1; then
  RUNNER_CMD="bun x"
elif command -v sass >/dev/null 2>&1; then
  RUNNER_CMD=""
  TOOL="sass"
elif command -v npx >/dev/null 2>&1; then
  RUNNER_CMD="npx"
else
  echo "::warning::Skipping SCSS syntax check — no 'bun', 'bunx', 'sass', or 'npx' available"
  exit 0
fi

while IFS= read -r file; do
  if [ "$TOOL" = "sass" ]; then
    if ! sass --no-source-map "$file" /dev/null 2>/dev/null; then
      echo "::error file=$file::SCSS syntax error (sass)"
      errors=$((errors + 1))
    fi
  else
    # Use RUNNER_CMD (bunx / bun x / npx) to run stylelint if available,
    # otherwise fall back to sass. We first check whether stylelint is
    # invokable via the runner; if so, treat any non-zero exit as a lint
    # error. If stylelint isn't available, use sass to validate syntax.
    IFS=' ' read -r -a RUNNER <<< "${RUNNER_CMD}"

    if [ "${#RUNNER[@]}" -gt 0 ]; then
      if "${RUNNER[@]}" stylelint --version >/dev/null 2>&1; then
        if ! "${RUNNER[@]}" stylelint --custom-syntax postcss-scss "$file" --max-warnings=0; then
          echo "::error file=$file::SCSS lint/syntax error (stylelint)"
          errors=$((errors + 1))
        fi
      else
        # stylelint not available via runner, try sass via runner
        if ! "${RUNNER[@]}" sass --no-source-map "$file" /dev/null 2>/dev/null; then
          echo "::error file=$file::SCSS syntax error (sass via runner)"
          errors=$((errors + 1))
        fi
      fi
    else
      # No runner (we have TOOL=sass)
      if ! sass --no-source-map "$file" /dev/null 2>/dev/null; then
        echo "::error file=$file::SCSS syntax error (sass)"
        errors=$((errors + 1))
      fi
    fi
  fi
done < <(find public_html -name '*.scss' -type f ! -path '*/node_modules/*')

if [ $errors -gt 0 ]; then
  echo "::error::$errors SCSS file(s) with issues"
  exit 1
fi

echo "All SCSS files passed syntax check"
