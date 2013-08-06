<?php

// php cron job
	// Absolute require paths
	// To run:
	// crontab -e
	// 0 22 * * * /usr/local/bin/php /absolute/path/to/cron_job.php
	// This will run it once a day, at 10 PM
	
	function getScripts() {
		$f = "vineet.eternadev.org/get/?type=awscript";
		$data = file_get_contents($f);
		return json_decode($data);
	}

	function getPuzzles() {
		$f = "vineet.eternadev.org/get/?type=awpuzzle";
		$data = file_get_contents($f);
		return json_decode($data);
	}

	function sendData($scripts, $puzzles) {
		$myscripts = $scripts['data']['algorithmlist'];
		$mypuzzles = $puzzles['data']['puzzlelist'];

		// http server is located on port 3000 in www/start.js of eval server
		$url = "ec2-54-242-61-159.compute-1.amazonaws.com:3000";
		$vars = 'scripts=' . $myscripts . '&puzzles=' . $mypuzzles;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}

	function main() {
		$scripts = getScripts();
		$puzzles = getPuzzles();
		sendData($scripts, $puzzles);
	}

	main();

?>