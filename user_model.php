<?php

// we did not introduce node_load in user_model, ignore all node_loads here

class EternaUserModel {
  
  private $current_incognito_id_ = NULL;
  
  function get_user($uid) {
    
    $query = "SELECT u.uid, u.name, u.mail, u.picture, up.points, u.created, f.fbuid, ur.rid FROM users u LEFT JOIN userpoints up ON u.uid=up.uid LEFT JOIN fbconnect_users f ON f.uid=u.uid LEFT JOIN users_roles ur ON ur.uid=u.uid WHERE u.uid=$uid";
    $result = db_query($query);
    $userres = db_fetch_array($result);
    $points = $userres['points'];
    
    if(!$points)
      $points = 0;
      
    $userres['created'] = date("d M Y",$userres['created']);
  
    if(!$userres['picture'] && $fbuid = $userres['fbuid']) {
      $userres['picture'] = "http://graph.facebook.com/$fbuid/picture?type=normal";
    }
  
    $query = "SELECT COUNT(up.uid) FROM userpoints up WHERE up.points > $points";
    $userres['rank'] = db_result(db_query($query)) + 1;
    $userres['is_admin'] = $userres['rid'] == 3;
  
    $query = "SELECT pv.value, pf.title FROM profile_values pv LEFT JOIN profile_fields pf ON pf.fid=pv.fid WHERE pv.uid=$uid AND pf.name='profile_profile'";
    $result = (db_query($query));
    while($res = db_fetch_array($result)) {
      $userres[$res['title']] = $res['value'];    
    } 
    
    $query = "SELECT pv.value, pf.title FROM profile_values pv LEFT JOIN profile_fields pf ON pf.fid=pv.fid WHERE pv.uid=$uid AND pf.title='Last read notification number'";
    $result = (db_query($query));
    while($res = db_fetch_array($result)) {
      $userres[$res['title']] = $res['value'];
    }
    
    $query = "SELECT pv.value, pf.title FROM profile_values pv LEFT JOIN profile_fields pf ON pf.fid=pv.fid WHERE pv.uid=$uid AND pf.title='Mail notification'";
    $result = (db_query($query));
    while($res = db_fetch_array($result)) {
      $userres[$res['title']] = $res['value'];
    }
    return $userres;
  }

  // sets all the new fields to their correct default values for all existing users
  function reset_all_users() {
    // new fields:
    // Puzzle Votes, profile_puzzle_votes, Text
    // Algorithm Votes, profile_algorithm_votes, Text
    $query = "UPDATE profile_values LEFT JOIN profile_fields ON profile_fields.fid = profile_values.fid SET profile_values.value='' WHERE profile_fields.title='Puzzle Votes'";
    $query2 = "UPDATE profile_values LEFT JOIN profile_fields ON profile_fields.fid = profile_values.fid SET profile_values.value='' WHERE profile_fields.title='Algorithm Votes'";
    return db_result(db_query($query)) && db_result(db_query($query2));
  }
  
  function find_users_ids($names){
    $names_arr = Array();      
    $names_arr = explode(",", $names);
      
    $uids_arr = Array();
    foreach($names_arr as $name) {
      if (!empty($name)){      
        $query = "SELECT u.uid FROM users u WHERE UPPER(u.name) = '".mysql_escape_string(strtoupper($name))."'";        
        $result = db_fetch_array(db_query($query));
        if($result['uid'] !== null)  
          array_push($uids_arr, $result['uid']);
        else
          array_push($uids_arr, -100);
      } else {
          array_push($uids_arr, -1);
      }
    }
    
    return $uids_arr;    
  }
  
  function get_users($args) {
      
    $query = "SELECT u.uid, u.name, u.picture, up.points, u.created, f.fbuid FROM users u LEFT JOIN userpoints up ON u.uid=up.uid LEFT JOIN fbconnect_users f ON f.uid=u.uid";
  
    $order = "";
    
    if($args['sort'] == "date") {
      $order = "ORDER BY u.created DESC";
    } else if($args['sort'] == "point") {
      $order = "ORDER BY up.points DESC";
    } else if($args['sort'] == "synthesizes") {
      $order = "ORDER BY (select count(*) from node n, content_type_solution sol where n.uid=u.uid and n.nid=sol.nid and n.type='solution' and n.status <> 0 and n.nid > 17320 and sol.field_solution_synthesis_round_value > 0) desc";
    }
  
    $skip = 0;
    if($args['skip'])
      $skip = $args['skip'];
    $size = 50;
    if($args['size'])
      $size = $args['size'];
    
    
    $limit = "LIMIT $skip, $size"; 
  
    if($args['nolimit']) {
      $limit = "";
    }
    
    $where = "";
  
    if($args['search']) {
      $search = mysql_escape_string(strtoupper($args['search']));
      $where = "WHERE UPPER(u.name) LIKE '%$search%'";
    } 
    
    $full_query = "$query $where $order $limit";
    $result = db_query($full_query);
  
    $users = array();
  
    while($res = db_fetch_array($result)) {
      
      $res['created'] = date("d M Y",$res['created']);
      
      if(!$res['picture'] && $fbuid = $res['fbuid']) {
        $res['picture'] = "http://graph.facebook.com/$fbuid/picture?type=normal";
      }
    
      array_push($users,$res);
    }
    
    return $users;
  }
  
  function get_users_info($args, $solution_model){
    // get list of users with
    // User Id, Points, How long they will be having, Number of designs selected, Number of designs which were found to be correct after fabrication, 
    // Number of votes to successful and selected designs, Total number of designs submitted, Total number of votes.
    $query = "SELECT u.uid, up.points, u.created FROM users u LEFT JOIN userpoints up ON u.uid=up.uid";
  
    $skip = 0;
    if($args['skip'])
      $skip = $args['skip'];
    $size = 50;
    if($args['size'])
      $size = $args['size'];
    
    $limit = "LIMIT $skip, $size"; 
  
    if($args['nolimit']) {
      $limit = "";
    }
    
    $where = "";
    $order = "";
    
    $full_query = "$query $where $order $limit";
    $result = db_query($full_query);
  
    $users = array();
  
    while($res = db_fetch_array($result)) {
      
      $res['created'] = date("d M Y",$res['created']);
      if($res['uid']){
        $uid = $res['uid'];
        $solutions = $solution_model->get_user_synthesized_design($uid);
        $selected_solutions = array();
        $correct_solutions = array();
        for ($ii = 0; $ii < count($solutions); $ii++) {
          if ($solutions[$ii]['score'] > 93)
            array_push($selected_solutions, $solutions[$ii]);
          if ($solutions[$ii]['score'] == 100)
            array_push($correct_solutions, $solutions[$ii]);
        }
        $res['num_selected_design'] = count($selected_solutions);
        $res['selected_design'] = $selected_solutions;
        
        $res['num_correct_design'] = count($correct_solutions);
        $res['correct_design'] = $correct_solutions;
        
        $q = "SELECT node.uid, field_vote_solution_ref_nid AS sid, field_vote_puzzle_ref_nid AS pid, field_synth_score_value AS score FROM content_type_vote LEFT JOIN node ON node.nid=content_type_vote.nid LEFT JOIN content_type_solution ON content_type_vote.field_vote_solution_ref_nid = content_type_solution.nid WHERE node.uid=$uid";
        $votes = array();
        $r = db_query($q);
        while($res_votes = db_fetch_array($r))
          array_push($votes,$res_votes);
        
        $success_votes = array();
        for ($ii = 0; $ii < count($votes); $ii++) {
          if ($votes[$ii]['score'] > 93)
            array_push($success_votes, $votes[$ii]);
        }
        $res['num_success_vote'] = count($success_votes);
        $res['success_vote'] = $success_votes;
        
        //$res['total_votes'] = $votes;
        $res['total_num_votes'] = count($votes);
        $res['total_num_design'] = count($solutions);
      }      
      array_push($users,$res);
    }
    return $users; 
  }
    
  function get_total_users($args) {
      
    $query = "SELECT COUNT(u.uid) FROM users u";
    
    $where = "";
  
    if($args['search']) {
      $search = mysql_escape_string(strtoupper($args['search']));
      $where = " WHERE UPPER(u.name) LIKE '%$search%'";
    } 
    
    $full_query = $query.$where;
    
    return db_result(db_query($full_query));
  }  

  function get_puzzlevotes($uid) {

    $query = "SELECT pv.value, pf.title FROM profile_values pv LEFT JOIN profile_fields pf ON pf.fid=pv.fid WHERE pv.uid=$uid AND pf.title='Puzzle Votes'";
    $result = (db_query($query));
    $userinfo = array();
    if($res = db_fetch_array($result))
    	return $res['value'];
    return null;
  }

  function get_algorithmvotes($uid) {
    $query = "SELECT pv.value, pf.title FROM profile_values pv LEFT JOIN profile_fields pf ON pf.fid=pv.fid WHERE pv.uid=$uid AND pf.title='Algorithm Votes'";
    $result = db_query($query);
    $user = array();
    if($res = db_fetch_array($result))
      return $res['value'];
    return null;    
  }

  function set_puzzlevotes($uid, $newvotes) {
  	$query = "UPDATE profile_values LEFT JOIN profile_fields ON profile_fields.fid = profile_values.fid SET profile_values.value=$newvotes WHERE profile_values.uid=$uid AND profile_fields.title='Puzzle Votes'";
    return db_result(db_query($query));
  }

  function set_algorithmvotes($uid, $newvotes) {
  	$query = "UPDATE profile_values LEFT JOIN profile_fields ON profile_fields.fid = profile_values.fid SET profile_values.value=$newvotes WHERE profile_values.uid=$uid AND profile_fields.title='Algorithm Votes'";
    return db_result(db_query($query));
  }

  function get_userpoints($uid) {
    if($uid) {
      return userpoints_get_current_points($uid);
    } else {
      $id = $this->current_incognito_id_;
      
      if(!$id)
        $id = $_COOKIE['incognito_id'];
      
      if(!$id) {
        return 0;
      }

      $search_params = array();
      $search_params['title'] = $id;
      $search_params['status'] = 1;
      $node = node_load($search_params);    
      $incog = $node->body;
      
      if(!$incog) {
        return 0;
      }
      
      $body = json_decode($incog);
      return $body->points;
    }
  }

  function get_leaderboard_array() {
    $query = "SELECT u.uid, u.name, up.points FROM users u LEFT JOIN userpoints up ON u.uid=up.uid ORDER BY up.points DESC";
    $result = db_query($query);
    
    $rank =0;
    $last_points = -1;
    $tied = 1;
    
    $ranks = array();
    $names = array();
    $points = array();
    $uids = array();
    
    while($res = db_fetch_array($result)) {
      if($res['points'] != $last_points) {
        $last_points = $res['points'];
        $rank += $tied;
        $tied = 1;
      } else {
        $tied += 1;
      }
      
      array_push($ranks,$rank);
      array_push($points,$res['points']);
      array_push($names,$res['name']);
      array_push($uids,$res['uid']);
    }
    
    $final = array();
    $final['names'] = $names;
    $final['ranks'] = $ranks;
    $final['points'] = $points;
    $final['uids'] = $uids;
    
    return $final;
    
  }  
  
  function get_user_around($uid, $points, $leaderboard, $range = 50) {
    
    $names = $leaderboard['names'];
    $userpoints = $leaderboard['points'];
    $ranks = $leaderboard['ranks'];
    $uids  = $leaderboard['uids'];
    
    $N = count($names);
    $richer_filled = false;
    $rank = -1;
    
    $prev = array();
    $next = array();
    
    for($ii=0; $ii<$N; $ii++) {
      
      $current_points = $userpoints[$ii];   
      if($current_points <= $points) {
                        
        if($richer_filled == false ) {  
          $richer_filled = true;  
          $rank = $ranks[$ii];  
            
          for($jj = max(0, $ii-$range); $jj<$ii; $jj++) {
            if($uids[$jj] != $uid) {
              $user = array();
              $user['name'] = $names[$jj];
              $user['points'] = $userpoints[$jj];
              $user['rank'] = $ranks[$jj];
                
              array_push($prev, $user);
            } 
          }
        }
        
        if($uids[$ii] != $uid) {
          $user = array();
          $user['name'] = $names[$ii];  
          $user['points'] = $userpoints[$ii];
          $user['rank'] = $ranks[$ii];
          array_push($next, $user);
        }
        
        if(count($next) >= $range)
          break;  
      }
    }
    
    $res = array();
    $res['richer'] = $prev;
    $res['poorer'] = $next;
    $res['rank'] = $rank;
    $res['points'] = $points;
    
    return $res;  
  }

  function create_incognito_id() {
    $CHARS = "abcdefghijklmnopqrstuvwxyz0123456789";
    $LEN = 10;
    $id = ""; 
    for($ii=0; $ii<$LEN; $ii++) {
      $id .= $CHARS[mt_rand(0, strlen($CHARS))];
    }
    return $id; 
  }
  
  
  function create_incognito() {
    $id = null;
    if($this->current_incognito_id_)
      return false;
    
    if($_COOKIE['incognito_id'])
      return false;
    
    for($ii=0; $ii<20; $ii++) {
      $id = $this->create_incognito_id();
      $query = "SELECT COUNT(nid) FROM node WHERE type='incognito' AND title='$id' WHERE status <> 0";
      if(db_result(db_query($query))== 0)
        break;
      $id = null;
    }
    
    if($id == null) 
      return false;
    
    $node = new stdClass();
    $node->title = $id;
    $node->type = "incognito";
    $node->created = time();
    $node->status = 1;
    
    $body = array();
    $body['points'] = 0;
    $body['cleared_puzzles'] = array();
    $body['cleared_args'] = array();
    
    $brief_body = array();
    $brief_body['points'] = 0;
    $brief_body['cleared_puzzles'] = array();
    
    $body_text = json_encode($body);
    $brief_body_text = json_encode($brief_body);
    
    $node->body = $body_text;
    
    node_save($node);
    $expiration = time() + 86400 * 30;
    setcookie("incognito_id", $id, $expiration, "/");
    setcookie("incognito_body", $brief_body_text, $expiration, "/");

    $this->current_incognito_id_ = $id;
  
    return $id;
  }
  
  function incognito_solve_puzzle($puznid, $points, $args) {
 
    $id = $this->current_incognito_id_;
    
    if(!$id)
      $id = $_COOKIE['incognito_id'];
    
    if(!$id)
      return false;
    
    $search_params = array();
    $search_params['title'] = $id;
    $search_params['status'] = 1;
    $node = node_load($search_params);
    
    if(!$node)
      return false;
    
    $body = json_decode($node->body);
    
    $cleared_puzzles = $body->cleared_puzzles;
    $cleared_args = $body->cleared_args;
    
    for($ii=0; $ii<count($cleared_puzzles); $ii++){
      if($cleared_puzzles[$ii] == $puznid)
        return true;
    }
    
    array_push($cleared_puzzles, $puznid);
    array_push($cleared_args,$args);
  
    $body->cleared_puzzles = $cleared_puzzles;
    $body->cleared_args = $cleared_args;
    $body->points += $points;
    $body_text = json_encode($body);
    $node->body = $body_text;
    
    node_save($node);
  
    $brief_body = array();
    $brief_body['cleared_puzzles'] = $cleared_puzzles;
    $brief_body['points'] = $body->points;
    $brief_body_text = json_encode($brief_body);
    
    $expiration = time() + 86400 * 30;
    setcookie("incognito_id", $id, $expiration, "/");
    setcookie("incognito_body", $brief_body_text, $expiration, "/");
    return true;
  }
  
  function delete_incognito() {
    
    $id = $this->current_incognito_id_;
    
    if(!$id)
      $id = $_COOKIE['incognito_id'];
    
    if(!$id)
      return false;
    
    $search_params = array();
    $search_params['title'] = $id;
    $search_params['status'] = 1;
    $node = node_load($search_params);
    
    if(!$node)
      return false;
  
    setcookie ("incognito_id", "", time() - 3600, "/");
    setcookie ("incognito_body", "", time() - 3600, "/");
  
    $node->status = 0;
    node_save($node);
  
    $this->current_incognito_id_ = null;
  
    return true;  
  }
  
  function process_incognito($uid, $solution_model) {
    global $incog_cache;
    
    if (!$solution_model) {
      eterna_utils_log_error("Cannot process incognito : solution model not found");
    }
    
    $id = $incog_cache['incognito_id'];
    
    if(!$id)
      $id = $_COOKIE['incognito_id'];
    
    if(!$id || !$uid)
      return false;
    
    
    $points = $this->get_userpoints($uid);
    if ($points > 0)
      return false;
    
    $search_params = array();
    $search_params['title'] = $id;
    $search_params['status'] = 1;
    $node = node_load($search_params);
  
    setcookie ("incognito_id", "", time() - 3600, "/");
    setcookie ("incognito_body", "", time() - 3600, "/");
    $incog_cache = null;
    
    if(!$node)
      return false;
  
    $body = json_decode($node->body);
    $cleared_args = $body->cleared_args;
  
    $node->status = 0;
    node_save($node);
    
    for($ii=0; $ii<count($cleared_args); $ii++) {
      $args = (array) $cleared_args[$ii];
      $solution_model->post_solution($args,$uid);
    }
  
    return true;
  }

  
}



/**

define(ETERNA_USER_NUM_USER_PER_PAGE,50);


function eterna_user_get_page_count($args) {

	$where = "";

	if($args['search']) {
		$search = mysql_escape_string(strtoupper($args['search']));
		$where = "WHERE u.name LIKE '%$search%'";
	}	
	
	return ceil(db_result(db_query("SELECT COUNT(u.uid) FROM users u $where")) / ETERNA_USER_NUM_USER_PER_PAGE);	
}




function eterna_user_get_cleared_ids($uid) {
	$query = "SELECT node.nid FROM node node INNER JOIN flag_content flag_content_node ON node.nid = flag_content_node.content_id INNER JOIN content_type_puzzle puz ON node.nid = puz.nid WHERE node.status <> 0 AND flag_content_node.fid = 3 AND flag_content_node.content_type = \"node\" AND flag_content_node.uid = $uid AND puz.field_puzzle_type_value != \"Experimental\"";
	$result = db_query($query);
	
	$ids = array();
	
	while($res = db_fetch_array($result)) {
		array_push($ids,$res['nid']);
	}
	
	return $ids;
}





function eterna_user_create_incognito_id() {
	$CHARS = "abcdefghijklmnopqrstuvwxyz0123456789";
	$LEN = 10;
	$id = "";	
	for($ii=0; $ii<$LEN; $ii++) {
         $id .= $CHARS[mt_rand(0, strlen($CHARS))];
    }
    return $id;	
}


function eterna_user_create_incognito() {
	$id = null;
	
	global $incog_cache;
	
	if($incog_cache['incognito_id'])
		return false;
	
	if($_COOKIE['incognito_id'])
		return false;
	
	for($ii=0; $ii<20; $ii++) {
		$id = eterna_user_create_incognito_id();
		$query = "SELECT COUNT(nid) FROM node WHERE type='incognito' AND title='$id' WHERE status <> 0";
		if(db_result(db_query($query))== 0)
			break;
		$id = null;
	}
	
	if($id == null) 
		return false;
	
	$node = new stdClass();
	$node->title = $id;
	$node->type = "incognito";
	$node->created = time();
	$node->status = 1;
	
	$body = array();
	$body['points'] = 0;
	$body['cleared_puzzles'] = array();
	$body['cleared_args'] = array();
	
	$brief_body = array();
	$brief_body['points'] = 0;
	$brief_body['cleared_puzzles'] = array();
	
	$body_text = json_encode($body);
	$brief_body_text = json_encode($brief_body);
	
	$node->body = $body_text;
	
	node_save($node);
	$expiration = time() + 86400 * 30;
	setcookie("incognito_id", $id, $expiration, "/");
	setcookie("incognito_body", $brief_body_text, $expiration, "/");
	
	if(!$incog_cache)
		$incog_cache = array();

	$incog_cache['incognito_id'] = $id;

	return $id;
}

function eterna_user_incognito_solve_puzzle($puznid, $points, $args) {
	
	global $incog_cache;
	
	$id = $incog_cache['incognito_id'];
	
	if(!$id)
		$id = $_COOKIE['incognito_id'];
	
	if(!$id)
		return false;
	
	$search_params = array();
	$search_params['title'] = $id;
	$search_params['status'] = 1;
	$node = node_load($search_params);
	
	if(!$node)
		return false;
	
	$body = json_decode($node->body);
	
	$cleared_puzzles = $body->cleared_puzzles;
	$cleared_args = $body->cleared_args;
	
	for($ii=0; $ii<count($cleared_puzzles); $ii++){
		if($cleared_puzzles[$ii] == $puznid)
			return true;
	}
	
	array_push($cleared_puzzles, $puznid);
	array_push($cleared_args,$args);

	$body->cleared_puzzles = $cleared_puzzles;
	$body->cleared_args = $cleared_args;
	$body->points += $points;
	$body_text = json_encode($body);
	$node->body = $body_text;
	
	node_save($node);

	$brief_body = array();
	$brief_body['cleared_puzzles'] = $cleared_puzzles;
	$brief_body['points'] = $body->points;
	$brief_body_text = json_encode($brief_body);
	
	$expiration = time() + 86400 * 30;
	setcookie("incognito_id", $id, $expiration, "/");
	setcookie("incognito_body", $brief_body_text, $expiration, "/");
	return true;
}

function eterna_user_delete_incognito() {
	global $incog_cache;
	
	$id = $incog_cache['incognito_id'];
	
	if(!$id)
		$id = $_COOKIE['incognito_id'];
	
	if(!$id)
		return false;
	
	$search_params = array();
	$search_params['title'] = $id;
	$search_params['status'] = 1;
	$node = node_load($search_params);
	
	if(!$node)
		return false;

	setcookie ("incognito_id", "", time() - 3600);
	setcookie ("incognito_body", "", time() - 3600);

	$node->status = 0;
	node_save($node);

	$incog_cache = null;

	return true;	
}



**/


?>
