# Social Coding

  [Github Repository](https://www.github.com/litecart/litecart)
  Branch: dev


# Changelog / Commit Messages

    ! means critical
    + means added
    - means removed
    * means changed

  Examples:

    ! Fix critical issue where drinks was not coming out of the tap
    + Added lettuce to the sallad
    - Removed tomatoes as some guests are allergic
    * Replaced the smaller plate with a larger one

  Issue Tracker Fix Example:

    * Fix #1234 - Car engine doesn't start

  The commit message must always reveal what's inside the commit, no surprises or unreferenced work.

  DO NOT COMMIT test data or debug code. All commits should be ready for production.


# How To Install and Run the Build Tools

This project is preconfigured with Grunt for Node.js. Grunt can be used for compiling LESS to CSS along with some other useful tools.
To install Grunt do the following:

1. Install Node.js from https://nodejs.org/

2. In your LiteCart project folder (next to package.json) create the file "Run Node.cmd" with the following content:

    @C:\Windows\System32\cmd.exe /k "C:\Program Files\nodejs\nodevars.bat"

3. Execute "Run Node.cmd".

4. To install all necessary node libraries, type the following in the command prompt:

    npm install

Done! Node.js should now have installed all necessary libraries. You can now execute any of the following commands:

    npm run grunt         (Launches all grunt tasks)
    npm run less          (Compile and minify .min.css from .less)
    npm run uglify        (Uglify and minify .min.js from .js)
    npm run replace       (Update version number in scripts from package.js)
    npm run phplint       (Check PHP scripts for syntax errors)
    npm run watch         (Watch for changes in .less and .js and update minified versions on the fly)
    npm run hash          (Update checksums.md5 for all tracked files)

    npm update    (Update your node modules to newer versions)


# How To Make a Git Pull Request

If you are new to Git, we recommend SourceTree or GitHub Desktop as a great graphical user interface for working with Git.

1. Fork the official LiteCart repository by going to https://github.com/litecart/litecart and clicking "Fork".

2. Use the "git clone" command to initiate a copy of the code from your forked repository. Use the "dev" branch as source.

    Example:
    git clone https://github.com/you/litecart.git
    git checkout dev

3. Create a new local branch for your new feature or modification.

    Example:
    git checkout -b mynewfeature

4. Commit your changes and push the new branch to your forked repository.

    Example:
    git commit -m "My commit of my new feature"
    git push -u origin mynewfeature

5. Go to your forked repository in Github and click "Pull Requests" followed by the button "New Pull Request".

6. Set Base to the dev branch of the official repository.
   Set Head to your forked repository and the branch containing your new feature.

7. After creating the pull request, do not push any new features to your feature branch, unless you have bugs that needs patching.

8. Once the pull request is accepted you can safely delete your feature branch.


# Enable Git Hook Automations

This project contains some useful git hook automations that can be enabled.
Edit **~/.git/config** and set the following configuration:

    [core]
      hooksPath = .git-hooks/

Alternatively, run the following command in Terminal window:

    git config core.hooksPath .git-hooks/
