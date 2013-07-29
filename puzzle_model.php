<?php

include_once(ETERNA_WORKBRANCH_BACKEND.'eterna_utils.php');

class EternaPuzzleModel {

  function get_puzzle($nid) {
    $query = "SELECT n.title, n.created, puz.field_puzzle_objective_value AS object, puz.field_puzzle_rna_type_value AS rna_type,u.uid AS uid, u.name AS username, u.picture AS userpicture, puz.field_reward_puzzle_value AS reward, puz.field_structure_value AS secstruct, puz.field_puzzle_solved_by_bot_value AS 'solved-by-bot', puz.field_puzzle_num_cleared_value AS 'num-cleared', n.nid AS id, nr.body, puz.field_puzzle_locks_value AS locks, puz.field_begin_seq_value AS beginseq, puz.field_use_tails_value AS usetails, puz.field_constraints_puzzle_value AS constraints, puz.field_scoring_puzzle_value AS scoring, puz.field_folder_puzzle_value AS folder, puz.field_made_by_player_value AS 'made-by-player',puz.field_tutorial_level_puzzle_value AS 'tutorial-level', puz.field_ui_specs_puzzle_value AS 'ui-specs', puz.field_puzzle_type_value AS 'type', puz.field_puzzle_last_synthesis_value AS 'last-round', puz.field_next_puzzle_value AS 'next-puzzle', field_puzzle_objective_value AS objective, field_puzzle_check_hairpin_value AS check_hairpin, field_puzzle_cloud_round_value AS cloud_round FROM content_type_puzzle puz LEFT JOIN node n ON puz.nid=n.nid LEFT JOIN node_revisions nr ON n.vid=nr.vid LEFT JOIN users u ON u.uid=n.uid WHERE n.nid=$nid";
    $result = db_query($query);
    if($res = db_fetch_array($result)) {
      if($res['rna_type'] == "switch"){
        $objs = json_decode($res['object']);
        $obj_arr = array();
        foreach($objs AS $obj){
          foreach($obj AS $key => $value){
            if($key == "secstruct")
              array_push($obj_arr, $value);
          }  
        }
        $res['switch_struct'] = $obj_arr;
      }
       
      $res['created'] = date("d M Y", $res['created']);
      return $res;
    }
    
    return null;
  }

  function get_rated_puzzles($min, $max) {
    $query = "SELECT node.nid AS id, node.created AS created, puz.field_puzzle_rating AS rating, puz.field_puzzle_rna_type_value AS rna_type, puz.field_puzzle_type_value AS 'type', puz.field_structure_value AS 'secstruct', puz.field_puzzle_num_cleared_value AS 'num-cleared' FROM node node, content_type_puzzle puz WHERE node.nid = puz.nid AND node.status <> 0 AND puz.field_puzzle_type_value != \"Experimental\"";
    if($max != -1) $where = "WHERE rating >= " . $min . " AND rating < " . $max;
    else $where = "WHERE rating >= " . $min;
    $result = db_query($query . " " . $where);
    $puzzles = array();
    
    while($res = db_fetch_array($result)) {
      array_push($puzzles,$res);
    }
    
    return $puzzles;
  }

  function get_easy_puzzles() {
    return get_rated_puzzles(1200, 1400);
  }

  function get_medium_puzzles() { 
    return get_rated_puzzles(1400, 1600);
  }

  function get_hard_puzzles() {
    return get_rated_puzzles(1600, 1800);
  }

  function get_very_hard_puzzles() {
    return get_rated_puzzles(1800, 2000);
  }

  function get_expert_puzzles() {
    return get_rated_puzzles(2000, -1);
  }

  function get_rnd_puzzles($numpuzzles, $numTimesTested, $constraintsAllowed) {
    $query = "SELECT node.nid AS id, node.created AS created, puz.field_puzzle_rating AS rating, puz.field_puzzle_tested AS tested, puz.field_constraints_puzzle_value AS constraints, puz.field_puzzle_rna_type_value AS rna_type, puz.field_puzzle_type_value AS 'type', puz.field_structure_value AS 'secstruct', puz.field_puzzle_num_cleared_value AS 'num-cleared' FROM node node, content_type_puzzle puz WHERE node.nid = puz.nid AND node.status <> 0 AND puz.field_puzzle_type_value != \"Experimental\"";
    $where = "";
    if($constraintsAllowed == 0) $where = "WHERE constraints IS NULL";
    if($constraintsAllowed == 1) $where = "WHERE constraints IS NOT NULL";

    // commas are delimeter, no spaces

    $result = $db_query($query);
    $puzzles = array();
    while($res = db_fetch_array($result)) {
      if( ($numTimesTested < 0) || (substr_count($res['tested'], ',') + 1 == $numTimesTested))
        array_push($puzzles, $res);
    }

    return array_rand($puzzles, $numpuzzles);
  }

  // Method of function overloading
  function get_puzzle() {
    switch func_num_args() {
      case 1 : 
        // get_puzzle($nid) - returns the puzzle with the given id, as an array of characteristics
        return _get_puzzle(func_get_arg(0));
      case 2 : 
        // gets a puzzle with 
        return __get_puzzle(func_get_arg(0), func_get_arg(1));
      default :
        throw new Exception("get_puzzle invalid arguments");
    }
  }

  function get_puzzles($args) {
    $query = "SELECT n.nid AS id, n.title, n.created, u.name AS username, u.picture AS userpicture, puz.field_made_by_player_value AS 'made-by-player', puz.field_puzzle_num_cleared_value AS 'num-cleared', puz.field_puzzle_type_value AS type, puz.field_puzzle_solved_by_bot_value AS 'solved-by-bot', puz.field_reward_puzzle_value AS reward, puz.field_puzzle_made_for_lab_value AS 'made-for-lab' FROM content_type_puzzle puz LEFT JOIN node n ON n.nid=puz.nid LEFT JOIN users u ON u.uid=n.uid";
    $where = "WHERE n.status <> 0";
    
    if($args['puzzle_type'] == "Basic" ) {
      $where = "$where AND puz.field_puzzle_type_value = 'Basic'";
    } else if($args['puzzle_type'] == "PlayerPuzzle") {
      $where = "$where AND puz.field_puzzle_type_value = 'Challenge' AND puz.field_made_by_player_value = 1";
      if($args['uid']) {
//        $where = "$where AND n.uid=".$args['uid'];
      }
    } else {
      $where = "$where AND puz.field_puzzle_type_value = 'Challenge' AND puz.field_made_by_player_value != 1";
    }

    if($args['search']) {
      $search = mysql_escape_string(strtoupper($args['search']));
      if(!$args['simple'] && $args['puzzle_type'] == "PlayerPuzzle")
        $where = "$where AND (UPPER(n.title) LIKE '%$search%' OR UPPER(UPPER(u.name)) LIKE '%$search%')";
      else
        $where = "$where AND UPPER(n.title) LIKE '%$search%'";
    }
    
    if($args['single'] == "fail") 
      if($args['switch'] == "fail") 
        $where = "$where AND (puz.field_puzzle_rna_type_value = 'single' OR puz.field_puzzle_rna_type_value = 'switch')";
      else
        $where = "$where AND puz.field_puzzle_rna_type_value = 'single'";
    else      
      if($args['switch'] == "fail") 
        $where = "$where AND puz.field_puzzle_rna_type_value = 'switch'";    
    
    if($args['vienna'] == "fail") $where = "$where AND puz.field_puzzle_solved_by_bot_value like \"%ViennaRNA_Failure%\"";
    if($args['rnassd'] == "fail") $where = "$where AND puz.field_puzzle_solved_by_bot_value like \"%RNASSD_Failure%\"";
    if($args['inforna'] == "fail") $where = "$where AND puz.field_puzzle_solved_by_bot_value like \"%InfoRNA_Failure%\"";

    if($args['notcleared'] == "true"){
	    if($args['uid']){
		    $uid = $args['uid'];
		    $where = "$where AND (SELECT COUNT(*) as count FROM flag_content f WHERE f.fid=3 AND f.content_id=n.nid AND f.content_type=\"node\" AND f.uid=$uid)=0";
	    }
    }

    $order = "ORDER BY puz.field_reward_puzzle_value ASC";
    
    if($args['sort'] == "date") {
      $order = "ORDER BY n.created DESC";
    } else if($args['sort'] == "solved") {
      $order = "ORDER BY puz.field_puzzle_num_cleared_value DESC";
    } else if($args['sort'] == "length") {
      $order = "ORDER BY length(puz.field_structure_value) ASC";
    }
   
    $skip = 0;
    if ($args['skip'])
      $skip = $args['skip'];
    
    $size = 20;
    if ($args['size'])
      $size = $args['size'];

    $limit = "LIMIT $skip, ".$size;
    if($args['nolimit'])
      $limit ="";

    $full_query = "$query $where $order $limit";
    
    $result = db_query($full_query);
    $puzzles = array();
    
    while($res = db_fetch_array($result)) {
      array_push($puzzles,$res);
    }
    
    return $puzzles;

  }
  
  
  function get_total_puzzles($args) {
    $query = "SELECT COUNT(n.nid) FROM content_type_puzzle puz LEFT JOIN node n ON n.nid=puz.nid LEFT JOIN users u ON n.uid=u.uid";
    $where = "WHERE n.status <> 0";
    
    if($args['puzzle_type'] == "Basic" ) {
      $where = "$where AND puz.field_puzzle_type_value = 'Basic'";
    } else if($args['puzzle_type'] == "PlayerPuzzle") {
      $where = "$where AND puz.field_puzzle_type_value = 'Challenge' AND puz.field_made_by_player_value = 1";
      if($args['uid']) {
        //$where = "$where AND n.uid=".$args['uid'];
      }
    } else {
      $where = "$where AND puz.field_puzzle_type_value = 'Challenge' AND puz.field_made_by_player_value != 1";
    }

    if($args['search']) {
      $search = mysql_escape_string(strtoupper($args['search']));
      $where = "$where AND (UPPER(n.title) LIKE '%$search%' OR UPPER(UPPER(u.name)) LIKE '%$search%')";
    }

    if($args['single'] == "fail") 
      if($args['switch'] == "fail") 
        $where = "$where AND (puz.field_puzzle_rna_type_value = 'single' OR puz.field_puzzle_rna_type_value = 'switch')";
      else
        $where = "$where AND puz.field_puzzle_rna_type_value = 'single'";
    else      
      if($args['switch'] == "fail") 
        $where = "$where AND puz.field_puzzle_rna_type_value = 'switch'";

    if($args['vienna'] == "fail") $where = "$where AND puz.field_puzzle_solved_by_bot_value like \"%ViennaRNA_Failure%\"";
    if($args['rnassd'] == "fail") $where = "$where AND puz.field_puzzle_solved_by_bot_value like \"%RNASSD_Failure%\"";
    if($args['inforna'] == "fail") $where = "$where AND puz.field_puzzle_solved_by_bot_value like \"%InfoRNA_Failure%\"";

    if($args['notcleared'] == "true"){
      if($args['uid']){
        $uid = $args['uid'];
        $where = "$where AND (SELECT COUNT(*) as count FROM flag_content f WHERE f.fid=3 AND f.content_id=n.nid AND f.content_type=\"node\" AND f.uid=$uid)=0";
      }
    }
    $full_query = "$query $where";
  
    return db_result(db_query($full_query));  
  
  }

  function get_latest_puzzles($uid) {
    $query = "SELECT value From profile_values WHERE uid=$uid and fid=10";
    $res = db_fetch_array(db_query($query));
    
    $puznids = array();
    if($res['value'] != NULL)
      $puznids = explode(",", $res['value']);
    
    $puzzles = array();
    foreach($puznids AS $puznid){
       $puzzle = array();
       $_res = db_fetch_array(db_query("SELECT n.title from node n LEFT JOIN content_type_puzzle puz ON n.nid=puz.nid WHERE puz.nid=$puznid"));
       $puzzle['title'] = $_res['title'];
       $puzzle['puznid'] = $puznid;
       array_push($puzzles, $puzzle);
    }
    
    return $puzzles;
  }  

  function get_cleared_puzzles($uid) {
    /*
    * solutions.created has timestamp of puzzle solves. 
    */
    
    $query = "SELECT node.nid AS id, node.title, puz.field_puzzle_type_value AS 'type' FROM node node INNER JOIN flag_content flag_content_node ON node.nid = flag_content_node.content_id INNER JOIN content_type_puzzle puz ON node.nid = puz.nid WHERE node.status <> 0 AND flag_content_node.fid = 3 AND flag_content_node.content_type = \"node\" AND flag_content_node.uid = $uid AND puz.field_puzzle_type_value != \"Experimental\" AND node.status <> 0";
    $result = db_query($query);
    $puzzles = array();
    while($res = db_fetch_array($result))
      array_push($puzzles,$res);
    return $puzzles;
  }  

  function get_created_puzzles($uid) {
    $query = "SELECT node.nid AS id, node.title, puz.field_puzzle_type_value AS 'type' FROM node node LEFT JOIN content_type_puzzle puz ON puz.nid=node.nid WHERE node.uid=$uid AND puz.field_puzzle_type_value != \"Experimental\" AND node.status <> 0";
    $result = db_query($query);
    $puzzles = array();
    while($res = db_fetch_array($result))
      array_push($puzzles,$res);
    return $puzzles;
  }  

  function try_puzzle($puznid, $uid) {
  
    $query = "SELECT pt.field_puzzle_trial_puzzle_nid AS puzzle, pt.field_puzzle_trial_cleared_value AS cleared FROM content_type_puzzle_trial pt LEFT JOIN node n ON n.nid=pt.nid WHERE n.uid=$uid AND pt.field_puzzle_trial_puzzle_nid=$puznid";
    $result = db_query($query);
    $pt_exists = false;
    if($res = db_fetch_array($result)) {
      $pt_exists = true;
    }
  
    if($pt_exists == false) {
      $ptnode = new stdClass();
      $ptnode->type = "puzzle_trial";
      $ptnode->uid = $uid;
      $ptnode->created = time();
      $ptnode->status = 1;
      $ptnode->field_puzzle_trial_puzzle[0]['nid'] = $puznid;
      node_save($ptnode);
    }
  }

  function recommend_puzzle($uid) {
    
    if(!$uid)
      return null;
    
    $query = "SELECT n.title, n.created, puz.field_puzzle_objective_value AS object, puz.field_puzzle_rna_type_value AS rna_type, puz.field_reward_puzzle_value AS reward, puz.field_puzzle_type_value AS type, puz.field_structure_value AS secstruct, n.nid AS id, puz.field_puzzle_locks_value AS locks, puz.field_begin_seq_value AS beginseq, puz.field_use_tails_value AS usetails, puz.field_constraints_puzzle_value AS constraints, puz.field_scoring_puzzle_value AS scoring, puz.field_folder_puzzle_value AS folder, puz.field_made_by_player_value AS 'made-by-player', puz.field_tutorial_level_puzzle_value AS tutorial_level, puz.field_ui_specs_puzzle_value AS ui_specs, field_puzzle_objective_value AS objective FROM content_type_puzzle puz LEFT JOIN node n ON puz.nid=n.nid LEFT JOIN flag_content flag_content_node ON (n.nid = flag_content_node.content_id AND flag_content_node.uid=$uid AND flag_content_node.fid=3 AND flag_content_node.content_type='node') WHERE n.status <> 0 AND flag_content_node.uid IS NULL AND puz.field_puzzle_type_value != 'Experimental' AND puz.field_puzzle_impossible_value IS NULL ORDER BY type ASC, puz.field_made_by_player_value ASC, reward ASC LIMIT 5";
        
    $result = db_query($query);
    $puzzles = array();
    $is_there_puzzle = false; 
    while($res = db_fetch_array($result)) {
      if($res['rna_type'] == "switch"){
        $objs = json_decode($res['object']);
        $obj_str = "";
        foreach($objs AS $obj){
          foreach($obj AS $key => $value){
            if($key == "secstruct")
              $obj_str = $obj_str."<br>".$value."</br>";
          }  
        }
        $res['secstruct'] = $obj_str;
      }
             
      array_push($puzzles,$res);
      if($res['type'] == "Basic")
        return $res;
      
      $is_there_puzzle = true;
    }
    
    if($is_there_puzzle)
      return $puzzles[rand(0,count($puzzles)-1)];
    else
      return null;
  }

  function get_evaluation_puzzle($from, $to, $num){
    // get random puzzles from puzzles that at least $from users solved and at most $to users solved
    if(!$from || !$to) return null;
    if(!$num) $num = 1;
    $query = "SELECT node.nid AS id, node.title, puz.field_puzzle_objective_value AS object, puz.field_puzzle_rna_type_value AS rna_type, puz.field_puzzle_type_value AS 'type', puz.field_structure_value AS 'secstruct', puz.field_puzzle_num_cleared_value AS 'num-cleared' FROM node node INNER JOIN flag_content flag_content_node ON node.nid = flag_content_node.content_id INNER JOIN content_type_puzzle puz ON node.nid = puz.nid WHERE node.status <> 0 AND flag_content_node.fid = 3 AND flag_content_node.content_type = \"node\" AND puz.field_puzzle_type_value != \"Experimental\" AND puz.field_puzzle_num_cleared_value >= $from AND puz.field_puzzle_num_cleared_value <= $to order by rand() limit $num";
    $result = db_query($query);
    $puzzles = array();
    while($res = db_fetch_array($result)) {
      if($res['rna_type'] == "switch"){
        $objs = json_decode($res['object']);
        $obj_str = "";
        foreach($objs AS $obj){
          foreach($obj AS $key => $value){
            if($key == "secstruct")
              $obj_str = $obj_str."<br>".$value."</br>";
          }  
        }
        $res['secstruct'] = $obj_str;
      }
                   
      array_push($puzzles,$res);
    }
    /*
    $ret = array();
    $range = count($puzzles) / $num;
    $start = 0;
    while($num--){
      $end = $start + $range - 1;
      if(!$num) $end = count($puzzles) - 1;
      array_push($ret, $puzzles[rand($start, $end)]);
      $start += $range;
    }
    return $ret;
     */
    return $puzzles;
  }
  
  function get_total_puzzles_for_cron($params){
    $query = "SELECT node.nid AS id, node.created AS created, puz.field_puzzle_rna_type_value AS rna_type, puz.field_puzzle_type_value AS 'type', puz.field_structure_value AS 'secstruct', puz.field_puzzle_num_cleared_value AS 'num-cleared' FROM node node, content_type_puzzle puz WHERE node.nid = puz.nid AND node.status <> 0 AND puz.field_puzzle_type_value != \"Experimental\"";
    $result = db_query($query);
    $puzzles = array();
    while($res = db_fetch_array($result)) {
     array_push($puzzles,$res);
    }  
    return $puzzles;
  }
  function post_puzzle($args,$uid,$user_model) {
      
    if (!$user_model) {
      eterna_utils_log_error("Cannot find user model - please contact admin");
      return NULL;
    } 

    $current_time = time();
    $onedaybefore = $current_time - 86400;
  
    $points = $user_model->get_userpoints($uid);
    if($points < 20000) {
      eterna_utils_log_error("You must have at least 20,000 game points to submit a puzzle");
      return NULL;   
    }
    
    $query = "SELECT COUNT(n.created) FROM node n LEFT JOIN content_type_puzzle puz ON n.nid=puz.nid WHERE n.type=\"puzzle\" AND n.status <> 0 AND n.uid = %d AND n.created > $onedaybefore AND puz.field_puzzle_type_value = 'Challenge'";
    $count = db_result(db_query($query,$uid));
  
    if($count >= 3 && $uid != 10) {
      eterna_utils_log_error("You have already submitted 3 designs within past 24 hours");
      return NULL;
    }
    
    $secstruct = $args['secstruct'];
    if(!$secstruct) {
      eterna_utils_log_error("You cannot submit an empty structure");
      return NULL;
    }
    
    /**
    $query = "SELECT COUNT(n.nid) FROM content_type_puzzle puz LEFT JOIN node n ON n.nid=puz.nid WHERE puz.field_made_by_player_value = 1 AND n.status = 1 AND puz.field_structure_value=\"$secstruct\"";
    if(db_result(db_query($query)) > 0) {
      eterna_utils_log_error("This shape was already submitted by another player");
      /// Duplicate shape
      return false;
    }
    **/
     
    $node = new stdClass();
    $node->uid = $uid;
    $node->status = 1;

    $node->type = "puzzle";
    $node->created = $current_time;
    $node->field_puzzle_type[0]['value'] = "Challenge";
    $node->field_made_by_player[0]['value'] = 1;
    $node->comment = 2;
    $node->field_reward_puzzle[0]['value'] = 100;
  
    $node->title = $args['title'];
    $node->body = $args['body'];
    $node->field_structure[0]['value'] = $args['secstruct'];
    $node->field_constraints_puzzle[0]['value'] = $args['constraints'];
    $node->field_puzzle_locks[0]['value'] = $args['lock'];
    $node->field_begin_seq[0]['value'] = $args['begin_sequence'];
    $node->field_puzzle_objective[0]['value'] = $args['objectives'];
    if($args['is_for_lab'])
      $node->field_puzzle_made_for_lab[0]['value'] = 1;

    if ($uid == 10 && false) {
      $node->status = 1;
      $node->field_puzzle_type[0]['value'] = "Experimental";
      $node->field_made_by_player[0]['value'] = null;
      $node->field_reward_puzzle[0]['value'] = 0;
      $node->field_constraints_puzzle[0]['value'] = "SHAPE,0,CONSECUTIVE_G,4,CONSECUTIVE_C,4";
      $node->field_use_tails[0]['value'] = 1;
      $node->field_puzzle_last_synthesis[0]['value'] = 0;
      $node->field_puzzle_check_hairpin[0]['value']= 1;
      $node->field_exp_phase[0]['value'] = 1;
      $node->field_synthesis_date_puzzle[0]['value'] = "01/31/2013 11:59 PM";
    }

  
    node_save($node);
    
    if(!$node->nid) {
      //eterna_utils_log_error("Failed to submit puzzle - please contact site admins.");
      return NULL;
    }
  
    eterna_utils_save_file("/puzzle_big_thumbnails/thumbnail".$node->nid.".png",  base64_decode($args['bigimgdata']));
    eterna_utils_save_file("/puzzle_mid_thumbnails/thumbnail".$node->nid.".png",  base64_decode($args['midimgdata']));  
    
    return $node->nid;
  }
  function edit_puzzle($nid, $uid, $params){
    $title = $params['title'];
    $description = $params['description'];
    if ($title && $description){
      $node = node_load($nid);
      
      if ($uid != $node->uid) return false;
      
      $node->status = 1;
      $node->title = $title;
      $node->body = $description;
      node_save($node);
      return true;
    }
    else return false;
  }

  function post_cloud_lab_puzzle($args,$uid,$user_model) {
    
    /**  
    if (!$user_model) {
      eterna_utils_log_error("Cannot find user model - please contact admin");
      return NULL;
    } 

    $current_time = time();
    $onedaybefore = $current_time - 86400;
  
    $points = $user_model->get_userpoints($uid);
    if($points < 20000) {
      eterna_utils_log_error("You must have at least 20,000 game points to submit a puzzle");
      return NULL;   
    }
    
    $query = "SELECT COUNT(n.created) FROM node n LEFT JOIN content_type_puzzle puz ON n.nid=puz.nid WHERE n.type=\"puzzle\" AND n.status <> 0 AND n.uid = %d AND n.created > $onedaybefore AND puz.field_puzzle_type_value = 'Challenge'";
    $count = db_result(db_query($query,$uid));
  
    if($count >= 3 && $uid != 10) {
      eterna_utils_log_error("You have already submitted 3 designs within past 24 hours");
      return NULL;
    }
    **/

    
    $secstruct = $args['secstruct'];
    if(!$secstruct) {
      eterna_utils_log_error("You cannot submit an empty structure");
      return NULL;
    }

    $node = new stdClass();
    $node->uid = $uid;
    $node->status = 1;

    $node->type = "puzzle";
    $node->created = $current_time;
    $node->field_puzzle_type[0]['value'] = "Experimental";
    //$node->field_made_by_player[0]['value'] = 1;
    $node->comment = 2;
    $node->field_reward_puzzle[0]['value'] = 0;

    $constraints = "SHAPE,0,CONSECUTIVE_G,4,CONSECUTIVE_C,4,CONSECUTIVE_A,5,SOFT,0"; 
    if ($args['gus']) {
      $constraints = "GU,".$args['gus'].",".$constraints;
    }
  
    if ($args['gcs']) {
      $constraints = "GC,".$args['gcs'].",".$constraints;
    }  
  
    if ($args['aus']) {
      $constraints = "AU,".$args['aus'].",".$constraints;
    }
    
    //if (strlen($args['secstruct']) != 63)
      //return FALSE;
  
    $node->title = $args['title'];
    $node->body = $args['project-body'];
    $node->field_structure[0]['value'] = $args['secstruct']."(((((((....))))))).";
    $node->field_constraints_puzzle[0]['value'] = $constraints;
    $node->field_puzzle_locks[0]['value'] = $args['locks']."oooooooxxxxooooooox";
    $node->field_begin_seq[0]['value'] = $args['sequence']."AAAAAAAUUCGAAAAAAAA";
    $node->field_puzzle_objective[0]['value'] = $args['objectives'];
    
    $node->field_use_tails[0]['value'] = 1;
    $node->field_puzzle_last_synthesis[0]['value'] = 0;
    $node->field_puzzle_check_hairpin[0]['value']= 1;
    $node->field_exp_phase[0]['value'] = 1;
    $node->field_synthesis_date_puzzle[0]['value'] = "05/22/2013 11:59 PM";
    $node->field_puzzle_cover_image[0]['value'] = $args['filename'];
    $node->field_puzzle_num_synthesis[0]['value'] = $args['num_synthesis'];
    $node->field_puzzle_pending[0]['value'] = 1;
    $node->field_puzzle_selection[0]['value'] = $args['selection-type'];

    node_save($node);
    
    if(!$node->nid) {
      //eterna_utils_log_error("Failed to submit puzzle - please contact site admins.");
      return FALSE;
    }
  
    eterna_utils_save_file("/puzzle_cloud_thumbnails/thumbnail".$node->nid.".png",  base64_decode($args['thumbnail']));
    
    return $node->nid;
  }  
  
}


/**
function eterna_puzzle_get_puzzles($args) {

	$query = "";
	if($args['simple'])
		$query = ETERNA_PUZZLE_SIMPLE_SELECT_CLAUSE;
	else
		$query = ETERNA_PUZZLE_FULL_SELECT_CLAUSE;
	
	$where = "WHERE n.status <> 0";
	
	if($args['puzzle_type'] == "Basic" ) {
		$where = "$where AND puz.field_puzzle_type_value = 'Basic'";
	} else if($args['puzzle_type'] == "PlayerPuzzle") {
		$where = "$where AND puz.field_puzzle_type_value = 'Challenge' AND puz.field_made_by_player_value = 1";
		if($args['uid']) {
			$where = "$where AND n.uid=".$args['uid'];
		}
		
	} else {
		$where = "$where AND puz.field_puzzle_type_value = 'Challenge' AND puz.field_made_by_player_value != 1";
	}
		
	if($args['search']) {
		$search = mysql_escape_string(strtoupper($args['search']));
		if(!$args['simple'] && $args['puzzle_type'] == "PlayerPuzzle")
			$where = "$where AND (UPPER(n.title) LIKE '%$search%' OR UPPER(UPPER(u.name)) LIKE '%$search%')";
		else
			$where = "$where AND UPPER(n.title) LIKE '%$search%'";
	}
	
	$order = "ORDER BY puz.field_reward_puzzle_value ASC";
	
	if($args['sort'] == "date") {
		$order = "ORDER BY n.created DESC";
	} else if($args['sort'] == "solved") {
		$order = "ORDER BY puz.field_puzzle_num_cleared_value DESC";
	}
	
	
	$page = 0;
	if($args['pageindex']) {
		$page = $args['pageindex'];
	}
	$skip = ETERNA_PUZZLE_NUM_PUZZLE_PER_PAGE * $page;
	$limit = "LIMIT $skip, ".ETERNA_PUZZLE_NUM_PUZZLE_PER_PAGE;
	if($args['nolimit'])
		$limit ="";
	
	
	$full_query = "$query $where $order $limit";
	
	$result = db_query($full_query);
	
	$puzzles = array();
	
	while($res = db_fetch_array($result)) {
		array_push($puzzles,$res);
	}
	
	return $puzzles;
}

function eterna_puzzle_get_puzzle($nid) {
  $query = "SELECT n.title, n.created, puz.field_reward_puzzle_value AS reward, puz.field_structure_value AS secstruct, puz.field_puzzle_num_cleared_value AS 'num-cleared', n.nid AS id, nr.body, puz.field_puzzle_locks_value AS locks, puz.field_begin_seq_value AS beginseq, puz.field_use_tails_value AS usetails, puz.field_constraints_puzzle_value AS constraints, puz.field_scoring_puzzle_value AS scoring, puz.field_folder_puzzle_value AS folder, puz.field_made_by_player_value AS 'made-by-player',puz.field_tutorial_level_puzzle_value AS 'tutorial-level', puz.field_ui_specs_puzzle_value AS 'ui-specs', puz.field_puzzle_type_value AS 'type', puz.field_puzzle_last_synthesis_value AS 'last-round', puz.field_next_puzzle_value AS 'next-puzzle' FROM content_type_puzzle puz LEFT JOIN node n ON puz.nid=n.nid LEFT JOIN node_revisions nr ON n.vid=nr.vid WHERE n.nid=$nid";
  $result = db_query($query);
  if($res = db_fetch_array($result)) {
    $res['created'] = date("d M Y", $res['created']);
    return $res;
  }
  return null;
}


function eterna_puzzle_get_cleared_puzzles($uid) {
	$query = "SELECT node.nid AS id, node.title, puz.field_puzzle_type_value AS 'type' FROM node node INNER JOIN flag_content flag_content_node ON node.nid = flag_content_node.content_id INNER JOIN content_type_puzzle puz ON node.nid = puz.nid WHERE node.status <> 0 AND flag_content_node.fid = 3 AND flag_content_node.content_type = \"node\" AND flag_content_node.uid = $uid AND puz.field_puzzle_type_value != \"Experimental\"";
	$result = db_query($query);
	$puzzles = array();
	while($res = db_fetch_array($result))
		array_push($puzzles,$res);

	return $puzzles;
}

function eterna_puzzle_update_cleared($nid) {
	$node = node_load($nid);
	
	include_once(ETERNA_WORKBRANCH_BACKEND."/solution/eterna_solution_api.php");
	$node->field_puzzle_num_cleared[0]['value'] = eterna_solution_get_solution_count($nid);
	node_save($node);
}









**/



?>
