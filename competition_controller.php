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
      		} else if($func == "top") {
      			$rating = $params["num"];
      		} else if($func == "rating") {
      			$rating = $params["id"];
      		} else if($func == "all") {

      		} else if($func == "ranking") {
      			$rating = $params["id"];
      		} else if($func == "queue") {

      		} else if($func == "next") {

      		}




			$nid = $parmas["bla"];
		} else if($type == "awpuzzles") {

		}

	}
}


?>
