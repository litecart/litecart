#!/bin/bash

# Get the list of JS files staged to be committed
js_files=$(git diff --cached --name-only --diff-filter=ACM | grep '\.js$')

# Check if there are any JS files to lint
if [ -z "$js_files" ]; then
	exit 0
fi

echo ""
echo "--------------------------------------"
echo "-- JS Lint Checker Pre-Commit Hook --"
echo "--------------------------------------"
echo ""

# Check dependencies
if ! command -v bun &> /dev/null && ! command -v node &> /dev/null; then
	echo "Bun or Node.js could not be found. Please install one of them."
	exit 1
fi

# Lint JS files
for file in $js_files
do
	echo -n "- $file"

	# Get the staged content of the file
	tmp_file=$(mktemp --suffix=.js)
	git cat-file blob ":$file" > "$tmp_file"

	# Check syntax using Node.js or Bun
	if command -v bun &> /dev/null; then
		output=$(bun --check "$tmp_file" 2>&1)
	else
		output=$(node --check "$tmp_file" 2>&1)
	fi
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
