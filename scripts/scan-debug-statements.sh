#!/usr/bin/env bash

set -euo pipefail

echo "Scanning for debug statements in production code..."

errors=0

while IFS= read -r file; do

  matches=$(grep -nE '\bvar_dump\s*\(' "$file" || true)

  if [ -n "$matches" ]; then
    while IFS= read -r match; do
      echo "::error file=$file::Debug statement found: $match"
      errors=$((errors + 1))
    done <<< "$matches"
  fi

  matches=$(grep -nE '^\s*print_r\s*\(' "$file" || true)

  if [ -n "$matches" ]; then
    while IFS= read -r match; do
      echo "::error file=$file::Debug statement found: $match"
      errors=$((errors + 1))
    done <<< "$matches"
  fi
done < <(find src -name '*.php' -type f ! -path '*/install/*' ! -path '*/tests/*')

if [ $errors -gt 0 ]; then
  echo "::error::$errors debug statement(s) found in production code"
  exit 1
fi

echo "No debug statements found"
