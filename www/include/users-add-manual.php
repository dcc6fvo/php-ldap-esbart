<?php

if (strlen(trim($_POST['name_1'])) == 0 || strlen(trim($_POST['name_2'])) == 0 || strlen(trim($_POST['email'])) == 0) {
  $err[] = user_add_assisted_name_surname_email_required;
}
else {
  if (!filter_var(trim($_POST['email']),FILTER_VALIDATE_EMAIL)) {
    $err[] = verify_email;
  }
  else {
    $name_1 = trim($_POST['name_1']);
    $name_2 = trim($_POST['name_2']);
    $login = trim($_POST['login']);
    $mail = trim($_POST['email']);
    $phone = trim($_POST['telephoneNumber']);
    $ou=$_POST["ou"];
        
    if (getPersonalData($login)) {
      $err[] = user_already_exists;
    }
    if (!isset($err)) {
      //prepare entry
      $uidnumber = getNextId($con,'user');
      
      $user_oclass_array=preg_split ("/\,/", LDAP_USER_OBJ_CLASSES);
      $user_attr_array=preg_split ("/\,/", LDAP_USER_ATTRS);

      foreach($user_oclass_array as $value){
        $entry['objectclass'][] = $value;
      }
      $entry['cn'] = $name_1.' '.$name_2;
      $entry['sn'] = $name_2;
      $entry['uid'] = $login;

      if (in_array("posixAccount", $user_oclass_array)){
        $entry['uidnumber'] = $uidnumber;
        $entry['gidnumber'] = LDAP_PRIMARY_GROUP_ID;
      }

      if ( in_array("givenname", $user_attr_array) && isset($name_1) )
        $entry['givenname'] = $name_1;

      if (in_array("homedirectory", $user_attr_array) && isset($name_1) && isset($name_2) )
        $entry['homedirectory'] = '/home/'.$name_1.'.'.$name_2;
      
      if (in_array("loginshell", $user_attr_array))
        $entry['loginshell'] = '/bin/false';
      
      if (in_array("mail", $user_attr_array) && isset( $mail ) )
        $entry['mail'] = $mail;

      if (in_array("telephoneNumber", $user_attr_array) && isset( $phone ) )
        $entry['telephoneNumber'] = $phone;

      /* RADIUS ATTR */

      if (in_array("radiusSimultaneousUse", $user_attr_array) && in_array("radiusprofile", $user_oclass_array) && isset( $_POST['radiusSimultaneousUse'] ) )
        $entry['radiusSimultaneousUse'] = $_POST['radiusSimultaneousUse'];
      
      if (in_array("radiusTunnelPrivateGroupId", $user_attr_array) && in_array("radiusprofile", $user_oclass_array) && isset( $_POST['radiusTunnelPrivateGroupId'] ) )
        $entry['radiusTunnelPrivateGroupId'] = $_POST['radiusTunnelPrivateGroupId'];
      
      if (in_array("radiusExpiration", $user_attr_array) && in_array("radiusprofile", $user_oclass_array) && isset( $_POST['radiusExpiration'] ) )
        $entry['radiusExpiration'] = $_POST['radiusExpiration'];
      
      if (in_array("radiusprofile", $user_oclass_array)){
        $entry['radiusTunnelType'] = '13';
        $entry['radiusTunnelMediumType'] = '6';
      }

      if (in_array("brPersonCpf", $user_attr_array) && in_array("brPerson", $user_oclass_array) && isset( $_POST['brPersonCpf'] ) ){
        $entry['brPersonCpf'] = $_POST['brPersonCpf'];
        $password = mb_substr($entry['brPersonCpf'], 0, 4);
      }
      else{
        $password = '1234';
      }
      //set the samba password
      $entry['sambantpassword'] = strtoupper(hash('md4',iconv('UTF-8','UTF-16LE',$password)));
      $entry['sambapasswordhistory'] = '0000000000000000000000000000000000000000000000000000000000000000';
      $entry['sambapwdlastset'] = time();
      $entry['sambaacctflags'] = '[U          ]';
      
      //set the password
      $entry['userpassword'] = "{SHA}".base64_encode(pack("H*",sha1($password)));
    
      /*if (in_array("sambasid", $user_attr_array) )
        $entry['sambasid'] = getSambaSID($uidnumber);
      */

      if (in_array("sambaSamAccount", $user_oclass_array) )
        $entry['sambasid'] = LDAP_SAMBA_SID;
      
      $exists = userExistSyncDB($entry['mail']);
      if ( $exists ) {
          $sync = getSyncPersonalData($entry['mail']);
          $entry['sambantpassword'] = $sync[0]['sambantpassword'][0];
          $entry['userpassword'] = $sync[0]['userpassword'][0];
          $entry['sn'] = $sync[0]['sn'][0];
          $entry['brpersoncpf'] = $sync[0]['brpersoncpf'][0];
          writeLog('info.log',"$email ".user_sync_success);
      }else{
        writeLog('info.log',"email not found in sync ldap.");
      }

      //add entry
      $res_entry = ldap_add($con,'uid='.$login.",".$ou,$entry);
            
      //add memberships
      if (isset($_POST["groups"]))
        addUserToGroups($login,$_POST["groups"]);
        
      if ($res_entry) {
        $err[] = user_add_success;
        /*if (sendOneTimeSetPasswordEmail($login,false)) {
          $err[] = user_add_welcome_email_sent." ".trim($name_1);
        }
        else {
          $err[] = user_add_welcome_email_not_sent;
        }*/
        $err[] = user_add_welcome_email_not_sent;
      }
      else {
        writeLog('error.log',ldap_error($con));
        ldap_get_option($con, LDAP_OPT_DIAGNOSTIC_MESSAGE, $err);
        writeLog('error.log',$err);
        $err[] = a_problem_occurred;
      }
    }
  }
}

?>
