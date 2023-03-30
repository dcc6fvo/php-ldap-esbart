<?php

if (strlen($_POST['name']) == 0) {
  $err[] = name_cannot_be_empty;
}
else {
  //prepare entry
  $entry['objectclass'] = 'groupOfNames';
  $entry['cn'] = $_POST['name'];
  $entry['member'] = '';
      
  //add entry
  $res_entry = @ldap_add($con,'cn='.trim($_POST['name']).",".LDAP_GROUPS_DN,$entry);
      
  if ($res_entry) {
    $err[] = group_add_success;
  }
  else {
    if (ldap_errno($con) == 68) {
      $err[] = group_already_exists;
    }
    else {
      $err[] = a_problem_occurred;
      writeLog('error.log',ldap_error($con));
      writeLog('error.log',$err);
    }
  }
}

?>
