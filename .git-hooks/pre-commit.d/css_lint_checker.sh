#!/bin/bash

# Get the list of CSS files staged to be committed
css_files=$(git diff --cached --name-only --diff-filter=ACM | grep '\.css$')

# Check if there are any CSS files to lint
if [ -z "$css_files" ]; then
	exit 0
fi

echo ""
echo "---------------------------------------"
echo "-- CSS Lint Checker Pre-Commit Hook --"
echo "---------------------------------------"
echo ""

# Lint CSS files
for file in $css_files
do
	echo -n "- $file"

	# Get the staged content of the file
	tmp_file=$(mktemp --suffix=.css)
	git cat-file blob ":$file" > "$tmp_file"

	# Check syntax using lessc --lint (CSS is valid LESS)
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
