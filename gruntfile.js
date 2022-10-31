module.exports = function(grunt) {

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    replace: {
      app_header: {
        src: ['public_html/includes/app_header.inc.php'],
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
          'public_html/index.php',
          'public_html/install/install.php',
          'public_html/install/upgrade.php'
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
      backend: {
        options: {
          compress: false,
          sourceMap: false,
          relativeUrls: true
        },
        files: {
          'public_html/includes/templates/default.admin/css/variables.css'       : 'public_html/includes/templates/default.admin/less/variables.less',
        }
      },

      backend_minified: {
        options: {
          compress: true,
          sourceMap: true,
          sourceMapBasepath: 'public_html/includes/templates/default.admin/less/',
          sourceMapRootpath: '../less/',
          sourceMapURL: function(path) { return path.replace(/.*\//, '') + '.map'; },
          relativeUrls: true
        },
        files: {
          'public_html/includes/templates/default.admin/css/app.min.css'       : 'public_html/includes/templates/default.admin/less/app.less',
          'public_html/includes/templates/default.admin/css/framework.min.css' : 'public_html/includes/templates/default.admin/less/framework.less',
          'public_html/includes/templates/default.admin/css/printable.min.css' : 'public_html/includes/templates/default.admin/less/printable.less',
        }
      },

      frontend: {
        options: {
          compress: false,
          sourceMap: false,
          relativeUrls: true
        },
        files: {
          'public_html/includes/templates/default.catalog/css/app.css'       : 'public_html/includes/templates/default.catalog/less/app.less',
          'public_html/includes/templates/default.catalog/css/checkout.css'  : 'public_html/includes/templates/default.catalog/less/checkout.less',
          'public_html/includes/templates/default.catalog/css/framework.css' : 'public_html/includes/templates/default.catalog/less/framework.less',
          'public_html/includes/templates/default.catalog/css/printable.css' : 'public_html/includes/templates/default.catalog/less/printable.less',
          'public_html/includes/templates/default.catalog/css/variables.css' : 'public_html/includes/templates/default.catalog/less/variables.less',
        }
      },

      frontend_minified: {
        options: {
          compress: true,
          sourceMap: true,
          sourceMapBasepath: 'public_html/includes/templates/default.catalog/less/',
          sourceMapRootpath: '../less/',
          sourceMapURL: function(path) { return path.replace(/.*\//, '') + '.map'; },
          relativeUrls: true
        },
        files: {
          'public_html/includes/templates/default.catalog/css/app.min.css'       : 'public_html/includes/templates/default.catalog/less/app.less',
          'public_html/includes/templates/default.catalog/css/checkout.min.css'  : 'public_html/includes/templates/default.catalog/less/checkout.less',
          'public_html/includes/templates/default.catalog/css/framework.min.css' : 'public_html/includes/templates/default.catalog/less/framework.less',
          'public_html/includes/templates/default.catalog/css/printable.min.css' : 'public_html/includes/templates/default.catalog/less/printable.less',
        }
      },

      featherlight_minified: {
        options: {
          compress: true,
          //sourceMap: false,
          //sourceMapBasepath: 'public_html/ext/featherlight/',
          //sourceMapRootpath: './',
          //sourceMapURL: function(path) { return path.replace(/.*\//, '') + '.map'; },
          relativeUrls: true
        },
        files: {
          'public_html/ext/featherlight/featherlight.min.css'       : 'public_html/ext/featherlight/featherlight.less',
        }
      },
    },

    'dart-sass': {
      trumbowyg_minified: {
        options: {
          sourceMap: false,
          outputStyle: 'compressed',
          compass: false
        },
        files: {
          'public_html/ext/trumbowyg/ui/trumbowyg.min.css': 'public_html/ext/trumbowyg/ui/trumbowyg.scss'
        }
      }
    },

    uglify: {
      featherlight: {
        options: {
          sourceMap: false,
        },
        files: {
          'public_html/ext/featherlight/featherlight.min.js'   : ['public_html/ext/featherlight/featherlight.js'],
        }
      },
      litecart: {
        options: {
          sourceMap: true,
        },
        files: {
          'public_html/includes/templates/default.admin/js/app.min.js'   : ['public_html/includes/templates/default.admin/js/app.js'],
          'public_html/includes/templates/default.catalog/js/app.min.js' : ['public_html/includes/templates/default.catalog/js/app.js'],
        }
      },
    },

    phplint: {
      options: {
        //phpCmd: 'C:/xampp/php/php.exe', // Defaults to php
        limit: 10,
        stdout: false
      },
      files: 'public_html/**/*.php'
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
          'public_html/ext/featherlight/featherlight.less',
          'public_html/includes/templates/**/*.less',
        ],
        tasks: ['less']
      },

      javascripts: {
        files: [
          'public_html/ext/featherlight/featherlight.js',
          'public_html/includes/templates/**/js/*.js',
        ],
        tasks: ['uglify']
      },

      sass: {
        files: [
          'public_html/ext/trumbowyg/ui/trumbowyg.scss',
        ],
        tasks: ['dart-sass']
      },
    }
  });

  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-dart-sass');
  grunt.loadNpmTasks('grunt-text-replace');

  grunt.registerTask('default', ['replace', 'less', 'dart-sass', 'uglify']);
  grunt.registerTask('compile', ['less', 'dart-sass', 'uglify']);

  require('phplint').gruntPlugin(grunt);
  grunt.registerTask('test', ['phplint']);
};