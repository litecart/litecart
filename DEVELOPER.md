# Install Node.js + Grunt For Windows

1. Install Node.js from https://nodejs.org/

2. In your LiteCart project folder (next to package.json) create the file "Run Node.cmd" with the following content:

    @C:\Windows\System32\cmd.exe /k "C:\Program Files\nodejs\nodevars.bat"

3. Execute "Run Node.cmd" and in the command prompt type:

    npm install

Done! Node.js should now have installed all necessary libraries, you can now use any of the following commands:

    grunt         (Launches all grunt tasks)
    grunt less    (Compile and minify .min.css from .less)
    grunt uglify  (Uglify and minify .min.js from .js)
    grunt replace (Update version number in scripts from package.js)
    grunt phplint (Check scripts for syntax errors)
    grunt watch   (Watch for changes in .less and .js and update minified versions on the fly)

# Social Coding

  [Github Repository](https://www.github.com/litecart/litecart)
  Branch: dev

## Changelog / Commit Messages

    ! means critical
    + means added
    - means removed
    * means changed

  Examples:

    ! Fix critical issue where beer was not coming out of the tap
    + Added lettuce to the sallad
    - Removed tomatoes as some guests are allergic
    * Replaced the smaller plate with a larger one

  Issue Tracker Fix Example:

    * Fix #1234 - Car engine doesn't start

  The commit message must always reveal what's inside the commit, no surprises or unreferenced work.

  DO NOT COMMIT test data or debug code. All commits should be ready for production.
