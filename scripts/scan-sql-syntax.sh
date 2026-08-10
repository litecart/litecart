#!/usr/bin/env bash

set -euo pipefail

errors=0

while IFS= read -r file; do
  if ! php -r "
    \$sql = file_get_contents('$file');
    if (preg_match('/[^\x09\x0A\x0D\x20-\x7E\x80-\xFF]/', \$sql)) {
      echo 'Contains invalid characters';
      exit(1);
    }
  " 2>/dev/null; then
    echo "::error file=$file::SQL file contains invalid characters"
    errors=$((errors + 1))
  fi
done < <(find src -name '*.sql' -type f)

if [ $errors -gt 0 ]; then
  echo "::error::$errors SQL file(s) with issues"
  exit 1
fi

echo "All SQL files passed check"
