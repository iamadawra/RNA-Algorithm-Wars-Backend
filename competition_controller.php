<? php 

// The controller methods for Algorithm Wars
// Note that functions in this file will ultimately have to be extracted
// out and added to eterna_get_controller.php and eterna_post_controller.php

class EternaGETController extends EternaController {

	function __construct() { 
		parent::__construct();
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
				$algorithm_model->resetQueue(100); // 100 matches?
				// DONE
			} else if($type == "awpuzzle") {
				$data['ret'] = $puzzle_model->get_current_puzzle_queue();
				$puzzle_model->resetQueue(100, -1); // 100 matches?
				// get the queue of puzzles and reset the queue
			} else if($type == "awpuzzles") {
				// put only new methods
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
