# Social Coding

Github Repository: https://www.github.com/litecart/litecart
Repository URL: https://www.github.com/litecart/litecart.git
Branch Name: `dev-major`


# Changelog / Commit Messages

		! means critical
		+ means added
		- means removed
		* means changed

	Examples:

		! Fix critical issue where beer was not coming out of the tap
		* Replaced the smaller plate with a larger one
		+ Added lettuce to the sallad
		- Removed rotten tomatoes

	Issue Tracker Fix Example:

		* Fix #1234 - Car engine doesn't start

	The commit message must always reveal what's inside the commit, no surprises or unreferenced work.

	DO NOT COMMIT test data or debug code. All commits should be ready for production.


# How To Install and Run the Build Tools

This project uses Gulp 5 (ESM) for building assets. Requires Node.js 24+ or Bun.

1. Install [Node.js](https://nodejs.org/) (or [Bun](https://bun.sh/)).

2. Open a Terminal window and run these commands:

```bash
# Step into working directory
cd /path/to/project

# Install project dependencies
npm install   # or bun install
```

Done!

You can now execute any of the following commands:

		npm run build         (Compile LESS/SCSS to CSS, minify JS — includes watch)
		npm run watch         (Watch for changes in LESS/JS/SCSS and recompile)
		npm run phplint       (Check PHP scripts for syntax errors via Gulp)
		npm test              (Run platform tests — requires a running database)
		npm run hash          (Update checksums.md5 for all tracked files)
		npm run uglify        (Minify JavaScript and update version numbers)


# Database Schema

The database schema is defined in `install/structure.json`. This file is the single source of truth — the upgrade script automatically patches the database with any missing tables, columns, and indexes from this file.

**Migrations** (`install/migrations/*.inc.php`) are only needed for operations the auto-patcher cannot handle:
- Renaming columns or tables
- Moving data between columns
- Data transformations

When adding new columns or tables, only update `structure.json`. No migration needed.

**Important:** Keep the existing formatting in `structure.json` — use spaces after `:` and `,` in JSON column definitions (e.g. `{ "type": "INT", "length": 10 }`). Do not run JSON formatters or prettifiers on the file.


# JSON File Formatting

Do not use automated JSON formatters (Prettier, VS Code format-on-save, etc.) on JSON files in this project. The existing formatting is intentional for human readability.

For `structure.json`, column definitions use compact single-line objects with spaces:

		"column_name": { "type": "VARCHAR", "length": 128, "default": "''" },

Do NOT expand these into multi-line format:

		"column_name": {
				"type": "VARCHAR",
				"length": 128,
				"default": "''"
		},

When editing JSON files, make targeted changes and preserve the surrounding format.


# CSS and Frontend Assets

Storefront styles are compiled from LESS source files:

		frontend/templates/default/less/variables.less   (CSS custom properties — theme tokens)
		frontend/templates/default/less/app.less          (main storefront styles)
		frontend/templates/default/less/checkout.less     (checkout-specific styles)

All color values must use CSS custom properties defined in `variables.less`. Do not hardcode hex/rgb values in component LESS files.

**Important:** Compiled CSS files must be committed alongside LESS source changes. Run `npm run build` before committing.


# How To Make a Git Pull Request

If you are new to Git, we recommend SourceTree or GitHub Desktop as a great graphical user interface for working with Git.

1. Fork the official LiteCart repository by going to https://github.com/litecart/litecart and clicking **Fork**.

2. Initiate a copy of the code from your forked repository. Use the `dev-major` branch as source.

```bash
# Clone the repo
git clone https://github.com/you/litecart.git
cd litecart
git checkout dev-major
```

3. Add the official repository as second source, and let's call it `upstream`.

```bash
# Add official repo
git remote add upstream https://github.com/litecart/litecart.git

# Fetch remote details
git fetch upstream
```

4. Create a new local branch for your new feature or modification, based on the state of the `dev-major` branch of the official repository (upstream).

```bash
git checkout -b mynewfeature upstream/dev-major
```

5. Commit your changes and push the new branch to your forked repository.

```bash
# Stage files for commit
git add path/to/file.ext

# Commit your new feature locally
git commit -m "+ Commit message of new feature, used for changelog"

# Push commit to your Github repository
git push -u origin mynewfeature
```

6. Go to your forked repository in Github and click **Pull Requests** followed by the button **New Pull Request**.

	* Base Repository: `litecart/litecart`, Compare: `dev-major`

	* Head Repository: `you/litecart`, Compare: `mynewfeature`

Caution! After creating the pull request, do not push any more commits to your feature branch, unless you have bugs that needs patching.

Once the pull request is accepted you can safely delete your feature branch.


# Enable Git Hook Automations

This project contains some useful Git-hook automations that can be fired upon specific git events. I.e. checking your code for syntax errors before storing your commit, or updating file checksums after storing the commit.

Execute this command to enable git-hooks:

```bash
# Configure Git to execute git-hook automations upon specific events
git config core.hooksPath .git-hooks/
```

Alternatively, edit **~/.git/config** and set the following configuration:

```ini
[core]
hooksPath = .git-hooks/
```
