#!/bin/bash

# Get the list of LESS files staged to be committed
less_files=$(git diff --cached --name-only --diff-filter=ACM | grep '\.less$')

# Check if there are any LESS files to lint
if [ -z "$less_files" ]; then
	exit 0
fi

echo ""
echo "----------------------------------------"
echo "-- LESS Lint Checker Pre-Commit Hook --"
echo "----------------------------------------"
echo ""

# Lint LESS files
for file in $less_files
do
	echo -n "- $file"

	# Get the staged content of the file
	tmp_file=$(mktemp --suffix=.less)
	git cat-file blob ":$file" > "$tmp_file"

	# Check syntax using lessc --lint (via npx to use local node_modules)
	output=$(npx lessc --lint "$tmp_file" 2>&1)
	lint_result=$?

	# Remove temporary file
	rm -f "$tmp_file"

	if [ $lint_result -ne 0 ]; then
		echo " [ERROR]"
		echo "  - $file contains syntax errors!"
		echo "  - $output"
		exit 1
	fi

	echo " [OK]"
done
