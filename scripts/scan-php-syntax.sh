#!/usr/bin/env bash

set -euo pipefail

errors=0

while IFS= read -r file; do
  if ! php -l "$file" > /dev/null 2>&1; then
    php -l "$file"
    errors=$((errors + 1))
  fi
done < <(find public_html -name '*.php' -type f)

if [ $errors -gt 0 ]; then
  echo "::error::$errors file(s) with PHP syntax errors"
  exit 1
fi

echo "All PHP files passed syntax check"
