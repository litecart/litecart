#!/bin/bash

# Get the list of SQL files staged to be committed
sql_files=$(git diff --cached --name-only --diff-filter=ACM | grep '\.sql$')

# Check dpendencies
if [ -n "$sql_files" ]; then
	if ! command -v sqlfluff &> /dev/null; then
		echo "sqlfluff could not be found. Please install it with 'pip install sqlfluff'."
		exit 1
	fi
fi

# Fix SQL files with sqlfluff
for file in $sql_files
do
	echo "Checking $file file for syntax errors..."

	# Get the staged content of the file
	tmp_file=$(mktemp --suffix=.sql)
	git cat-file blob ":$file" > "$tmp_file"

	# Lint the staged content
	sqlfluff lint --dialect mysql "$tmp_file"

	lint_result=$?

	# Remove temporary file
	rm -f "$tmp_file"

	if [ $lint_result -ne 0 ]; then
		echo "[Error]"
		echo "sqlfluff found errors in $file. Commit aborted."
		exit 1
	fi

	echo "[OK]"$'\n'
done
