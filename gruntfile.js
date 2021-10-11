module.exports = function(grunt) {

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    replace: {
      app_header: {
        src: ['upload/includes/app_header.inc.php'],
        overwrite: true,
        replacements: [
          {
            from: /define\('PLATFORM_VERSION', '([0-9\.]+)'\);/,
            to: 'define(\'PLATFORM_VERSION\', \'<%= pkg.version %>\');'
          }
        ]
      },

      app: {
        src: [
          'upload/index.php',
          'upload/install/install.php',
          'upload/install/upgrade.php'
        ],
        overwrite: true,
        replacements: [
          {
            from: /LiteCart® ([0-9\.]+)/,
            to: 'LiteCart® <%= pkg.version %>'
          }
        ]
      },
    },

    less: {
      backend_variables: {
        options: {
          compress: false,
          sourceMap: false,
          relativeUrls: true
        },
        files: {
          'upload/backend/template/css/variables.css' : 'upload/backend/template/less/variables.less',
        }
      },

      backend_minified: {
        options: {
          compress: true,
          sourceMap: true,
          sourceMapBasepath: 'upload/backend/template/less/',
          sourceMapRootpath: '../less/',
          sourceMapURL: function(path) { return path.replace(/.*\//, '') + '.map'; },
          relativeUrls: true
        },
        files: {
          'upload/backend/template/css/app.min.css'       : 'upload/backend/template/less/app.less',
          'upload/backend/template/css/framework.min.css' : 'upload/backend/template/less/framework.less',
          'upload/backend/template/css/printable.min.css' : 'upload/backend/template/less/printable.less',
        }
      },

      frontend: {
        options: {
          compress: false,
          sourceMap: false,
          relativeUrls: true
        },
        files: {
          'upload/frontend/templates/classic/css/app.css'       : 'upload/frontend/templates/classic/less/app.less',
          'upload/frontend/templates/classic/css/checkout.css'  : 'upload/frontend/templates/classic/less/checkout.less',
          'upload/frontend/templates/classic/css/framework.css' : 'upload/frontend/templates/classic/less/framework.less',
          'upload/frontend/templates/classic/css/printable.css' : 'upload/frontend/templates/classic/less/printable.less',
          'upload/frontend/templates/classic/css/variables.css' : 'upload/frontend/templates/classic/less/variables.less',

          'upload/frontend/templates/default/css/app.css'       : 'upload/frontend/templates/default/less/app.less',
          'upload/frontend/templates/default/css/checkout.css'  : 'upload/frontend/templates/default/less/checkout.less',
          'upload/frontend/templates/default/css/framework.css' : 'upload/frontend/templates/default/less/framework.less',
          'upload/frontend/templates/default/css/printable.css' : 'upload/frontend/templates/default/less/printable.less',
          'upload/frontend/templates/default/css/variables.css' : 'upload/frontend/templates/default/less/variables.less',
        }
      },

      frontend_classic_minified: {
        options: {
          compress: true,
          sourceMap: true,
          sourceMapBasepath: 'upload/frontend/templates/classic/less/',
          sourceMapRootpath: '../less/',
          sourceMapURL: function(path) { return path.replace(/.*\//, '') + '.map'; },
          relativeUrls: true
        },
        files: {
          'upload/frontend/templates/classic/css/app.min.css'       : 'upload/frontend/templates/classic/less/app.less',
          'upload/frontend/templates/classic/css/checkout.min.css'  : 'upload/frontend/templates/classic/less/checkout.less',
          'upload/frontend/templates/classic/css/framework.min.css' : 'upload/frontend/templates/classic/less/framework.less',
          'upload/frontend/templates/classic/css/printable.min.css' : 'upload/frontend/templates/classic/less/printable.less',
        }
      },

      frontend_default_minified: {
        options: {
          compress: true,
          sourceMap: true,
          sourceMapBasepath: 'upload/frontend/templates/default/less/',
          sourceMapRootpath: '../less/',
          sourceMapURL: function(path) { return path.replace(/.*\//, '') + '.map'; },
          relativeUrls: true
        },
        files: {
          'upload/frontend/templates/default/css/app.min.css'       : 'upload/frontend/templates/default/less/app.less',
          'upload/frontend/templates/default/css/checkout.min.css'  : 'upload/frontend/templates/default/less/checkout.less',
          'upload/frontend/templates/default/css/framework.min.css' : 'upload/frontend/templates/default/less/framework.less',
          'upload/frontend/templates/default/css/printable.min.css' : 'upload/frontend/templates/default/less/printable.less',
        }
      },

      featherlight_minified: {
        options: {
          compress: true,
          sourceMap: false,
          sourceMapBasepath: 'upload/assets/featherlight/',
          sourceMapRootpath: './',
          sourceMapURL: function(path) { return path.replace(/.*\//, '') + '.map'; },
          relativeUrls: true
        },
        files: {
          'upload/assets/featherlight/featherlight.min.css'       : 'upload/assets/featherlight/featherlight.less',
        }
      },
    },

    sass: {
      trumbowyg_minified: {
        options: {
          implementation: require('node-sass'),
          sourceMap: true,
          outputStyle: 'compressed',
          compass: false
        },
        files: {
          'upload/assets/trumbowyg/ui/trumbowyg.min.css': 'upload/assets/trumbowyg/ui/trumbowyg.scss'
        }
      }
    },

    uglify: {
      featherlight: {
        options: {
          sourceMap: false,
        },
        files: {
          'upload/assets/featherlight/featherlight.min.js'   : ['upload/assets/featherlight/featherlight.js'],
        }
      },
      litecart: {
        options: {
          sourceMap: true,
        },
        files: {
          'upload/backend/template/js/app.min.js'   : ['upload/backend/template/js/app.js'],
          'upload/frontend/templates/classic/js/app.min.js' : ['upload/frontend/templates/classic/js/app.js'],
          'upload/frontend/templates/default/js/app.min.js' : ['upload/frontend/templates/default/js/app.js'],
        }
      },
    },

    phplint: {
      options: {
        //phpCmd: 'C:/xampp/php/php.exe', // Defaults to php
        limit: 10,
        stdout: false
      },
      files: 'upload/**/*.php'
    },

    watch: {
      replace: {
        files: [
          'package.json',
        ],
        tasks: ['replace']
      },

      less: {
        files: [
          'upload/assets/featherlight/featherlight.less',
          'upload/backend/template/**/*.less',
          'upload/frontend/templates/**/*.less',
        ],
        tasks: ['less']
      },

      javascripts: {
        files: [
          'upload/assets/featherlight/featherlight.js',
          'upload/backend/template/**/js/*.js',
          'upload/frontend/templates/**/js/*.js',
        ],
        tasks: ['uglify']
      },

      sass: {
        files: [
          'upload/assets/trumbowyg/ui/trumbowyg.scss',
        ],
        tasks: ['sass']
      },
    }
  });

  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-text-replace');

  grunt.registerTask('default', ['replace', 'less', 'sass', 'uglify']);
  grunt.registerTask('compile', ['less', 'sass', 'uglify']);

  require('phplint').gruntPlugin(grunt);
  grunt.registerTask('test', ['phplint']);
};