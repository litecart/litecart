#!/usr/bin/env bash

set -euo pipefail

echo "Scanning for insecure PRNG usage..."

errors=0

while IFS= read -r file; do
  matches=$(grep -nE '\b(mt_rand|mt_srand|str_shuffle)\s*\(' "$file" || true)
  if [ -n "$matches" ]; then
    while IFS= read -r match; do
      echo "::warning file=$file::Insecure PRNG: $match"
      errors=$((errors + 1))
    done <<< "$matches"
  fi
done < <(find src -name '*.php' -type f ! -path '*/install/*' ! -path '*/tests/*')

if [ $errors -gt 0 ]; then
  echo "::warning::$errors insecure PRNG call(s) found — consider using random_int() / random_bytes()"
fi

echo "PRNG scan complete"
