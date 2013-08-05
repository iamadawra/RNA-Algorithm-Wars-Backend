<?php 

$link = mysqli_connect("localhost", "root", "root", "eterna");

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}


function db_query($query) {

	global $link;

	return mysqli_query($link, $query);
}

function db_fetch_array($result) {

	global $link;

	return mysqli_fetch_array($link, $result);
}

function db_result($args) {
	return $args;
}



?>