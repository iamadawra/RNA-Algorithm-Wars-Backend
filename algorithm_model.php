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
		//Implementation
	}

	//Gets all the algorithms by a particular rating
	function get_algorithms_by_rating($rating){
		//Implementation
	}

	//Gets the top numofAlgorithms algorithms based on the number of votes
	function get_top_voted_algorithms($numofAlgorithms){
		//Implementation
	}

	//Gets the rating of a particular algorithm
	function get_algorithm_rating($id){
		//Implementation
	}

	//Get Algorithm ranking
	function get_algorithm_ranking($id){
		//Implementation
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
		//Implementation
	}

	//Remove a vote for a particular algorithm by a given user
	function remove_algorithm_vote($uid, $aid){
		//Implementation
	}

	//Set all algorithm ratings to default
	function set_default_ratings_for_all($defaultRating){
		//Implementatio
	}

	//Update Algorithm rating
	function update_algorithm_rating($id){
		//Implementation
	}
}