<? php 

// The controller methods for Algorithm Wars
// Note that functions in this file will ultimately have to be extracted
// out and added to eterna_get_controller.php and eterna_post_controller.php

class EternaGETController extends EternaController {

	function __construct() { 
		parent::__construct();
	}


	// POST Controller methods
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

		if($type == "awalgorithms") {
			$func = $params["func"];
			if($func == "add") {
				$algorithm_model->add_algorithm($params, $params["uid"], $user_model);
			}
		} else if($type == "awupdate") {
			// justin's cron is sending us data
			// send to algorithm model to also update matches table
			 $algorithm_model->update_matches($params["data"], $puzzle_model);
		}
	}


	//GET Controller Methods
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
				} else if($func == "top") {
					$data['ret'] = $puzzle_model->get_top_ranked_puzzles($params["num"]);
				} else if($func == "queue") {
					$data['ret'] = $puzzle_model->get_current_puzzle_queue();
				}
			} else if($type == "awalgorithms") {

				$func = $params["func"];

				if(!$func) {
					$nid = $params["nid"];
					$data['ret'] = $algorithm_model->get_algorithm($nid);
				} else if($func == "top") {
					$num = $params["num"];
					$data['ret'] = $algorithm_model->get_top_ranked_algorithms($num);
				} else if($func == "queue") {
					$id = $params["id"];
					$data['ret'] = $algorithm_model->get_current_algorithm_queue($id);
				}
		}
	}
}


?>
