<?php
	require "algorithm_model.php";
	require "puzzle_model.php";
	require "user_model.php";

	require "db_func.php";

	echo "Opened Page.<br />";

	$params = $_GET['params'];
	$params = explode('|', $params);
	$function = $_GET['function'];

	$algo_model = new EternaAlgorithmsModel();
	$puzzle_model = new EternaPuzzleModel();
	$user_model = new EternaUserModel();

	$ret = "";

	if(method_exists($algo_model, $function)) {
		echo "Found in algorithm model<br />";
		$ret = call_user_func_array( array($algo_model, $function), $params);
	}

	else if(method_exists($puzzle_model, $function)) {
		echo "Found in puzzzle model<br />";
		$ret = call_user_func_array( array($puzzle_model, $function), $params);
	}

	else if(method_exists($user_model, $function)) {
		echo "Found in user model<br />";
		$ret = call_user_func_array( array($user_model, $function), $params);
	}

	else {
		echo "Not found: $function <br />";
	}

	echo("Result : \"" . $ret . "\"");

	print_r($ret);

?>