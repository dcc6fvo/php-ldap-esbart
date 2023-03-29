<?php

if ($pd = getPersonalDataAttrs($_GET['object'], array('sn','uid','mail','userpassword','sambantpassword','brpersoncpf'))) {

  $con = LDAPconnect();
  $conSync = LDAPSyncConnect();
 
  $mail = $pd[0]['mail'][0]; 

  $sync = getSyncPersonalData($mail);

  if ( $sync ) {
      writeLog('info.log',$mail." found in sync ldap.");
      $entry['sambantpassword'] = $sync[0]['sambantpassword'][0];
      $entry['userpassword'] = $sync[0]['userpassword'][0];
      $entry['sn'] = $sync[0]['sn'][0];
      $entry['brpersoncpf'] = $sync[0]['brpersoncpf'][0];
  
      $res_entry = ldap_mod_replace($con[0],$pd[0]['dn'],$entry);
      if ($res_entry) {
        $err[] = user_sync_success;
      }
      else{
        $err_num = ldap_errno($con[0]);
        $err[] = 'Error: LDAP '.$err_num;
        writeLog('error.log','Error: LDAP '.$err_num);
      }
  }else{
    writeLog('error.log',$mail." ".user_sync_failure);
    $err[] = user_sync_failure;
  }  

  printMessages($err);
  ldap_close($con[0]);
  ldap_close($conSync[0]);
}

?>
