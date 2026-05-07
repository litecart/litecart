#!/bin/env bash

# Get the list of SQL files staged to be committed
sql_files=$(git diff --cached --name-only --diff-filter=ACM | grep '\.sql$')

# Check if there are any SQL files to lint
if [ -z "$sql_files" ]; then
	exit 0
fi

# Check dependencies - try to run sqlfluff directly
if ! sqlfluff --version &> /dev/null; then
	echo "ERROR: sqlfluff could not be found or executed."
	echo "Please install it with: pip install sqlfluff"
	echo "Or ensure it's in your PATH."
	exit 1
fi

echo ""
echo "---------------------------------------"
echo "-- SQL Lint Checker Pre-Commit Hook --"
echo "---------------------------------------"
echo ""

# Fix SQL files with sqlfluff
for file in $sql_files
do
	echo "Checking $file file for syntax errors..."

	# Get the staged content of the file
	tmp_file=$(mktemp --suffix=.sql)
	git cat-file blob ":$file" > "$tmp_file"

	# Lint the staged content
	output=$(sqlfluff lint --dialect mysql "$tmp_file" 2>&1)
	lint_result=$?

	# Remove temporary file
	rm -f "$tmp_file"

	if [ $lint_result -ne 0 ]; then
		echo "[ERROR]"
		echo "sqlfluff found errors in $file:"
		echo "$output"
		echo ""
		echo "Commit aborted."
		exit 1
	fi

	echo "[OK]"$'\n'
done
