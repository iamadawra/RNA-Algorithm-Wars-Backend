<? php 

// The controller methods for Algorithm Wars
// Note that functions in this file will ultimately have to be extracted
// out and added to eterna_get_controller.php and eterna_post_controller.php

class EternaGETController extends EternaController {

	function __construct() { 
		parent::__construct();
	}

	function post_on_process($params) {
		// this function should be on_process,
		// I just seperated to make it easier to read
		global $user;

		$ret = array();
		$type = $params['type'];
		$data = array();
		$user_model = $this->get_model("EternaUserModel");
		$algorithm_model = $this->get_model("EternaAlgorithmsModel");      
		$puzzle_model = $this->get_model("EternaPuzzleModel");

		if($type == "awpuzzles") {
			$func = $params["func"];
			if($func == "reset") {
				$puzzle_model->resetQueue($params["nummatches"], -1); // 100 matches?
			} else if($func == "resetAll") {
				$puzzle_model->reset_all_puzzles();
			} else if($func == "update") {
				$puzzle_model->update_puzzle_rating($params["aid"], $params["pid"], $params["ascore"], $params["pscore"]);
			}
		} else if($type == "awalgorithms") {
			$func = $params["func"];
			if($func == "reset") {
				$algorithm_model->resetQueue($params["nummatches"]);
			} else if($func == "add") {
				$algorithm_model->add_algorithm($params, $params["uid"], $user_model);
			} else if($func == "delete") {	
				$algorithm_model->delete_algorithm($params["aid"]);
			} else if($func == "modify") {
				$algorithm_model->modify_algorithm($params["aid"], $params["source"]);
			} else if($func == "setrating") {
				$algorithm_model->set_rating($params["rating"], $params["aid"]);
			} else if($func == "setall") {
				$algorithm_model->set_default_ratings_for_all($params["defaultrating"]);
			} else if($func == "update") {
				$algorithm_model->update_algorithm_rating($params["aid"], $params["pid"], $params["ascore"], $params["pscore"]);
			} else if($func == "addQueue") {
				$algorithm_model->add_algorithms_to_queue($params["id"]);
			}
		} else if($type == "awupdate") {
			// justin's cron is sending us data
			// parse the data and send to the models
		}
	}

	function on_process($params) {
		global $user;

		$ret = array();
		$type = $params['type'];
		$data = array();
		$user_model = $this->get_model("EternaUserModel");
		$algorithm_model = $this->get_model("EternaAlgorithmsModel");      
		$puzzle_model = $this->get_model("EternaPuzzleModel");

			if($type == "awscript") {
				$data['ret'] = $algorithm_model->get_current_algorithm_queue();
				$algorithm_model->resetQueue($params["nummatches"]); // 100 matches?
				// DONE
			} else if($type == "awpuzzle") {
				$data['ret'] = $puzzle_model->get_current_puzzle_queue();
				$puzzle_model->resetQueue($params["nummatches"], -1); // 100 matches?
				// get the queue of puzzles and reset the queue
			} else if($type == "awpuzzles") {
				// DONE
				$func = $params["func"];
				if(!$func) {
					$nid = $params["nid"];
					$data['ret'] = $puzzle_model->get_puzzle($nid);
				} else if($func == "next") {

				} else if($func == "favored") {
					$data['ret'] = $puzzle_model->get_favored_puzzles($params["num"]);
				} else if($func == "rated") {
					$data['ret'] = $puzzle_model->get_rated_puzzles($params["min"], $params["max"]);
				} else if($func == "tested") {
					$data['ret'] = $puzzle_model->get_least_tested_puzzles($params["num"], $params["constraints"]);
				} else if($func == "rating") {
					$data['ret'] = $puzzle_model->get_puzzle_rating($params["pid"]);
				} else if($func == "type") {
					if(method_exists($puzzle_model, "get_" . $params["level"] . "_puzzles"))
						$data['ret'] = call_user_func( array($puzzle_model, "get_" . $params["level"] . "_puzzles"));
					else
						$data['ret'] = "Function not found in line 51 of competition_controller :(";				
				}
			} else if($type == "awalgorithms") {
				// DONE.

				$func = $params["func"];

				if(!$func) {
					$nid = $params["nid"];
					$data['ret'] = $algorithm_model->get_algorithm($nid);
				} else if($func == "between") {
					$ratingA = $params["above"];
					$ratingB = $params["below"];
					$data['ret'] = $algorithm_model->get_algorithms_between($ratingA, $ratingB);
				} else if($func == "byrating") {
					$rating = $params["rating"];
					$data['ret'] = $algorithm_model->get_algorithms_by_rating($rating);
				} else if($func == "top") {
					$num = $params["num"];
					$data['ret'] = $algorithm_model->get_top_voted_algorithms($num);
				} else if($func == "rating") {
					$id = $params["id"];
					$data['ret'] = $algorithm_model->get_algorithm_rating($id);
				} else if($func == "all") {
					$data['ret'] = $algorithm_model->get_all_algorithms();
				} else if($func == "ranking") {
					$id = $params["id"];
					$data['ret'] = $algorithm_model->get_algorithm_ranking($id);
				} else if($func == "next") {
					$data['ret'] = $algorithm_model->get_next_algorithm_in_queue();
				}
		}
	}
}


?>
