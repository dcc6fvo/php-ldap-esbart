<?php

class DB extends PDO {
  public function __construct() {
    $dsn = "mysql:host=".DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME;
    parent::__construct($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
  }
}

function LDAPconnect() {
  $con = ldap_connect(LDAP_HOST);
  ldap_set_option($con,LDAP_OPT_PROTOCOL_VERSION,3);
  if ($con) {
    $bind = ldap_bind($con,LDAP_USER,LDAP_PASS);
  }
  return array($con,$bind);
}

function LDAPSyncConnect() {
  $con = ldap_connect(LDAP_SYNC_HOST);
  ldap_set_option($con,LDAP_OPT_PROTOCOL_VERSION,3);
  if ($con) {
    $bind = ldap_bind($con,LDAP_SYNC_USER,LDAP_SYNC_PASS);
  }
  return array($con,$bind);
}

function printMessages($err) {
  //displays HTML form error messages
  if (count($err)) {
    echo '      <div id="messages">'."\n";
    echo '        '.implode('<br />',$err)."\n";
    echo "      </div>\n";
  }
}
  
function writeLog($filename,$message) {
  if (LOGGING) {
    $dir = 'log/';
    if (!is_dir($dir)) {
      mkdir($dir);
    }
    $file = fopen($dir.$filename,'a');
    $date = new DateTime(null, new DateTimeZone('UTC'));
    $data = $date->format('D M d H:i:s e Y').' '.$_SERVER['REMOTE_ADDR']." $message\n";
    fwrite($file,$data);
    fclose($file);
  }
}

function sortByName($a,$b) {
  return strtolower($a['cn'][0]) > strtolower($b['cn'][0]);
}

function getNextId($con,$entity) {

  $user_oclass_array=preg_split ("/\,/", LDAP_USER_OBJ_CLASSES);

  //returns next available ID for either users or groups
  if ($entity == 'user') {
    $attr = 'uidnumber';

    //If not working with posixAccount, return 0;
    if (!in_array("posixAccount", $user_oclass_array)){
      return 0;
    }
  }
  else if ($entity == 'group') {
    $attr = 'gidnumber';
  }
  $result = ldap_search($con,"ou=$entity"."s,".LDAP_TREE,"(cn=*)",array($attr));
  $entries = ldap_get_entries($con,$result);
  $numbers = array();
  for ($i = 0; $i < $entries['count']; $i++) {
    $numbers[] = $entries[$i][$attr][0];
  }
  if (count($numbers) > 0) {
    return max($numbers) + 1;
  }
  else {
    //not likely to happen, but using the same ID schema across entities
    return LDAP_PRIMARY_GROUP_ID + 1;
  }
}

function getSambaSID($uidnumber) {
  $samba_id = $uidnumber * 2 + 1000;
  return LDAP_SAMBA_SID.'-'.$samba_id;
}

function userExistSyncDB($mail) {

  if(!isset($mail) || empty($mail))
    return false;

  $con = LDAPSyncConnect();
  $res = ldap_search($con[0], LDAP_SYNC_SEARCH_DN, "(mail=$mail)");
  $first = ldap_first_entry($con[0], $res);
  if (!$first)
    return false;
  else{
    writeLog('info.log',$mail.' '.$first);
    return true;
  }
}

function getSyncPersonalData($mail) {

  $con = LDAPSyncConnect();
  $res = ldap_search($con[0], LDAP_SYNC_SEARCH_DN, "(mail=$mail)");
  $first = ldap_first_entry($con[0], $res);
  if (!$first)
    return null;
  $udn = ldap_get_dn($con[0], $first);
  if (!$udn){
    writeLog('error.log',user_dn_not_found);
    return null;
  }

  $user_sync_attr_array=preg_split ("/\,/", LDAP_SYNC_USER_ATTRS);
  $result = @ldap_read($con[0],$udn,"(cn=*)",$user_sync_attr_array); 
  $user_attr_not_found[] = null;

  if ($result) {
    $entries = ldap_get_entries($con[0],$result);

    if ($entries){
      return $entries;
    }
    else{
      ldap_close($con[0]);
      return null;
    }
  }
  else {
    ldap_close($con[0]);
    return false;
  }
}

function getPersonalData($uid) {
  /*
   * returns an array with the attributes
   * defined in LDAP_USER_ATTRS var from config.php 
   * 
   * returns false on failure, or two arrays: the first
   * containing the attributes and the second array
   * containing its values
   */
  $con = LDAPconnect();
  $udn=getUserDN($uid);
  
  if($udn == null)
    return false;
  
  $user_attr_array=preg_split ("/\,/", LDAP_USER_ATTRS);
  $result = @ldap_read($con[0],$udn,"(cn=*)",$user_attr_array); 
  $user_attr_not_found[] = null;

  if ($result) {
    $entries = ldap_get_entries($con[0],$result);

    if ($entries){
      return $entries;
    }
    else{
      ldap_close($con[0]);
      return null;
    }
  }
  else {
    ldap_close($con[0]);
    return false;
  }
}

function getPersonalDataAttrs($uid,$attr_array) {
  /*
   * returns an array with the attributes
   * passed by parameter
   * 
   * returns false on failure, or two arrays: the first
   * containing the attributes and the second array
   * containing its values
   */
  $con = LDAPconnect();
  $udn=getUserDN($uid);
  
  if($udn == null)
    return false;
  
  $user_attr_array=preg_split ("/\,/", LDAP_USER_ATTRS);
  $result = @ldap_read($con[0],$udn,"(cn=*)",$attr_array); 
  $user_attr_not_found[] = null;

  if ($result) {
    $entries = ldap_get_entries($con[0],$result);

    if ($entries){
      return $entries;
    }
    else{
      ldap_close($con[0]);
      return null;
    }
  }
  else {
    ldap_close($con[0]);
    return false;
  }
}

function getPersonalDataAllAttrs($uid) {
  /*
   * returns an array with all the attrs
   * from an entry
   * 
   * returns false on failure, or two arrays: the first
   * containing the attributes and the second array
   * containing its values
   */
  $con = LDAPconnect();
  $udn=getUserDN($uid);
  
  if($udn == null)
    return false;
  
  $user_attr_array=preg_split ("/\,/", LDAP_USER_ATTRS);
  $result = @ldap_read($con[0],$udn,"(cn=*)"); 
  $user_attr_not_found[] = null;

  if ($result) {
    $entries = ldap_get_entries($con[0],$result);

    if ($entries){
      return $entries;
    }
    else{
      ldap_close($con[0]);
      return null;
    }
  }
  else {
    ldap_close($con[0]);
    return false;
  }
}

function console($obj){
    $js = json_encode($obj);
    print_r('<script>console.log('.$js.')</script>');
}

function accountHasEmail($uid) {
  $con = LDAPconnect();
  $udn=getUserDN($uid);
  $result = ldap_read($con[0],$udn,"(cn=*)", array(LDAP_USER_EMAIL_ATTR ));
  $entries = ldap_get_entries($con[0],$result);
  ldap_close($con[0]);
  if(isset($entries[0][LDAP_USER_EMAIL_ATTR][0])){
    if (strlen($entries[0][LDAP_USER_EMAIL_ATTR][0]) > 0) {
      return true;
    } 
    else {
      writeLog('info.log',"uid=$uid,".LDAP_SEARCH_DN." has no email");
      return false;
    }
  }  
  else {
    writeLog('info.log',"uid=$uid,".LDAP_SEARCH_DN." has no email");
    return false;
  }
}

function getUserDN($user){
  $con = LDAPconnect();
  $res = ldap_search($con[0], LDAP_SEARCH_DN, "(uid=$user)");
  $first = ldap_first_entry($con[0], $res);
  if (!$first)
    return null;
  $data = ldap_get_dn($con[0], $first);
  if ($data){
    return $data;
  }  
  else{
    writeLog('login-error.log',user_dn_not_found);
    return null;
  }
}

function accountIsEnabled($uid) {
  //an account is considered active if either userpassword or sambantpassword are set
  $con = LDAPconnect();
  $udn=getUserDN($uid);
  $result = ldap_read($con[0],$udn,"(cn=*)",array('userpassword','sambantpassword'));
  $entries = ldap_get_entries($con[0],$result);
  ldap_close($con[0]);
  if (isset($entries[0]['userpassword'][0]) || isset($entries[0]['sambantpassword'][0])) {
    writeLog('info.log',$uid.' account enabled');
    return true;
  }
  else if (!isset($entries[0]['userpassword'][0]) && !isset($entries[0]['sambantpassword'][0])){
    writeLog('info.log',$uid.' account disabled');
    return false;
  }
}

function disableAccount($uid) {
  //removes userpassword and/or sambantpassword attributes
  if (getPersonalData($uid)) {
    $con = LDAPconnect();
    $udn=getUserDN($uid);
    $result = ldap_read($con[0],$udn,"(cn=*)",array('userpassword','sambantpassword'));
    $entries = ldap_get_entries($con[0],$result);
    if (isset($entries[0]['userpassword'][0])) {
      ldap_mod_del($con[0],"uid=$uid,".LDAP_SEARCH_DN,array('userpassword' => array()));
    }
    if (isset($entries[0]['sambantpassword'][0])) {
      ldap_mod_del($con[0],"uid=$uid,".LDAP_SEARCH_DN,array('sambantpassword' => array()));
    }
    ldap_close($con[0]);
    return true;
  }
  else {
    return false;
  }
}

function canChangeUserPassword($uid){

  $udn=getUserDN($uid);

  if ( strpos($udn, LDAP_NOPASSWD_CHANGE_OU) !== false) {
    $err[] = 'Error: '.$uid.' '.unable_change_pass_sync;
    writeLog('login-error.log','Error: '.$uid.' '.unable_change_pass_sync);
    return false;
  }else{
    return true;
  }
}

function addUserToGroups($uid,$groups) {
  //expects an array of group names
  $con = LDAPconnect();
  $udn=getUserDN($uid);

  # The attributes member and uniqueMember contain the DN
  if (LDAP_GROUP_ATTR == 'memberuid') {
    $entry[LDAP_GROUP_ATTR] = $uid;
  }
  else {
    $entry[LDAP_GROUP_ATTR] = $udn;
  }
  
  $fail = false;
  foreach ($groups as $group) {
    //the select html tag may send an empty string
    if (strlen($group) > 0) {
      $result = @ldap_mod_add($con[0],"cn=$group,".LDAP_GROUPS_DN,$entry);
        if (!$result) {
          $fail = true;
      }
    }
  }
  ldap_close($con[0]);
  return !$fail;
}

function removeUser($uid) {

  $con = LDAPconnect();
  $udn=getUserDN($uid);

  $result = ldap_delete($con[0],$udn);
  if($result){
    writeLog('info.log',$uid.' '.removed_user_success);
  }
  else {
    writeLog('error.log',ldap_error($con));
    ldap_get_option($con, LDAP_OPT_DIAGNOSTIC_MESSAGE, $err);
    writeLog('error.log',$err);
    $err[] = removed_user_failed. ' '.$uid;
  }
  ldap_close($con[0]);
  return $result;  
}

function removeUserFromGroups($uid,$groups) {
  //expects an array of group names
  $con = LDAPconnect();
  $udn=getUserDN($uid);

  # The attributes member and uniqueMember contain the DN
  if (LDAP_GROUP_ATTR == 'memberuid') {
    $entry[LDAP_GROUP_ATTR] = $uid;
  }
  else {
    $entry[LDAP_GROUP_ATTR] = $udn;
  }
  
  $fail = false;
  foreach ($groups as $group) {
    $result = ldap_mod_del($con[0],"cn=$group,".LDAP_GROUPS_DN,$entry);
    if (!$result) {
      $fail = true;
    }
  }
  ldap_close($con[0]);
  return !$fail;
}

function getOrganizationalUnits(){
  $con = LDAPconnect();
  $filter="(objectClass=organizationalunit)"; 
  $dn = LDAP_TREE; 
  $justthese = array('dn', 'ou'); 
  $result=ldap_search($con[0], $dn, $filter, $justthese); 
  $entries = ldap_get_entries($con[0],$result);
  ldap_close($con[0]);
  $ous[]=null;
  if($entries){
    for ($i=0; $i < $entries['count']; $i++) { 
      $ous[$i]['dn']=$entries[$i]['dn'];
      $ous[$i]['ou']=$entries[$i]['ou']; 
    }
    ldap_close($con[0]);
    return $ous;
  }
  else {
    writeLog('login-error.log',ldap_error($con));
    ldap_get_option($con, LDAP_OPT_DIAGNOSTIC_MESSAGE, $err);
    writeLog('login-error.log',$err);
    $err[] = cant_get_ous;
    ldap_close($con[0]);
    return null;
  }
}

function getAssignableGroups() {
  //returns an array of group names
  $con = LDAPconnect();
  $result = ldap_search($con[0],LDAP_GROUPS_DN,"(cn=*)",array('cn'));
  $entries = ldap_get_entries($con[0],$result);
  ldap_close($con[0]);
  $groups = array();
  for ($i = 0; $i < $entries['count']; $i++) {
    if (array_search($entries[$i]['cn'][0],explode(',',LDAP_GROUP_EXCLUSIONS)) === false) {
      $groups[] = $entries[$i]['cn'][0];
    }
  }
  return $groups;
}

function getGroupMembers($cn) {
  //returns an array of uids
  $con = LDAPconnect();
  $result = ldap_search($con[0],"cn=$cn,".LDAP_GROUPS_DN,"(cn=*)",array(LDAP_GROUP_ATTR));
  $entries = ldap_get_entries($con[0],$result);
  ldap_close($con[0]);
  $members = array();
  if (isset($entries[0][LDAP_GROUP_ATTR])) {
    for ($i = 0; $i < $entries[0][LDAP_GROUP_ATTR]['count']; $i++) {
      $members[] = $entries[0][LDAP_GROUP_ATTR][$i];
    }
  }
  return $members;
}

function getUserMembership($uid) {
  //returns an array of group names
  $con = LDAPconnect();
  $udn=getUserDN($uid);
  $result = ldap_search($con[0],LDAP_GROUPS_DN,"(cn=*)",array('cn',LDAP_GROUP_ATTR));
  $entries = ldap_get_entries($con[0],$result);
  ldap_close($con[0]); 

  # The attributes member and uniqueMember contain the DN
  if (LDAP_GROUP_ATTR == 'memberuid') {
    $ident = $uid;
  }
  else {
    $ident = $udn;
  }

  $groups = array();
  for ($i = 0; $i < $entries['count']; $i++) {
    if (isset($entries[$i][LDAP_GROUP_ATTR])) {
      for ($j = 0; $j < $entries[$i][LDAP_GROUP_ATTR]['count']; $j++) {
        if ($entries[$i][LDAP_GROUP_ATTR][$j] == $ident) {
          $groups[] = $entries[$i]['cn'][0];
          break;
        }
      }
    }
  }
  return $groups;
}

function trimSplitFormatName($name) {
  /* 
   * some european naming customs (like the catalan one) may include composite two-word
   * names or even composite surnames with a preposition in front of them. We'll select
   * only one word to form up the username.
   */
  if (strpos($name,' ') !== false) {
    $name_a = explode(' ',trim($name));
    //special characters are transliterated to ASCII
    //any other non alphabetic character is replaced by an underscore
    return preg_replace('/[^a-zA-Z]/','_',strtolower(iconv('UTF-8','ASCII//TRANSLIT',$name_a[0])));
  }
  else {
    return preg_replace('/[^a-zA-Z]/','_',strtolower(iconv('UTF-8','ASCII//TRANSLIT',trim($name))));
  } 
}

function trimSplitFormatSurname($name) {
  if (strpos($name,' ') !== false) {
    $name_a = explode(' ',trim($name));
    return preg_replace('/[^a-zA-Z]/','_',strtolower(iconv('UTF-8','ASCII//TRANSLIT',mb_substr($name_a[1],0,1))));
  }
  else {
    return preg_replace('/[^a-zA-Z]/','_',strtolower(iconv('UTF-8','ASCII//TRANSLIT',mb_substr(trim($name),0,1))));
  } 
}

function sendOneTimeSetPasswordEmail($uid,$manual) {
  /*
   * sends an email with a link to reset the password
   * if 'manual' GET parameter is set, the welcome email template is not used
   * and the user is presented with a generic password reset email instead
   * 
   * returns false on failure
   */
  if ($pd = getPersonalData($uid)) {
    $headers[] = 'From: '.FROM_NAME.' <'.FROM_ADDR.'>';
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=utf-8';
    $headers[] = 'X-Mailer: '.TITLE;
    $headers[] = 'X-Mailer: PHP/'.phpversion();
    $headers[] = 'X-PHP-Originating-Script: '.TITLE;
  
    //create a random string token
    $pass = bin2hex(openssl_random_pseudo_bytes(8));
    $url = URL."/set.php?p=$pass";
  
    if ($manual) {

      console($pd);

      $url = $url."&man";
      $subject = reset_password;
      $message = "<html><p>".ucfirst(greeting).' '.$pd[1][0]."</p>
      <p>".somebody_offered_reset_link."</p>
      <p><a href=\"$url\">$url</a></p>
      <p>".if_unsolicited_ignore."</p>
      <p>".thank_you."</p>
      <img src=\"".URL."/".FOOTER_IMAGE_P."\" alt=\"".FROM_NAME."\" />
      <p style=\"font-size: 12px;\"><a href=\"".FOOTER_PRIVACY_POLICY_URL."\">".FROM_NAME." - ".privacy_policy."</a></p>
      </html>";
  }
  else {
      $subject = welcome_to." ".FROM_NAME;
      $message = "<html><p>".ucfirst(greeting)." $pd[0],</p>
      <p>".welcome_set_password_via."</p>
      <p><a href=\"$url\">$url</a></p>
      <p>".welcome_access_advice."</p>
      <p>".thank_you."</p>
      <img src=\"".URL."/".FOOTER_IMAGE_P."\" alt=\"".FROM_NAME."\" />
      <p style=\"font-size: 12px;\"><a href=\"".FOOTER_PRIVACY_POLICY_URL."\">".FROM_NAME." - ".privacy_policy."</a></p>
      </html>";
  }

    $result_mail = mail($pd[1][3],$subject,$message,implode("\r\n",$headers));

    /*
     * insert the token into the database
     * the token will be tied to a username and expired by cron.php
     * in case it is overlooked or rejected
     */
    $db = new DB();
    $stmt = $db->prepare('INSERT INTO pw_set_requests (pass,user_id) VALUES (:pass,:user_id)');
    $result_db = $stmt->execute(array(':pass' => $pass,':user_id' => $uid));
  
    if ($result_mail && $result_db) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    return false;
  }
}

function sendWelcomeEmail($uid) {
  /*
   * sends the welcome email
   * called after the password is successfully set in the last steps
   * of the user creation process
   * 
   * returns false on failure
   */
  $pd = getPersonalData($uid);
  $subject = 'El teu compte a '.FROM_NAME.' ja és actiu';
  
  $headers[] = 'From: '.FROM_NAME.' <'.FROM_ADDR.'>';
  $headers[] = 'Reply-To: '.FROM_REPLYTO;
  $headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-type: text/html; charset=utf-8';
  $headers[] = 'X-Mailer: '.TITLE;
  $headers[] = 'X-Mailer: PHP/'.phpversion();
  $headers[] = 'X-PHP-Originating-Script: '.TITLE;
  
  $message = "<html><p>".ucfirst(greeting_again).",</p>
<p>".account_ready." <strong>$pd[2]</strong>. ".account_ready_advice."</p>
<ul>
<li>".account_ready_server."</li>
<li>".ucfirst(username).": $pd[2]</li>
</ul>
<p>".account_ready_explanation."</p>
<p></p>
<p>".cheers."</p>
<img src=\"".URL."/".FOOTER_IMAGE_P."\" alt=\"".FROM_NAME."\" />
<p style=\"font-size: 12px;\"><a href=\"".FOOTER_PRIVACY_POLICY_URL."\">".FROM_NAME." - ".privacy_policy."</a></p>
</html>";

  if (mail($pd[3],$subject,$message,implode("\r\n",$headers))) {
    return true;
  }
  else {
    return false;
  }
}

?>
