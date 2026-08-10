#!/usr/bin/env bash

set -euo pipefail

errors=0

while IFS= read -r file; do
  if ! php -r "json_decode(file_get_contents('$file')); exit(json_last_error() ? 1 : 0);" 2>/dev/null; then
    echo "::error file=$file::Invalid JSON"
    errors=$((errors + 1))
  fi
done < <(find src -name '*.json' -type f ! -path '*/node_modules/*')

if [ $errors -gt 0 ]; then
  echo "::error::$errors file(s) with JSON errors"
  exit 1
fi

echo "All JSON files passed syntax check"
