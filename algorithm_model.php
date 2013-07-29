<?php

class AlgorithmModel{

	##################################
	##  Getter Methods
	##################################


	//returns algorithm with a given id
	function get_algorithm($id){
		//implementation
	}

	//Overloaded method for fetching a method with a
	//specified rating
	function get_algorithm($id,$difficulty){
		//Implementation
	}


	//Gets algorithms with ratings between ratingA and ratingB
	function get_algorithms_between($ratingA, $ratingB){
		//Implementation
	}

	//Gets the top numofAlgorithms algorithms based on the number of votes
	function get_top_voted_algorithms($numofAlgorithms){
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
}