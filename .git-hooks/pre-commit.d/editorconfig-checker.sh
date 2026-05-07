#!/bin/env bash

set -euo pipefail

bun run editorconfig-checker --exclude .git --exclude node_modules --exclude public_html/vendor
