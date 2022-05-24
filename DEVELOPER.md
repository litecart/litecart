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


# How To Make a Git Pull Request

If you are new to Git we recommend SourceTree or GitHub Desktop as a great graphical user interface for working with Git.

1. Fork the official LiteCart repository.

2. Use git clone to download the code from your forked repository.

    Example:
    git clone https://github.com/you/litecart.git
    git checkout dev

3. Create a new branch for your new feature or modification.

    Example:
    git checkout -b mynewfeature

4. Commit your changes and push it to your forked repository.

    Example:
    git commit -m "My commit of things"
    git push -u origin mynewfeature

5. Go to your forked repository in Github and click "Pull Requests" and the button "New Pull Request".

6. Set Base to the dev branch of the official repository.
   Set Head to your forked repository and the branch containing your new feature.

7. After creating the pull request. Do not implement new features to your branch, unless you have bugs that needs patching.

8. Once the pull request is accepted you can safely delete your feature branch.


# Install Node.js v15

This project uses Node + Grunt to compile less to css among other things.
To install Grunt do the following:

1. Install Node.js from https://nodejs.org/

2. In your LiteCart project folder (next to package.json) create the file "Run Node.cmd" with the following content:

    @C:\Windows\System32\cmd.exe /k "C:\Program Files\nodejs\nodevars.bat"

3. Execute "Run Node.cmd".

4. To install all necessary node libraries, type the following in the command prompt:

    npm install

Done! Node.js should now have installed all necessary libraries, you can now use any of the following commands:

    npm run grunt         (Launches all grunt tasks)
    npm run less          (Compile and minify .min.css from .less)
    npm run uglify        (Uglify and minify .min.js from .js)
    npm run replace       (Update version number in scripts from package.js)
    npm run phplint       (Check PHP scripts for syntax errors)
    npm run watch         (Watch for changes in .less and .js and update minified versions on the fly)
    npm run hash          (Update checksums.md5 for all tracked files)

    npm update    (Update your node modules to newer versions)
