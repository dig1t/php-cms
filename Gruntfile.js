module.exports = function(grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
		
    // Tasks
		sass: { // Convert sass file into css
      dist: {
        options: {
          sourcemap: 'none'
        },
        files: [{
          expand: true,
          cwd: 'src/scss',
          src: [
						'admin.scss',
						'main.scss',
					],
          dest: 'dist/css',
          ext: '.css'
      	}]
      }
    },
		
		concat: { // Compile files into one
			dist: {
				src: [
					'dist/css/*.css',
					'!dist/css/*.min.css',
					'src/css/*.css',
					
					// ignore
					'!dist/css/admin.css'
				],
				dest: 'dist/css/build.css'
			}
		},
		
		cssmin: {
      dist: {
        files: [{
					expand: true,
					cwd: 'dist/css',
					src: '*.css',
          dest: 'dist/css',
					ext: '.min.css'
    		}]
      }
    },
		
		clean: {
		  dist: {
		    src: [
					'dist/css/*.php',
					
					// ignore
					'!dist/css/build.min.css',
					'!dist/css/admin.min.css'
				]
		  }
		},
		
    /*uglify: {
      build: {
        src: ['src/*.js'],
        dest: 'js/script.min.js'
      }
    },*/
		
    watch: { // Compile everything into one task with Watch Plugin
      css: {
        files: 'src/scss/*.scss',
        tasks: ['sass', 'cssmin', 'clean']
      }
      //js: {
        //files: 'src/js/*.js',
        //tasks: ['uglify']
      //}
    }
  })
	
	grunt.loadNpmTasks('grunt-contrib-sass')
  grunt.loadNpmTasks('grunt-contrib-concat')
	grunt.loadNpmTasks('grunt-contrib-cssmin')
	grunt.loadNpmTasks('grunt-contrib-uglify')
	grunt.loadNpmTasks('grunt-contrib-clean')
  grunt.loadNpmTasks('grunt-contrib-watch')
	
  grunt.registerTask('default', ['watch'])
}