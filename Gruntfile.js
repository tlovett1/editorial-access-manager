module.exports = function ( grunt ) {
	grunt.initConfig( {
		pkg : grunt.file.readJSON( 'package.json' ),
		uglify : {
			js : {
				files : [{
					expand: true,
					cwd: 'js',
					src: '**/*.js',
					dest: 'build/js/',
					ext: '.min.js'
				}]
			}
		},
		jshint : {
			options : {
				smarttabs : true
			}
		},
		sass : {
			dist : {
				files : {
					'build/css/post-admin.css' : 'scss/post-admin.scss'
				},
				options : {
					sourcemap: 'none'
				}
			}
		},
		cssmin : {
			backend : {
				src : 'build/css/post-admin.css',
				dest : 'build/css/post-admin.min.css'
			}
		},
		watch : {
			files : [
				'js/*',
				'scss/*'
			],
			tasks : ['jshint', 'uglify', 'sass', 'cssmin:backend']
		},
		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					potFilename: 'editorial-access-manager.pot',
					processPot: function( pot ) {
						pot.headers['report-msgid-bugs-to'] = 'https://github.com/tlovett1/editorial-access-manager/issues\n';
						pot.headers['plural-forms'] = 'nplurals=2; plural=n != 1;';
						pot.headers['x-poedit-basepath'] = '.\n';
						pot.headers['x-poedit-language'] = 'English\n';
						pot.headers['x-poedit-country'] = 'United States\n';
						pot.headers['x-poedit-sourcecharset'] = 'utf-8\n';
						pot.headers['x-poedit-keywordslist'] = '__;_e;__ngettext:1,2;_n:1,2;__ngettext_noop:1,2;_n_noop:1,2;_c,_nc:4c,1,2;_x:1,2c;_ex:1,2c;_nx:4c,1,2;_nx_noop:4c,1,2;\n';
						pot.headers['x-poedit-bookmarks'] = '\n';
						pot.headers['x-poedit-searchpath-0'] = '.\n';
						pot.headers['x-textdomain-support'] = 'yes\n';
						return pot;
					},
					type: 'wp-plugin',
					updateTimestamp: true
				}
			}
		}
	} );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.registerTask( 'default', ['jshint', 'uglify:js', 'sass', 'cssmin:backend', 'makepot'] );
};