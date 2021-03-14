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
      backend_template: {
        options: {
          compress: false,
          sourceMap: false,
          relativeUrls: true
        },
        files: {
          'public_html/backend/template/css/variables.css' : 'public_html/backend/template/less/variables.less',
        }
      },

      backend_template_minified: {
        options: {
          compress: true,
          sourceMap: true,
          sourceMapBasepath: 'public_html/backend/template/less/',
          sourceMapRootpath: '../less/',
          sourceMapURL: function(path) { return path.replace(/.*\//, '') + '.map'; },
          relativeUrls: true
        },
        files: {
          'public_html/backend/template/css/app.min.css'       : 'public_html/backend/template/less/app.less',
          'public_html/backend/template/css/framework.min.css' : 'public_html/backend/template/less/framework.less',
          'public_html/backend/template/css/printable.min.css' : 'public_html/backend/template/less/printable.less',
        }
      },

      frontend_template: {
        options: {
          compress: false,
          sourceMap: false,
          relativeUrls: true
        },
        files: {
          'public_html/frontend/templates/classic/css/app.css'       : 'public_html/frontend/templates/classic/less/app.less',
          'public_html/frontend/templates/classic/css/checkout.css'  : 'public_html/frontend/templates/classic/less/checkout.less',
          'public_html/frontend/templates/classic/css/framework.css' : 'public_html/frontend/templates/classic/less/framework.less',
          'public_html/frontend/templates/classic/css/printable.css' : 'public_html/frontend/templates/classic/less/printable.less',
          'public_html/frontend/templates/classic/css/variables.css' : 'public_html/frontend/templates/classic/less/variables.less',

          'public_html/frontend/templates/default/css/app.css'       : 'public_html/frontend/templates/default/less/app.less',
          'public_html/frontend/templates/default/css/checkout.css'  : 'public_html/frontend/templates/default/less/checkout.less',
          'public_html/frontend/templates/default/css/framework.css' : 'public_html/frontend/templates/default/less/framework.less',
          'public_html/frontend/templates/default/css/printable.css' : 'public_html/frontend/templates/default/less/printable.less',
          'public_html/frontend/templates/default/css/variables.css' : 'public_html/frontend/templates/default/less/variables.less',
        }
      },

      frontend_template_minified: {
        options: {
          compress: true,
          sourceMap: true,
          sourceMapBasepath: 'public_html/frontend/templates/default/less/',
          sourceMapRootpath: '../less/',
          sourceMapURL: function(path) { return path.replace(/.*\//, '') + '.map'; },
          relativeUrls: true
        },
        files: {
          'public_html/frontend/templates/default/css/app.min.css'       : 'public_html/frontend/templates/default/less/app.less',
          'public_html/frontend/templates/default/css/checkout.min.css'  : 'public_html/frontend/templates/default/less/checkout.less',
          'public_html/frontend/templates/default/css/framework.min.css' : 'public_html/frontend/templates/default/less/framework.less',
          'public_html/frontend/templates/default/css/printable.min.css' : 'public_html/frontend/templates/default/less/printable.less',

          'public_html/frontend/templates/default/css/app.min.css'       : 'public_html/frontend/templates/default/less/app.less',
          'public_html/frontend/templates/default/css/checkout.min.css'  : 'public_html/frontend/templates/default/less/checkout.less',
          'public_html/frontend/templates/default/css/framework.min.css' : 'public_html/frontend/templates/default/less/framework.less',
          'public_html/frontend/templates/default/css/printable.min.css' : 'public_html/frontend/templates/default/less/printable.less',
        }
      },

      featherlight_minified: {
        options: {
          compress: true,
          sourceMap: true,
          sourceMapBasepath: 'public_html/assets/featherlight/',
          sourceMapRootpath: './',
          sourceMapURL: function(path) { return path.replace(/.*\//, '') + '.map'; },
          relativeUrls: true
        },
        files: {
          'public_html/assets/featherlight/featherlight.min.css'       : 'public_html/assets/featherlight/featherlight.less',
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
          'public_html/assets/trumbowyg/ui/trumbowyg.min.css': 'public_html/assets/trumbowyg/ui/trumbowyg.scss'
        }
      }
    },

    uglify: {
      featherlight: {
        options: {
          sourceMap: true,
        },
        files: {
          'public_html/assets/featherlight/featherlight.min.js'   : ['public_html/assets/featherlight/featherlight.js'],
        }
      },
      litecart: {
        options: {
          sourceMap: true,
        },
        files: {
          'public_html/backend/template/js/app.min.js'   : ['public_html/backend/template/js/app.js'],
          'public_html/frontend/templates/classic/js/app.min.js' : ['public_html/frontend/templates/classic/js/app.js'],
          'public_html/frontend/templates/default/js/app.min.js' : ['public_html/frontend/templates/default/js/app.js'],
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
          'public_html/assets/featherlight/featherlight.less',
          'public_html/backend/template/**/*.less',
          'public_html/frontend/templates/**/*.less',
        ],
        tasks: ['less']
      },

      javascripts: {
        files: [
          'public_html/assets/featherlight/featherlight.js',
          'public_html/backend/template/**/js/*.js',
          'public_html/frontend/templates/**/js/*.js',
        ],
        tasks: ['uglify']
      },

      sass: {
        files: [
          'public_html/assets/trumbowyg/ui/trumbowyg.scss',
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