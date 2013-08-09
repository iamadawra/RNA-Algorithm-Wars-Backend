<?php


class EternaSolutionModel {

  function get_solution($nid) {
    $query = "SELECT n.title, n.nid AS id, nr.body, sol.field_sequence_value AS sequence, sol.field_puzzle_ref_solution_nid AS puznid, u.name, u.uid, u.picture, sol.field_synth_score_value AS 'synthesis-score', sol.field_solution_synthesis_round_value AS 'synthesis-round', sol.field_solution_submitted_round_value AS 'submitted-round', sol.field_gus_value AS gu, sol.field_gcs_value AS gc, sol.field_uas_value AS au, sol.field_melting_point_value AS meltpoint, sol.field_solution_energy_value AS energy, sol.field_solution_shape_value AS SHAPE, sol.field_solution_shape_threshold_value AS 'SHAPE-threshold', sol.field_solution_shape_max_value AS 'SHAPE-max', sol.field_solution_shape_min_value AS 'SHAPE-min', sol.field_solution_synthesis_data_value AS 'synthesis-data' FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid LEFT JOIN users u ON u.uid=n.uid LEFT JOIN node_revisions nr ON n.vid=nr.vid";
    
    if($args['simple']) {
      $query = "SELECT n.title, n.nid AS id, sol.field_puzzle_ref_solution_nid AS puznid, u.name, u.uid, u.picture, sol.field_solution_shape_value AS SHAPE, sol.field_synth_score_value AS 'synthesis-score' FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid LEFT JOIN users u ON u.uid=n.uid";
    }
    
    $where = "WHERE n.nid=".$nid;
    $sols = array();
    $result = db_query($full_query);
    while($res = db_fetch_array($result)) {
      if($res['synthesis-score'] && !$res['SHAPE']) {
        $res['synthesis-score'] = NULL;
      }         
      array_push($sols,$res);
    }
    return $sols;
  }
  
  function get_solution_info($nid){
    $query = "SELECT n.title, n.nid AS id, nr.body, sol.field_sequence_value AS sequence, puz.field_puzzle_objective_value AS switch_structs, puz.field_use_tails_value, sol.field_solution_shape_threshold_value AS shape_threshold, sol.field_solution_synthesis_data_value AS switch_shape_value, sol.field_solution_shape_value AS single_shape_value, puz.field_structure_value AS secstruct, sol.field_puzzle_ref_solution_nid AS puznid, u.name, u.uid, sol.field_synth_score_value AS score, sol.field_solution_synthesis_round_value AS ready, sol.field_solution_pending_value AS pending, sol.field_solution_submitted_round_value AS submitted_round FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid LEFT JOIN users u ON u.uid=n.uid LEFT JOIN node_revisions nr ON n.vid=nr.vid LEFT JOIN content_type_puzzle puz on puz.nid = sol.field_puzzle_ref_solution_nid";
    $where = " WHERE n.status <> 0 and n.nid=".$nid;
    $res = db_fetch_array(db_query($query.$where));
        
    $secstruct = $res['secstruct'];
    if($res['field_use_tails_value'] == 1) 
      $secstruct = ".....".$secstruct."....................";
    $res['secstruct'] = $secstruct;

    $single_shape_value = $res['single_shape_value'];
    if($single_shape_value != null)
      $single_shape_value = explode(",",$res['single_shape_value']);
    $res['single_shape_value'] = $single_shape_value;    
    
    $switch_shape_value = $res['switch_shape_value'];
    if($switch_shape_value != null)
      $switch_shape_value = json_decode($res['switch_shape_value']);
    $res['switch_shape_value'] = $switch_shape_value;
        
    if($res['switch_structs'] != null){
      $objs = json_decode($res['switch_structs']);
      $obj_arr = array();
      foreach($objs AS $obj){
        foreach($obj AS $key => $value){
          if($key == "secstruct")
            array_push($obj_arr, $value);
        }  
      }
      $res['switch_structs'] = $obj_arr;
    }    
    
    return $res;
    
  }

  function get_solutions($args) {
    $query = "SELECT n.title, n.nid AS id, n.created AS created, nr.body, sol.field_sequence_value AS sequence, sol.field_puzzle_ref_solution_nid AS puznid, u.name, u.uid, u.picture, sol.field_synth_score_value AS 'synthesis-score', sol.field_solution_synthesis_round_value AS 'synthesis-round', sol.field_solution_submitted_round_value AS 'submitted-round', sol.field_gus_value AS gu, sol.field_gcs_value AS gc, sol.field_uas_value AS au, sol.field_melting_point_value AS meltpoint, sol.field_solution_energy_value AS energy, sol.field_solution_shape_value AS SHAPE, sol.field_solution_shape_threshold_value AS 'SHAPE-threshold', sol.field_solution_shape_max_value AS 'SHAPE-max', sol.field_solution_shape_min_value AS 'SHAPE-min', sol.field_solution_synthesis_data_value AS 'synthesis-data' FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid LEFT JOIN users u ON u.uid=n.uid LEFT JOIN node_revisions nr ON n.vid=nr.vid";
    
    if($args['score_only']) {
      $query = "SELECT n.title, n.nid AS id, sol.field_puzzle_ref_solution_nid AS puznid, sol.field_synth_score_value AS score FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid";
    } else if($args['simple']) {
      $query = "SELECT n.title, n.nid AS id, sol.field_puzzle_ref_solution_nid AS puznid, u.name, u.uid, u.picture, sol.field_solution_shape_value AS SHAPE, sol.field_synth_score_value AS 'synthesis-score' FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid LEFT JOIN users u ON u.uid=n.uid";
    }
    
    $where = "WHERE n.type='solution' AND n.status <> 0 AND n.nid > 17320";
    $order = "ORDER BY n.nid DESC";
    
    if($args['uid']) {
      $where = "$where AND n.uid =".$args['uid'];
    }
    
    if($args['puznid']) {
      $where = "$where AND sol.field_puzzle_ref_solution_nid = ".$args['puznid'];
    }
    
    if($args['synthesized']) {
      $where = "$where AND sol.field_solution_synthesis_round_value > 0";
      if($args['winners_only']) {
        $where = "$where AND sol.field_synth_score_value > 93";
      }
      
      if($args['scored_only']) {
        $where = "$where AND sol.field_solution_shape_value IS NOT NULL";
      }
        
      $order = "ORDER BY sol.field_synth_score_value DESC";
    }
    
    if($args['limit']) {
      $full_query = "$query $where $order LIMIT 0,".$args['limit'];   
    } else {
      $full_query = "$query $where $order"; 
    }
    
   
    
    $sols = array();
    $result = db_query($full_query);
    while($res = db_fetch_array($result)) {
      if($res['synthesis-score'] && !$res['SHAPE']) {
        $res['synthesis-score'] = NULL;
      }         
      array_push($sols,$res);
    }
    
    return $sols;
    
  }
  
  // old & need to deleted
  function get_solutions_for_script($args){
    $query = "SELECT n.title, n.nid AS id, n.created AS created, sol.field_puzzle_ref_solution_nid AS puznid, sol.field_sequence_value AS sequence FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid LEFT JOIN content_type_puzzle puzz ON puzz.nid=sol.field_puzzle_ref_solution_nid";
    $where = "WHERE n.type='solution' AND n.status <> 0 AND n.nid > 17320 AND puzz.field_puzzle_type_value='Experimental'";
    $order = "ORDER BY n.nid DESC";
    $full_query = "$query $where $order"; 
    
    $sols = array();
    $result = db_query($full_query);
    while($res = db_fetch_array($result)) {
      array_push($sols,$res);
    }
    
    return $sols;
  }
  
  function get_solution_for_script($args){
    $nid = $args['nid'];
    if(!$nid) return false;
    
    $sols = array();
    
    $query = "SELECT n.title, n.nid AS id, n.title AS title, n.created AS created, puzz.field_structure_value AS secstruct, sol.field_puzzle_ref_solution_nid AS puznid, sol.field_sequence_value AS sequence FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid LEFT JOIN content_type_puzzle puzz ON puzz.nid=sol.field_puzzle_ref_solution_nid";
    $where = "WHERE sol.nid=$nid";
    $full_query = "$query $where";
    
    
    $result = db_query($full_query);
    while($res = db_fetch_array($result)){
      array_push($sols, $res);
    } 
    
    return $sols;
  }
  
  
  function get_user_synthesized_design($user){
  
    $query = "SELECT n.title, n.nid AS solnid, sol.field_puzzle_ref_solution_nid AS puznid,  sol.field_synth_score_value AS score FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid LEFT JOIN users u ON u.uid=n.uid WHERE n.type='solution' AND n.status <> 0 AND n.nid > 17320 AND n.uid = $user AND sol.field_solution_synthesis_round_value >= 1";
        
    $designs = array();
    $result = db_query($query);
    
    while($res = db_fetch_array($result)){
      array_push($designs, $res);
    }
    
    return $designs;    
    
  }

  function get_saved_sequence($uid, $nid ) {
    $query = "SELECT sol.field_sequence_value FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid WHERE sol.field_puzzle_ref_solution_nid=$nid AND n.uid=$uid AND n.status > 0";
    $result = db_query($query);
    if ($res = db_fetch_array($result)) {
      return $res['field_sequence_value'];
    }    
    return null;  
  }

  function get_hairpin_pool($cloud_round, $debug) {
    $hairpins = array();   
    if ($cloud_round) {
      $query = "SELECT sol.field_sequence_value FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid WHERE n.status <> 0 AND sol.field_solution_cloud_round_value=$cloud_round";  
      $result = db_query($query);
    } else {
      $query = "SELECT sol.field_sequence_value FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid LEFT JOIN content_type_puzzle puz ON puz.nid=sol.field_puzzle_ref_solution_nid LEFT JOIN node puznode ON puz.nid=puznode.nid WHERE n.status <> 0 AND puznode.status <> 0 AND puz.field_puzzle_check_hairpin_value = 1 AND puz.field_puzzle_cloud_round_value IS NULL";  
      $result = db_query($query);
    }
    while ($res = db_fetch_array($result)) {
      $seq = $res['field_sequence_value'];
      preg_match('/([AGUC]{7})AAAAGAAACAACAACAACAAC$/i', $seq, $hairpin_match);
      if ($hairpin_match[1]) {
        array_push($hairpins, $hairpin_match[1]);
      }
    }  
    return $hairpins;       
  }

  function post_solution($args, $uid, $user_model, $follow_model) {
        
    $puzzle_node = node_load($args['puznid']);
    $puzzle_type = $puzzle_node->field_puzzle_type[0]['value'];
    $round = $puzzle_node->field_puzzle_last_synthesis[0]['value'] + 1;
    $puzzle_scoring_type = $puzzle_node->field_scoring_puzzle[0]['value'];
    $puzzle_field_name = "";
 
    if(!$uid) {
      if($puzzle_type == "Experimental") {
        eterna_utils_log_error("Anonymous users cannot participate in the lab");
        return false;
      }
      
      $user_model->create_incognito();
      return $user_model->incognito_solve_puzzle($puzzle_node->nid, $puzzle_node->field_reward_puzzle[0]['value'], $args);
    }     
 
    /// Count puzzle trials  
    if($puzzle_type != "Experimental") {
      $puznid = $puzzle_node->nid;
      $query = "SELECT n.nid FROM content_type_puzzle_trial pt LEFT JOIN node n ON n.nid=pt.nid WHERE n.uid=$uid AND pt.field_puzzle_trial_puzzle_nid=$puznid";
      $pt_result = db_query($query);
      if($pt_res = db_fetch_array($pt_result)) {
        $ptnode = node_load($pt_res['nid']);
        if($ptnode->field_puzzle_trial_cleared[0]['value'] < 1) {
          $ptnode->field_puzzle_trial_cleared[0]['value'] = 1;
          $ptnode->field_puzzle_trial_done[0]['value'] = time();
          node_save($ptnode);
        }
      }   
    }   

    $submit_user = user_load($uid);
  
    $old_node_nid = -1;
    $update_solution = FALSE;
    $score_update_from = -1;
  
    if($puzzle_type == "Challenge" || $puzzle_type == "Basic") {
   
      $result = db_query("SELECT sol.nid FROM {content_type_solution} sol LEFT JOIN node n ON n.nid = sol.nid  WHERE n.uid = %d AND sol.field_puzzle_ref_solution_nid = %d",$uid,$puzzle_node->nid);
  
      if($res = db_fetch_array($result)) {
        $old_node_nid = $res['nid'];
      }
  
      if($old_node_nid > 0) {
        return true;
      }
  
    } else {
  
      $current_points = userpoints_get_current_points();
  
      if($current_points < 10000 && $puzzle_node->nid != 2665466) {   
        eterna_utils_log_error("You must have at least 10,000 game points to submit your design to the lab"); 
        return false;
      }
        
      $sequence = mysql_escape_string(strtoupper($args['sequence']));
      $solution_query = "SELECT n.title FROM {content_type_solution} sol LEFT JOIN node n ON n.nid = sol.nid WHERE n.type='solution' AND sol.field_solution_submitted_round_value = %d AND sol.field_puzzle_ref_solution_nid = %d AND n.status <> 0 AND UPPER(sol.field_sequence_value)=\"$sequence\" LIMIT 1";
      
      if($res = db_fetch_array(db_query($solution_query, $round, $puzzle_node->nid))) {
        $othertitle = $res['title'];  
        eterna_utils_log_error("Design \"$othertitle\" already used this sequence in the current round.");
        return false;
      }

      if ($puzzle_node->field_puzzle_check_hairpin[0]['value']) {

        preg_match('/[AUCG]{7}UUCG([AUCG]{7})AAAAGAAACAACAACAACAAC$/i', $sequence, $sequence_hairpin_match);
        if ($sequence_hairpin_match[1]) {
          $hairpin = $sequence_hairpin_match[1];
          
          if ($puzzle_node->field_puzzle_cloud_round[0]['value']) {
            $cloud_round = $puzzle_node->field_puzzle_cloud_round[0]['value'];
            $query = "SELECT n.title FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid WHERE n.status <> 0 AND sol.field_solution_cloud_round_value=$cloud_round AND UPPER(sol.field_sequence_value) REGEXP '".$hairpin."AAAAGAAACAACAACAACAAC$' LIMIT 1";  
          } else {
            $query = "SELECT n.title FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid LEFT JOIN content_type_puzzle puz ON puz.nid=sol.field_puzzle_ref_solution_nid LEFT JOIN node puznode ON puz.nid=puznode.nid WHERE n.status <> 0 AND puznode.status <> 0 AND puz.field_puzzle_check_hairpin_value = 1 AND puz.field_puzzle_cloud_round_value IS NULL AND UPPER(sol.field_sequence_value) REGEXP '".$hairpin."AAAAGAAACAACAACAACAAC$' LIMIT 1";
          }
          if($res = db_fetch_array(db_query($query))) {
            $othertitle = $res['title'];  
            eterna_utils_log_error("<FONT COLOR='#FEC942' SIZE='20'>Your \"Barcode\" is not unqiue!</FONT>\nAnother player already submitted a design <FONT COLOR='#FEC942'>\"$othertitle\"</FONT> with the same barcode.\nUnfortunately, we cannot synthesize your design unless you have a unique barcode.");             
            return false;
          }
        } else {
          eterna_utils_log_error("Hairpin missing!");
          return false;
        }
        
      }
      
 
      $solution_query = "SELECT n.title FROM {content_type_solution} sol LEFT JOIN node n ON n.nid = sol.nid WHERE n.type='solution' AND sol.field_puzzle_ref_solution_nid = %d AND n.status <> 0 AND sol.field_solution_synthesis_round_value > 0 AND UPPER(sol.field_sequence_value)=\"$sequence\" LIMIT 1";
      
      if($res = db_fetch_array(db_query($solution_query, $puzzle_node->nid))) {
        $othertitle = $res['title'];  
        eterna_utils_log_error("This sequence (used by $othertitle) was already chosen as a synthesis candidate");
        return false;
      }

      $solution_query = "SELECT sol.nid FROM {content_type_solution} sol LEFT JOIN node n ON n.nid = sol.nid WHERE n.type='solution' AND n.uid = %d AND sol.field_solution_submitted_round_value = %d AND sol.field_puzzle_ref_solution_nid = %d AND n.status <> 0";
      $result = db_query($solution_query,$uid,$round,$puzzle_node->nid);
  
      $old_solution_count = 0;
  
      while($obj = db_fetch_object($result)) {
        $old_solution_count++;
      }
      
      $is_bot = ($uid == 24553) || ($uid == 24195) || ($uid == 26533);

      if($old_solution_count > 2 && $is_bot == false) {
        eterna_utils_log_error("You already submitted 3 designs in this round");
        return false;
      }

    }
  
    /// Now we actually start saving the solution
    $node->type = 'solution';
    $node->status = 1;
  
    if($old_node_nid < 0) {
      $node->uid = $submit_user->uid;
      $node->name = $submit_user->name;
    }
  
    if($args['title'] && $args['title'] != "") {
      $node->title = $args['title'];
    }
  
    if($args['body'] && $args['body'] != "") {
      $node->body = $args['body'];
    }

    $node->comment = 2;
    $node->field_sequence[0]['value'] = $args['sequence'];
    $node->field_solution_energy[0]['value'] = $args['energy'];
    $node->field_gus[0]['value'] = $args['gu'];
    $node->field_gcs[0]['value'] = $args['gc'];
    $node->field_uas[0]['value'] = $args['ua'];
    $node->field_melting_point[0]['value'] = $args['melt'];
    $node->field_robust[0]['value'] = $args['robust'];
    $node->field_repetition[0]['value'] = $args['repetition'];
    $node->field_puzzle_ref_solution[0]['nid'] = $puzzle_node->nid;
    $node->field_puzzle_score_solution[0]['value'] = $args['puzzle_score'];
    $node->field_solution_submitted_round[0]['value'] = $round;
    $node->field_solution_cloud_round[0]['value'] = $puzzle_node->field_puzzle_cloud_round[0]['value'];    

    if ($is_bot)
      $node->field_solution_synthesis_round[0]['value'] = $round;

    $node->created = time();

    node_save($node);
  
    if($puzzle_type != "Experimental") {
      $puzzle_node->field_puzzle_num_cleared[0]['value'] = $puzzle_node->field_puzzle_num_cleared[0]['value'] + 1;
      node_save($puzzle_node);
    }
  
    $flag = flag_get_flag('completed') or die('no "completed" flag defined'); 
    /// flag cleared  
    if(!($flag->is_flagged( $node->field_puzzle_ref_solution[0]['nid'], $node->uid))) {
  
      $params = array();
      $params['uid'] = $node->uid;
      $params['points'] = $puzzle_node->field_reward_puzzle[0]['value'];
      if($puzzle_type == "Experimental")  $params['type'] = 1;
      else $params['type'] = 0;
      
      userpoints_userpointsapi($params);
      $flag->flag('flag', $puzzle_node->nid,$current_user);
    }
   
    if ($follow_model) 
      $follow_model->node_follow($node->uid, $node->nid); 
    return true;
    
  }  
  
  function post_solution_simple($puznid, $uid, $sequence, $title) {
    
    /// Now we actually start saving the solution
    $node->type = 'solution';
    $node->status = 1;
    $node->uid = $uid;
    $node->title = $title;  

    $node->comment = 2;
    $node->field_sequence[0]['value'] = $sequence;
    $node->field_puzzle_ref_solution[0]['nid'] = $puznid;
    $node->field_solution_submitted_round[0]['value'] = 1;
    //$node->field_solution_cloud_round[0]['value'] = $puzzle_node->field_puzzle_cloud_round[0]['value'];    
    $node->created = time();
    node_save($node);
  }



  function unpublish_solution($nid,$uid, $lab_model) {
    
    if (!$lab_model) {
      eterna_utils_log_error("Cannot find lab model");
      return false;
    }
        
    $node = node_load($nid);
    
    if($node->uid != $uid) {
      eterna_utils_log_error("You don't have a previlege to delete this solution");
      return false;
    }
    
    if($node->type != "solution") {
      eterna_utils_log_error("Not a solution");
      return false;
    } 
    
    $puzzle_nid = $node->field_puzzle_ref_solution[0]['nid'];
    $puzzle_node = node_load($puzzle_nid);
    
    if($puzzle_node->field_puzzle_type[0]['value'] == "Experimental") {
      $round = $lab_model->get_current_round($puzzle_nid);
      
      $sol_round = $node->field_solution_submitted_round[0]['value'];
      if($sol_round < $round) {
        eterna_utils_log_error("You cannot delete a solution form previous rounds");
        return false;
      }
      
      $votesum = $lab_model->get_votes_for_solution($nid, $round);
      
      if($votesum > 0) {
        eterna_utils_log_error("You cannot delete a solution that has already been voted");
        return false;
      }
    }
    
    $node->status = 0;
    
    node_save($node);
    
    return true;
  } 
  
  function eterna_solution_get_solution_count($puznid) {
    $query = "SELECT COUNT(n.nid) FROM content_type_solution sol LEFT JOIN node n ON n.nid=sol.nid WHERE n.type='solution' AND n.status <> 0 AND sol.field_puzzle_ref_solution_nid=$puznid";
    return db_result(db_query($query));
  }
     
}

function eterna_solution_score_synthesis($secstruct, $start_index, $shape, $threshold, $min) {
	$score = 0;
	$shapelen = count($shape);
	$score_count = 0;
	
	for($ii=0; $ii<$shapelen; $ii++) {
		$char_index = $ii + $start_index;
		if($char_index < 0 || $char_index >= strlen($secstruct))
			$char = ".";
		else
			$char = substr($secstruct,$char_index,1);

		if($char == ".") {
			if($shape[$ii] > $threshold / 4 + $min /4 * 3) {
				$score += 1;
			}
			$score_count++;
		} else if($char == "(" || $char == ")") {

			if($shape[$ii] < $threshold) {
				$score += 1;
			}
			$score_count++;
		} else {
			return -1;
		}
	}

	if($score_count > 0)
		$score /= $score_count;
	return $score * 100;
}



?>
