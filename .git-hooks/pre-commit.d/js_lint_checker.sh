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
	base=$(basename -- "$file")
	if [[ "$base" == *.* ]]; then
		suffix=".${base##*.}"
	else
		suffix=".tmp"
	fi
	tmp_file=$(mktemp --suffix="$suffix")
	git cat-file blob ":$file" > "$tmp_file"

	# Create a processed copy that mocks top-level waitFor(...) calls to a safe noop
	proc_file=$(mktemp --suffix="$suffix")

	# Prepend a safe lint prelude to avoid modifying source comments/strings.
	# This defines harmless `window`, `globalThis`, and `waitFor` for the syntax checker.
	cat > "$proc_file" <<'LINT_PRELUDE'
// LINT PRELUDE - safe globals for syntax checking
var globalThis = (typeof globalThis !== 'undefined') ? globalThis : (typeof window !== 'undefined' ? window : {});
if (typeof window === 'undefined') { var window = globalThis; }
if (typeof waitFor === 'undefined') { var waitFor = function(){}; }
LINT_PRELUDE

	# Append original file contents unchanged
	cat "$tmp_file" >> "$proc_file"

	# Prefer Bun's syntax check when available; fall back to Node
	if command -v bun &> /dev/null; then
		output=$(bun --check "$proc_file" 2>&1)
		lint_result=$?
	else
		output=$(node --check "$proc_file" 2>&1)
		lint_result=$?
	fi

	# Remove temporary files
	rm -f "$tmp_file" "$proc_file"

	if [ $lint_result -ne 0 ]; then
		echo " [ERROR]"
		echo "  - $file contains syntax errors!"
		echo "  - $output"
		exit 1
	fi

	echo " [OK]"
done
