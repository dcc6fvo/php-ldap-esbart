<?php

  $uid=$_GET['object'];
  $edn=getEntryDN($uid);

  if (!isset($err)) {
  
    //prepare entry
    foreach($_POST as $key => $value){
      if(!is_array($value))
        $entry[$key] = trim($value);
    }
    unset($entry['submit']);
    
    $entry['uid'] = $uid;
    $entry['cn'] = $uid;

    //add entry
    $res_entry = ldap_mod_replace($con,$edn,$entry);
          
    if ($res_entry) {
      $err[] = user_edit_success;
    }
    else {
      writeLog('error.log',ldap_error($con));
      ldap_get_option($con, LDAP_OPT_DIAGNOSTIC_MESSAGE, $err);
      writeLog('error.log',$err);
      $err[] = a_problem_occurred;
    }
}

?>
