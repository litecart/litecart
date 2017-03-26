module.exports = function(grunt) {

  grunt.initConfig({
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
      responsiveslides_minified: {
        options: {
          compress: true,
          sourceMap: true,
          sourceMapBasepath: 'public_html/ext/responsiveslides/',
          sourceMapRootpath: '/',
          relativeUrls: true
        },
        files: {
          'public_html/ext/responsiveslides/responsiveslides.min.css'       : 'public_html/ext/responsiveslides/responsiveslides.less',
        }
      },
    },

    uglify: {
      litecart: {
        files: {
          'public_html/includes/templates/default.admin/js/app.min.js'   : ['public_html/includes/templates/default.admin/js/app.js'],
          'public_html/includes/templates/default.catalog/js/app.min.js' : ['public_html/includes/templates/default.catalog/js/app.js'],
        }
      },
    }
  });

  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  grunt.registerTask('default', ['less', 'uglify']);
};