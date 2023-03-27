<?php

//prepare entry
$password = $_POST['password'];
$uid = $_GET['object'];

  if (strlen($_POST['password']) == 0 || strlen($_POST['password_c']) == 0) {
    $err[] = both_fields_required;
  }
  else {
    if ($_POST['password'] != $_POST['password_c']) {
      $err[] = password_does_not_match;
    }
    else {
      if (!isset($err)) {

        //set the samba password
        $entry['sambantpassword'] = strtoupper(hash('md4',iconv('UTF-8','UTF-16LE',$password)));
        $entry['sambapasswordhistory'] = '0000000000000000000000000000000000000000000000000000000000000000';
        $entry['sambapwdlastset'] = time();
        $entry['sambaacctflags'] = '[U          ]';
        
        //set the password
        $entry['userpassword'] = "{SHA}".base64_encode(pack("H*",sha1($password)));
        $udn=getUserDN($uid);
        $res = ldap_mod_replace($con,$udn,$entry);
        
        if (!$res) {
          $err_des = ldap_error($con);
          $err_num = ldap_errno($con);
          $err[] = 'Error: LDAP '.$err_num .' '. $error_des;
          writeLog('error.log','Error: LDAP '.$err_num .' '. $error_des);
        }
        else {
          $err[] = new_password_ready;
        }
      }
    }
  }

?>
