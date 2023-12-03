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
      backend_variables: {
        options: {
          compress: false,
          sourceMap: false,
          relativeUrls: true
        },
        files: {
          'public_html/backend/template/css/variables.css' : 'public_html/backend/template/less/variables.less',
        }
      },

      backend_minified: {
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

      frontend: {
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

      frontend_classic_minified: {
        options: {
          compress: true,
          sourceMap: true,
          sourceMapBasepath: 'public_html/frontend/templates/classic/less/',
          sourceMapRootpath: '../less/',
          sourceMapURL: function(path) { return path.replace(/.*\//, '') + '.map'; },
          relativeUrls: true
        },
        files: {
          'public_html/frontend/templates/classic/css/app.min.css'       : 'public_html/frontend/templates/classic/less/app.less',
          'public_html/frontend/templates/classic/css/checkout.min.css'  : 'public_html/frontend/templates/classic/less/checkout.less',
          'public_html/frontend/templates/classic/css/framework.min.css' : 'public_html/frontend/templates/classic/less/framework.less',
          'public_html/frontend/templates/classic/css/printable.min.css' : 'public_html/frontend/templates/classic/less/printable.less',
        }
      },

      frontend_default_minified: {
        options: {
          compress: true,
          //sourceMap: false,
          //sourceMapBasepath: 'public_html/ext/featherlight/',
          //sourceMapRootpath: './',
          //sourceMapURL: function(path) { return path.replace(/.*\//, '') + '.map'; },
          relativeUrls: true
        },
        files: {
          'public_html/frontend/templates/default/css/app.min.css'       : 'public_html/frontend/templates/default/less/app.less',
          'public_html/frontend/templates/default/css/checkout.min.css'  : 'public_html/frontend/templates/default/less/checkout.less',
          'public_html/frontend/templates/default/css/framework.min.css' : 'public_html/frontend/templates/default/less/framework.less',
          'public_html/frontend/templates/default/css/printable.min.css' : 'public_html/frontend/templates/default/less/printable.less',
        }
      },

      featherlight_minified: {
        options: {
          compress: true,
          sourceMap: false,
          //sourceMapBasepath: 'public_html/assets/featherlight/',
          //sourceMapRootpath: './',
          //sourceMapURL: function(path) { return path.replace(/.*\//, '') + '.map'; },
          relativeUrls: true
        },
        files: {
          'public_html/assets/featherlight/featherlight.min.css': 'public_html/assets/featherlight/featherlight.less',
        }
      },
    },

    'dart-sass': {
      chartist_minified: {
        options: {
          sourceMap: false,
          outputStyle: 'compressed',
          compass: false
        },
        files: {
          'public_html/assets/chartist/chartist.min.css': 'public_html/assets/chartist/chartist.scss'
        }
      },
      trumbowyg_minified: {
        options: {
          sourceMap: false,
          outputStyle: 'compressed',
          compass: false
        },
        files: {
          'public_html/assets/trumbowyg/ui/trumbowyg.min.css': 'public_html/assets/trumbowyg/ui/trumbowyg.scss'
        }
      }
    },

    concat: {
      backend: {
        //options: {
        //  stripBanners: true,
        //  banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
        //    '<%= grunt.template.today("yyyy-mm-dd") %> */',
        //},
        files: {
          'src/project.js': ['public_html/frontend/templates/classic/js/app.min.js']
        },
      },
      frontend: {
        files: {
          //'public_html/frontend/templates/classic/js/app.js': ['public_html/frontend/templates/classic/js/components/*.js'],
          'public_html/frontend/templates/default/js/app.js': ['public_html/frontend/templates/default/js/components/*.js']
        },
      }
    },

    uglify: {
      featherlight: {
        options: {
          sourceMap: false,
        },
        files: {
          'public_html/assets/featherlight/featherlight.min.js': ['public_html/assets/featherlight/featherlight.js'],
        }
      },
      litecart: {
        options: {
          sourceMap: true,
        },
        files: {
          'public_html/backend/template/js/app.min.js':           ['public_html/backend/template/js/app.js'],
          'public_html/frontend/templates/classic/js/app.min.js': ['public_html/frontend/templates/classic/js/app.js'],
          'public_html/frontend/templates/default/js/app.min.js': ['public_html/frontend/templates/default/js/app.js'],
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
          'public_html/backend/template/**/*.js',
          'public_html/frontend/templates/**/*.js',
          '!public_html/frontend/templates/**/*.min.js',
        ],
        tasks: ['uglify']
      },

      sass: {
        files: [
          'public_html/assets/trumbowyg/ui/trumbowyg.scss',
        ],
        tasks: ['dart-sass']
      },
    }
  });

  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-dart-sass');
  grunt.loadNpmTasks('grunt-text-replace');

  grunt.registerTask('default', ['replace', 'less', 'dart-sass', 'concat', 'uglify']);
  grunt.registerTask('compile', ['less', 'dart-sass', 'concat', 'uglify']);

  require('phplint').gruntPlugin(grunt);
  grunt.registerTask('test', ['phplint']);
};