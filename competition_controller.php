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

		if($type == "awalgorithms") {

			$user_model = $this->get_model("EternaUserModel");
      		$algorithm_model = $this->get_model("EternaAlgorithmsModel");      
      		$puzzle_model = $this->get_model("EternaPuzzleModel");

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
      		} else if($func == "queue") {
      			$data['ret'] = $algorithm_model->get_current_algorithm_queue();
      		} else if($func == "next") {
      			$data['ret'] = $algorithm_model->get_next_algorithm_in_queue();
      		}




		} else if($type == "awpuzzles") {

		}

	}
}


?>
