<?php

class EternaAlgorithmsModel{

	var $current_queue = array();
	##################################
	##  Getter Methods
	##################################

	//returns algorithm with a given id
	function get_algorithm($nid){
		$query = "SELECT n.title, n.created, algorithm.field_algorithm_rating_value AS rating, algorithm.field_algorithm_code_value AS code, algorithm.field_algorithm_votes_value AS numvotes, algorithm.field_algorithm_description_value AS description, algorithm.field_algorithm_times_tested_value AS timestested, algorithm.field_algorithm_tested_puzzles_value AS tested, u.uid AS uid, u.name AS username, u.picture AS userpicture, n.nid AS id, nr.body FROM content_type_algorithm_wars_algorithms algorithm LEFT JOIN node n ON algorithm.nid=n.nid LEFT JOIN node_revisions nr ON n.vid=nr.vid LEFT JOIN users u ON u.uid=n.uid WHERE n.nid=$nid AND n.status <> 0";
		$result = db_query($query);
		if($res = db_fetch_array($result)) {
			$res['created'] = date("d M Y", $res['created']);
			return $res;
		}
		return null;
	}

	//Gets algorithms with ratings between ratingA and ratingB
	function get_algorithms_between($ratingA, $ratingB){
		$query = "SELECT n.title, n.created, algorithm.field_algorithm_rating_value AS rating, algorithm.field_algorithm_code_value AS code, algorithm.field_algorithm_votes_value AS numvotes, algorithm.field_algorithm_description_value AS description, algorithm.field_algorithm_times_tested_value AS timestested, algorithm.field_algorithm_tested_puzzles_value AS tested, u.uid AS uid, u.name AS username, u.picture AS userpicture, n.nid AS id, nr.body FROM content_type_algorithm_wars_algorithms algorithm LEFT JOIN node n ON algorithm.nid=n.nid LEFT JOIN node_revisions nr ON n.vid=nr.vid LEFT JOIN users u ON u.uid=n.uid WHERE n.nid=$nid AND n.status <> 0";
		$where = "WHERE rating >= $ratingA AND rating <= $ratingB";
		$result = db_query("$query $where");
		$algorithms = array();
		while($res = db_fetch_array($result))
			array_push($algorithms, $res);

		return $algorithms;
	}

	//Gets all the algorithms by a particular rating
	function get_algorithms_by_rating($rating){
		$query = "SELECT n.title, n.created, algorithm.field_algorithm_rating_value AS rating, algorithm.field_algorithm_code_value AS code, algorithm.field_algorithm_votes_value AS numvotes, algorithm.field_algorithm_description_value AS description, algorithm.field_algorithm_times_tested_value AS timestested, algorithm.field_algorithm_tested_puzzles_value AS tested, u.uid AS uid, u.name AS username, u.picture AS userpicture, n.nid AS id, nr.body FROM content_type_algorithm_wars_algorithms algorithm LEFT JOIN node n ON algorithm.nid=n.nid LEFT JOIN node_revisions nr ON n.vid=nr.vid LEFT JOIN users u ON u.uid=n.uid WHERE n.nid=$nid AND n.status <> 0";
		$where = "WHERE rating = $rating";
		$result = db_query("$query $where");
		$algorithms = array();
		while($res = db_fetch_array($result))
			array_push($algorithms, $res);

		return $algorithms;
	}

	//Gets the top numofAlgorithms algorithms based on the number of votes
	function get_top_voted_algorithms($numofAlgorithms){
		$query = "SELECT n.title, n.created, algorithm.field_algorithm_rating_value AS rating, algorithm.field_algorithm_code_value AS code, algorithm.field_algorithm_votes_value AS numvotes, algorithm.field_algorithm_description_value AS description, algorithm.field_algorithm_times_tested_value AS timestested, algorithm.field_algorithm_tested_puzzles_value AS tested, u.uid AS uid, u.name AS username, u.picture AS userpicture, n.nid AS id, nr.body FROM content_type_algorithm_wars_algorithms algorithm LEFT JOIN node n ON algorithm.nid=n.nid LEFT JOIN node_revisions nr ON n.vid=nr.vid LEFT JOIN users u ON u.uid=n.uid WHERE n.status <> 0";
		$order = "ORDER BY numvotes DESC";
		$limit = "LIMIT $numofAlgorithms";
		$full_query = "$query $order $limit";
		$result = db_query($full_query);
		$algorithms = array();
		while($res = db_fetch_array($result))
			array_push($algorithms, $res);

		return $algorithms;
	}

	//Gets the rating of a particular algorithm
	function get_algorithm_rating($id){
		$algorithm = get_algorithm($id);
		return $algorithm["rating"];
	}

	// get all algorithms
	function get_all_algorithms() {
		$numrows = db_result(db_query("SELECT COUNT(*) FROM content_type_algorithm_wars_algorithms"));
		$algorithms = get_top_voted_algorithms($numrows);
		return $algorithms;
	}

	//Get Algorithm ranking
	function get_algorithm_ranking($id){
		$algorithms = get_all_algorithms();
		$len = count($algorithms);
		for($i = 0; $i < $len; $i++) {
			if($algorithms[$i]["id"] == $id) return ($i + 1);
		}
		return null;
	}

	//Getter Method to get the complete current queue
	function get_current_algorithm_queue(){
		return $current_queue;
	}

	//Get next algorithm in queue
	function get_next_algorithm_in_queue(){
		$next_algorithm_id = array_pop($current_queue);
		//Returns algorithm id right now, must be changed if need be
		return $next_algorithm_id
	}

	##################################
	##  Setter Methods
	##################################

	//Adds an algorithm to the database
	function add_algorithm($args, $uid, $user_model){
		
		if(!$user_model) {
			eterna_utils_log_error("Cannot find user model - please contact admin");
      		return NULL;
		}

		$current_time = time();

    	$node = new stdClass();
    	$node->uid = $uid;
    	$node->status = 1;
    	$node->type = "algorithm_wars_algorithms";
    	$node->created = $current_time;
    	$node->comment = 2;
    	$node->title = $args['title'];
    	$node->body = $args['body'];

    	$node->field_algorithm_rating[0]['value'] = 1200;
    	$node->field_algorithm_code[0]['value'] = $args['code'];
    	$node->field_algorithm_votes[0]['value'] = 0;
    	$node->field_algorithm_description[0]['value'] = $args['description'];
    	$node->field_algorithm_tested_puzzles[0]['value'] = "";
    	$node->field_algorithm_times_tested[0]['value'] = 0;
    	$node->field_algorithm_input[0]['value'] = $args['input'];

    	node_save($node);

    	if(!$node->nid) {
    		return NULL;
    	}

    	return $node->nid;
	}

	//Deletes and algorithm from the database
	function delete_algorithm($aid){
		$query = "UPDATE node SET node.status=0 WHERE node.nid=$aid";
		return db_result(db_query($query));
	}

	//Update a previous algorithm with a given ID
	function modify_algorithm($id, $source) {
		$query = "UPDATE content_type_algorithm_wars_algorithms SET content_type_algorithm_wars_algorithms.field_algorithm_code_value=$source WHERE content_type_algorithm_wars_algorithms.nid=$id";
		return db_result(db_query($query));
	}

	//Add a vote for a particular algorithm by a given user
	function add_algorithm_vote($uid, $aid, $user_model){

		if(!$user_model) {
			eterna_utils_log_error("Cannot find user model - please contact admin");
      		return false;
		}
		// check if vote already exists
		// check if user has maxed out his num votes
		$votes = $user_model->get_algorithmvotes($uid);

		if(!is_null($votes)) {

			if(substr_count($votes, ',') >= 5) {
				// only 5 algorithms
				eterna_utils_log_error("You can only vote for a maximum of 5 algorithms!");
				return false;
			}

			if(strpos($votes, $aid) !== false) {
				// found already
				eterna_utils_log_error("You have already voted for this algorithm.");
				return false;
			}

			$user_model->set_algorithmvotes($uid, $votes . $aid . ',');
		} else {
			$user_model->set_algorithmvotes($uid, $aid . ',');
		}

		$query = "UPDATE content_type_algorithm_wars_algorithms SET content_type_algorithm_wars_algorithms.field_algorithm_votes_value=content_type_algorithm_wars_algorithms.field_algorithm_votes_value+1 WHERE content_type_algorithm_wars_algorithms.nid=$aid";
		return db_result(db_query($query));
	}

	//Remove a vote for a particular algorithm by a given user
	function remove_algorithm_vote($uid, $aid, $user_model){

		if(!$user_model) {
			eterna_utils_log_error("Cannot find user model - please contact admin");
      		return NULL;
		}

		$votes = $user_model->get_algorithmvotes($uid);
		if(is_null($votes) || strpos($votes, $pid) === false) {
			eterna_utils_log_error("You have not voted for this algorithm yet.");
			return false;
		}

		// safely remove the search $aid without causing damage to the other ids
		$votes = explode(",", $votes);
		$newvotes = array();
		foreach($votes as $val) {
			if($val !== $aid)
				array_push($newvotes, $val);
		}

		$user_model->set_algorithmvotes($uid, implode(",", $newvotes));
		$query = "UPDATE content_type_algorithm_wars_algorithms SET content_type_algorithm_wars_algorithms.field_algorithm_votes_value=content_type_algorithm_wars_algorithms.field_algorithm_votes_value-1 WHERE content_type_algorithm_wars_algorithms.nid=$aid";
	    return db_result(db_query($query));  
	}

	function set_rating($rating, $aid) {
		$query = "UPDATE content_type_algorithm_wars_algorithms SET content_type_algorithm_wars_algorithms.field_algorithm_rating_value=$rating WHERE content_type_algorithm_wars_algorithms.nid=$aid";
		return db_result(db_query($query));
	}

	//Set all algorithm ratings to default
	function set_default_ratings_for_all($defaultRating){
		$algorithms = get_all_algorithms();
		$len = count($algorithms);
		$query  = "UPDATE content_type_algorithm_wars_algorithms SET content_type_algorithm_wars_algorithms.field_algorithm_rating_value=$defaultRating";
		return db_result(db_query($query));
	}

	//Update Algorithm rating. 
	//Params need to be double checked in the controller
	//Possible causes of bugs are getter methods and parameters being passed.
	//Double check while testing.
	function update_algorithm_rating($aid,$pid,$algorithmScore, $puzzleScore){
		// Implementation of the ELO Rating

		if(!$puzzle_model) {
			eterna_utils_log_error("Cannot find Puzzle model - please contact admin");
      		return NULL;
		}

		$K = 32;

		$currentAlgorithmRating = $algorithm_model->get_algorithm_rating($aid);
		$currentPuzzleRating = $puzzle_model->get_puzzle_rating($pid);

		$expectedAlgorithmScore = (1/(1+pow(10,($currentPuzzleRating-$currentAlgorithmRating)/400)));
		$newAlgorithmRating = $currentAlgorithmRating + ($K * ($algorithmScore - $expectedAlgorithmScore));

		return set_rating($newAlgorithmRating, $aid);
	}

	//Add algorithms to queue based on which algorithm has been tested the
	//lowest getting first priority to get into the queue
	// Bug Pitfall: Can't pop the first element if the puzzle has been tested on it
	// Awaiting suggestions..
	function add_algorithms_to_queue(){
		//Implementation
	}
}