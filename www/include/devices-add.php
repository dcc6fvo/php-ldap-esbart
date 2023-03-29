<?php

if (strlen(trim($_POST['name_1'])) == 0 || strlen(trim($_POST['login'])) == 0) {
  $err[] = device_add_name_login_required;
}
else {
    console($_POST);
    console($_POST['macaddress']);
    $ou = LDAP_DEVICES_DN; 
    $name_1 = trim($_POST['name_1']);
    $login = trim($_POST['login']);
        
    if (getPersonalData($login)) {
      $err[] = user_already_exists;
    }
    if (!isset($err)) {
      
      $user_oclass_array=preg_split ("/\,/", LDAP_DEVICES_OBJ_CLASSES);
      $user_attr_array=preg_split ("/\,/", LDAP_DEVICES_ATTRS);

      foreach($user_oclass_array as $value){
        $entry['objectclass'][] = $value;
      }
      $entry['sn'] = $name_1;
      $entry['cn'] = $login;
      $entry['uid'] = $login;

      if (isset( $_POST['macaddress'] ) ){
        $entry['macaddress'] = $_POST['macaddress'];
      }

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

      if ( isset( $_POST['password'] ) ){
        $password = $_POST['password'] ;
      }
      else{
        $password = '1234';
      }

      /* SAMBA ATTR */

      //set the samba password
      $entry['sambantpassword'] = strtoupper(hash('md4',iconv('UTF-8','UTF-16LE',$password)));
      $entry['sambapasswordhistory'] = '0000000000000000000000000000000000000000000000000000000000000000';
      $entry['sambapwdlastset'] = time();
      $entry['sambaacctflags'] = '[U          ]';
      
      //set the password
      $entry['userpassword'] = "{SHA}".base64_encode(pack("H*",sha1($password)));

      if (in_array("sambaSamAccount", $user_oclass_array) )
        $entry['sambasid'] = LDAP_SAMBA_SID;
      
      //add entry
      $res_entry = ldap_add($con,'uid='.$login.",".$ou,$entry);
                  
      if ($res_entry) {
        $err[] = user_add_success;
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

?>
