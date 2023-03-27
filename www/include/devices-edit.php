<?php

if (isset($_POST["groups"]))
  $groups=$_POST["groups"];
else
  $groups=array('');
$uid=$_GET['object'];
$udn=getUserDN($uid);

if (!isset($err)) {
  //prepare entry
  foreach($_POST as $key => $value){
    if(!is_array($value))
      $entry[$key] = trim($value);
  }
  unset($entry['submit']);
  
  //add entry
  $res_entry = ldap_mod_replace($con,$udn,$entry);
      
  //edit memberships
  if (isset($groups)) {
    //get the groups the user is a member of
    $current_groups = getUserMembership($uid);
    //obtain the new groups to assign (the ones missing in the latest assignation)
    $new_groups = array_diff($groups,$current_groups);
    //add the memberships
    addUserToGroups($uid,$new_groups);
          
    //get the groups the user is not a member of (the ones missing in the latest assignation)
    $foreign_groups = array_diff(getAssignableGroups(),$groups);
    //get the groups the user must be removed from (the ones missing in the latest assignation
    //but present in the expired membership)
    $remaining_groups = array_intersect($foreign_groups,$current_groups);
    //remove de memberships
    removeUserFromGroups($uid,$remaining_groups);
  }
        
  if ($res_entry) {
    $err[] = user_edit_success;
  }
  else {
    $err[] = a_problem_occurred;
  }
}

?>
