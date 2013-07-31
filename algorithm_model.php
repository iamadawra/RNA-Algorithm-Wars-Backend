<?php

class EternaAlgorithmsModel{

	##################################
	##  Getter Methods
	##################################


	//returns algorithm with a given id
	function get_algorithm($nid){
		$query = "SELECT n.title, n.created, algorithm.field_algorithm_rating_value AS rating, algorithm.field_algorithm_code_value AS code, algorithm.field_algorithm_votes_value AS numvotes, algorithm.field_algorithm_description_value AS description, algorithm.field_algorithm_times_tested_value AS timestested, algorithm.field_algorithm_tested_puzzles_value AS tested, u.uid AS uid, u.name AS username, u.picture AS userpicture, n.nid AS id, nr.body FROM content_type_algorithm_wars_algorithms algorithm LEFT JOIN node n ON algorithm.nid=n.nid LEFT JOIN node_revisions nr ON n.vid=nr.vid LEFT JOIN users u ON u.uid=n.uid WHERE n.nid=$nid"
		$result = db_query($query);
		if($res = db_fetch_array($result)) {
			$res['created'] = date("d M Y", $res['created']);
			return $res;
		}
		return null;
	}

	//Gets algorithms with ratings between ratingA and ratingB
	function get_algorithms_between($ratingA, $ratingB){
		$query = "SELECT n.title, n.created, algorithm.field_algorithm_rating_value AS rating, algorithm.field_algorithm_code_value AS code, algorithm.field_algorithm_votes_value AS numvotes, algorithm.field_algorithm_description_value AS description, algorithm.field_algorithm_times_tested_value AS timestested, algorithm.field_algorithm_tested_puzzles_value AS tested, u.uid AS uid, u.name AS username, u.picture AS userpicture, n.nid AS id, nr.body FROM content_type_algorithm_wars_algorithms algorithm LEFT JOIN node n ON algorithm.nid=n.nid LEFT JOIN node_revisions nr ON n.vid=nr.vid LEFT JOIN users u ON u.uid=n.uid WHERE n.nid=$nid"
		$where = "WHERE rating >= $ratingA AND rating <= $ratingB";
		$result = db_query("$query $where");
		$algorithms = array();
		while($res = db_fetch_array($result))
			array_push($algorithms, $res);

		return $algorithms;
	}

	//Gets all the algorithms by a particular rating
	function get_algorithms_by_rating($rating){
		$query = "SELECT n.title, n.created, algorithm.field_algorithm_rating_value AS rating, algorithm.field_algorithm_code_value AS code, algorithm.field_algorithm_votes_value AS numvotes, algorithm.field_algorithm_description_value AS description, algorithm.field_algorithm_times_tested_value AS timestested, algorithm.field_algorithm_tested_puzzles_value AS tested, u.uid AS uid, u.name AS username, u.picture AS userpicture, n.nid AS id, nr.body FROM content_type_algorithm_wars_algorithms algorithm LEFT JOIN node n ON algorithm.nid=n.nid LEFT JOIN node_revisions nr ON n.vid=nr.vid LEFT JOIN users u ON u.uid=n.uid WHERE n.nid=$nid"
		$where = "WHERE rating = $rating";
		$result = db_query("$query $where");
		$algorithms = array();
		while($res = db_fetch_array($result))
			array_push($algorithms, $res);

		return $algorithms;
	}

	//Gets the top numofAlgorithms algorithms based on the number of votes
	function get_top_voted_algorithms($numofAlgorithms){
		$query = "SELECT n.title, n.created, algorithm.field_algorithm_rating_value AS rating, algorithm.field_algorithm_code_value AS code, algorithm.field_algorithm_votes_value AS numvotes, algorithm.field_algorithm_description_value AS description, algorithm.field_algorithm_times_tested_value AS timestested, algorithm.field_algorithm_tested_puzzles_value AS tested, u.uid AS uid, u.name AS username, u.picture AS userpicture, n.nid AS id, nr.body FROM content_type_algorithm_wars_algorithms algorithm LEFT JOIN node n ON algorithm.nid=n.nid LEFT JOIN node_revisions nr ON n.vid=nr.vid LEFT JOIN users u ON u.uid=n.uid WHERE n.nid=$nid"
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

	//Get Algorithm ranking
	function get_algorithm_ranking($id){
		$numrows = db_result(db_query("SELECT COUNT(*) FROM content_type_algorithm_wars_algorithms"));
		$algorithms = get_top_voted_algorithms($numrows);
		for($i = 0; $i < $numrows; $i++) {
			if($algorithms[$i]["id"] == $id) return ($i + 1);
		}
		return null;
	}

	##################################
	##  Setter Methods
	##################################

	//Adds an algorithm to the database
	function add_algorithm($algorithm){
		//Implementation
	}

	//Deletes and algorithm from the database
	function delete_algorithm($algorithm){
		//Implementation
	}

	//Update a previous algorithm with a given ID
	function modify_algorithm($id){
		//Implementation
	}

	//Add a vote for a particular algorithm by a given user
	function add_algorithm_vote($uid, $aid){
		// check if vote already exists
		// check if user has maxed out his num votes
		$votes = $user_model->get_algorithmvotes($uid);

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
		$query = "UPDATE content_type_algorithm_wars_algorithms SET content_type_algorithm_wars_algorithms.field_algorithm_votes_value=content_type_algorithm_wars_algorithms.field_algorithm_votes_value+1 WHERE content_type_algorithm_wars_algorithms.nid=$aid";
		return db_result(db_query($query));
	}

	//Remove a vote for a particular algorithm by a given user
	function remove_algorithm_vote($uid, $aid){
		$votes = $user_model->get_algorithmvotes($uid);
		if(strpos($votes, $pid) === false) {
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

	//Set all algorithm ratings to default
	function set_default_ratings_for_all($defaultRating){
		//Implementation
	}

	//Update Algorithm rating
	function update_algorithm_rating($id){
		// What is this supposed to do???
	}
}