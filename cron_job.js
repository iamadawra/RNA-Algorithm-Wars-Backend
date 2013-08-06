#!/usr/bin/env node

// Node.js cron job
	// Absolute require paths
	// To run:
	// 0 22 * * * node cron_job.js
	// This will run it once a day, at 10 PM

var http = require('http');

function getData(queryoptions) {
	try {
		http.get(options, function(res) {
			var str = '';
			res.on('data', function(chunk) {
				str += chunk;
			});
			res.on('end', function() {
				return JSON.parse(str);
			});
		});
	} catch (e) {
		console.log(e.toString());
		return getData(queryoptions);
	}
}

function getScripts() {
	var queryoptions = {
		host: 'http://vineet.eternadev.org',
		path: '/get/?type=SOMETHING&arg1name=SOMETHINGELSE'
	}

	return getData(queryoptions);
}

function getPuzzles() {
	var queryoptions = {
		host: 'http://vineet.eternadev.org',
		path: '/get/?type=SOMETHING&arg1name=SOMETHINGELSE'
	}

	return getData(queryoptions);
}

function sendData(scripts, puzzles) {
	var queryoptions = {
		host: 'justinserverlink?.com',
		path: '/path/to/post/file'
	}

	getData(queryoptions);
}

function main() {
	var scripts = getScripts();
	var puzzles = getPuzzles();
	sendData(scripts, puzzles);
}