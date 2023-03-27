<?php

/**
 * php-ldap-esbart
 *
 * Copyright 2023 by Felipe Volpato <fvolpato@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with this program. 
 * If not, see <https://www.gnu.org/licenses/>.
 */

require 'config.php';
require 'locale/'.LOCALE.'.php';
ini_set('error_reporting',ERROR_REPORTING);
ini_set('display_errors',DISPLAY_ERRORS);

session_start();
if (!isset($_SESSION['id'])) {
  if (!isset($_COOKIE['sessionPersists'])) {
    header("Location: login.php");
  }
  else {
    $_SESSION['id'] = $_COOKIE['sessionPersists'];
  }
}

require 'include/functions.php';
require 'include/template/head.php';

if (!isset($_GET['module'])) {
  $_GET['module'] = 'home';
}

if ($_GET['module'] == 'home') {
  echo ' <h2>'.home."</h2>";
  echo " <div id='sec-menu'></div>";
}
else if ($_GET['module'] == 'users') {
  echo '      <h2>'.users."</h2>\n";
  echo '      <div id="sec-menu"><a href="?module=users&action=add&mode=manual">'.add.'</a> - <a href="?module=users&action=list">'.list_action."</a></div>\n";
}
else if ($_GET['module'] == 'groups') {
  echo '      <h2>'.groups."</h2>\n";
  echo '      <div id="sec-menu"><a href="?module=groups&action=add">'.add.'</a> - <a href="?module=groups&action=list">'.list_action."</a></div>\n";
}
else if ($_GET['module'] == 'devices') {
  echo '      <h2>'.devices."</h2>\n";
  echo '      <div id="sec-menu"><a href="?module=devices&action=add">'.add.'</a> - <a href="?module=devices&action=list">'.list_action."</a></div>\n";
}

if ($_POST) {
  $con = LDAPconnect()[0];
  if (!isset($_GET['mode'])) {
    require('include/'.$_GET['module'].'-'.$_GET['action'].'.php');
  }
  else {
    require('include/'.$_GET['module'].'-'.$_GET['action'].'-'.$_GET['mode'].'.php');
  }
  ldap_close($con);
}
else {
  $err = [];
}

if (isset($_GET['action'])) {
  if ($_GET['action'] == 'list') {
    require('include/'.$_GET['module'].'-list.php');
  }
  else {
    if (!isset($_GET['mode'])) {
      require('include/'.$_GET['module'].'-'.$_GET['action'].'-form.php');
    }
    else {
      require('include/'.$_GET['module'].'-'.$_GET['action'].'-'.$_GET['mode'].'-form.php');
    }
  }
}
else {
  //default page after a clean url
  require('include/'.$_GET['module'].'-list.php');
}

require 'include/template/base.php';

?>
