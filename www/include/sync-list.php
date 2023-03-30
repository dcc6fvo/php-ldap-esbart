<?php

  $con = LDAPconnect();
  $conSync = LDAPSyncConnect();

  $result = ldap_search($con[0],LDAP_SEARCH_DN,"(uid=*)",array('sn','uid', LDAP_USER_EMAIL_ATTR  ,'userpassword','sambantpassword','brpersoncpf'));
  $entries = ldap_get_entries($con[0],$result);  

  for ($i=0; $i< $entries['count']; $i++) {

    if (isset($entries[$i]['mail'][0]))
      $mail = $entries[$i]['mail'][0];
    else
      continue;
      
    $sync = getSyncPersonalData($mail);

    if ( $sync ) {
        writeLog('info.log',$mail." found in sync ldap.");

        if ($entries[$i]['sambantpassword'][0] != $sync[0]['sambantpassword'][0] || $entries[$i]['userpassword'][0] != $sync[0]['userpassword'][0]){

          $entry['sambantpassword'] = $sync[0]['sambantpassword'][0];
          $entry['userpassword'] = $sync[0]['userpassword'][0];
          $entry['sn'] = $sync[0]['sn'][0];
          $entry['brpersoncpf'] = $sync[0]['brpersoncpf'][0];
    
          $res_entry = ldap_mod_replace($con[0],$entries[0]['dn'],$entry);
          
          if ($res_entry) {
            $err[] = $entries[$i]['mail'][0].' '.user_sync_success;
          }
          else{
            $err_num = ldap_errno($con[0]);
            $err[] = 'Error: LDAP '.$err_num;
            writeLog('error.log','Error: LDAP '.$err_num);
          }
        }
    }else{
      writeLog('error.log',$mail." ".user_sync_failure);
      $err[] = user_sync_failure;
      continue;
    }  

  }
    
  printMessages($err);
  ldap_close($con[0]);
  ldap_close($conSync[0]);

?>