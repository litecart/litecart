#!/bin/env bash

# Get the list of JS files staged to be committed
files=$(git diff --cached --name-only --diff-filter=ACM)

# Check if there are any JS files to lint
if [ -z "$files" ]; then
	exit 0
fi

echo ""
echo "-----------------------------------------------"
echo "-- EditorConfig Lint Checker Pre-Commit Hook --"
echo "-----------------------------------------------"
echo ""

# Check dependencies
if ! command -v bun &> /dev/null && ! command -v node &> /dev/null; then
	echo "Bun or Node.js could not be found. Please install one of them."
	exit 1
fi

# Lint JS files
for file in $files
do
	echo -n "- $file"

	# Get the staged content of the file
	# Create a temp file preserving the original file's suffix (extension)
	base=$(basename -- "$file")
	# POSIX-compatible check for extension
	case "$base" in
		*.*)
			suffix=".${base##*.}"
			;;
		*)
			suffix=".tmp"
			;;
	esac
	tmp_file=$(mktemp --suffix="$suffix")
	git cat-file blob ":$file" > "$tmp_file"

	# Check syntax using Node.js or Bun
	if command -v bun &> /dev/null; then
		output=$(bun run editorconfig-checker "$tmp_file" 2>&1)
	else
		output=$(node run editorconfig-checker "$tmp_file" 2>&1)
	fi
	lint_result=$?

	# Remove temporary file
	rm -f "$tmp_file"

	if [ $lint_result -ne 0 ]; then
		echo " [ERROR]"
		echo "  - $file contains whitespace violations!"
		echo "  - $output"
		exit 1
	fi

	echo " [OK]"
done
