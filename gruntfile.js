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

      index: {
				src: ['public_html/index.php'],
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
      litecart_admin_template_minified: {
        options: {
          compress: true,
          sourceMap: true,
          sourceMapBasepath: 'public_html/includes/templates/default.admin/less/',
          sourceMapRootpath: '../less/',
          relativeUrls: true
        },
        files: {
          'public_html/includes/templates/default.admin/css/app.min.css'       : 'public_html/includes/templates/default.admin/less/app.less',
          'public_html/includes/templates/default.admin/css/framework.min.css' : 'public_html/includes/templates/default.admin/less/framework.less',
          'public_html/includes/templates/default.admin/css/printable.min.css' : 'public_html/includes/templates/default.admin/less/printable.less',
        }
      },
      litecart_catalog_template_minified: {
        options: {
          compress: true,
          sourceMap: true,
          sourceMapBasepath: 'public_html/includes/templates/default.catalog/less/',
          sourceMapRootpath: '../less/',
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
          sourceMap: true,
          sourceMapBasepath: 'public_html/ext/featherlight/',
          sourceMapRootpath: '/',
          relativeUrls: true
        },
        files: {
          'public_html/ext/featherlight/featherlight.min.css'       : 'public_html/ext/featherlight/featherlight.less',
        }
      },
    },

    sass: {
      trumbowyg: {
        options: {
          sourceMap: true,
          outputStyle: 'compressed',
          compass: false
        },
        files: {
          'public_html/ext/trumbowyg/ui/trumbowyg.min.css': 'public_html/ext/trumbowyg/ui/trumbowyg.scss'
        }
      }
    },

    uglify: {
      litecart: {
        files: {
          'public_html/includes/templates/default.admin/js/app.min.js'   : ['public_html/includes/templates/default.admin/js/app.js'],
          'public_html/includes/templates/default.catalog/js/app.min.js' : ['public_html/includes/templates/default.catalog/js/app.js'],
        }
      },
      featherlight: {
        files: {
          'public_html/ext/featherlight/featherlight.min.js'   : ['public_html/ext/featherlight/featherlight.js'],
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
      less: {
        files: [
          'public_html/includes/templates/*/less/*/*.less',
          'public_html/includes/templates/*/less/*.less',
          'public_html/ext/featherlight/featherlight.less'
        ],
        tasks: ['less']
      },
      javascripts: {
        files: [
          'public_html/ext/featherlight/featherlight.js',
          'public_html/includes/templates/*/js/app.js',
        ],
        tasks: ['uglify']
      },
      sass: {
        files: [
          'public_html/ext/trumbowyg/ui/trumbowyg.scss'
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

  require('phplint').gruntPlugin(grunt);
  grunt.registerTask('test', ['phplint']);
};