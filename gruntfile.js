/**
 * Build Plugin.
 *
 * @author korotkov@ud
 * @version 1.2.1
 * @param grunt
 */
module.exports = function( grunt ) {

  // Automatically Load Tasks.
  require( 'load-grunt-tasks' )( grunt, {
    pattern: 'grunt-*',
    config: './package.json',
    scope: 'devDependencies'
  });

  // Build Configuration.
  grunt.initConfig({

    // Get Package.
    package: grunt.file.readJSON( 'composer.json' ),

    // Compile Core and Template Styles.
    less: {
      production: {
        options: {
          yuicompress: true,
          relativeUrls: true
        },
        files: {
          'static/css/wpi-admin.css': [ 'static/css/src/wpi-admin.less' ],
          'static/css/ie7.css': [ 'static/css/src/ie7.less' ],
          'static/css/invoice_page_wpi_page_manage_invoice.css': [ 'static/css/src/invoice_page_wpi_page_manage_invoice.less' ],
          'static/css/invoice_page_wpi_page_settings.css': [ 'static/css/src/invoice_page_wpi_page_settings.less' ],
          'static/css/invoice_page_wpi_spc.css': [ 'static/css/src/invoice_page_wpi_spc.less' ],
          'static/css/jquery-ui-1.8.21.custom.css': [ 'static/css/src/jquery-ui-1.8.21.custom.less' ],
          'static/css/toplevel_page_wpi_main.css': [ 'static/css/src/toplevel_page_wpi_main.less' ],
          'static/css/wpi-data-tables.css': [ 'static/css/src/wpi-data-tables.less' ]
        }
      },
      development: {
        options: {
          yuicompress: false,
          relativeUrls: true
        },
        files: {
          'static/css/wpi-admin.css': [ 'static/css/src/wpi-admin.less' ],
          'static/css/ie7.css': [ 'static/css/src/ie7.less' ],
          'static/css/invoice_page_wpi_page_manage_invoice.css': [ 'static/css/src/invoice_page_wpi_page_manage_invoice.less' ],
          'static/css/invoice_page_wpi_page_settings.css': [ 'static/css/src/invoice_page_wpi_page_settings.less' ],
          'static/css/invoice_page_wpi_spc.css': [ 'static/css/src/invoice_page_wpi_spc.less' ],
          'static/css/jquery-ui-1.8.21.custom.css': [ 'static/css/src/jquery-ui-1.8.21.custom.less' ],
          'static/css/toplevel_page_wpi_main.css': [ 'static/css/src/toplevel_page_wpi_main.less' ],
          'static/css/wpi-data-tables.css': [ 'static/css/src/wpi-data-tables.less' ]
        }
      }
    },

    // Generate YUIDoc documentation.
    yuidoc: {
      compile: {
        name: '<%= package.name %>',
        description: '<%= package.description %>',
        version: '<%= package.version %>',
        url: '<%= package.homepage %>',
        options: {
          extension: '.js,.php',
          outdir: 'static/codex/',
          "paths": [
            "./lib",
            "./static/js"
          ]
        }
      }
    },

    // Watch for Development.
    watch: {
      options: {
        interval: 100,
        debounceDelay: 500
      },
      less: {
        files: [ 'static/css/src/*.less' ],
        tasks: [ 'less:production' ]
      }
    },

    // Generate Markdown Documentation.
    markdown: {
      all: {
        files: [
          {
            expand: true,
            src: 'readme.md',
            dest: 'static/codex',
            ext: '.html'
          }
        ],
        options: {
          markdownOptions: {
            gfm: true,
            codeLines: {
              before: '<span>',
              after: '</span>'
            }
          }
        }
      }
    },

    // Clean Directories.
    clean: {
      temp: [
        "static/cache"
      ],
      all: [
        "static/cache",
        "cache",
        "vendor",
        "node_modules",
        "composer.lock"
      ]
    },

    // Execute Shell Commands.
    shell: {
      install: {
        command: 'composer install',
        options: {
          stdout: true
        }
      },
      update: {
        command: 'composer update',
        options: {
          stdout: true
        }
      }
    }

  });

  // Register NPM Tasks.
  grunt.registerTask( 'default', [ 'markdown', 'less:production', 'yuidoc' ] );

  // Install Library.
  grunt.registerTask( 'install', [ 'markdown', 'less:production', 'yuidoc' ] );

  // Prepare for Distribution.
  grunt.registerTask( 'make-distribution', [ 'markdown', 'less:production', 'yuidoc' ] );

};