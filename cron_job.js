#!/usr/bin/env node

// Node.js cron job
	// Absolute require paths
	// To run:
	// 0 22 * * * node cron_job.js
	// This will run it once a day, at 10 PM

var http = require('http');

function getScriptsAndPuzzles() {
	var queryoptions = {
		host: 'http://vineet.eternadev.org',
		path: '/get/?type=SOMETHING&arg1name=SOMETHINGELSE'
	}

	try {
		http.get(options, function(res) {
			var str = '';
			res.on('data', function(chunk) {
				str += chunk;
			});
			res.on('end', function() {
				return JSON.parse(str);
			})
		})
	} catch (e) {
		console.log(e.toString());
		return getScriptsAndPuzzles(); // if error, repeat
	}

}

function sendScriptsAndPuzzles(scripts, puzzles) {
	var queryoptions = {
		host: 'justinserverlink?.com',
		path : '/path/to/post/file'
	}

	try {
		http.get(options, function(res) {
			var str = '';
			res.on('data', function(chunk) {
				str += chunk;
			});
			res.on('end', function() {
				return JSON.parse(str);
			})
		})
	} catch (e) {
		console.log(e.toString());
		sendScriptsAndPuzzles(); // if error, repeat
	}
}