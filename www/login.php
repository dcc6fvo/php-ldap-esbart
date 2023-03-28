<?php

require 'config.php';
require 'locale/'.LOCALE.'.php';
ini_set('error_reporting',ERROR_REPORTING);
ini_set('display_errors',DISPLAY_ERRORS);

session_start();
if (isset($_GET['action'])) {
  if ($_GET['action'] == 'logout') {
    session_destroy();
    $domain = explode('/', URL)[2];
    $path = explode('/', URL)[3];
    setcookie("sessionPersists", "", time()-3600, $path, $domain, 1);
    header("Location: login.php");
  }
}
ob_start();
//buffers output until end of page or ob_ functions

require 'include/functions.php';
require 'include/template/head-login.php';

if ($_POST) {
  $con = ldap_connect(LDAP_HOST);
  ldap_set_option($con,LDAP_OPT_PROTOCOL_VERSION,3);

  if ($con) {
    if (strlen($_POST['username']) == 0 || strlen($_POST['password']) == 0) {
      $err[] = both_fields_required;
      writeLog('login-error.log',both_fields_required_log);
    }
    else {
      $username = trim($_POST['username']);
      $pass = $_POST['password'];
      $udn=getUserDN($username);
      $bind = @ldap_bind($con,$udn,$pass);
      if ($bind) {
        # Get the groups the user is a member of
        # and allow access if member of LDAP_GROUP_ATTR
        $groups = getUserMembership($username);     
        $ldap_auth_group_cn = explode('=', explode(',', LDAP_AUTH_GROUP)[0])[1];
        if (in_array($ldap_auth_group_cn, $groups)) {
            $_SESSION['id'] = $username;
            writeLog('login-access.log',$udn.' '.logged_in);
            $domain = explode('/', URL)[2];
            $path = explode('/', URL)[3];
            setcookie("sessionPersists", $username, time()+3600*24*30, $path, $domain, 1);
            ob_end_clean(); //cleans the output buffer and stops buffering
            header("Location: index.php");
        }
        else {
          $err[] = unauthorized;
          writeLog('login-error.log',$udn.' '.unauthorized_log);
        }
      }
      else {
        $err[] = user_and_or_password_incorrect;
        writeLog('login-error.log',user_and_or_password_incorrect);
      }
    }
  }
}
else {
  $err = [];
}
?>
      <h1><?=TITLE?></h1>
      <form id="login" enctype="application/x-www-form-urlencoded" method="post" action="<?=$_SERVER['PHP_SELF']?>">
        <div><label for="username"><?=username?></label></div>
        <div><input name="username" type="text" class="short" value="" /></div>

        <div><label for="password"><?=password?></label></div>
        <div><input name="password" type="password" class="short" value="" /></div>

        <input name="envia" type="submit" value="<?=login_submit?>" />
<?php
  printMessages($err);
  echo <<<EOD
      </form>
    </div>
  </body>
</html>
EOD;

?>
