# Social Coding

Github Repository: https://www.github.com/litecart/litecart
Repository URL: https://www.github.com/litecart/litecart.git
Branch Name: `dev`


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

This project is preconfigured with Grunt for Node.js. Grunt can be used for compiling LESS to CSS along with some other useful tools.
To install Grunt do the following:

1. Install Node.js from https://nodejs.org/

2. Open a Terminal window and run these commands:

```bash
# Step into working directory
cd /path/to/project

# Install project dependencies
npm install
```

Done!

Node.js should now have installed all necessary libraries.

You can now execute any of the following commands:

    npm run grunt         (Launches all grunt tasks)
    npm run less          (Compile and minify .min.css from .less)
    npm run uglify        (Uglify and minify .min.js from .js)
    npm run replace       (Update version number in scripts from package.js)
    npm run phplint       (Check PHP scripts for syntax errors)
    npm run watch         (Watch for changes in .less and .js and update minified versions on the fly)
    npm run hash          (Update checksums.md5 for all tracked files)
    npm update            (Update your node modules to newer versions)


# How To Make a Git Pull Request

If you are new to Git, we recommend SourceTree or GitHub Desktop as a great graphical user interface for working with Git.

1. Fork the official LiteCart repository by going to https://github.com/litecart/litecart and clicking **Fork**.

2. Initiate a copy of the code from your forked repository. Use the `dev` branch as source.

```bash
# Clone the repo
git clone dev https://github.com/you/litecart.git
```

2. Add the official repository as second source, and let's call it `upstream`.

```bash
# Add official repo
git remote add upstream https://github.com/litecart/litecart.git

# Fetch remote details
git fetch upstream
```

3. Create a new local branch for your new feature or modification, based on the HEAD of the official branch.

```bash
git checkout -b mynewfeature upstream/dev
```

4. Commit your changes and push the new branch to your forked repository.

```bash
# Cherry pick a commit made in another branch...
git cherry-pick <commit-hash>

# ... or stage new files for commit
git add path/to/file.ext
git add path/to/file2.ext

# Commit your new feature locally
git commit -m "Commit message of new feature, used for changelog"

# Push commit to your Github repository
git push -u origin mynewfeature
```

5. Go to your forked repository in Github and click **Pull Requests** followed by the button **New Pull Request**.

  * Base Repository: `litecart/litecart`, Compare: `dev`

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
