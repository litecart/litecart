#!/bin/env bash

# Get the list of SCSS files staged to be committed
scss_files=$(git diff --cached --name-only --diff-filter=ACM | grep '\.s?css$')

# Check if there are any SCSS files to lint
if [ -z "$scss_files" ]; then
	exit 0
fi

echo ""
echo "----------------------------------------"
echo "-- (S)CSS Lint Checker Pre-Commit Hook --"
echo "----------------------------------------"
echo ""

# Lint SCSS files from staged content
for file in $scss_files
do
	echo -n "- $file"

	# Build a file:// URL so relative @use/@import paths resolve from the staged file location
	stdin_url="file://$(pwd)/$file"

	# Compile staged content with sass to detect syntax and reference errors
	output=$(git cat-file blob ":$file" | npx sass --stdin --stdin-url="$stdin_url" --no-source-map --no-charset 2>&1 > /dev/null)
	lint_result=$?

	if [ $lint_result -ne 0 ]; then
		echo " [ERROR]"
		echo "  - $file contains (S)CSS errors!"
		echo "  - $output"
		exit 1
	fi

	echo " [OK]"
done
